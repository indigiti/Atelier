<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli'){http_response_code(403);exit('CLI only');}
require dirname(__DIR__).'/src/bootstrap.php';
if(confidential_key()===null)throw new RuntimeException('ATELIER_DATA_KEY is missing.');
if(strlen((string)getenv('ATELIER_SENDGRID_API_KEY'))<20)throw new RuntimeException('Transactional email provider is not configured.');
$max=max(1,min(100,(int)($argv[1]??20)));
$sent=0;$retry=0;
for($i=0;$i<$max;$i++){
    $result=notification_dispatch_next();
    if($result==='EMPTY')break;
    if($result==='SENT')$sent++; else $retry++;
}
echo "Mail outbox processed: sent=$sent retry=$retry\n";
if($retry>0)exit(1);
