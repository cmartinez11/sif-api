<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$names = \App\Models\Plantilla::all()->pluck('nombre')->toArray();
echo implode(", ", $names) . "\n";
