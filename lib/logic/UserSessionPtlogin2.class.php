<?php
//**********************************************************
// File name: UserSessionPtlogin2.class.php
// Class name: UserSessionPtlogin2
// Create date: 2009/04/17
// Update date: 2011/03/14
// Author: garyzou
// Description: 用户登录类
//**********************************************************

define('STX', 0x02);
define('ETX', 0x03);
define('CMD_SESSION_VERIFY2', 0x16);
define('CMD_VERIFYCODE', 0x01);

define('VERIFY_OK', 0x01);
define('RESULT_HEAD_LENGTH', 38);
define('RESULT_PERSONAL_LENGTH', 14);
define('VERIFY_TIMEOUT', 1);

//define(SOBAR_APP_ID, 21000108);

define('PTLOGIN_SEQ_KEY', "ptl_seq");

class UserSessionPtlogin2
{
	//const DEFAULT_SESSION_SERVER_ADDR = "172.23.32.48";
	//const DEFAULT_SESSION_SERVER_ADDR_BAK = "172.23.32.45";
	const DEFAULT_SESSION_SERVER_ADDR = "172.16.236.81";
	const DEFAULT_SESSION_SERVER_ADDR_BAK = "172.27.129.228";
	const SESSION_PORT = 18891;

	// add by parkerzhu 2011.1.7 begin
	const DEFAULT_REDIRECT_SERVER_ADDR = "172.16.195.235";
	const DEFAULT_REDIRECT_SERVER_PORT = "8807";
	// add by parkerzhu end

   	private $m_iAppID;
	/* \brief 标志用户是否登录 */
    private $m_bIsUserLogin;

    /* \brief 用户QQ号码 */
    private $m_iUin;
    private $m_sKey;
    private $m_iFace;
    /* \brief 用户昵称 */
    private $m_sNickName;
    private $m_sClientIP;
	/**
	 * Constructor
	 * @access protected
	 */
    function UserSessionPtlogin2($appID,$iUin=0,$sKey=NULL,$sClientIP=NULL,$SessionServerAddr=self::DEFAULT_SESSION_SERVER_ADDR,$SessionServerAddrBak=self::DEFAULT_SESSION_SERVER_ADDR_BAK)
	//function UserSessionPtlogin2($appID)
	{
		$this->m_iAppID = $appID;
		$this->m_bIsUserLogin = false;
		$this->m_sNickName = NULL;
		$this->m_iFace = 0;
		$this->m_sClientIP = $_SERVER['REMOTE_ADDR'];
		$this->get_uin_skey($this->m_iUin, $this->m_sKey);
		if(strlen($this->m_iUin)<=4 || strlen($this->m_sKey)!=10)
		{
			return false;
		}
      	else
      	{
			$this->m_bIsUserLogin = $this->login_verify($this->m_iAppID,$this->m_iUin,$this->m_sClientIP,$this->m_sKey,$this->m_sNickName,$this->m_iFace);
			return $this->m_bIsUserLogin;
        }
	}


	 /*!
    * \brief 判断用户是否登录
    * \return true:已经登录 false:尚未登录
    */
    public function IsLogin()
	{
		return $this->m_bIsUserLogin;
	}

    /*!
    * \brief 取用户昵称
    * \return 用户昵称
    * \throw OssException 如果用户没有登录时调用此函数，则会抛出异常
    */
    public function GetNickName()
    {
    	return $this->m_sNickName;
	}

    /*!
    * \brief 取用户uin
    * \return 用户uin
    * \throw OssException 如果用户没有登录时调用此函数，则会抛出异常
    */
    public function GetUin()
    {
    	return $this->m_iUin;
	}

	public function login_verify($appID, $uin, $userIp, &$skey, &$nick, &$face)
	{
		//$appID = SOBAR_APP_ID;
		$seq = self::getSequence();
		//$uin = (int) $uin;
		$userIp = (int)ip2long($userIp);
		$inBufLength = self::encode($uin, $userIp, $skey, $appID, $seq, $inBuf, CMD_SESSION_VERIFY2);
		// modified by parkerzhu 2010.1.7 for redirect server begin
        $str = null;
        if(function_exists("shmop_open"))
        {
            $tok = ftok("/usr/local/oss_dev/config/osslogic.cfg", pack("c", 8));
            $shmid = shmop_open($tok, "a", 0644, 1024);
            if($tok && $shmid) {
                $str = trim(shmop_read($shmid, 0, 32));
            }
        }
        if(!empty($str)) {
            $pair = explode("|", $str);
            $sessionHost = $pair[0];
            $sessionPort = $pair[1];
        }
        else {
    		$sessionHost = self::GetTargetServer(self::getSequence());
    		//$sessionPort = self::SESSION_PORT;
    		//$sessionHost = self::DEFAULT_REDIRECT_SERVER_ADDR;
    		$sessionPort = self::DEFAULT_REDIRECT_SERVER_PORT;
        }
		// modified by parkerzhu end
		//if(!SocketAPI::udpPackage($sessionHost, $sessionPort, $inBuf, $outBuf, $errMsg,true))
		if(!SocketAPI::udpPackageTimeout($sessionHost, $sessionPort, $inBuf, $outBuf, 5000, $errMsg))
		{
			return false;
		}
		//echo $inBuf."<br/>";
		//echo $outBuf."<br/>";
		$outBufLength = strlen($outBuf);
		if(self::decode($outBuf, $outBufLength, $uin, $seq, $nick, $face))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	private function get_uin_skey(&$uin, &$skey)
	{
		$uin = $_COOKIE['uin'];
		$skey = $_COOKIE['skey'];
		if ($uin == '')
		{
			return false;
		}

		if ($uin{0} == 'o')
		{
			$uin = substr($uin ,1);
		}

		$uin_len = strlen($uin);
		for ($i=0; $i<$uin_len; $i++)
		{
			if ($uin{$i} != '0') break;
		}

		$uin = substr($uin, $i);
		if ($uin == '')
		{
			return false;
		}
		return true;
	}

	protected function &getString($buf, &$currPos)
	{
		$len = unpack("Clen", substr($buf, $currPos, 1));
		$currPos += 1;
		$value = &substr($buf, $currPos, $len['len']);
		$currPos += $len['len'];
		return $value;
	}

	protected function getSequence()
	{
		srand((double)microtime()*1000000);
        return rand();
	}

	protected function encode($uin, $userIP, &$skey, $appID, $seq, &$buf, $cmd)
	{
		//echo bin2hex(pack("N",2202080778))."<br/>";
		//echo bin2hex(pack("N","2202080778"))."<br/>";
		$abuf = &pack("nnLnNnNnNNNNNN", $cmd, 0, $userIP, 0, 0, 0, 0, 0, 0, (double)$uin, $seq, (double)$uin, $userIP, $appID);
		$abuf = $abuf . pack("n", strlen($skey)) . $skey . pack("c", ETX);
		$pakLength = 3 + strlen($abuf);
		$buf = pack("cn", STX, $pakLength) . $abuf;
		return $pakLength;
	}

	protected function decode(&$buf, $recvLen, $uin, $seq, &$nick, &$face)
	{
		$res = &unpack("cblank/n14heads/Nuin/Nseq/Cres/Cage", $buf);
		$res["uin"]=sprintf('%u', $res['uin']);
		if($res['res'] == VERIFY_OK && $res["uin"] == $uin && $res['seq'] == $seq)
		{
			$personInfo = unpack("Cage/Cgender/Nface/Nlogintime/Nlastaccess", substr($buf, RESULT_HEAD_LENGTH, RESULT_PERSONAL_LENGTH));
		}
		else
		{
			//error
			$currPos = RESULT_HEAD_LENGTH;
			$errMsg = $this->getString($buf, $currPos);
			//Log::instance()->logError("ptlogin:$errMsg");
			return false;
		}
		$currPos = RESULT_HEAD_LENGTH + RESULT_PERSONAL_LENGTH;

		if($currPos < $recvLen)
		{
			$passPort = self::getString($buf, $currPos);
		}
		if($currPos < $recvLen)
		{
			$nickName = self::getString($buf, $currPos);
		}
		if($currPos < $recvLen)
		{
			$mail = self::getString($buf, $currPos);
		}
		if($currPos < $recvLen)
		{
			$errMsg = self::getString($buf, $currPos);
		}
		$face = $personInfo['face'];
		$nick = $nickName;
		return true;
	}

	// 返回第一个随机选取的server
	// 同时设置下次调用的是另一个server, 用rand做参数是为了在server1 和server2 之间负载均衡
	protected function GetTargetServer($rand)
	{
		$sAddrServer1 = self::DEFAULT_SESSION_SERVER_ADDR;
		$sAddrServer2 = self::DEFAULT_SESSION_SERVER_ADDR_BAK;
		static $tmp = 0;
		if($tmp %2 == 0)
		{
			++$tmp;
			if($rand%2 == 0)
				return $sAddrServer1;
			else
				return $sAddrServer2;
		}
		else
		{
			++$tmp;
			if($rand%2 == 0)
				return $sAddrServer2;
			else
				return $sAddrServer1;
		}
	}
}
?>
