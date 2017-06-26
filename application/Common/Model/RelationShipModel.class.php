<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2012 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: benjamin
// +----------------------------------------------------------------------

// defined('THINK_PATH') or exit();
/**
 * ThinkPHP关联模型扩展 
 * @category   Extend
 * @package  Extend
 * @subpackage  Model
 * @author    benjamin
 */
namespace Common\Model;
use Think\Model;

class RelationShipModel extends Model {
    // 连接表对象 benjamin
    protected $_joinObjArr      =   array();
    protected $_dataRebuildArr  =   array();
    // 数据处理对象 benjamin
    protected $_filterObjArr    =   array();
    // 返回数据查询字段 benjamin
    protected $_listsArr        =   array();
    
    
    /**
     * 查询SQL组装 join
     * @access public
     * @param mixed $join
     * @return Model
     */
    public function join($join='')
    {
    	if(!empty($join)){
    		parent::join($join,$type='INNER');
    	}
//         if(is_array($join))
//         {
//             $this->options['join']      =   $join;
//         }
//         elseif(!empty($join))
//         {
//             $this->options['join'][]    =   $join;
//         }
        #聚合表 benjamin 2015-03-26
        if(empty($join))
        {
            foreach ($this->_joinObjArr as $k => $v)
            {
                $v->dataRebuild = $this->_dataRebuildArr[$k];
                $this->_listsArr = $v->join($this->_listsArr,$this->_dataRebuildArr[$k]);
            }
        }
    
        return $this;
    }
    
    /**
     * @desc: 增加关联表
     * @access:public
     * @param: obj $joinObj 关联的表对象
     * @return: Obj
     * @example: $userObj = D('Home/User');
     *           $userObj->addJoinObj(new UserDocAdept());
     * @date: 2015年3月26日
     * @author: benjamin
     */
    public function addJoinObj($joinObj,$dataRebuild = 'rebuild')
    {
        $this->_joinObjArr[]  = $joinObj;
        $this->_dataRebuildArr[] = $dataRebuild;
    }
    
    /**
     * @desc: 增加数据过滤对象
     * @access: public
     * @param: unknowtype
     * @return: Obj
     * @example: $userObj = D('Home/User');
     *           $userObj->addFilterObj(new UserDocInfoFilter());
     * @date: 2015年3月26日
     * @author: benjamin
     */
    public function addFilterObj($filterObj)
    {
        $this->_filterObjArr[] = $filterObj;
    }
    
    /**
     * @desc: 数据处理
     * @access:
     * @param: unknowtype
     * @return:
     * @example:
     * @date: 2015年5月4日
     * @author: benjamin
     */
    public function filter()
    {
        foreach($this->_filterObjArr as $k => $v)
        {
            $this->_listsArr = $v->filter($this->_listsArr);
        }
        return $this;
    }
    
    /**
     * @desc: 获取数据
     * @access:public
     * @param: unknowtype
     * @return: Array
     * @example: $userObj = D('Home/User');
     *           $userObj->addJoinObj(new UserDocAdept());
     *           $userObj->addJoinObj(new DocAdeptType());
     *           $userObj->addJoinObj(new UserDocInfo());
     *           $userObj->addFilterObj(new UserDocInfoFilter());
     *           $userArr = $userObj->single(new SingleUserById($idNum))->join()->filter()->getLists();//医生相关数据
     * @date: 2015年3月26日
     * @author: benjamin
     */
    public function getLists()
    {
        return $this->_listsArr;
    }
    
    /**
     * @desc: 设置数据
     * @access:public
     * @param: array $listsArr 多是查询（select和find）后的数据
     * @return: array
     * @example:
     * @date: 2015年5月7日
     * @author: benjamin
     */
    public function setLists($listsArr)
    {
        $this->_listsArr = $listsArr;
        return $this;
    }
    
    #benjamin
    public function single($singleObj)
    {
        $this->_listsArr = $singleObj->single();
        return $this;
    }
    
    #benjamin
    public function lists($listObj)
    {
        $this->_listsArr = $listObj->lists();
        return $this;
    }
}

/**
 * @desc: 多条记录抽象类
 * @author: yyf
 * @date: 2015年3月12日
 */
abstract class ALists
{
    /**
     * @desc: 分页构造函数
     * @access:
     * @param: array $map
     * @param: int $page 记录开始
     * @param: int $limit 记录结束
     * @param: string $order
     * @param: string $field
     * @param: bool $except
     * @return: void
     * @example:
     * @date: 2015年3月31日
     * @author: benjamin
     */
    function __construct($map=null,$firstRow=0,$listRows=10,$order='id desc',$field="*",$except=false)
    {
        $this->map    = $map;
        //         $this->page   = $firstRow;
        $this->firstRow   = $firstRow;
        $this->order  = $order;
        //         $this->limit  = $listRows;
        $this->listRows  = $listRows;
        $this->field  = $field;
        $this->except = $except;
    }

    abstract function lists();
}

/**
 * @desc: 单条记录抽象类
 * @author: yyf
 * @date: 2015年3月12日
 */
abstract class ASingle
{
    function __construct($map=null,$field='*',$except=false)
    {
        $this->map    = $map;
        $this->field  = $field;
        $this->except = $except;
    }
    abstract function single();
}

/**
 * @desc: 数据处理抽象类
 * @author: yyf
 * @date: 2015年3月12日
 */
abstract class AFilter
{
    abstract function filter($lists);
}

abstract class AJoin
{
    /**
     * @desc:
     * @access:
     * @param: string $field 字段名，仅支持字符串，不同字段用英文的逗号分割
     * @param: bool $except 布尔型 是否排除，默认为false，如果为true表示定义的字段为数据表中排除field参数定义之外的所有字段
     * @param: array $map 等同thinkphp的CURD中的数组查询条件
     * @return: void
     * @example:
     * @date: 2015年3月30日
     * @author: benjamin
     */
    function __construct($map = null,$field = "*",$except=false)
    {
        $this->field  = $field;
        $this->except = $except;
        $this->map    = $map;
    }

    abstract function join($lists,$rebuild);

    function rebuild($arr)
    {
        return $arr;
    }

    function filter($arr)
    {
        return $arr;
    }
}