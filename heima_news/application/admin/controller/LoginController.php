<?php

namespace app\admin\controller;

use think\Controller;

class LoginController extends Controller
{
    //后台登录显示
    public function index(){
        return view('');
    }

    public function login(){
        $username = input('username');
        $password = input('password');

        $loginKey = 'user:' . $username;
        $redis = redis_connect();
        if(!$redis->exists($loginKey)){
            return $this->error('登录失败');
        }

        $pwd = $redis->get($loginKey);
        $key = 'cs'.$username;
        if($pwd != $password){

            if(!$redis->exists($key)){
                $redis->set($key,0,86400);
            }

            $times = $redis->incr($key);
//            $time = $redis->ttl($value);
//            dump($times);
            if($times > 3){
                return $this->error('尝试次数太多,请一天后再试');
            }
            return $this->error('登录失败');
        }
            $times = $redis->get($key);
//            dump($times);
            if($times > 3){
                return $this->error('尝试次数太多,请一天后再试');
            }
        session('admin.user', $username);
        $redis -> del($key);
        // 成功跳转
        return $this->success('登陆成功',url('news/index'));
    }
}
