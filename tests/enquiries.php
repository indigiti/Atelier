<?php
declare(strict_types=1);
require __DIR__.'/_isolation.php';
putenv('APP_ENV=staging');
putenv('ATELIER_DATA_KEY='.base64_encode(str_repeat('E',32)));
$_SERVER['SCRIPT_NAME']='/index.php';
$_SERVER['HTTP_HOST']='staging.example.test';
$_SERVER['REMOTE_ADDR']='127.0.0.2';
require dirname(__DIR__).'/src/bootstrap.php';
@unlink(enquiry_store_path());
putenv('ATELIER_MAIL_FROM=studio@example.test');
putenv('ATELIER_SENDGRID_API_KEY='.str_repeat('T',40));
$errors=[];
[$ok,$result]=enquiry_submit(['name'=>'Test Client','phone'=>'+919876543210','email'=>'client@example.test','project'=>'A wall-mounted bespoke wood sculpture for the entryway.']);
if(!$ok||!is_string($result))$errors[]='Valid enquiry rejected.';
$rows=enquiries_all();
if(count($rows)!==1||($rows[0]['project']??'')!=='A wall-mounted bespoke wood sculpture for the entryway.')$errors[]='Encrypted enquiry did not persist.';
if(str_contains((string)file_get_contents(enquiry_store_path()),'9876543210'))$errors[]='Private enquiry contact details leaked in plaintext.';
$_SESSION['admin_user']=['guid'=>'test-admin','name'=>'Test','email'=>'admin@example.test','role'=>'admin'];
enquiry_set_status((string)$rows[0]['guid'],'CONTACTED');
if((enquiries_all()[0]['status']??'')!=='CONTACTED')$errors[]='Enquiry admin update failed.';
[$invalid]=enquiry_submit(['name'=>'A','phone'=>'xx','project'=>'short']);
if($invalid)$errors[]='Invalid enquiry accepted.';
$id=notification_enqueue('customer@example.test','Test delivery','Private message test');
$raw=(string)file_get_contents(notification_outbox_path());
if(!str_contains($raw,'ciphertext')||str_contains($raw,'customer@example.test'))$errors[]='Notification outbox was not encrypted.';
if($errors){fwrite(STDERR,implode("\n",$errors)."\n");exit(1);}
echo "PASS: encrypted enquiry storage, confidentiality, validation, and admin state transitions.\n";
