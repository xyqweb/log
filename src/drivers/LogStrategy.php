<?php
declare(strict_types = 1);
/**
 * Created by PhpStorm.
 * User: XYQ
 * Date: 2020-03-24
 * Time: 15:38
 */

namespace xyqWeb\log\drivers;


abstract class LogStrategy
{
    /**
     * 写入日志
     *
     * @author xyq
     * @param string $logName 日志名称
     * @param string|array|object $logContent 日志内容
     * @param string $charList 分隔符
     * @param int $jsonFormatCode json格式化code
     * @return bool
     */
    abstract public function write(string $logName, $logContent, string $charList, int $jsonFormatCode) : bool;

    /**
     * 获取最终的path路径
     *
     * @author xyq
     * @param array $config
     * @return string
     * @throws LogException
     */
    protected function getFinalPath(array $config) : string
    {
        if (!isset($config['path']) || !is_string($config['path'])) {
            throw new LogException("no path can write");
        }
        $config['path'] = rtrim($config['path'], '/');
        if (isset($config['project'])) {
            $config['path'] .= '/' . $config['project'];
        }
        $config['path'] .= '/' . date('Y-m-d');
        return $config['path'];
    }

    /**
     * 重置日志名
     *
     * @author xyq
     * @param string $logName
     * @return array
     */
    protected function resetLogName(string $logName)
    {
        $path = '';
        if (is_int(strpos($logName, '/'))) {
            $temp = explode('/', $logName);
            $logName = array_pop($temp);
            $temp = array_values(array_filter($temp));
            $path = implode('/', $temp);
        }
        return ['logName' => $logName, 'path' => $path];
    }
}