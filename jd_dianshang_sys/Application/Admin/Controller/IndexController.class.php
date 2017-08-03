<?php
namespace Admin\Controller;
use Common\Controller\AdminBaseController;
/**
 * 后台首页控制器
 */
class IndexController extends AdminBaseController{
	/**
	 * 首页
	 */
	public function index(){
		$data=D('AuthRule')->getTreeData('tree','id','title');
		$assign=array(
			'data'=>$data
		);
		$this->assign($assign);
		$this->display();
	}
}
