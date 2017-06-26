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

class AdminContactController extends AdminbaseController {
    
	protected $options_model;
	
	function _initialize() {
		parent::_initialize();
		$this->options_model =D("Common/Options");
	}
	
	// 后台页面管理列表
	public function index(){

        $data = $this->options_model->where(array('option_name' => 'web_contact'))->find();
        $data = json_decode($data['option_value'], true);
        $this->assign('data', $data);
	    $this->display();
	}

	// 页面编辑提交
	public function edit_post() {
	
		if (IS_POST) {

			$data=I("post.");
            $data = json_encode($data);
			$result=$this->options_model->where(array('option_name' => 'web_contact'))->save(array('option_value' => $data));
			if ($result !== false) {
				$this->success("保存成功！");
			} else {
				$this->error("保存失败！");
			}
		}
	}
	
}