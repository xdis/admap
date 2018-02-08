<?php
namespace common\components\upacp\sdk;

include_once 'log.class.php';

class Common
{

    static $_log = null;

    static function getLogger()
    {
        if (! self::$_log) {
            // 初始化日志
            self::$_log = new \PhpLog(SDK_LOG_FILE_PATH, "PRC", SDK_LOG_LEVEL);
        }
        return self::$_log;
    }

    /**
     * key1=value1&key2=value2转array
     *
     * @param $str key1=value1&key2=value2的字符串            
     * @param $$needUrlDecode 是否需要解url编码，默认不需要            
     */
    static function parseQString($str, $needUrlDecode = false)
    {
        $result = array();
        $len = strlen($str);
        $temp = "";
        $curChar = "";
        $key = "";
        $isKey = true;
        $isOpen = false;
        $openName = "\0";
        
        for ($i = 0; $i < $len; $i ++) {
            $curChar = $str[$i];
            if ($isOpen) {
                if ($curChar == $openName) {
                    $isOpen = false;
                }
                $temp = $temp . $curChar;
            } elseif ($curChar == "{") {
                $isOpen = true;
                $openName = "}";
                $temp = $temp . $curChar;
            } elseif ($curChar == "[") {
                $isOpen = true;
                $openName = "]";
                $temp = $temp . $curChar;
            } elseif ($isKey && $curChar == "=") {
                $key = $temp;
                $temp = "";
                $isKey = false;
            } elseif ($curChar == "&" && ! $isOpen) {
                self::putKeyValueToDictionary($temp, $isKey, $key, $result, $needUrlDecode);
                $temp = "";
                $isKey = true;
            } else {
                $temp = $temp . $curChar;
            }
        }
        self::putKeyValueToDictionary($temp, $isKey, $key, $result, $needUrlDecode);
        return $result;
    }

    static function putKeyValueToDictionary($temp, $isKey, $key, &$result, $needUrlDecode)
    {
        if ($isKey) {
            $key = $temp;
            if (strlen($key) == 0) {
                return false;
            }
            $result[$key] = "";
        } else {
            if (strlen($key) == 0) {
                return false;
            }
            if ($needUrlDecode)
                $result[$key] = urldecode($temp);
            else
                $result[$key] = $temp;
        }
    }

    /**
     * 字符串转换为 数组
     *
     * @param unknown_type $str            
     * @return multitype:unknown
     */
    static function convertStringToArray($str)
    {
        return self::parseQString($str);
    }

    /**
     * 压缩文件 对应java deflate
     *
     * @param unknown_type $params            
     */
    static function deflate_file(&$params)
    {
        $log = Common::getLogger();;
        foreach ($_FILES as $file) {
            $log->LogInfo("---------处理文件---------");
            if (file_exists($file['tmp_name'])) {
                $params['fileName'] = $file['name'];
                
                $file_content = file_get_contents($file['tmp_name']);
                $file_content_deflate = gzcompress($file_content);
                
                $params['fileContent'] = base64_encode($file_content_deflate);
                $log->LogInfo("压缩后文件内容为>" . base64_encode($file_content_deflate));
            } else {
                $log->LogInfo(">>>>文件上传失败<<<<<");
            }
        }
    }

    /**
     * 讲数组转换为string
     *
     * @param $para 数组            
     * @param $sort 是否需要排序            
     * @param $encode 是否需要URL编码            
     * @return string
     */
    static function createLinkString($para, $sort, $encode)
    {
        if ($para == NULL || ! is_array($para))
            return "";
        
        $linkString = "";
        if ($sort) {
            $para = self::argSort($para);
        }
        while (list ($key, $value) = each($para)) {
            if ($encode) {
                $value = urlencode($value);
            }
            $linkString .= $key . "=" . $value . "&";
        }
        // 去掉最后一个&字符
        $linkString = substr($linkString, 0, count($linkString) - 2);
        
        return $linkString;
    }

    /**
     * 对数组排序
     *
     * @param $para 排序前的数组
     *            return 排序后的数组
     */
    static function argSort($para)
    {
        ksort($para);
        reset($para);
        return $para;
    }
}