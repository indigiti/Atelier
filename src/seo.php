<?php
declare(strict_types=1);

/**
 * Atelier SEO / GEO — conservative, source-backed structured data and diagnostics.
 * No rankings, reviews, business location, shipping promises, certifications or
 * return policies are asserted without verifiable source data.
 */
function seo_indexable_environment(): bool {
    return ($GLOBALS['app']['environment'] ?? 'staging') === 'production';
}

function seo_private_route(string $path): bool {
    foreach (['/admin','/account','/checkout','/cart','/wishlist','/payment','/order-confirmed','/bespoke','/configuration','/trade','/passport','/health','/ready','/api','/feeds','/webhooks'] as $prefix) {
        if ($path === $prefix || str_starts_with($path, $prefix.'/')) return true;
    }
    return false;
}

function seo_robots_directive(string $path, array $query=[]): string {
    if (!seo_indexable_environment()) return 'noindex,nofollow,noarchive';
    if (seo_private_route($path)) return 'noindex,nofollow,noarchive';
    if ($path === '/shop' && $query !== []) return 'noindex,follow';
    if (str_starts_with($path,'/product/') && isset($query['config'])) return 'noindex,follow';
    if ($path === '/collections/sacred-spaces' && $query !== []) return 'noindex,follow';
    return 'index,follow,max-image-preview:large';
}

function seo_robots_txt(): string {
    // Deliberately allow crawling in staging so the HTTP/meta noindex can be seen.
    // Blocking crawling in robots.txt can stop search engines from detecting noindex.
    $lines=[
        'User-agent: *',
        'Allow: /',
        'Disallow: /admin/',
        'Disallow: /account/',
        'Disallow: /checkout',
        'Disallow: /cart',
        'Disallow: /wishlist',
        'Disallow: /payment/',
        'Disallow: /order-confirmed/',
        'Disallow: /api/',
        'Disallow: /webhooks/',
        'Disallow: /trade/',
        'Disallow: /configuration/',
    ];
    if (seo_indexable_environment()) $lines[]='Sitemap: '.absolute_url('/sitemap.xml');
    return implode("\n",$lines)."\n";
}

function seo_canonical(string $path): string {
    $path=parse_url($path,PHP_URL_PATH) ?: '/';
    return absolute_url($path);
}

function seo_page_breadcrumbs(string $path, string $title): array {
    $crumbs=[['name'=>'Home','url'=>seo_canonical('/')]];
    if (preg_match('~^/product/[a-z0-9-]+$~',$path)) {
        $crumbs[]=['name'=>'Shop','url'=>seo_canonical('/shop')];
    } elseif (preg_match('~^/(journal|guides)/[a-z0-9-]+$~',$path,$m)) {
        $crumbs[]=['name'=>ucfirst($m[1]),'url'=>seo_canonical('/'.$m[1])];
    } elseif (str_starts_with($path,'/collections/')) {
        $crumbs[]=['name'=>'Shop','url'=>seo_canonical('/shop')];
    } elseif ($path === '/') return [];
    $crumbs[]=['name'=>$title,'url'=>seo_canonical($path)];
    return $crumbs;
}

function seo_breadcrumb_schema(array $crumbs): array {
    return [
        '@type'=>'BreadcrumbList',
        '@id'=>seo_canonical('/').'#breadcrumb-'.substr(hash('sha256',json_encode($crumbs)),0,12),
        'itemListElement'=>array_map(static fn($item,$i)=>[
            '@type'=>'ListItem','position'=>$i+1,'name'=>$item['name'],'item'=>$item['url']
        ],$crumbs,array_keys($crumbs))
    ];
}

function seo_product_available(array $p): bool {
    return empty($p['inventory']['track']) || !empty($p['inventory']['made_to_order']) ||
        (int)($p['inventory']['stock']??0)>0;
}

function seo_product_price(array $p): int {
    if (function_exists('configurator_schema_for_product') &&
        function_exists('configurator_quote') &&
        configurator_schema_for_product($p)) {
        $q=configurator_quote($p,configurator_default_selection($p));
        if (!empty($q['ok'])) return (int)$q['unit_price'];
    }
    return (int)($p['price']??0);
}

function seo_product_schema(array $p): array {
    $link=seo_canonical('/product/'.(string)$p['slug']);
    $images=[];
    foreach ((array)($p['gallery']??[]) as $file) {
        if (is_string($file) && $file!=='') $images[]=absolute_url(image_url($file));
    }
    if (!$images && !empty($p['image'])) $images[]=absolute_url(image_url($p['image']));
    $schema=[
        '@type'=>'Product',
        '@id'=>$link.'#product',
        'name'=>(string)$p['name'],
        'sku'=>(string)$p['sku'],
        'description'=>trim((string)($p['description']??'')) ?: (string)($p['short']??''),
        'url'=>$link,
        'category'=>(string)($p['category_label']??$p['category']??''),
        'brand'=>['@type'=>'Brand','name'=>'Atelier Wood'],
        'offers'=>[
            '@type'=>'Offer',
            '@id'=>$link.'#offer',
            'url'=>$link,
            'priceCurrency'=>'INR',
            'price'=>seo_product_price($p),
            'availability'=>'https://schema.org/'.(seo_product_available($p)?'InStock':'OutOfStock'),
            'itemCondition'=>'https://schema.org/NewCondition',
            'seller'=>['@id'=>seo_canonical('/').'#organization']
        ]
    ];
    if ($images) $schema['image']=array_values(array_unique($images));
    if (!empty($p['material'])) $schema['material']=(string)$p['material'];
    return $schema;
}

function seo_document_graph(string $path,string $title,string $description,?array $product=null,?array $article=null): array {
    $home=seo_canonical('/');
    $link=seo_canonical($path);
    $graph=[
        ['@type'=>'Organization','@id'=>$home.'#organization','name'=>'Atelier Wood','url'=>$home],
        ['@type'=>'WebSite','@id'=>$home.'#website','name'=>'Atelier Wood','url'=>$home,
            'publisher'=>['@id'=>$home.'#organization']],
    ];
    $crumbs=seo_page_breadcrumbs($path,$title);
    $page=['@type'=>'WebPage','@id'=>$link.'#webpage','url'=>$link,'name'=>$title,
        'description'=>$description,'isPartOf'=>['@id'=>$home.'#website']];
    if ($crumbs) {
        $breadcrumb=seo_breadcrumb_schema($crumbs);
        $page['breadcrumb']=['@id'=>$breadcrumb['@id']];
        $graph[]=$breadcrumb;
    }
    $graph[]=$page;
    if ($product) {
        $node=seo_product_schema($product);
        $node['mainEntityOfPage']=['@id'=>$link.'#webpage'];
        $graph[]=$node;
    }
    if ($article) {
        $node=['@type'=>'Article','@id'=>$link.'#article','mainEntityOfPage'=>['@id'=>$link.'#webpage'],
            'headline'=>(string)$article['title'],'description'=>trim((string)($article['excerpt']??'')),
            'author'=>['@id'=>$home.'#organization'],'publisher'=>['@id'=>$home.'#organization']];
        if (!empty($article['published_at'])) $node['datePublished']=(string)$article['published_at'];
        if (!empty($article['updated_at'])) $node['dateModified']=(string)$article['updated_at'];
        if (!empty($article['hero_image'])) $node['image']=absolute_url(image_url((string)$article['hero_image']));
        $graph[]=$node;
    }
    return ['@context'=>'https://schema.org','@graph'=>$graph];
}

function seo_llms_txt(): string {
    $lines=[
        '# Atelier Wood',
        '',
        '> Handcrafted wood furniture, wall pieces, mandirs, decor and made-to-order products.',
        '> Product prices, availability and customisation must be verified on the linked product page.',
        '',
        '## Authoritative site pages',
        '- [Home]('.seo_canonical('/').')',
        '- [Product catalogue]('.seo_canonical('/shop').')',
        '- [Sacred Spaces collection]('.seo_canonical('/collections/sacred-spaces').')',
        '- [Materials]('.seo_canonical('/materials').')',
        '- [Guides]('.seo_canonical('/guides').')',
        '- [Journal]('.seo_canonical('/journal').')',
        '',
        '## Machine-readable sources',
        '- [Product data]('.seo_canonical('/feeds/ai-products.json').')',
        '- [Brand and product knowledge graph]('.seo_canonical('/feeds/knowledge.json').')',
        '- [Merchant data]('.seo_canonical('/feeds/google-merchant.xml').')',
        '',
        '## Product pages',
    ];
    foreach (products_public() as $p) $lines[]='- ['.str_replace(['[',']'], '', (string)$p['name']).']('.seo_canonical('/product/'.$p['slug']).')';
    $lines[]='';
    $lines[]='This file is optional documentation for crawlers; it does not grant permission to access private account or order data.';
    return implode("\n",$lines)."\n";
}

function seo_knowledge_graph(): array {
    $home=seo_canonical('/');
    $graph=[['@type'=>'Organization','@id'=>$home.'#organization','name'=>'Atelier Wood','url'=>$home]];
    foreach (products_public() as $p) $graph[]=seo_product_schema($p);
    foreach (cms_items(null,'PUBLISHED') as $item) {
        if (!empty($item['seo']['noindex']) || !in_array($item['type']??'',['GUIDE','JOURNAL','ROOM_STORY'],true)) continue;
        $link=seo_canonical(cms_content_route($item));
        $graph[]=['@type'=>'Article','@id'=>$link.'#article','url'=>$link,
            'headline'=>(string)$item['title'],
            'description'=>(string)($item['excerpt']??''),
            'datePublished'=>(string)($item['published_at']??$item['created_at']??''),
            'dateModified'=>(string)($item['updated_at']??''),
            'publisher'=>['@id'=>$home.'#organization']];
    }
    return ['@context'=>'https://schema.org','@graph'=>$graph];
}

function seo_sitemap_xml(): string {
    $xml='<?xml version="1.0" encoding="UTF-8"?>'."\n".
        '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
    foreach (cms_sitemap_urls() as $item) {
        $xml.='<url><loc>'.htmlspecialchars(seo_canonical($item['loc']),ENT_QUOTES|ENT_XML1,'UTF-8').'</loc>';
        if (!empty($item['lastmod']) && preg_match('/^\d{4}-\d{2}-\d{2}$/',(string)$item['lastmod'])) {
            $xml.='<lastmod>'.htmlspecialchars((string)$item['lastmod'],ENT_QUOTES|ENT_XML1,'UTF-8').'</lastmod>';
        }
        $xml.='</url>';
    }
    return $xml.'</urlset>';
}

function seo_product_issues(array $p): array {
    $issues=[];
    $title=trim((string)($p['seo']['title']??''));
    $description=trim((string)($p['seo']['description']??''));
    if ($title==='') $issues[]='Custom SEO title not set; product name fallback is used';
    if ($description==='') $issues[]='Custom meta description not set; short-description fallback is used';
    if (trim((string)($p['description']??''))==='') $issues[]='Detailed product description missing';
    if ((int)($p['price']??0)<=0) $issues[]='Price missing or invalid';
    $hero=mediaos_role($p,'hero');
    if (!$hero || empty($hero['file'])) $issues[]='Hero image missing';
    elseif (!is_file(dirname(__DIR__).'/public/assets/images/products/'.basename((string)$hero['file']))) $issues[]='Hero image file missing';
    if (empty($hero['alt'])) $issues[]='Image alt text missing';
    if (($hero['status']??'staging')!=='approved') $issues[]='Hero image is not approved for production';
    if (($hero['rights']??'unverified')==='unverified') $issues[]='Image rights not verified';
    return $issues;
}

function seo_diagnostics(): array {
    $products=products_public();$rows=[];$issueCount=0;
    foreach ($products as $p) {
        $issues=seo_product_issues($p);$issueCount+=count($issues);
        $rows[]=['guid'=>(string)$p['guid'],'slug'=>(string)$p['slug'],
            'name'=>(string)$p['name'],'issues'=>$issues,'count'=>count($issues)];
    }
    usort($rows,static fn($a,$b)=>($b['count']<=>$a['count']) ?: strcmp($a['name'],$b['name']));
    $duplicateTitles=[];$seen=[];
    foreach ($products as $p) {
        $title=text_lower(trim((string)($p['seo']['title']??'')) ?: (string)$p['name']);
        if (isset($seen[$title])) $duplicateTitles[]=$title;
        $seen[$title]=true;
    }
    $xmlValid=null;$merchantValid=null;
    if (function_exists('simplexml_load_string')) {
        $old=libxml_use_internal_errors(true);
        try {
            $xmlValid=simplexml_load_string(seo_sitemap_xml(),'SimpleXMLElement',LIBXML_NONET)!==false;
            $merchantValid=simplexml_load_string(merchant_xml(),'SimpleXMLElement',LIBXML_NONET)!==false;
        } finally { libxml_clear_errors();libxml_use_internal_errors($old); }
    }
    $cmsWarnings=[];
    foreach (cms_items(null,'PUBLISHED') as $item) {
        if (preg_match('/\b(test|demo|sample)\b/i',(string)($item['title']??''))) $cmsWarnings[]='Published test content: '.(string)$item['title'];
    }
    return [
        'summary'=>[
            'products'=>count($products),
            'product_issues'=>$issueCount,
            'products_ready'=>count(array_filter($rows,static fn($row)=>$row['count']===0)),
            'duplicate_titles'=>count($duplicateTitles),
            'published_articles'=>count(cms_items(null,'PUBLISHED')),
        ],
        'products'=>$rows,
        'duplicates'=>$duplicateTitles,
        'cms_warnings'=>$cmsWarnings,
        'sitemap_valid'=>$xmlValid,
        'merchant_valid'=>$merchantValid,
        'external'=>[
            'search_console'=>'Not connected / no verified indexing data',
            'merchant_center'=>'Not connected / feed approval unknown',
            'ai_referrals'=>'No provider attribution connected',
        ],
        'site_indexable'=>seo_indexable_environment(),
    ];
}

function seo_release_checks(): array {
    $diag=seo_diagnostics();return [
        ['name'=>'SEO sitemap XML validity','ok'=>$diag['sitemap_valid']===true,'detail'=>'Sitemap is well formed when SimpleXML is available.'],
        ['name'=>'SEO merchant XML validity','ok'=>$diag['merchant_valid']===true,'detail'=>'Merchant feed is well formed when SimpleXML is available.'],
        ['name'=>'SEO entity graph coverage','ok'=>count(seo_knowledge_graph()['@graph'])>=count(products_public())+1,
            'detail'=>'Brand and public catalogue have machine-readable schema nodes.'],
        // Informational: product content and image rights are explicit commercial gates, not staging CI blockers.
    ];
}
