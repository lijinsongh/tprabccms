<?php
namespace Admin\Controller;
use Common\Controller\AdminBaseController;

class DZOrderController extends AdminBaseController{
	public function index(){
		$data=D('AuthRule')->getTreeData('tree','id','title');
		$assign=array(
				'data'=>$data
		);
		$this->assign($assign);
		$this->display();
	}
	public function orderadd(){
		$and = "";
		if($_GET['sdate']){
			$and .= " and o.createtime >= '".$_GET['sdate']."'";
		}
		if($_GET['edate']){
			$and .= " and o.createtime <= '".$_GET['edate']."'";
		}
		if ($_GET['orderid']) {
			$and .= " and o.orderid = '".$_GET['orderid']."'";
		}
		if ($_GET['status']) {
			$status = $_GET['status'] - 1;
			$and .=" and o.status = '".$status."'";
		}
		//判断是否是超级管理员
		if ($_SESSION['user']['username'] != 'admin'){
			$and  .= " and o.uid = ".$_SESSION['user']['id'];
		}
		$this->prolist = M("dazhiproduct")->select();
// 		echo M()->getLastSql();
		$totalRows = M()->table('s_dazhiorder o')->join("left join s_users u on o.uid=u.id")->join("left join s_dazhiproduct c on o.proid = c.id")->where("o.id is not null ".$and)->count();
// 		echo M()->getLastSql();
		$limit = 30;
		$page=new \Org\Nx\Page($totalRows,$limit);
		$this->list = M()->table('s_dazhiorder o')->join("left join s_users u on o.uid=u.id")->join("left join s_dazhiproduct c on o.proid = c.id")->where("o.id is not null ".$and)->limit($page->firstRow,$page->listRows)->field("u.realname,o.*,c.proname as  pronameother")->order("o.id desc")->select();
// 		echo M()->getLastSql();
		$this->page = $page->show();
		$this->display();
	}
	
	public function update_dzorder(){
		$data['id'] = $_POST['id'];
		$data['pronum'] = $_POST['pronum'];
		$data['price'] = $_POST['price'];
		$data['totalprice'] = $_POST['totalprice'];
		$data['daiqustatus'] = $_POST['daiqustatus'];
		$data['dingjin'] = $_POST['dingjin'];
		$data['remark'] = $_POST['remark'];
		$data['kfname'] = $_POST['kfname'];
		$data['kfphone'] = $_POST['kfphone'];
		$data['kfadress'] = $_POST['kfadress'];
		$data['jijianname'] = $_POST['jijianname'];
		$data['jijianphone'] = $_POST['jijianphone'];
		$data['uid'] = $_SESSION['user']['id'];
		$data['proid'] = $_POST['proid'];
		if ($_GET['dingjin']) {
			$data['weikuan'] = $data['totalprice'] - $data['dingjin'];
		} else {
			$data['weikuan'] = $data['totalprice'];
		}
		$is = false;
		$is = M('dazhiorder')->save($data);
		if ($is) {
			$result['msg'] = 1;
		} else {
			$result['msg'] = 0;
		}
		echo json_encode($result);
	}
	
	public function add_order(){
		$data['pronum'] = $_POST['pronum'];
		$data['price'] = $_POST['price'];
		$data['totalprice'] = $_POST['totalprice'];
		$data['daiqustatus'] = $_POST['daiqustatus'];
		$data['dingjin'] = $_POST['dingjin'];
		$data['remark'] = $_POST['remark'];
		$data['kfname'] = $_POST['kfname'];
		$data['kfphone'] = $_POST['kfphone'];
		$data['kfadress'] = $_POST['kfadress'];
		$data['jijianname'] = $_POST['jijianname'];
		$data['jijianphone'] = $_POST['jijianphone'];
		$data['uid'] = $_SESSION['user']['id'];
		$data['proid'] = $_POST['proid'];
		$data['createtime'] = date('Y-m-d');
		if ($_GET['dingjin']) {
			$data['weikuan'] = $data['totalprice'] - $data['dingjin'];
		} else {
			$data['weikuan'] = $data['totalprice'];
		}
		$yi = rand(0,9);
		$er = rand(0,9);
		$san = rand(0,9);
		$si = rand(0,9);
		$wu = rand(0,9);
		$liu = rand(0,9);
		$data['orderid'] = "DZ".$yi.$er.$san.$si.$wu.$liu;
		//查询是否复购
		$fugoulist = M('dazhiorder')->where("kfname = '".$data['kfname']."' and kfphone = '".$data['kfphone']."'")->count();
		//list为0则不是复购，如果大于0则是复购
		if ($fugoulist == 0) {
			$data['fugoustatus'] = 0;
			$data['fugounum'] = 1;
		} else if ($fugoulist > 0) {
			$data['fugoustatus'] = 1;
			$data['fugounum'] = $fugoulist + 1;
		}
		$list = M('dazhiorder')->where("orderid = '".$data['orderid']."'")->select();
		if (!$list) {
			$is = M('dazhiorder')->add($data);
		}
		if ($is) {
			$result['msg'] = 1;
		} else {
			$result['msg'] = 0;
		}
		echo json_encode($result);
	}
	
	public function financialcheck(){
		import("ORG.Util.Page");
		$and = "";
		if ($_GET['sdate']) {
			$and .=" and o.createtime >= '".$_GET['sdate']."'";
		}
		if ($_GET['edate']) {
			$and .=" and o.createtime <= '".$_GET['edate']."'";
		}
		if ($_GET['orderid']) {
			$and .= " and  o.orderid = '".$_GET['orderid']."'";
		}
		if ($_GET['status']) {
			$status = $_GET['status'] - 1;
			$and .=" and o.status = '".$status."'";
		}
		if ($_GET['qianshoustatus']) {
			$status = $_GET['qianshoustatus'] - 1;
			$and .=" and o.qianshoustatus = '".$status."'";
		}
		$totalRows = M()->table('s_dazhiorder o')->join("left join s_users u on o.uid=u.id")->join("left join s_dazhiproduct c on o.proid = c.id")->where("o.id is not null ".$and)->count();
		$limit = 30;
		$page=new \Org\Nx\Page($totalRows,$limit);
		$this->list = M()->table('s_dazhiorder o')->join("left join s_users u on o.uid=u.id")->join("left join s_dazhiproduct c on o.proid = c.id")->where("o.id is not null ".$and)->limit($page->firstRow,$page->listRows)->field("u.realname,o.*,c.proname as pronameother")->order("o.id desc")->select();
		$this->wuliu = M('kuaidi')->select();
		$this->page = $page->show();
		$this->display();
	}
	//财务审核
	public function checkpass(){
		$data['id'] = $_POST['id'];
		$data['status'] = 1;
		$data2['companyname'] = $_POST['companyname'];
		$data2['code'] = $_POST['code'];
		$data2['dzorderid'] = $data['id'];
		$is = M('dazhiorder')->save($data);
		$is2 = M('dazhikuaidi')->add($data2);
		if ($is && $is2) {
			$result['msg'] = 0;
		} else {
			$result['msg'] = 1;
		}
		echo json_encode($result);
	}
	
	//订单管理
	public function ordermanager(){
		$and = "";
		if ($_GET['sdate']) {
			$and .=" and do.createtime = '".$_GET['sdate']."'";
		}
		if ($_GET['orderid']) {
			$and .=" and do.orderid = '".$_GET['orderid']."'";
		}
		if ($_GET['status']) {
			$status = $_GET['status'] - 1;
			$and .=" and do.status = '".$status."'";
		}
		if ($_GET['uid']) {
			$and .=" and do.uid = '".$_GET['uid']."'";
		}
		if ($_GET['qianshoustatus']) {
			$status = $_GET['qianshoustatus'] - 1;
			$and .=" and do.qianshoustatus = '".$status."'";
		}
		$this->xiaoshou = M("users")->where("username != 'admin'")->order("id desc")->select();
		$totalRows = M()->table('s_dazhiorder do')->join("left join s_users u on do.uid=u.id")->join("left join s_dazhiproduct c on do.proid = c.id")->where ("do.id is not null ".$and)->count();
		$limit = 30;
		$page=new \Org\Nx\Page($totalRows,$limit);
		$this->list = M()->table('s_dazhiorder do')->join("left join s_users u on do.uid=u.id")->join("left join s_dazhiproduct c on do.proid = c.id")->where ("do.id is not null ".$and)->limit($page->firstRow,$page->listRows)->field("do.*,u.realname,c.proname as pronameother")->order("do.id desc,do.createtime desc")->select();
		$this->page = $page->show();
		$this->display();
	}
	public function del_order_other(){
		$id = $_POST['id'];
		$list = M('dazhiorder')->where("id = ".$id)->select();
		if ($list) {
			$is = M('dazhiorder')->delete($id);
			if ($is) {
				$data['msg'] = 1;
			} else {
				$data['msg'] = 2;
			}
		}
		echo json_encode($data);
	}
	
		//获取物流信息
		public function getOrderInfoOther(){
			$id = $_POST['id'];
			if($id){
				$kuaidilist = M('dazhikuaidi')->where("dzorderid = ".$id)->select();
				$name = $kuaidilist[0]['companyname'];
				$order = $kuaidilist[0]['code'];
		    	$result = file_get_contents("http://www.kuaidi100.com/query?type=$name&postid=$order");
			}else{
				$result[0]['time'] = date('Y-m-d');
				$result[0]['context'] = '上门自取';
				$resu['data'] = $result;
				$resu['status'] = 200;
				$result = json_encode($resu);
			}
        	echo $result;
		}
		//获取物流信息
		public function getOrderInfoTwo(){
			$id = $_POST['id'];
			if($id){
				$kuaidilist = M('dazhikuaidi')->where("dzorderid = ".$id)->select();
				$name = $kuaidilist[0]['companyname'];
				$order = $kuaidilist[0]['code'];
		    	$result = file_get_contents("http://www.kuaidi100.com/query?type=$name&postid=$order");
			}else{
				$result[0]['time'] = date('Y-m-d');
				$result[0]['context'] = '上门自取';
				$resu['data'] = $result;
				$resu['status'] = 200;
				$result = json_encode($resu);
			}
        	echo $result;
		}
	public function phpexcelother(){
		$and = "";
		if ($_GET['sdate']) {
			$and .=" and a.createtime >= '".$_GET['sdate']."'";
		}
		if ($_GET['edate']) {
			$and .=" and a.createtime <= '".$_GET['edate']."'";
		}
		if ($_GET['orderid']) {
			$and .=" and a.orderid = '".$_GET['orderid']."'";
		}
		if ($_GET['status']) {
			$status = $_GET['status'] - 1;
			$and .=" and a.status = ".$status;
		}
		if ($_GET['uid']) {
			$and .=" and a.uid = ".$_GET['uid'];
		}
		if ($_GET['ids']) {
			$and .=" and a.id in (".$_GET['ids'].")";
		}
		if ($_GET['qianshoustatus']) {
			$status = $_GET['qianshoustatus'] - 1;
			$and .=" and a.qianshoustatus = ".$status;
		}
		$list = M()->table('s_dazhiorder a')->join("left join s_users b on a.uid = b.id")->join("left join s_dazhiproduct c on a.proid = c.id")->where("a.id is not null ".$and)->field("a.*,b.realname,c.proname")->select();
//		dump(M()->getLastSql());
		import("ORG.Util.PHPExcel");
        import("ORG.Util.PHPExcel.Writer.Excel5");
        import("ORG.Util.PHPExcel.IOFactory.php");
        $filepath = "./Upload/model/model.xls";
        $filepath = iconv("UTF-8", "gb2312", $filepath);
        $PHPReader = new \PHPExcel_Reader_Excel2007();
        if(!$PHPReader->canRead($filepath)){
           $PHPReader = new \PHPExcel_Reader_Excel5();
           if(!$PHPReader->canRead($filepath)){
               echo 'no Excel';
               return ;
           }
        }
        $PHPExcel = $PHPReader->load($filepath);
        /**读取excel文件中的第一个工作表*/
        $currentSheet = $PHPExcel->getActiveSheet();
        $currentSheet->setTitle('导入数据表');
        /**从第4行开始输出，因为excel表中第一行为列名*/
		$column = 4;
		for ($i = 0; $i < count($list); $i++) {
			$row = $column + $i;
			$currentSheet->setCellValue("B".$row,$list[$i]['jijianname']);
			$currentSheet->setCellValue("C".$row,$list[$i]['jijianname']);
			$currentSheet->setCellValue("D".$row,$list[$i]['jijianphone']);
			$currentSheet->setCellValue("E".$row,"湖南省常德市德山经济开发区");
			$currentSheet->setCellValue("F".$row,$list[$i]['kfname']);
			$currentSheet->setCellValue("G".$row,$list[$i]['kfname']);
			$currentSheet->setCellValue("I".$row,$list[$i]['kfphone']);
			$currentSheet->setCellValue("J".$row,$list[$i]['kfadress']);
			$currentSheet->setCellValue("M".$row,$list[$i]['proname']);
			$currentSheet->setCellValue("N".$row,$list[$i]['pronum']);
			$currentSheet->setCellValue("O".$row,$list[$i]['pronum']);
			$currentSheet->setCellValue("R".$row,"顺丰隔日");
		}
		ob_end_clean();
		$fileName = "大智销售报表_".date("YmdHis").".xlsx";
		$fileName = iconv("UTF-8", "gb2312", $fileName);
		header('Content-Type: application/vnd.ms-excel');
        header("Content-Disposition: attachment;filename=\"$fileName\"");
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($PHPExcel, 'Excel2007');
        $objWriter->save('php://output'); //文件通过浏览器下载
        exit;
    }
    
    //签收订单
    public function qianshou(){
    	$data['id'] = $_POST['id'];
    	$data['qianshoustatus'] = $_POST['qianshoustatusother'] + 1;
    	$is = M('dazhiorder')->save($data);
    	if ($is) {
    		if ($data['qianshoustatus'] == 1) {
    			$result['msg'] = 0;
    		} else if ($data['qianshoustatus'] == 2) {
    			$result['msg'] = 1;
    		}
    	} else {
    		$result['msg'] = 2;
    	}
    	echo json_encode($result);
    }
	//主管签收订单
    public function qianshoulead(){
    	$data['id'] = $_GET['id'];
    	$data['qianshoustatus'] = $_GET['qianshoustatusother'] + 1;
    	$is = M('dazhiorder')->save($data);
    	if ($is) {
    		if ($data['qianshoustatus'] == 1) {
    			$result['msg'] = 0;
    		} else if ($data['qianshoustatus'] == 2) {
    			$result['msg'] = 1;
    		}
    	} else {
    		$result['msg'] = 2;
    	}
    	echo json_encode($result);
    }
	//管理签收订单
    public function qianshouadmin(){
    	$data['id'] = $_POST['id'];
    	$data['qianshoustatus'] = $_POST['qianshoustatusother'] + 1;
    	$is = M('dazhiorder')->save($data);
    	if ($is) {
    		if ($data['qianshoustatus'] == 1) {
    			$result['msg'] = 0;
    		} else if ($data['qianshoustatus'] == 2) {
    			$result['msg'] = 1;
    		}
    	} else {
    		$result['msg'] = 2;
    	}
    	echo json_encode($result);
    }
}
?>