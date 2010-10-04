<?php

class Phactory_Logger {

    const LEVEL_DEBUG = 1;
    const LEVEL_WARN = 2;
    const LEVEL_ERROR = 4;
    const LEVEL_FATAL = 8;
    const LEVEL_ALL = 15;

    protected static $_level = self::LEVEL_ALL;

    protected static $_level_strs = array(self::LEVEL_DEBUG => 'DEBUG',
                                          self::LEVEL_WARN  => 'WARN',
                                          self::LEVEL_ERROR => 'ERROR',
                                          self::LEVEL_FATAL => 'FATAL');

    public static function setLogLevel($level) {
        self::$_level = $level;
    }

    public static function debug($msg, $backtrace = false) {
       self::outputMessage(self::LEVEL_DEBUG, $msg, $backtrace);
    }

    public static function warn($msg, $backtrace = false) {
       self::outputMessage(self::LEVEL_WARN, $msg, $backtrace);
    }

    public static function error($msg, $backtrace = false) {
       self::outputMessage(self::LEVEL_ERROR, $msg, $backtrace);
       throw new Exception();
    }

    public static function fatal($msg, $backtrace = true) {
       self::outputMessage(self::LEVEL_FATAL, $msg, $backtrace);
       die;
    }

    protected static function outputMessage($log_level, $msg, $backtrace) {
        if(self::$_level & $log_level) {
            print(strftime("%Y-%m-%d %H:%M:%S Phactory ") . self::$_level_strs[$log_level] . ' - ' . $msg . "\n");

            if($backtrace) {
                debug_print_backtrace();
            }
        }
    }

}
