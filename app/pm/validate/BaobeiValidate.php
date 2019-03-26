<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小夏 < 449134904@qq.com>
// +----------------------------------------------------------------------
namespace app\pm\validate;

use think\Validate;

class BaobeiValidate extends Validate
{
    protected $rule = [
        'obj_id' => 'require',
        'client_name' => 'require',
        'client_mobile' => 'require|checkClientMobile',
        'client_time' => 'require',
        'client_num' => 'require|number|gt:0',

        'roomon'=>'require',
        'area'=>'require|number',
        'price'=>'require|number',
        'brokerage'=>'require|number',

    ];
    protected $message = [
        'obj_id' => '报备项目必填',

        'client_name' => '客户名称必填',
        'client_mobile' => '请正确填写客户电话',
        'client_time' => '客户到访时间必填',
        'client_num' => '客户到访人数必须大于0',

        'roomon'=>'房号必填',
        'area'=>'面积必填且只能是数字',
        'price'=>'价格必填且只能是数字',
        'brokerage'=>'佣金必填且只能是数字',
    ];

    protected $scene = [
        'edit'=>['obj_id','client_name','client_mobile','client_time','client_num'],
        'deal'=>['obj_id','client_name','client_mobile','client_time','client_num',
            'roomon','area','price','brokerage'],
    ];

    public function checkClientMobile($value){
        $reg = "/^1[34578]\d{5}$/";
        return $this->checkMobile($value,$reg);
    }

    public function checkMobile($value,$reg = "/^1[34578]\d{9}$/"){
        if(preg_match($reg,$value)){
            return true;
        } else {
            return false;
        }
    }
}