<?php

$test = $_GET['test'];





if ($test === 'server-var') {
    echo isset($_SERVER[$_GET['var']]) ? $_SERVER[$_GET['var']] : '';
}

if ($test === 'method') {
    echo $_REQUEST['METHOD'];
}

if ($test === 'cookies') {
    
    ob_start();
    foreach ($_COOKIE as $name => $value) {
        echo rawurlencode($name) . "=" . rawurlencode($value) . '; ';
    }
    echo rtrim(ob_get_clean(), '; ');
}

if ($test === 'headers') {
    echo isset($_SERVER['HTTP_TEST1']) ? $_SERVER['HTTP_TEST1'] . ' ' : '';
    echo isset($_SERVER['HTTP_TEST2']) ? $_SERVER['HTTP_TEST2'] : '';
}

if ($test === 'userAgent') {
    echo isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'null';
}


if ($test === 'followredirects') {
    if (!isset($_GET['end'])) {
        header("Location: /?test={$test}&end=1");
    }
}

if ($test === 'maxRedirects') {
    if (!isset($_GET['redirect']) || $_GET['redirect'] < 10) {
        header("Location: /?test={$test}&redirect=" . ($_GET['redirect'] + 1));
    }
}

if ($test === 'autoReferer') {
    if (!isset($_GET['end'])) {
        header("Location: /?test={$test}&end=1");
    } else {
        echo isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
    }
}