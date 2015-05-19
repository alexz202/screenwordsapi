<?php

/**
 * This class contains two main functions
 * 1. encode and decode of all the parameters
 * 2. user access limit control @TODO
 *
 * @author Jensenzhang
 */
class SecurityMgr {
    /**
     * decode params
     * first should check params valid or not
     *  accoring to checksum
     * 
     * @param type $strParams
     * @return boolean
     */
    public static function DecodeLoginParams( $strParams ){
        $strTmp = base64_decode( $strParams );
        list( $sParams, $checksum ) = explode(';', $strTmp);
        
        if(md5("$sParams;".SEC_KEY) == $checksum) {
            $arr = explode('||', $sParams);
            $result = array();
            foreach( $arr as $strItem ){
                list( $key, $value ) = explode('=', $strItem);
                $result[$key] = $value;
            }
            
            return $result;
        }
        else{
            return false;
        }
    }
    
    /**
     * encrypt params
     * 
     * @param type $params
     * @return type
     */
    public static function EncodeLoginParams( $params ){
        // sort params by key
        $arrSortedKeys = array_keys($params);
        sort( $arrSortedKeys );
        $strParams = "";
        foreach ( $arrSortedKeys as $key ){
            if( $strParams == "" ){
                $strParams = $key.'='.$params[$key];
            }
            else{
                $strParams = $strParams.'||'.$key.'='.$params[$key];
            }
        }
        
        $enc = md5( $strParams.';'.SEC_KEY );
        $result = base64_encode( $strParams.';'.$enc );
        
        return $result;
    }
    
    /**
     * 组装Login返回串
     * 
     * @param type $strParams
     * @return type
     */
    public static function EncodeLoginResponseParams( $strParams ){
        $enc = md5( $strParams.';'.SEC_KEY );
        $result = base64_encode( $strParams.';'.$enc );
        
        return $result;
    }
    
    /**
     * decode params
     * first should check params valid or not
     *  accoring to checksum
     * 
     * @param type $strParams
     * @return boolean
     */
    public static function DecodeParams( $strParams, $strUserKey ){
        $strTmp = base64_decode( $strParams );
        list( $sParams, $checksum ) = explode(';', $strTmp);
        
        if(md5("$sParams;".$strUserKey) == $checksum) {
            $arr = explode('||', $sParams);
            $result = array();
            foreach( $arr as $strItem ){
                list( $key, $value ) = explode('=', $strItem);
                $result[$key] = $value;
            }
            
            return $result;
        }
        else{
            return false;
        }
    }
    
    /**
     * encrypt params
     * 
     * @param type $params
     * @return type
     */
    public static function EncodeParams( $params, $strUserKey ){
        // sort params by key
        $arrSortedKeys = array_keys($params);
        sort( $arrSortedKeys );
        
        $strParams = "";
        foreach ( $arrSortedKeys as $key ){
            if( $strParams == "" ){
                $strParams = $key.'='.$params[$key];
            }
            else{
                $strParams = $strParams.'||'.$key.'='.$params[$key];
            }
        }
        
        $enc = md5( $strParams.';'.$strUserKey );
        $result = base64_encode( $strParams.';'.$enc );
        
        return $result;
    }
    
    /**
     * 组装其他action返回串
     * @param type $strParams
     * @param type $strUserKey
     * @return type
     */
    public static function EncodeResponseParams( $strParams, $strUserKey ){
        $enc = md5( $strParams.';'.$strUserKey );
        $result = base64_encode( $strParams.';'.$enc );
        
        return $result;
    }
    
    /**
     * 生成用户的 Private Key
     * 
     * sha1( pid||openid||openkey||time+24hour )
     * 
     * @param type $strOpenId
     * @param type $strOpenKey
     * @return string
     */
    public static function GenerateUserPrivateKey( $strOpenId, $strOpenKey ){
        $pid = getmypid();
        $time = time()+3600*24;
        
        $strContent = $pid."||".$strOpenId."||".$strOpenKey."||".$time;
        return sha1( $strContent );
    }
    
    /**
     * 从加密串获取用户Id
     * 不做参数验证
     * 
     * @param type $strParams
     * @return int
     */
    public static function GetUserIdFromEncrypt( $strParams ){
        $strTmp = base64_decode( $strParams );
        list( $sParams, $checksum ) = explode(';', $strTmp);
        
        $arr = explode('||', $sParams);
        foreach( $arr as $strItem ){
            list( $key, $value ) = explode('=', $strItem);
            if( $key == 'iUid' ){
                return $value;
            }
        }
            
        return 0;
    }
}

?>
