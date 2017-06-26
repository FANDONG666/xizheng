<?php
/**
 * @desc: wangge api接口
 * @author: benjamin
 * @date: 2017年5月5日
 */
namespace Api\Controller;

use Think\Controller;
// use Org\Validate\ApiParamsValidate as ApiParamsValidate;

class ApiController extends Controller {
	function __set($name, $value)
	{
		if ($name == '__rulesArr'){
			$this->_doRules($value);
		}
		elseif ($name == '__listArr'){
			$this->returnType($value);
		}
	}
	
	protected function _doRules($rulesArr)
	{
		$messagesArr = array();
		import("@.ORG.Validate.ApiParamsValidate");
		$apvObj = new \ApiParamsValidate($rulesArr, $_REQUEST);
		$tipsArr = $apvObj->doCheck();
		if (!empty($tipsArr)) {
			foreach ($tipsArr as $k => $v) {
				if (empty($v['code']))//解决校验时，直接提示code为0情况
				{
					$messagesArr['code'] = 0;
					$messagesArr['message'] .= $v['message'] . ";";
				} else {
					$messagesArr['code'] = $v['code'];
					$messagesArr['message'] .= $v['code'] . '-' . $v['message'] . ";";
				}
			}
			trim($messagesArr['message'], ';');
			$this->__listArr = $messagesArr;
			exit();
		}
	}

	public function returnType($messageArr){
		
		header("Access-Control-Allow-Origin: *");
		header("Access-Control-Allow-Headers: Content-Type,Accept");
		$platform=strtolower($this->platform);
		$callback = isset($_REQUEST['callback']) ? $_REQUEST['callback'] : "";
		switch($platform)
		{
			case 'html5':
				{
					
					header('Content-Type: application/json');
				}
				break;
			case "ios":
				{
					header('Content-Type: application/json');
				}
				break;
			case "android":
				{
					header('Content-Type: application/json');
				}
				break;
			default:
				{
					header('Content-Type: application/json');
				}
				
		}
		if($callback != '') {
			echo '/**/' . $callback . '(' . json_encode($messageArr).')';
			exit;
		}
		
		echo json_encode($messageArr);
		exit;
	}

    /**
     * api返回json数据结构
     * @param int $error_code
     * @param string $msg
     * @param array $data
     */
    public function returnApiJson($error_code = 0, $msg = '成功', $data = array())
    {
        $this->__listArr = array('error_code' => $error_code, 'msg' => $msg, 'data' => $data);
	}
	
	#benjamin
	function __destruct()
	{
// 		if (is_array($this->__listArr) && isset($this->__listArr['code'])) {
// 			dump($this->__listArr);die();
// 			$this->returnType($this->__listArr);
// 		} else {
// 			$messageArr['code'] = 0;
// 			$messageArr['message'] = $this->__listArr;
// 			$this->returnType($messageArr);
// 		}
	}
	
	/**
	* @desc 新闻中心分类
	* @access 
	* @param unknowtype
	* @return 
	* @example http://thinkcmfx.tao3w.com/index.php?g=Api&m=Wangge&a=newsType
	* @date 2017年5月5日
	* @author benjamin
	*/
	public function newsType()
	{
		$listArr = sp_get_all_child_terms(5);
		$termsObj = D("Terms");
		$termsObj->addFilterObj(new \Common\Model\NewsTypeApiOutFilter());
		$listArr = $termsObj->setLists($listArr)->filter()->getLists();
		$this->__listArr = $listArr; 
	}
	
	//http://xizhengjingshui.tao3w.com/index.php?g=Api&m=Api&a=demo&id=90
    public function demo() {
    	#第一步，校验
    	$this->__rulesArr = array(
    			'id' => array('required' => array(1000011)),
    	);
    	
    	#第二步，逻辑处理
    	
    	#第三步，输出
    	$this->__listArr = array('name'=>'jjdoor');
    }

    /**
     * 产品目录
     *
     * @data 2017-06-07
     */
    public function productCategory()
    {
        $data = sp_get_child_terms(75);

        $this->returnApiJson(0, '成功', $data);
    }

    /**
     * 产品和新闻列表
     */
    public function posts()
    {
        $cat_id = I('get.term_id',0,'intval');
        $page = I('get.p',1,'intval');
        $page_size = I('get.page_size',6,'intval');

//        $start = $page_size * ($page - 1);
//        $limit = $start . ',' . $page_size;

//        $data = sp_sql_posts_paged("cid:$cat_id;limit:$limit;order:post_date DESC;",$page_size);
        $data = sp_sql_posts_paged("cid:$cat_id;order:post_date DESC;",$page_size);
        foreach ($data['posts'] as $k => $v) {
            if ($v['post_content']) {
                $data['posts'][$k]['post_content'] = msubstr(strip_tags($v['post_content']), 0, 200);
            }

        }

        $data['total'] = $data['count'];
        $data['page'] = $page;
        $data['page_size'] = $page_size;
        $data['upload_path'] = sp_get_image_url();

        unset($data['count']);
        unset($data['total_pages']);

        $this->returnApiJson(0, '成功', $data);
    }

    /**
     * 热销产品
     */
    public function hotProduct()
    {
        $cat_id = 75;

        $data = sp_sql_posts_paged("cid:$cat_id;order:post_date DESC;where:recommended=1",0);
        $data['upload_path'] = sp_get_image_url();

        $this->returnApiJson(0, '成功', $data);
    }

    /**
     * 详情
     */
    public function detail()
    {
        $data = array();

        $article_id=I('get.id',0,'intval');
        $term_id=I('get.term_id',0,'intval');

        $posts_model=M("Posts");

        $article=$posts_model
            ->alias("a")
            ->field('a.*,c.user_login,c.user_nicename,b.term_id')
            ->join("__TERM_RELATIONSHIPS__ b ON a.id = b.object_id")
            ->join("__USERS__ c ON a.post_author = c.id")
            ->where(array('a.id'=>$article_id,'b.term_id'=>$term_id))
            ->find();

        if(empty($article)){
            $this->returnApiJson(1, '文章不存在', $data);
        }


        $productCategory = sp_get_child_terms(75);
        // 产品列表term_id数组
        $categoryList = array();
        foreach ($productCategory as $cate) {
            $categoryList[] = $cate['term_id'];
        }

        // 产品详情
        if (in_array($term_id, $categoryList) && $article) {
            $article['post_content'] = strip_tags($article['post_content']);
            $article['detail'] = M('ProductDetail')->where(array('object_id' => $article['id']))->find();
        }

        // 增加阅读量
        $posts_model->where(array('id'=>$article_id))->setInc('post_hits');

        $article_date=$article['post_date'];

        $join = '__POSTS__ as b on a.object_id =b.id';
        $join2= '__USERS__ as c on b.post_author = c.id';

        $term_relationships_model= M("TermRelationships");

        if (!in_array($term_id, $categoryList)) {
            // 下一篇
            $data['next'] = $term_relationships_model
                ->alias("a")
                ->join($join)->join($join2)
                ->where(array('b.id' => array('gt', $article_id), "post_date" => array("egt", $article_date), "a.status" => 1, 'a.term_id' => $term_id, 'post_status' => 1))
                ->field("b.id, b.post_title, a.term_id")
                ->order("post_date asc,b.id asc")
                ->find();

            // 上一篇
            $data['prev'] = $term_relationships_model
                ->alias("a")
                ->join($join)->join($join2)
                ->where(array('b.id' => array('lt', $article_id), "post_date" => array("elt", $article_date), "a.status" => 1, 'a.term_id' => $term_id, 'post_status' => 1))
                ->field("b.id, b.post_title, a.term_id")
                ->order("post_date desc,b.id desc")
                ->find();
        }

        $smeta=json_decode($article['smeta'],true);
        $content_data=sp_content_page($article['post_content']);
        $article['post_content']=$content_data['content'];
        $article['smeta'] = $smeta['thumb'];

        $data['current'] = $article;
        $data['upload_path'] = sp_get_image_url();

        $this->returnApiJson(0, '成功', $data);
    }

    /**
     * 销售网络,售后服务,关于我们
     */
    public function page()
    {
        $id=I('get.id',0,'intval');
        $content=sp_sql_page($id);

        $data = array();
        $data['content'] = $content['post_content'];
        if ($id == 56) {
            $data['faq'] = all_faq();
        }
        if ($id == 63) {
            $data['content'] = strip_tags($data['content']);
            $data['pic'] = $content['smeta'] ? json_decode($content['smeta'], true)['thumb']: '';
            $data['upload_path'] = sp_get_image_url();
        }

        $this->returnApiJson(0, '成功', $data);
    }

    /**
     * 反馈
     */
    public function guestbook()
    {
        if (IS_POST) {
            #第一步，校验
            $this->__rulesArr = array(
                'name' => array('required' => array(1000011)),
//                'email' => array('required' => array(1000012)),
                'phone' => array('required' => array(1000013)),
            );

            $params = I('post.');
            $guestbook_model = M("Guestbook");
            $data['full_name'] = $params['name'];
            $data['email'] = $params['email'];
            $data['phone'] = $params['phone'];
            $data['msg'] = $params['msg'];
            $data['createtime'] = date('Y-m-d H:i:s');

            $result = $guestbook_model->add($data);

            $this->returnApiJson(0, '成功');
        }

    }

    /**
     * 联系我们
     */
    public function contactInformation()
    {
        $options_model = M("Options");
        $data = $options_model->where(array('option_name' => 'web_contact'))->find();
        $data = json_decode($data['option_value'], true);
        $data['upload_path'] = sp_get_image_url();

        $this->returnApiJson(0, '成功', $data);
    }

}

