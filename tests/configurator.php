<?php
declare(strict_types=1);
require __DIR__.'/_isolation.php';
$root=dirname(__DIR__);
putenv('APP_ENV=staging');
if(!getenv('ATELIER_DATA_KEY')) putenv('ATELIER_DATA_KEY='.base64_encode(random_bytes(32)));
if(!getenv('ATELIER_SANDBOX_WEBHOOK_SECRET')) putenv('ATELIER_SANDBOX_WEBHOOK_SECRET=configurator-test-secret-'.bin2hex(random_bytes(8)));
$_SERVER['REMOTE_ADDR']='127.0.0.1';$_SERVER['REQUEST_URI']='/';$_SERVER['SCRIPT_NAME']='/index.php';
require $root.'/src/bootstrap.php';
$errors=[];function ccheck(bool $ok,string $msg):void{global $errors;if(!$ok)$errors[]=$msg;}
foreach([commerce_customer_file(),commerce_address_file(),commerce_order_file(),commerce_payment_file(),commerce_request_file(),commerce_cart_file(),commerce_reset_file(),commerce_rate_file(),configurator_saved_file(),data_root().'/journals/commerce-audit.ndjson'] as $f)@unlink($f);
$_SESSION=[];$_SESSION['_created_at']=time();$_SESSION['_last_activity']=time();
try{
    ccheck(count(configurator_rules())===count(products_public()),'Configurator rule coverage does not match active products.');
    $p=product_by_slug('nordic-ladder-wall-shelf');ccheck((bool)$p,'Nordic shelf missing.');
    $default=configurator_default_selection($p);$q1=configurator_quote($p,$default);$q2=configurator_quote($p,$default);
    ccheck(!empty($q1['ok'])&&$q1['unit_price']===(int)$p['price'],'Default configuration must equal base product price.');
    ccheck($q1['signature']===$q2['signature'],'Configurator quote is not deterministic.');
    $larger=$default;$larger['height_cm']=112;$qLarge=configurator_quote($p,$larger);ccheck($qLarge['unit_price']>$q1['unit_price'],'Dimension pricing did not increase for larger size.');
    $tamper=$larger;$tamper['material']='NOT-A-MATERIAL';$qt=configurator_quote($p,$tamper);ccheck(empty($qt['ok']),'Invalid material was accepted.');
    $priceTamper=$larger;$priceTamper['unit_price']=1;$qp=configurator_quote($p,$priceTamper);ccheck($qp['unit_price']===$qLarge['unit_price'],'Client unit_price field influenced server price.');

    [$ok,$u]=customer_register(['name'=>'Config Test','email'=>'config@example.invalid','phone'=>'+919111111111','password'=>'LongConfiguratorPass!23']);ccheck($ok,'Customer registration failed.');
    ccheck(customer_login('config@example.invalid','LongConfiguratorPass!23'),'Customer login failed.');
    [$sok,$saved]=save_customer_configuration($p['slug'],$larger,'Living room custom');ccheck($sok&&is_array($saved),'Saved configuration failed.');
    ccheck(customer_saved_configuration($saved['guid'])!==null,'Saved configuration cannot be read by owner.');
    $raw=(string)@file_get_contents(configurator_saved_file());ccheck(!str_contains($raw,'Living room custom')&&!str_contains($raw,'config@example.invalid'),'Saved configuration leaked plaintext.');

    ccheck(commerce_cart_add($p['slug'],1,'','',$larger),'Configured cart add failed.');$lines=commerce_cart_lines();ccheck(count($lines)===1,'Configured cart line missing.');
    ccheck($lines[0]['unit']===$qLarge['unit_price'],'Cart did not use server-authoritative configured price.');
    ccheck(($lines[0]['configuration']['height_cm']??0)===112,'Cart configuration snapshot missing dimension.');

    [$aok,$addr]=save_customer_address(['type'=>'home','name'=>'Config Test','phone'=>'+919111111111','line1'=>'1 Design Street','city'=>'Pune','state'=>'Maharashtra','pincode'=>'411001','is_default'=>'1']);ccheck($aok,'Address save failed.');
    $order=create_order_from_checkout($addr['guid'],false,'configurator-checkout-key');$item=$order['items'][0]??[];
    ccheck(($item['unit_price']??0)===$qLarge['unit_price'],'Order snapshot lost configured price.');
    ccheck(($item['configuration_snapshot']['height_cm']??0)===112,'Order snapshot lost configuration.');
    ccheck(!empty($item['configuration_signature'])&&!empty($item['pricing_version']),'Order snapshot missing pricing provenance.');

    customer_logout();[$ok2,$u2]=customer_register(['name'=>'Other Config','email'=>'otherconfig@example.invalid','phone'=>'+919222222222','password'=>'OtherConfiguratorPass!23']);ccheck($ok2,'Second registration failed.');customer_login('otherconfig@example.invalid','OtherConfiguratorPass!23');
    ccheck(customer_saved_configuration($saved['guid'])===null,'Cross-customer configuration access was allowed.');
}catch(Throwable $e){$errors[]='Unhandled: '.$e->getMessage();}
if($errors){fwrite(STDERR,implode("\n",array_unique($errors))."\n");exit(1);}echo "PASS: rule coverage, deterministic server pricing, input tamper rejection, dimension pricing, encrypted saved configurations, object authorization, configured cart pricing and immutable order configuration snapshots verified.\n";
