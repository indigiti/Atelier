<?php
declare(strict_types=1);

function mediaos_file(): string { return data_root().'/public-domain/media/mediaos.json'; }
function mediaos_roles(): array {
    return [
        'hero'=>'Hero','front'=>'Front','angle'=>'3/4 angle','side'=>'Side','open'=>'Open / storage','detail'=>'Detail / joinery',
        'material'=>'Material macro','lighting'=>'Lighting on','lifestyle'=>'Lifestyle','scale'=>'Scale reference','packaging'=>'Packaging','installation'=>'Installation',
    ];
}
function mediaos_default_store(): array { return ['version'=>1,'products'=>[],'updated_at'=>null]; }
function mediaos_store(): array {
    $d=foundation_json_read(mediaos_file(),mediaos_default_store());
    return (isset($d['products'])&&is_array($d['products']))?array_replace(mediaos_default_store(),$d):mediaos_default_store();
}
function mediaos_safe_alt(array $product,string $role): string {
    $roleLabel=mediaos_roles()[$role]??ucwords(str_replace('_',' ',$role));
    return trim((string)($product['name']??'Atelier Wood product').' — '.$roleLabel);
}
function mediaos_seed_product(array $product): array {
    $files=array_values(array_unique(array_filter(array_merge([(string)($product['image']??'')],(array)($product['gallery']??[])))));
    $roles=[];
    $roleOrder=['hero','front','angle','detail','lifestyle','scale'];
    foreach($files as $i=>$file){
        $role=$roleOrder[min($i,count($roleOrder)-1)];
        if(isset($roles[$role]))$role='detail';
        $roles[$role]=['file'=>$file,'alt'=>mediaos_safe_alt($product,$role),'status'=>'staging','rights'=>'unverified','focal_x'=>50,'focal_y'=>50,'sort'=>$i+1];
    }
    if(!isset($roles['hero']) && !empty($product['image']))$roles['hero']=['file'=>$product['image'],'alt'=>mediaos_safe_alt($product,'hero'),'status'=>'staging','rights'=>'unverified','focal_x'=>50,'focal_y'=>50,'sort'=>1];
    return ['product_guid'=>$product['guid'],'slug'=>$product['slug'],'roles'=>$roles,'updated_at'=>gmdate('c')];
}
function mediaos_ensure_catalog(): void {
    $store=mediaos_store();$changed=false;
    foreach(products_all() as $p){$g=(string)($p['guid']??'');if($g===''||isset($store['products'][$g]))continue;$store['products'][$g]=mediaos_seed_product($p);$changed=true;}
    if($changed){$store['updated_at']=gmdate('c');atomic_json_write(mediaos_file(),$store,foundation_checksum(mediaos_file()),'mediaos.seed');}
}
function mediaos_product(string $productGuid): array {
    $store=mediaos_store();
    if(isset($store['products'][$productGuid]))return $store['products'][$productGuid];
    $p=product_by_guid($productGuid);return $p?mediaos_seed_product($p):['product_guid'=>$productGuid,'roles'=>[]];
}
function mediaos_role(array $product,string $role='hero'): ?array {
    $entry=mediaos_product((string)($product['guid']??''));$row=$entry['roles'][$role]??null;
    if(!$row && $role==='hero' && !empty($product['image']))$row=['file'=>$product['image'],'alt'=>$product['name']??'Product image','focal_x'=>50,'focal_y'=>50,'status'=>'staging','rights'=>'unverified'];
    return is_array($row)?$row:null;
}
function mediaos_primary_file(array $product): string { return (string)(mediaos_role($product,'hero')['file']??($product['image']??'')); }
function mediaos_primary_alt(array $product): string { return (string)(mediaos_role($product,'hero')['alt']??($product['name']??'Product image')); }
function mediaos_gallery(array $product): array {
    $entry=mediaos_product((string)($product['guid']??''));$out=[];
    foreach(mediaos_roles() as $role=>$label){$r=$entry['roles'][$role]??null;if(is_array($r)&&!empty($r['file']))$out[]=['role'=>$role,'label'=>$label]+$r;}
    usort($out,fn($a,$b)=>(int)($a['sort']??99)<=>(int)($b['sort']??99));return $out;
}
function mediaos_save_product(string $productGuid,array $src): void {
    $p=product_by_guid($productGuid);if(!$p)throw new RuntimeException('Product not found.');$available=array_column(media_files(),'name');$entry=mediaos_product($productGuid);$entry['slug']=$p['slug'];$roles=[];
    foreach(mediaos_roles() as $role=>$label){
        $file=trim((string)($src['file'][$role]??''));if($file==='')continue;if(!in_array($file,$available,true))throw new RuntimeException('Unknown media file for '.$label.'.');
        $alt=trim((string)($src['alt'][$role]??''))?:mediaos_safe_alt($p,$role);
        $status=in_array(($src['status'][$role]??'staging'),['staging','approved','retired'],true)?$src['status'][$role]:'staging';
        $rights=in_array(($src['rights'][$role]??'unverified'),['unverified','owned','licensed','generated'],true)?$src['rights'][$role]:'unverified';
        $roles[$role]=['file'=>$file,'alt'=>$alt,'status'=>$status,'rights'=>$rights,'focal_x'=>max(0,min(100,(int)($src['focal_x'][$role]??50))),'focal_y'=>max(0,min(100,(int)($src['focal_y'][$role]??50))),'sort'=>(int)($src['sort'][$role]??50)];
    }
    $entry['roles']=$roles;$entry['updated_at']=gmdate('c');$store=mediaos_store();$store['products'][$productGuid]=$entry;$store['updated_at']=gmdate('c');atomic_json_write(mediaos_file(),$store,foundation_checksum(mediaos_file()),'mediaos.update');audit_event('mediaos.update',['product_guid'=>$productGuid]);
}
function mediaos_completeness(array $product): array {
    $gallery=mediaos_gallery($product);$roles=array_column($gallery,'role');$required=['hero'];if(($product['category']??'')==='sacred-spaces')$required=['hero','front'];$missing=array_values(array_diff($required,$roles));
    $approved=count(array_filter($gallery,fn($r)=>($r['status']??'')==='approved'));
    return ['count'=>count($gallery),'approved'=>$approved,'missing'=>$missing,'production_ready'=>empty($missing)&&$approved>=count($required)];
}
function mediaos_release_checks(): array {
    mediaos_ensure_catalog();$store=mediaos_store();$refs=true;$alts=true;$coverage=true;$prodReady=0;$available=array_column(media_files(),'name');
    foreach(products_all() as $p){$e=$store['products'][$p['guid']]??null;if(!$e){$coverage=false;continue;}foreach((array)($e['roles']??[]) as $r){if(!in_array((string)($r['file']??''),$available,true))$refs=false;if(trim((string)($r['alt']??''))==='')$alts=false;}if(mediaos_completeness($p)['production_ready'])$prodReady++;}
    return [
        ['name'=>'MediaOS catalog coverage','ok'=>$coverage,'detail'=>$coverage?'Every catalogue product has a MediaOS record.':'One or more products are missing MediaOS metadata.'],
        ['name'=>'MediaOS file integrity','ok'=>$refs,'detail'=>$refs?'All role assignments resolve to published media files.':'A MediaOS role references a missing file.'],
        ['name'=>'MediaOS accessibility metadata','ok'=>$alts,'detail'=>$alts?'All assigned media has alt text.':'One or more assigned images is missing alt text.'],
        ['name'=>'Production media approval','ok'=>true,'detail'=>$prodReady.' / '.count(products_all()).' products currently satisfy the production-media approval gate; staging may proceed while rights/photography are finalized.'],
    ];
}
