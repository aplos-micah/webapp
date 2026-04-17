#!/usr/bin/env php
<?php

/**
 * CLI migration runner.
 * Usage: php migrate.php [--dry-run]
 */

if (php_sapi_name() !== 'cli') {
    echo "This script must be run from the command line.\n";
    exit(1);
}

$dryRun = in_array('--dry-run', $argv ?? [], true);

require_once __DIR__ . '/Application/Env.php';
Env::load(__DIR__ . '/configuration/.env');

require_once __DIR__ . '/Application/Logger.php';
require_once __DIR__ . '/configuration/config.php';
require_once __DIR__ . '/Application/DB.php';
require_once __DIR__ . '/Application/Migrations/Runner.php';

$runner = new Runner(new DB());
$runner->ensureTable();

$pending = $runner->getPending();

if (empty($pending)) {
    echo "No pending migrations.\n";
    exit(0);
}

echo count($pending) . " pending migration(s):\n";
foreach ($pending as $file) {
    echo "  - {$file}\n";
}
echo "\n";

if ($dryRun) {
    echo "--dry-run: no migrations were run.\n";
    exit(0);
}

$results = $runner->runAll('cli');

$failed = false;
foreach ($results as $r) {
    $icon = $r['ok'] ? '[OK]  ' : '[FAIL]';
    echo "{$icon} {$r['file']}";
    if (!$r['ok']) {
        echo "\n       Error: {$r['error']}";
        $failed = true;
    }
    echo "\n";
}

echo "\n";
if ($failed) {
    echo "Migration run completed with errors. Stopped at first failure.\n";
    exit(1);
}

echo "All migrations applied successfully.\n";
exit(0);
