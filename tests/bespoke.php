<?php
declare(strict_types=1);
require __DIR__.'/_isolation.php';
$root=dirname(__DIR__);
putenv('APP_ENV=staging');
if(!getenv('ATELIER_DATA_KEY'))putenv('ATELIER_DATA_KEY='.base64_encode(random_bytes(32)));
if(!getenv('ATELIER_SANDBOX_WEBHOOK_SECRET'))putenv('ATELIER_SANDBOX_WEBHOOK_SECRET=bespoke-test-secret-'.bin2hex(random_bytes(8)));
$_SERVER['REMOTE_ADDR']='127.0.0.1';$_SERVER['REQUEST_URI']='/';$_SERVER['SCRIPT_NAME']='/index.php';
require $root.'/src/bootstrap.php';
$errors=[];function bcheck(bool $ok,string $msg):void{global $errors;if(!$ok)$errors[]=$msg;}
foreach([commerce_customer_file(),commerce_address_file(),commerce_order_file(),commerce_payment_file(),commerce_request_file(),commerce_cart_file(),commerce_reset_file(),commerce_rate_file(),configurator_saved_file(),bespoke_quote_file(),bespoke_payment_file(),data_root().'/journals/commerce-audit.ndjson'] as $f)@unlink($f);
$_SESSION=[];$_SESSION['_created_at']=time();$_SESSION['_last_activity']=time();
try{
    [$ok,$u]=customer_register(['name'=>'Bespoke Client','email'=>'bespoke@example.invalid','phone'=>'+919333333333','password'=>'BespokeLongPass!23']);bcheck($ok,'Customer registration failed.');bcheck(customer_login('bespoke@example.invalid','BespokeLongPass!23'),'Customer login failed.');
    $p=product_by_slug('nordic-ladder-wall-shelf');bcheck((bool)$p,'Test product missing.');$sel=configurator_default_selection($p);$sel['height_cm']=112;[$sok,$saved]=save_customer_configuration($p['slug'],$sel,'Bespoke source');bcheck($sok,'Saved configuration failed.');
    [$qok,$quote]=bespoke_create_request(['slug'=>$p['slug'],'configuration_guid'=>$saved['guid'],'brief'=>'Increase the height and add a concealed drawer for keys and mail.','space'=>'Entryway','target_dimensions'=>'W 72 × H 125 × D 22 cm','budget'=>'₹12,000–₹18,000']);bcheck($qok&&is_array($quote),'Bespoke request failed.');
    bcheck(($quote['status']??'')==='REQUESTED','Initial quote status invalid.');bcheck(count(bespoke_customer_quotes())===1,'Customer quote list failed.');
    $raw=(string)@file_get_contents(bespoke_quote_file());bcheck(!str_contains($raw,'bespoke@example.invalid')&&!str_contains($raw,'concealed drawer'),'Bespoke quote data leaked plaintext.');
    try{bespoke_customer_decide($quote['guid'],'APPROVE');$errors[]='Customer approved before publication.';}catch(Throwable){}

    $_SESSION['admin_user']=['guid'=>'019-test-admin','name'=>'Test Admin','email'=>'admin@example.invalid','role'=>'admin'];
    $q=bespoke_admin_create_revision($quote['guid'],['title'=>'Entryway Ladder · Bespoke','scope'=>'Increase height to 125 cm, add one concealed drawer, preserve the open ladder composition and wall fixing logic.','dimensions'=>'W 72 × H 125 × D 22 cm','material'=>'Solid ash','finish'=>'Warm walnut matte','piece_amount'=>13500,'design_fee'=>1500,'delivery_estimate'=>1000,'deposit_percent'=>50,'lead_time'=>'4 weeks','valid_until'=>gmdate('Y-m-d',strtotime('+60 days')),'terms'=>'Final wall substrate check required before installation.']);
    $r=bespoke_latest_revision($q);bcheck(($r['pricing']['total']??0)===16000,'Revision total was not calculated from components.');bcheck(($r['deposit_amount']??0)===8000,'Deposit amount incorrect.');bcheck(($r['state']??'')==='DRAFT','New revision is not draft.');bcheck(hash_equals((string)$r['fingerprint'],bespoke_revision_hash($r)),'Revision fingerprint invalid.');
    $q=bespoke_admin_publish_revision($quote['guid']);bcheck(($q['status']??'')==='AWAITING_APPROVAL','Publish did not move quote to approval.');

    unset($_SESSION['admin_user']);
    $q=bespoke_customer_decide($quote['guid'],'APPROVE');bcheck(($q['status']??'')==='APPROVED_AWAITING_DEPOSIT','Approval did not open deposit stage.');bcheck((int)$q['approved_revision']===1,'Approved revision missing.');bcheck(($q['deposit']['status']??'')==='DUE'&&(int)$q['deposit']['amount']===8000,'Deposit due state invalid.');
    $attempt=bespoke_create_deposit_attempt($q);bcheck((int)$attempt['amount']===8000,'Deposit attempt amount invalid.');
    $event=['event_id'=>'bdep-fixed-001','attempt_guid'=>$attempt['guid'],'status'=>'SUCCESS','provider_reference'=>'bsp-fixed'];$rawEvent=json_encode($event,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR);$sig=sandbox_signature($rawEvent);
    $r1=bespoke_process_deposit_event($event,$rawEvent,$sig);$r2=bespoke_process_deposit_event($event,$rawEvent,$sig);bcheck(empty($r1['duplicate'])&&!empty($r2['duplicate']),'Deposit webhook idempotency failed.');
    try{bespoke_process_deposit_event($event,$rawEvent,'bad');$errors[]='Bad deposit signature accepted.';}catch(Throwable){}
    $q=bespoke_customer_quote($quote['guid']);bcheck(($q['status']??'')==='DEPOSIT_PAID'&&($q['deposit']['status']??'')==='PAID','Verified deposit did not settle quote.');

    $_SESSION['admin_user']=['guid'=>'019-test-admin','name'=>'Test Admin','email'=>'admin@example.invalid','role'=>'admin'];
    try{bespoke_admin_create_revision($quote['guid'],['scope'=>'Should fail','piece_amount'=>1]);$errors[]='Revision allowed after deposit.';}catch(Throwable){}
    $q=bespoke_admin_freeze($quote['guid'],'Final workshop review complete.');bcheck(($q['status']??'')==='DESIGN_FROZEN','Design Freeze did not complete.');bcheck(!empty($q['freeze']['fingerprint']),'Freeze fingerprint missing.');$calc=hash('sha256',json_encode($q['freeze']['snapshot'],JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR));bcheck(hash_equals((string)$q['freeze']['fingerprint'],$calc),'Freeze fingerprint invalid.');
    $again=bespoke_admin_freeze($quote['guid'],'Second call');bcheck(($again['freeze']['guid']??'')===($q['freeze']['guid']??''),'Design Freeze is not idempotent.');

    unset($_SESSION['admin_user']);customer_logout();[$ok2,$u2]=customer_register(['name'=>'Other Client','email'=>'otherbespoke@example.invalid','phone'=>'+919444444444','password'=>'OtherBespokePass!23']);bcheck($ok2,'Second customer registration failed.');customer_login('otherbespoke@example.invalid','OtherBespokePass!23');bcheck(bespoke_customer_quote($quote['guid'])===null,'Cross-customer quote access allowed.');

    $checks=bespoke_release_checks();foreach($checks as $c)if(in_array($c['name'],['Bespoke quote encryption','Bespoke quote schema','Bespoke quote number uniqueness','Design revision integrity','Design freeze integrity','Deposit workflow gate'],true))bcheck(!empty($c['ok']),'Release check failed: '.$c['name']);
}catch(Throwable $e){$errors[]='Unhandled: '.$e->getMessage();}
if($errors){fwrite(STDERR,implode("\n",array_unique($errors))."\n");exit(1);}echo "PASS: encrypted bespoke request, immutable revision, customer approval, deterministic deposit, signed/idempotent settlement, post-deposit revision lock, design freeze integrity and object authorization verified.\n";
