<?php

use Illuminate\Support\Facades\Storage;

/**
 * @Description: curl请求
 * @param $url
 * @param null $data
 * @param string $method
 * @param array $header
 * @param bool $https
 * @param int $timeout
 * @return mixed
 */
if (!function_exists("curl_request")) {
    function curl_request($url, $data = null, $method = 'get', $header = array("content-type: application/x-www-form-urlencoded"), $https = true, $timeout = 10)
    {
        $method = strtoupper($method);
        $ch = curl_init(); //初始化
        curl_setopt($ch, CURLOPT_URL, $url); //访问的URL
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //只获取页面内容，但不输出
        if ($https) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //https请求 不验证证书
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); //https请求 不验证HOST
        }
        if ($method != "GET") {
            if ($method == 'POST') {
                curl_setopt($ch, CURLOPT_POST, true); //请求方式为post请求
            }
            if ($method == 'PUT' || strtoupper($method) == 'DELETE') {
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method); //设置请求方式
            }
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data); //请求数据
        }
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header); //模拟的header头
        $result = curl_exec($ch); //执行请求
        curl_close($ch); //关闭curl，释放资源
        return $result;
    }
}
/**
 * 转换一个int为byte数组
 * @param $byt 目标byte数组
 * @param $val 需要转换的字符串
 */
if (!function_exists("integerToBytes")) {
    function integerToBytes($val)
    {
        $byt = array();
        $byt[0] = ($val >> 24 & 0xff);
        $byt[1] = ($val >> 16 & 0xff);
        $byt[2] = ($val >> 8 & 0xff);
        $byt[3] = ($val & 0xff);
        return $byt;
    }
}

/**
 * 转换一个String字符串为byte数组
 * @param $str 需要转换的字符串
 * @param $bytes 目标byte数组
 */
if (!function_exists("getBytes")) {
    function getBytes($string)
    {
        $bytes = array();
        for ($i = 0; $i < strlen($string); $i++) {
            $bytes[] = ord($string[$i]);
        }
        return $bytes;
    }
}
/**
 * cURL传递文件流
 */
if (!function_exists("curl_file")) {
    function curl_file($url, $data)
    {
        var_dump($data);
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => http_build_query($data),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }
}
/**
 * 合并音频文件
 */
if (!function_exists("mergeWavFile")) {
    function mergeWavFile($originFiles, $newFile)
    {
        $filePath  = fopen($newFile, 'a+');
        foreach($originFiles as $v){
            $cacheFile = fopen(storage_path().$v, 'rb');
            $content   = fread($cacheFile, filesize(storage_path().$v));
            fwrite($filePath, $content);
            fclose($cacheFile);
        }
        fclose($filePath);
    }
}
/**
 * 处理微信用户头像为圆形
 */
if(!function_exists("wxAvatarCircle")){
    function wxAvatarCircle($original_path,$openid){
        $w = 132;  $h=132; // original size  微信默认头像大小 高132,宽132
        $path = '/file/'.date('Ymd');
        $file_path = storage_path().$path;
        $dest_path = $file_path.'/'.$openid.'_circle.png';
        $src = imagecreatefromstring(file_get_contents($original_path));
        $newpic = imagecreatetruecolor($w,$h);
        imagealphablending($newpic,false);
        $transparent = imagecolorallocatealpha($newpic, 0, 0, 0, 127);
        $r=$w/2;
        for($x=0;$x<$w;$x++){
            for($y=0;$y<$h;$y++){
                $c = imagecolorat($src,$x,$y);
                $_x = $x - $w/2;
                $_y = $y - $h/2;
                if((($_x*$_x) + ($_y*$_y)) < ($r*$r)){
                    imagesetpixel($newpic,$x,$y,$c);
                }else{
                    imagesetpixel($newpic,$x,$y,$transparent);
                }
            }
        }
        imagesavealpha($newpic, true);
        imagepng($newpic, $dest_path);
        imagedestroy($newpic);
        imagedestroy($src);
        return $dest_path;
    }
}
/**
 * 中文字符串转换为unicode编码
 */
if(!function_exists("toUnicode")){
    function toUnicode($string){
        $str = mb_convert_encoding($string, 'UCS-2', 'UTF-8');
        $arrstr = str_split($str, 2);
        $unistr = '';
        foreach ($arrstr as $n) {
            $dec = hexdec(bin2hex($n));
            $unistr .= '&#' . $dec . ';';
        }
        return $unistr;
    }
}
