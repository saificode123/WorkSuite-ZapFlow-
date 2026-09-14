<?php
$lines = file('storage/logs/laravel-2026-07-23.log');
$errors = [];
foreach($lines as $i => $line) {
    if (strpos($line, 'local.ERROR') !== false) {
        $errors[] = $line . (isset($lines[$i+1]) ? $lines[$i+1] : '') . (isset($lines[$i+2]) ? $lines[$i+2] : '');
    }
}
echo implode("\n---\n", array_slice($errors, -5));
