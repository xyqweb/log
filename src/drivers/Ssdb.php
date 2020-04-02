<?php
declare(strict_types = 1);
/**
 * Created by PhpStorm.
 * User: XYQ
 * Date: 2020-03-24
 * Time: 10:57
 */

namespace xyqWeb\log\drivers;


use xyqWeb\log\drivers\ssdb\SimpleSSDB;

class Ssdb extends LogStrategy
{
    /**
     * @var string 日志主路径
     */
    protected $path;
    /**
     * @var SimpleSSDB ssdb连接
     */
    protected $ssdb;
    /**
     * @var string 缓存key
     */
    protected $key;
    /**
     * @var array 连接配置项
     */
    protected $connectionConfig;

    /**
     * Ssdb constructor.
     * @param array $config
     * @throws LogException
     * @throws ssdb\SSDBException
     */
    public function __construct(array $config)
    {
        if (!isset($config['host']) || empty($config['host']) || !isset($config['port']) || empty($config['port'])) {
            throw new LogException('Missing SSDB parameter');
        }
        if (!isset($config['key']) || empty($config['key']) || !is_string($config['key'])) {
            throw new LogException('Missing cache key');
        }
        $this->key = $config['key'];
        $this->connectionConfig = [
            'host' => $config['host'],
            'port' => $config['port']
        ];
        $this->getSSDBConnection();
        $realPath = $this->getFinalPath($config);
        $this->path = $realPath . '/';
    }

    /**
     * 获取ssdb连接
     *
     * @author xyq
     * @throws ssdb\SSDBException
     */
    protected function getSSDBConnection()
    {
        $this->ssdb = new SimpleSSDB($this->connectionConfig['host'], $this->connectionConfig['port']);
    }

    /**
     * 写入ssdb队列
     *
     * @author xyq
     * @param string $logName
     * @param array|object|string $logContent
     * @param string $charList
     * @param int $jsonFormatCode
     * @return bool
     * @throws ssdb\SSDBException
     */
    public function write(string $logName, $logContent, string $charList, int $jsonFormatCode) : bool
    {
        $newNameArray = $this->resetLogName($logName);
        if (!empty($newNameArray['path'])) {
            $filePath = $this->path . $newNameArray['path'] . '/' . $newNameArray['logName'];
        } else {
            $filePath = $this->path . $newNameArray['logName'];
        }
        $data = [
            'file'           => $filePath,
            'content'        => $logContent,
            'date'           => date('Y-m-d H:i:s'),
            'charList'       => $charList,
            'jsonFormatCode' => $jsonFormatCode,
        ];
        if (false == $this->ssdb->closed()) {
            $this->getSSDBConnection();
        }
        if (is_int($this->ssdb->qpush_front($this->key, json_encode($data)))) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 返回关闭状态
     *
     * @author xyq
     * @return bool
     */
    public function closed() : bool
    {
        return (bool)$this->ssdb->closed();
    }

    /**
     * 关闭ssdb连接
     *
     * @author xyq
     */
    public function close()
    {
        if (false == $this->ssdb->closed()) {
            $this->ssdb->close();
        }
    }
}