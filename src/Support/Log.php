<?php
/**
 * Created by PhpStorm.
 * User: Ning Luo
 * Date: 16/8/21
 * Time: 上午12:51
 */

namespace Lndj\Support;

use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\NullHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

class Log
{
    /**
     * Logger instanes.
     * @var
     */
    protected static $logger;

    /**
     * Get the logger instanes.
     * @return Monolog\Logger
     */
    public static function getLogger()
    {
        return self::$logger ?: self::$logger = self::createDefaultLogger();
    }

    /**
     * Set Logger.
     * @param LoggerInterface $logger
     */
    public static function setLogger(LoggerInterface $logger)
    {
        self::$logger = $logger;
    }

    /**
     * Has the logger exists.
     * @return bool
     */
    public static function hasLogger()
    {
        return self::$logger ? true : false;
    }

    /**
     * Create a default Logger.
     * @return Monolog\Logger
     */
    private static function createDefaultLogger()
    {
        $log = new Logger('Lcrawl');

        if (defined('PHPUNIT_RUNNING')) {
            $log->pushHandler(new NullHandler());
        } else {
            $log->pushHandler(new ErrorLogHandler());
        }

        return $log;
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    function __call($name, $arguments)
    {
        return call_user_func_array([self::getLogger(), $name], $arguments);
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        return forward_static_call_array([self::getLogger(), $name], $arguments);
    }


}
