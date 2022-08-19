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
    
    echo '<pre>';
    
    foreach ($backtrace as $row) {
        echo '<p>' . $row['file'] . ' [line: ' . $row['line'] . '] </p>';
    }
    
    echo '</pre>';
    
    exit;
}

function dumpp() {
     $args = func_get_args();

    foreach ($args as $arg) {
        echo '<pre>';
        var_dump($arg);
        echo '</pre>';
    }
    
    $backtrace = debug_backtrace();

    echo '<p><strong>DUMPP called in ' . $backtrace[0]['file'] . ' [line: ' . $backtrace[0]['line'] . '] </strong></p>';

    echo '<pre>';

    foreach ($backtrace as $row) {
        echo '<p>' . $row['file'] . ' [line: ' . $row['line'] . '] </p>';
    }

    echo '</pre>';
}
