<?php

/**
 * 设置职务
 * 通过GET传入参数验证
 *
 * @author jensenzhang
 */
class checkscreenword extends AbstractAction
{

    public function __construct($params, $actionInfo, $cfgMgr)
    {
        parent::__construct($params, $actionInfo, $cfgMgr);
    }

    //put your code here
    public function run()
    {
        // test error log
        //        ErrorReportMgr::ReportError(__CLASS__, 'testing', $this->m_params, 0, 1);
        //        die;

        // 如果加密开启，需要解密
        if ($this->m_cfgMgr->GetEncrypt()) {
            // decode params
            $strParams = $this->m_params['sig'];
            $res = SecurityMgr::DecodeLoginParams($strParams);

            if ($res === false) {
                $this->assembleResult(FATAL_ERROR_FAKE_PARAMS, '用户伪造参数');
                return $this->m_result;
            }
            // 获取参数
            $this->m_params = array_merge($this->m_params, $res);
        }

        // 参数验证
        $validate_config = $this->m_actionInfo['params'];

        if (!$this->validateParams($validate_config, $detailMsg)) {
            $this->assembleResult(ERROR_INVALID_PARAM, '参数错误:' . $detailMsg);
        } else {
            // get iUid by openId
            $strOpenId = $this->m_params['sUsername'];
            $list = array();
            //TODO 判断合法行
//            include_once '/var/www/html/xhprof_lib/utils/xhprof_lib.php';
//            include_once '/var/www/html/xhprof_lib/utils/xhprof_runs.php';
//			 //start profiling
//            xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY);
            $triedao = $this->GetTrieMapDao();
            $triestr = $triedao->getTrieInfo();
            $trieTreeclass = new TrieTree();
            $trieTreeclass->import($triestr);
            $checktrie = $trieTreeclass->contain($strOpenId);
            //        // stop profiler
//            $xhprof_data = xhprof_disable();
//            $profiler_namespace = "TrieTreeserialize";
//            $xhprof_runs = new XHProfRuns_Default();
//            $run_id = $xhprof_runs->save_run($xhprof_data, $profiler_namespace);
            if ($checktrie === true) {
                $this->assembleResult(EXIST_SCREENWORDS, '存在屏蔽字', $list);
                return $this->m_result;
            }
            $this->assembleResult(SUCCEED, '合法', $list);
        }

        return $this->m_result;
    }

    /**
     * insert new user into user openid map table
     * insert new user into user table
     *
     * @param type $strOpenId
     */
    private function initalizeUserInfo($strOpenId, $strOpenKey, $strPrivateKey, &$iUid, $iGameId, $qqUserInfo = null, $oemtarget)
    {
        $openIdMapDao = $this->GetUserOpenIdMapDao();
        $iUid = $openIdMapDao->AddNewUser($strOpenId, $oemtarget, $strOpenKey);
        if ($iUid !== 0) {
            return true;
            //$userDao = $this->GetUserDao();
            //return $userDao->AddNewUserInfo($iUid, $strOpenKey, $strPrivateKey,$iGameId,$qqUserInfo);
        } else {
            return false;
        }
    }

}

?>
