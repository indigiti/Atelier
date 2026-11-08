<?php
declare(strict_types=1);
$root=dirname(__DIR__);putenv('APP_ENV=staging');if(!getenv('ATELIER_DATA_KEY'))putenv('ATELIER_DATA_KEY='.base64_encode(str_repeat('M',32)));if(!getenv('ATELIER_SANDBOX_WEBHOOK_SECRET'))putenv('ATELIER_SANDBOX_WEBHOOK_SECRET=mediaos-test');$_SERVER['SCRIPT_NAME']='/index.php';$_SERVER['HTTP_HOST']='staging.example.test';$_SERVER['REMOTE_ADDR']='127.0.0.1';require $root.'/src/bootstrap.php';
mediaos_ensure_catalog();$store=mediaos_store();$errors=[];
if(count($store['products'])<count(products_all()))$errors[]='MediaOS catalogue coverage is incomplete.';
foreach(products_all() as $p){$entry=mediaos_product($p['guid']);if(empty($entry['roles']['hero']['file']))$errors[]='Missing hero role: '.$p['slug'];if(trim((string)($entry['roles']['hero']['alt']??''))==='')$errors[]='Missing hero alt: '.$p['slug'];}
$tmp=tempnam(sys_get_temp_dir(),'aw-media').'.png';$py='from PIL import Image; Image.new("RGB",(32,32),(210,180,140)).save('.var_export($tmp,true).')';exec('python3 -c '.escapeshellarg($py));[$ok,$file]=media_upload(['error'=>UPLOAD_ERR_OK,'size'=>filesize($tmp),'tmp_name'=>$tmp,'name'=>'test upload.png']);@unlink($tmp);if(!$ok)$errors[]='Safe media upload fallback failed: '.$file;elseif(!str_ends_with($file,'.webp'))$errors[]='Media upload did not produce WebP.';else @unlink($root.'/public/assets/images/products/'.$file);
$themes=theme_allowed_surfaces();foreach(['sacred-spaces','furniture','lighting','gifting','outdoor','kids','trade'] as $surface)if(!isset($themes[$surface]))$errors[]='Missing ThemeOS surface: '.$surface;
if($errors){fwrite(STDERR,implode("\n",$errors)."\n");exit(1);}echo "PASS: MediaOS role coverage, alt metadata, safe Python/Pillow upload fallback and expanded ThemeOS surfaces verified.\n";
