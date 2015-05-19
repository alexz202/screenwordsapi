<?php
/**
 * 数据操作类, 单例
 * 
 * @author jensenzhang
 * 
*/
class DAO 
{
    public $db;
	protected $_config = '';
    
    /**
     * singleton
     * @var type 
     */
    private static $instance;
    
    private function __construct( $isdebug = true ){
        $this->_config = GetGlobalConfig();
		
        if( debug ){
            $this->db = new DBMysql(
                $this->_config["DB"]["host"],
                $this->_config["DB"]["user"],
                $this->_config["DB"]["password"],
                $this->_config["DB"]["database"],
                $this->_config["DB"]["port"]
            );
        }
        else{
            $this->db = new DBMysql(
                $this->_config["DB"]["host"],
                $this->_config["DB"]["user"],
                $this->_config["DB"]["password"],
                $this->_config["DB"]["database"],
                $this->_config["DB"]["port"]
            );
        }

		try {
			$this->db->ExecTrans(array('set names "utf8"'));
		} catch (exception $e) {
			//这里由于现有oss库不能setname，所以这样处理下
		}
    }

    public static function getInstance( $isdebug = true ) {
    	if(!isset(self::$instance)) {
    	    $c = __CLASS__;
    	    self::$instance = new $c( $isdebug );    	
    	}
    	return self::$instance;
    }
    
    protected function filterParameters($param) {
        if (is_array($param)){
            foreach ($param as $k => $v){
                $param[$k] = $this->filterParameters($v); //recursive
            }
        }
        elseif (is_string($param)){
            $trans = array(
                '<' => '&lt;',
                '>' => '&gt;'
            );
            $param = strtr($param,$trans);
            $param = mysql_real_escape_string($param);
        }
        return $param;
    }
    
    // 如果有多个数据库，需要如下函数
    public function setFailedLogDB() {
        $this->db->SetDatabase("dbWozClanFailedLog");
    }

    public function setUserDB() {
        $this->db->SetDatabase("dbWozClan");
    }
}
?>
