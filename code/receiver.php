<?php

require __DIR__ . '/vendor/autoload.php';

$main = new \App\Main();

$queue = new \App\Queue();
$queue->consumeMessages([$main, 'consume']);
