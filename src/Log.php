<?php
declare(strict_types = 1);
/**
 * Created by PhpStorm.
 * User: XYQ
 * Date: 2020-03-24
 * Time: 10:53
 */

namespace xyqWeb\log;


use xyqWeb\log\drivers\LogException;

class Log
{
    /**
     * @var \xyqWeb\log\drivers\LogStrategy
     */
    protected $driver;

    /**
     * Log constructor.
     * @param array $config
     * @throws LogException
     */
    public function __construct(array $config)
    {
        if (!isset($config['driver']) || !in_array($config['driver'], ['ssdb', 'file'])) {
            throw new LogException('log driver error');
        }
        $driver = "\\xyqWeb\\log\\drivers\\" . ucfirst($config['driver']);
        unset($config['driver']);
        $this->driver = new $driver($config);
    }

    /**
     * 写入日志
     *
     * @author xyq
     * @param string $logName 日志名称
     * @param string|array|object $logContent 日志内容
     * @param string $charList 日志分割符
     * @param int $jsonFormatCode json格式化的code
     * @return bool
     */
    public function write(string $logName, $logContent, string $charList = "\n", int $jsonFormatCode = JSON_UNESCAPED_UNICODE) : bool
    {
        return $this->driver->write($logName, $logContent, $charList, $jsonFormatCode);
    }
}