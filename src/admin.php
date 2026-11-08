<?php
declare(strict_types=1);

function data_root(): string { $configured=trim((string)getenv('ATELIER_STORAGE_ROOT')); return $configured!==''?rtrim($configured,'/'):dirname(__DIR__).'/storage'; }
function atelier_public_root(): string { $configured=trim((string)getenv('ATELIER_PUBLIC_ROOT')); return $configured!==''?rtrim($configured,'/'):dirname(__DIR__).'/public'; }
function atelier_upload_root(): string { return data_root().'/public-domain/uploads'; }
function atelier_media_file_exists(string $name): bool { $name=basename($name);return (bool)preg_match('/^[a-zA-Z0-9._-]+\.(?:webp|png|jpe?g)$/i',$name) && (is_file(atelier_public_root().'/assets/images/products/'.$name)||is_file(atelier_upload_root().'/products/'.$name)); }
function admin_user_file(): string { return data_root() . '/private-domain/admin/users.json'; }
function setup_key_file(): string { return data_root() . '/private-domain/admin/setup.key'; }
function catalog_file(): string { return data_root() . '/public-domain/catalog/products.json'; }
function taxonomy_file(): string { return data_root() . '/public-domain/catalog/taxonomies.json'; }
function audit_file(): string { return data_root() . '/journals/admin-audit.ndjson'; }
function revisions_dir(): string { return data_root() . '/revisions/catalog'; }

function json_read(string $path, array $default = []): array {
    return foundation_json_read($path, $default);
}

function ensure_storage_dirs(): void {
    foreach ([dirname(admin_user_file()), dirname(catalog_file()), dirname(audit_file()), revisions_dir(), data_root().'/indexes', data_root().'/backups', data_root().'/tmp', foundation_security_dir()] as $d) {
        if (!is_dir($d)) @mkdir($d, 0750, true);
    }
}

function atomic_json_write(string $path, array $payload, ?string $expectedChecksum = null, string $reason = 'atomic.json'): string {
    return foundation_json_write($path, $payload, $expectedChecksum, $reason);
}

function audit_event(string $action, array $context = []): void {
    ensure_storage_dirs();
    $identity=admin_identity();
    $ip=(string)($_SERVER['REMOTE_ADDR'] ?? '');
    $record = [
        'ts'=>gmdate('c'),
        'action'=>$action,
        'actor_guid'=>$identity['guid'] ?? null,
        'actor_role'=>$identity['role'] ?? null,
        'ip_hash'=>$ip!==''?substr(hash('sha256',$ip),0,20):null,
        'context'=>$context,
    ];
    $line = json_encode($record, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) . "\n";
    $fh = @fopen(audit_file(), 'ab');
    if ($fh) { flock($fh, LOCK_EX); fwrite($fh,$line); fflush($fh); flock($fh,LOCK_UN); fclose($fh); }
}

function password_algo(): string|int|null {
    return defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_DEFAULT;
}

function admin_users_legacy_plaintext(): bool {
    if (!is_file(admin_user_file())) return false;
    $raw = foundation_json_read(admin_user_file(), []);
    return array_is_list($raw) && isset($raw[0]['password_hash']);
}

function admin_users(): array {
    if (!is_file(admin_user_file())) return [];
    $raw = foundation_json_read(admin_user_file(), []);
    if (isset($raw['alg'], $raw['ciphertext'])) {
        try { return confidential_decrypt($raw, 'atelierwood:admin-users:v1'); }
        catch (Throwable) { return []; }
    }
    // v0.2.0 upgrade compatibility only. /admin/release flags this until migrated.
    return (array_is_list($raw) && isset($raw[0]['password_hash'])) ? $raw : [];
}

function admin_initialized(): bool { return is_file(admin_user_file()) && (filesize(admin_user_file()) ?: 0) > 0; }
function admin_identity(): ?array { return $_SESSION['admin_user'] ?? null; }
function admin_logged_in(): bool { return !empty($_SESSION['admin_user']['email']); }
function require_admin(): void {
    if (!admin_logged_in()) { flash('error','Please sign in to continue.'); redirect('/admin/login'); }
}

function admin_rate_file(): string { return foundation_security_dir() . '/admin-login-rate.json'; }

function admin_rate_key(string $email): string {
    $ip = (string)($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    return hash('sha256', text_lower(trim($email)) . '|' . $ip);
}

function admin_login_rate_status(string $email): array {
    $window = max(60, (int)($GLOBALS['app']['login_window_seconds'] ?? 900));
    $max = max(3, (int)($GLOBALS['app']['login_max_attempts'] ?? 8));
    $now = time(); $key = admin_rate_key($email);
    $data = foundation_json_read(admin_rate_file(), []);
    $row = $data[$key] ?? ['fails'=>[]];
    $fails = array_values(array_filter(array_map('intval', $row['fails'] ?? []), fn($ts)=>($now-$ts)<$window));
    return ['allowed'=>count($fails)<$max,'remaining'=>max(0,$max-count($fails)),'retry_after'=>$fails ? max(0,$window-($now-min($fails))) : 0];
}

function admin_login_rate_fail(string $email): void {
    $window = max(60, (int)($GLOBALS['app']['login_window_seconds'] ?? 900));
    $now=time(); $key=admin_rate_key($email);
    foundation_json_mutate(admin_rate_file(), function(array $data) use($window,$now,$key){
        foreach($data as $k=>$row){
            $fails=array_values(array_filter(array_map('intval',$row['fails']??[]),fn($ts)=>($now-$ts)<$window));
            if($fails) $data[$k]=['fails'=>$fails,'updated_at'=>gmdate('c')]; else unset($data[$k]);
        }
        $fails=array_values(array_filter(array_map('intval',$data[$key]['fails']??[]),fn($ts)=>($now-$ts)<$window));
        $fails[]=$now; $data[$key]=['fails'=>$fails,'updated_at'=>gmdate('c')];
        return $data;
    }, 'security.login_fail');
}

function admin_login_rate_clear(string $email): void {
    $key=admin_rate_key($email);
    foundation_json_mutate(admin_rate_file(), function(array $data) use($key){ unset($data[$key]); return $data; }, 'security.login_clear');
}

function admin_login(string $email, string $password): bool {
    $email = text_lower(trim($email));
    $status = admin_login_rate_status($email);
    if (!$status['allowed']) return false;
    foreach (admin_users() as $u) {
        if (text_lower((string)($u['email']??'')) !== $email) continue;
        if (!password_verify($password, (string)($u['password_hash']??''))) { admin_login_rate_fail($email); return false; }
        session_regenerate_id(true);
        $_SESSION['_created_at'] = time();
        $_SESSION['_last_activity'] = time();
        $_SESSION['admin_user'] = [
            'guid'=>$u['guid'] ?? null,
            'email'=>$u['email'],
            'name'=>$u['name'] ?? 'Administrator',
            'role'=>$u['role'] ?? 'admin',
        ];
        admin_login_rate_clear($email);
        audit_event('admin.login');
        return true;
    }
    admin_login_rate_fail($email);
    return false;
}

function admin_logout(): void {
    audit_event('admin.logout');
    unset($_SESSION['admin_user']);
    session_regenerate_id(true);
}

function uuidv7(): string {
    $ms = (int) floor(microtime(true) * 1000);
    $timeHex = str_pad(dechex($ms), 12, '0', STR_PAD_LEFT);
    $rand = bin2hex(random_bytes(10));
    $hex = $timeHex . $rand;
    $hex[12] = '7';
    $n = hexdec($hex[16]);
    $hex[16] = dechex(($n & 0x3) | 0x8);
    return substr($hex,0,8).'-'.substr($hex,8,4).'-'.substr($hex,12,4).'-'.substr($hex,16,4).'-'.substr($hex,20,12);
}

function staging_setup_key(): string {
    ensure_storage_dirs();
    $env = trim((string)getenv('ATELIER_SETUP_KEY'));
    if ($env !== '') return $env;
    if (!is_file(setup_key_file())) {
        $key = 'AW-' . strtoupper(bin2hex(random_bytes(9)));
        file_put_contents(setup_key_file(), $key . "\n", LOCK_EX);
        @chmod(setup_key_file(), 0640);
    }
    return trim((string)file_get_contents(setup_key_file()));
}

function create_first_admin(string $key, string $name, string $email, string $password): array {
    if (admin_initialized()) return [false,'Admin has already been initialized.'];
    if (!hash_equals(staging_setup_key(), trim($key))) return [false,'Invalid setup key.'];
    if (confidential_key() === null) return [false,'ATELIER_DATA_KEY must be configured before creating the administrator.'];
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return [false,'Enter a valid email address.'];
    if (text_len($password) < 12) return [false,'Use at least 12 characters for the admin password.'];
    $user = [
        'guid'=>uuidv7(), 'name'=>trim($name) ?: 'Administrator', 'email'=>text_lower(trim($email)),
        'role'=>'admin','password_hash'=>password_hash($password, password_algo()),'created_at'=>gmdate('c')
    ];
    try {
        confidential_json_write(admin_user_file(), [$user], 'atelierwood:admin-users:v1', foundation_checksum(admin_user_file()));
    } catch (Throwable $e) { return [false,'Unable to create encrypted administrator store: '.$e->getMessage()]; }
    if (is_file(setup_key_file())) @unlink(setup_key_file());
    audit_event('admin.setup',['actor_guid'=>$user['guid']]);
    return [true,'Administrator created. You can now sign in.'];
}

function taxonomies(): array {
    return json_read(taxonomy_file(), [
        'categories'=>[
            ['slug'=>'wall-shelves','name'=>'Wall Shelves'],['slug'=>'ritual-incense','name'=>'Ritual & Incense'],
            ['slug'=>'plant-displays','name'=>'Plant Displays'],['slug'=>'utility-gifting','name'=>'Utility & Gifting'],
            ['slug'=>'outdoor-accents','name'=>'Outdoor Accents'],
        ],
        'materials'=>['Teak','Walnut','Ash','Pine','Beech','Hardwood'],
        'finishes'=>['Natural matte','Warm walnut','Deep walnut','Honey oak','Espresso','Charcoal'],
        'collections'=>['Signature Objects','Quiet Rituals','Living Walls','Plant & Propagation','Gifting'],
    ]);
}

function save_taxonomies(array $data, ?string $expectedChecksum=null): void { atomic_json_write(taxonomy_file(), $data, $expectedChecksum, 'taxonomy.update'); audit_event('taxonomy.update'); }

function product_by_guid(string $guid): ?array {
    foreach (products_all() as $p) if (($p['guid']??'') === $guid) return $p;
    return null;
}

function products_save(array $products, string $reason='catalog.update', ?string $expectedChecksum=null): void {
    $existing = is_file(catalog_file()) ? (string)file_get_contents(catalog_file()) : '[]';
    if ($existing !== '') {
        $rev = revisions_dir() . '/' . gmdate('Ymd_His') . '_' . bin2hex(random_bytes(3)) . '.json';
        file_put_contents($rev, $existing, LOCK_EX); @chmod($rev,0640);
    }
    atomic_json_write(catalog_file(), array_values($products), $expectedChecksum, $reason);
    $GLOBALS['products'] = array_values($products);
    rebuild_catalog_index($products);
    audit_event($reason,['count'=>count($products)]);
}

function rebuild_catalog_index(array $products): void {
    $idx = ['generated_at'=>gmdate('c'),'by_guid'=>[],'by_slug'=>[],'by_sku'=>[]];
    foreach ($products as $i=>$p) {
        if (!empty($p['guid'])) $idx['by_guid'][$p['guid']]=$i;
        if (!empty($p['slug'])) $idx['by_slug'][$p['slug']]=$i;
        if (!empty($p['sku'])) $idx['by_sku'][$p['sku']]=$i;
    }
    atomic_json_write(data_root().'/indexes/catalog-index.json',$idx,null,'index.rebuild');
}

function product_form_payload(array $src, ?array $existing=null): array {
    $tax = taxonomies();
    $slug = strtolower(trim((string)($src['slug'] ?? '')));
    $slug = preg_replace('/[^a-z0-9]+/','-',$slug) ?: '';
    $slug = trim($slug,'-');
    $sku = strtoupper(trim((string)($src['sku'] ?? '')));
    $materials = array_values(array_filter(array_map('trim', preg_split('/\r?\n|,/', (string)($src['materials']??'')))));
    $finishes = array_values(array_filter(array_map('trim', preg_split('/\r?\n|,/', (string)($src['finishes']??'')))));
    $tags = array_values(array_filter(array_map('trim', explode(',', (string)($src['tags']??'')))));
    $product = [
        'guid'=>$existing['guid'] ?? uuidv7(),
        'slug'=>$slug,'name'=>trim((string)($src['name']??'')),'sku'=>$sku,
        'status'=>in_array(($src['status']??'draft'),['draft','active','archived'],true)?$src['status']:'draft',
        'category'=>trim((string)($src['category']??'')),
        'category_label'=>category_label(trim((string)($src['category']??''))),
        'collection'=>trim((string)($src['collection']??'')),
        'price'=>max(0,(int)($src['price']??0)),
        'compare_at_price'=>max(0,(int)($src['compare_at_price']??0)),
        'featured'=>isset($src['featured']) && (string)$src['featured']==='1',
        'image'=>trim((string)($src['image']??($existing['image']??''))),
        'gallery'=>$existing['gallery'] ?? array_values(array_filter([trim((string)($src['image']??''))])),
        'material'=>trim((string)($src['material']??'')),
        'finish'=>trim((string)($src['finish']??'')),
        'dimensions'=>trim((string)($src['dimensions']??'')),
        'lead_time'=>trim((string)($src['lead_time']??'')),
        'short'=>trim((string)($src['short']??'')),
        'description'=>trim((string)($src['description']??'')),
        'tags'=>$tags,'care'=>trim((string)($src['care']??'')),'warranty'=>trim((string)($src['warranty']??'')),
        'materials'=>$materials ?: array_values(array_filter([trim((string)($src['material']??''))])),
        'finishes'=>$finishes ?: array_values(array_filter([trim((string)($src['finish']??''))])),
        'inventory'=>[
            'track'=>isset($src['track_inventory']) && (string)$src['track_inventory']==='1',
            'stock'=>max(0,(int)($src['stock']??($existing['inventory']['stock']??0))),
            'made_to_order'=>isset($src['made_to_order']) && (string)$src['made_to_order']==='1',
        ],
        'customisation'=>[
            'enabled'=>isset($src['customisable']) && (string)$src['customisable']==='1',
            'notes'=>trim((string)($src['customisation_notes']??'')),
        ],
        'seo'=>[
            'title'=>trim((string)($src['seo_title']??'')),
            'description'=>trim((string)($src['seo_description']??'')),
        ],
        'updated_at'=>gmdate('c'),
        'created_at'=>$existing['created_at'] ?? gmdate('c'),
    ];
    return $product;
}

function validate_product_record(array $p, ?string $ignoreGuid=null): array {
    $errors=[];
    foreach (['name','slug','sku','category','short'] as $k) if (trim((string)($p[$k]??''))==='') $errors[] = ucfirst(str_replace('_',' ',$k)).' is required.';
    if (($p['price']??0) <= 0) $errors[]='Price must be greater than zero.';
    foreach (products_all() as $x) {
        if ($ignoreGuid && ($x['guid']??'')===$ignoreGuid) continue;
        if (($x['slug']??'')===($p['slug']??'')) $errors[]='Slug is already in use.';
        if (($x['sku']??'')===($p['sku']??'')) $errors[]='SKU is already in use.';
    }
    return array_values(array_unique($errors));
}

function upsert_product(array $product, ?string $expectedChecksum=null): void {
    $all=products_all(); $found=false;
    foreach ($all as $i=>$p) if (($p['guid']??'')===($product['guid']??'')) { $all[$i]=$product; $found=true; break; }
    if (!$found) $all[]=$product;
    products_save($all,$found?'product.update':'product.create',$expectedChecksum);
}

function delete_product(string $guid, ?string $expectedChecksum=null): bool {
    $all=products_all(); $before=count($all);
    $all=array_values(array_filter($all,fn($p)=>(string)($p['guid']??'')!==$guid));
    if (count($all)===$before) return false;
    products_save($all,'product.delete',$expectedChecksum); return true;
}

function media_files(): array {
    $files=[];
    foreach ([atelier_public_root().'/assets/images/products', atelier_upload_root().'/products'] as $dir) {
        foreach (glob($dir.'/*.{webp,jpg,jpeg,png}',GLOB_BRACE) ?: [] as $p) {
            $files[basename($p)]=['name'=>basename($p),'bytes'=>filesize($p)?:0,'mtime'=>filemtime($p)?:0];
        }
    }
    $files=array_values($files);
    usort($files,fn($a,$b)=>$b['mtime']<=>$a['mtime']); return $files;
}

function media_upload(array $file): array {
    if (($file['error']??UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_OK) return [false,'Upload failed.'];
    if (($file['size']??0)>8*1024*1024) return [false,'Image exceeds 8 MB.'];
    $tmp=(string)$file['tmp_name'];
    $finfo=new finfo(FILEINFO_MIME_TYPE); $mime=$finfo->file($tmp);
    if (!in_array($mime,['image/jpeg','image/png','image/webp'],true)) return [false,'Only JPEG, PNG or WebP images are allowed.'];
    $stem=strtolower(pathinfo((string)($file['name']??'image'),PATHINFO_FILENAME));
    $stem=trim((string)preg_replace('/[^a-z0-9]+/','-',$stem),'-') ?: 'image';
    $name=$stem.'-'.substr(bin2hex(random_bytes(5)),0,10).'.webp';
    $uploadDir=atelier_upload_root().'/products';
    if(!is_dir($uploadDir)&&!@mkdir($uploadDir,0750,true)&&!is_dir($uploadDir))return [false,'Protected upload directory unavailable.'];
    $dest=$uploadDir.'/'.$name;
    $raw=@file_get_contents($tmp); $img=$raw!==false && function_exists('imagecreatefromstring') ? @imagecreatefromstring($raw) : false;
    if ($img && function_exists('imagewebp')) {
        $w=imagesx($img); $h=imagesy($img); $pixels=$w*$h;
        if ($w>6000 || $h>6000 || $pixels>36000000) { imagedestroy($img); return [false,'Image dimensions exceed the 6000 px / 36 MP safety ceiling.']; }
        imagepalettetotruecolor($img); imagealphablending($img,true); imagesavealpha($img,true);
        $ok=imagewebp($img,$dest,84); imagedestroy($img); if (!$ok) return [false,'Unable to save optimized image.'];
    } else {
        $worker=dirname(__DIR__).'/python/media_worker.py';
        if(!is_file($worker)) return [false,'No safe image processor is available. Install PHP GD/WebP or enable the packaged Python/Pillow worker.'];
        $cmd='python3 '.escapeshellarg($worker).' '.escapeshellarg($tmp).' '.escapeshellarg($dest).' 2>&1';
        $out=[];$code=1;exec($cmd,$out,$code);$payload=json_decode(implode("\n",$out),true);
        if($code!==0 || empty($payload['ok'])){ @unlink($dest); return [false,'Safe image processing failed: '.(string)($payload['error']??'unknown error')]; }
    }
    @chmod($dest,0644); audit_event('media.upload',['file'=>$name]); return [true,$name];
}

function admin_metrics(): array {
    $all=products_all();
    return [
        'products'=>count($all),
        'active'=>count(array_filter($all,fn($p)=>($p['status']??'active')==='active')),
        'orders'=>function_exists('admin_orders')?count(admin_orders()):0,
        'customers'=>function_exists('admin_customers_count')?admin_customers_count():0,
        'requests'=>function_exists('admin_open_requests_count')?admin_open_requests_count():0,
        'bespoke'=>function_exists('bespoke_open_count')?bespoke_open_count():0,
        'workshop_jobs'=>function_exists('makeos_open_count')?makeos_open_count():0,
        'media'=>count(media_files()),
        'room_plans'=>function_exists('designos_room_plans')?count(designos_room_plans()):0,
        'visual_analyses'=>function_exists('intelligence_analyses')?count(intelligence_analyses()):0,
        'passports'=>function_exists('passports_all')?count(passports_all()):0,
        'trade_projects'=>function_exists('trade_projects')?count(trade_projects()):0,
    ];
}

function render_admin(string $template, array $data=[]): void {
    extract($data, EXTR_SKIP);
    $templateFile=dirname(__DIR__).'/templates/admin/'.$template.'.php';
    if(!is_file($templateFile)) throw new RuntimeException('Admin template not found: '.$template);
    ob_start(); include $templateFile; $content=ob_get_clean();
    include dirname(__DIR__).'/templates/admin/layout.php';
}

function release_checks(): array {
    $items=products_all(); $guids=[];$skus=[];$slugs=[];$schemaOk=true;
    foreach($items as $p){
        foreach(['guid','sku','slug','name','category','price'] as $k) if(empty($p[$k])) $schemaOk=false;
        if(!empty($p['guid']))$guids[]=$p['guid']; if(!empty($p['sku']))$skus[]=$p['sku']; if(!empty($p['slug']))$slugs[]=$p['slug'];
    }
    $dupes=fn($a)=>count($a)!==count(array_unique($a));
    $privateProtected=!str_starts_with(realpath(data_root().'/private-domain')?:'', realpath(dirname(__DIR__).'/public')?:'__public__');
    $revisionWritable=is_dir(revisions_dir()) && is_writable(revisions_dir());
    $mediaMissing=0; foreach($items as $p){ if(!empty($p['image']) && !atelier_media_file_exists((string)$p['image'])) $mediaMissing++; }
    $taxSlugs=array_column(taxonomies()['categories']??[],'slug'); $orphanCategories=0; foreach($items as $p) if(!in_array((string)($p['category']??''),$taxSlugs,true)) $orphanCategories++;
    $txIncomplete = foundation_incomplete_transactions();
    $cryptoAvailable = confidential_crypto_available();
    $dataKeyReady = confidential_key() !== null;
    $adminEncrypted = !is_file(admin_user_file()) || !admin_users_legacy_plaintext();
    $adminReadable = !is_file(admin_user_file()) || count(admin_users()) > 0;
    $cookieSecure = request_is_https() ? (bool)ini_get('session.cookie_secure') : true;
    $checks = [
        ['name'=>'Product schema','ok'=>$schemaOk,'detail'=>$schemaOk?'Required identity and commerce fields present.':'One or more required fields are missing.'],
        ['name'=>'GUID uniqueness','ok'=>!$dupes($guids),'detail'=>$dupes($guids)?'Duplicate GUID detected.':'No duplicate GUIDs.'],
        ['name'=>'SKU uniqueness','ok'=>!$dupes($skus),'detail'=>$dupes($skus)?'Duplicate SKU detected.':'No duplicate SKUs.'],
        ['name'=>'Slug uniqueness','ok'=>!$dupes($slugs),'detail'=>$dupes($slugs)?'Duplicate slug detected.':'No duplicate slugs.'],
        ['name'=>'Media references','ok'=>$mediaMissing===0,'detail'=>$mediaMissing===0?'All primary images resolve.':$mediaMissing.' missing image references.'],
        ['name'=>'Taxonomy referential integrity','ok'=>$orphanCategories===0,'detail'=>$orphanCategories===0?'All products reference canonical categories.':$orphanCategories.' product(s) reference unknown categories.'],
        ['name'=>'Private storage boundary','ok'=>$privateProtected,'detail'=>'Private-domain remains outside the public document root.'],
        ['name'=>'Catalogue revision store','ok'=>$revisionWritable,'detail'=>$revisionWritable?'Revision path is writable.':'Revision path is unavailable.'],
        ['name'=>'Transaction journal recovery','ok'=>count($txIncomplete)===0,'detail'=>count($txIncomplete)===0?'No incomplete BEGIN transactions detected.':count($txIncomplete).' incomplete transaction(s) require review.'],
        ['name'=>'Authenticated encryption capability','ok'=>$cryptoAvailable,'detail'=>$cryptoAvailable?'Authenticated encryption provider available.':'Neither libsodium nor OpenSSL authenticated encryption is available.'],
        ['name'=>'Confidential data key','ok'=>$dataKeyReady,'detail'=>$dataKeyReady?'ATELIER_DATA_KEY is configured and valid.':'Configure a 32-byte ATELIER_DATA_KEY before v0.3 customer/order persistence.'],
        ['name'=>'Encrypted admin identity store','ok'=>$adminEncrypted,'detail'=>$adminEncrypted?'Admin identities are encrypted or not yet initialized.':'Legacy plaintext admin store detected; run scripts/migrate_admin_encryption.php after configuring ATELIER_DATA_KEY.'],
        ['name'=>'Admin identity decryptability','ok'=>$adminReadable,'detail'=>$adminReadable?'Admin identity store is readable with the configured key or is not initialized.':'Admin identity store exists but cannot be decrypted; verify ATELIER_DATA_KEY.'],
        ['name'=>'HTTPS session cookie policy','ok'=>$cookieSecure,'detail'=>request_is_https()?'Secure session cookies are enabled on HTTPS.':'HTTP/local staging detected; Secure cookie will enable automatically under HTTPS.'],
        ['name'=>'Admin initialized','ok'=>admin_initialized(),'detail'=>admin_initialized()?'Administrator account exists.':'First-run admin setup is still required.'],
        ['name'=>'Production persistence gate','ok'=>($GLOBALS['app']['environment']??'staging')!=='production','detail'=>'This package is staging-only and must not be used as file-authoritative production.'],
    ];
    if (function_exists('commerce_release_checks')) $checks = array_merge($checks, commerce_release_checks());
    if (function_exists('configurator_release_checks')) $checks = array_merge($checks, configurator_release_checks());
    if (function_exists('bespoke_release_checks')) $checks = array_merge($checks, bespoke_release_checks());
    if (function_exists('makeos_release_checks')) $checks = array_merge($checks, makeos_release_checks());
    if (function_exists('procurement_release_checks')) $checks = array_merge($checks, procurement_release_checks());
    if (function_exists('quality_release_checks')) $checks = array_merge($checks, quality_release_checks());
    if (function_exists('fulfilment_release_checks')) $checks = array_merge($checks, fulfilment_release_checks());
    if (function_exists('cms_release_checks')) $checks = array_merge($checks, cms_release_checks());
    if (function_exists('theme_release_checks')) $checks = array_merge($checks, theme_release_checks());
    if (function_exists('mediaos_release_checks')) $checks = array_merge($checks, mediaos_release_checks());
    if (function_exists('designos_release_checks')) $checks = array_merge($checks, designos_release_checks());
    if (function_exists('intelligence_release_checks')) $checks = array_merge($checks, intelligence_release_checks());
    if (function_exists('passport_release_checks')) $checks = array_merge($checks, passport_release_checks());
    if (function_exists('trade_release_checks')) $checks = array_merge($checks, trade_release_checks());
    if (function_exists('integrations_release_checks')) $checks = array_merge($checks, integrations_release_checks());
    if (function_exists('seo_release_checks')) $checks = array_merge($checks, seo_release_checks());
    if (function_exists('security_release_checks')) $checks = array_merge($checks, security_release_checks());
    if (function_exists('commerceux_release_checks')) $checks = array_merge($checks, commerceux_release_checks());
    return $checks;
}
