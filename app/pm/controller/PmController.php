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



class PmController extends AdminBaseController
{

    public function index()
    {
        $where = [];
        $keyword = $this->request->param('keyword');
        if ($keyword) {
            $where['obj_name'] = ['like', "%$keyword%"];
        }
        $data = Db::name('pm_obj')
            ->where($where)
            ->order("id DESC")
            ->paginate(10);
        $data->appends(['username' => $keyword]);
        $page = $data->render();
        $this->assign("page", $page);
        $this->assign("data", $data);
        return $this->fetch();
    }


    public function add()
    {
        return $this->fetch();
    }


    public function addPost()
    {
        if ($this->request->isPost()) {
            $data   = $this->request->param();
            $result = $this->validate($data, 'Pm');
            if ($result !== true) {
                $this->error($result);
            }
            $result = Db::name('pm_obj')->insert($data);
            if ($result) {
                $this->success("项目添加成功", url("Pm/index"));
            } else {
                $this->error("项目添加失败");
            }
        }

    }


    public function edit()
    {
        $id    = $this->request->param('id', 0, 'intval');
        $data = DB::name('pm_obj')->where(["id" => $id])->find();
        $this->assign($data);
        return $this->fetch();
    }


    public function editPost()
    {
        if ($this->request->isPost()) {
            $data   = $this->request->param();

            $result = $this->validate($data, 'Pm');
            if ($result !== true) {
                // 验证失败 输出错误信息
                $this->error($result);
            }

            $result = DB::name('pm_obj')->update($data);
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
            $ret = Db::name('pm_obj')->delete($ids);
        } else if(isset($param['id'])) {
            $ret = Db::name('pm_obj')->delete($param['id']);
        }
        if ( $ret !== false) {
            $this->success("删除成功！");
        } else {
            $this->error("删除失败！");
        }
    }
}
