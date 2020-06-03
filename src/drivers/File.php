<?php
declare(strict_types = 1);
/**
 * Created by PhpStorm.
 * User: XYQ
 * Date: 2020-03-24
 * Time: 10:57
 */

namespace xyqWeb\log\drivers;


class File extends LogStrategy
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
        $realPath = $this->getFinalPath($config);
        $errorCode = $this->createDir($realPath);
        if (1 == $errorCode) {
            throw new LogException("目录没有创建权限");
        } elseif (2 == $errorCode) {
            throw new LogException("目录创建失败，请检查!");
        }
        $this->path = $realPath . '/';
    }

    /**
     * 创建文件目录
     *
     * @author xyq
     * @param string $path
     * @return int
     */
    private function createDir(string $path) : int
    {
        if (is_dir($path)) {
            return 0;
        }
        $code = 2;
        $result = @mkdir($path, 0777, true);
        if (false == $result) {
            $error = error_get_last();
            $message = $error['message'] ?? '';
            if (strpos($message, 'Permission denied')) {
                $code = 1;
            } elseif (strpos($message, 'File exists')) {
                $code = 0;
            }
        } elseif (is_dir($path)) {
            $code = 0;
        }
        return $code;
    }

    /**
     * 写入文本日志
     *
     * @author xyq
     * @param string $logName
     * @param $logContent
     * @param string $charList
     * @param int $jsonFormatCode
     * @return bool
     * @throws LogException
     */
    public function write(string $logName, $logContent, string $charList, int $jsonFormatCode) : bool
    {
        $logContent = is_array($logContent) ? json_encode($logContent, $jsonFormatCode) : $logContent;
        if (is_array($logContent)) {
            $logContent = json_encode($logContent, $jsonFormatCode);
        } elseif (is_object($logContent)) {
            $logContent = print_r($logContent, true);
        }
        $finalPath = $this->path . date('Y-m-d') . '/';
        $newNameArray = $this->resetLogName($logName);
        if (!empty($newNameArray['path'])) {
            $errorCode = $this->createDir($finalPath . $newNameArray['path']);
            if (1 == $errorCode) {
                throw new LogException("目录没有创建权限");
            } elseif (2 == $errorCode) {
                throw new LogException("目录创建失败，请检查!");
            }
            $filePath = $finalPath . $newNameArray['path'] . '/' . $newNameArray['logName'];
        } else {
            $filePath = $finalPath . $newNameArray['logName'];
        }
        $status = error_log(date('Y-m-d H:i:s') . '   ' . $logContent . $charList, 3, $filePath);
        if (true == $status) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 文本类型的永不关闭
     *
     * @author xyq
     * @return bool
     */
    public function closed() : bool
    {
        return false;
    }

    /**
     * 关闭文件连接此处无需实现
     *
     * @author xyq
     */
    public function close()
    {

    }
}