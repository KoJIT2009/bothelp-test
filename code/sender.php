<?php

require __DIR__ . '/vendor/autoload.php';

$main = new \App\Main();
$main->generate();

echo ' [x] Sent ' . "\n";
