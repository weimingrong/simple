<?php

final class Bootstrap
{
    public function errorInit()
    {
        error_reporting(E_ALL & ~E_NOTICE);
    }

    public function timeZoneInit()
    {
        date_default_timezone_set("Asia/Shanghai");
    }

    public function pathInit()
    {
        define('MAIN_PATH', dirname(dirname(__FILE__)));
        define('LOG_PATH', MAIN_PATH . '/runtime/log');
    }

    public function logInit()
    {
        Log::setLogDir(LOG_PATH);
    }

    public function cgiInit()
    {
        if (false === strpos(php_sapi_name(), 'cgi')) {
            return;
        }

        if(isset($_COOKIE['PHPSESSID'])) {
            session_id($_COOKIE['PHPSESSID']);
        }
        session_start();
    }
}