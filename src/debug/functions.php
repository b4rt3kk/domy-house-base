<?php
function diee() {
    ini_set("display_errors", "1");
    error_reporting(E_ALL);
    $args = func_get_args();
    
    $memoryUsage = 0;
    
    echo '<p style="margin: 10px;"><strong>ARGUMENTS</strong></p>';
    
    echo '<pre style="margin: 10px; padding: 10px; background: #f8f9fa; color: #343a40; word-wrap: break-word; white-space: pre-wrap;">';
    
    foreach ($args as $arg) {
        $startMemory = memory_get_usage();
        
        var_dump($arg);
        
        $memoryUsage += memory_get_usage() - $startMemory;
    }
    
    echo '</pre>';
    
    $backtrace = debug_backtrace();
    
    echo '<p style="margin: 10px; padding: 10px; background-color: #dc3545; color: white;"><strong>DIEE called in ' . $backtrace[0]['file'] . ' [line: ' . $backtrace[0]['line'] . '] </strong></p>';
    
    echo '<p style="margin: 10px; padding: 10px; background-color: #0dcaf0; color: white;"><strong>Total memory usage: ' . round($memoryUsage / 1024) . ' KB </strong></p>';
    
    echo '<pre style="margin: 10px; padding: 10px; background: #f8f9fa; color: #343a40; word-wrap: break-word; white-space: pre-wrap;">';
    
    foreach ($backtrace as $row) {
        echo '<p>' . $row['file'] . ' [line: ' . $row['line'] . '] </p>';
    }
    
    echo '</pre>';
    
    exit;
}

function dumpp() {
     $args = func_get_args();

    foreach ($args as $arg) {
        echo '<pre>DUMPP<br/>';
        var_dump($arg);
        echo '</pre>';
    }
    /*
    $backtrace = debug_backtrace();

    echo '<p><strong>DUMPP called in ' . $backtrace[0]['file'] . ' [line: ' . $backtrace[0]['line'] . '] </strong></p>';

    echo '<pre>';

    foreach ($backtrace as $row) {
        echo '<p>' . $row['file'] . ' [line: ' . $row['line'] . '] </p>';
    }

    echo '</pre>';
     * 
     */
}
