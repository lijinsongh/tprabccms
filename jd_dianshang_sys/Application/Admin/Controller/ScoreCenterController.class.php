<?php
namespace Admin\Controller;
use Common\Controller\AdminBaseController;

class ScoreCenterController extends AdminBaseController{
	public function index(){
		$data=D('AuthRule')->getTreeData('tree','id','title');
		$assign=array(
				'data'=>$data
		);
		$this->assign($assign);
		$this->display();
	}
	public function leaderscore(){
		$data=D('AuthRule')->getTreeData('tree','id','title');
		$assign=array(
				'data'=>$data
		);
		$this->assign($assign);
		$this->display();
	}
	
	public function addscore(){
		$data['leadername'] = $_POST['leadername'];
		$data['score'] = $_POST['score'];
		$data['remark'] = $_POST['remark'];
		$data['uid'] = $_SESSION['user']['id'];
		$data['createtime'] = date('Y-m-d');
		$is = M('dazhiscore')->add($data);
		$this->redirect("ScoreCenter/leaderscore");
	}
	
	public function scoresearch(){
		import("ORG.Util.Page");
		$pid = $_SESSION['pid'];
		if ($_GET['sdate']) {
			$and .=" and a.createtime >= '".$_GET['sdate']."'";
		}
		if ($_GET['edate']) {
			$and .=" and a.createtime <= '".$_GET['edate']."'";
		}
		if ($_GET['uid']) {
			$and .=" and a.uid = '".$_GET['uid']."'";
		}
		$this->xiaoshou = M('users')->where("username != 'admin'")->select();
		$totalRows = M()->table('s_dazhiscore a,s_users b')->where ("a.uid = b.id ".$and)->count();
		$limit = 30;
		$page=new \Org\Nx\Page($totalRows,$limit);
		$this->list = M()->table('s_dazhiscore a,s_users b')->where ("a.uid = b.id ".$and)->limit($page->firstRow,$page->listRows)->field("a.*,b.realname")->order("a.id desc")->select();
		$this->status = $_SESSION['user']['username'];
		//		echo M()->getLastSql();
		$this->page = $page->show();
		$this->display();
	}
	
	public function delscore(){
		$id = $_POST['id'];
		$is = M('dazhiscore')->where("id = ".$id)->delete();
		if ($is) {
			$data['msg'] = 1;
		} else {
			$data['msg'] = 0;
		}
		echo json_encode($data);
	}
	
	public function averagemanager(){
		if ($_GET['sdate']) {
			$and .=" and a.createtime >= '".$_GET['sdate']."'";
		} else {
			$_GET['sdate'] = date('Y-m-d',strtotime("-7 day"));
			$and .=" and a.createtime >= '".$_GET['sdate']."'";
		}
		if ($_GET['edate']) {
			$and .=" and a.createtime <= '".$_GET['edate']."'";
		} else {
			$_GET['edate'] = date('Y-m-d');
			$and .=" and a.createtime <= '".$_GET['edate']."'";
		}
		if ($_GET['leadername']) {
			$and .=" and a.leadername = '".$_GET['leadername']."'";
		}
		$this->list = M()->table('s_dazhiscore a,s_users b')->where("a.uid = b.id ".$and)->field("TRUNCATE(avg(a.score),0) as avgscore,a.leadername")->group("a.leadername")->select();
		$this->display();
	}
	
	public function getScoreInfo(){
		$and = "";
		if ($_POST['sdate']) {
			$and .=" and a.createtime >= '".$_POST['sdate']."'";
		}
		if ($_POST['edate']) {
			$and .=" and a.createtime <= '".$_POST['edate']."'";
		}
		if ($_POST['leadername']) {
			$and .=" and a.leadername = '".$_POST['leadername']."'";
		}
		$data = M()->table('s_dazhiscore a,s_users b')->where("a.uid = b.id ".$and)->field("a.*,b.realname")->select();
		//$data['msg'] = M()->getLastSql();
		echo json_encode($data);
	}
}
?>