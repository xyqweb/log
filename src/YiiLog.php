<?php
declare(strict_types = 1);
/**
 * Created by PhpStorm.
 * User: XYQ
 * Date: 2020-03-25
 * Time: 15:08
 */

namespace xyqWeb\log;

use xyqWeb\log\drivers\LogException;
use xyqWeb\log\drivers\LogStrategy;
use yii\base\Component;
use Yii;

class YiiLog extends Component
{
    /**
     * @var \xyqWeb\log\drivers\LogStrategy
     */
    private static $driver;
    /**
     * @var array 配置内容
     */
    public $config = [];

    /**
     * 初始化驱动
     *
     * @author xyq
     * @param array $config
     * @throws LogException
     */
    public static function initDriver(array $config)
    {
        if (is_null(self::$driver)) {
            if (!isset($config['driver']) || !in_array($config['driver'], ['ssdb', 'file'])) {
                throw new LogException('log driver error');
            }
            try {
                if (is_int(strpos($config['path'], '@'))) {
                    $config['path'] = Yii::getAlias($config['path']);
                }
                $driver = "\\xyqWeb\\log\\drivers\\" . ucfirst($config['driver']);
                self::$driver = new $driver($config);
            } catch (\Exception $e) {
                self::$driver = null;
            }
        }
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
        try {
            if (!(self::$driver instanceof LogStrategy) || self::$driver->closed()) {
                self::initDriver($this->config);
            }
            return self::$driver->write($logName, $logContent, $charList, $jsonFormatCode, date('Y-m-d H:i:s'));
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 获取临时存储的文件
     *
     * @author xyq
     * @param int|null $port
     * @param string|null $key
     * @param int $size
     * @return bool
     * @throws LogException
     */
    public function get(int $port = null, string $key = null, int $size = 1)
    {
        if (!is_null($port) && $port > 0) {
            $this->config['port'] = $port;
        }
        if (!is_null($key) && !empty($key)) {
            $this->config['key'] = $key;
        }
        if (!(self::$driver instanceof LogStrategy) || self::$driver->closed()) {
            self::initDriver($this->config);
        }
        if (self::$driver instanceof LogStrategy) {
            return self::$driver->get($size);
        } else {
            return false;
        }
    }

    /**
     * 关闭句柄
     *
     * @author xyq
     */
    public function close()
    {
        if (self::$driver instanceof LogStrategy) {
            self::$driver->close();
        }
        self::$driver = null;
    }
}
