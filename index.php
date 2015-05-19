<?php
require_once 'entry.php';

/**
 * unique app entrance
 * url looks like : index.php?action=A1000&p=xxxxxxxx encoded string xxxxxxxxxx
 */
class Index extends AdminAppFrame {
    private $cfgMgr;
    
    public function __construct() {
        parent::__construct();
        
        $this->cfgMgr = new ConfigFileMgr();
    }
    function HandleAppException(AppException $e) {
        OSS_LOG(__FILE__, __LINE__, LP_ERROR, 'SYSTEM:' . $e->getMessage() . "\n");
        $this->output(ERROR_SYSTEM_ERROR,"APP_EXCEPTION");
        return -1;
    }

    function HandleOssException(OssException $e) {
        OSS_LOG(__FILE__, __LINE__, LP_ERROR, 'SYSTEM:' . $e->getMessage() . "\n");
        $this->output(ERROR_SYSTEM_ERROR,"OSS_EXCEPTION");
        return -1;
    }

    function HandleStdException(Exception $e) {
        OSS_LOG(__FILE__, __LINE__, LP_ERROR, 'SYSTEM:' . $e->getMessage() . "\n");
        $this->output(ERROR_SYSTEM_ERROR,"STD_EXCEPTION");
        return -1;
    }

    function HandleUnknownException() {
        OSS_LOG(__FILE__, __LINE__, LP_ERROR, 'SYSTEM:' . $e->getMessage() . "\n");
        $this->output(FATAL_ERROR_UNKNOWN_ERROR,"UNKNOWN_ERROR");
        return -1;
    }

    public function GetConfig()
    {
        return GetGlobalConfig();
    }
    
    function StartApp()
    {
        // 判断当前的服务状态
        $config = $this->GetConfig();
        if( $config['SYSTEM']['is_mantain'] == 1 ){
            $this->output( FATAL_ERROR_SYSTEM_MANTAIN, '服务器维护中...' );
            return;
        }
        
        // decode params
        $isEncrypt = $config['SYSTEM']['is_encrypt'];
        $this->cfgMgr->SetEncrypt($isEncrypt);
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
				$action = $_GET['action'];
                //$params = UTF8toGBK( $_GET );
                $params = $_GET;
                break;
            case 'POST':
                $action = $_POST['action'];
                //$params = UTF8toGBK( $_POST );
                $params = $_POST;
        }
        
        // 根据action来初始化相应的 module 来处理
        $actionInfo = $this->cfgMgr->GetActionInfo( $action );
        if( $actionInfo && class_exists( $actionInfo['name'] ) ){
            // 频率限制
            if(extension_loaded('oss_base')) {
                if( !$this->AccessLimit($params, $actionInfo['name']) ){
                    return;
                }
            }
            
            $actionRealName = $actionInfo['name'];
            $module = new $actionRealName( $params, $actionInfo, $this->cfgMgr );
            $res = $module->run();
            
            $this->output( $res['iRet'], $res['sMsg'], $res['list'], $res['sPrivateKey'] );
        }
        else{
            OSS_LOG(__FILE__, __LINE__, LP_ERROR, 'action by user '.$_GET['iUid'].' is not supported:' . $_GET['action'] . "\n");
            $this->output( FATAL_ERROR_INVALID_ACTION, 'action does not support' );
        }
    }
    
    
    public function AccessLimit( $params, $action ){
        // 访问频率控制
        if( !empty( $params['iUid'] ) ){
            $limitSource = $params['iUid'];  // 如果玩家登陆，获取uin
        }
        else{
            $limitSource = $this->ip_address_to_number( $_SERVER["REMOTE_ADDR"] );         // 如果玩家未登陆，获取IP
//            $limitSource = $this->ip_address_to_number( "192.168.1.125" );
        }
        $ret = $this->handleAccessLimit( $action, $limitSource );
        if( $ret == ERROR_USER_ACCESS_LIMIT_REACHED ){
            $this->output( ERROR_USER_ACCESS_LIMIT_REACHED, "很抱歉，您的操作太频繁，请休息10秒后再试哦！" );
            return false;
        }
        else if( $ret == ERROR_SERVER_ACCESS_LIMIT_REACHED ){
            $this->output(  ERROR_SERVER_ACCESS_LIMIT_REACHED, "很抱歉，目前使用系统的人数过多，您可以休息10秒后再来哦！");
            return false;
        }
        
        return true;
    }
    
    // 频率控制 限制QQ号 和总人数
	private function handleAccessLimit($action, $iUid) {
        $config = $this->GetConfig();
        
        // user acess limit
        $access_limit_file_user = $config['FRAMEWORK_DEFAULT']['log_file_path'];
        if( isset( $config['activity'][$action.'_MIN_INTERVAL_SEC'] ) ){
            $access_limit_file_user .= "/pvzUser".$action."AccessLimit.dat";
            $res = local_access_limit( $access_limit_file_user, $iUid, 
                    $config['ACCESS_LIMIT'][$action.'_MIN_INTERVAL_SEC'],
                    $config['ACCESS_LIMIT'][$action.'_MAX_ACCESS'] );
        }
        else{
            $access_limit_file_user .= "/pvzUserDefaultAccessLimit.dat";
            $res = local_access_limit( $access_limit_file_user, $iUid, 
                    $config['ACCESS_LIMIT']['DEFAULT_MIN_INTERVAL_SEC'], 
                    $config['ACCESS_LIMIT']['DEFAULT_MAX_ACCESS'] );
        }
        
		if( $res!=0 )
		{
            return ERROR_USER_ACCESS_LIMIT_REACHED;
		}
        
        //server access limit
        $access_limit_file_server = $config['FRAMEWORK_DEFAULT']['log_file_path'];
		$access_limit_file_server .= "/pvzServerAccessLimit.dat";
		$res = local_access_limit( $access_limit_file_server, 0, 
                $config['ACCESS_LIMIT']['SERVER_MIN_INTERVAL_SEC'], 
                $config['ACCESS_LIMIT']['SERVER_MAX_ACCESS'] );
        
		if( $res!=0 )
		{
            return ERROR_SERVER_ACCESS_LIMIT_REACHED;
		}
        
        return 0;
	}
    
    
    protected function output( $iRetcode, $sErrorMsg, $vmResult=array(), $privateKey = '')
    {
        $res = array( 
            'iRet' => $iRetcode,
            'sRetMsg' => $sErrorMsg,
            'list' => $vmResult
        );
        
        $jsonRes = json_encode($res);
        
        if( $this->cfgMgr->GetEncrypt() ){
            //        $res = GBKtoUTF8( $res );
//            $params = array();
//            $params['json'] = $jsonRes;
            
            if( empty($privateKey) ){
                echo SecurityMgr::EncodeLoginResponseParams($jsonRes);
            }
            else{
                echo SecurityMgr::EncodeResponseParams($jsonRes, $privateKey);
            }
        }
        else{
            echo $jsonRes;
        }

//        echo $jsonRes;
    }
    
    private function ip_address_to_number($IPaddress) {
        if (!$IPaddress) {
            return false;
        } else {
            $ips = split('\.', $IPaddress);
            return($ips[3] | $ips[2] << 8 | $ips[1] << 16 | $ips[0] << 24);
        }
    }
}

RUN_APP('Index');

?>
