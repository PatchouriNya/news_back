<?php

namespace app\api\controller;

use think\Controller;

class NewsController extends Controller
{
    public function index(){
        $data = [];
        $redis = redis_connect();
        $ids = $redis->zRange('news:zset:id',0,-1);
        foreach ($ids as $id){
            $key = 'news:id' . $id;
            $item = $redis->hGetAll($key);
            $data[]= $item;
        }

        return api($data);

    }
}
