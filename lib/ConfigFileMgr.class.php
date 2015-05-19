<?php
/**
 * ACL 统一处理类
 *
 * @author jensenzhang
 */
class ConfigFileMgr {
    private $actionConfig;
    private $isEncrypt = 0;
    
    public function __construct() {
        $this->actionConfig = require ALL_ACTION_INFO;
    }
    
    public function GetActionInfo( $action ){
        if( $this->actionExist( $action ) ){
            return $this->actionConfig[$action];
        }
        else{
            return false;
        }
    }
    
    public function SetEncrypt($isEncrypt){
        $this->isEncrypt = $isEncrypt;
    }
    
    public function GetEncrypt(){
        return $this->isEncrypt;
    }
    
    public function GetActionIdByName( $strActionName ){
        foreach( $this->actionConfig as $key=>$item ){
            if( $item['name'] == $strActionName ){
                return substr( $key, 1 );
            }
        }
    }
    
    /**
     * 某个动作是否存在
     * @param type $roleId 
     */
    private function actionExist( $key ){
        $actionNames = array_keys( $this->actionConfig );
        if( in_array( $key, $actionNames )){
            return true;
        }
        else{
            OSS_LOG(__FILE__, __LINE__, LP_ERROR, "action: $key does not exist\n");
        }
    }
}

?>
