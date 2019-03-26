<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: pl125 <xskjs888@163.com>
// +----------------------------------------------------------------------

namespace api\pm\controller;


use cmf\controller\PmRestBaseController;
use think\Db;
use think\Validate;

//不验证权限
class UserController extends PmRestBaseController
{


    public function __construct()
    {
        parent::__construct();

    }

    //图片上传(微信小程序端)
    public function wx_upload(){

        //$this->error('图片上传失败');
        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file('wximg');
        // 移动到框架应用根目录/public/uploads/ 目录下
        if($file){
            $info = $file->move(ROOT_PATH . 'public' . DS . 'upload'. DS .'pm');
            if($info){
                $saveName = $info->getSaveName();
                $this->success('图片上传成功','/upload/pm/'.$saveName);
            }else{
                // 上传失败获取错误信息
                $this->error('图片上传失败',$file->getError());
            }
        }
    }

    //经纪人注册(web端)
    public function add()
    {
        if ($this->request->isPost()) {
            $data   = $this->request->param();
            if($data['user_type'] == 2){            //公司类型
                $result = $this->validate($data, 'User.company');
            } else {
                $result = $this->validate($data, 'User.add');
            }

            if ($result !== true) {
                $this->error($result);
            }

            //申请码的处理
            if(!empty($data['recommend_code'])){
                $role = Db::name('role')->where('recommend_code',$data['recommend_code'])->find();
                if(empty($role)){
                    $this->error("申请码错误!");
                } else {
                    $arr = $this->getRoleParntIds($role['id']);
                    if(empty($arr)){
                        $arr = $role['id'];
                    }else {
                        $arr = $role['id'].','.implode(',',$arr);
                    }
                    $data['department'] = $arr;
                }

            }
            unset( $data['recommend_code']);
            $data['password']   = cmf_password($data['password']);
            $data['create_time'] = time();
            //var_dump($data);die();
            $result = Db::name('pm_user')->insert($data);
            if ($result) {
                $this->success("用户添加成功");
            } else {
                $this->error("用户添加失败");
            }

        }

    }

    private function getRoleParntIds($id){
        static $data;
        static $ret = [];
        if(!$data){
            $data =  Db::name('role')->column('id,parent_id');
        }
        if(isset($data[$id]) && $data[$id] != 0){
            array_push($ret,$data[$id]);
            $this->getRoleParntIds($data[$id]);
        }
        return $ret;
    }


    //测试
//    public function test(){
//        $this->success("hehe!!",'test666');
//    }


    //登录
    public function login(){
        $validate = new Validate([
            'mobile' => 'require',
            'password' => 'require'
        ]);
        $validate->message([
            'mobile.require' => '请输入手机号,!',
            'password.require' => '请输入您的密码!'
        ]);

        $data = $this->request->param();
        if (!$validate->check($data)) {
            $this->error($validate->getError());
        }

        $findUserWhere = [];
        $findUserWhere['mobile'] = $data['mobile'];

        $findUser = Db::name("pm_user")->where($findUserWhere)->find();

        if (empty($findUser)) {
            $this->error("用户不存在!");
        } else {

            switch ($findUser['user_status']) {
                case 0:
                    $this->error('您的审核还未通过!');
            }

            if (!cmf_compare_password($data['password'], $findUser['password'])) {
                $this->error("密码不正确!");
            }
        }

        $allowedDeviceTypes = $this->allowedDeviceTypes;

        if (empty($data['device_type']) || !in_array($data['device_type'], $allowedDeviceTypes)) {
            $this->error("请求错误,未知设备!");
        }

        $userTokenQuery = Db::name("pm_token")
            ->where('user_id', $findUser['id'])
            ->where('device_type', $data['device_type']);
        $findUserToken  = $userTokenQuery->find();
        $currentTime    = time();
        $expireTime     = $currentTime + 24 * 3600 * 180;
        $token          = md5(uniqid()) . md5(uniqid());
        if (empty($findUserToken)) {
            $result = $userTokenQuery->insert([
                'token'       => $token,
                'user_id'     => $findUser['id'],
                'expire_time' => $expireTime,
                'create_time' => $currentTime,
                'device_type' => $data['device_type']
            ]);
        } else {
            $result = $userTokenQuery
                ->where('user_id', $findUser['id'])
                ->where('device_type', $data['device_type'])
                ->update([
                    'token'       => $token,
                    'expire_time' => $expireTime,
                    'create_time' => $currentTime
                ]);
        }

        if (empty($result)) {
            $this->error("登录失败!");
        }

        $this->success("登录成功!", ['token' => $token, 'user' => $findUser]);
    }

}