<?php
namespace common\helper;

require_once 'QRcode.php';
require_once 'simple_html_dom.php';

CommonTools::init();

class CommonTools
{

    private static $logger = null;

    /**
     * 初始化函数
     */
    static function init()
    {
        if (! self::$logger) {
            self::$logger = Logger::getLogger();
        }
    }

    /**
     * 集成第三方库simple_html_dom
     * 根据输入返回DOM对象，用于HTML解析
     * 详见：http://www.phpddt.com/manual/simplehtmldom_1_5/manual.htm
     * @param string $path
     */
    public static function getDom($path)
    {
        if (substr($path, 0, 4) == 'http' || file_exists($path)) {
            return file_get_html($path);
        } else {
            return str_get_html($path);
        }
    }

    /**
     * 获取Guid
     * @return string
     */
    public static function getGuid()
    {
        $charid = strtolower(md5(uniqid(mt_rand(), true)));
        $hyphen = chr(45); // "-"
        $uuid = substr($charid, 0, 8) . $hyphen . substr($charid, 8, 4) . $hyphen . substr($charid, 12, 4) . $hyphen . substr($charid, 16, 4) . $hyphen . substr($charid, 20, 12);
        return $uuid;
    }

    /**
     * 获取访问IP
     * @return string
     */
    public static function getIPAddr()
    {
        $ip = "Unknown";
        if (getenv("HTTP_X_FORWARDED_FOR")) {
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        } elseif (getenv("HTTP_CLIENT_IP")) {
            $ip = getenv("HTTP_CLIENT_IP");
        } elseif (getenv("REMOTE_ADDR")) {
            $ip = getenv("REMOTE_ADDR");
        }
        return $ip;
    }

    /**
     * curl多线程访问，目前还有小BUG，貌似链接数有限制，链接数过多会报错
     * @param array $lst_url
     */
    public static function multi_run($lst_url)
    {
        $mh = curl_multi_init();
        foreach ($lst_url as $i => $url) {
            $conn[$i] = curl_init($url);
            curl_setopt($conn[$i], CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($conn[$i], CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($conn[$i], CURLOPT_SSL_VERIFYHOST, false);
            curl_multi_add_handle($mh, $conn[$i]);
        }
        
        do {
            $mrc = curl_multi_exec($mh, $active);
        } while ($mrc == CURLM_CALL_MULTI_PERFORM);
        
        while ($active and $mrc == CURLM_OK) {
            if (curl_multi_select($mh) != - 1) {
                do {
                    $mrc = curl_multi_exec($mh, $active);
                } while ($mrc == CURLM_CALL_MULTI_PERFORM);
            }
        }
        foreach ($lst_url as $i => $url) {
            $res[$i] = curl_multi_getcontent($conn[$i]);
            curl_close($conn[$i]);
        }
        return $res;
    }

    /**
     * post请求
     * @param string $url
     * @param mixed $data
     * @param string $type
     * @return array
     */
    public static function http_post_data($url, $data, $type = 'json')
    {
        $header = [];
        if(!is_array($data)){
            $data = preg_replace("/\s/", "", $data);
        }
        switch ($type) {
            case 'form':
                if(is_array($data)){
                    $data = self::argsEncode($data);
                }
                $header[] = 'Content-Type: application/x-www-form-urlencoded; charset=UTF-8';
                break;
            case 'xml':
            case 'text':
                $header[] = 'Content-Type: text/xml; charset=UTF-8';
                break;
            case 'json':
                if(is_array($data)){
                    $data = json_encode($data);
                }
                $header[] = 'Content-Type: application/json; charset=UTF-8';
                break;
        }
        $data_string = self::decodeUnicode($data);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        
        $return_content = curl_exec($ch);
        $return_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        curl_close($ch);
        
        self::$logger->debug("\nURL==" . $url . "\nPOST==" . $data_string . "\nRETURN==" . $return_content);
        return array(
            $return_code,
            $return_content
        );
    }

    /**
     * get请求
     * @param string $url
     */
    public static function http_request_get($url)
    {
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        
        $return_content = curl_exec($ch);
        $return_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        curl_close($ch);
        
        self::$logger->debug("\nURL==" . $url . "\nRETURN==" . $return_content);
        return array(
            $return_code,
            $return_content
        );
    }

    /**
     * 数组参数组装，用于发送请求
     * @param array $arr
     */
    static function argsEncode($arr)
    {
        $p_lst = [];
        foreach ($arr as $k => $v) {
            $p_lst[] = "$k=$v";
        }
        return implode('&', $p_lst);
    }

    /**
     * Unicode解码
     * @param string $str
     * @return string
     */
    static function decodeUnicode($str)
    {
        return preg_replace_callback('/\\\\u([0-9a-f]{4})/i', create_function('$matches', 'return mb_convert_encoding(pack("H*", $matches[1]), "UTF-8", "UCS-2BE");'), $str);
    }

    /**
     * 判断是否为真实有效可访问的网址（因为要获取网页头，所以速度有点慢，慎用）
     * @param string $url
     * @return boolean
     */
    static function relUrl($url)
    {
        if (substr($url, 0, 4) == 'http') {
            $array = get_headers($url, true);
            if (count($array) > 0 && is_array($array)) {
                if (preg_match('/200/', $array[0])) {
                    unset($array);
                    return true;
                } else {
                    unset($array);
                    return false;
                }
            } else {
                unset($array);
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * 获取图片的真实地址（服务器端）
     *
     * @param string $fileName         
     *    
     * @return boolean|string
     */
    static function getImgPath($fileName)
    {
        if (self::relUrl($fileName)) {
            return $fileName;
        } elseif (! file_exists($fileName)) {
            $fileName = $_SERVER['DOCUMENT_ROOT'] . $fileName;
        }
        return file_exists($fileName) ? $fileName : false;
    }

    /**
     * 下载图片
     * 成功后会返回本地链接
     * 
     * @param string $url       图片链接
     * @param string $filePath  下载路径
     * @param string $fileName  文件名
     * 
     * @return string|boolean   服务器地址
     */
    public static function downImg($url, $filePath = null, $fileName = null)
    {
        if (! $filePath) {
            $filePath = '/data/img/files/' . date('Y') . '/' . date('md') . '/';
        }
        $urlPath = $filePath;
        $filePath = $_SERVER['DOCUMENT_ROOT'] . $urlPath;
        
        if (! file_exists($filePath) || ! is_dir($filePath)) {
            mkdir($filePath, 0744, true);
        }
        if (! $fileName) {
            $fileName = substr(strrchr($url, "/"), 1);
        }
        
        if (! @pathinfo($fileName)['extension']) {
            $fileName = $fileName . ".jpg";
        }
        $file = $filePath . $fileName;
        
        if (! file_exists($file)) {
            
            $ch = curl_init();
            if (defined('CURLOPT_IPRESOLVE') && defined('CURL_IPRESOLVE_V4')) {
                curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
            }
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            
            $return_content = curl_exec($ch);
            $return_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($return_code != 200) {
                $file = false;
            } else {
                file_put_contents($file, $return_content);
            }
        }
        
        return $file ? $urlPath . $fileName : false;
    }

    /**
     * 通过data生成二维码
     *
     * @param string $data      二维码数据
     * @param string $fileName  生成的文件名
     * @param string $path      生成的路径
     * 
     * @param string            服务器地址
     */
    public static function genQRcode($data, $fileName = null, $path = '/data/img/qrcode/')
    {
        $urlPath = $path . date('Y') . '/' . date('md') . '/';
        $path = $_SERVER['DOCUMENT_ROOT'] . $urlPath;
        
        if (! file_exists($path) || ! is_dir($path)) {
            mkdir($path, 0744, true);
        }
        if (! $fileName) {
            $fileName = self::getGuid() . ".png";
        }
        
        $qrcode = $path . $fileName;
        \QRcode::png($data, $qrcode, 'L', 4, 2);
        
        return $urlPath . $fileName;
    }

    /**
     * 添加LOGO至二维码中间
     *
     * @param string $fileName 二维码
     * @param string $logo     logo
     * 
     * @return string          服务器地址
     */
    public static function addLogo($fileName, $logo)
    {
        if (($fileName = self::getImgPath($fileName)) === false) {
            return false;
        }
        if (($logo = self::getImgPath($fileName)) === false) {
            return false;
        }
        $file = imagecreatefromstring(file_get_contents($fileName));
        $logo = imagecreatefromstring(file_get_contents($logo));
        
        $qr_width = imagesx($file); // 二维码图片宽度
        $qr_height = imagesy($file); // 二维码图片高度
        $logo_width = imagesx($logo); // logo图片宽度
        $logo_height = imagesy($logo); // logo图片高度
        $logo_qr_width = $qr_width / 5;
        $scale = $logo_width / $logo_qr_width;
        $logo_qr_height = $logo_height / $scale;
        $from_width = ($qr_width - $logo_qr_width) / 2;
        // 重新组合图片并调整大小
        imagecopyresampled($file, $logo, $from_width, $from_width, 0, 0, $logo_qr_width, $logo_qr_height, $logo_width, $logo_height);
        imagepng($file, $fileName);
        
        return $fileName;
    }

    /**
     * 合成图片，以imgs[0]为基准图片，后面图片的x,y以他为基准计算
     * 将其他图片合入，后面的图片会覆盖前面的区域
     * [
     * 'src' => 图片地址
     * 'x' => 默认为0
     * 'y' => 默认为0
     * 'w' => 默认为图片宽度
     * 'h' => 默认为图片高度
     * ]
     *
     * @param array $imgs       图片集合
     * @param string $filePath  保存路径
     * 
     * @return string|boolean   服务器地址
     */
    public static function mixImgs(array $imgs, $filePath = '/data/img/shared/')
    {
        if (is_array($imgs) && sizeof($imgs) > 1) {
            $baseImg = $imgs[0]['src'];
            if (($baseImg = self::getImgPath($baseImg)) === false) {
                return false;
            }
            $file = imagecreatefromstring(file_get_contents($baseImg));
            $length = sizeof($imgs);
            for ($i = 1; $i < $length; $i ++) {
                if (($path = self::getImgPath($imgs[$i]['src'])) === false) {
                    return false;
                }
                $img = imagecreatefromstring(file_get_contents($path));
                $x = isset($imgs[$i]['x']) ? $imgs[$i]['x'] : 0;
                $y = isset($imgs[$i]['y']) ? $imgs[$i]['y'] : 0;
                $w = isset($imgs[$i]['w']) ? $imgs[$i]['w'] : imagesx($img);
                $h = isset($imgs[$i]['h']) ? $imgs[$i]['h'] : imagesy($img);
                
                imagecopyresampled($file, $img, $x, $y, 0, 0, $w, $h, imagesx($img), imagesy($img));
            }
            
            $urlPath = $filePath . date('Y') . '/' . date('md') . '/';
            $filePath = $_SERVER['DOCUMENT_ROOT'] . $urlPath;
            $output = self::getGuid() . '.png';
            
            if (! file_exists($filePath) || ! is_dir($filePath)) {
                mkdir($filePath, 0744, true);
            }
            
            $fileName = $filePath . $output;
            imagepng($file, $fileName);
            
            return $urlPath . $output;
        } else {
            return false;
        }
    }
    
    public static function emailType($emailtype)
    {
        switch ($emailtype) {
            case '/[^@]+@(qq.com)$/':
                $type = 'https://mail.qq.com/';
                break;
            case '/[^@]+@(163.com)$/':
                $type = 'http://mail.163.com/';
                break;
            case '/[^@]+@(sina.com)$/':
                $type = 'http://mail.sina.com.cn/';
                break;
            case '/[^@]+@(sohu.com)$/':
                $type = 'http://mail.sohu.com/';
                break;
            case '/[^@]+@(189.cn)$/':
                $type = 'http://webmail30.189.cn/w2/';
                break;
            case '/[^@]+@(139.com)$/':
                $type = 'http://mail.10086.cn/';
                break;
            default:
                $type = 'https://mail.qq.com/';
                break;
        }
        return $type;
    }

    /**
     * 是否微信浏览器
     * 
     * @return boolean
     */
    public static function is_weixin()
    {
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
            return true;
        }
        return false;
    }
}