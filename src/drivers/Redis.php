<?php
declare(strict_types = 1);
/**
 * Created by PhpStorm.
 * User: XYQ
 * Date: 2021-03-24
 * Time: 10:55
 */

namespace xyqWeb\log\drivers;


class Redis extends LogStrategy
{
    /**
     * @var string 日志主路径
     */
    protected $path;
    /**
     * @var \Redis $redis
     */
    protected $redis;
    /**
     * @var string 缓存key
     */
    protected $key;
    /**
     * @var array 连接配置项
     */
    protected $connectionConfig;

    /**
     * Redis constructor.
     * @param array $config
     * @throws LogException
     */
    public function __construct(array $config)
    {
        if (!isset($config['host']) || empty($config['host']) || !isset($config['port']) || empty($config['port'])) {
            throw new LogException('Missing Redis parameter');
        }
        if (!isset($config['key']) || empty($config['key']) || !is_string($config['key'])) {
            throw new LogException('Missing cache key');
        }
        $this->key = $config['key'];
        $this->connectionConfig = [
            'host'     => $config['host'],
            'port'     => $config['port'],
            'password' => $config['password'] ?? '',
            'database' => $config['database'] ?? 0,
            'prefix'   => $config['prefix'] ?? '',
        ];
        $this->getRedisConnection();
        $realPath = $this->getFinalPath($config);
        $this->path = $realPath . '/';
    }

    /**
     * 获取redis连接
     *
     * @author xyq
     */
    protected function getRedisConnection()
    {
        $this->redis = new \Redis();
        if (!$this->redis->connect($this->connectionConfig['host'], $this->connectionConfig['port'], 2)) {
            throw new LogException('connect redis fail');
        }
        if (!empty($this->connectionConfig['password'])) {
            if (!$this->redis->auth($this->connectionConfig['password'])) {
                throw new LogException('redis wrong password');
            }
        }
        if (!empty($this->connectionConfig['database'])) {
            if (!$this->redis->select((int)$this->connectionConfig['database'])) {
                throw new LogException('redis fail to switch database');
            }
        }
        if (!empty($this->connectionConfig['prefix'])) {
            if (!$this->redis->setOption(\Redis::OPT_PREFIX, $this->connectionConfig['prefix'])) {
                throw new LogException('redis set prefix error');
            }
        }
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
        if (is_int($this->redis->lPush($this->key, json_encode($data)))) {
            return true;
        } else {
            return false;
        }
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
        if ($size > 1) {
            $content = [];
            for ($i = 0; $i < $size; $i++) {
                $log = $this->redis->rPop($this->key);
                if (!empty($log)) {
                    $content[] = json_decode($log, true);
                } else {
                    break;
                }
            }
            return $content;
        } else {
            $log = $this->redis->rPop($this->key);
            if (!empty($log)) {
                return json_decode($log, true);
            } else {
                return [];
            }
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
        $status = $this->redis->ping();
        if ('PONG' == $status) {
            return false;
        }
        return true;
    }

    /**
     * 关闭redis连接
     *
     * @author xyq
     */
    public function close()
    {
        if (!$this->closed()) {
            $this->redis->close();
        }
    }
}
