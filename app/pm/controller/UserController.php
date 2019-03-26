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
namespace app\pm\controller;

use cmf\controller\AdminBaseController;
use think\Db;


class UserController extends AdminBaseController
{


    public function index()
    {
        $where = [];
        $username = $this->request->param('username');
        $user_type = $this->request->param('user_type');
        if ($username) {
            $where['username'] = ['like', "%$username%"];
        }
        if($user_type){
            $where['user_type'] = $user_type;
        }
        $department = DB::name('role')->column('id,name');
        $users = Db::name('pm_user')
            ->where($where)
            ->order("id DESC")
            ->field('leader_id,license,open_account',true)
            ->paginate(10)
            ->each(function($item, $key) use ($department){
                $department_name = '';
                if(!empty($item['department'])){
                    $deps = explode(',',$item['department']);
                    foreach ($deps as $val){
                        if(isset($department[$val])){
                            $department_name .=$department[$val].' ';
                        }
                    }
                }
                $item['department_name'] =  rtrim($department_name,' ');
                return $item;
            });

        $users->appends(['username' => $username,'user_type'=>$user_type]);
        $page = $users->render();
        $this->assign("page", $page);
        $this->assign("users", $users);
        return $this->fetch();
    }

    public function add(){
        return $this->fetch();
    }

    public function addPost(){
        if ($this->request->isPost()) {
            $data   = $this->request->param();
            //var_dump($data);die();
            if($data['user_type'] == 2){            //公司类型
                if( isset($data['leader_id_photos']) && isset($data['license_photos']) && isset($data['open_account_photos'])){
                    $data['leader_id_photos'] =  $data['leader_id_photos'][0];
                    $data['license_photos'] =  $data['license_photos'][0];
                    $data['open_account_photos'] =  $data['open_account_photos'][0];
                }

                $result = $this->validate($data, 'User.company');
            } else {
                $result = $this->validate($data, 'User.add');
            }
            if ($result !== true) {
                $this->error($result);
            }

            $this->base64Add($data);
            $data['password'] = cmf_password($data['password']);
            $data['create_time'] = time();
            $result = Db::name('pm_user')->insert($data);
            if ($result) {
                $this->success("用户添加成功", url("user/index"));
            } else {
                $this->error("用户添加失败");
            }
        }
    }

    public function edit(){
        $id    = $this->request->param('id', 0, 'intval');
        $user = DB::name('pm_user')->where(["id" => $id])->find();
        $this->assign($user);
        return $this->fetch();
    }

    public function editPost(){

        if ($this->request->isPost()) {
            $data   = $this->request->param();


            if($data['user_type'] == 2){            //公司类型
                if( isset($data['leader_id_photos']) && isset($data['license_photos']) && isset($data['open_account_photos'])){
                    $data['leader_id_photos'] =  $data['leader_id_photos'][0];
                    $data['license_photos'] =  $data['license_photos'][0];
                    $data['open_account_photos'] =  $data['open_account_photos'][0];
                }

                $result = $this->validate($data, 'User.edit_company');
            } else {
                $result = $this->validate($data, 'User.edit');
            }

            if ($result !== true) {
                // 验证失败 输出错误信息
                $this->error($result);
            }
            if(isset($data['password'])){
                $data['password'] = cmf_password($data['password']);
            }

            $this->checkMobile($data);
            $this->base64Add($data);
            $result = DB::name('pm_user')->update($data);
            if ($result !== false) {
                $this->success("保存成功！");
            } else {
                $this->error("保存失败！");
            }
        }
    }

    public function delete()
    {
        $id = $this->request->param('id', 0, 'intval');

        if (Db::name('pm_user')->delete($id) !== false) {
            $this->success("删除成功！");
        } else {
            $this->error("删除失败！");
        }
    }

    //审核
    public function toggle()
    {
        $data                = $this->request->param();
        DB::name('pm_user')->where('id' ,$data['id'])->update(['user_status' => $data['status']]);
        $this->success("更新成功！");

    }

    //选择归属
    public function select(){
        if(request()->isAjax()){
            $id = input('id/d');
            $select_ids = input('select_ids/a');
            DB::name('pm_user')->where('id' ,$id)->update(['department' => implode(',',$select_ids)]);
            $this->success('更新成功');
        }else {
            $ids                 = $this->request->param('ids');
            $selectedIds         = explode(',', $ids);
            $data = DB::name('role')->column('id,name,remark,parent_id');
            if($data){
                $data = $this->createTree($data);
            }
            // var_dump($data);die();
            //trace($data);
            $this->assign('data', $data);
            $this->assign('selectedIds', $selectedIds);
            return $this->fetch();
        }

    }



    private function checkMobile($data){
        if(isset($data['mobile']) && $data['mobile'] != ''){
            if(!preg_match("/^(13[0-9]|14[5|7]|15[0|1|2|3|5|6|7|8|9]|18[0|1|2|3|5|6|7|8|9])\d{8}$/",$data['mobile'])){
                $this->error('联系电话格式错误');
            }
            $result = DB::name('pm_user')->where([
                'mobile'=>$data['mobile'],
                'id'=>['neq',$data['id']]
            ])->count();

            if($result > 0){
                $this->error('联系电话重复');
            }

        } else {
            $this->error('联系电话必填');
        }
    }


    private function base64Add(&$data){

        if(isset($data['leader_id_photos'])){
            if(false === strpos($data['leader_id_photos'],'data:image/jpeg;base64')){
                $data['leader_id'] = $this->base64Get($data['leader_id_photos']);
            }
            unset($data['leader_id_photos']);
            unset($data['leader_id_names']);
        }

        if(isset($data['license_photos'])){
            if(false === strpos($data['license_photos'],'data:image/jpeg;base64')){
                $data['license'] = $this->base64Get($data['license_photos']);
            }
            unset($data['license_photos']);
            unset($data['license_names']);
        }

        if(isset($data['open_account_photos'])){
            if(false === strpos($data['open_account_photos'],'data:image/jpeg;base64')){
                $data['open_account'] = $this->base64Get($data['open_account_photos']);
            }
            unset($data['open_account_photos']);
            unset($data['open_account_names']);
        }
    }

    private function base64Get($path){
        $path = './upload/'.$path;
        $ret = $this->base64EncodeImage($path);
        @unlink($path);
        return $ret ;
    }

    private function base64EncodeImage ($image_file){
        $base64_image = '';
        $image_info = getimagesize($image_file);
        $image_data = fread(fopen($image_file, 'r'), filesize($image_file));
        $base64_image = 'data:' . $image_info['mime'] . ';base64,' . chunk_split(base64_encode($image_data));
        return $base64_image;
    }



    //创建成树形结构
    private function createTree($data,$parent_id = 0,$deep = 0){
        static $ret = [];
        foreach($data as $val){
            if($parent_id == $val['parent_id']){
                $_deep = $deep;
                $val['deep'] = $_deep;
                array_push($ret,$val);
                $this->createTree($data,$val['id'],++$_deep);
            }
        }
        return $ret;
    }



    public function getVal(){
        $id = input('id/d');
        $field = input('field');
        $result  = DB::name('pm_user')->where('id',$id)->value($field);
        if($result){
            $this->success('获取成功',null,$result);
        } else {
            $this->error('获取失败');
        }

    }
}