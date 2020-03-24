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
        $this->ssdb = new SimpleSSDB($config['host'], $config['port']);
        $realPath = $this->getFinalPath($config);
        $this->path = $realPath . '/';
    }

    /**
     * 写入ssdb队列
     *
     * @author xyq
     * @param string $logName
     * @param $logContent
     * @return bool
     */
    public function write(string $logName, $logContent) : bool
    {
        $logContent = ctype_alnum($logContent) ? $logContent : json_encode($logContent, JSON_UNESCAPED_UNICODE);
        $newNameArray = $this->resetLogName($logName);
        if (!empty($newNameArray['path'])) {
            $filePath = $this->path . $newNameArray['path'] . '/' . $newNameArray['logName'];
        } else {
            $filePath = $this->path . $newNameArray['logName'];
        }
        $data = [
            'file'    => $filePath,
            'content' => $logContent,
        ];
        if (is_int($this->ssdb->qpush_front($this->key, json_encode($data)))) {
            return true;
        } else {
            return false;
        }
    }
}