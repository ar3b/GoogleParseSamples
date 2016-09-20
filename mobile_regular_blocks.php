<?php

// parsing libs
use DiDom\Document;

// vendor autoload
require_once 'autoload.php';
require_once 'libs/libs.php';

// settings
$_domain = "google.com.ua";
$_user_agent = "Mozilla/5.0 (iPhone; U; CPU iPhone OS 5_1_1 like Mac OS X; en) AppleWebKit/534.46.0 (KHTML, like Gecko) CriOS/19.0.1084.60 Mobile/9B206 Safari/7534.48.3";
$_force_pc = false;
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
l("Response code: ".$request->status_code);

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

// Search for ADS blocks
$data = $doc->xpath("//div[@class='srg']/div[@class='mnr-c']/div[contains(@class, 'g')]/div[@class='rc']");

// Processing every block
foreach ($data as $key=>$ads) {

    // Searching for title
    $title = $ads->xpath("//h3/a")[0];
    r("Title", $title->text());

    // Searching for urls
    $url = $ads->xpath("//div/div/cite")[0]->text();
    $url = str_replace(" › ","/", $url);
    if (!preg_match('/\.\.\./', $url)) {
        r("Url", "http://" . $url);
    }

    $description = $ads->xpath("//div/div/span[@class='st']")[0];
    r("Description", trim($description->text()));

    sep();
    continue;


    // Testing description have phone
    $umg = $ads->xpath("//div[@class='_Umg']");
    $have_phone = count($umg)!=0;
    $phone = null;

    // Searching for descr
    $descr = $ads->xpath("//div[contains(@class, 'ads-creative') or starts-with(@class, 'ellip')]");
    $description = "";
    foreach ($descr as $number=>$d) {
        if (($have_phone) and ($number==(count($descr)-1))) {
            $phone = trim(explode(":", $d->text())[1]);
        } else {
            $description .= $d->text() . " ";
        }
    }
    r("Description", trim($description));

    if (($have_phone) and !is_null($phone)) {
        r("Phone", $phone);
    }

    // Searching for address
    $addr = $ads->xpath("//div[@class='_pEc']/div/span/a/span[contains(@class,'ellip')]/span");
    if (count($addr)!=0) {
        r("Address", $addr[0]->text());
    }

}