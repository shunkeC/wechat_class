<?php
/**
 * Created by PhpStorm.
 * User: shawn
 * Date: 2018/4/21
 * Time: 18:42
 */

namespace Home\Controller;


/**
 * 微信公总号
 * Class WechatController
 * @package Home\Controller
 */
class Wechat extends Common
{
    public function entrance()
    {
        


    }

    public function verify()
    {
        $signature = $_GET['signature'];
        $timestamp = $_GET['timestamp'];
        $nonce = $_GET['nonce'];
        $token = WechatConfigController::TOKEN;
        $tmp_arr = [$token, $timestamp, $nonce];
        sort($tmp_arr, SORT_STRING);
        $tmp_str = implode($tmp_arr);
        $tmp_str = sha1($tmp_str);
        if ($tmp_str == $signature) {
            echo $_GET["echostr"];
        } else {
            echo 'error';
        }
    }

    /**
     * 获取access_token
     * @return mixed
     */
    public function getAccessToken()
    {
        $return_data = $this->httpGet("https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . WechatConfigController::APP_ID . "&secret=" . WechatConfigController::APP_SECRET);
        $data = json_decode($return_data, true);
        if (isset($data['errcode'])) {
            $this->ajaxReturn(10001, '', $data['errmsg']);
        } else return $data['access_token'];
    }

    /**
     * 发送模板消息
     * @param $openid string 用户标识
     * @param $template_id string 模板id
     * @param $data array 数据
     * @param null $jump_url string 跳转路径
     * @param bool $is_miniprogram
     * @param string $mini_appid
     * @param string $page_path
     * @return mixed
     */
    public function sendTemplate($openid, $template_id, $data, $jump_url = null, $is_miniprogram = false, $mini_appid = '', $page_path = '')
    {
        $url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=" . $this->getAccessToken();
        $post_data = array(
            'touser' => $openid,
            'template_id' => $template_id,
        );
        if ($jump_url !== null) $post_data['url'] = $jump_url;
        if ($is_miniprogram) {
            if ($mini_appid === '') $this->ajaxReturn(10001, '', 'appId必填');
            $post_data['miniprogram'] = array('appid' => $mini_appid);
            if ($page_path !== '') $post_data['miniprogram']['pagepath'] = $page_path;
        }
        $post_data['data'] = $data;
        $return_data = $this->httpPost($url, $post_data, true);
        if ($return_data['errcode'] != 0) {
            $this->ajaxReturn(10001, '', $return_data['errmsg']);
        } else return $return_data['msgid'];
    }

    /**
     * 获取用户的unionId
     * @param $openid
     * @return bool|string
     */
    public function getUnionId($openid)
    {
        $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token={$this->getAccessToken()}&openid={$openid}&lang=zh_CN";
        $return_data = $this->httpGet($url);
        if (isset($return_data['errcode'])) $this->ajaxReturn(10001, '', $return_data['errmsg']);
        return $return_data;
    }


}