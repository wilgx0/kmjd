<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: pl125 <xskjs888@163.com>
// +----------------------------------------------------------------------

namespace api\pm\controller;


use cmf\controller\PmRestUserBaseController;
use think\Db;
use think\Validate;
use api\portal\model\PortalPostModel;

//需要验证权限
class BaobeiController extends PmRestUserBaseController
{


    public function __construct()
    {
        parent::__construct();

    }

    //获取报备项目
    public function getObj()
    {
        $result = Db::name('pm_obj')->select();
        if ($result) {
            $this->success("数据获取成功", $result);
        } else {
            $this->error("数据获取失败");
        }

    }

    //报备添加
    public function add()
    {
        if ($this->request->isPost()) {
            $user = $this->user;
            if($user['user_type'] == 3){
                $this->error('内部人员不能报备');
            }
            $data = $this->request->param();
            $result = $this->validate($data, 'Baobei.add');
            if ($result !== true) {
                $this->error($result);
            }

            $data['create_time'] = time();
            $data['user_id'] = $this->userId;
            $result = Db::name('pm_baobei')->insert($data);
            if ($result) {
                $this->success("项目报备添加成功");
            } else {
                $this->error("项目报备添加失败");
            }

        }
    }

   public function checkClientMobile(){
        $mobile = input('client_mobile');
        if($this->_checkClientMobile($mobile)){
            $this->error('号码重复');
        } else {
            $this->success('号码唯一');
        }
   }

    /**
     * 检查客户号码是否重复
     */
     private function _checkClientMobile($v){
        $ret = Db::name('pm_baobei')->where('client_mobile',$v)->count();
        return $ret > 0 ? true : false;
    }

    //获取经纪人数据
    public function get_user_list()
    {
        $page = input('page/d', 1);
        $where = [];
        $user = $this->user;
        if($user['user_type'] == 3){    //内部人员
            if(!empty($user['department'])){
                $where = '';
                $department = explode(',',$user['department']);
                foreach ($department as $val){
                    $where .= 'OR FIND_IN_SET('.$val.',department) ';
                }
                $where = ltrim($where,'OR');
            }
        } else {
            $this->error("非内部人员无法查看!");
        }

        $department = DB::name('role')->column('id,name');

        $result = Db::name('pm_user')
            ->where('user_type' ,'neq',3)
            ->where($where)
            ->order('id')
            ->page($page, 7)
            ->select()
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
        // var_dump(db()->getLastSql());die();
        if ($result) {
            $this->success("数据获取成功", $result);
        } else {
            $this->error("数据获取失败");
        }
    }

    //获取报备客户数据
    public function get_list()
    {

        $page = input('page/d', 1);
        $where = [];
        $user = $this->user;
        if($user['user_type'] == 3){    //内部人员
            if(!empty($user['department'])){
                $where = '';
                $department = explode(',',$user['department']);
                foreach ($department as $val){
                    $where .= ' OR FIND_IN_SET('.$val.',c.department) ';
                }
                $where = ltrim($where,' OR');
            }
        } else {
            $where['a.user_id'] = $this->userId;
        }

        $department = DB::name('role')->column('id,name');

        $result = Db::name('pm_baobei')
            ->alias('a')
            ->join('__PM_OBJ__ b','a.obj_id = b.id','left')
            ->join('__PM_USER__ c','a.user_id = c.id')
            ->where($where)
            ->order('a.id')
            ->field('a.*,b.obj_name,b.obj_describe,
                c.username as u_name,c.mobile as u_mobile,c.company_name as u_company,c.department as department')
            ->page($page, 7)
            ->select()
            ->each(function($item, $key)use ($department){
                //处理电话号码
                $item['client_mobile'] = substr($item['client_mobile'],0,3).'****'.substr($item['client_mobile'],-4);

                //处理部门归属
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
       // var_dump(db()->getLastSql());die();
        if ($result) {
            $this->success("数据获取成功", $result);
        } else {
            $this->error("数据获取失败");
        }
    }

    /**
     * 获取注册用户文章
     */
    public function get_user_article(){
        $type = $this->user['user_type'];
        $params                       = [];
        if($type == 1){             //个人
            $params['id']                 = 64;
        } else if($type == 2){          //公司
            $params['id']                 = 65;
        } else {
            $this->error("抱歉您没有查看权限");
        }

        $params['where']['post_type'] = 2;
        $PortalPostModel = new PortalPostModel();
        $data   = $PortalPostModel->getDatas($params);
        $this->success('请求成功!', $data);
    }


    //记录带看时间
//    public function accompany_action()
//    {
//        $id = input('id/d', 0);
//        $accompany = input('accompany', '');
//        if ($accompany == '') {
//            $this->error("带看时间必填");
//        }
//        $result = Db::name('pm_baobei')
//            ->where('id', $id)
//            ->find();
//        if ($result) {
//            if ($result['accompany'] == '') {
//                Db::name('pm_baobei')
//                    ->where('id', $id)
//                    ->update(['accompany' => $accompany]);
//                $this->success("操作成功");
//            } else {
//                $this->error("已经记录过带看时间");
//            }
//        } else {
//            $this->error("没有找到数据");
//        }
//    }

    //交易完成
//    public function complete_action()
//    {
//        $data = $this->request->param();
//        $result = $this->validate($data, 'Baobei.complete');
//        if ($result !== true) {
//            $this->error($result);
//        }
//        $result = Db::name('pm_baobei')
//            ->where('id', $data['id'])
//            ->find();
//        if ($result) {
//            if ($result['status'] == 1) {
//                $this->error("交易已经完成");
//            } else {
//                Db::name('pm_baobei')
//                    ->where('id', $data['id'])
//                    ->update([
//                        'roomon' => $data['roomon'],
//                        'area' => $data['area'],
//                        'price' => $data['price'],
//                        'status' => 1,
//                    ]);
//                $this->success("操作成功");
//
//            }
//        } else {
//            $this->error("没有找到数据");
//        }
//    }

}