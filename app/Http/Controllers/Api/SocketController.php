<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use WebSocket\Client;

class SocketController extends Controller
{
    /**
     * 声音转换
     */
    public function vioceChange(Request $request)
    {
        $filePath = $request->post('file_name');
        $token = $request->post('token');
        $voiceName = $request->post('voice_name');
        //录制的音频文件
        $pcmFile = storage_path() .'/'. $filePath;
        $handle = fopen($pcmFile,'rb');
        var_dump($handle);die;
        $res = integertobytes(32);
        //传递参数
        $params = array(
            'access_token' => $token,
            'voice_name' => $voiceName,
            'enable_vad' => true,
            'lastpkg' => true
        );
        //第二部分--json数据
        $params_json = json_encode($params);
        //计算json字符串的长度
        $len = strlen($params_json);
        $headerArray = integerToBytes($len);
        $jsonArray = getBytes($params_json);
        $data = $headerArray + $jsonArray;
        $a = [0, 0, 0];
        $b = [1, 1, 1, 1];
        $c = [2, 2, 2, 2, 2];
        $d = array_merge($a,$b,$c);
        $ret = array('data'=>$d);
        return json_encode(['code'=>200,'data'=>$ret,'msg'=>'ok']);
        /* var_dump($params);
        var_dump($params_json);
        var_dump($len);
        var_dump($header); */
        var_dump($d);
        /* $client = new Client("wss://openapi.data-baker.com/ws/voice_conversion");
        $client->send("Hello WebSocket.org!",'binary');
        $result = $client->receive();
        echo $result;
        echo '<br>';
        $res = json_decode($result,true);
        var_dump($result); */
    }
}
