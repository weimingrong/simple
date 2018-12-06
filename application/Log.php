<?php

class Log
{
    private static $logDir;

    public static function debug($content)
    {
        self::record($content, 'debug');
    }

    public static function exception(Throwable $e)
    {
        self::record([
            'code' => $e->getCode(),
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => array_slice($e->getTrace(), 0, 3),
        ], strtolower(get_class($e)));
    }

    public static function record($content, $type = 'default')
    {
        $data = [
            'id'      => self::getId(),
            'date'    => date('Y-m-d H:i:s'),
            'content' => $content
        ];

        $data = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n";

        self::writeFile($data, $type);
    }

    public static function writeFile($content, $filename)
    {
        $content  = (string) $content;
        $filename = self::getLogDir() . date('/Ym/d/') . $filename .'.log';

        (!file_exists($filename)) && ($path = dirname($filename)) && (!is_dir($path)) && (mkdir($path, 0770, true));

        file_put_contents($filename, $content, FILE_APPEND);
    }

    public static function getId()
    {
        if (!defined('LOG_ID')) {
            $arr = gettimeofday();
            $logId = ((($arr['sec']*100000 + $arr['usec']/10) & 0x7FFFFFFF) | 0x80000000);
            define('LOG_ID', $logId);
        }
        return LOG_ID;
    }

    public static function getLogDir()
    {
        return self::$logDir ?: (dirname(__FILE__) . '/log');
    }

    public static function setLogDir($logDir)
    {
        self::$logDir = $logDir;
    }
}