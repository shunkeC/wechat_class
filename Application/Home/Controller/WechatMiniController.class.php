<?php
/**
 * Created by PhpStorm.
 * User: shawn
 * Date: 2018/4/21
 * Time: 10:54
 */

namespace Home\Controller;

/**
 * 微信小程序接口
 * Class WechatMiniController
 * @package Home\Controller
 */
class WechatMiniController extends PublicController
{
    public $OK = 0;
    public $IllegalAesKey = -41001;
    public $IllegalIv = -41002;
    public $IllegalBuffer = -41003;
    public $DecodeBase64Error = -41004;

    /**
     * 微信统一下单
     * @param int $fee
     * @param string $openid
     * @param null $out_trade_no
     * @param string $body
     * @return mixed
     */
    public function payWechat($fee = 1, $openid = 'oAfYU0V7qtFYt_oRvbt2vB13Ln1E', $out_trade_no = null, $body = '商品')
    {
        $post = array(
            'appid' => WechatConfigController::APP_ID,
            'mch_id' => WechatConfigController::MCH_ID,
            'nonce_str' => $this->nonceStr(),
            'body' => $body,
            'out_trade_no' => $out_trade_no ? $out_trade_no : $this->orderStr($openid),
            'total_fee' => $fee,
            'spbill_create_ip' => $_SERVER['REMOTE_ADDR'],
            'notify_url' => 'http://www.weixin.qq.com/wxpay/pay.php',
            'trade_type' => 'JSAPI',
            'openid' => $openid
        );
        ksort($post);
        $sign = strtoupper(md5(urldecode(http_build_query($post) . '&key=' . WechatConfigController::KEY)));
        $post['sign'] = $sign;
        $data = $this->arrayToXml($post);
        $return_data = $this->httpPost('https://api.mch.weixin.qq.com/pay/unifiedorder', $data, false);
        return json_decode(json_encode(simplexml_load_string($return_data, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
    }

    //随机32位字符串
    private function nonceStr()
    {
        $result = '';
        $str = 'QWERTYUIOPASDFGHJKLZXVBNMqwertyuioplkjhgfdsamnbvcxz';
        for ($i = 0; $i < 32; $i++) {
            $result .= $str[rand(0, 48)];
        }
        return $result;
    }

    // 伪装的订单id
    private function orderStr($openid)
    {
        $result = md5($openid . time() . rand(10, 99));
        return $result;
    }

    public function notify()
    {

    }

    /**
     * 获取用户openid
     * @return bool|mixed|string
     */
    public function getOpenid()
    {
        if (isset($_POST['code'])) {
            $code = $_POST['code'];
            $app_id = WechatConfigController::APP_ID;
            $app_secret = WechatConfigController::APP_SECRET;
            $response = $this->httpGet("https://api.weixin.qq.com/sns/jscode2session?appid={$app_id}&secret={$app_secret}&js_code={$code}&grant_type=authorization_code");
            $response = json_decode($response, true);
            if ($response['openid'] && $response['session_key']) {
                return $response;
            }
        }
        $this->ajaxReturn(10001, '', '获取信息失败');
    }

    /**
     * 获取用户unionId
     */
    public function getUnionId()
    {
        $openData = $this->getOpenid();
        $iv = $_POST['iv'];
        $encryptedData = $_POST['encryptedData'];
        $return_data = $this->decryptData($encryptedData, $iv, $openData['session_key']);
        return $return_data;
    }

    /**
     * 检验数据的真实性，并且获取解密后的明文.
     * @param $encryptedData string 加密的用户数据
     * @param $iv string 与用户数据一同返回的初始向量
     * @param $session_key
     * @return int | string ，失败返回对应的错误码
     */
    public function decryptData($encryptedData, $iv, $session_key)
    {
        if (strlen($session_key) != 24) $this->ajaxReturn(10001, '', 'encodingAesKey 非法');
        $aesKey = base64_decode($session_key);
        if (strlen($iv) != 24) $this->ajaxReturn(10001, '', 'aes 解密失败');
        $aesIV = base64_decode($iv);
        $aesCipher = base64_decode($encryptedData);
        $result = openssl_decrypt($aesCipher, "AES-128-CBC", $aesKey, 1, $aesIV);
        $dataObj = json_decode($result);
        if ($dataObj == NULL) $this->ajaxReturn(10001, '', 'base64加密失败');
        if ($dataObj->watermark->appid != WechatConfigController::APP_ID) $this->ajaxReturn(10001, '', 'base64加密失败');
        $data = $result;
        return $data;
    }


}