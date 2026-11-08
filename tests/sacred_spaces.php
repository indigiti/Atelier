<?php
declare(strict_types=1);
require __DIR__.'/_isolation.php';
require dirname(__DIR__) . '/src/bootstrap.php';
$items=array_values(array_filter(products_public(),fn($p)=>(string)($p['category']??'')==='sacred-spaces'));
if(count($items)!==20) throw new RuntimeException('Expected 20 Sacred Spaces products, got '.count($items));
$slugs=[];$skus=[];
foreach($items as $p){
    foreach(['guid','slug','sku','name','price','image','short','description','material','finish','dimensions','lead_time'] as $k) if(empty($p[$k])) throw new RuntimeException('Missing '.$k.' on '.($p['slug']??'unknown'));
    if(($p['collection']??'')!=='Sacred Spaces') throw new RuntimeException('Incorrect collection on '.$p['slug']);
    if(!is_file(dirname(__DIR__).'/public/assets/images/products/'.$p['image'])) throw new RuntimeException('Missing image '.$p['image']);
    if(!isset(configurator_rules()[$p['slug']])) throw new RuntimeException('Missing configurator rule for '.$p['slug']);
    $slugs[]=$p['slug'];$skus[]=$p['sku'];
}
if(count($slugs)!==count(array_unique($slugs))||count($skus)!==count(array_unique($skus))) throw new RuntimeException('Duplicate Sacred Spaces SKU/slug');
$tax=taxonomies();if(!in_array('sacred-spaces',array_column($tax['categories']??[],'slug'),true))throw new RuntimeException('Sacred Spaces taxonomy missing');
$urls=array_column(cms_sitemap_urls(),'loc');if(!in_array('/collections/sacred-spaces',$urls,true))throw new RuntimeException('Sacred Spaces collection missing from sitemap');
echo "PASS: 20 Sacred Spaces products, media, taxonomy, configurator rules and sitemap coverage verified.\n";
