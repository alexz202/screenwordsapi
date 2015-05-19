<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AbstractAction
 *
 * @author jensenzhang
 */
abstract class AbstractAction {


	protected $m_params;
	protected $m_actionInfo;
	protected $m_result;
	protected $m_cfgMgr;
	protected $m_privateKey = '';

	public function __construct($params, $actionInfo, $cfgMgr) {
		$this->m_params = $params;
		$this->m_actionInfo = $actionInfo;
		$this->m_cfgMgr = $cfgMgr;
	}

	//逻辑执行函数
	abstract public function run();

	protected function assembleResult($iRet, $sMsg, $list = array()) {
		$this->m_result['iRet'] = $iRet;
		$this->m_result['sMsg'] = $sMsg;
		$this->m_result['list'] = $list;
		$this->m_result['sPrivateKey'] = $this->m_privateKey;
	}

	/**
	 * 参数验证
	 */
	protected function validateParams($validate_config, &$detailMsg='') {
		$validator = new Validator($validate_config);
		try {
			$validator->Validate($this->m_params);
			return true;
		} catch (ValidateException $e) {
			$detailMsg=$e->getMessage();
			//TODO
			// for debug, tmp change here
			OSS_LOG(__FILE__, __LINE__, LP_ERROR, $e->getMessage() . "\n");
			return false;
		}
	}

	protected function GetOperationIdByName($strName) {
		return intval($this->m_cfgMgr->GetActionIdByName($strName));
	}

	/**
	 * 判断用户是否登陆
	 *
	 * @param type $iUid
	 * @return type
	 */
	protected function IsLogin() {
		$iUid = $this->m_params['iUid'];
		$sGameName = $this->m_params['sGameName'];
		$userDao = $this->GetUserDao();
		return true;
		return $userDao->isLogin($iUid,$sGameName);
	}

	/**
	 * 获取用户操作DAO
	 * @return type
	 */
	protected function GetUserOpenIdMapDao() {
		return $this->GetDAO('UserOpenIdMapDao', 1);
	}

	protected function GetTrieMapDao() {
		return $this->GetDAO('TrieDao', 1);
	}


	/**
	 * 实例化指定DAO
	 * 单例返回
	 *
	 * @param type $name
	 * @param type $tableCount
	 * @return \name|null
	 */
	protected function GetDAO($class, $tableCount) {
		global $$class;
		if (isset($$class))
		return $$class;
		else {
			if (class_exists($class)) {
				$dao = new $class(debug, $tableCount);

				$$class = new CacheProxy($dao);
				return $$class;
			} else {
				return null;
			}
		}
	}

	
	public function GetBuyCoinLogic() {
		return  $this->GetLogic("BuyCoinLogic");
	}

	/**
	 * 实例化指定Logic
	 * 单例返回
	 *
	 * @param type $name
	 * @param type $tableCount
	 * @return \name|null
	 */
	protected function GetLogic($class) {
		global $$class;
		if(isset($$class))
		return $$class;
		else {
			if(class_exists($class)) {
				$$class = new $class();
				return $$class;
			} else {
				return null;
			}
		}
	}

	protected function gernerateSerailNumber($iuid,$data){
		return getmypid().microtime().$iuid.$data;
	}
	/**
	 * 参数解密
	 * @return type
	 */
	protected function UnEncryptParameters(){
		if( $this->m_cfgMgr->GetEncrypt() ){
			// decode params
			$strParams = $this->m_params['sig'];

			// get user privatekey
			$iUid = SecurityMgr::GetUserIdFromEncrypt($strParams);
			$userDao = $this->GetUserDao();
			$userInfo = $userDao->GetUserInfo($iUid);

			// 保存当前用户的 private key
			$this->m_privateKey = $userInfo['strPrimKey'];
			if( $iUid!=0 && !empty( $this->m_privateKey )){
				$res = SecurityMgr::DecodeParams($strParams, $this->m_privateKey);

				if( $res === false ){
					$this->assembleResult(FATAL_ERROR_FAKE_PARAMS, '用户伪造参数');
					return $this->m_result;
				}
				// 整合参数参数
				$this->m_params = array_merge($this->m_params, $res );
			}
			else{
				$this->assembleResult(ERROR_USER_NOT_LOGIN, '用户未登陆');
				return $this->m_result;
			}
		}
	}
	protected function combineLogstring($arr,$tag='|'){
		return join($tag,$arr);
	}
}

?>
