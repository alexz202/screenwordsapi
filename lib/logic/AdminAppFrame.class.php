<?php
//**********************************************************
// File name: AdminAppFrame.class.php
// Class name: OssException
// Create date: 2009/07/27
// Update date: 2011/03/15
// Author: garyzou
// Description: 管理端应用框架
//**********************************************************

abstract class AdminAppFrame extends CommApp
{
    /*! \brief 配置文件中指定是否进行登录检查的参数项 */
	const PARAMNAME_NEED_LOGIN = "need_login";
	const PARAMNAME_LOGIN_AID = "login_aid";

	/*! \brief 配置文件中指定权限验证类型的参数项 */
    const PARAMNAME_AUTH_TYPE = "auth_type";

    /*! \brief 配置文件中指定权限码的参数项 */
    const PARAMNAME_AUTH_CODE = "auth_code";

    protected $m_psession;

    /*! \brief cgi应用程序入口，子类必须重写此函数，并在此函数中实现具体的应用逻辑
    */
    abstract function StartApp();


    /*! \brief 客户端CGI框架入口
    */
    protected function Start()
    {
	    try
	    {
	        $this->InitSession();
	        $this->StartApp();
	    }
	    catch(AdminNotLoginException $e)
	    {
	        $this->HandleAdminNotLoginException($e);
	    }
		catch(AdminNoValidException $e)
	    {
	        $this->HandleAdminNoValidException($e);
	    }
    }


    /*! \brief 返回用户session对象的指针
    * \return 用户session对象的指针
    * \throw OssException
    */
    public function GetSessionPtr()
    {
    	if(!($this->IsCheckLogin()))
        {
			throw new AdminNotLoginException("对不起，您没有登录，请登录后重试！\n");
		}
		return $this->m_psession;
    }

    /*! \brief 初始化Session
    */
    private function InitSession()
    {
		$authcode = $this->GetAuthCode();
        $authtype = $this->GetAuthType();
        //echo "authcode:".$authcode."<br/>";
        //echo "authtype:".$authtype."<br/>";

        if ($authtype == 1)
        {
        	return false;
            //m_psession = new CmAdminSession(authcode);
        }
        else if ($authtype == 2)
        {
		    $this->m_psession = new OssAdminSession($authcode);
		}
        else
        {
			return false;
		}


        //if ($this->m_psession == NULL)
        //{
        //	throw new OssException("admin session doesn't exist") ;
		//}

        if (!$this->m_psession->IsLogin())
        {
           	throw new AdminNotLoginException("对不起，您没有登录，请登录后重试！\n");
		}

        if (!$this->m_psession->IsValidAdmin())
        {
			throw new AdminNoValidException("对不起，您没有操作权限！") ;
		}


    }

    /*! \brief 返回登录检查标志。子类可以重写此函数来改变缺省行为。
    * \return 登录检查标志 true:登录检查 false:不检查
    */
    protected function IsCheckLogin()
    {
    	//$config = $this->GetConfig();
		//$login = $config[$this->GetConfigNode()][self::PARAMNAME_NEED_LOGIN];
        $login = $this->GetParamValue($this->GetConfigNode(),self::PARAMNAME_NEED_LOGIN);
        if($login)
			return true;
		else
        	return false;
    }

	/*! \brief 返回权限码。子类可以重写此函数来改变缺省行为。
	* \return 权限码
	*/
	protected function GetAuthCode()
    {
        return $this->GetParamValue($this->GetConfigNode(),self::PARAMNAME_AUTH_CODE);
    }

    /*
	! \brief 返回权限验证类型，子类可重写此函数，否则使用默认配置
    * \return 权限验证类型 1:敏感业务系统权限 2:oss系统权限
    */
    protected function GetAuthType()
    {
        $authtype  = $this->GetParamValue($this->GetConfigNode(),self::PARAMNAME_AUTH_TYPE);
        if ($authtype == "cm")
		{
			return 1;
		}
        else if ($authtype == "oss")
        {
			return 2;
		}
        return 0;
    }

    /*! \brief 处理用户没有登录的异常。
	*        该类的默认行为是记录日志，并显示登录错误信息。子类可以重写此函数来改变缺省行为。
	* \param[in] e 相关异常对象
	*/
    protected function HandleAdminNotLoginException(AdminNotLoginException $e)
    {

        OSS_LOG(__FILE__,__LINE__,LP_ERROR,$e);
        ob_start();
        //$this->CgiOutput->SetContentType("\"text/html; charset=gb2312\"");
        $this->CgiOutput->MessageBox($e->GetUserVisibleMsg());
		$this->CgiOutput->GoUrl();
		ob_flush();
        return false;
    }
	
    /*! \brief 处理用户没有权限的异常。
	*        该类的默认行为是记录日志，并显示登录错误信息。子类可以重写此函数来改变缺省行为。
	* \param[in] e 相关异常对象
	*/
    protected function HandleAdminNoValidException(AdminNoValidException $e)
    {

        OSS_LOG(__FILE__,__LINE__,LP_ERROR,$e);
        ob_start();
        //$this->CgiOutput->SetContentType("\"text/html; charset=gb2312\"");
        $this->CgiOutput->MessageBox($e->GetUserVisibleMsg());
		$this->CgiOutput->GoUrl();
		ob_flush();
        return false;
    }
}

?>
