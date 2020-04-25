<?php

namespace app\admin\controller;

use think\Controller;

class BaseController extends Controller
{
    protected $redis = null;
    public function _initialize(){
        if(!session('?admin.user')){
            // 用户没有登录
            $this->redirect(url('login/index'));
        }
        $this->redis = redis_connect();
    }
}
