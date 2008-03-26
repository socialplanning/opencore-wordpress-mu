<?php
// From: http://dev.kanngard.net/Permalinks/ID_20050507183447.html
function selfURL() { 
    $s = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : ""; 
    $protocol = strleft(strtolower($_SERVER["SERVER_PROTOCOL"]), "/").$s; 
    $port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":".$_SERVER["SERVER_PORT"]); 
    return $protocol."://".$_SERVER['SERVER_NAME'].$port.$_SERVER['REQUEST_URI']; 
} 

function strleft($s1, $s2) { 
    return substr($s1, 0, strpos($s1, $s2)); 
}

function send_supervisor_exception($exception) {
    send_supervisor_message($exception->getMessage(),
                            $exception->getTraceAsString()
                            . "\n" . $exception,
                            'error');
}

set_exception_handler('send_supervisor_exception');

// Probably we need this to get all errors:
// http://us2.php.net/manual/en/function.set-error-handler.php#35622

function send_supervisor_error($code, $description, 
                               $err_filename, $err_line, $err_context) {

    $error_code_mapping = array(E_ERROR          => 'Error',
                                E_WARNING        => 'Warning',
                                E_PARSE          => 'Parsing Error',
                                E_NOTICE         => 'Notice',
                                E_CORE_ERROR     => 'Core Error',
                                E_CORE_WARNING   => 'Core Warning',
                                E_COMPILE_ERROR  => 'Compile Error',
                                E_COMPILE_WARNING => 'Compile Warning',
                                E_USER_ERROR     => 'User Error',
                                E_USER_WARNING   => 'User Warning',
                                E_USER_NOTICE    => 'User Notice',
                                E_STRICT         => 'Strict Notice',
                                E_RECOVERABLE_ERROR  => 'Recoverable Error'
                                );
    if ($code != E_ERROR && $code != E_USER_ERROR 
        && $code != E_COMPILE_ERROR) {
        return FALSE;
    }
    $code_msg = $error_code_mapping[$code];
    if (! $code_msg) {
        $code_msg = $code;
    }
    $var_message = "";
    if ($err_context) {
        $var_message .= "\n\nVariables:\n";
        foreach ($err_context as $key => $value) {
            $var_message .= "$key = ".print_r($value, TRUE)."\n";
        }
    }
    $sig_parts = "$code:$description:$err_filename:$err_line";
    $sig = hash("md5", $sig_parts);
    send_supervisor_message("$code_msg: $description " . E_WARNING,
                            "There was an error in $err_filename:$err_line\n"
                            . $description
                            . $var_message,
                            'error',
                            $sig);
    // FIXME: not sure if it should return FALSE (do normal handling)
    // or TRUE (don't do any more handling):
    return FALSE;
}

set_error_handler('send_supervisor_error');

function send_supervisor_warning($title, $message) {
    send_supervisor_message("Warning: ".$title, $message, 'warning');
}

function send_supervisor_message($title, $message, $type, $traceback_signature="") {
    // FIXME: need better title
    if ($traceback_signature) {
        $traceback_signature = "Traceback-Signature: $traceback_signature\n";
    } else {
        $traceback_signature = '';
    }
    error_log("<!--XSUPERVISOR:BEGIN-->"
              . "Log-Type: $type\n"
              . "Content-type: text/plain\n"
              . "Request-URL: " . selfURL() . "\n"
              . "Title: $title\n"
              . $traceback_signature
              . "\n"
              . $message
              . "<!--XSUPERVISOR:END-->\n",
              3, "php://stdout");
}

// end TOPP
