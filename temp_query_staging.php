<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$rows = DB::table('excel_import_staging')->count();
$last = DB::table('excel_import_staging')->latest('id')->first();

echo 'count=' . $rows . PHP_EOL;
if ($last) {
    echo json_encode($last, JSON_UNESCAPED_UNICODE) . PHP_EOL;
}
