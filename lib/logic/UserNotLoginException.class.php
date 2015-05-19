<?php
//**********************************************************
// File name: UserNotLoginException.class.php
// Class name: UserNotLoginException
// Create date: 2009/04/17
// Update date: 2009/04/17
// Author: garyzou
// Description: 没有登陆异常处理类
//**********************************************************

/*
class Exception
{
    protected $message = 'Unknown exception';   // 异常信息
    protected $code = 0;                        // 用户自定义异常代码
    protected $file;                            // 发生异常的文件名
    protected $line;                            // 发生异常的代码行号

    function __construct($message = null, $code = 0);

    final function getMessage();                // 返回异常信息
    final function getCode();                   // 返回异常代码
    final function getFile();                   // 返回发生异常的文件名
    final function getLine();                   // 返回发生异常的代码行号
    final function getTrace();                  // backtrace() 数组
    final function getTraceAsString();          // 已格成化成字符串的 getTrace() 信息
    echo "异常信息：".$e->getMessage()."\\n"; //返回用户自定义的异常信息
	echo "异常代码：".$e->getCode()."\\n"; //返回用户自定义的异常代码
	echo "文件名：".$e->getFile()."\\n"; //返回发生异常的PHP程序文件名
	echo "异常代码所在行".$e->getLine()."\\n"; //返回发生异常的代码所在行的行号
	echo "传递路线：";
	print_r($e->getTrace()); //以数组形式返回跟踪异常每一步传递的路线
	echo $e->getTraceAsString(); //返回格式化成字符串的getTrace函数信息


    // 可重载的方法
    function __toString();                       // 可输出的字符串
}

*/
class UserNotLoginException extends Exception
{
	const DEFAULT_USER_VISIBLE_MSG = "对不起，您没有登录，请登录后重试！";

    // 重定义构造器使 message 变为必须被指定的属性
    public function __construct($message,$code = 0)
	{
		//"/usr/local/xxx/yyy/abc.log"
		$this->file = basename($this->file);        // $file is set to "abc.log"
		//$file = basename ($path,".log"); // $file is set to "abc"

        // 确保所有变量都被正确赋值
        parent::__construct($message, $code);
	}

    // 自定义字符串输出的样式 */
    public function __toString()
	{
		//return __CLASS__ . " [$this->file:$this->line] $this->message ";

		//[2009-04-03 18:18:51] [DEBUG] [xxxxx.php:149] logmessage
		//$log = "[".date("Y-m-d H:i:s")."] [".__CLASS__."] [".$this->file.":".$this->line."] ErrNo:".$this->code." ErrMsg:".$this->message;
		return "ErrNo:".$this->code." ErrMsg:".$this->message;
	}
	 /*! \brief 返回用户可见的提示信息
        * \return 用户可见的提示信息
        */
	public function GetUserVisibleMsg()
  	{
		return self::DEFAULT_USER_VISIBLE_MSG;
	}

}

?>
