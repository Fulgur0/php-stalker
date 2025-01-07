<?php

echo $_SERVER['REQUEST_URI'];

$config = json_decode(file_get_contents(__DIR__ . '/../config.json'), true);

require __DIR__ . '/../views/index.php';
