<?php
//**********************************************************
// File name: OSSBaseLogic.inc.php
// Create date: 2009/04/17
// Update date: 2011/03/25
// Author: garyzou
// Description: 只要包含这个文件，在使用到库中的类的时候，就会自动包含对应的类文件
// Example:
// require '/usr/local/demoweb/htdocs/phpdemo/PHPOSSBase/OSSBaseLogic.inc.php';
//**********************************************************
//包含 memcached 类文件
//require './base/memcached-client.php';

//这个需要根据库存放路径做修改
//28测试环境
//$libdir = '/web/garyzou/PHPLib';
//正式环境
$libdir = './lib';
$moduledir = './module';

function __autoload($classname)
{
	global $libdir;
	$basefile = $libdir.'/base/'.$classname.'.class.php';
	$logicfile = $libdir.'/logic/'.$classname.'.class.php';
    $modulefile = $moduledir.'/'.$classname.'.class.php';
	if(file_exists($basefile))
	{
		require $basefile;
//		echo 'require ['.$basefile.'] success!<br/>';
	}
	else if(file_exists($logicfile))
	{
		require $logicfile;
//		echo 'require ['.$logicfile.'] success!<br/>';
	}
    else if(file_exists($modulefile))
	{
		require $modulefile;
//		echo 'require ['.$modulefile.'] success!<br/>';
	}
}


//传入一个自定义的应用类名称
function RUN_APP($CGI_APP_TYPE)
{
	$evalstr = "\$app = new $CGI_APP_TYPE();";
	//echo $evalstr."<br/>";
	eval($evalstr);
	$app->Run();
}

define('ICONV_FROM', 'UTF-8');
define('ICONV_TO', 'gb2312');

function oss_iconv($in_charset, $out_charset, $str)
{
	return iconv($in_charset,$out_charset,$str);
}

//日志类型

define('ROLL_FILE_LOGGER', 1);
define('DATE_FILE_LOGGER', 2);
define('DIRECT_ECHO', 3);
//! \brief 滚动日志的默认文件最大值为10MB 1024*1024*10
define('DEFAULT_LOG_MAX_SIZE', 1024*1024*10);
  //! \brief 滚动日志默认保存最近3个
define('DEFALUT_LOG_SAVE_NUM', 5);

/*! \brief 设置日志处理器
 */
function OSS_INIT_LOGGER($logtype=ROLL_FILE_LOGGER,$dir=NULL,$filename=NULL,$maxsize=DEFAULT_LOG_MAX_SIZE,$filenum=DEFALUT_LOG_SAVE_NUM)
{
	Logger::Instance()->initLogger($logtype,$dir,$filename,$maxsize,$filenum,$maxsize,$filenum);
}

/*! \brief 设置相应级别的日志处理器
 *  \param[in] loglevel 日志级别
 *  \note OSS_SET_NULL_LOGGER(LP_BASE|LP_TRACE|LP_DEBUG);
 */
function OSS_SET_NULL_LOGGER($loglevel)
{
	Logger::Instance()->setNullLoger($loglevel);
}

//日志级别
/// \brief 基础级别。 此级别一般不用。
define('LP_BASE', 1);
/// \brief 跟踪级别。用在函数的进入/退出时做记录用，一般只会用在前期的debug版。\n
/// 生成release版时一般要将此级别的信息忽略 (使用NullLogger处理)
define('LP_TRACE', LP_BASE << 1);
/// \brief 调试级别。程序输出debug信息时用。\n
/// 生成release版时一般要将此级别的信息忽略 (使用NullLogger处理)
define('LP_DEBUG', LP_BASE << 2);
/// \brief 普通级别。记录一般性的非错信息
define('LP_INFO', LP_BASE << 3);
/// \brief 用户级别1。级别说明的最终解释权归应用程序所有。
define('LP_USER1', LP_BASE << 4);
/// \brief 用户级别2。级别说明的最终解释权归应用程序所有。
define('LP_USER2', LP_BASE << 5);
/// \brief 警告信息。
define('LP_WARNING', LP_BASE << 6);
/// \brief 普通错误。大部分错误信息都用此级别记录。
define('LP_ERROR', LP_BASE << 7);
/// \brief 严重错误。只在系统层发生严重错误时，才用此级别。例如出现硬件故障的情况。
define('LP_CRITICAL', LP_BASE << 8);
/// \brief 当前最大日志级别。
define('LP_MAX', LP_CRITICAL);

/*! \brief 记录日志
 *  \param[in] LEVEL 日志级别
 *  \param[in] ...   日志信息
 *  \note 例如 OSS_LOG(LP_INFO,"info log level string rest \n");
 */
function OSS_LOG($codefilename, $codefileline, $loglevel, $log)
{
	Logger::Instance()->writeLog($codefilename, $codefileline, $loglevel, $log);
}

/*! \brief 记录平题啊日志
 *  \param[in] LEVEL 日志级别
 *  \param[in] ...   日志信息
 *  \note 例如 OSS_LOG_PLAT(LP_INFO,"info log level string rest \n");
 */
function OSS_LOG_PLAT($codefilename, $codefileline, $loglevel, $actid, $platname, $log)
{
    Logger::Instance()->writePlatLog($codefilename, $codefileline, $loglevel, $actid, $platname, $log);
}

/**
* 默认异常处理
*当异常被抛出时，其后的代码不会继续执行，PHP 会尝试查找匹配的 "catch" 代码块。
*如果异常没有被捕获，而且又没用使用 set_exception_handler() 作相应的处理的话，那么将发生一个严重的错误（致命错误），
* 并且输出 "Uncaught Exception" （未捕获异常）的错误消息。
* 当你的程序只有throw而没有catch块时，程序按你自定义异常的处理来处理异常.
* @param object $e 异常对象
*/
/*function exceptionHandler($e)
{
	echo 'Uncaught Exception';
    //print "\n<strong>Exception Thrown:</strong>\n";
    print_r($e);
    exit;
}

set_exception_handler('exceptionHandler');*/
?>
