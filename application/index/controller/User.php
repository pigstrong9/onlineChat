<?php

namespace app\index\controller;

use think\Controller;
use app\index\model\Users;
use \think\Session;
use think\View;

class User extends Controller {

    //返回登录页面
    public function login() {
        $view = new View;
        return $view->fetch();
    }

    //执行注册操作,将客户端发送的消息保存如数据库
    public function registerAction() {
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $sex = $_POST['sex'];
        $user = new Users;
        //判断用户名是否存在,如果存在,向客户端返回1
        if ($user->where('username', $username)->select() == null) {
            //判断电子邮箱是否存在,如果存在,向客户的返回2
            if ($user->where('email', $email)->select() == null) {
                $user->username = $username;
                $user->email = $email;
                $user->password = $password;
                $user->sex = $sex;
                $user->save();
                return 0;
            } else {
                return 2;
            }
        } else {
            return 1;
        }
    }

    //执行登陆操作,将客户端发送的用户记录与数据库进行校对
    public function loginAction() {
        $email = $_POST['email'];
        $password = $_POST['password'];
        $user = new Users;
        //判断邮箱是否存在,如果不存在返回1
        if ($user->where('email', $email)->select()) {
            //判断邮箱与密码是否匹配,如果匹配不到则返回2
            if ($user->where('password', $password)->select()) {
                $user = Users::get(['email' => $email]);
                Session::set('user', $user);
                return 0;
            } else {
                return 2;
            }
        } else {
            return 1;
        }
    }

    //执行注销操作
    public function logout() {
        if (Session::has('user')) {
            $user = Users::get(['id' => Session::get('user')->id]);
            //将用户的状态改为0,表示离线
            $user->online = 0;
            $user->save();
            //清除session
            Session::clear();
        }
        $view = new View;
        return $view->fetch('User/login');
    }

    public function modifyPassword() {
        $id = Session::get('user')->id;
        $password = $_GET['newPassword'];
        $user = Users::get($id);
        $user->password = $password;
        $user->save();
    }

    public function modifyInfo() {
        $user = Session::get('user');
        $email = $_POST['email'];
        $username = $_POST['username'];
        $sex = $_POST['sex'];
        $id = $user->id;
        //进行用户名查重
        if (Users::get(['email' => $email]) == null || $email == $user->email) {
            //进行邮箱查重
            if (Users::get(['username' => $username]) == null || $username == $user->username) {
                $newUser = Users::get($id);
                $newUser->username = $username;
                $newUser->email = $email;
                $newUser->sex = $sex;
                $newUser->save();
                $newUser = Users::get($id);
                Session::set('user', $newUser);
                return 0;
            } else {
                return 2;
            }
        } else {
            return 1;
        }
    }

}
