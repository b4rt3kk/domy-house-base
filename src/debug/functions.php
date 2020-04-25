<?php
function diee() {
    $args = func_get_args();
    
    foreach ($args as $arg) {
        echo '<pre>';
        var_dump($arg);
        echo '</pre>';
    }
    
    $backtrace = debug_backtrace();
    
    echo '<p><strong>DIEE called in ' . $backtrace[0]['file'] . ' [line: ' . $backtrace[0]['line'] . '] </strong></p>';
    exit;
}
