<?php

namespace app\admin\controller;

use think\Controller;

class NewsController extends BaseController
{
    public function index(){
//        $url = url('create');
//        return '<a href="'.$url.'">添加新闻</a>';
        $data = [];
        $redis = redis_connect();
        $ids = $redis->zRange('news:zset:id',0,-1);
        foreach ($ids as $id){
            $key = 'news:id' . $id;
            $item = $redis->hGetAll($key);
            $data[]= $item;
        }
        return view('',compact('data'));
    }

    public function create(){
        return view();
    }

    public function store(){
        $data = input('post.');
//        dump($data);
//        exit();
        $rule = [
          'title|标题'   => 'require',
          'desn|描述'    => 'require',
          'author|作者'  => 'require',
          'body|内容'    => 'require'
        ];
        $result = $this->validate($data,$rule);
        if($result !== true){
            $this->error($result);
            exit;
        }else{
            $redis = redis_connect();
            $idKey = 'news:id';
            $id = $redis->incr($idKey);

            $hkey = 'news:id' . $id;
//            给数据源添加id
            $data['id'] = $id;
            $redis->hMSet($hkey,$data);

            $zkey = 'news:zset:id';
            $redis->zAdd($zkey,$id,$id);

            $this->success('添加成功','news/index');
        }

    }

//    删除

    public function del(){
        $redis = redis_connect();
        $id = input('id');
        $hKey = 'news:id:' . $id;
        $redis -> del($hKey);

        $zKey = 'news:zset:id';
        $redis -> zRem($zKey,$id);

        return ['status' => 0,'msg' =>'删除成功'];

    }

    public function delall(){
        $redis = redis_connect();

        $zKey = 'news:zset:id';
        $dead = $redis ->exists($zKey);
        if(!$dead){
            $this->error('没有需要删除的数据');
        }
        $arr = $redis->zRange($zKey,0,-1);
        if(!is_null($arr)){
            foreach ($arr as $id){
                $hKey = 'news:id' . $id;
                $redis -> del($hKey);
            }
            $redis -> del('news:id');
        }
        $res = $redis->zRemRangeByRank($zKey,0,-1);
        if($res){
            $this->success('删除全部成功','news/index');
        }
    }

    public function edit($id){
        $redis = redis_connect();
        $data = $redis->hGetAll('news:id'.$id);
        $data['id'] = $id;
        return view('',compact('data'));
    }

    public function update(){
        $data = input('post.');
        $rule = [
            'title|标题'   => 'require',
            'desn|描述'    => 'require',
            'author|作者'  => 'require',
            'body|内容'    => 'require'
        ];
        $result = $this->validate($data,$rule);
        if($result !== true){
            $this->error($result);
            exit;
        }else{
            $redis = redis_connect();
            $hkey = 'news:id' . $data['id'];

            $redis->hMSet($hkey,$data);

            $this->success('修改成功','news/index');
        }
    }
}

