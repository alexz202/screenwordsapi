<?php

/**
 * 设置职务
 * 通过GET传入参数验证
 *
 * @author jensenzhang
 */
class addscreenword extends AbstractAction
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
            $strword = $this->m_params['word'];
            $list=array();
            //TODO 判断敏感词
//            include_once '/var/www/html/xhprof_lib/utils/xhprof_lib.php';
//            include_once '/var/www/html/xhprof_lib/utils/xhprof_runs.php';
////			 start profiling
//            xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY);
            $triedao = $this->GetTrieMapDao();
           $res= $triedao->addWord($strword);
            // stop profiler
//            $xhprof_data = xhprof_disable();
//            $profiler_namespace = "TrieTreeadd";
//            $xhprof_runs = new XHProfRuns_Default();
//            $run_id = $xhprof_runs->save_run($xhprof_data, $profiler_namespace);
            if($res)
            $this->assembleResult(SUCCEED, '添加成功', $list);
            else
                $this->assembleResult(ERROR_ADDWORD, '添加失败', $list);
        }
        return $this->m_result;
    }
}

?>
