<?php

require 'autoload.php';
use DiDom\Document;

function l($txt) {
    print htmlspecialchars($txt).PHP_EOL;
}

function sep() {
    l(str_repeat("-",20));
}

function __uule($city) {
    $secretkey = array_merge(range('A','Z'), range('a','z'), range('0','9'), array('-', '_'));
    return trim('w+CAIQICI'.$secretkey[strlen($city)%count($secretkey)].base64_encode($city), '=');
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//  --- SETTINGS
$_domain = "google.com.ua";
$_user_agent = "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:48.0) Gecko/20100101 Firefox/48.0";
$_force_pc = true;
$_location = "Kiev,Kyiv city,Ukraine";
$_force_cache = false;

echo "<pre>".PHP_EOL;
l("start");

$url = "https://www.".$_domain."/search?";

$params = array(
    "q" => "купить футболку",
    "adtest" => "on",
    "start" => "0",
    "num" => "50",
    "noj" => "1",
    "uule" => __uule($_location),
);

if ($_force_pc) {
    $params = array_merge(
        $params,
        array(
            "nomo" => "1",
            "nota" => "1",
        )
    );
}

$params_array = array();
foreach ($params as $key=>$value) {
    $params_array[] = $key."=".urlencode($value);
}
$url .= implode("&", $params_array);

l("Expected url: ".$url);

$headers = array(
    "Accept" => "text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
    "Accept-Encoding" => "gzip, deflate",
    "Referer" => $url,
    "Connection" => "keep-alive",
    "User-Agent" => $_user_agent,
);
$request = Requests::get($url,$headers);
l("Real url:     ".$request->url);
l("Request code: ".$request->status_code);

if (($request->status_code == 200) AND (!$_force_cache)) {
    file_put_contents(__DIR__. "/cached/result.html", $request->body);
} else {
    l("From cache");
    $data = file_get_contents("/cached/result.html");
}

sep();
l("Done");