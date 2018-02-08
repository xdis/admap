<?php
namespace common\helper;

class Logger
{

    private static $file_name = "CommonTools.log";
    private $MAX_SIZE = 100000000;
    private $file_path = null;
    private $file_fullpath = null;
    private $time_format = "Y-m-d H:i:s ";

    public static function getLogger($name = null, $path = null)
    {
        if (! $path) {
            $path = dirname(__DIR__) . "/../log/";
        }
        if (! $name) {
            $name = self::$file_name;
        }
        return new Logger($name, $path);
    }

    function __construct($name, $path)
    {
        $this->file_path = $path;
        $this->file_fullpath = $path . $name;
    }

    public function debug($value)
    {
        $str_value = "";
        if (is_array($value)) {
            $str_value = implode(',', $value);
        } else {
            $str_value = $value;
        }
        $ip = CommonTools::getIPAddr();
        $print_value = date($this->time_format) . ": " . $ip . "--" . $str_value . "\n";
        
        if (! file_exists($this->file_path)) {
            mkdir($this->file_path, 0744, true);
        }
        if (! file_exists($this->file_fullpath)) {
            touch($this->file_fullpath);
            chmod($this->file_fullpath, 0644);
        }
        
        $pre_log_file = $this->file_fullpath . date("YmdHis");
        if (filesize($this->file_fullpath) > $this->MAX_SIZE) {
            copy($this->file_fullpath, $pre_log_file);
            file_put_contents($this->file_fullpath, "");
        }
        
        file_put_contents($this->file_fullpath, $print_value, FILE_APPEND);
    }
}
?>