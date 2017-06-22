<?php

namespace app\index\controller;

use \think\Controller;
use app\index\model\Message;
use app\index\model\Users;
use think\Session;

class Index extends Controller {   
    //打开主页判断是否登陆,如果无登陆则跳转到登录页面
    public function index() {
        if (Session::has('user')) {
            $user = Users::get(Session::get('user')->id);
            $user->online = 1;
            $user->save();
            return $this->fetch();
        } else {
            $view = new Index();
            return $view->fetch('User/login');
        }
    }
    //将客户端发送的消息保存到服务器
    public function sendMessage() {
        $message = new Message;
        $message->senderID = $_POST["senderID"];
        $message->getterID = $_POST["getterID"];
        $message->content = $_POST["content"];
        $message->sendTime = $_POST["sendTime"];
        $message->save();
        return "success";
    }
    //从服务器获取消息,并用以json格式返回
    public function getMessage() {
        $senderID = $_GET['senderID'];
        $sender = Users::get($senderID);
        //如果用户离线,则发送离线消息给客户端
        if ($sender->online == 1) {
            $mes = new Message();
            $messages = $mes->where('type', 0)->where('senderID', $senderID)->order('sendTime asc')->select();
            $mes->where('type', 0)->where('senderID', $senderID)->update(['type' => 1]);
            return $messages;
        } else {
            return "outline";
        }
    }
    //获取在线用户列表,并以json格式返回
    public function getUserList() {
        $id=Session::get('user')->id;
        $userList = new Users();
        $user=Users::get($id);
        $user->lastTime=time();
        $user->save();
        $list = $userList->where('online', '1')->where('id', '<>', $id)->where('lastTime','>',time()-10)->select();
        $userList->where('lastTime','<',time()-10)->update(['online' => 0]);
        return $list;
    }
    //获取未读消息条数
    public function getMesNumber() {
        $user = Session::get('user');
        $mes = new Message();
        $mesNunber = $mes->where('senderID', $_GET['id'])->where('getterID', Session::get('user')->id)->where('type', '0')->count();
        return $mesNunber;
    }
    //将用户状态改为在线
    public function  online(){
         if (Session::has('user')) {
            $user = Users::get(['id' => Session::get('user')->id]);
            $user->online = 1;
            $user->save();
        }
        return 0;
    }
    //将用户状态改为离线
    public function  outline(){
         if (Session::has('user')) {
            $user = Users::get(['id' => Session::get('user')->id]);
            $user->online = 0;
            $user->save();
        }
        return 0;
    }

}
