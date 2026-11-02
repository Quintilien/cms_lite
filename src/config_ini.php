<?php
// CMS Lite — reading config/config.ini, shared by the site (src/config.php)
// and the editor (admin/lib.php). Rev. 4.5.0: previously two readers,
// written differently (the site's one cut a value containing "=").
//
// Format, unchanged since version 1: one "key = value" line per setting.
// This is not quite PHP's INI format (parse_ini_file): the same key may
// appear on several lines (external_db_allowed, one line per database),
// and ";" does not start a comment (a title may contain one). Lines
// without "=" are ignored; a leading UTF-8 BOM is ignored.

declare(strict_types=1);

// config/config.ini (production, not in the Git repository); otherwise config.ini.example
// (demo). $config_dir: the config/ directory, next to the served site.
function cms_config_ini_file(string $config_dir): ?string {
    foreach (['config.ini', 'config.ini.example'] as $f) {
        if (is_file($config_dir . '/' . $f)) return $config_dir . '/' . $f;
    }
    return null;
}

// All values, per key: ['db' => ['../data/x.db'], 'external_db_allowed' => [..., ...]]
function cms_config_ini(string $config_dir): array {
    static $cache = [];
    if (isset($cache[$config_dir])) return $cache[$config_dir];
    $out  = [];
    $file = cms_config_ini_file($config_dir);
    $lines = $file !== null ? (@file($file) ?: []) : [];
    foreach ($lines as $i => $line) {
        if ($i === 0) $line = preg_replace('/^\xEF\xBB\xBF/', '', $line);
        $kv = explode('=', $line, 2);
        if (count($kv) !== 2) continue;
        $out[trim($kv[0])][] = trim($kv[1]);
    }
    return $cache[$config_dir] = $out;
}

// Last value of a key (as the former readers did), or null.
function cms_config_value(string $config_dir, string $key): ?string {
    $all = cms_config_ini($config_dir)[$key] ?? [];
    return $all ? end($all) : null;
}
