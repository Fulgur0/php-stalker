<?php

echo $_SERVER['REQUEST_URI'];

$config = json_decode(file_get_contents(__DIR__ . '/../config.json'), true);

if ($_SERVER['REQUEST_URI'] == '/') {
    require __DIR__ . '/../views/index.php';
} else {
    require __DIR__ . '/../views/404.html';
}
