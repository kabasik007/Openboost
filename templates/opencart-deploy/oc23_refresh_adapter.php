<?php
/**
 * OpenCart 2.3 OCMOD refresh adapter for server_bridge.php.
 *
 * Returns a callable. The bridge must have already loaded the storefront
 * config.php so DB_* and DIR_* constants are available.
 *
 * This adapter intentionally mirrors the OpenCart 2.3 refresh lifecycle:
 * clear generated modification files, load system/modification.xml,
 * system/*.ocmod.xml and enabled DB modifications, apply operations, then
 * write generated files under DIR_MODIFICATION.
 */

return function () {
    if (!defined('DIR_SYSTEM') || !defined('DIR_MODIFICATION') || !defined('DB_PREFIX')) {
        throw new Exception('OpenCart constants are not loaded.');
    }

    $siteRoot = rtrim(dirname(rtrim(DIR_SYSTEM, '/\\')), '/\\') . DIRECTORY_SEPARATOR;
    $catalogRoot = $siteRoot . 'catalog' . DIRECTORY_SEPARATOR;
    $adminRoot = $siteRoot . 'admin' . DIRECTORY_SEPARATOR;

    $port = defined('DB_PORT') ? DB_PORT : 3306;
    $db = @new mysqli(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE, $port);
    if ($db->connect_errno) {
        throw new Exception('Database connection failed during OCMOD refresh.');
    }
    $db->set_charset('utf8');

    $deleteTree = function ($root) {
        if (!is_dir($root)) {
            @mkdir($root, 0775, true);
            return;
        }
        $items = array();
        $it = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($it as $item) {
            if ($item->getFilename() === 'index.html') {
                continue;
            }
            $items[] = $item->getPathname();
        }
        foreach ($items as $path) {
            if (is_dir($path)) {
                @rmdir($path);
            } else {
                @unlink($path);
            }
        }
    };

    $deleteTree(DIR_MODIFICATION);

    $xmlSources = array();
    $baseXml = DIR_SYSTEM . 'modification.xml';
    if (is_file($baseXml)) {
        $xmlSources[] = array('source' => $baseXml, 'xml' => file_get_contents($baseXml));
    }
    $files = glob(DIR_SYSTEM . '*.ocmod.xml');
    if ($files) {
        foreach ($files as $file) {
            $xmlSources[] = array('source' => $file, 'xml' => file_get_contents($file));
        }
    }

    $table = DB_PREFIX . 'modification';
    $result = $db->query("SELECT code, name, xml FROM `" . $table . "` WHERE status = 1 ORDER BY modification_id ASC");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $xmlSources[] = array('source' => 'db:' . $row['code'], 'xml' => $row['xml']);
        }
    }

    $generated = array();
    $original = array();
    $log = array();
    $errors = array();

    $resolveTargets = function ($pathSpec) use ($catalogRoot, $adminRoot) {
        $targets = array();
        foreach (explode('|', $pathSpec) as $spec) {
            $spec = trim($spec);
            if ($spec === '') {
                continue;
            }
            if (strpos($spec, 'catalog/') === 0) {
                $absolute = $catalogRoot . substr($spec, 8);
            } elseif (strpos($spec, 'admin/') === 0) {
                $absolute = $adminRoot . substr($spec, 6);
            } elseif (strpos($spec, 'system/') === 0) {
                $absolute = DIR_SYSTEM . substr($spec, 7);
            } else {
                continue;
            }
            $matched = glob($absolute, GLOB_BRACE);
            if ($matched) {
                foreach ($matched as $match) {
                    if (is_file($match)) {
                        $targets[] = $match;
                    }
                }
            }
        }
        return array_values(array_unique($targets));
    };

    $keyForFile = function ($file) use ($catalogRoot, $adminRoot) {
        if (strpos($file, $catalogRoot) === 0) {
            return 'catalog/' . str_replace('\\', '/', substr($file, strlen($catalogRoot)));
        }
        if (strpos($file, $adminRoot) === 0) {
            return 'admin/' . str_replace('\\', '/', substr($file, strlen($adminRoot)));
        }
        if (strpos($file, DIR_SYSTEM) === 0) {
            return 'system/' . str_replace('\\', '/', substr($file, strlen(DIR_SYSTEM)));
        }
        return null;
    };

    $applyPlain = function ($content, $searchNode, $addNode) {
        $search = $searchNode->textContent;
        $trim = $searchNode->getAttribute('trim');
        if ($trim === '' || $trim === 'true') {
            $search = trim($search);
        }
        $add = $addNode->textContent;
        if ($addNode->getAttribute('trim') === 'true') {
            $add = trim($add);
        }
        $position = $addNode->getAttribute('position');
        if ($position === '') {
            $position = 'replace';
        }
        $offset = (int)$addNode->getAttribute('offset');
        $indexAttr = trim($searchNode->getAttribute('index'));
        $indexes = array();
        if ($indexAttr !== '') {
            foreach (explode(',', $indexAttr) as $index) {
                $indexes[] = (int)trim($index);
            }
        }

        $lines = explode("\n", str_replace("\r\n", "\n", $content));
        $matchNumber = 0;
        $changed = false;
        for ($i = 0; $i < count($lines); $i++) {
            if (stripos($lines[$i], $search) === false) {
                continue;
            }
            $selected = !$indexes || in_array($matchNumber, $indexes, true);
            $matchNumber++;
            if (!$selected) {
                continue;
            }

            if ($position === 'before') {
                $newLines = explode("\n", $add);
                array_splice($lines, max(0, $i - $offset), 0, $newLines);
                $i += count($newLines);
            } elseif ($position === 'after') {
                $newLines = explode("\n", $add);
                array_splice($lines, $i + 1 + $offset, 0, $newLines);
                $i += count($newLines);
            } else {
                if ($offset < 0) {
                    $start = max(0, $i + $offset);
                    $length = abs($offset) + 1;
                } else {
                    $start = $i;
                    $length = $offset + 1;
                }
                $replacement = str_replace($search, $add, $lines[$i]);
                array_splice($lines, $start, $length, array($replacement));
            }
            $changed = true;
        }
        return array(implode("\n", $lines), $changed, $search);
    };

    $applyRegex = function ($content, $searchNode, $addNode) {
        $pattern = trim($searchNode->textContent);
        $replacement = $addNode->textContent;
        $limit = $searchNode->getAttribute('limit');
        $limit = $limit === '' ? -1 : (int)$limit;
        $position = $addNode->getAttribute('position');
        if ($position === 'before') {
            $replacement = $replacement . '$0';
        } elseif ($position === 'after') {
            $replacement = '$0' . $replacement;
        }
        $count = 0;
        $updated = @preg_replace($pattern, $replacement, $content, $limit, $count);
        if ($updated === null) {
            throw new Exception('Invalid OCMOD regex: ' . $pattern);
        }
        return array($updated, $count > 0, $pattern);
    };

    foreach ($xmlSources as $source) {
        $xml = trim($source['xml']);
        if ($xml === '') {
            continue;
        }
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        if (!@$dom->loadXML($xml)) {
            $errors[] = 'Malformed XML: ' . $source['source'];
            continue;
        }
        $modNodes = $dom->getElementsByTagName('modification');
        if (!$modNodes->length) {
            continue;
        }
        $mod = $modNodes->item(0);
        $nameNodes = $mod->getElementsByTagName('name');
        $modName = $nameNodes->length ? trim($nameNodes->item(0)->textContent) : $source['source'];
        $log[] = 'MOD: ' . $modName;

        foreach ($mod->getElementsByTagName('file') as $fileNode) {
            $pathSpec = $fileNode->getAttribute('path');
            $targets = $resolveTargets($pathSpec);
            if (!$targets) {
                $errors[] = 'No target files for ' . $modName . ': ' . $pathSpec;
                continue;
            }

            foreach ($targets as $file) {
                $key = $keyForFile($file);
                if ($key === null) {
                    continue;
                }
                if (!isset($generated[$key])) {
                    $content = str_replace("\r\n", "\n", file_get_contents($file));
                    $generated[$key] = $content;
                    $original[$key] = $content;
                }

                foreach ($fileNode->getElementsByTagName('operation') as $operation) {
                    $searchNodes = $operation->getElementsByTagName('search');
                    $addNodes = $operation->getElementsByTagName('add');
                    if (!$searchNodes->length || !$addNodes->length) {
                        continue;
                    }
                    $searchNode = $searchNodes->item(0);
                    $addNode = $addNodes->item(0);

                    $ignoreNodes = $operation->getElementsByTagName('ignoreif');
                    if ($ignoreNodes->length) {
                        $ignore = $ignoreNodes->item(0);
                        $ignoreText = $ignore->textContent;
                        $shouldIgnore = $ignore->getAttribute('regex') === 'true'
                            ? (@preg_match($ignoreText, $generated[$key]) === 1)
                            : (strpos($generated[$key], $ignoreText) !== false);
                        if ($shouldIgnore) {
                            continue;
                        }
                    }

                    if ($searchNode->getAttribute('regex') === 'true') {
                        list($updated, $changed, $needle) = $applyRegex($generated[$key], $searchNode, $addNode);
                    } else {
                        list($updated, $changed, $needle) = $applyPlain($generated[$key], $searchNode, $addNode);
                    }

                    if (!$changed) {
                        $mode = strtolower($operation->getAttribute('error'));
                        $message = 'Search not found in ' . $key . ' for ' . $modName . ': ' . $needle;
                        $errors[] = $message;
                        if ($mode === 'abort') {
                            $generated[$key] = $original[$key];
                            break;
                        }
                        continue;
                    }
                    $generated[$key] = $updated;
                }
            }
        }
    }

    $written = 0;
    foreach ($generated as $key => $content) {
        if ($content === $original[$key]) {
            continue;
        }
        $destination = rtrim(DIR_MODIFICATION, '/\\') . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $key);
        $dir = dirname($destination);
        if (!is_dir($dir) && !@mkdir($dir, 0775, true)) {
            throw new Exception('Cannot create modification directory: ' . $dir);
        }
        if (file_put_contents($destination, $content, LOCK_EX) === false) {
            throw new Exception('Cannot write generated modification: ' . $destination);
        }
        $written++;
    }

    if (defined('DIR_LOGS')) {
        @file_put_contents(DIR_LOGS . 'modification.log', implode(PHP_EOL, array_merge($log, $errors)) . PHP_EOL, LOCK_EX);
    }

    $db->close();

    return array(
        'adapter' => 'opencart-2.3',
        'sources' => count($xmlSources),
        'generated_files' => $written,
        'warnings' => $errors,
    );
};
