<?php

$time_start = microtime(true);
require_once 'table_regular_blocks.php';

$time_end = microtime(true);
l();l();
l("Done in ".($time_end - $time_start)." sec");