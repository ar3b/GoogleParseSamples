<?php

// vendor autoload
require 'autoload.php';

// parsing libs
use DiDom\Document;
use DiDom\Query;

// log
function l($txt) {
    print htmlspecialchars($txt).PHP_EOL;
}

// log separator
function sep() {
    l(str_repeat("-",20));
}

// uule counting
function __uule($city) {
    $secretkey = array_merge(range('A','Z'), range('a','z'), range('0','9'), array('-', '_'));
    return trim('w+CAIQICI'.$secretkey[strlen($city)%count($secretkey)].base64_encode($city), '=');
}

// settings
$_domain = "google.com.ua";
$_user_agent = "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:48.0) Gecko/20100101 Firefox/48.0";
$_force_pc = true;
$_location = "Kiev,Kyiv city,Ukraine";
$_force_cache = true;

echo "<pre>".PHP_EOL;
l("start");
sep();

$url = "https://www.".$_domain."/search?";

// query params
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
$request = Requests::get($url, $headers);
l("Real url:     ".$request->url);
l("Request code: ".$request->status_code);

if (($request->status_code == 200) AND (!$_force_cache)) {
    file_put_contents(__DIR__ . "/cached/result.html", $request->body);
    $data = $request->body;
} else {
    l("From cache".($_force_cache?" - forced":""));
    $data = file_get_contents(__DIR__ . "/cached/result.html");
}
sep();

// Parsing
$doc = new Document();
$doc->loadHtml($data);

sep();
l("Done");