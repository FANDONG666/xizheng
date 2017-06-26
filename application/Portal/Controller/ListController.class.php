<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2014 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------
namespace Portal\Controller;
use Common\Controller\HomebaseController;

// 各分类term_id
define('NEWS_TERM_ID', 5);
define('LAWS_TERM_ID', 11);

class ListController extends HomebaseController {

    protected $posts_model;
    protected $term_relationships_model;
    protected $terms_model;

    function _initialize() {
        parent::_initialize();
        $this->posts_model = D("Portal/Posts");
        $this->terms_model = D("Portal/Terms");
        $this->term_relationships_model = D("Portal/TermRelationships");
    }

	// 前台文章列表
	public function index() {
		$nav_id = $_REQUEST['nav_id'];
		$nav_one   = sp_get_nav_level_one($nav_id);
		$nav_two   = sp_get_nav_level_two($nav_id);
		$nav_three = sp_get_nav_level_three($nav_id);
		$nav_four  = sp_get_nav_level_four($nav_id);
		
		$this->assign('nav_one',$nav_one);
		$this->assign('nav_two',$nav_two);
		$this->assign('nav_three',$nav_three);
		$this->assign('nav_four',$nav_four);
		
		$navTopArr = sp_get_top_menu_info();//benjamin by 2017-05-09
        foreach ($navTopArr as $key => $value) {
            if ($value['id'] == 1 && $value['cid'] == 1) {
                $navTopArr[$key]['href'] = '/index.php?';
            }
        }
        $this->assign('navTopArr',$navTopArr);

	    $term_id=I('get.id',0,'intval');
	    if ($term_id == NEWS_TERM_ID) {
	        $this->news();exit;
        } else if ($term_id == LAWS_TERM_ID) {
            $this->laws();exit;
        }
		$term=sp_get_term($term_id);
		
		if(empty($term)){
		    header('HTTP/1.1 404 Not Found');
		    header('Status:404 Not Found');
		    if(sp_template_file_exists(MODULE_NAME."/404")){
		        $this->display(":404");
		    }
		    return;
		}
		
		$tplname=$term["list_tpl"];
    	$tplname=sp_get_apphome_tpl($tplname, "list");
    	$this->assign($term);
    	$this->assign('cat_id', $term_id);
    	$this->display(":$tplname");
	}
	
	// 文章分类列表接口,返回文章分类列表,用于后台导航编辑添加
	public function nav_index(){
		$navcatname="文章分类";
        $term_obj= M("Terms");

        $where=array();
        $where['status'] = array('eq',1);
        $terms=$term_obj->field('term_id,name,parent')->where($where)->order('term_id')->select();
		$datas=$terms;
		$navrule = array(
		    "id"=>'term_id',
            "action" => "Portal/List/index",
            "param" => array(
                "id" => "term_id"
            ),
            "label" => "name",
		    "parentid"=>'parent'
        );
		return sp_get_nav4admin($navcatname,$datas,$navrule) ;
	}

    /**
     * 新闻中心
     */
    public function news()
    {
        // 默认的新闻中心分类id
        $newsId = NEWS_TERM_ID;
        $term_id=I('get.id',$newsId,'intval');
        $term=sp_get_term($term_id);

        // 子分类
        $cate = $this->terms_model->where(array('parent' => $newsId))->select();

        if ($term_id == 5) {
            $path = '0-5';
        } else {
            $path = '0-5-' . $term_id;
        }
        $where = array(
            'p.post_status' => 1,
            't.path' => ['like', '%' . $path . '%']
        );

        $count = $this->posts_model->field("p.*, t.path")->alias("p")
            ->join("__TERM_RELATIONSHIPS__ tr ON p.id = tr.object_id", "LEFT")
            ->join("__TERMS__ t ON tr.term_id = t.term_id", "LEFT")
            ->where($where)->count();

        $pageSize = 8;
        $page = $this->page($count, $pageSize);

        $data = $this->posts_model->field("p.*, t.term_id, t.path")->alias("p")
            ->join("__TERM_RELATIONSHIPS__ tr ON p.id = tr.object_id", "LEFT")
            ->join("__TERMS__ t ON tr.term_id = t.term_id", "LEFT")
            ->where($where)
            ->order("p.post_date DESC")
            ->limit($page->firstRow , $page->listRows)
            ->select();

        $this->assign("page", $page->show('news'));
        $this->assign("pageCount", ceil($count / $pageSize));
        $this->assign("count", $count);
        $this->assign('data', $data);

        $this->assign('newsId', $newsId);
        $this->assign('term', $term);
        $this->assign('cate', $cate);

        $this->display(":news");
	}

    /**
     * 政策法规
     */
    public function laws()
    {
        // 默认的政策法规分类id
        $lawsId = LAWS_TERM_ID;
        // 子分类
        $cate = $this->terms_model->where(array('parent' => $lawsId))->select();
        $term_id=I('get.id',$lawsId,'intval');
        if ($term_id == $lawsId && !empty($cate)) {
            $term_id = $cate[0]['term_id'];
        }

        $term=sp_get_term($term_id);

        $where = array(
            'p.post_status' => 1,
            'tr.term_id' => $term_id
        );

        $count = $this->posts_model->field("p.*, tr.term_id")->alias("p")
            ->join("__TERM_RELATIONSHIPS__ tr ON p.id = tr.object_id", "LEFT")
//            ->join("__TERMS__ t ON tr.term_id = t.term_id", "LEFT")
            ->where($where)->count();

        $pageSize = 6;
        $page = $this->page($count, $pageSize);

        $data = $this->posts_model->field("p.*, tr.term_id")->alias("p")
            ->join("__TERM_RELATIONSHIPS__ tr ON p.id = tr.object_id", "LEFT")
//            ->join("__TERMS__ t ON tr.term_id = t.term_id", "LEFT")
            ->where($where)
            ->order("p.post_date DESC")
            ->limit($page->firstRow , $page->listRows)
            ->select();

        $this->assign('term', $term);
        $this->assign('cate', $cate);
        $this->assign('data', $data);
        $this->assign('page', $page->show('laws'));

        $this->display(":laws");
	}
}
