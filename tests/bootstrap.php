<?php

require_once('./src/Object.php');

foreach (glob('./src/*.php') as $file) {
    require_once($file);
}
