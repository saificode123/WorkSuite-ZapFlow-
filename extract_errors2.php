<?php
$lines = file('storage/logs/laravel-2026-07-23.log');
$errors = [];
foreach($lines as $line) {
    if (strpos($line, 'local.ERROR') !== false) {
        $errors[] = trim($line);
    }
}
// Get the last 10 errors
$lastErrors = array_slice($errors, -10);
foreach($lastErrors as $err) {
    echo $err . "\n";
}
