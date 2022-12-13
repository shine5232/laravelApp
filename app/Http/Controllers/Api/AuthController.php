<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\Wx\WxBizDataCryptController;
use Intervention\Image\Facades\Image;

class AuthController extends Controller{
    protected $appid = 'wx50e36d93a1849688';//小程序appid
    protected $secret = '1ae7be35055aba992c748003cde1f882';//小程序秘钥
    protected $baseApiHost = 'https://h5demo.tts168.com.cn/wxapi';//项目接口请求地址
    /* protected $ret = array(
        'code' => 200,
        'data' => array(),
        'msg' => 'ok'
    ); */
    /**
     * 获取Token
     */
    public function getToken(Request $request){
        $client_secret = $request->post('client_secret'); //0578849928ac4e4db08aabd9af183591;
        $client_id = $request->post('client_id'); //c95018bd25d74ea08ae1351d591ef494;
        $token = Cache::get($client_id);
        if ($token) {
            $this->ret['data'] = array(
                'token' => $token
            );
        } else {
            $url = 'https://openapi.data-baker.com/oauth/2.0/token?grant_type=client_credentials&client_secret=' . $client_secret . '&client_id=' . $client_id;
            $result = curl_request($url);
            $result_array = json_decode($result, true);
            Cache::put($client_id, $result_array['access_token'], $result_array['expires_in']);
            $this->ret['data'] = array(
                'token' => $result_array['access_token']
            );
        }
        return json_encode($this->ret);
    }
    /**
     * 小程序通过code获取session_key
     */
    public function code(Request $request){
        $code = $request->post('code');
        $result_array = $this->getSessionKey($code);
        if (!isset($result_array['errcode']) && $result_array) {
            $this->ret['data'] = array(
                'isNew' => 0,
                'openid'=> $result_array['openid'],
                'access_token' => $result_array['session_key'],
            );
        } else {
            $this->ret['code'] = 2;
            $this->ret['msg'] = '系统繁忙，请稍候再试';
        }
        return json_encode($this->ret);
    }
    /**
     * 小程序获取用户信息
     */
    public function getUserInfo(Request $request){
        $openId = $request->post('openId');
        $session_key = Cache::get($openId.'_SessionKey');
        if ($session_key) {
            $iv = $request->post('iv');
            $encryptedData = $request->post('encryptedData');
            $pc = new WxBizDataCryptController($this->appid, $session_key);
            $errCode = $pc->decryptData($encryptedData, $iv, $datas);
            if ($errCode == 0) {
                $resData = json_decode($datas, true);
                $this->ret['data'] = array(
                    'userId' => $openId,
                    'isNew' => 0,
                    'avatarUrl' => $resData['avatarUrl'],
                    'nickName' =>   $resData['nickName'],
                    'openId' => $openId
                );
            } else {
                $this->ret['code'] = $errCode;
                $this->ret['msg'] = '请求失败，解密信息失败';
            }
        } else {
            $this->ret['code'] = 2;
            $this->ret['msg'] = '请求失败，session_key过期';
        }
        return json_encode($this->ret);
    }
    /**
     * 小程序获取用户手机号
     */
    public function mobileAuth(Request $request){
        $openId = $request->post('openId');
        $session_key = Cache::get($openId.'_SessionKey');
        if ($session_key) {
            $iv = $request->post('iv');
            $encryptedData = $request->post('encryptedData');
            $pc = new WxBizDataCryptController($this->appid, $session_key);
            $errCode = $pc->decryptData($encryptedData, $iv, $datas);
            if ($errCode == 0) {
                $resData = json_decode($datas, true);
                $mobile = $resData['phoneNumber'];
                $this->ret['data'] = array(
                    'mobile' => $mobile,
                );
            } else {
                $this->ret['code'] = $errCode;
                $this->ret['msg'] = '请求失败，解密信息失败';
            }
        } else {
            $this->ret['code'] = 2;
            $this->ret['msg'] = '请求失败，session_key过期';
        }
        return json_encode($this->ret);
    }
    /**
     * 小程序获取session_key
     */
    public function getSessionKey($code){
        $url = 'https://api.weixin.qq.com/sns/jscode2session?appid=' . $this->appid . '&secret=' . $this->secret . '&js_code=' . $code . '&grant_type=authorization_code';
        $result = curl_request($url);
        $result_array = json_decode($result, true);
        if (!isset($result_array['errcode'])) {
            Cache::put($result_array['openid'] . '_SessionKey', $result_array['session_key']);
            return $result_array;
        } else {
            return false;
        }
    }
    /**
     * 小程序获取access_token
     */
    public function getAccessToken(){
        $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . $this->appid . '&secret=' . $this->secret;
        $result = curl_request($url);
        $result_array = json_decode($result, true);
        if (!isset($result_array['errcode'])) {
            return $result_array['access_token'];
        } else {
            return false;
        }
    }
    /**
     * 获取声音模型数据
     */
    public function getUserModelLis(Request $request){
        $mobile = $request->post('mobile');
        $userId = $request->post('userId');
        $url = $this->baseApiHost.'/getModel.php?user='.$userId;
        $result = curl_request($url);
        $result_array = json_decode($result, true);
        $this->ret['data'] = array(
            'userModelInfoList' => [], //声音模型
        );
        if(!empty($result_array) && $result_array['code'] == '0'){
            foreach($result_array['data']['models'] as $key=>$v){
                if($v['status']=='modeling'){
                    $result_array['data']['models'][$key]['status'] = 4;
                }else if($v['status']=='modeled'){
                    $result_array['data']['models'][$key]['status'] = 8;
                }
                $result_array['data']['models'][$key]['defaultText'] = '我是'.$v['rolename'].'：请输入您要转换的文字';
            }
            $this->ret['data'] = array(
                'userModelInfoList' => $result_array['data']['models'], //声音模型
            );
        }
        return json_encode($this->ret);
    }
    /**
     * 获取建模Token
     */
    public function getModelToken(Request $request){
        $userId = $request->post('userId');
        $url = $this->baseApiHost.'/getModelToken.php';
        $result = curl_request($url);
        $result_array = json_decode($result, true);
        if ($result_array['code'] == 0) {
            $this->ret['data'] = array(
                'data' => $result_array['data']['access_token']
            );
        } else {
            $this->ret['code'] = 10100;
        }
        return json_encode($this->ret);
    }
    /**
     * 用户个性语音合成转换
     */
    public function ttsPersonal(Request $request){
        $userId = $request->post('userId');
        $data = array(
            'voice_name' => $request->post('voice_name'),
            'text'  =>  $request->post('text'),
            'speed' => $request->post('speed'),
            'volume' => $request->post('volume'),
            'language' => 'zh',
            'domain' => 1,
            'access_token' => $request->post('access_token'),
        );
        $url = 'https://openapi.data-baker.com/tts_personal?access_token='.$data['access_token'].'&voice_name='.$data['voice_name'].'&text='.$data['text'].'&speed='.$data['speed'].'&volume='.$data['volume'].'&language='.$data['language'].'&domain='.$data['domain'];
        $result = curl_request($url);
        $result_array = json_decode($result, true);
        if($result_array && $result_array['err_no']){
            $this->ret['code'] = $result_array['err_no'];
            $this->ret['msg'] = $result_array['err_msg'];
        }else{
            $fileName = $userId.'_'.time().'.mp3';
            $res = Storage::disk('vioce')->put($fileName,$result);
            if($res){
                $this->ret['data'] = array(
                    'ossUrl' => 'https://'.$_SERVER["HTTP_HOST"]."/vioce/".date('Ymd').'/'.$fileName,
                );
            }else{
                $this->ret['code'] = 1000;
            }
        }
        return json_encode($this->ret);
    }
    
    /**
     * 小程序校验内容安全性 
     */
    public function checkContent(Request $request){
        $access_token = $this->getAccessToken();
        $content = $request->post('content');
        $openid = $request->post('openid');
        $url = 'https://api.weixin.qq.com/wxa/msg_sec_check?access_token='.$access_token;
        $data = array(
            'content' => $content,
            'openid' => $openid,
            'version' => 2,
            'scene'=>2,
        );
        $result = curl_request($url,json_encode($data),'POST',array('content-type: application/json'));
        $result_array = json_decode($result, true);
        if (!$result_array) {
            $this->ret['code'] = 100;
            $this->ret['data'] = '请求失败';
            $this->ret['msg'] = 'fail';
        } else {
            if($result_array['errcode'] == 0 && $result_array['result']['suggest'] == 'pass'){
                $this->ret['data'] = 'pass';
            }else{
                $this->ret['data'] = '内容含有敏感或违规信息';
                $this->ret['msg'] = 'fail';
            }
        }
        return json_encode($this->ret);
    }
    /**
     * 个人小程序码
     */
    public function getOwnCodeQr(Request $request){
        $openid = $request->post('openid');
        $canvasUrl = $request->post('avatar');
        $nickName = $request->post('nickname');
        if(empty($openid) || empty($canvasUrl) || empty($nickName)){
            $this->ret['code'] = 1000;
            $this->ret['msg'] = '缺少参数openid|avatar';
            return json_encode($this->ret);
        }
        $accessToken = $this->getAccessToken();
        $url = 'https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=' . $accessToken;
        $data = array(
            'scene' => $openid,
            'page' => 'pages/mine/mine',
            'is_hyaline' => true,
        );
        $result = curl_request($url,json_encode($data),'POST',array('content-type: application/json'));
        $result_array = json_decode($result, true);
        if (!$result_array) {
            $path = '/file/'.date('Ymd');
            $file_path = storage_path().$path;
            if(!is_dir($file_path)){
                mkdir($file_path);
            }
            $file = $file_path.'/'.$openid.'_org.png';
            //保存小程序码
            Image::make($result)->save($file)->destroy();
            //创建画布
            $canvas = Image::canvas(380,600);
            //插入背景图
            $canvas->fill(storage_path().'/app/bg.jpg');
            //生成圆形头像
            $avatarCicle = wxAvatarCircle($canvasUrl,$openid);
            //获取圆形头像资源
            $avatar = Image::make($avatarCicle);
            //修改用户头像尺寸大小
            $avatar->resize(50,50);
            //将用户头像绘制到画布顶部左侧位置，且左边距、上边距为20像素
            $canvas->insert($avatar,'top-left',20,20);
            //将用户昵称绘制到画布
            $canvas->text($nickName, 120, 30, function($font) {
                //设置字体
                $font->file(storage_path().'/app/GB2312.ttf');
                //设置字体大小
                $font->size(18);
                //设置字体颜色
                $font->color('#fdf6e3');
                //文本水平对齐方式
                $font->align('center');
                //文本垂直对齐方式
                $font->valign('top');
            });
            //获取小程序码资源
            $qr = Image::make(storage_path().'/file/20220110/1641827089.png');
            //修改小程序码尺寸大小
            $qr->resize(100,100);
            //将小程序码绘制到画布右下方位置，且下边距为20像素
            $canvas->insert($qr,'bottom-right',20,20);
            //设置最终绘制的图片的保存路径
            $files = $file_path.'/'.$openid.'.png';
            //保存最终绘制的图片
            $canvas->save($files);
            //关闭打开的资源
            $canvas->destroy();
            $qr->destroy();
            $avatar->destroy();
            //返回数据
            $this->ret['data'] = array(
                'url' => $path.'/'.$openid.'.png'
            );
        } else {
            $this->ret['code'] = $result_array['errcode'];
            $this->ret['data'] = $result_array['errmsg'];
            $this->ret['msg'] = 'fail';
        }
        return json_encode($this->ret);
    }
    /**
     * 语音合成发音人列表
     */
    public function getVoicePersonLis(Request $request){
        $openid = $request->post('openid');
        $data = array(
        [
            'scene' => array(
                'name' => '普通合成',
                'voice' => array(
                    [
                        'aliasName' => '娇娇',
                        'code' => 'Jiaojiao',
                        'imageUrl' => 'https://h5demo.tts168.com.cn/res/static/vc/jingjing@2x.png',
                        'id' => 1,
                        'type' => 0,
                        'language' => '支持纯中文',
                        'defaultText' => '您好，我们是中国电信的，请问是张三先生吗？'
                    ],[
                        'aliasName' => '天天',
                        'code' => 'Tiantian',
                        'imageUrl' => 'https://h5demo.tts168.com.cn/res/static/vc/tiantian@2x.png',
                        'id' => 2,
                        'type' => 1,
                        'language' => '支持纯中文',
                        'defaultText' => '您好，亲，能不能方便说一下您的身高、体重及年龄？'
                    ],[
                        'aliasName' => '果子',
                        'code' => 'Guozi',
                        'imageUrl' => 'https://h5demo.tts168.com.cn/res/static/vc/jingjing@2x.png',
                        'id' => 3,
                        'type' => 2,
                        'language' => '支持纯中文',
                        'defaultText' => '三月下旬，南方正是梅雨季节，天空总是灰蒙蒙的一片。'
                    ]
                )
            )
        ],[
            'scene' => array(
                'name' => '新闻播报',
                'voice' => array(
                    [
                        'aliasName' => '阿科',
                        'code' => 'Ake',
                        'imageUrl' => 'https://h5demo.tts168.com.cn/res/static/vc/tiantian@2x.png',
                        'id' => 1,
                        'type' => 1,
                        'language' => '支持纯中文',
                        'defaultText' => '随着多个国家和地区代表团陆续抵达日本...'
                    ],[
                        'aliasName' => '小金',
                        'code' => 'Xiaojin',
                        'imageUrl' => 'https://h5demo.tts168.com.cn/res/static/vc/tiantian@2x.png',
                        'id' => 2,
                        'type' => 1,
                        'language' => '支持纯中文',
                        'defaultText' => '据日本共同社报道，在日本...'
                    ]
                )
            )
        ]);
        $this->ret['data'] = $data;
        return json_encode($this->ret);
    }
    /**
     * 获取语音合成token
     */
    public function getTtsToken(){
        $url = $this->baseApiHost.'/getTtsToken.php';
        $result = curl_request($url);
        $result_array = json_decode($result, true);
        if ($result_array['code'] == 0) {
            return $result_array['data']['access_token'];
        } else {
            return false;
        }
    }
    /**
     * 在线语音合成
     */
    public function tts(Request $request){
        $access_token = $this->getTtsToken();
        $userId = $request->post('openid');
        if(!$access_token){
            $this->ret['code'] = 1000;
            $this->ret['msg'] = 'access_token fail';
            return json_encode($this->ret);
        }
        $data = array(
            'voice_name' => $request->post('voiceName'),
            'text'  =>  $request->post('text'),
            'speed' => $request->post('speed'),
            'volume' => $request->post('volume'),
            'language' => 'zh',
            'domain' => 1,
            'access_token' => $access_token,
        );
        $url = 'https://openapi.data-baker.com/tts?access_token='.$data['access_token'].'&voice_name='.$data['voice_name'].'&text='.$data['text'].'&speed='.$data['speed'].'&volume='.$data['volume'].'&language='.$data['language'].'&domain='.$data['domain'];
        $result = curl_request($url);
        $result_array = json_decode($result, true);
        if($result_array && $result_array['err_no']){
            $this->ret['code'] = $result_array['err_no'];
            $this->ret['msg'] = $result_array['err_msg'];
        }else{
            $fileName = $userId.'_tts_'.time().'.mp3';
            $res = Storage::disk('vioce')->put($fileName,$result);
            if($res){
                $this->ret['data'] = array(
                    'ossUrl' => 'https://'.$_SERVER["HTTP_HOST"]."/vioce/".date('Ymd').'/'.$fileName,
                );
            }else{
                $this->ret['code'] = 1000;
            }
        }
        return json_encode($this->ret);
    }
}
