<?php

/**
 * 日志上报类
 *
 * @author Jensen Zhang
 */
class LogMgr {
	private static function getLogConfig(){
		$config = GetGlobalConfig();
		return $config['LOGSERVER'];
	}
	
	private static function isLogEnable(){
		$config = self::getLogConfig();
		if( $config['is_log_enabled'] == 1 ){
			return true;
		}
		else{
			return false;
		}
	}
	
	/**
	 * 上报日志
	 * 
	 * @param type $content
	 * @return boolean
	 */
	public static function UploadLog( $content ){
		
		if( self::isLogEnable() ){
            try{
                $config = self::getLogConfig();
                $res = baseTCPLogMgr::sendTcpPackage( $config['host'], $config['port'], $content, $outBuf, $config['timeout'], $errMsg);

                if( $res <0 ){
                    OSS_LOG(__FILE__, __LINE__, LP_ERROR, "upload log failed: $errMsg, response is : $outBuf\n");
                    return false;
                }
                else{
                    return true;
                }
            }
            catch (Exception $e){
                OSS_LOG(__FILE__, __LINE__, LP_ERROR, $e->getMessage() . "\n");
                return true;
            }
		}
		else{
			return true;
		}
	}
}

?>
