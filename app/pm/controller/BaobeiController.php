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



class BaobeiController extends AdminBaseController
{

    public function index()
    {
        $where = [];
        $keyword = $this->request->param('keyword');
        $status = $this->request->param('status');
        if ($keyword) {
            $where['c.username'] = ['like', "%$keyword%"];
        }

        if (in_array($status,['0','1'])) {
            $where['a.status'] = $status;
            if($status == 0){
                $where['a.good_status'] = 1;
            } else if($status == 1){
                $where['a.closing'] = 0;
            }
        } else if($status == '-1'){
            $where['a.closing'] = 1;
        } else if($status == '-2'){
            $where['a.good_status'] = 0;
        }

        $data = Db::name('pm_baobei')
            ->alias('a')
            ->join('__PM_OBJ__ b','a.obj_id = b.id')
            ->join('__PM_USER__ c','a.user_id = c.id')
            ->where($where)
            ->order("a.id DESC")
            ->field('a.*,b.obj_name,c.username as u_name,c.mobile as u_mobile,c.company_name as u_company')
            ->paginate(10)
            ->each(function($item, $key){
                $item['client_mobile'] = substr($item['client_mobile'],0,3).'****'.substr($item['client_mobile'],-4);
                return $item;
            });
        //var_dump(Db()->getLastSql());die();
        $data->appends(['keyword' => $keyword,'status'=>$status]);
        $page = $data->render();
        $this->assign("page", $page);
        $this->assign("data", $data);
        return $this->fetch();
    }





    public function edit()
    {
        $id    = $this->request->param('id', 0, 'intval');
        $data = DB::name('pm_baobei')->where(["id" => $id])->find();
        $pm_obj = DB::name('pm_obj')->select();
        $this->assign('pm_obj',$pm_obj);
        $this->assign($data);
        return $this->fetch();
    }


    public function editPost()
    {
        if ($this->request->isPost()) {
            $data   = $this->request->param();
            $findData = DB::name('pm_baobei')->where('id',$data['id'])->find();
            if(empty($findData)) {
                $this->error("没有找到要修改的数据");
            } else if($findData['closing'] == 1) {
                $this->error("已结佣数据，不能修改!");
            }

            $saveData = [
                'id'=> $data['id'],
                'obj_id' => $data['obj_id'],
                'client_name' => $data['client_name'],
                'client_mobile' => $data['client_mobile'],
                'client_time' => $data['client_time'],
                'client_num' => $data['client_num'],
                'accompany' => $data['accompany'],
                'good_status' => $data['good_status'],      //是否审核
                'valid' => $data['valid'],              //是否有效

                'status'=>$data['status'],              //是否成交
                'roomon'=>$data['roomon'],
                'area'=>$data['area'],
                'price'=>$data['price'],
                'brokerage'=>$data['brokerage'],
                'closing'=>$data['closing'],            //是否结佣
            ];

            $validateStr = 'Baobei.edit';

            if($saveData['good_status'] == 0){      //是否审核
                $saveData['closing'] = 0;
                $saveData['status'] = 0;
            }

            if($saveData['closing'] == 1){          //已结佣
                $saveData['status'] = 1;
            }

            if($saveData['status'] == 1){           //已成交
                $saveData['good_status'] = 1;
                $validateStr = 'Baobei.deal';
            } else if($saveData['status'] == 0){  //未成交
                $saveData['roomon'] = null;
                $saveData['area'] = null;
                $saveData['price'] = null;
                $saveData['brokerage'] = null;
            }

            $result = $this->validate($saveData, $validateStr);
            if ($result !== true) {
                // 验证失败 输出错误信息
                $this->error($result);
            }
            $result = DB::name('pm_baobei')->update($saveData);

            if ($result !== false) {
                $this->success("保存成功！");
            } else {
                $this->error("保存失败！");
            }
        }
    }


    public function delete()
    {
        $param  = $this->request->param();
        $ret = false;
        if (isset($param['ids'])) {
            $ids     = $this->request->param('ids/a');
            $ret = Db::name('pm_baobei')->delete($ids);
        } else if(isset($param['id'])) {
            $ret = Db::name('pm_baobei')->delete($param['id']);
        }
        if ( $ret !== false) {
            $this->success("删除成功！");
        } else {
            $this->error("删除失败！");
        }
    }
}
