<?php
require 'vendor/autoload.php';

$c = new App\Http\Controllers\ClientFullImportController();
$m = new ReflectionMethod($c, 'normalizeHeader');
$m->setAccessible(true);
$headers = ['Nombre empresa Persona', 'Tipo Identificación', 'Identificación'];
foreach ($headers as $header) {
    $text = (string) $header;
    if (function_exists('iconv')) {
        $text = @iconv('UTF-8', 'ASCII//TRANSLIT', $text);
    }
    $text = str_replace(['|', '/', '\\', '-', '(', ')', '[', ']', '{', '}', ':', ';', '.', ','], ' ', $text);
    $text = strtolower(trim(preg_replace('/[^a-z0-9]+/', ' ', $text)));
    $text = preg_replace('/\s+/', ' ', $text);
    $text = trim($text);
    $text = preg_replace('/\b([a-z]+)s\b/', '$1', $text);
    echo $header . ' => ' . $text . PHP_EOL;
    echo 'method => ' . $m->invoke($c, $header) . PHP_EOL;
}
