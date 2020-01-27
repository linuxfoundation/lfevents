<?php
function shortpixel_process_error_backtrace($errno, $errstr, $errfile, $errline, $errcontext) {
    if(!(error_reporting() & $errno))
        return;
    switch($errno) {
    case E_WARNING      :
    case E_USER_WARNING :
    case E_STRICT       :
    case E_NOTICE       :
    case E_USER_NOTICE  :
        $type = 'warning';
        $fatal = false;
        break;
    default             :
        $type = 'fatal error';
        $fatal = true;
        break;
    }
    $trace = array_reverse(debug_backtrace());
    array_pop($trace);
    if(php_sapi_name() == 'cli') {
        echo 'Backtrace from ' . $type . ' \'' . $errstr . '\' at ' . $errfile . ' ' . $errline . ':' . "\n";
        foreach($trace as $item)
            echo '  ' . (isset($item['file']) ? $item['file'] : '<unknown file>') . ' ' . (isset($item['line']) ? $item['line'] : '<unknown line>') . ' calling ' . $item['function'] . '()' . "\n";
    } else {
        echo '<p class="error_backtrace">' . "\n";
        echo '  Backtrace from ' . $type . ' \'' . $errstr . '\' at ' . $errfile . ' ' . $errline . ':' . "\n";
        echo '  <ol>' . "\n";
        foreach($trace as $item)
            echo '    <li>' . (isset($item['file']) ? $item['file'] : '<unknown file>') . ' ' . (isset($item['line']) ? $item['line'] : '<unknown line>') . ' calling ' . $item['function'] . '()</li>' . "\n";
        echo '  </ol>' . "\n";
        echo '</p>' . "\n";
    }
    if(ini_get('log_errors')) {
        $items = array();
        foreach($trace as $item)
            $items[] = (isset($item['file']) ? $item['file'] : '<unknown file>') . ' ' . (isset($item['line']) ? $item['line'] : '<unknown line>') . ' calling ' . $item['function'] . '()';
        $message = 'Backtrace from ' . $type . ' \'' . $errstr . '\' at ' . $errfile . ' ' . $errline . ': ' . join(' | ', $items);
        error_log($message);
    }
    if($fatal)
        exit(1);
}

if(WP_DEBUG === true) {
    set_error_handler('shortpixel_process_error_backtrace');
}
