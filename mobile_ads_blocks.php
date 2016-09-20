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
l("REGULAR BLOCKS");
sep();

// Parsing
$doc = new Document();
$doc->loadHtml($data);

// Search for ADS blocks
$ads_data = $doc->xpath("//li[@class='ads-ad']");

// Processing every block
foreach ($ads_data as $key=>$ads) {

    // Searching for title
    $title = $ads->xpath("//h3/a[@data-preconnect-urls]")[0];
    r("Title", $title->text());

    // Searching for urls
    $urls = explode(",",$title->attr('data-preconnect-urls'));
    $href = $title->attr("href");
    if ((!is_null($href)) and ($href!=false) and ($href!="#") and (!in_array($href, $urls))) {
        array_unshift($urls, $href);
    }
    r("Urls",implode("\n \t\t", $urls));

    // Searching for descr
    $descr = $ads->xpath("//div[starts-with(@class, 'ellip')]");
    $description = "";
    foreach ($descr as $d) {
        $description .= $d->text()." ";
    }
    r("Description", trim($description));

    // Searching for phone
    $phone = $ads->xpath("//span[@class='_r2b']");
    if (count($phone)!=0) {
        r("Phone", $phone[0]->text());
    }

    // Searching for address
    $addr = $ads->xpath("//div[contains(@class, '_wnd') and contains(@class, 'ellip')]/div[@class='_H2b']/a[@class='_vnd']");
    if (count($addr)!=0) {
        r("Address", $addr[0]->text());
    }

    // Searching for working time
    $work_times = $ads->xpath("//div[contains(@class, '_wnd') and contains(@class, 'ellip')]/div[@class='_H2b']/div[@class='_K2b']/span/g-bubble/div//table[@role='presentation']//tr");
    if (count($work_times)!=0) {
        $time_string = array();
        foreach ($work_times as $time_item) {
            $day = $time_item->xpath("//td[not(@class)]/div")[0];
            $time = $time_item->xpath("//td[@class]/div")[0];
            $time_string[] = $day->text().": ".$time->text();
        }
        r("Working time",implode("\n \t\t", $time_string));
    }

    sep();
}