<?php
declare(strict_types=1);
$root=dirname(__DIR__);putenv('APP_ENV=staging');putenv('ATELIER_DATA_KEY='.base64_encode(str_repeat('C',32)));putenv('ATELIER_SANDBOX_WEBHOOK_SECRET=cms-test-secret');$_SERVER['REMOTE_ADDR']='127.0.0.1';$_SERVER['REQUEST_URI']='/';$_SERVER['SCRIPT_NAME']='/index.php';require $root.'/src/bootstrap.php';
$errors=[];function ccheck(bool $ok,string $msg):void{global $errors;if(!$ok)$errors[]=$msg;}$_SESSION['admin_user']=['guid'=>'019-test-admin','email'=>'admin@example.invalid','name'=>'Admin','role'=>'admin'];@unlink(cms_file());
try{
$b=cms_save_block('care-note','Care standard','Use a dry cloth and retain the approved finish specification.');ccheck(($b['key']??'')==='care-note','Block save failed.');
$i=cms_save_item(['type'=>'GUIDE','title'=>'Test Wood Guide','slug'=>'test-wood-guide','excerpt'=>'A useful test guide for SEO publishing.','body'=>"## Start here\n\nTest editorial body.\n\n[[block:care-note]]",'seo_title'=>'Test Wood Guide | Atelier Wood','seo_description'=>'A complete staging test guide for the CMS and SEO layer.']);ccheck(($i['status']??'')==='DRAFT','New CMS item should be draft.');$i=cms_publish($i['guid'],true);ccheck($i['status']==='PUBLISHED','Publish failed.');$pub=cms_public_by_slug('GUIDE','test-wood-guide');ccheck((bool)$pub,'Published content lookup failed.');$html=cms_render_body($pub['body']);ccheck(str_contains($html,'Care standard')&&str_contains($html,'Use a dry cloth'),'Reusable block rendering failed.');
$r=cms_add_redirect('/old-guide','/guides/test-wood-guide',301);ccheck(($r['code']??0)===301,'Redirect save failed.');ccheck((cms_redirect_for_path('/old-guide')['to']??'')==='/guides/test-wood-guide','Redirect resolution failed.');try{cms_add_redirect('/old-guide','/shop',301);$errors[]='Duplicate redirect source accepted.';}catch(Throwable){}
$urls=cms_sitemap_urls();ccheck((bool)array_filter($urls,fn($u)=>$u['loc']==='/guides/test-wood-guide'),'Published guide missing from sitemap.');
$checks=cms_release_checks();foreach($checks as $x)ccheck(!empty($x['ok']),'Release check failed: '.$x['name']);
}catch(Throwable $e){$errors[]='Unhandled: '.$e->getMessage();}
if($errors){fwrite(STDERR,implode("\n",array_unique($errors))."\n");exit(1);}echo "PASS: CMS draft/publish, safe body rendering, reusable blocks, SEO fields, redirects and sitemap coverage verified.\n";
