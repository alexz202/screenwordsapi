<?php

/* -*- C++ -*- */
//=============================================================================
/**
 *  @file       comm_app.h
 *  @brief      app框架
 *  @version    1.0.0
 *  @author     vencentli
 *  @date       2007/03/14
 */
//=============================================================================

#ifndef ___COMM_APPLICATION_H
#define ___COMM_APPLICATION_H

#include <string>
#include "oss_config.h"
#include "log.h"
#include "type_transform.h"
#include "handle.h"
#include "non_copyable.h"
#include "app_exception.h"


/*! \class CommApp
 *  \brief CommAPP框架,实现配置和日志
 */

//define(OSS_LOG, "\$this->LogerPtr->writeLog");

abstract class CommApp
{
	const DEFAULT_USER_VISIBLE_MSG = "对不起，系统繁忙，请稍后再试！";
	const NODENAME_FRAMEWORK_DEFAULT = "FRAMEWORK_DEFAULT";
	const PARAMNAME_LOG_FILE_PATH = "log_file_path";
	const PARAMNAME_LOG_FILE_NAME = "log_file_name";
	const PARAMNAME_LOG_TYPE = "log_type";
	const PARAMNAME_ROLL_LOG_SIZE = "roll_log_size";
	const PARAMNAME_ROLL_LOG_NUM = "roll_log_num";
	const PARAMNAME_RUNTIME_DEBUG = "runtime_debug";
	const PARAMNAME_LOGIN_AID = "login_aid";
	protected $CgiOutput;

	/*!
	*brief 唯一的公共接口，程序入口
	*/
    public function Run()
    {
	    try
	    {
	        $this->InitLog() ;
	        $this->Start();
	    }
	    catch(OssException $e)
	    {
	        $this->HandleOssException($e);
	    }
	    catch(AppException $e)
	    {
	        $this->HandleAppException($e);
	    }
	    catch(Exception $e)
	    {
	        $this->HandleStdException($e);
	    }
	    //catch (...)
	    //{
	    //    $this->HandleUnknownException();
	    //}

	    return 0;

	}

	/*! \brief 构造函数
	*/
	function __construct()
	{
		$this->CgiOutput = new CGIOutput();
		//$this->CgiOutput->SetContentType();
	}

	/*! \brief 析构函数
	*/
	function __destruct(){}

	/*! \brief 应用程序入口，子类必须重写此函数，并在此函数中实现具体的应用逻辑
	*/
    abstract protected function Start();


	/*! \brief 返还配置数组，子类必须重写此函数以指定配置数组
	* \return 配置数组
	*/
    abstract public function GetConfig();

	/*! \brief 返回框架使用的配置节点，子类可重写此函数以指定配置节点，否则使用默认节点
	* \return 配置节点
	*/
    protected function GetConfigNode()
    {
        return self::NODENAME_FRAMEWORK_DEFAULT ;
    }

	/*! \brief 获取配置项的值
	*/
    protected function GetParamValue($nodename,$param)
    {
    	$config = $this->GetConfig();
        if(!$config)
        {
			throw new OssException(__FILE__, __LINE__,"config doesn't exist");
		}
        if($config[$nodename] && array_key_exists($param,$config[$nodename]))
        {
			return $config[$nodename][$param];
		}
        if($config[$this->GetConfigNode()] && array_key_exists($param,$config[$this->GetConfigNode()]))
        {
			return $config[$this->GetConfigNode()][$param];
		}
        return $config[self::NODENAME_FRAMEWORK_DEFAULT][$param];
    }

	/*! \brief 返回日志文件路径，子类可重写此函数，否则使用默认配置
	* \return 日志文件路径
	*/
    protected function GetLogPath()
    {
		return $this->GetParamValue($this->GetConfigNode(),self::PARAMNAME_LOG_FILE_PATH);
    }

	/*! \brief 返回日志文件名，子类可重写此函数，否则使用默认配置
	* \return 日志文件各1?
	*/
    protected function GetLogName()
    {
		return $this->GetParamValue($this->GetConfigNode(),self::PARAMNAME_LOG_FILE_NAME);
    }

	/*! \brief 返回日志类型，子类可重写此函数，否则使用默认配置
	* \return 日志类型 1:滚动日志 2:日期日志
	*/
    protected function GetLogType()
    {
		$logtype = $this->GetParamValue($this->GetConfigNode(),self::PARAMNAME_LOG_TYPE);
        if($logtype == "roll_file" )
			return 1;
        else if($logtype == "date_file")
			return 2;
		else if($logtype == "direct_echo")
			return 3;
        return 1;
    }

	/*! \brief 返回滚动日志文件大小，子类可重写此函数，否则使用默认配置
	* \return 滚动日志文件大小
	*/
    protected function GetRollLogSize()
    {
		return $this->GetParamValue($this->GetConfigNode(),self::PARAMNAME_ROLL_LOG_SIZE);
    }

	/*! \brief 返回滚动日志文件数量，子类可重写此函数，否则使用默认配置
	* \return 滚动日志文件数量
	*/
    protected function GetRollLogNum()
    {
		return $this->GetParamValue($this->GetConfigNode(),self::PARAMNAME_ROLL_LOG_NUM);
    }

	/*! \brief 返回appid，子类可重写此函数，否则使用默认配置
	* \return appid
	*/
    protected function GetLoginAID()
    {
		return $this->GetParamValue($this->GetConfigNode(),self::PARAMNAME_LOGIN_AID);
    }

	/*! \brief 返回debug日志标志，子类可重写此函数，否则使用默认配置
	* \return debug日志标志 true: 显示debug日志  false: 不显示debug日志
	*/
    protected function IsRuntimeDebug()
    {
		$runtime_debug = $this->GetParamValue($this->GetConfigNode(),self::PARAMNAME_RUNTIME_DEBUG);
        if($runtime_debug == true )
			return true ;
        else
			return false;
    }

	/*! \brief 根据框架配置初始化日志
	* \throw OssException
	*/
    protected function InitLog()
    {
		if($this->GetLogType() == DATE_FILE_LOGGER)
		{
			OSS_INIT_LOGGER(DATE_FILE_LOGGER,$this->GetLogPath(),$this->GetLogName());
		}
		else if($this->GetLogType() == DIRECT_ECHO)
		{
			OSS_INIT_LOGGER(DIRECT_ECHO,NULL,NULL);
		}
		else
		{
			OSS_INIT_LOGGER(ROLL_FILE_LOGGER,$this->GetLogPath(),$this->GetLogName(),$this->GetRollLogSize(),$this->GetRollLogNum());
		}

        if(!($this->IsRuntimeDebug()))
        {
			OSS_SET_NULL_LOGGER(LP_BASE|LP_TRACE|LP_DEBUG);
        }
    }

	/*! \brief 处理应用抛出的异常
	*   该类的默认行为是执行通用错误处理。子类可以重写此函数来改变缺省行为
	* \param[in] 应用异常对象
	*/
    protected function HandleAppException(AppException $e)
    {
		// 捕获异常
	    OSS_LOG($e->getFile(),$e->getLine(),LP_ERROR,$e);
	    ob_start();
	    $this->CgiOutput->MessageBox($e->getMessage());
		$this->CgiOutput->GoUrl();
		ob_flush();
	    return false;
    }


	/*! \brief 处理Oss异常
	*        该类的默认行为是执行通用错误处理。子类可以重写此函数来改变缺省行为
	* \param[in] e OssException对象
	*/
    protected function HandleOssException(OssException $e)
    {
    	// 捕获异常
	    OSS_LOG($e->getFile(),$e->getLine(),LP_ERROR,$e);
	    ob_start();
		$this->CgiOutput->MessageBox("对不起，系统繁忙，请稍后再试！");
		//$this->CgiOutput->MessageBox($e->getMessage());
		$this->CgiOutput->GoUrl();
		ob_flush();
	    return false;
    }


	/*! \brief 处理标准异常
	*        该类的默认行为是执行通用错误处理。子类可以重写此函数来改变缺省行为
	* \param[in] e PHP标准异常对象
	*/
    protected function HandleStdException(Exception $e)
    {
    	OSS_LOG($e->getFile(),$e->getLine(),LP_ERROR,$e);
    	ob_start();
	    $this->CgiOutput->MessageBox($e->getMessage());
		$this->CgiOutput->GoUrl();
		ob_flush();
	    return false;
    }

	/*! \brief 处理未知异常
	*        该类的默认行为是执行通用错误处理。子类可以重写此函数来改变缺省行为
	*/
    protected function HandleUnknownException()
    {
    	OSS_LOG(__FILE__,__LINE__,LP_ERROR,"Unknow Error!\n");
    	ob_start();
	    $this->CgiOutput->MessageBox(self::DEFAULT_USER_VISIBLE_MSG);
		$this->CgiOutput->GoUrl();
		ob_flush();
	    return false;
    }

}

?>
