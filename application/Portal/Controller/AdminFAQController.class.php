<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2014 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Tuolaji <479923197@qq.com>
// +----------------------------------------------------------------------
namespace Portal\Controller;

use Common\Controller\AdminbaseController;

class AdminFAQController extends AdminbaseController {
    
	protected $faq_model;
	
	function _initialize() {
		parent::_initialize();
		$this->faq_model =D("Common/Faq");
	}
	
	// 后台页面管理列表
	public function index(){
	    $this->_lists(array("status" => 1));
	    $this->display();
	}

	/**
	 * 页面列表处理方法,根据不同条件显示不同的列表
	 * @param array $where 查询条件
	 */
	private function _lists($where=array()){


	    $count=$this->faq_model
	    ->alias("a")
	    ->where($where)
	    ->count();

	    $page = $this->page($count, 20);

	    $posts=$this->faq_model
	    ->alias("a")
	    ->where($where)
	    ->limit($page->firstRow , $page->listRows)
	    ->order("a.id DESC")
	    ->select();

	    $this->assign("page", $page->show('Admin'));
	    $this->assign("formget",array_merge($_GET,$_POST));
	    $this->assign("posts",$posts);
	}
	
	// 页面添加
	public function add(){
         $this->display();
	}
	
	// 页面添加提交
	public function add_post(){
		if (IS_POST) {
			$data=I("post.");
			$result=$this->faq_model->add($data);
			if ($result) {
				$this->success("添加成功！");
			} else {
				$this->error("添加失败！");
			}
		}
	}
	
	// 页面编辑
	public function edit(){
		$id= I("get.id",0,'intval');
		$data=$this->faq_model->where(array('id'=>$id))->find();
		
		$this->assign("data",$data);
		$this->display();
	}
	
	// 页面编辑提交
	public function edit_post(){
	
		if (IS_POST) {

			$data=I("post.");
			$result=$this->faq_model->save($data);
			if ($result !== false) {
				$this->success("保存成功！");
			} else {
				$this->error("保存失败！");
			}
		}
	}
	
	// 删除页面
	public function delete(){
		if(isset($_POST['ids'])){
			$ids = array_map("intval", $_POST['ids']);
			$data=array("status"=>0);
			if ($this->faq_model->where(array('id'=>array('in',$ids)))->save($data)!==false) {
				$this->success("删除成功！");
			} else {
				$this->error("删除失败！");
			}
		}else{
			if(isset($_GET['id'])){
				$id = I("get.id",0,'intval');
				$data=array("id"=>$id,"status"=>0);
				if ($this->faq_model->save($data)) {
					$this->success("删除成功！");
				} else {
					$this->error("删除失败！");
				}
			}
		}
	}


	
}