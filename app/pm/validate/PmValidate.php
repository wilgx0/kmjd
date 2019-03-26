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

class PmValidate extends Validate
{
    protected $rule = [
        'obj_name' => 'require',

    ];
    protected $message = [
        'obj_name.require' => '项目名称不能为空',

    ];

    protected $scene = [

    ];


}