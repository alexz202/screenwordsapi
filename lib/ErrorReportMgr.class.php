<?php

/**
 * Description of ErrorReportMgr
 * 记录出错信息到DB，等待扫描程序扫描
 *
 * @author Jensen Zhang
 */
class ErrorReportMgr {
    //put your code here
    
    /**
     * 
     * @param type $sActionName     出错action的名字，如：login
     * @param type $sActionStep     出错action步骤，如：tencent server invoke
     * @param type $arrParams         出错的步骤所涉及到的所有参数，一般把action的参数m_params传过来即可
     * @param type $iErrorType      
     *      错误类型   
     *      1: DB 更新失败  
     *      2: 外部接口调用失败
     *      3: 业务逻辑失败 如：用户跳关、刷分等
     *      4: .....                                      
     * 
     * @param type $iErrorLevel
     *      出错级别
     *      1: 普通错误
     *      2: 致命错误
     *      3: 。。。
     * 
     * @param type $iErrorCode      如果接口调用出错，返回接口错误码，详细定义需要视情况添加
     * @param type $sErrorOutput    接口调用返回详细信息
     */
    public static function ReportError( $sActionName, $sActionStep, $arrParams, 
            $iErrorType, $iErrorLevel, $iErrorCode=0, $sErrorOutput='' ){
        $dao = new ServerErrorDao(debug, 1);
        $params = array();
        $params['strActionName'] = $sActionName;
        $params['strActionStep'] = $sActionStep;
        $params['strParams'] = json_encode( $arrParams );
        $params['iErrorType'] = $iErrorType;
        $params['iErrorLevel'] = $iErrorLevel;
        $params['iErrorCode'] = $iErrorCode;
        $params['iErrorOutput'] = $sErrorOutput;
        
        $dao->ReportError($params);
    }
}

?>
