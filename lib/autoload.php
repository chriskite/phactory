<?php
spl_autoload_register(function($className) {
    if (strpos($className, 'Phactory\\') === 0) {
        require_once __DIR__ . '/' . str_replace('\\', '/', $className) . '.php';
    }
});