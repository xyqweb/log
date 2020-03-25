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
use yii\base\Component;
use yii\base\Application as BaseApp;
use yii\base\Event;

class YiiLog extends Component
{
    /**
     * @var \xyqWeb\log\drivers\LogStrategy
     */
    private $driver;
    /**
     * @var array 配置内容
     */
    public $config = [];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->initDriver($this->config);
        Event::on(BaseApp::class, BaseApp::EVENT_AFTER_REQUEST, function () {
            $this->close();
        });
    }

    /**
     * Log constructor.
     * @param array $config
     * @throws LogException
     */
    public function initDriver(array $config)
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

    /**
     * 关闭句柄
     *
     * @author xyq
     */
    public function close()
    {
        $this->driver->close();
    }
}