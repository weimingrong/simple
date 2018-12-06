<?php

final class Application
{
    /**
     * @var Application
     */
    private static $instance;
    /**
     * @var string
     */
    private static $path;

    private function __construct()
    {
        if (!self::$path) {
            self::$path = dirname(__FILE__);
        }

        spl_autoload_register([self::class, 'autoload']);
        set_exception_handler([self::class, 'exceptionHandler']);
    }

    public static function getInstance()
    {
        if (!(self::$instance instanceof self)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function execute()
    {
        $this->bootstrap()->dispatch();
    }

    public function bootstrap()
    {
        if (class_exists('Bootstrap')) {
            $bootstrap = new Bootstrap();
            $methods = get_class_methods($bootstrap);

            foreach ($methods as $method) {
                if (strpos($method, 'Init')) {
                    $bootstrap->$method();
                }
            }
        }
        return $this;
    }

    public function dispatch()
    {
        $ctl = ucfirst(strtolower(($_GET['c'] ?: 'index'))) . 'Controller';
        $act = strtolower(($_GET['a'] ?: 'index')) . 'Action';

        if (class_exists($ctl) && ($ctl = new $ctl()) && is_callable([$ctl, $act])) {
            call_user_func([$ctl, $act], $_POST);
        } else {
            throw new RuntimeException('404 Not Found');
        }
    }

    public static function autoload($className)
    {
        $file = self::$path . '/' . $className . '.php';
        if (is_readable($file)) {
            require_once $file;
        }
    }

    public static function exceptionHandler(Throwable $e)
    {
        Log::exception($e);

        ob_clean();

        $result = [
            'code' => $e->getCode() ?: 10,
            'message' => $e->getMessage() ?: 'Unknown Error',
            'logId' => Log::getId(),
        ];
        if (in_array(get_class($e), [RuntimeException::class, BadMethodCallException::class])) {
            $result['code'] = '1';
            $result['message'] = 'System Error';
        }
        exit(json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }
}
