<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    /**
     * 上传音频文件
     */
    public function vioceFile(Request $request){
        $userId = $request->post('userId');
        $file = $request->file('clientFile');
        //获取文件的扩展名
        $ext = $file->getClientOriginalExtension();
        //获取文件的绝对路径
        $path = $file->getRealPath();
        //定义新的文件名
        $fileName = $userId.'_'.time().'.'.$ext;
        $res = Storage::disk('file')->put($fileName,file_get_contents($path));
        if($res){
            $url = '/file/'.date('Ymd').'/'.$fileName;
            $this->ret['data'] = array('url'=>$url);
        }else{
            $this->ret['code'] = 1001;
            $this->ret['msg'] = 'upload file fail';
        }
        return json_encode($this->ret);
    }
    /**
     * 获取上传的音频文件
     */
    public function getUploadFile(Request $request){
        $filePath = $request->post('file_name');
        $token = $request->post('token');
        $file = storage_path().$filePath;
        $handle = fopen($file,'rb');
        var_dump($handle);
    }
}
