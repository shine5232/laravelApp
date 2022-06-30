<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RecordController extends Controller
{
    protected $ret = array(
        'code' => 200,
        'data' => array(),
        'msg' => 'ok'
    );
    /**
     * 用户登录
     */
    public function login(Request $request){
        $name = $request->post('name');
        $pass = $request->post('pass');
        $this->ret['data'] = array(
            'token' => 1,
        );
        return json_encode($this->ret);
    }
}
