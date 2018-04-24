<?php
/**
 * Created by PhpStorm.
 * User: shawn
 * Date: 2018/4/21
 * Time: 18:30
 */

namespace Home\Controller;


class OperateController extends PublicController
{
    public function payWechat()
    {
        $wechat_mini = new WechatMiniController();
        $data = $wechat_mini->payWechat();
        $this->ajaxReturn(10000, $data);
    }


}