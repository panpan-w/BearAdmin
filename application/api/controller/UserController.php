<?php

namespace app\api\controller;

use app\common\model\User;
use app\common\validate\UserValidate;
use think\Request;

class UserController extends Controller
{

    //列表
    public function index(User $model)
    {
        $data = $model->field('id,nickname,avatar')->page($this->page, $this->limit)->select();
        return admin_success($data);
    }

    //新增
    public function save(Request $request, User $model, UserValidate $validate)
    {
        $param           = $request->param();
        $validate_result = $validate->scene('add')->check($param);
        if (!$validate_result) {
            return admin_error($validate->getError());
        }
        $result = $model::create($param);
        return $result ? admin_success() : admin_error();
    }


    //查看
    public function read($id, User $model)
    {
        $data = $model::get(function ($query) use ($id) {
            $query->where('id', $id)->field('id,nickname,avatar');
        });

        return admin_success($data);
    }


    //更新
    public function update($id, Request $request, User $model, UserValidate $validate)
    {
        $data            = $model::get($id);
        $param           = $request->param();
        $validate_result = $validate->scene('edit')->check($param);
        if (!$validate_result) {
            return admin_error($validate->getError());
        }

        $result = $data->save($param);
        return $result ? admin_success() : admin_error();
    }


    //删除
    public function delete($id, User $model)
    {
        if (count($model->noDeletionId) > 0) {
            if (is_array($id)) {
                if (array_intersect($model->noDeletionId, $id)) {
                    return admin_error('ID为' . implode(',', $model->noDeletionId) . '的数据无法删除');
                }
            } else if (in_array($id, $model->noDeletionId)) {
                return admin_error('ID为' . $id . '的数据无法删除');
            }
        }

        if ($model->softDelete) {
            $result = $model->whereIn('id', $id)->useSoftDelete('delete_time', time())->delete();
        } else {
            $result = $model->whereIn('id', $id)->delete();
        }

        return $result ? admin_success('删除成功') : admin_error('删除失败');
    }
}
