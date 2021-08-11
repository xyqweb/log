<?php
declare(strict_types = 1);
/**
 * Created by PhpStorm.
 * User: XYQ
 * Date: 2021-08-10
 * Time: 09:58
 */

namespace xyqWeb\log\drivers;


class Seas extends LogStrategy
{
    /**
     * @var string 日志主路径
     */
    protected $path;

    /**
     * File constructor.
     * @param array $config
     * @throws LogException
     */
    public function __construct(array $config)
    {
        if (!extension_loaded('SeasLog')) {
            throw new LogException('请先安装SeasLog组件');
        }
        $this->path = $this->getFinalPath($config) . '/';
        \SeasLog::setBasePath($this->path);
    }

    /**
     * 写入日志
     *
     * @author xyq
     * @param string $logName
     * @param array|object|string $logContent
     * @param string $charList
     * @param int $jsonFormatCode
     * @param string $time
     * @return bool
     */
    public function write(string $logName, $logContent, string $charList, int $jsonFormatCode, string $time) : bool
    {
        $logContent = is_array($logContent) ? json_encode($logContent, $jsonFormatCode) : $logContent;
        if (is_array($logContent)) {
            $logContent = json_encode($logContent, $jsonFormatCode);
        } elseif (is_object($logContent)) {
            $logContent = print_r($logContent, true);
        }
        $appender = ini_get("seaslog.appender");
        if (in_array($appender, [2, 3])) {
            $finalPath = $this->path . date('Y-m-d') . '/';
        } else {
            $finalPath = date('Y-m-d') . '/';
        }
        $newNameArray = $this->resetLogName($logName);
        if (!empty($newNameArray['path'])) {
            $filePath = $finalPath . $newNameArray['path'] . '/' . $newNameArray['logName'];
        } else {
            $filePath = $finalPath . $newNameArray['logName'];
        }
        \SeasLog::setLogger($filePath);
        return \SeasLog::log(\SEASLOG_INFO, $logContent);
    }

    /**
     * 获取日志内容
     *
     * @author xyq
     * @param int $size
     * @return mixed|void
     * @throws \Exception
     */
    public function get(int $size)
    {
        throw new \Exception('不支持');
    }

    /**
     * 关闭状态
     *
     * @author xyq
     * @return bool
     */
    public function closed() : bool
    {
        return false;
    }

    /**
     * 执行关闭
     *
     * @author xyq
     */
    public function close()
    {
        \SeasLog::closeLoggerStream();
    }
}
