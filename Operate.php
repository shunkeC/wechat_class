<?php
/**
 * Created by PhpStorm.
 * User: shawn
 * Date: 2018/4/21
 * Time: 18:30
 */

namespace Home\Controller;

require_once './Common.php';
require_once './Wechat.php';
require_once './WechatMini.php';

class Operate extends Common
{
    public function payWechat()
    {
        $wechat_mini = new WechatMini();
        $data = $wechat_mini->payWechat();
        $this->ajaxReturn(10000, $data);
    }


}