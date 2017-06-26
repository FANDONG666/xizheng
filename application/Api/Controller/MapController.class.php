<?php
namespace Api\Controller;
use Common\Controller\AdminbaseController;
class MapController extends AdminbaseController {


	function _initialize() {
	}
	
	//http://xizhengjingshui.tao3w.com/index.php?g=admin&m=index&a=index
	//http://xizhengjingshui.tao3w.com/index.php?g=Api&m=Map&a=index
	function index(){
		$lng=I("get.lng","121.481798");
		$lat=I("get.lat","31.238845");
		$this->assign("lng",$lng);
		$this->assign("lat",$lat);
		$this->display();
	}
	
}