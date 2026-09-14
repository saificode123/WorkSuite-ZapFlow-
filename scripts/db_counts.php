<?php
$tables = ['booking_groups', 'passengers', 'vouchers', 'travel_payments'];
$databases = ['zapflow_travel', 'zapflow_travel_testing'];

foreach ($databases as $db) {
    echo "=== $db ===\n";
    try {
        $pdo = new PDO("mysql:host=127.0.0.1;dbname=$db", 'root', '');
        foreach ($tables as $t) {
            $c = $pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
            echo "$t: $c\n";
        }
    } catch (Exception $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
    }
    echo "\n";
}
