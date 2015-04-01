<?php

// This is going to be under output/test
require __DIR__ . '/../../bootstrap.php';
require __DIR__ . '/config.php';

$board = new MyBoard\Board($config);
$board->execute();

