<?php
//**********************************************************
// File name: UserAppFrame.class.php
// Class name: UserAppFrame
// Create date: 2009/04/17
// Update date: 2009/04/17
// Author: garyzou
// Description: 客户端cgi框架
//**********************************************************

abstract class UserAppFrame extends CommApp
{
    /*! \brief 配置文件中指定是否进行登录检查的参数项 */
	const PARAMNAME_NEED_LOGIN = "need_login";
	const PARAMNAME_LOGIN_AID = "login_aid";
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
	    catch(UserNotLoginException $e)
	    {
	        $this->HandleUserNotLoginException($e);
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
			throw new UserNotLoginException("对不起，您没有登录，请登录后重试！\n");
		}
		return $this->m_psession;
    }
	
    /*! \brief 初始化Session
    */
    private function InitSession()
    {
        if($this->IsCheckLogin())
        {
			$this->m_psession = new UserSessionPtlogin2V4($this->GetLoginAID());
			if(!($this->m_psession->IsLogin()))
	        {
	        	throw new UserNotLoginException("对不起，您没有登录，请登录后重试！\n");
			}
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
	
    /*! \brief 处理用户没有登录的异常。
    *        该类的默认行为是记录日志，并显示登录错误信息。子类可以重写此函数来改变缺省行为
    * \param[in] e 相关异常对象
    */
    protected function HandleUserNotLoginException(UserNotLoginException $e)
    {
        OSS_LOG(__FILE__,__LINE__,LP_ERROR,$e);
        ob_start();
        //$this->CgiOutput->MessageBox($e->GetUserVisibleMsg());
		$this->CgiOutput->MessageBox("对不起，您没有登陆，请登陆后再试！");
		$this->CgiOutput->GoUrl();
		ob_flush();
        return false;
    }

}

?>
