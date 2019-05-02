<?php

error_reporting(E_ALL);
ini_set('display_errors', 'On');

if (isset($_SERVER['HTTP_ORIGIN'])) {
  header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
  header('Access-Control-Allow-Credentials: true');
  header('Access-Control-Max-Age: 86400');
}

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
  if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
  if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
    header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
  exit(0);
}

$password = trim(file_get_contents(__DIR__ . '/../password'));
if($_REQUEST['password'] != $password) {
  print 'Invalid password';
  exit;
}

require __DIR__ . '/../vendor/autoload.php';

use Mike42\Escpos\EscposImage;
use Mike42\Escpos\PrintConnectors\CupsPrintConnector;
use Mike42\Escpos\Printer;

$tmp_png = tempnam(sys_get_temp_dir(), '');
rename($tmp_png, $tmp_png .= '.png');
file_put_contents($tmp_png, base64_decode(explode(',', $_REQUEST['base64'])[1]));

$printer = new Printer(new CupsPrintConnector($_REQUEST['printer']));
$image = EscposImage::load($tmp_png);
$printer->bitImage($image);
$printer->cut();
$printer->close();

unlink($tmp_png);

print '1';
