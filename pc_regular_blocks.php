<?php

// parsing libs
use DiDom\Document;

// vendor autoload
require_once 'autoload.php';
require_once 'libs/libs.php';

// settings
$_domain = "google.com.ua";
$_user_agent = "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:48.0) Gecko/20100101 Firefox/48.0";
$_force_pc = true;
$_location = "Kiev,Kyiv city,Ukraine";
$_force_cache = true;
$_query = "купить футболку";

$url = "https://www.".$_domain."/search?";

header('Content-Type: text/html; charset=utf-8');
echo "<pre>".PHP_EOL;
l("start");
sep();

// query params
$params = array(
    "q" => $_query,
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

// Searching for regular result items
$reg_blocks = $doc->xpath("//div[@id='search']//div[@id='ires']//div[@class='g']");
foreach ($reg_blocks as $block) {
    $title = $block->xpath("//h3/a")[0];
    r("Title", $title->text());

    $urls = $block->xpath("//div[@class='s']//div[contains(@class, 'f') and contains(@class, 'kv')]/cite[(@class='_Rm')]");
    if ((count($urls)>0) and (!preg_match('/\.\.\./', $urls[0]->text()))) {
        r("Url", "http://".$urls[0]->text());
    }  else {
        r("Url", "None");
    }

    $descr = $block->xpath("//div[@class='s']//span[@class='st']");
    r("Descr", $descr[0]->text());

    sep();
}