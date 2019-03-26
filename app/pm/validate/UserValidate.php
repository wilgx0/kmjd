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

class UserValidate extends Validate
{
    protected $rule = [
        'username' => 'require',
        'mobile' => 'require|checkMobile|unique:pm_user,mobile',
        'password' => 'require',
        'user_type' => 'require',

        'company_name'=>'require',
        'company_mobile'=>'require|checkMobile',
        'leader'=>'require',

        'leader_id_photos'=>'require',
        'license_photos'=>'require',
        'open_account_photos'=>'require',


    ];
    protected $message = [
        'username.require' => '用户不能为空',
        'mobile.require' => '联系电话不能为空',
        'mobile.checkMobile' => '联系电话格式错误',
        'mobile.unique'  => '联系电话已存在',
        'password.unique'  => '密码必填',
        'user_type.require' => '申请类型不能为空',

        'company_name'=>'公司名称必填',
        'company_mobile'=>'请正确填写公司电话',
        'leader'=>'负责人必填',

        'leader_id_photos'=>'请正确上传法人身份证',
        'license_photos'=>'请正确上传营业执照',
        'open_account_photos'=>'请正确上传开户许可证',
    ];

    protected $scene = [
        'add'  => ['username', 'mobile', 'password','user_type'],
        'company'=>['username', 'mobile', 'user_type','password', 'company_name', 'company_mobile', 'leader', 'license_photos', 'open_account_photos', 'leader_id_photos'],
        'edit' => ['username', 'user_type'],
        'edit_company' =>['username', 'user_type',
            'company_name', 'company_mobile', 'leader', 'license_photos', 'open_account_photos', 'leader_id_photos'
        ],
    ];

    public function checkMobile($value){
        $myreg = "/^1[345789]\d{9}$/" ;
        $myreg_1 = "/\d{3}-\d{8}|\d{4}-\d{7}/" ;
        if(preg_match($myreg,$value) || preg_match($myreg_1,$value)){
            return true;
        } else {
            return false;
        }
    }


}
