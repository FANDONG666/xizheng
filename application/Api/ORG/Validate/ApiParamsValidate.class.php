<?php
/**
 * @desc: api参数校验类
 * @author: benjamin by 2015-05-15
 * @example import("@.ORG.ApiParamsValidate");
 *          使用默认错误提示(一般推荐使用这个参数)
 *          $rulesArr = array('userid'=>array(
                                	         'userbyid'=>array(100681),
                                	         'required'=>array(100681)
                                	     ),
            	         'id'=>array(
                            	             'quanzibyid'=>array(100681),
                            	             'required'=>array(100681)
            	                       ),
            	         'type'=>array(
                            	             'favoritesfavtype'=>array(100681),
                            	             'required'=>array(100681)
            	         ));
                                使用自定义错误提示
            $rulesArr = array('userid'=>array(
                                	         'userbyid'=>array(100681,'不存在该用户'),
                                	         'required'=>array(100681,'该参数是必填参数')
                                	     ),
            	         'id'=>array(
                            	             'quanzibyid'=>array(100681,'不存在该圈子'),
                            	             'required'=>array(100681,'该参数是必填参数')
            	                       ),
            	         'type'=>array(
                            	             'favoritesfavtype'=>array(100681,'请填入正确关注类型'),
                            	             'required'=>array(100681,'该参数是必填参数')
            	         )); 	 
                                组合参数，即验证2个以上参数   
            $rulesArr = array('userid,questionid' => array('Cust' => array(170501)),
                              'questionid'=>array('IsExistQuestionid'=>array(170503),'Required'=>array(170504))
                            );                    
                                
            $apvObj  = new ApiParamsValidate($rulesArr,$_REQUEST);
            $tipsArr = $apvObj->doCheck();
                               如果参数校验完全正确$tipsArr的值是：
            array();
                               如果有错误信息返回如下：
            Array
            (
                [userid] => Array
                    (
                        [code] => 100681
                        [message] => userid参数的值【17106000】是非法数据,根据UserById规则验证
                    )
            
                [id] => Array
                    (
                        [code] => 100681
                        [message] => id参数的值【128】是非法数据,根据QuanziById规则验证
                    )
            )
            
 * @date: 2015年05月15日
 */
// namespace Org\Validate;
class ApiParamsValidate
{
    private $_status = 0;//只有0和1两种值，当为0时，仅仅输出参数不符合规定的一种状态，当为1时，输出该参数所有的不符合规定

    function __construct($rulesArr,$dataArr)
    {
        $this->_rulesArr = $rulesArr;
        $this->_dataArr  = $dataArr;
    }
    
    #参数校验客户端  benjamin by 2015-05-15
    function doCheck()
    {
        $messages = array();
        foreach($this->_rulesArr as $param => $rulesDetailArr)
        {
            #必须先检查在$rulesDetailArr里是否有requires这个key
            if($rulesDetailArr['required'] || $rulesDetailArr['Required'])
            {
                $ruleObj = new Required($param,$this);
                $checkArr = $ruleObj->rule();
                #记录不成功的参数
                if($checkArr['code'] !== 0)
                {
                    $messages[$param] = $checkArr;
                    continue;
                }
            }
            #如果是非必须参数，并且未传递该参数，可忽略校验
            if(strpos($param,",") === false && strpos($param,":") === false)
            {
                if( array_key_exists($param, $this->_dataArr) === false)
                    continue;
            }
            else 
            {

                $tmpParamArr = explode(",", $param);
                $tmpErrNum      = 0;
                foreach ($tmpParamArr as $key => $value)
                {
                		$value = strpos($value, ':') > -1 ? substr($value,strpos($value, ':')+1) : $value;
                		$value = strpos($value, '->') > -1 ? substr($value,0,strpos($value, '->')) : $value;
                		
                    if( array_key_exists($value, $this->_dataArr) === false)
                    {
                        $tmpErrNum ++;
                    }
                }
                //都不存在
                if($tmpErrNum == count($tmpParamArr))
                {
                    continue;
                }
                //都存在
                elseif ($tmpErrNum == 0)
                {
                    
                }
                else 
                {
//                     //组合参数缺失
//                     $ruleObj = new Required($param,$this);
//                     $checkArr = $ruleObj->rule();
//                     #记录不成功的参数
//                     if($checkArr['code'] !== 0)
//                     {
//                         $messages[$param] = $checkArr;
//                         continue;
//                     }
                    
                    
                }
            }
            #校验
            foreach ($rulesDetailArr as $ruleNameStr => $ruleTipsArr)
            {
                $ruleObj = new $ruleNameStr($param,$this);
                
                $checkArr = $ruleObj->rule();
                #记录不成功的参数
                if($checkArr['code'] !== 0)
                {
                    #会形成多维数组，对输入参数进行一次完全校验
                    #$messages[$param][$ruleTipsArr[0]] = $checkArr;
                    if(isset($messages[$param]))
                    {
                        if($ruleObj->getClassName() == 'required')
                        {
                            $messages[$param] = $checkArr;
                        }
                    }
                    else
                    {
                        $messages[$param] = $checkArr;
                    }
                }
            }
        }
        return $messages;
    }
}

/**
 * @desc: api参数校验抽象类
 * @author: benjamin
 * @date: 2015年5月15日
 */
abstract class ARule
{
    protected $_currentClassName;
    protected $_currentClassNameLower;
    protected $_errorNum;
    protected $_messageStr;
    protected $_apiParamsValidateObj;
    protected $_paramNameStr;
    protected $_paramValueStr;
    #benjamin
    function __construct($paramNameStr,ApiParamsValidate $ApiParamsValidateObj)
    {
        if(strpos($paramNameStr,",") === false && strpos($paramNameStr,":") === false)
        {
            $this->_paramNameStr     = $paramNameStr;
            $this->_paramValueStr    = $ApiParamsValidateObj->_dataArr[$paramNameStr];
        }
        else 
        {
        		

            $this->_paramNameStr     = explode(",",$paramNameStr);

            ############## add by lww
            if(strpos($paramNameStr,":") > -1){
        			$model = substr($paramNameStr,0,strpos($paramNameStr, ':'));
        			$this->_paramNameStr[0] = substr($this->_paramNameStr[0],strpos($this->_paramNameStr[0], ':')+1);
        			$this->_paramNameStr['model'] = $model;
        		}

            foreach ($this->_paramNameStr as $k => $v)
            {
            		if($k === 'model'){
            			$this->_paramValueStr['model'] = $v;
            			continue;
            		}
            		$tmpV = $v;
            		$v= strpos($v, '->') > -1 ? substr($v,0,strpos($v, '->')) : $v;

                if(isset($ApiParamsValidateObj->_dataArr[$v]))
                    $this->_paramValueStr[]    = $ApiParamsValidateObj->_dataArr[$v];
                else 
                    $this->_paramValueStr[]    = null;
                
                
                $this->_paramNameStr[$k]= strpos($tmpV, '->') > -1 ? substr($tmpV,strpos($tmpV, '->')+2) : $tmpV;

               
            }

        }
        $this->_currentClassName      = get_class($this);
        $this->_currentClassNameLower = strtolower($this->_currentClassName);
        $this->_errorNum              = $ApiParamsValidateObj->_rulesArr[$paramNameStr][$this->_currentClassName][0] ? 
                                        $ApiParamsValidateObj->_rulesArr[$paramNameStr][$this->_currentClassName][0] : 
                                        $ApiParamsValidateObj->_rulesArr[$paramNameStr][$this->_currentClassNameLower][0];
        $this->_messageStr            = $ApiParamsValidateObj->_rulesArr[$paramNameStr][$this->_currentClassName][1] ? 
                                        $ApiParamsValidateObj->_rulesArr[$paramNameStr][$this->_currentClassName][1] : 
                                        $ApiParamsValidateObj->_rulesArr[$paramNameStr][$this->_currentClassNameLower][1] ; 
        $this->_apiParamsValidateObj  = $ApiParamsValidateObj;
    }
    
    /**
     * @desc: 返回失败信息
     * @author: benjamin
     * @date: 2015年5月15日
     */
    function messageFail()
    {
        if(strtolower($this->_currentClassName) == 'required')
        {
            if(is_array($this->_paramValueStr))
            {		
            		if(!empty($this->_messageStr)) return array('code'=>$this->_errorNum,'message'=>$this->_messageStr);
                $this->_messageStr .= implode(",", $this->_paramNameStr).'是组合参数,'; 
                foreach ($this->_paramValueStr as $k => $v)
                {
                    if($v === null)
                    {
                        $this->_messageStr .= $this->_paramNameStr[$k].'参数缺失'; 
                    }
                    else 
                    {
                        $this->_messageStr .= $this->_paramNameStr[$k].'参数值是【'.$this->_paramValueStr[$k].'】';
                    }
                }
                return array('code'=>$this->_errorNum,'message'=>$this->_messageStr);
            }
            else 
            {
                return array('code'=>$this->_errorNum,'message'=>$this->_messageStr ? $this->_messageStr : $this->_paramNameStr.'参数缺失');
            }
        }
        $paramValueStr = is_array($this->_paramValueStr) ? implode(',',$this->_paramValueStr) : $this->_paramValueStr;
        $paramNameStr  = is_array($this->_paramNameStr) ? implode(',',$this->_paramNameStr) : $this->_paramNameStr;
        return array('code'=>$this->_errorNum,'message'=>$this->_messageStr ? $this->_messageStr : $paramNameStr.'参数的值【'.$paramValueStr.'】是非法数据,根据'.get_class($this).'规则验证');
    }
    
    /**
     * @desc: 返回成功信息
     * @author: benjamin
     * @date: 2015年5月15日
     */
    function messageOk()
    {
        return array('code'=>0);
    }
    
    /**
     * @desc: 取得当前运行的类名
     * @author: benjamin
     * @date: 2015年5月15日
     */
    function getClassName()
    {
        return $this->_currentClassName;
    }
    
    /**
     * @desc: 规则处理
     * @author: benjamin
     * @date: 2015年5月15日
     */
    function rule()
    {
        if($this->doRule($this->_paramValueStr) === false)
            return $this->messageFail();
        return $this->messageOk();       
    }
    
    /**
     * @desc: 规则处理的抽象类
     * @author: benjamin
     * @date: 2015年5月15日
     */
    abstract function doRule($paramValueStr);
    
}

/**
 * @desc: 必须参数的校验
 * @author: benjamin
 * @date: 2015年5月15日
 */
class Required extends ARule
{
    function doRule($paramValueStr)
    {
        if(is_array($paramValueStr))
        {
            foreach ($paramValueStr as $k => $v)
            {
                if($v === null || $v == '')
                    return false;
            }
        }
        else 
        {
            if($paramValueStr === NULL || $paramValueStr == '')
                return false;
        }
        return true;
    }
}

/**
 * @desc: 验证格林威治时间
 * @author: benjamin
 * @date: 2015年5月15日
 */
class Time extends ARule
{
    function doRule($paramValueStr)
    {
        if(strtotime($paramValueStr))
            return true;
        return false;
    }
}

/**
 * @desc: 验证date函数生成的数据格式
 * @author: benjamin
 * @date: 2015年5月15日
 */
class Date extends ARule
{
    function doRule($paramValueStr)
    {
        if(strtotime($paramValueStr) > strtotime("2010-01-01"))
            return true;
        return false;
    }
}

/**
 * @desc: 根据用户id判断是否存在该用户
 * @author: benjamin
 * @date: 2015年5月15日
 */
class UserById extends ARule
{
    function doRule($paramValueStr)
    {

        if(M('User')->where("id=".intval($paramValueStr))->count() == 1)
            return true;
        return false;
    }
}

/**
 * @desc: 根据圈子id判断是否存在该圈子
 * @author: benjamin
 * @date: 2015年5月15日
 */
class QuanziById extends ARule
{
    function doRule($paramValueStr)
    {
        $paramsNum = count(explode(',', $paramValueStr));
        $recordNum = M("Quanzi")->where("id in ({$paramValueStr})")->count();
        if($paramsNum == $recordNum)
            return true;
        return false;
    }
}
/**
 * @desc: 根据圈子id判断是否存在该圈子
 * @author: benjamin
 * @date: 2015年5月15日
 */
class QuanziposById extends ARule
{
    function doRule($paramValueStr)
    {
        if(M("Quanzipos")->where("id={$paramValueStr}")->count() == 1)
            return true;
        return false;
    }
}

class QuanziQuanziposById extends ARule
{
    function doRule($paramValueStr)
    {
        $quanziNum = M("Quanzi")->where("id={$paramValueStr}")->count();
        $quanziposNum = M("Quanzipos")->where("id={$paramValueStr}")->count();
        if($quanziNum == 1 or $quanziposNum == 1)
            return true;
        return false;
            
    }
}

/**
 * @desc: 判断关注的类型是否在类型表中
 * @author: benjamin
 * @date: 2015年5月15日
 */
class FavoritesFavType extends ARule
{
    function doRule($paramValueStr)
    {
        if(in_array($paramValueStr, array('circle','quanzi','quanzipos')))
            return true;
        return false;
    }
}
//美否关注类型
class FollowType extends ARule
{
    function doRule($paramValueStr)
    {
        if(in_array($paramValueStr, array('topic','ask','answer','user','label','peepoint')))
            return true;
        return false;
    }
}

//美否关注类型
class ActionStatus extends ARule
{
    function doRule($paramValueStr)
    {
        if($paramValueStr[0] == 'subject')
        {
            if(in_array($paramValueStr[1], array('yes','no','none')))
            {
                return true;
            }
        }
//         elseif ($paramValueStr[0] == 'peepoint')
//         {
//             if(in_array($paramValueStr[1], array('pee')))
//             {
//                 return true;
//             }
//         }
        return false;
    }
}

//美否扩展关注类型
class IsExistFollowExtObject extends ARule
{
    function doRule($paramValueStr)
    {
        if(!in_array($paramValueStr[0],array('subject')))
            return false;
        
        if(M($paramValueStr[0])->where("id=".intval($paramValueStr[1]))->count() == 1)
            return true;
        
        return false;
    }
}
/**
 * @desc: 判断该项目id是否存在
 * @author: benjamin
 * @date: 2015年5月15日
 */
class TypeByTypeid extends ARule
{
    function doRule($paramValueStr)
    {
        if(empty($paramValueStr))return false;
        if(M('Type')->where("typeid=".$paramValueStr)->count() == 1)
            return true;
        return false;
    }
}

/**
 * @desc: 判断是否为空
 * @author: benjamin
 * @date: 2015年5月15日
 */
class IsEmpty extends ARule
{
    function doRule($paramValueStr)
    {
        if(trim($paramValueStr))
            return true;
        return false;
    }
}

/**
 * @desc: 判断是否为空
 * @author: benjamin
 * @date: 2015年5月15日
 */
class IsNotEmpty extends ARule
{
    function doRule($paramValueStr)
    {
        if(trim($paramValueStr))
            return true;
        return false;
    }
}
class ContentIsNotEmpty extends ARule{
    function doRule($paramValueStr)
    {
        if(trim($paramValueStr)!='')
            return true;
        return false;
    }
}
/**
 * @desc: 检测变量是否是数字
 * @author: benjamin
 * @date: 2015年5月15日
 */
class IsNumeric extends ARule
{
    function doRule($paramValueStr)
    {
        if(is_numeric($paramValueStr))
            return true;
        return false;
    }
}
/**
 * @desc: 检测变量是否是正数字
 * @author: benjamin
 * @date: 2015年5月15日
 */
class IsNumericPositive extends ARule
{
    function doRule($paramValueStr)
    {
        if(is_numeric($paramValueStr) && $paramValueStr)
            return true;
        return false;
    }
}

//检测变量为正数，正数包括小数，不是正整数。@xtc
class IsPositiveNumber extends  ARule{
    function doRule($paramValueStr)
    {
        if(is_numeric($paramValueStr) && $paramValueStr>0)
            return true;
        return false;
    }
}
/**
 * @desc: 检测变量是否是正整数
 * @author: benjamin
 * @date: 2015年8月14日
 */
class IsIntPositive extends ARule
{
    function doRule($paramValueStr)
    {
        if(preg_match("/^\d+$/", $paramValueStr))
//         if(is_int($paramValueStr) && $paramValueStr)
            return true;
        return false;
    }
}

/**
 * @desc: 检测是否是rss表字段
 * @author: benjamin
 * @date: 2015年5月25日
 */
class IsRssTableFields extends ARule
{
    function doRule($paramValueStr)
    {
        if(in_array($paramValueStr,array('id','foreignid','content','catid','status','userid','username','url','listorder','createtime','updatetime','title','thumb','typeid','hits','duangcount','commentscount','anonymous','source')))
            return true;
        return FALSE;
    }
}

class IsContenttype extends ARule
{
    function doRule($paramValueStr)
    {
        $paramValueStr = intval($paramValueStr);
        if(in_array($paramValueStr, array(0,1,2,3)))
            return true;
        return false;
    }
}

/**
 * @desc: 是否存在该热门地区
 * @author: benjamin
 * @date: 2015年7月13日
 */
class IsExistRegionid extends ARule
{
    function doRule($paramValueStr)
    {
        if(M("Hotcitys")->where("rid=".intval($paramValueStr))->count() > 0)
            return true;
        return false;
    }
}

/**
 * @desc: 判断特惠是否存在该排序
 * @author: benjamin
 * @date: 2015年7月13日
 */
class IsExistGetSaleListsOrder extends ARule
{
    function doRule($paramValueStr)
    {
        if(in_array(1, array(1,2,3,4,5)))
            return true;
        return false;
    }
}

/**
 * @desc: 判断特惠商品是否存在
 * @author: benjamin
 * @date: 2015年7月15日
 */
class IsExistProductid extends ARule
{
    function doRule($paramValueStr)
    {
        if(M("Product")->where("id=".intval($paramValueStr))->count() > 0)
            return true;
        return false;
    }
}
/*@desc: 判断订单号是否存在,并且是否符合退款条件
 *#author:xtc
 * 2015-07-15
 *  **/
class IsExistSnToRefund extends ARule{
    function doRule($paramValueStr)
    {
        if(M("Order")->where("pay_status=1 and sn=".($paramValueStr))->count() == 1)
            return true;
        return false;
    }
}
/*@desc: 判断订单号是否存在,并且状态是退款申请或者完成状态，pay_status=5或者4
 *#author:xtc
 * 2015-07-15
 *  **/
class IsExistRefundSn extends ARule{
    function doRule($paramValueStr)
    {
        if(M("Order")->where("pay_status in(4,5) and sn=".($paramValueStr))->count() == 1)
            return true;
        return false;
    }
}

/**
 * Class IsExistPromotionSn
 * 判断是否是分销订单sn
 */
class IsExistPromotionSn extends ARule{

    function doRule($paramValueStr)
    {
        if(trim($paramValueStr) == '')
            return false;
        if(M("promotionorder")->where("sn=".(trim($paramValueStr)))->count() == 1)
            return true;
        return false;
    }
}

/**
 * @desc: 正整数
 * @author: benjamin
 * @date: 2015年7月16日
 */
class IsPositiveInteger extends ARule
{
    function doRule($paramValueStr)
    {
        if(preg_match ("/^[1-9]+[0-9]*$/",$paramValueStr)==1)//当不为整数时
            return true;
        return false;
    }
}

/**
 * @desc: 最大数
 * @author: benjamin
 * @date: 2016年5月23日
 */
class MaxPageNum extends ARule
{
    function doRule($paramValueStr)
    {
        if(intval($paramValueStr) < 100)//最大不超过100
        {
            return true;
        }
        return false;
    }
}

/**
 * @desc: 手机号码验证
 * @author: benjamin
 * @date: 2015年7月16日
 */
class IsMobile extends ARule
{
    function doRule($paramValueStr)
    {
        if(preg_match("/1[34578]{1}\d{9}$/",$paramValueStr))
            return true;
        return false;
    }
}

/**
 * @desc: 是否存在该订单状态
 * @author: benjamin
 * @date: 2015年7月16日
 */
class IsExistOrderStatus extends ARule
{
    function doRule($paramValueStr)
    {
        if(in_array($paramValueStr,array(1,2)))
            return true;
        return false;
    }
}

/**
 * @desc: 是否存在该支付状态
 * @author: benjamin
 * @date: 2015年7月16日
 */
class IsExistPayStatus extends ARule
{
    function doRule($paramValueStr)
    {
        if(in_array($paramValueStr,array(0,1,2,3,4,5)))
            return true;
        return false;
    }
}

/**
 * @desc: 校验typeid用逗号分隔的字符串，例如 :34
 *                                          34,35,36,37
 * @author: benjamin
 * @date: 2015年7月23日
 */
class IsTypeidString extends ARule
{
    function doRule($paramValueStr)
    {
        if(preg_match("/^\d+(\,\d{1,})*$/",$paramValueStr) === 1)
            return true;
        return false;
    }
}

/**
 * @desc: 是否是匿名状态
 * @author: benjamin
 * @date: 2015年7月23日
 */
class IsAnonymousStatus extends ARule
{
    function doRule($paramValueStr)
    {
        if(in_array($paramValueStr,array(0,1)))
            return true;
        return false;
    }
}

/**
 * @desc: 是否存在模型
 * @author: benjamin
 * @date: 2015年7月24日
 */
class IsExistModule extends ARule
{
    function doRule($paramValueStr)//exists_table
    {
        if(in_array($paramValueStr, array('xinyangdiary','question','bbs','mfask','mfanswer')))
            return true;
        return false;
    }
}

/**
 * @desc: 是否是字典
 * @author: benjamin
 * @date: 2015年7月27日
 */
class IsDictionary extends ARule
{
    function doRule($paramValueStr)
    {
        if(json_decode($paramValueStr) !== NULL)
            return true;
        return false;
    }
}

/**
 * @desc: 是否是bbs表字段
 * @author: benjamin
 * @date: 2015年7月27日
 */
class IsBbsFields extends ARule
{
    function doRule($paramValueStr)
    {
        $db=D('');
        $db = DB::getInstance();
        $fieldsArr = array_keys($db->getFields(C(DB_PREFIX).'bbs'));
        if(in_array($paramValueStr,$fieldsArr))
            return true;
        return false;
    }
}

/**
 * @desc: dirty
 * @author: benjamin
 * @date: 2015年7月27日
 */
class IsGetNoteListMapData extends ARule
{
    function doRule($paramValueStr)
    {
        $rulesArr = array("userid","typeid");
        $paramValueStr = json_decode($paramValueStr,true);
        foreach($paramValueStr as $k => $v)
        {
            if(in_array($k, $rulesArr))
                return true;
        }
        return false;
        //string(20) "{"userid":"1708626"}"
        //string(40) "{"userid":100086,"typeid":1,"0":2,"1":3}"
    }
}

/**
 * @desc: 是否存在举报理由里
 * @author: lww
 * @date: 2015年7月29日
 */
class IsExistReporttype extends ARule
{
    function doRule($paramValueStr)
    {
        if(M("Reporttype")->where("id=".intval($paramValueStr))->count() > 0)
            return true;
        return false;
    }
}

/**
 * @desc: 根据questionid判断是否存在该question
 * @author: benjamin
 * @date: 2015年8月7日
 */
class IsExistQuestionid extends ARule
{
    function doRule($paramValueStr)
    {
        if(M('Question')->where("id=".intval($paramValueStr))->count() > 0)
            return true;
        return false;
    }
}

/**
 * @desc: 多参数校验例子
 * @author: benjamin
 * @date: 2015年8月10日
 */
class Cust extends ARule
{
    function doRule($paramValueStr)
    {
        if($paramValueStr[0] === null || $paramValueStr[1] === null)
            return false;
        return true;
    }
}

/**
 * @desc: 推荐人
 * @author: benjamin
 * @date: 2015年8月13日
 */
class IsPromotionuser extends ARule
{
    function doRule($paramValueStr)
    {
        if(M('Promotionuser')->where("userid=".intval($paramValueStr))->count())
            return true;
        return false;
    }
}

/**
 * @desc: 
 * @author: benjamin
 * @date: 2015年8月18日
 */
class IsWithdrawCashAmountOverBalance extends ARule
{
    function doRule($paramValueStr)
    {
        if(M('Commission')->where("userid=".intval($paramValueStr[0]))->getField("balance") > $paramValueStr[1]*100)
            return true;
        return false;
    }
}

/**
 * @desc: 身份证号码检查
 * @author: benjamin
 * @date: 2015年8月18日
 */
class IsIdNo extends ARule
{
    function doRule($paramValueStr)
    {
        if(checkIdNo($paramValueStr) !== false)
            return true;
        return false;
    }
}

/**
 * @desc: 银行卡号
 * @author: benjamin
 * @date: 2015年8月18日
 */
class IsBankNo extends ARule
{
    function doRule($paramValueStr)
    {
        if(preg_match("/^\d{16,19}$/", $paramValueStr))
            return true;
        return false;
    }
}


class hospitalIdExist extends  ARule{
    function doRule($paramValueStr)
    {
        if(empty($paramValueStr))return false;
        $rs=M('hospital')->where('id='.$paramValueStr)->find();
        if($rs){
            return true;
        }else{
            return false;
        }
    }
}

class UserByIdAndPassword extends  ARule{
    function doRule($paramValueStr)
    {
        if(empty($paramValueStr))return false;
        $rs=M('User')->where('id='.intval($paramValueStr[0]))->find();
        
        if($rs && $rs['password'] == sysmd5($paramValueStr[1])){
            return true;
        }else{
            return false;
        }
    }
}


class AskById extends  ARule{
    function doRule($paramValueStr)
    {
        if(empty($paramValueStr)){
            return false;
        }
        $rs=M('mfask')->where('id='.$paramValueStr)->find();
        if($rs){
            return true;
        }else{
            return false;
        }
    }
}

/**
* @desc 判断是否可以对美否问题点赞
* @param 
* @return 
* @example 
* @date 2015年9月2日
* @author benjamin
*/
class canDoMfAskPraise extends ARule
{
    function doRule($paramValueStr)
    {
        $countNum = M("Mfpraise")->where("foreignid=".$paramValueStr[0]." and userid=".$paramValueStr[1]." and source=5")->count();
        if($countNum == 0)
            return true;
        return false;
    }
}

/**
* @desc 判断是否可以取消美否问题点赞
* @param 
* @return 
* @example 
* @date 2015年9月2日
* @author benjamin
*/
class canUnDoMfAskPraise extends ARule
{
    function doRule($paramValueStr)
    {
        $countNum = M("Mfpraise")->where("foreignid=".$paramValueStr[0]." and userid=".$paramValueStr[1]." and source=5")->count();
        if($countNum > 0)
            return true;
        return false;
    }
}

/**
* @desc 判断是否可以对美否回复点赞
* @param 
* @return 
* @example 
* @date 2015年9月9日
* @author benjamin
*/
class canDoMfAnswerPraise extends ARule
{
    function doRule($paramValueStr)
    {
        $countNum = M("Mfpraise")->where("foreignid=".$paramValueStr[0]." and userid=".$paramValueStr[1]." and source=6")->count();
        if($countNum == 0)
            return true;
        return false;
    }
}

/**
 * @desc 判断是否可以对美否专题回复点赞
 * @param
 * @return
 * @example
 * @date 2015年9月9日
 * @author benjamin
 */
class canDoMfCommentPraise extends ARule
{
    function doRule($paramValueStr)
    {
        $countNum = M("Mfpraise")->where("foreignid=".$paramValueStr[0]." and userid=".$paramValueStr[1]." and source=".MFPRAISE_FOR_MFSPECIALCOMMENT)->count();
        if($countNum == 0)
            return true;
        return false;
    }
}

/**
* @desc 判断是否可以取消美否回复点赞
* @param 
* @return 
* @example 
* @date 2015年9月9日
* @author benjamin
*/
class canUnDoMfAnswerPraise extends ARule
{
    function doRule($paramValueStr)
    {
        $countNum = M("Mfpraise")->where("foreignid=".$paramValueStr[0]." and userid=".$paramValueStr[1]." and source=6")->count();
        if($countNum > 0)
            return true;
        return false;
    }
}

/**
 * @desc 判断是否可以取消美否专题回复点赞
 * @param
 * @return
 * @example
 * @date 2015年9月9日
 * @author benjamin
 */
class canUnDoMfCommentPraise extends ARule
{
    function doRule($paramValueStr)
    {
        $countNum = M("Mfpraise")->where("foreignid=".$paramValueStr[0]." and userid=".$paramValueStr[1]." and source=".MFPRAISE_FOR_MFSPECIALCOMMENT)->count();
        if($countNum > 0)
            return true;
        return false;
    }
}


/**
 * @desc 判断是否可以给定类型回复点赞
 * @param
 * @return
 * @example
 * @date 2015年11月10日
 * @author lww
 */
class canDoPraiseByType extends ARule
{
    function doRule($paramValueStr)
    {
    		switch($paramValueStr[2]){
    			case 'mfactivityshow':
    				$count = M("mfactivityshow")->where("id=".$paramValueStr[0])->count();
    				$source = MFPRAISE_FOR_ACTIVITYSHOW;
    				break;
    			case 'mfactivityshowcomment':
    				$count = M("mfcomment")->where("id=".$paramValueStr[0])->count();
    				$source = MFPRAISE_FOR_ACTIVITYSHOWCOMMENT;
    				break;
    			case 'mfarticlecomment':
    				$count = M("mfcomment")->where("id=".$paramValueStr[0])->count();
    				$source = MFPRAISE_FOR_ARTICLECOMMENT;
    				break;
    		}
    		if($source && $count) return true;
    		/*可以无限点赞动作，数据库只保存一次
        $countNum = M("Mfpraise")->where("foreignid=".$paramValueStr[0]." and userid=".$paramValueStr[1]." and source=".$source)->count();
        if($countNum == 0)
            return true;
        */
        return false;
        
    }
}



/**
 * @desc 判断是否可以取消给定类型回复点赞
 * @param
 * @return
 * @example
 * @date 2015年11月10日
 * @author lww
 */
class canUnDoPraiseByType extends ARule
{
    function doRule($paramValueStr)
    {
    		switch($paramValueStr[2]){
    			case 'mfactivityshow':
    				$count = M("mfactivityshow")->where("id=".$paramValueStr[0])->count();
    				$source = MFPRAISE_FOR_ACTIVITYSHOW;
    				break;
    			case 'mfactivityshowcomment':
    				$count = M("mfcomment")->where("id=".$paramValueStr[0])->count();
    				$source = MFPRAISE_FOR_ACTIVITYSHOWCOMMENT;
    				break;
    			case 'mfarticlecomment':
    				$count = M("mfcomment")->where("id=".$paramValueStr[0])->count();
    				$source = MFPRAISE_FOR_ARTICLECOMMENT;
    				break;
    		}
    		if(!$source && !$count) return false;

        $countNum = M("Mfpraise")->where("foreignid=".$paramValueStr[0]." and userid=".$paramValueStr[1]." and source=".$source)->count();
        if($countNum > 0)
            return true;
        return false;
    }
}

class NoFavorite extends ARule
{
    function doRule($paramValueStr)
    {
        //id,userid,whattofavorite
        $map['foreign_type'] = $paramValueStr[2];//$_REQUEST['whattofavorite'];
        $map['userid']       = $paramValueStr[1];
        $map['foreign_id']   = $paramValueStr[0];
        $rs = M("mffavorites")->where($map)->find();
        if(empty($rs))
            return true;
        return false;
    }
}

/**
 * @desc 判断是否可以收藏给定类型
 * @param
 * @return
 * @example
 * @date 2015年12月08日
 * @author lww
 */
class canDoFavoriteByType extends ARule
{
    function doRule($paramValueStr)
    {
    		$foreign_type = $paramValueStr[2];
    		$tablesArr = array('subject');//无前缀表名
    		if(in_array($paramValueStr[2], $tablesArr))
    		{
    		    $table = $paramValueStr[2];
    		}
    		else
    		{
    		    $table = 'mf' . $paramValueStr[2];
    		}

    		if(!IsTable::doRule(lcfirst($table)))
    		  return false;
    	
        if(!is_numeric($paramValueStr[0])){
            return false;
        }
        
        $count = M($table)->where("status <> 2 and  id=".$paramValueStr[0])->count();

    	if($foreign_type && $count) return true;
    	
        return false;
    }
}

/**
 * @desc 判断是否可以取消收藏给定类型
 * @param
 * @return
 * @example
 * @date 2015年12月08日
 * @author lww
 */
class canUnDoFavoriteByType extends ARule
{
    function doRule($paramValueStr)
    {
		$foreign_type = $paramValueStr[2];
		
		$tablesArr = array('subject');//无前缀表名
		if(in_array($paramValueStr[2], $tablesArr))
		{
		    $table = $paramValueStr[2];
		}
		else
		{
		    $table = 'mf' . $paramValueStr[2];
		}

		if(!IsTable::doRule(lcfirst($table)))
		  return false;
    	
        if(!is_numeric($paramValueStr[0])){
            return false;
        }
        
        $count = M($table)->where("status <> 2 and  id=".$paramValueStr[0])->count();
        
        if(!$foreign_type || !$count) return false;

        $countNum = M("Mffavorites")->where("foreign_id=".$paramValueStr[0]." and userid=".$paramValueStr[1]." and foreign_type ='".$foreign_type."'")->count();
        if($countNum > 0)
            return true;
        return false;
    }
}

/**
 * @desc: 判断是否可以收藏给定类型
 * @author: benjamin
 * @date: 2016年5月27日
 */
class DoSubscription extends ARule
{
    //id,userid,type
    function doRule($paramValueStr)
    {
        if(!UserById::doRule($paramValueStr[1]))
            return false;
        
        if(!IsTable::doRule(lcfirst($paramValueStr[2])))
            return false;
             
        if(!is_numeric($paramValueStr[0]))
            return false;

        $count = M($paramValueStr[2])->where("status <> 2 and  id=".$paramValueStr[0])->count();

        if($paramValueStr[2] && $count) 
            return true;
        
        return false;
    }
}

/**
 * @desc: 判断是否已经订阅
 * @author: benjamin
 * @date: 2016年5月27日
 */
class NoSubscription extends ARule
{
    function doRule($paramValueStr)
    {
        //id,userid,type
        $map['foreigntype'] = $paramValueStr[2];
        $map['userid']      = $paramValueStr[1];
        $map['foreignid']   = $paramValueStr[0];
        $rs = M("subscription")->where($map)->find();
        if(empty($rs))
            return true;
        return false;
    }
}

/**
 * @desc: 判断是否可以取消收藏给定类型
 * @author: benjamin
 * @date: 2016年5月27日
 */
class CancelSubscription extends ARule
{
    function doRule($paramValueStr)
    {
        //id,userid,type
        if(!UserById::doRule($paramValueStr[1]))
            return false;
        
        if(!IsTable::doRule(lcfirst($paramValueStr[2])))
            return false;
             
        if(!is_numeric($paramValueStr[0]))
            return false;

        $count = M($paramValueStr[2])->where("status <> 2 and  id=".$paramValueStr[0])->count();

        if(!$paramValueStr[2] || !$count) 
            return false;

        $countNum = M("subscription")->where("foreignid=".$paramValueStr[0]." and userid=".$paramValueStr[1]." and foreigntype ='".$paramValueStr[2]."'")->count();
        if($countNum > 0)
            return true;
        
        return false;
    }
}

/**
 * @desc: 判断顶和踩是否在限定关键词内
 * @author: benjamin
 * @date: 2016年5月27日
 */
class ActionType extends ARule
{
    function doRule($paramValueStr)
    {
        if(in_array($paramValueStr, array('good','bad')))
            return true;
        return false;
    }
}

/**
 * @desc: 判断是否可以顶
 * @author: benjamin
 * @date: 2016年5月27日
 */
class DoLike extends ARule
{
    //foreignid,foreigntype,actiontype,machine
    function doRule($paramValueStr)
    {
        if(!is_numeric($paramValueStr[0]))
            return false;
        
        if(!IsTable::doRule(lcfirst($paramValueStr[1])))
            return false;
        
        if(!in_array($paramValueStr[2], array('good','bad'))) 
            return false;
        
        $model =  trim($paramValueStr[3]);
        if(empty($model))
            return false;
        
        $count = M($paramValueStr[1])->where("status <> 2 and  id=".$paramValueStr[0])->count();
        if($count)
            return true;
        
        return false;
    }
}

/**
 * @desc: 判断是否已经顶
 * @author: benjamin
 * @date: 2016年5月27日
 */
class NoLike extends ARule
{
    function doRule($paramValueStr)
    {
        //foreignid,foreigntype,actiontype,machine
        $map['foreignid']   = $paramValueStr[0];
        $map['foreigntype'] = $paramValueStr[1];
        $map['actiontype']  = $paramValueStr[2];
        $map['machine']     = crc32($paramValueStr[3]);
        
        $rs = M("dingcai")->where($map)->find();
        if(empty($rs))
            return true;
        return false;
    }
}

//验证问题id存不存在
class AskBuyId extends ARule{
    function doRule($paramValueStr)
    {
        if(empty($paramValueStr)){
            return false;
        }
        $countNum = M("mfask")->where("status <> 2 and id=".$paramValueStr)->count();
        if($countNum > 0)
            return true;
        return false;
    }
}


class AnswerById extends  ARule{
    function doRule($paramValueStr)
    {
        if(empty($paramValueStr)){
            return false;
        }
        if(!is_numeric($paramValueStr)){
            return false;
        }
        $countNum = M("mfanswer")->where("status <> 2 and  id=".$paramValueStr)->count();
        if($countNum > 0)
            return true;
        return false;
    }
}


class CommentType extends  ARule{
    function doRule($paramValueStr)
    {
        if(empty($paramValueStr)){
            return false;
        }
        if(in_array($paramValueStr,array(1,2)))
            return true;
        return false;
    }
}


class CommentParentId extends  ARule{
    function doRule($paramValueStr)
    {
        if(empty($paramValueStr)){
            return true;//可以为0
        }elseif(!is_numeric($paramValueStr)){
            return  false;
        }elseif(!empty($paramValueStr)&&is_numeric($paramValueStr)){
            $res=M('mfcomment')->where('id='.$paramValueStr)->find();
            if($res){
                return true;
            }else{
                return false;
            }

        }



    }
}


class LabelsById extends ARule{
    function doRule($paramValueStr)
    {
        if(empty($paramValueStr)){
            return false;
        }
        if(!is_numeric($paramValueStr)){
            return false;
        }
        $rs=M('labels')->where('id='.$paramValueStr)->find();
        if($rs)
            return true;
        return false;
    }
}
/**
 * @desc: 
 * @author: benjamin
 * @date: 2016年8月19日
 */
class FilmproductById extends ARule{
    function doRule($paramValueStr)
    {
        if(M('filmproduct')->where('id='.intval($paramValueStr))->find())
            return true;
        return false;
    }
}
/**
 * @desc: 
 * @author: benjamin
 * @date: 2016年8月19日
 */
class UseraddressById extends ARule{
    function doRule($paramValueStr)
    {
        if(M('user_address')->where('id='.intval($paramValueStr))->find())
            return true;
        return false;
    }
}
/**
 * @desc: 
 * @author: benjamin
 * @date: 2016年8月19日
 */
class FilmproductdataById extends ARule{
    function doRule($paramValueStr)
    {
        if(M('Filmproductdata')->where('id='.intval($paramValueStr))->find())
            return true;
        return false;
    }
}

class CheckForDynamicUser extends ARule{
    function doRule($paramValueStr)
    {
        if(empty($paramValueStr)){
            return true;
        }
        if(!is_numeric($paramValueStr)){
            return false;
        }
        $rs=M('user')->where('id='.$paramValueStr)->find();
        if($rs)
            return true;
        return false;
    }
}

/**
 * @desc: 
 * @author: benjamin
 * @date: 2015年10月21日
 */
class SpecialById extends  ARule{
    function doRule($paramValueStr)
    {
        if(empty($paramValueStr)){
            return false;
        }
        if(!is_numeric($paramValueStr)){
            return false;
        }
        $countNum = M("mfspecial")->where("status <> 2 and id=".$paramValueStr)->count();
        if($countNum > 0)
            return true;
        return false;
    }
}

class ActionToDoValidateByid extends  ARule{
    function doRule($paramValueStr)
    {
        if( $paramValueStr[0] &&  $paramValueStr[1]){
	        if( !in_array( $paramValueStr[0], array('mfspecial','mfactivityshow','mfproductrecommend') ) )return false;
	
	        if(M($paramValueStr[0])->where('id='.intval($paramValueStr[1]))->count()){
	            return true;
	        }
	        return false;
	      }
	      return false;
    }

}

/**
 * @desc: 验证两个userid邀请状态是否成立
 *        自己不可邀请自己
 * @author: benjamin
 * @date: 2015年10月23日
 */
class IsSelfInvite extends  ARule{
    function doRule($paramValueStr)
    {
        if($paramValueStr[0] == $paramValueStr[1])
            return false;
        return true;
    }
}

/**
 * @desc: 判断该用户是可以被邀请
 *        已有邀请人的，不可再次被邀请
 * @author: benjamin
 * @date: 2015年10月23日
 */
class ExistInviteUserid extends ARule
{
    function doRule($paramValueStr)
    {
        if(M("User")->where("id=".$paramValueStr)->getField("invite_userid"))
            return false;
        return true;
    }
}

/**
 * @desc: 通过评论Mfcomment表的id判断该条记录是否存在 true-存在 false-不存在
 * @author: benjamin
 * @date: 2015年10月30日
 */
class MfcommentById extends ARule
{
    function doRule($paramValueStr)
    {
        if(M("Mfcomment")->where("id=".$paramValueStr)->count())
            return true;
        return false;
    }
}

//验证说说主题id是否存在
class IsActivityid extends  ARule{

    function doRule($paramValueStr)
    {
        if(!is_numeric($paramValueStr)){
            return false;
        }
        if(M("Mfactivity")->where("status <> 2 and  id=".intval($paramValueStr))->count())
            return true;
        return false;
    }
}


class IsActivityshowid extends ARule{

    function doRule($paramValueStr)
    {
        if(!is_numeric($paramValueStr)){
            return false;
        }
        if(M("Mfactivityshow")->where("status <> 2 and  id=".intval($paramValueStr))->count())
            return true;
        return false;
    }
}

class IsProductrecommendid extends ARule{

    function doRule($paramValueStr)
    {
        if(!is_numeric($paramValueStr)){
            return false;
        }
        if(M("mfproductrecommend")->where("status = 1 and  id=".intval($paramValueStr))->count())
            return true;
        return false;
    }
}


class jumpmethodByMfbanner extends  ARule{
    function doRule($paramValueStr)
    {
	      if( in_array( $paramValueStr, array('ask','answer','labels','special','activity','productrecommend','other') ) )
	      	return true;
	      return false;
    }

}


// 验证表是否存在。表必须在限定的表之中。
class IsInfo extends ARule{
    function doRule($paramValueStr)
    {
        $infos=array(
            'mfask','mfanswer','mfcomment','mfactivityshow'
        );

        if(in_array($paramValueStr,$infos)){
            return true;
        }else{
            return false;
        }

    }
}


class IsProductType extends ARule{

    function doRule($paramValueStr)
    {
        if(!is_numeric($paramValueStr)){
            return false;
        }
        $types=array(
            '1','2'
        );

        if(in_array($paramValueStr,$types)){
            return true;
        }else{
            return false;
        }

    }

}


class IsCtype extends ARule{
    function doRule($paramValueStr)
    {
        if(!is_numeric($paramValueStr)){
            return false;
        }
        $ctypes=array(
            'answer','special','activity','productrecommend'
        );

        if(in_array($paramValueStr,$ctypes)){
            return true;
        }else{
            return false;
        }

    }
}

class IsProductid extends ARule{

    function doRule($paramValueStr)
    {
        if(!is_numeric($paramValueStr)){
            return false;
        }
        if(M("mfproduct")->where("status = 1 and  id=".intval($paramValueStr))->count())
            return true;
        return false;
    }
}
class IsContact extends ARule
{
    function doRule($paramValueStr)
    {
        if(empty($paramValueStr) || filter_var($paramValueStr,FILTER_VALIDATE_EMAIL) || preg_match("/1[34578]{1}\d{9}$/",$paramValueStr) || preg_match("/^[1-9]\d{4,11}$$/",$paramValueStr))//email|mobile|QQ
            return true;
        return false;
    }
}

class IsLeastone extends ARule
{
    function doRule($paramValueStr)
    {return false;
        if(empty($paramValueStr[0]) && empty($paramValueStr[1]))
            return false;
        return true;
    }
}




/**
 * @desc: 查找有无数据表
 */
class IsTable extends ARule
{
    function doRule($paramValueStr)
    {
    		$table = M('')->query('SHOW TABLES LIKE "'.C("DB_PREFIX").$paramValueStr.'"');
        if(empty($table))
            return false;
        return true;
    }
}

/**
 * @desc: 美否文章输出时候带样式
 */
class MfarticleStyleLimit extends ARule
{
    function doRule($paramValueStr)
    {
        if($paramValueStr == 'no')
            return true;
        return false;
    }
}

/**
 * @desc: 根据提供的字段查找对应表中的数据
 */
class ModelByParam extends ARule
{
    function doRule($paramValueStr)
    {
    		foreach ($this->_paramNameStr as $k => $v)
        {	
        	if($k === 'model')continue;
        	$map[$v] = $this->_paramValueStr[$k];
        }
        //var_dump(M($paramValueStr['model'])->where($map)->count());
        //echo M()->getLastSql();
        //die;
        if(IsTable::doRule(lcfirst($paramValueStr['model'])) && M($paramValueStr['model'])->where($map)->count())

            return true;
        return false;
    }
}


//判断是否是表中的数据
class IsTableid extends ARule{

    function doRule($paramValueStr)
    {
    		if(!IsTable::doRule(lcfirst($paramValueStr[0])))
    		  return false;
    	
        if(!is_numeric($paramValueStr[1])){
            return false;
        }
        if(M($paramValueStr[0])->where("status <> 2 and  id=".intval($paramValueStr[1]))->count())
            return true;
        return false;
    }
}
/**
 * @desc: 6期我的收藏参数类型判断
 * @author: benjamin
 * @date: 2016年3月2日
 */
class V6GetFavoritesListTypeValidate extends ARule{

    function doRule($paramValueStr)
    {
        if(in_array($paramValueStr, array('article','productrecommend','activity')))
            return true;
        return false;
    }
}

/**
 * @desc: 判断id是否是framehomepage内数据
 * @author: benjamin
 * @date: 2015年10月23日
 */
class FrameHomepageById extends ARule
{
    function doRule($paramValueStr)
    {
        if(M("Framehomepage")->where("id=".$paramValueStr)->count() == 0)
            return false;
        return true;
    }
}

/**
 * @desc: 判断id是否是subject内数据
 * @author: benjamin
 * @date: 2015年10月23日
 */
class SubjectById extends ARule
{
    function doRule($paramValueStr)
    {
        if(M("Subject")->where("id=".$paramValueStr)->count() == 0)
            return false;
        return true;
    }
}

/**
 * @desc: 判断id是否是subject内数据
 * @author: benjamin
 * @date: 2015年10月23日
 */
class SearchScope extends ARule
{
    function doRule($paramValueStr)
    {
        $arr = array('all','subject','article','album','video');
        if(in_array($paramValueStr, $arr))
            return true;
        return false;
    }
}

/**
 * @desc: 判断id是否是subject内数据
 * @author: benjamin
 * @date: 2015年10月23日
 */
class CommentForeigntypeScope extends ARule
{
    function doRule($paramValueStr)
    {
        $arr = array('subject','mfarticle','mfcomment');
        if(in_array($paramValueStr, $arr))
            return true;
        return false;
    }
}
/**
 * @desc: 判断id是否是subject内数据
 * @author: benjamin
 * @date: 2015年10月23日
 */
class IsExistForeign extends ARule
{
    function doRule($paramValueStr)
    {
        //foreignid,foreigntype
        if(M($paramValueStr[1])->where("id=".$paramValueStr[0])->count())
            return true;
        return false;
    }
}

/**
 * @desc: 专题内容类型
 * @author: benjamin
 * @date: 2016年6月29日
 */
class SubjectContentType extends ARule
{
    function doRule($paramValueStr)
    {
        if(in_array($paramValueStr, array('actor','television')))
        {
            return true;
        }
        return false;
    }
}

/**
 * @desc: 排序方式
 * @author: benjamin
 * @date: 2016年6月29日
 */
class SortScope extends ARule
{
    function doRule($paramValueStr)
    {
        if(in_array($paramValueStr, array('desc','asc')))
        {
            return true;
        }
        return false;
    }
}

/**
 * @desc: 电影商城排序范围
 * @author: benjamin
 * @date: 2016年8月19日
 */
class SortRowScope extends ARule
{
    function doRule($paramValueStr)
    {
        if(in_array($paramValueStr, array('listorder','updatetime','price','sell')))
        {
            return true;
        }
        return false;
    }
}
/**
 * @desc: 订单状态范围
 * @author: benjamin
 * @date: 2016年8月19日
 */
class OrderScope extends ARule
{
    function doRule($paramValueStr)
    {
        if(in_array($paramValueStr, array('all','submit','complete','ship','pay')))
        {
            return true;
        }
        return false;
    }
}

/**
 * @desc: 
 * @author: benjamin
 * @date: 2016年8月23日
 */
class FilmorderById extends ARule
{
    function doRule($paramValueStr)
    {
        if(M("filmorder")->where("id=".intval($paramValueStr))->count())
            return true;
        return false;
    }
}
/**
 * @desc: 
 * @author: benjamin
 * @date: 2016年8月23日
 */
class FilmorderBySn extends ARule
{
    function doRule($paramValueStr)
    {
        if(M("filmorder")->where("sn=".$paramValueStr)->count())
        {
            return true;
        }
        return false;
    }
}
/**
 * @desc: 
 * @author: benjamin
 * @date: 2016年8月22日
 */
class FilmframehomepageById extends ARule
{
    function doRule($paramValueStr)
    {
        if(M("filmframehomepage")->where("id=".intval($paramValueStr))->count())
            return true;
        return false;
    }
}


/**
 * @desc:
 * @author: benjamin
 * @date: 2016年8月22日
 */
class AreaById extends ARule
{
    function doRule($paramValueStr)
    {
        if(M("area")->where("id=".intval($paramValueStr))->count())
            return true;
        return false;
    }
}

/**
 * @desc: 判断appid是否是支付宝的数据
 * @author: benjamin
 * @date: 2015年10月23日
 */
class AppidScope extends ARule
{
    function doRule($paramValueStr)
    {
        //            支付宝appid        支付宝美否应用appid
        $arr = array('2016080301697228','2016090901874597','2016082401797541','wxe55bf361d0d86c13','wx625f4d094e46116a');
        if(in_array($paramValueStr, $arr))
            return true;
        return false;
    }
}

/**
 * @desc: 判断appid是否是支付宝的数据
 * @author: benjamin
 * @date: 2015年10月23日
 */
class PaytypeScope extends ARule
{
    function doRule($paramValueStr)
    {
        //            支付宝appid        支付宝美否应用appid
        $arr = array('alipayapp','alipaywap','wechatpayapp');
        if(in_array($paramValueStr, $arr))
            return true;
        return false;
    }
}

/**
 * @desc: 判断是否是post提交
 * @author: benjamin
 * @date: 2017年6月6日
 */
//class IsPost extends ARule
//{
//    function doRule($paramValueStr)
//    {
//        if(){
//            return true;
//        }
//        return false;
//    }
//}