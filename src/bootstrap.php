<?php
declare(strict_types=1);

$root = dirname(__DIR__);
$GLOBALS['app'] = require $root . '/config/app.php';
if (($GLOBALS['app']['environment'] ?? 'staging') === 'production') {
    throw new RuntimeException('This v0.9.8.1 package is staging/file-mode only. Production must use the PostgreSQL-authoritative release path.');
}
require_once $root . '/src/helpers.php';
require_once $root . '/src/foundation.php';
require_once $root . '/src/security.php';
require_once $root . '/src/admin.php';
require_once $root . '/src/commerce.php';
require_once $root . '/src/enquiries.php';
require_once $root . '/src/notifications.php';
require_once $root . '/src/configurator.php';
require_once $root . '/src/bespoke.php';
require_once $root . '/src/makeos.php';
require_once $root . '/src/procurement.php';
require_once $root . '/src/quality.php';
require_once $root . '/src/fulfilment.php';
require_once $root . '/src/cms.php';
require_once $root . '/src/themes.php';
require_once $root . '/src/mediaos.php';
require_once $root . '/src/designos.php';
require_once $root . '/src/intelligence.php';
require_once $root . '/src/passport.php';
require_once $root . '/src/tradeos.php';
require_once $root . '/src/integrations.php';
require_once $root . '/src/seo.php';
require_once $root . '/src/commerceux.php';

/** Populate private persistent storage exactly once. Never replace existing runtime data. */
function atelier_seed_runtime_storage(string $sourceRoot): void {
    $target=data_root();
    if(rtrim($sourceRoot.'/storage','/')===rtrim($target,'/'))return;
    if(!is_dir($target) && !@mkdir($target,0750,true) && !is_dir($target))throw new RuntimeException('Persistent storage is unavailable.');
    $lock=@fopen($target.'/.atelier-seed.lock','c+');
    if(!$lock||!flock($lock,LOCK_EX))throw new RuntimeException('Persistent storage seed lock unavailable.');
    try {
        foreach(['catalog/products.json','catalog/taxonomies.json','catalog/configurator-rules.json','commerce/shipping-zones.json','content/cms.json','media/mediaos.json','themes/theme-settings.json'] as $relative){
            $source=$sourceRoot.'/storage/public-domain/'.$relative;
            if(!is_file($source))throw new RuntimeException('Required release seed missing: '.$relative);
            $destination=$target.'/public-domain/'.$relative;
            $directory=dirname($destination);
            if(!is_dir($directory)&&!@mkdir($directory,0750,true)&&!is_dir($directory))throw new RuntimeException('Could not create persistent storage directory.');
            if(!is_file($destination)){
                $content=file_get_contents($source);
                if($content===false)throw new RuntimeException('Could not read release seed.');
                $handle=@fopen($destination,'x');
                if(!$handle)throw new RuntimeException('Could not seed persistent state: '.$relative);
                try { if(fwrite($handle,$content)!==strlen($content))throw new RuntimeException('Incomplete seed write: '.$relative);fflush($handle); }
                finally { fclose($handle); }
                @chmod($destination,0640);
            }
        }
    } finally { flock($lock,LOCK_UN);fclose($lock); }
}
atelier_seed_runtime_storage($root);
mediaos_ensure_catalog();
designos_ensure_dirs();
intelligence_ensure_dirs();
passport_ensure_dirs();
trade_ensure_dirs();
function request_is_https(): bool {
    if (!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off') return true;
    if ((string)($_SERVER['SERVER_PORT'] ?? '') === '443') return true;
    $forwarded = strtolower(trim((string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')));
    return $forwarded === 'https';
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name('atelierwood_staging');
    session_start([
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
        'cookie_secure' => request_is_https(),
        'use_strict_mode' => true,
        'use_only_cookies' => true,
        'cookie_path' => '/',
    ]);
}

// Session lifetime enforcement. Admin sessions are invalidated rather than silently extended forever.
$now = time();
$created = (int)($_SESSION['_created_at'] ?? 0);
$last = (int)($_SESSION['_last_activity'] ?? 0);
$idle = max(300, (int)($GLOBALS['app']['session_idle_seconds'] ?? 1800));
$absolute = max($idle, (int)($GLOBALS['app']['session_absolute_seconds'] ?? 28800));
if (($created && ($now - $created) > $absolute) || ($last && ($now - $last) > $idle)) {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time()-42000, $params['path'], $params['domain'], (bool)$params['secure'], (bool)$params['httponly']);
    }
    session_regenerate_id(true);
}
if (empty($_SESSION['_created_at'])) $_SESSION['_created_at'] = $now;
$_SESSION['_last_activity'] = $now;

foundation_ensure_dirs();
ensure_storage_dirs();

$catalogPath = data_root().'/public-domain/catalog/products.json';
$products = foundation_json_read($catalogPath, []);
$GLOBALS['products'] = is_array($products) ? $products : [];
$GLOBALS['catalog_checksum'] = foundation_checksum($catalogPath);

if (PHP_SAPI === 'cli-server') {
    $GLOBALS['base_path'] = '';
} else {
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/index.php');
    $dir = rtrim(dirname($script), '/.');
    $GLOBALS['base_path'] = ($dir === '' || $dir === '/') ? '' : $dir;
}
