<?php
require 'vendor/autoload.php';

$dotenv = new Symfony\Component\Dotenv\Dotenv();
$dotenv->bootEnv(__DIR__.'/.env');

echo 'DATABASE_URL = ' . getenv('DATABASE_URL') . PHP_EOL;
