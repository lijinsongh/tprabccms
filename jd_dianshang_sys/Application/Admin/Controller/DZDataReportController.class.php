<?php
namespace Admin\Controller;
use Common\Controller\AdminBaseController;

class DZDataReportController extends AdminBaseController {
	public function index(){
		$data=D('AuthRule')->getTreeData('tree','id','title');
		$assign=array(
				'data'=>$data
		);
		$this->assign($assign);
		$this->display();
	}
	public function salereport() {
		//查询出大智的所有销售人员
		$_GET['sdate'] = date('Y-m-d',strtotime("-7 day"));
		$_GET['edate'] = date('Y-m-d');
		$this->prolist = M("dazhiproduct")->select();
		$this->xiaoshou = M("users")->select();
		$this->display();
	}
	
	//获取报表数据
	public function getDataReport(){
		$sdate = date('Y-m-d',strtotime($_GET['sdate']));
		$edate = date('Y-m-d',strtotime($_GET['edate']));
		$and = "";
		$data['column'] = array();
		$data['number'] = array();
		if ($_GET['uid'] > 0) {
			//查询单个销售员销售成绩
			if ($_GET['proid']) {
				$and = " and proid in (".$_GET['proid'].")";
			}
			//个人的除拒绝签收外的单子
			$orderlist = M('dazhiorder')->where("uid = ".$_GET['uid']." and createtime >= '".$sdate."' and createtime <= '".$edate."' and qianshoustatus != 2".$and)->field("sum(totalprice) as totalprice,createtime,id")->group("createtime")->select();
			$orderidlist = M('dazhiorder')->where("uid = ".$_GET['uid']." and createtime >= '".$sdate."' and createtime <= '".$edate."' and qianshoustatus != 2".$and)->select();
// 			$data['sql'] = M()->getLastSql();
			//个人已被拒签的单子
			$dingjin = M('dazhiorder')->where("createtime >= '".$sdate."' and createtime <= '".$edate."' and qianshoustatus = 2 and uid = ".$_GET['uid'])->field("sum(dingjin) as dingjin,createtime,id")->group("createtime")->select();
			$dingjinid = M('dazhiorder')->where("createtime >= '".$sdate."' and createtime <= '".$edate."' and qianshoustatus = 2 and uid = ".$_GET['uid'])->select();
			$id = "";
			//合并两种单子
			for($i = 0;$i<count($orderlist);$i++){
				if (count($dingjin) > 0){
					for ($x = 0;$x<count($dingjin);$x++) {
						if (strtotime($orderlist[$i]['createtime']) == strtotime($dingjin[$x]['createtime'])) {
							$data['column'][$i] = $orderlist[$i]['createtime'];
							$data['number'][$i] = intval($dingjin[$x]['dingjin']) + intval($orderlist[$i]['totalprice']);
						}
					}
				} else {
					$data['column'][$i] = $orderlist[$i]['createtime'];
					$data['number'][$i] = intval($orderlist[$i]['totalprice']);
				}
				$data['zongmoney'] += $data['number'][$i];
			}
				
			for ($i = 0;$i<count($orderidlist);$i++) {
				if ($i == (count($orderidlist) - 1)) {
					$id .= $orderidlist[$i]['id'];
				} else {
					$id .= $orderidlist[$i]['id'].",";
				}
			}
			if (count($dingjinid) > 0){
				for ($i = 0;$i<count($dingjinid);$i++) {
					$id .= ",".$dingjinid[$i]['id'];
				}
			}
			$data['data2'] = M()->table('s_dazhiorder a')->join('left join s_users b on a.uid = b.id')->join('left join s_dazhiproduct c on a.proid = c.id')->where("a.id in (".$id.") and a.createtime >= '".$sdate."' and a.createtime <= '".$edate."'")->field("a.*,b.realname,c.proname")->order("a.id desc")->select();
		} else {
			if ($_GET['proid']) {
				$str = $_GET['proid'];
				$strone = "";
				for ($i = 0; $i < count($str); $i++) {
					if ($i < (count($str) - 1)) {
						$strone .= $str[$i].",";
					} else if ($i == (count($str) - 1)) {
						$strone .= $str[$i];
					}
				}
				$data['msg'] = $strone;
				$and = " and proid in (".$strone.")";
			}
			$orderlist = M()->table('s_dazhiorder a,s_users b')->where("a.uid = b.id and a.createtime >= '".$sdate."' and a.createtime <= '".$edate."' and a.qianshoustatus != 2".$and)->field("sum(a.totalprice) as money,a.createtime,b.realname,a.uid")->group("a.uid")->select();
// 			$data['sql'] = M()->getLastSql();
			for($i=0;$i<count($orderlist);$i++){
				//查询是否有已收定金的拒绝签收定单
				$dingjin = M()->table('s_dazhiorder a,s_users b')->where("a.uid = b.id and a.createtime >= '".$sdate."' and a.createtime <= '".$edate."' and a.qianshoustatus = 2 and a.uid = ".$orderlist[$i]['uid'])->field("sum(a.dingjin) as dingjin")->group("a.uid")->select();
				$data['column'][$i] = $orderlist[$i]['realname']; 
				if ($dingjin) {
					$data['number'][$i] = intval($dingjin[0]['dingjin']) + intval($orderlist[$i]['money']);
				} else {
					$data['number'][$i] = intval($orderlist[$i]['money']);
				}
				$data['zongmoney'] += $data['number'][$i];
				$data['data'] = M()->table('s_dazhiorder a,s_users b')->where("a.uid = b.id and createtime >= '".$sdate."' and createtime <= '".$edate."'")->field("sum(totalprice) as money,createtime")->group("createtime")->select();
			}
		}
		if ($data['zongmoney'] == "" || $data['zongmoney'] == null) {
			$data['zongmoney'] = 0;
		}
		echo json_encode($data);
	}
	
	public function getXiaoShouInfo(){
		$createtime = $_GET['createtime'];
		$data = M()->table('s_dazhiorder a')->join("left join s_users b on a.uid = b.id")->join("left join s_dazhiproduct c on a.proid = c.id")->where("a.createtime = '".$createtime."'")->field("a.*,b.realname,c.proname")->select();
		echo json_encode($data);
	}
	
	//复购数据报表
	public function fugoureport(){
		$pid = $_SESSION['pid'];
		$and = "";
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
		if ($_GET['uid']) {
			$and .= " and a.uid = '".$_GET['uid']."'";
		}
		//查询总订单数
		$countlist = M()->table('s_dazhiorder a,s_users b')->where("a.uid = b.id and a.qianshoustatus != 2 ".$and)->field("count(*) as countnum")->select();
		//查询复购订单数
		$fugoulist = M()->table('s_dazhiorder a,s_users b')->where("a.uid = b.id and a.fugoustatus = 1 and a.qianshoustatus != 2 ".$and)->field("count(*) as countnum")->select();
		$data['countnum'] = $countlist[0]['countnum'];
		$data['fugounum'] = $fugoulist[0]['countnum'];
		$data['fugoupoint'] = number_format(($fugoulist[0]['countnum']/$countlist[0]['countnum']),2);
		$data['fugoupoint'] = ($data['fugoupoint'] * 100)."%";
		//查询详细的订单总数，销售人员名称
		$listone = M()->table('s_dazhiorder a,s_users b')->where("a.uid = b.id and a.qianshoustatus != 2 ".$and)->field("count(*) as countnum,a.uid,b.realname")->group("a.uid")->select();
		//查询详细的复购数，销售人员名称
		$listtwo = M()->table('s_dazhiorder a,s_users b')->where("a.uid = b.id and a.fugoustatus = 1 and a.qianshoustatus != 2 ".$and)->field("count(*) as countnum,a.uid,b.realname")->group("a.uid")->select();
		//计算复购率
		for ($i = 0; $i < count($listone); $i++) {
			for($x = 0; $x < count($listtwo); $x++){
				if ($listone[$i]['uid'] == $listtwo[$x]['uid'] && $listone[$i]['realname'] == $listtwo[$x]['realname']) {
					$listone[$i]['fugounum'] = $listtwo[$x]['countnum'];
					$listone[$i]['fugoupoint'] = number_format(($listtwo[$i]['countnum']/$listone[$x]['countnum']),2);
				}
			}
			if (empty($listone[$i]['fugounum'])) {
				$listone[$i]['fugounum'] = 0;
				$listone[$i]['fugoupoint'] = 0;
			} else {
				$listone[$i]['fugoupoint'] = number_format(($listone[$i]['fugounum']/$listone[$i]['countnum']),2);
				$listone[$i]['fugoupoint'] = ($listone[$i]['fugoupoint'] * 100)."%";
			}
			$listone[$i]['createtime'] = $_GET['sdate']."至".$_GET['edate'];
		}
		$this->xiaoshou = M("users")->where("username != 'admin'")->select();
		$this->list = $listone;
		$this->countnum = $data['countnum'];
		$this->fugounum = $data['fugounum'];
		$this->fugoupoint = $data['fugoupoint'];
		$this->display();
	}
	
	public function getFuGouOrderInfo(){
		$and = "";
		if ($_POST['sdate']) {
			$and .=" and a.createtime >= '".$_POST['sdate']."'";
		}
		if ($_POST['edate']) {
			$and .=" and a.createtime <= '".$_POST['edate']."'";
		}
		if ($_POST['uid']) {
			$and .= " and a.uid = '".$_POST['uid']."'";
		}
		$data = M()->table('s_dazhiorder a,s_users b')->where("a.uid = b.id ".$and)->field("a.*,b.realname")->select();
// 		$data['sql'] = M()->getLastSql();
		echo json_encode($data);
		
	}
	
	public function productreport(){
		//查询出大智的所有销售人员
		$pid = $_SESSION['pid'];
		$_GET['sdate'] = date('Y-m-d',strtotime("-7 day"));
		$_GET['edate'] = date('Y-m-d');
		$this->prolist = M("dazhiproduct")->select();
		$this->xiaoshou = M("users")->where("username != 'admin'")->order("id desc")->select();
		$this->display();
	}
	
	public function getproreport(){
		$sdate = date('Y-m-d',strtotime($_GET['sdate']));
		$edate = date('Y-m-d',strtotime($_GET['edate']));
		$data['column'] = array();
		$data['number'] = array();
		$and = "";
		if ($_GET['uid']) {
			$and .= " and a.uid  = ".$_GET['uid'];
		}
		$list = M()->table("s_dazhiorder a,s_dazhiproduct b")->where("a.proid = b.id and a.createtime >= '".$sdate."' and a.createtime <= '".$edate."' ".$and)->field("count(a.proid) as countpro,b.proname")->group("a.proid")->select();
//		$data['msg'] = M()->getLastSql();
		for ($i = 0; $i < count($list);$i++) {
			$data['column'][$i] = $list[$i]['proname'];
			$str = $list[$i]['countpro'];
			$data['number'][$i] = intval($str);
			$data['zongnum'] += $data['number'][$i];
		}
		echo json_encode($data);
	}
}
?>