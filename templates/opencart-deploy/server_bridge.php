<?php
/**
 * Openboost / SiteZilla OpenCart Deploy Bridge
 *
 * PHP 5.6-compatible reference implementation.
 * Copy to a private/restricted server path and edit CONFIG before use.
 * Never commit a real shared_secret.
 */

$CONFIG = array(
    'shared_secret' => 'CHANGE_TO_A_LONG_RANDOM_SECRET',
    'max_clock_skew' => 180,
    'max_body_bytes' => 1024 * 1024 * 2,
    'opencart_config' => dirname(__FILE__) . '/../config.php',
    'audit_log' => dirname(__FILE__) . '/deploy-audit.log',
    'ocmod_refresh_adapter' => dirname(__FILE__) . '/oc23_refresh_adapter.php',
    'allowed_ocmod_code_prefixes' => array('sitezilla_', 'jako_'),
    'allow_opcache_reset' => false,
    // Configure real project paths. Empty/non-existing paths are skipped.
    'cache_profiles' => array(
        'journal_templates' => array(
            dirname(__FILE__) . '/../system/storage/cache/',
        ),
        'journal_assets' => array(
            dirname(__FILE__) . '/../system/storage/cache/',
        ),
    ),
);

header('Content-Type: application/json; charset=utf-8');

function respond($ok, $data = array(), $status = 200) {
    http_response_code($status);
    $out = array('ok' => (bool)$ok);
    foreach ($data as $key => $value) {
        $out[$key] = $value;
    }
    echo json_encode($out);
    exit;
}

function audit($config, $event, $data) {
    $line = date('c') . ' ' . $event . ' ' . json_encode($data) . PHP_EOL;
    @file_put_contents($config['audit_log'], $line, FILE_APPEND | LOCK_EX);
}

function header_value($name) {
    $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
    return isset($_SERVER[$key]) ? trim($_SERVER[$key]) : '';
}

function secure_equals($a, $b) {
    if (function_exists('hash_equals')) {
        return hash_equals($a, $b);
    }
    if (!is_string($a) || !is_string($b) || strlen($a) !== strlen($b)) {
        return false;
    }
    $diff = 0;
    for ($i = 0; $i < strlen($a); $i++) {
        $diff |= ord($a[$i]) ^ ord($b[$i]);
    }
    return $diff === 0;
}

function require_auth($config, $body) {
    $timestamp = header_value('X-Deploy-Timestamp');
    $nonce = header_value('X-Deploy-Nonce');
    $signature = header_value('X-Deploy-Signature');

    if ($timestamp === '' || $nonce === '' || $signature === '') {
        respond(false, array('error' => 'Missing deploy authentication headers.'), 401);
    }
    if (!ctype_digit($timestamp) || abs(time() - (int)$timestamp) > (int)$config['max_clock_skew']) {
        respond(false, array('error' => 'Stale deploy timestamp.'), 401);
    }
    if (!preg_match('/^[a-f0-9]{16,128}$/i', $nonce)) {
        respond(false, array('error' => 'Invalid nonce.'), 401);
    }

    $signed = $timestamp . "\n" . $nonce . "\n" . $body;
    $expected = hash_hmac('sha256', $signed, $config['shared_secret']);
    if (!secure_equals($expected, $signature)) {
        respond(false, array('error' => 'Invalid signature.'), 401);
    }
}

function load_opencart_config($path) {
    if (!is_file($path)) {
        respond(false, array('error' => 'OpenCart config.php not found.'), 500);
    }
    require_once($path);
    $required = array('DB_HOSTNAME', 'DB_USERNAME', 'DB_PASSWORD', 'DB_DATABASE', 'DB_PREFIX');
    foreach ($required as $constant) {
        if (!defined($constant)) {
            respond(false, array('error' => 'Missing OpenCart DB constant: ' . $constant), 500);
        }
    }
}

function db_connect() {
    $port = defined('DB_PORT') ? DB_PORT : 3306;
    $db = @new mysqli(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE, $port);
    if ($db->connect_errno) {
        respond(false, array('error' => 'Database connection failed.'), 500);
    }
    $db->set_charset('utf8');
    return $db;
}

function allowed_code($config, $code) {
    foreach ($config['allowed_ocmod_code_prefixes'] as $prefix) {
        if (strpos($code, $prefix) === 0) {
            return true;
        }
    }
    return false;
}

function xml_meta_and_normalize($xml, $canonicalCode) {
    $dom = new DOMDocument('1.0', 'UTF-8');
    $dom->preserveWhiteSpace = true;
    if (!@$dom->loadXML($xml)) {
        throw new Exception('Malformed OCMOD XML.');
    }
    $mods = $dom->getElementsByTagName('modification');
    if ($mods->length < 1) {
        throw new Exception('XML does not contain <modification>.');
    }
    $root = $mods->item(0);

    $read = function($tag) use ($root) {
        $nodes = $root->getElementsByTagName($tag);
        return $nodes->length ? trim($nodes->item(0)->textContent) : '';
    };

    $codes = $root->getElementsByTagName('code');
    if ($codes->length) {
        while ($codes->item(0)->firstChild) {
            $codes->item(0)->removeChild($codes->item(0)->firstChild);
        }
        $codes->item(0)->appendChild($dom->createTextNode($canonicalCode));
    } else {
        $node = $dom->createElement('code');
        $node->appendChild($dom->createTextNode($canonicalCode));
        $root->insertBefore($node, $root->firstChild);
    }

    return array(
        'xml' => $dom->saveXML($dom->documentElement),
        'name' => $read('name') !== '' ? $read('name') : $canonicalCode,
        'code' => $canonicalCode,
        'version' => $read('version'),
        'author' => $read('author'),
        'link' => $read('link'),
    );
}

function regex_is_safe_owned($canonicalCode, $regex) {
    if (!is_string($regex) || strlen($regex) < 6 || strlen($regex) > 300) {
        return false;
    }
    // Require anchored regex so a config typo cannot wipe unrelated rows.
    return substr($regex, 0, 1) === '^' && substr($regex, -1) === '$';
}

function ocmod_upsert($config, $payload) {
    $canonical = isset($payload['canonical_code']) ? trim($payload['canonical_code']) : '';
    $xml = isset($payload['xml']) ? $payload['xml'] : '';
    $status = isset($payload['status']) ? (int)$payload['status'] : 1;
    $legacy = isset($payload['legacy_code_regexes']) && is_array($payload['legacy_code_regexes']) ? $payload['legacy_code_regexes'] : array();

    if ($canonical === '' || !preg_match('/^[a-zA-Z0-9_.-]{3,128}$/', $canonical)) {
        throw new Exception('Invalid canonical OCMOD code.');
    }
    if (!allowed_code($config, $canonical)) {
        throw new Exception('OCMOD code is outside configured ownership prefixes.');
    }
    if ($xml === '' || strlen($xml) > $config['max_body_bytes']) {
        throw new Exception('Missing or oversized OCMOD XML.');
    }

    $meta = xml_meta_and_normalize($xml, $canonical);
    $db = db_connect();
    $table = DB_PREFIX . 'modification';

    $stmt = $db->prepare("SELECT modification_id, status FROM `" . $table . "` WHERE code = ? ORDER BY modification_id ASC");
    $stmt->bind_param('s', $canonical);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    if ($row) {
        $id = (int)$row['modification_id'];
        $stmt = $db->prepare("UPDATE `" . $table . "` SET name=?, code=?, author=?, version=?, link=?, xml=?, status=?, date_added=NOW() WHERE modification_id=?");
        $stmt->bind_param('ssssssii', $meta['name'], $canonical, $meta['author'], $meta['version'], $meta['link'], $meta['xml'], $status, $id);
        $stmt->execute();
        $stmt->close();
        $mode = 'updated';
    } else {
        $stmt = $db->prepare("INSERT INTO `" . $table . "` SET name=?, code=?, author=?, version=?, link=?, xml=?, status=?, date_added=NOW()");
        $stmt->bind_param('ssssssi', $meta['name'], $canonical, $meta['author'], $meta['version'], $meta['link'], $meta['xml'], $status);
        $stmt->execute();
        $id = (int)$db->insert_id;
        $stmt->close();
        $mode = 'inserted';
    }

    // Remove only explicit, anchored, owned legacy patterns. Never fuzzy-delete.
    $removed = array();
    if ($legacy) {
        $res = $db->query("SELECT modification_id, code FROM `" . $table . "` WHERE code <> '" . $db->real_escape_string($canonical) . "'");
        while ($res && ($candidate = $res->fetch_assoc())) {
            foreach ($legacy as $regex) {
                if (!regex_is_safe_owned($canonical, $regex)) {
                    continue;
                }
                if (@preg_match('/' . str_replace('/', '\\/', $regex) . '/', $candidate['code']) === 1 && allowed_code($config, $candidate['code'])) {
                    $candidateId = (int)$candidate['modification_id'];
                    $db->query("DELETE FROM `" . $table . "` WHERE modification_id=" . $candidateId . " LIMIT 1");
                    $removed[] = $candidate['code'];
                    break;
                }
            }
        }
    }

    $db->close();
    return array('mode' => $mode, 'modification_id' => $id, 'code' => $canonical, 'version' => $meta['version'], 'removed_legacy_codes' => $removed);
}

function clear_directory_contents($path) {
    if (!is_dir($path)) {
        return 0;
    }
    $real = realpath($path);
    if ($real === false || strlen($real) < 6) {
        throw new Exception('Unsafe cache path.');
    }
    $count = 0;
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($real, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($iterator as $item) {
        $name = $item->getFilename();
        if ($name === 'index.html' || $name === '.htaccess') {
            continue;
        }
        if ($item->isDir()) {
            @rmdir($item->getPathname());
        } else {
            if (@unlink($item->getPathname())) {
                $count++;
            }
        }
    }
    return $count;
}

function cache_clear($config, $profile) {
    if (!isset($config['cache_profiles'][$profile]) || !is_array($config['cache_profiles'][$profile])) {
        throw new Exception('Unknown cache profile.');
    }
    $deleted = 0;
    foreach ($config['cache_profiles'][$profile] as $path) {
        $deleted += clear_directory_contents($path);
    }
    return array('profile' => $profile, 'deleted_files' => $deleted);
}

function ocmod_refresh($config) {
    $adapter = $config['ocmod_refresh_adapter'];
    if (!is_file($adapter)) {
        throw new Exception('Configured OCMOD refresh adapter not found.');
    }
    $callable = require($adapter);
    if (!is_callable($callable)) {
        throw new Exception('OCMOD refresh adapter must return a callable.');
    }
    return call_user_func($callable);
}

$body = file_get_contents('php://input');
if ($body === false || strlen($body) > $CONFIG['max_body_bytes']) {
    respond(false, array('error' => 'Invalid request body.'), 413);
}
require_auth($CONFIG, $body);

$request = json_decode($body, true);
if (!is_array($request) || empty($request['action'])) {
    respond(false, array('error' => 'Invalid JSON request.'), 400);
}
$action = $request['action'];
$payload = isset($request['payload']) && is_array($request['payload']) ? $request['payload'] : array();

load_opencart_config($CONFIG['opencart_config']);

try {
    if ($action === 'health') {
        $result = array('php' => PHP_VERSION, 'db_prefix' => DB_PREFIX);
    } elseif ($action === 'ocmod_upsert') {
        $result = ocmod_upsert($CONFIG, $payload);
    } elseif ($action === 'ocmod_refresh') {
        $result = ocmod_refresh($CONFIG);
    } elseif ($action === 'cache_clear') {
        $profile = isset($payload['profile']) ? $payload['profile'] : '';
        $result = cache_clear($CONFIG, $profile);
    } elseif ($action === 'opcache_reset') {
        if (!$CONFIG['allow_opcache_reset']) {
            throw new Exception('OPcache reset is disabled.');
        }
        $result = array('reset' => function_exists('opcache_reset') ? (bool)opcache_reset() : false);
    } else {
        throw new Exception('Unknown action.');
    }

    audit($CONFIG, $action, array('result' => $result, 'ip' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : ''));
    respond(true, array('action' => $action, 'result' => $result));
} catch (Exception $e) {
    audit($CONFIG, 'error', array('action' => $action, 'message' => $e->getMessage()));
    respond(false, array('error' => $e->getMessage()), 500);
}
