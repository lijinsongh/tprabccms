<?php
namespace Home\Controller;

use Think\Controller;

class IndexController extends Controller
{
    public function index()
    {
    	if($_POST){
            // 做一个简单的登录 组合where数组条件 
            $map['username'] = $_POST['username'];
            $map['password']=  $_POST['password'];
            $data=M('Users')->where("username = '".$_POST['username']."' and password = '".$map['password']."'")->find();
            if (empty($data)) {
                $this->error('账号或密码错误');
            }else{
                $_SESSION['user']=array(
                    'id'=>$data['id'],
                    'username'=>$data['username'],
                    'avatar'=>$data['avatar']
                    );
                $_SESSION['count'] = 0;
                $this->redirect("Admin/Index/index");
            }
        }else{
            $data=check_login() ? $_SESSION['user']['username'].'已登录' : '未登录';
            $assign=array(
                'data'=>$data
                );
            $this->assign($assign);
            $this->display();
        }
    }
    
    public function logout(){
    	unset($_SESSION);
    	session_destroy();
    	$this->redirect('Home/Index/index');
    }
}