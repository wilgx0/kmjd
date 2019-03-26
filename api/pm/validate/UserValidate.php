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

class UserValidate extends Validate
{
    protected $rule = [
        'username' => 'require',
        'mobile' => 'require|checkMobile|unique:pm_user,mobile',
        'user_type' => 'require',
        'password' => 'require',

        'company_name' => 'require',
        'leader' => 'require',
        'company_mobile' => 'require|checkMobile',

        'leader_id' => 'require',
        'open_account' => 'require',
        'license' => 'require'

//        'leader_id' => 'require|check64Base',
//        'open_account' => 'require|check64Base',
//        'license' => 'require|check64Base',


    ];
    protected $message = [
        'username.require' => '用户不能为空',
        'mobile.require' => '联系电话不能为空',
        'mobile.checkMobile' => '联系电话格式错误',
        'mobile.unique' => '联系电话已存在',
        'user_type.require' => '申请类型不能为空',
        'password.require' => '密码不能为空',

        'company_name' => '公司名称必填',
        'company_mobile' => '请正确填写公司电话',
        'leader' => '负责人必填',

        'leader_id.require' => '法人身份证必填',
        'open_account.require' => '开户许可证必填',
        'license.require' => '传营业执照必填',

        'leader_id.check64Base' => '法人身份证大小限制2m',
        'open_account.check64Base' => '开户许可证大小限制2m',
        'license.check64Base' => '传营业执照大小限制2m',

    ];

    protected $scene = [
        'add' => ['username', 'mobile', 'user_type', 'password'],
        'company' => ['username', 'mobile', 'user_type', 'password', 'company_name', 'leader', 'license', 'open_account', 'company_mobile', 'leader_id'],
        'edit' => ['username', 'user_type'],
    ];

    public function checkMobile($value)
    {
        $myreg = "/^1[345789]\d{9}$/";
        $myreg_1 = "/\d{3}-\d{8}|\d{4}-\d{7}/";
        if (preg_match($myreg, $value) || preg_match($myreg_1, $value)) {
            return true;
        } else {
            return false;
        }
    }


    public function check64Base($value)
    {
       if( false === strpos($value, 'data:image/jpeg;base64')){
           return false;
       } else {
          $size = file_get_contents($value);
          $size = strlen($size)/1024;
          if($size > (1024*2)){             //大小限制为2m
              return false;
          } else {
              return true;
          }
       }
    }
}
