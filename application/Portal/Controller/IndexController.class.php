<?php
/*
 *      _______ _     _       _     _____ __  __ ______
 *     |__   __| |   (_)     | |   / ____|  \/  |  ____|
 *        | |  | |__  _ _ __ | | _| |    | \  / | |__
 *        | |  | '_ \| | '_ \| |/ / |    | |\/| |  __|
 *        | |  | | | | | | | |   <| |____| |  | | |
 *        |_|  |_| |_|_|_| |_|_|\_\\_____|_|  |_|_|
 */
/*
 *     _________  ___  ___  ___  ________   ___  __    ________  _____ ______   ________
 *    |\___   ___\\  \|\  \|\  \|\   ___  \|\  \|\  \ |\   ____\|\   _ \  _   \|\  _____\
 *    \|___ \  \_\ \  \\\  \ \  \ \  \\ \  \ \  \/  /|\ \  \___|\ \  \\\__\ \  \ \  \__/
 *         \ \  \ \ \   __  \ \  \ \  \\ \  \ \   ___  \ \  \    \ \  \\|__| \  \ \   __\
 *          \ \  \ \ \  \ \  \ \  \ \  \\ \  \ \  \\ \  \ \  \____\ \  \    \ \  \ \  \_|
 *           \ \__\ \ \__\ \__\ \__\ \__\\ \__\ \__\\ \__\ \_______\ \__\    \ \__\ \__\
 *            \|__|  \|__|\|__|\|__|\|__| \|__|\|__| \|__|\|_______|\|__|     \|__|\|__|
 */
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2014 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Dean <zxxjjforever@163.com>
// +----------------------------------------------------------------------
namespace Portal\Controller;
use Common\Controller\HomebaseController; 
/**
 * 首页
 */
class IndexController extends HomebaseController {

    protected $posts_model;
    protected $term_relationships_model;
    protected $terms_model;
    protected $guestbook_model;

    function _initialize() {
        parent::_initialize();
        $this->posts_model = D("Portal/Posts");
        $this->terms_model = D("Portal/Terms");
        $this->term_relationships_model = D("Portal/TermRelationships");
        $this->guestbook_model = D("Portal/Guestbook");
    }

    //首页 小夏是老猫除外最帅的男人了
	public function index() {


        // 信息中心(图片新闻),term_id为9
        $infoCenter = $this->_lists(array(
            'post_status' => 1, // 已审核
            'recommended' => 1  // 已推荐
        ), 9, 0, 6);

        $videoPost = $this->_lists(array(
            'post_status' => 1, // 已审核
            'recommended' => 1  // 已推荐
        ), 10, 0, 3);

        $conveniencePost = $this->_lists(array(
            'post_status' => 1, // 已审核
            'recommended' => 1  // 已推荐
        ), 74, 0, 3);

        $this->assign('infoCenter', $infoCenter);
        $this->assign('videoPost', $videoPost);
        $this->assign('conveniencePost', $conveniencePost);

    	$this->display(":index");
    }

    public function guestBook () {
        if (IS_POST) {
            $data = I('post.');
            $result = $this->guestbook_model->add(array('full_name' => $data['username'], 'title' => $data['title'], 'msg' => $data['content'], 'createtime' => date('Y-m-d H:i:s'), 'status'));
            if ($result) {
                $this->ajaxReturn(array('code' => 0, 'msg' => '提交成功'));
            } else {
                $this->ajaxReturn(array('code' => 1, 'msg' => '提交失败'));
            }
        }
    }

    /**
     * 文章列表处理方法,根据不同条件显示不同的列表
     * @param array $where 查询条件
     */
    private function _lists($where=array(), $term_id, $start = 0, $limit = 10){

        $where['post_type']=array(array('eq',1),array('exp','IS NULL'),'OR');

        if(!empty($term_id)){
            $where['b.term_id']=$term_id;
            $term=$this->terms_model->where(array('term_id'=>$term_id))->find();
            $this->assign("term",$term);
        }

        $start_time=$where['start_time'];
        if(!empty($start_time)){
            $where['post_date']=array(
                array('EGT',$start_time)
            );
        }

        $end_time=$where['end_time'];
        if(!empty($end_time)){
            if(empty($where['post_date'])){
                $where['post_date']=array();
            }
            array_push($where['post_date'], array('ELT',$end_time));
        }

        $keyword=$where['keyword'];
        if(!empty($keyword)){
            $where['post_title']=array('like',"%$keyword%");
        }

        $this->posts_model
            ->alias("a")
            ->where($where);

        $this->posts_model
            ->alias("a")
            ->where($where)
            ->limit($start, $limit)
            ->order("a.post_date DESC");
            $this->posts_model->join("__TERM_RELATIONSHIPS__ b ON a.id = b.object_id");

        return $this->posts_model->select();
    }

}


