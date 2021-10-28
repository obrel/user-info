<?php

require 'vendor/autoload.php';

use GeoIp2\Database\Reader;
use DeviceDetector\DeviceDetector;

// Get user IP
function getUserIP() {
  if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER) && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    if (strpos($_SERVER['HTTP_X_FORWARDED_FOR'], ',') > 0) {
      $addr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
      return trim($addr[0]);
    } else {
      return $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
  } else {
    return $_SERVER['REMOTE_ADDR'];
  }
}

// Get route path
$route = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

$route = substr($route, 1);
$route = explode("?", $route);
$route = explode("/", $route[0]);

// We only use root path, otherwise return 404
if (count($route) <= 1) {
  $userAgent = $_SERVER['HTTP_USER_AGENT'];
  $info = new DeviceDetector($userAgent);
  $info->parse();

  $reader = new Reader('./db/GeoLite2-Country.mmdb');
  $ip = getUserIP();
  $geoip = $reader->country($ip);

  $response = array(
    'ip' => $ip,
    'country' => $geoip->country->name,
    'country_code' => $geoip->country->isoCode,
    'os' => $info->getOs('name'),
    'device' => $info->getDeviceName(),
    'browser' => $info->getClient('name'),
    'browser_version' => $info->getClient('version'),
  );

  header('HTTP/1.1 200 OK');
  echo json_encode($response, JSON_UNESCAPED_SLASHES);
} else {
  header('HTTP/1.1 404 Not Found');
}

?>
