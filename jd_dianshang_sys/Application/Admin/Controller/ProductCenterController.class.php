<?php
namespace Admin\Controller;
use Common\Controller\AdminBaseController;

class ProductCenterController extends AdminBaseController{
	public function index(){
		$data=D('AuthRule')->getTreeData('tree','id','title');
		$assign=array(
				'data'=>$data
		);
		$this->assign($assign);
		$this->display();
	}
	public function productlist(){
		$and = "";
		if ($_GET['productname']) {
			$and .= " proname = '".$_GET['productname']."'";
		}
		$totalRows = M("dazhiproduct")->where($and)->count();
		$limit = 30;
		$page=new \Org\Nx\Page($totalRows,$limit);
		$this->list = M("dazhiproduct")->where($and)->limit($page->firstRow,$page->listRows)->field("*")->order("id desc")->select();
//		echo M()->getLastSql();
		$this->page = $page->show();
		$this->display();
	}
	
	public function addproduct(){
	    if (!$_POST['id']) {
    		$data['proname'] = $_POST['proname'];
    		$data['createtime'] = date("Y-m-d H:i:s");
    		$list = M("dazhiproduct")->where("proname = '".$data['proname']."'")->select();
    		if (!$list) {
    			$is = M("dazhiproduct")->add($data);
    			if ($is) {
    				$data['msg'] = 1;
    			} else {
    				$data['msg'] = 2;
    			}
    		} else {
    			$data['msg'] = 0;
    		}
	    } else {
	        $data['id'] = $_POST['id'];
	        $data['proname'] = $_POST['proname'];
	        $list = M("dazhiproduct")->where("proname = '".$data['proname']."'")->select();
	        if (!$list) {
	            $is = M("dazhiproduct")->save($data);
	            if ($is) {
    				$data['msg'] = 1;
	            } else {
    				$data['msg'] = 2;
	            }
	        } else {
	            $data['msg'] = 0;
	        }
	    }
		echo json_encode($data);
	}
}
?>