<?php
declare(strict_types=1);
/** Durable encrypted transactional email outbox (SendGrid transport). */
function notification_outbox_path(): string { return data_root().'/private-domain/notifications/outbox.json'; }
function notification_enqueue(string $to,string $subject,string $body): string {
    if(!filter_var($to,FILTER_VALIDATE_EMAIL))throw new RuntimeException('Invalid notification recipient.');
    if(!filter_var((string)getenv('ATELIER_MAIL_FROM'),FILTER_VALIDATE_EMAIL))throw new RuntimeException('ATELIER_MAIL_FROM is not configured.');
    if(strlen((string)getenv('ATELIER_SENDGRID_API_KEY'))<20)throw new RuntimeException('ATELIER_SENDGRID_API_KEY is not configured.');
    $id=uuidv7();$row=['guid'=>$id,'to'=>$to,'subject'=>$subject,'body'=>$body,'status'=>'QUEUED','attempts'=>0,'next_attempt'=>time(),'lease_until'=>0,'created_at'=>gmdate('c')];
    confidential_json_mutate(notification_outbox_path(),'atelierwood:notifications:v1',
      function(array $rows)use($row){$rows[]=$row;return$rows;},'notification.enqueue');
    return $id;
}
function notification_send_sendgrid(array $item): void {
    if(!function_exists('curl_init'))throw new RuntimeException('PHP cURL extension is required for transactional email.');
    $key=trim((string)getenv('ATELIER_SENDGRID_API_KEY'));$from=trim((string)getenv('ATELIER_MAIL_FROM'));
    if(strlen($key)<20||!filter_var($from,FILTER_VALIDATE_EMAIL))throw new RuntimeException('Mail provider not configured.');
    $payload=json_encode(['personalizations'=>[['to'=>[['email'=>$item['to']]],'subject'=>$item['subject']]],
      'from'=>['email'=>$from],'content'=>[['type'=>'text/plain','value'=>$item['body']]]],JSON_THROW_ON_ERROR);
    $ch=curl_init('https://api.sendgrid.com/v3/mail/send');
    curl_setopt_array($ch,[CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>$payload,
       CURLOPT_HTTPHEADER=>['Authorization: Bearer '.$key,'Content-Type: application/json'],
       CURLOPT_RETURNTRANSFER=>true,CURLOPT_CONNECTTIMEOUT=>8,CURLOPT_TIMEOUT=>15,
       CURLOPT_SSL_VERIFYPEER=>true,CURLOPT_SSL_VERIFYHOST=>2]);
    $result=curl_exec($ch);$code=(int)curl_getinfo($ch,CURLINFO_HTTP_CODE);curl_close($ch);
    if($result===false||$code!==202)throw new RuntimeException('Transactional email provider did not accept the message (HTTP '.$code.').');
}
/** One-at-a-time leased delivery, safely restartable after process failure. */
function notification_dispatch_next(): string {
    $claimed=null;$now=time();
    confidential_json_mutate(notification_outbox_path(),'atelierwood:notifications:v1',function(array $rows)use(&$claimed,$now){
        foreach($rows as &$row) {
            if(($row['status']??'')==='SENT'||($row['status']??'')==='ABANDONED')continue;
            if(($row['status']??'')==='SENDING'&&(int)($row['lease_until']??0)>$now)continue;
            if((int)($row['next_attempt']??0)>$now)continue;
            if((int)($row['attempts']??0)>=8){$row['status']='ABANDONED';continue;}
            $row['status']='SENDING';$row['attempts']=(int)($row['attempts']??0)+1;$row['lease_until']=$now+120;$claimed=$row;break;
        }return $rows;
    },'notification.claim');
    if(!$claimed)return 'EMPTY';
    $ok=false;
    try { notification_send_sendgrid($claimed);$ok=true; } catch(Throwable $e) { error_log('Atelier transactional delivery attempt failed: '.get_class($e)); }
    confidential_json_mutate(notification_outbox_path(),'atelierwood:notifications:v1',function(array $rows)use($claimed,$ok,$now){
       foreach($rows as &$row)if(($row['guid']??'')===$claimed['guid']&&($row['status']??'')==='SENDING'){
           $row['status']=$ok?'SENT':'QUEUED';$row['lease_until']=0;
           $row['next_attempt']=$ok?0:$now+min(86400,60*(2**min(10,(int)$row['attempts'])));
           if($ok)$row['sent_at']=gmdate('c');
           break;
       }return $rows;
    },'notification.result');
    return $ok?'SENT':'RETRY';
}
