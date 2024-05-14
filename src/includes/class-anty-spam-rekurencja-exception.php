<?php

class Anty_Spam_Rekurencja_Exception extends Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->logError();
    }
    
    private function logError()
    {
        $log_message = sprintf("[%s] Error: %s in %s on line %d\nStack trace:\n%s\n\n",
            date("Y-m-d H:i:s"),
            $this->getMessage(),
            $this->getFile(),
            $this->getLine(),
            $this->getTraceAsString()
        );
        error_log($log_message, 3, plugin_dir_path(__FILE__) . 'logs/error_log.txt');
    }
}
