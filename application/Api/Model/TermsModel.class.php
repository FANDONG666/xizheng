<?php
namespace Common\Model;
use Common\Model\CommonModel;
class TermsModel extends CommonModel{
	//自动验证
	protected $_validate = array(
			//array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
// 			array('ad_name', 'require', '广告名称不能为空！', 1, 'regex', 3),
	);
	
// 	protected function _before_write(&$data) {
// 		parent::_before_write($data);
// 	}

	
}

/**
 * @desc:
 * @author: benjamin
 * @date: 2017年5月8日
 */
class NewsTypeApiOutFilter extends AFilter
{
    function filter($listsArr)
    {
        if(empty($listsArr))
        {
            return $listsArr;
        }
        
        $return = array();
        foreach ($listsArr as $k => $v)
        {
            $return[$k]['type']  = (int)$v['term_id'];
            $return[$k]['title'] = $v['name'];
        }
        return $return;
    }
}