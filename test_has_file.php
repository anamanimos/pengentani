<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$req = new Illuminate\Http\Request();
$req->files->set('photos', [new Illuminate\Http\UploadedFile(__FILE__, 'test.php')]);
var_dump($req->hasFile('photos'));
