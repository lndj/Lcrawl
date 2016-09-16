<?php
/**
 * Created by PhpStorm.
 * User: ning
 * Date: 16/9/16
 * Time: 上午9:38
 */

namespace Lndj\Supports;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;


class Log
{

    static private $logger;

    /**
     * The log file.
     * @var string
     */
    static public $log_file = '../../Lcrawl.log';


    /**
     * Log constructor.
     */
    public function __construct()
    {
        self::$logger = new Logger('Lcrawl');
    }

    /**
     * Info log.
     * @param $info
     * @param $context
     * @return bool
     */
    public static function info($info, $context)
    {
        self::$logger->pushHandler(new StreamHandler(self::$log_file), Logger::INFO);
        return self::$logger->info($info, $context);
    }

    /**
     * Debug message.
     * @param $message
     * @param $context
     * @return bool
     */
    public static function debug($message, $context)
    {
        self::$logger->pushHandler(new StreamHandler(self::$log_file), Logger::DEBUG);
        return self::$logger->debug($message, $context);

    }

    public static function error($message, $context)
    {
        self::$logger->pushHandler(new StreamHandler(self::$log_file), Logger::ERROR);
        return self::$logger->error($message, $context);
    }

}