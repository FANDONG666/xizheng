<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2014 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Tuolaji <479923197@qq.com>
// +----------------------------------------------------------------------
namespace Portal\Controller;

use Portal\Controller\AdminPostController;

class AdminPostDoController extends AdminPostController{

    // 文章添加
    public function add(){
        $terms = $this->terms_model->order(array("listorder"=>"asc"))->select();
        $term_id = I("get.term",0,'intval');
// 		$this->_getTermTree();
        $this->getTree();
        $term=$this->terms_model->where(array('term_id'=>$term_id))->find();
        $this->assign("term",$term);
        $this->assign("terms",$terms);
        $this->display();
    }
	
}