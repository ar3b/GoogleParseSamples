<?php

use DiDom\Document;

#  --- SETTINGS
$_domain = "google.com.ua";
$_user_agent = "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:48.0) Gecko/20100101 Firefox/48.0";
$_force_pc = true;
$_location = "Kiev,Kyiv city,Ukraine";
$_force_cache = true;

function __uule($city) {
    $secretkey = array_merge(range('A','Z'), range('a','z'), range('0','9'), array('-', '_'));
    return trim('w+CAIQICI'.$secretkey[strlen($city)%count($secretkey)].base64_encode($city), '=');
}

echo "<pre>";
echo "Done";

$url = "https://www.".$_domain."/";

$headers = array(
    "Accept" => "text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
    "Accept-Encoding" => "gzip, deflate",
    "Referer" => $url,
    "Connection" => "keep-alive",
    "User-Agent" => $_user_agent,
);

$params = array(
    "q" => "купить футболку",
    "adtest" => "on",
    "start" => "0",
    "num" => "50",
    "noj" => "1",
    "uule" => __uule($_location),
);