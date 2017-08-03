<?php
namespace Admin\Controller;
use Common\Controller\AdminBaseController;
/**
 * 后台菜单管理
 */
class NavController extends AdminBaseController{
	/**
	 * 菜单列表
	 */
	public function index(){
		$data=D('AdminNav')->getTreeData('tree','order_number,id');
		$assign=array(
			'data'=>$data
			);
		$this->assign($assign);
		$this->display();
	}

	/**
	 * 添加菜单
	 */
	public function add(){
		$data['name']=$_POST['name'];
		$data['pid']=$_POST['pid'];
		$data['mca']=$_POST['mca'];
		$data['ico']=$_POST['ico'];
		$result=D('AdminNav')->addData($data);
		if ($_POST['pid'] == 0) {
			$data['id'] = $result;
			$data['order_number'] = $result;
			$result = M("admin_nav")->save($data);
		}
// 		$data2['msg'] = M()->getLastSql();
		if ($result) {
			$data2['msg'] = 1;
		}else{
			$data2['msg'] = 0;
		}
		echo json_encode($data2);
	}

	/**
	 * 修改菜单
	 */
	public function edit(){
		$data['id'] = $_POST['id'];
		$data['name']=$_POST['name'];
		$data['mca']=$_POST['mca'];
		$data['ico']=$_POST['ico'];
		$map=array(
			'id'=>$data['id']
			);
		$result=D('AdminNav')->editData($map,$data);
// 		$data2['msg'] = D()->getLastSql();
		if ($result) {
			$data2['msg'] = 1;
		}else{
			$data2['msg'] = 0;
		}
		echo json_encode($data2);
	}

	/**
	 * 删除菜单
	 */
	public function delete(){
		$id=$_GET['id'];
		$map=array(
			'id'=>$id
			);
		$result=D('AdminNav')->deleteData($map);
		if($result){
			$data['msg'] = 1;
		}else{
			$data['msg'] = 0;
		}
		echo json_encode($data);
	}
}
