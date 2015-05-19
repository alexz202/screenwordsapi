<?php
/*
数据操作类
*/


abstract class AbstractDao
{
   protected $dao;
	protected $_tablecount;
    
    public function __construct( $isdebug = true, $iTableCount=100 ){
        $this->dao = DAO::getInstance( $isdebug );
        $this->_tablecount = $iTableCount;
    }

    /**
     * 根据用户iUid的末尾两位来选择插入数据表
     * 
     * @param type $iUin
     * @return type 
     */
    protected function getSuffix( $iUin, $tableCount = '' ){
    	return 'stux';
        if( $tableCount == '' ){
            return fmod( $iUin, $this->_tablecount );
        }
        else{
            return fmod( $iUin, $tableCount );
        }
        
    //	return 'stux';
    }
    
    protected function filterParameters($param) {
        if (is_array($param)){
            foreach ($param as $k => $v){
                $param[$k] = $this->filterParameters($v); //recursive
            }
        }
        elseif (is_string($param)){
//            $trans = array(
//                '<' => '&lt;',
//                '>' => '&gt;'
//            );
//            $param = strtr($param,$trans);
//            $param = mysql_real_escape_string($param);
            $param = htmlspecialchars($param);
            // 过滤引号
            $trans = array(
                "'" => '&apos;'
            );
            $param = strtr($param,$trans);
            //$param = mysql_real_escape_string($param);
        }
        return $param;
    }
    
 protected function add($params,$condition=array()){
    	$table=$condition['table'];
    	unset($condition['table']);
    	$cols = implode(",", array_keys($params));
    	$strval=$this->_getStrVal($params);
       $sql="insert into ".$table." (".$cols.")values(".$strval.")";
      $res = $this->dao->db->ExecUpdate($sql);
		if($res>0) {
			return $res;
		} else {
			return false;
		}
    }
    protected function save($param,$condition=array()){
    	$table=$condition['table'];
    	unset($condition['table']);
    	$strval=$this->getStrKeyVal($param);
       $strcondition=$this->getStrKeyVal($condition);
       $sql="UPDATE ".$table." SET ".$strval." where ".$strcondition;	
       $res = $this->dao->db->ExecUpdate($sql);
		if($res>0) {
			return true;
		} else {
			return false;
		}
    }
    protected function delete($condition=array()){
    	$table=$condition['table'];
    	unset($condition['table']);
    	$strcondition=$this->getStrKeyVal_where($condition);
    	$sql="DELETE FROM ".$table." WHERE ".$strcondition;
      $res = $this->dao->db->ExecUpdate($sql);
		if($res>0) {
			return true;
		} else {
			return false;
		}
    }
   protected function _getStrVal($params){
        $vals = array_values( $params );
        $strVal = "";
        foreach( $vals as $value ){
            if( $strVal == "" ){
                $strVal = "'".$value."'";
            }
            else{
                $strVal = $strVal.", '".$value."'";
            }
        }
        return $strVal;
    }
    protected function getStrKeyVal($params){
    	$strVal="";
    foreach($params as $k=>$value ){
            if( $strVal == "" ){
                $strVal = $k."='".$value."'";
            }
            else{
                $strVal = $strVal.",".$k."='".$value."'";
            }
        }
    	return $strVal;
    }
    protected function getStrKeyVal_where($params){
    	$strVal="";
    foreach($params as $k=>$value ){
            if( $strVal == "" ){
                $strVal = $k."='".$value."'";
            }
            else{
                $strVal = $strVal." and ".$k."='".$value."'";
            }
        }
    	return $strVal;
    }  
}
?>
