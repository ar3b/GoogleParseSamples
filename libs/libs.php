<?php

// log
function l($txt=" ") {
    print htmlspecialchars($txt).PHP_EOL;
}
function r($key, $value) {
    l("\t".$key.":\n \t\t".$value);
}

// log separator
function sep() {
    l(str_repeat("-",80));
}

// uule counting
function __uule($city) {
    $secretkey = array_merge(range('A','Z'), range('a','z'), range('0','9'), array('-', '_'));
    return trim('w+CAIQICI'.$secretkey[strlen($city)%count($secretkey)].base64_encode($city), '=');
}
