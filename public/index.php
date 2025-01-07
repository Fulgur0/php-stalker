<?php

echo $_SERVER['REQUEST_URI'];

if ($_SERVER['REQUEST_URI'] == '/') {
    require __DIR__ . '/../views/index.php';
} else {
    require __DIR__ . '/../views/404.html';
}
