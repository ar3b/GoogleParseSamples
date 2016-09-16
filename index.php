<?php

$time_start = microtime(true);

// vendor autoload
require 'autoload.php';

// parsing libs
use DiDom\Document;
use DiDom\Query;

// log
function l($txt=" ") {
    print htmlspecialchars($txt).PHP_EOL;
}
function r($key, $value) {
    l("\t".$key.":\n \t\t".$value);
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

header('Content-Type: text/html; charset=utf-8');
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

// Search for ADS blocks
$ads_data = $doc->find("//li[@class='ads-ad']", Query::TYPE_XPATH);

// Processing every block
foreach ($ads_data as $key=>$ads) {

    // Searching for title
    $title = $ads->find("//h3/a[@data-preconnect-urls]", Query::TYPE_XPATH)[0];
    r("Title", $title->text());

    // Searching for urls
    $urls = explode(",",$title->attr('data-preconnect-urls'));
    $href = $title->attr("href");
    if ((!is_null($href)) and ($href!=false) and ($href!="#") and (!in_array($href, $urls))) {
        array_unshift($urls, $href);
    }
    r("Urls",implode("\n \t\t", $urls));

    // Searching for descr
    $descr = $ads->find("//div[starts-with(@class, 'ellip')]", Query::TYPE_XPATH);
    $description = "";
    foreach ($descr as $d) {
        $description .= $d->text()." ";
    }
    r("Description", trim($description));

    // Searching for phone
    $phone = $ads->find("//span[@class='_r2b']", Query::TYPE_XPATH);
    if (count($phone)!=0) {
        r("Phone", $phone[0]->text());
    }

    // Searching for address
    $addr = $ads->find("//div[contains(@class, '_wnd') and contains(@class, 'ellip')]/div[@class='_H2b']/a[@class='_vnd']", Query::TYPE_XPATH);
    if (count($addr)!=0) {
        r("Address", $addr[0]->text());
    }

    $work_times = $ads->find("//div[contains(@class, '_wnd') and contains(@class, 'ellip')]/div[@class='_H2b']/div[@class='_K2b']/span/g-bubble/div//table[@role='presentation']//tr", Query::TYPE_XPATH);
    if (count($work_times)!=0) {
        $time_string = array();
        foreach ($work_times as $time_item) {
            $day = $time_item->find("//td[not(@class)]/div", Query::TYPE_XPATH)[0];
            $time = $time_item->find("//td[@class]/div", Query::TYPE_XPATH)[0];
            $time_string[] = $day->text().": ".$time->text();
        }
        r("Working time",implode("\n \t\t", $time_string));
    }

    sep();
}
$time_end = microtime(true);

l();l();
l("Done in ".($time_end - $time_start)." sec");