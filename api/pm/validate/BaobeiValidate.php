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
namespace api\pm\validate;

use think\Validate;

class BaobeiValidate extends Validate
{
    protected $rule = [
        'obj_id' => 'require',

        'client_name' => 'require',
       // 'client_mobile' => 'require|checkClientMobile|unique:pm_baobei,client_mobile',
        'client_mobile' => 'require|checkClientMobile',
        'client_time' => 'require',
        'client_num' => 'require|number|gt:0',

        'roomon' => 'require',
        'area' => 'require|number',
        'price' => 'require|number',
    ];
    protected $message = [
        'obj_id' => '报备项目必填',

        'baobei_company' => '分销公司必填',
        'client_name' => '客户名称必填',

        'client_mobile.require' => '客户电话必填',
        'client_mobile.checkClientMobile' => '客户电话格式不正确',
        //'client_mobile.unique' => '客户电话已存在',

        'client_time' => '客户到访时间必填',
        'client_num' => '客户到访人数大于0',

        'roomon.require' => '房号必填',
        'area.require' => '面积必填',
        'price.require' => '价格必填',
        'area.number' => '面积只能是数字',
        'price.number' => '价格只能是数字',
    ];

    protected $scene = [
        'add'=>['obj_id',
            'client_name', 'client_mobile', 'client_time', 'client_num'],
        'complete' => ['roomon', 'area','price'],
    ];

    public function checkClientMobile($value){
        $ret = "/^1[345789]\d{5}$/";
        return $this->checkMobile($value,$ret);
    }

    public function checkMobile($value,$reg = "/^1[345789]\d{9}$/"){
        if(preg_match($reg,$value)){
            return true;
        } else {
            return false;
        }
    }
}
