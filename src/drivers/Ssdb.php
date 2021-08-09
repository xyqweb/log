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
     * @param string $time
     * @return bool
     */
    public function write(string $logName, $logContent, string $charList, int $jsonFormatCode, string $time) : bool
    {
        $newNameArray = $this->resetLogName($logName);
        $finalPath = $this->path . date('Y-m-d') . '/';
        if (!empty($newNameArray['path'])) {
            $filePath = $finalPath . $newNameArray['path'] . '/' . $newNameArray['logName'];
        } else {
            $filePath = $finalPath . $newNameArray['logName'];
        }
        $data = [
            'file'           => $filePath,
            'content'        => $logContent,
            'date'           => $time,
            'charList'       => $charList,
            'jsonFormatCode' => $jsonFormatCode,
        ];
        $retry = 0;
        do {
            try {
                if (is_int($this->ssdb->qpush_front($this->key, json_encode($data)))) {
                    $result = true;
                    $retry = 2;
                } else {
                    $result = false;
                }
            } catch (\Exception $e) {
                try {
                    $this->close();
                    $this->getSSDBConnection();
                } catch (\Exception $exception) {
                    //exception break
                    $retry = 2;
                }
                $result = false;
            }
            !$result && $retry++;
        } while (!$result && $retry < 2 && $retry > 0);
        return $result;
    }

    /**
     * 获取存储的日志
     *
     * @author xyq
     * @param int $size
     * @return array
     */
    public function get(int $size)
    {
        $content = [];
        $logData = $this->ssdb->qpop_back($this->key, $size);
        if (!empty($logData)) {
            if ($size > 1) {
                foreach ($logData as $item) {
                    if (!empty($item)) {
                        $content[] = json_decode($item, true);
                    }
                }
            } else {
                if (!empty($logData)) {
                    $content = json_decode($logData, true);
                }
            }
        }
        return $content;
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
