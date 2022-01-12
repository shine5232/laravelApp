<?php

namespace App\Http\Controllers\Wx;

use App\Http\Controllers\Controller;

class ErrorCodeController extends Controller
{
    /**
     * error code 说明
     * -41001: encodingAesKey 非法
     * -41003: aes 解密失败
     * -41004: 解密后得到的buffer非法
     * -41005: base64加密失败
     * -41016: base64解密失败
    */
    public static $OK = 0;
	public static $IllegalAesKey = -41001;
	public static $IllegalIv = -41002;
	public static $IllegalBuffer = -41003;
	public static $DecodeBase64Error = -41004;
}
