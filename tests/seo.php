<?php
declare(strict_types=1);
require __DIR__.'/_isolation.php';
putenv('APP_ENV=staging');
putenv('ATELIER_DATA_KEY='.base64_encode(str_repeat('G',32)));
putenv('ATELIER_SANDBOX_WEBHOOK_SECRET=seo-test');
$_SERVER['HTTP_HOST']='staging.example.test';
$_SERVER['SCRIPT_NAME']='/index.php';
$_SERVER['REQUEST_URI']='/shop';
$_SERVER['REMOTE_ADDR']='127.0.0.1';
require dirname(__DIR__).'/src/bootstrap.php';
$errors=[];
function seo_check(bool $condition,string $message): void { global $errors; if(!$condition)$errors[]=$message; }

seo_check(!seo_indexable_environment(),'Staging unexpectedly indexable.');
seo_check(str_contains(seo_robots_directive('/shop'), 'noindex'),'Staging meta noindex is not enforced.');
seo_check(!str_contains(seo_robots_txt(),'Sitemap:'),'Staging robots advertised sitemap before launch.');
seo_check(str_contains(seo_robots_txt(),'Allow: /'),'Staging crawler cannot detect the noindex rule.');
$original=$GLOBALS['app']['environment'];
$GLOBALS['app']['environment']='production'; // Test the pure directive logic; never boot file-mode production.
seo_check(seo_robots_directive('/shop',['q'=>'shelf'])==='noindex,follow','Faceted shop page indexable.');
seo_check(seo_robots_directive('/account/orders')==='noindex,nofollow,noarchive','Account route indexable.');
seo_check(seo_robots_directive('/product/sample',['config'=>'private-token'])==='noindex,follow','Private product configuration indexable.');
seo_check(str_contains(seo_robots_txt(),'Sitemap: http://staging.example.test/sitemap.xml'),'Production robots sitemap missing.');
$GLOBALS['app']['environment']=$original;

$products=products_public();seo_check(count($products)>0,'Empty public catalogue.');
$first=$products[0]??null;
if($first){
    $schema=seo_product_schema($first);
    seo_check(($schema['@type']??'')==='Product','Product entity missing.');
    seo_check(($schema['offers']['@type']??'')==='Offer','Product uses non-merchant AggregateOffer.');
    seo_check(($schema['offers']['priceCurrency']??'')==='INR','Product currency missing.');
    seo_check(($schema['offers']['price']??0)>0,'Product base price invalid.');
    seo_check(str_starts_with((string)($schema['url']??''),'http://staging.example.test/product/'),'Product canonical URL invalid.');
    $graph=seo_document_graph('/product/'.$first['slug'],$first['name'],$first['short'],$first);
    seo_check(count(array_filter($graph['@graph'],fn($n)=>($n['@type']??'')==='BreadcrumbList'))===1,'BreadcrumbList missing.');
    seo_check(count(array_filter($graph['@graph'],fn($n)=>($n['@type']??'')==='Product'))===1,'Product duplicated in schema.');
    seo_check(($schema['offers']['price']??-1)===seo_product_price($first),'Default configured price mismatch.');
    seo_check(!isset($schema['aggregateRating']),'Invented rating detected.');
}
foreach($products as $p) {
    seo_check(trim((string)($p['seo']['title']??''))!=='','Product SEO title missing: '.$p['slug']);
    seo_check(trim((string)($p['seo']['description']??''))!=='','Product SEO description missing: '.$p['slug']);
}
$siteXml=seo_sitemap_xml();
$merchantXml=merchant_xml();
if(function_exists('simplexml_load_string')){
    $sx=simplexml_load_string($siteXml);
    seo_check($sx!==false && count($sx->url)>=count($products),'XML sitemap invalid or incomplete.');
    $mx=simplexml_load_string($merchantXml);
    seo_check($mx!==false,'Merchant XML invalid.');
}
seo_check(!str_contains($siteXml,'?q='),'Faceted search query leaked into sitemap.');
seo_check(!str_contains($merchantXml,'Furniture &gt; Home &amp; Garden'),'Fictitious Google taxonomy included.');
$llms=seo_llms_txt();
seo_check(str_contains($llms,'/feeds/knowledge.json'),'AI file lacks knowledge source.');
seo_check(!str_contains($llms,'/account/orders'),'Private orders exposed in AI index.');
$knowledge=seo_knowledge_graph();
seo_check(count($knowledge['@graph'])>=count($products)+1,'Machine-readable product coverage incomplete.');
$publicKnowledge=json_encode($knowledge,JSON_THROW_ON_ERROR);
foreach(['customer_guid','password_hash','address_snapshot'] as $sensitive){
    seo_check(!str_contains($publicKnowledge,'"'.$sensitive.'"'),'Private key exposed: '.$sensitive);
}
$report=seo_diagnostics();
seo_check(($report['summary']['products']??0)===count($products),'SEO report counts wrong.');
seo_check(($report['summary']['duplicate_titles']??999)===0,'Duplicate product title detected.');
seo_check(($report['site_indexable']??true)===false,'SEO dashboard claims staging indexing.');
seo_check(isset($report['external']['search_console']),'External verification status not disclosed.');
seo_check(atelier_public_root()===dirname(__DIR__).'/public','Local public asset root changed unexpectedly.');
seo_check(atelier_media_file_exists((string)$first['image']),'Packaged stock image not accessible.');
foreach(seo_release_checks() as $check)seo_check(!empty($check['ok']), 'SEO release check failed: '.$check['name']);
if($errors){fwrite(STDERR,implode("\n",array_unique($errors))."\n");exit(1);}
echo "PASS: stage noindex, production URL policy, rich Product Offer, breadcrumbs, safe knowledge graph, metadata, XML and SEO dashboard diagnostics.\n";
