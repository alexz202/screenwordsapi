<?php
//**********************************************************
// File name: OssAdminSession.class.php
// Class name: OssAdminSession
// Create date: 2009/07/22
// Update date: 2009/07/22
// Author: garyzou
// Description: 管理员登录类
//**********************************************************

/*! \brief OSS系统管理员登录类
 */
class OssAdminSession
{

	/*! \brief 本次操作的权限码 */
	private $m_sAdminCode;

	/*! \brief 管理员名 */
	private $m_sAdminName;

	/*! \brief 是否登陆 */
	private $m_bIsUserLogin;

	/*! \brief 该管理员名拥有的权限码 */
	private $m_vAdminCodeArray;

	/*!
	* \brief 构造函数
	* \param[in] sAdminCode: OSS管理操作的权限码
	* \param[in] ptrCgiInput: CGIInput类的实例
	* \throw OssBase::OssException
	*/
	function OssAdminSession($sAdminCode)
	{

		$this->m_sAdminCode = $sAdminCode;
		$this->m_bIsUserLogin = false;

		//判断是否是整数并在指定的某个范围
		if(!is_numeric($this->m_sAdminCode) || intval($this->m_sAdminCode)<0 || intval($this->m_sAdminCode)>999999)
		{
			return false;
		}

		$this->m_sAdminName = $_COOKIE["ossadmin"];
		$sArrOssOp = $_COOKIE["sArrOssOp"];
		//mark by garyzou at 2010-04-22
		//if(!empty($this->m_sAdminName) && !empty($sArrOssOp))
		if(!empty($this->m_sAdminName))
        {
			$this->m_bIsUserLogin = true;
			$this->m_vAdminCodeArray = explode("|", $_COOKIE["sArrOssOp"]);
        }
        return false;
	}

	/*!
	* \brief 析构函数
	*/
	//function ~OssAdminSession()

    /*!
    * \brief 判断管理员是否登录
    * \return true:已登录 false:未登录
    */
    public function IsLogin()
    {
    	return $this->m_bIsUserLogin;
	}

    /*!
    * \brief 判断管理员是否有足够权限
    * \return true:有足够权限 false:没有足够权限
    * \throw OssBase::OssException
    */
    function IsValidAdmin()
    {
		if(!$this->IsLogin())
		{
            return false;
        }

        if($this->CheckAdminCode($this->m_sAdminCode) ||
			$this->CheckAdminCode(substr($this->m_sAdminCode,0,4)."00") ||
			$this->CheckAdminCode(substr($this->m_sAdminCode,0,2)."0000") ||
			$this->CheckAdminCode("000000"))
		{
                return true;
        }

        return false;
	}

    /*!
    * \brief 取当前管理员名
    * \return 管理员名
    * \throw OssBase::OssException
    */
    function GetAdminName()
    {
		if ( !$this->IsLogin() )
		{
			return false;
		}

		return $this->m_sAdminName;
	}

    /*!
    * \brief 记录管理员LOG
    * \return 0:成功 -1:失败
    */
    function Log($sLogMsg)
    {
    	/*if ( !IsLogin() ) {
                return -1;
        }

        try {
                UnixConfig cOssConfig(CONFIG_FILE_PATH);

                SqlTpl cSqlTpl("select iLogFlag from tbOssOpCfg where sOperate = '[$]'");
                cSqlTpl << _sAdminCode;

                DBConnectionPtr  pDBConn = DBFactory::CreateMySqlDBConnection(cOssConfig["host"]("Oss"),  "root", "root1234", "dbOssAdminDB");
                DBConnection::ResultSet vResultSet;

                pDBConn->Connect();
                if ( pDBConn->ExecQuery(cSqlTpl.GetSql(), vResultSet) == 0 ||
                        TypeTransform::StringToInt(vResultSet[0][0]) == 0 )
                {
                        return -1;
                }

                cSqlTpl = SqlTpl("insert into tbAdminOpLog(sId,sOperate,sIP,sMemo,dtDate) values ('[$]','[$]','[$]','[$]', now())");
                cSqlTpl << _sAdminName << _sAdminCode << CGIEnv::Instance()->GetRemoteAddr() << sLogMsg;

                pDBConn->ExecUpdate(cSqlTpl.GetSql());
        }
        catch ( OssException & e ) {
                return -1;
        }

        return 0;*/
	}


    /*!
    * \brief 取当前管理员角色
    * \return 管理员角色
    * \throw OssBase::OssException
    */
    function GetAdminRole()
    {
		if (!$this->IsLogin())
		{
			return false;

		}
		return $_COOKIE["ossadminType"];

	}

    /*!
    * \brief 判断操作码是否在集合中
    * \param[in] sAdminCode: OSS管理操作的权限码
    * \return true or false
    */
    private function CheckAdminCode($sAdminCode)
    {
        foreach($this->m_vAdminCodeArray as $value)
        {
        	if($value == $sAdminCode)
        		return true;
		}
		return false;
    }

}
?>
