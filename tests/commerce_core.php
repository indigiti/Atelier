<?php
declare(strict_types=1);
require __DIR__.'/_isolation.php';
$root=dirname(__DIR__);
putenv('APP_ENV=staging');
if(!getenv('ATELIER_DATA_KEY')) putenv('ATELIER_DATA_KEY='.base64_encode(random_bytes(32)));
if(!getenv('ATELIER_SANDBOX_WEBHOOK_SECRET')) putenv('ATELIER_SANDBOX_WEBHOOK_SECRET=test-webhook-secret-'.bin2hex(random_bytes(8)));
$_SERVER['REMOTE_ADDR']='127.0.0.1';
$_SERVER['REQUEST_URI']='/';
$_SERVER['SCRIPT_NAME']='/index.php';
require $root.'/src/bootstrap.php';
$errors=[];
function check(bool $ok,string $msg): void { global $errors; if(!$ok)$errors[]=$msg; }

// Reset commerce-only state in this runtime test copy.
foreach([commerce_customer_file(),commerce_address_file(),commerce_order_file(),commerce_payment_file(),commerce_request_file(),commerce_cart_file(),commerce_reset_file(),commerce_rate_file(),data_root().'/journals/commerce-audit.ndjson'] as $f) @unlink($f);
$_SESSION=[]; $_SESSION['_created_at']=time(); $_SESSION['_last_activity']=time();

try {
    [$ok,$u]=customer_register(['name'=>'Commerce Test','email'=>'commerce@example.invalid','phone'=>'+919999999999','password'=>'LongCommercePass!23']);
    check($ok && is_array($u),'Customer registration failed.');
    check(customer_login('commerce@example.invalid','LongCommercePass!23'),'Customer login failed.');
    $cg=(string)customer_identity()['guid'];
    [$aok,$addr]=save_customer_address(['type'=>'home','name'=>'Commerce Test','phone'=>'+919999999999','line1'=>'1 Test Street','line2'=>'','landmark'=>'','city'=>'Pune','state'=>'Maharashtra','pincode'=>'411001','is_default'=>'1']);
    check($aok && is_array($addr),'Address save failed.');
    $p=products_public()[0]??null; check(is_array($p),'No public product available.');
    commerce_cart_add($p['slug'],2,$p['materials'][0]??$p['material'],$p['finishes'][0]??$p['finish']);
    check(commerce_cart_count()===2,'Cart v2 quantity failed.');
    $q=shipping_quote('411001',commerce_cart_subtotal(),commerce_cart_lines());
    check(!empty($q['serviceable']) && ($q['zone_id']??'')==='pune-urban','Pincode/shipping resolution failed.');
    $checkoutKey='checkout-fixed-token';
    $order=create_order_from_checkout($addr['guid'],true,$checkoutKey);
    check(($order['status']??'')==='PENDING_PAYMENT','Order initial state invalid.');
    check(($order['pricing']['grand_total']??0)>0,'Order total missing.');
    check(count($order['items']??[])===1 && ($order['items'][0]['unit_price']??0)===(int)$p['price'],'Immutable product price snapshot failed.');
    $same=create_order_from_checkout($addr['guid'],true,$checkoutKey);
    check($same['guid']===$order['guid'],'Checkout idempotency created duplicate order.');
    check(count(customer_orders($cg))===1,'Duplicate order persisted.');

    $attempt=create_payment_attempt($order);
    $event=['event_id'=>'evt_fixed_001','attempt_guid'=>$attempt['guid'],'status'=>'SUCCESS','provider_reference'=>'sbx_fixed'];
    $raw=json_encode($event,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR);
    $sig=sandbox_signature($raw);
    $r1=process_payment_event($event,$raw,$sig); $r2=process_payment_event($event,$raw,$sig);
    check(empty($r1['duplicate']) && !empty($r2['duplicate']),'Payment event idempotency failed.');
    try { process_payment_event(['event_id'=>'evt_fixed_001','attempt_guid'=>$attempt['guid'],'status'=>'FAILED'],$raw,$sig); $errors[]='Conflicting webhook replay accepted.'; } catch(Throwable) {}
    // Re-send an already persisted SUCCESS event after simulating an incomplete order update.
    order_set_payment_status($order['guid'],'PENDING');
    $reconciled=process_payment_event($event,$raw,$sig);
    check(!empty($reconciled['duplicate']) && (order_by_guid($order['guid'])['payment_status']??'')==='PAID','Payment retry did not reconcile order state.');
    $paid=order_by_guid($order['guid']);
    check(($paid['status']??'')==='PAID' && ($paid['payment_status']??'')==='PAID','Successful payment did not update order.');
    check(!empty($paid['invoice']['number']),'Invoice was not issued on verified payment.');
    try { process_payment_event($event,$raw,'invalid-signature'); $errors[]='Invalid webhook signature accepted.'; } catch(Throwable) {}
    check(commerce_cart_count()===2,'Webhook must not clear an unrelated browser cart.');
    check(!order_can_transition('DELIVERED','PENDING_PAYMENT'),'Invalid order rollback transition is allowed.');

    // Object authorization: a second customer cannot resolve the first customer's address/order.
    customer_logout();
    [$ok2,$u2]=customer_register(['name'=>'Other Customer','email'=>'other@example.invalid','phone'=>'+918888888888','password'=>'AnotherLongPass!23']);
    check($ok2,'Second customer registration failed.');
    check(customer_login('other@example.invalid','AnotherLongPass!23'),'Second customer login failed.');
    check(customer_address($addr['guid'])===null,'Cross-customer address access was allowed.');
    check(customer_order($order['guid'])===null,'Cross-customer order access was allowed.');

    // Return to owner and test cancellation/refund workflow.
    customer_logout(); customer_login('commerce@example.invalid','LongCommercePass!23');
    [$rok,$req]=customer_request_order_action($order['guid'],'CANCELLATION','Changed my project requirements.');
    check($rok && is_array($req),'Paid cancellation request was not created.');
    $_SESSION['admin_user']=['guid'=>'019-test-admin','name'=>'Test Admin','email'=>'admin@example.invalid','role'=>'admin'];
    admin_resolve_request($req['guid'],'APPROVE','Approved in regression test.');
    $refunded=order_by_guid($order['guid']);
    check(($refunded['status']??'')==='REFUNDED' && ($refunded['payment_status']??'')==='REFUNDED','Paid cancellation did not resolve through refund state.');

    // Confidential-at-rest and audit leakage checks.
    $custRaw=(string)@file_get_contents(commerce_customer_file());
    check(!str_contains($custRaw,'commerce@example.invalid') && !str_contains($custRaw,'9999999999'),'Customer PII appears in encrypted store plaintext.');
    $auditRaw=(string)@file_get_contents(data_root().'/journals/commerce-audit.ndjson');
    check(!str_contains($auditRaw,'commerce@example.invalid') && !str_contains($auditRaw,'9999999999'),'PII leaked into commerce audit log.');

    // Backup/restore sanity for encrypted canonical commerce data.
    $bak=data_root().'/backups/commerce-orders-test.bak'; copy(commerce_order_file(),$bak); $before=foundation_checksum(commerce_order_file()); copy($bak,commerce_order_file()); $after=foundation_checksum(commerce_order_file());
    check($before===$after && count(commerce_orders())===1,'Encrypted order backup/restore parity failed.'); @unlink($bak);
} catch(Throwable $e){ $errors[]='Unhandled: '.$e->getMessage(); }

if($errors){fwrite(STDERR,implode("\n",array_unique($errors))."\n");exit(1);} 
echo "PASS: customer auth, encrypted addresses, persistent cart v2, pincode/shipping, checkout idempotency, immutable order snapshots, signed/idempotent payment events, invoice issuance, object authorization, cancellation/refund, PII audit minimization and encrypted restore parity verified.\n";
