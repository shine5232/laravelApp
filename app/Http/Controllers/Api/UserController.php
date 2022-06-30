<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    /**
     * 用户登录
     */
    public function login(Request $request)
    {
        $mobile = trim($request->post('mobile'));
        $pass = trim($request->post('password'));
        if (empty($mobile)) {
            $this->ret = array(
                'code' => 201,
                'data' => '',
                'msg' => '用户手机号不得为空'
            );
            return json_encode($this->ret);
        } else {
            if (!isMobile($mobile)) {
                $this->ret = array(
                    'code' => 201,
                    'data' => '',
                    'msg' => '手机号不正确'
                );
                return json_encode($this->ret);
            }
        }
        if (empty($pass)) {
            $this->ret = array(
                'code' => 201,
                'data' => '',
                'msg' => '用户密码不得为空'
            );
            return json_encode($this->ret);
        }
        $where = [
            ['mobile', '=', $mobile],
            ['type', '<>', 1]
        ];
        $users = DB::table('user')->where($where)->get()->map(function ($value) {
            $value->avatar = 'https://'.$_SERVER["HTTP_HOST"].$value->avatar;
            return (array)$value;
        })->toArray();
        if ($users) {
            if ($users[0]['type'] == 2) {
                $this->ret = array(
                    'code' => 201,
                    'data' => '',
                    'msg' => '该用户已被禁用'
                );
            } else if ($users[0]['password'] != md5(md5($pass) . $mobile)) {
                $this->ret = array(
                    'code' => 201,
                    'data' => '',
                    'msg' => '密码错误'
                );
            } else {
                $token = setToken($mobile);
                Cache::put($token, $mobile, 86400);
                $update = [
                    'loginip' => $request->ip(),
                    'update_time' => date('Y-m-d H:i:s'),
                ];
                Db::table('user')->where('id', $users[0]['id'])->update($update);
                unset($users[0]['password']);
                switch ($users[0]['type']) {
                    case 0:
                        $users[0]['typeName'] = '普通会员';
                        break;
                    case 1:
                        $users[0]['typeName'] = 'VIP会员';
                        break;
                    default:
                        $users[0]['typeName'] = '会员';
                }
                $this->ret['data'] = array(
                    'token' => $token,
                    'userInfo' => $users[0]
                );
                $this->ret['msg'] = '登录成功';
            }
        } else {
            $this->ret = array(
                'code' => 201,
                'data' => '',
                'msg' => '用户不存在'
            );
        }
        return json_encode($this->ret);
    }
    /**
     * 用户注册
     */
    public function regist(Request $request)
    {
        $mobile = trim($request->post('mobile'));
        $pass = trim($request->post('password'));
        $code = trim($request->post('code'));
        if (empty($mobile)) {
            $this->ret = array(
                'code' => 201,
                'data' => '',
                'msg' => '用户手机号不得为空'
            );
            return json_encode($this->ret);
        } else {
            if (!isMobile($mobile)) {
                $this->ret = array(
                    'code' => 201,
                    'data' => '',
                    'msg' => '手机号不正确'
                );
                return json_encode($this->ret);
            }
        }
        if (empty($pass)) {
            $this->ret = array(
                'code' => 201,
                'data' => '',
                'msg' => '用户密码不得为空'
            );
            return json_encode($this->ret);
        }
        if (empty($code)) {
            $this->ret = array(
                'code' => 201,
                'data' => '',
                'msg' => '验证码不得为空'
            );
            return json_encode($this->ret);
        } else {
            //验证码校验
        }
        $where = [
            ['mobile', '=', $mobile],
            ['type', '<>', 1]
        ];
        $users = DB::table('user')->where($where)->get()->toArray();
        if ($users) {
            $this->ret = array(
                'code' => 201,
                'data' => '',
                'msg' => '手机号已注册'
            );
            return json_encode($this->ret);
        } else {
            $insert = [
                'name' => 'App_' . $mobile,
                'mobile' => $mobile,
                'password' => md5(md5($pass) . $mobile),
                'loginip' => $request->ip(),
                'create_time' => date('Y-m-d H:i:s'),
            ];
            $user_id = DB::table('user')->insertGetId($insert);
            if ($user_id) {
                $token = setToken($mobile);
                Cache::put($token, $mobile, 86400);
                $insert['id'] = $user_id;
                $insert['typeName'] = '普通会员';
                unset($insert['password']);
                $this->ret['data'] = array(
                    'token' => $token,
                    'userInfo' => $insert
                );
                $this->ret['msg'] = '恭喜您，注册成功';
            } else {
                $this->ret = array(
                    'code' => 201,
                    'data' => '',
                    'msg' => '注册失败，请稍后再试'
                );
            }
            return json_encode($this->ret);
        }
    }
    /**
     * 修改登录密码
     */
    public function updatePassword(Request $request){
        $mobile = trim($request->post('mobile'));
        $pass = trim($request->post('password'));
        $code = trim($request->post('code'));
        if (empty($mobile)) {
            $this->ret = array(
                'code' => 201,
                'data' => '',
                'msg' => '用户手机号不得为空'
            );
            return json_encode($this->ret);
        } else {
            if (!isMobile($mobile)) {
                $this->ret = array(
                    'code' => 201,
                    'data' => '',
                    'msg' => '手机号不正确'
                );
                return json_encode($this->ret);
            }
        }
        if (empty($pass)) {
            $this->ret = array(
                'code' => 201,
                'data' => '',
                'msg' => '用户密码不得为空'
            );
            return json_encode($this->ret);
        }
        if (empty($code)) {
            $this->ret = array(
                'code' => 201,
                'data' => '',
                'msg' => '验证码不得为空'
            );
            return json_encode($this->ret);
        } else {
            //验证码校验
        }
        $where = [
            ['mobile', '=', $mobile],
            ['type', '<>', 1]
        ];
        $users = DB::table('user')->where($where)->get()->toArray();
        if (!$users) {
            $this->ret = array(
                'code' => 201,
                'data' => '',
                'msg' => '手机号不存在'
            );
            return json_encode($this->ret);
        } else {
            $update = [
                'password' => md5(md5($pass) . $mobile),
                'update_time' => date('Y-m-d H:i:s'),
            ];
            $user_id = DB::table('user')->where('mobile',$mobile)->update($update);
            if ($user_id) {
                $this->ret['msg'] = '密码修改成功';
            } else {
                $this->ret = array(
                    'code' => 201,
                    'data' => '',
                    'msg' => '密码修改失败'
                );
            }
            return json_encode($this->ret);
        }
    }
    /**
     * 退出登录
     */
    public function outLogin(Request $request){
        $token = $request->header('Authorization');
        Cache::pull($token);
        $this->ret['msg'] = '退出成功';
        return json_encode($this->ret);
    }
    /**
     * 获取用户信息
     */
    public function getUserInfo(Request $request)
    {
        $token = $request->header('Authorization');
        $mobile = Cache::get($token);
        $where = [
            ['mobile', '=', $mobile],
            ['type', '<>', 1]
        ];
        $users = DB::table('user')->select('id','name','mobile','avatar','type','rank','loginip')->where($where)->get()->map(function ($value) {
            $value->avatar = 'https://'.$_SERVER["HTTP_HOST"].$value->avatar;
            return (array)$value;
        })->toArray();
        if ($users) {
            switch ($users[0]['type']) {
                case 0:
                    $users[0]['typeName'] = '普通会员';
                    break;
                case 1:
                    $users[0]['typeName'] = 'VIP会员';
                    break;
                default:
                    $users[0]['typeName'] = '会员';
            }
            $this->ret['data'] = array(
                'userInfo' => $users[0]
            );
        } else {
            $this->ret = array(
                'code' => 201,
                'data' => '',
                'msg' => '用户不存在'
            );
        }
        return json_encode($this->ret);
    }
}
