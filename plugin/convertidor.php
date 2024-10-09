<?php

$config = require_once('config2.php');

$phar = new Phar($config['output']);
foreach ($config['files'] as $file) {
    $phar->addFile($file);
}
