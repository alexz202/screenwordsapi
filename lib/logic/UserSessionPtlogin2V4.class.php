<?php
//**********************************************************
// File name: UserSessionPtlogin2V4.class.php
// Class name: UserSessionPtlogin2V4
// Create date: 2011/09/20
// Update date: 2011/09/20
// Author: garyzou
// Description: 用户登录类
//**********************************************************

define('STX', 0x02);
define('ETX', 0x03);
define('CMD_SESSION_VERIFY2', 0x16);
define('CMD_SESSION_VERIFY4', 0x1b);

define('CMD_VERIFYCODE', 0x01);

define('VERIFY_OK', 0x01);
define('RESULT_HEAD_LENGTH', 66);
define('RESULT_PERSONAL_LENGTH', 14);
define('VERIFY_TIMEOUT', 1);

define('SUBCMD_NO_INFO_VERIFY', 1);
define('SUBCMD_DETAIL_INFO_VERIFY', 2);

//define(SOBAR_APP_ID, 21000108);

define('PTLOGIN_SEQ_KEY', "ptl_seq");

class UserSessionPtlogin2V4
{
	const DEFAULT_SESSION_SERVER_ADDR = "172.16.195.235";
	const DEFAULT_SESSION_SERVER_PORT = 8807;
	
	const DEFAULT_SESSION_SERVER_ADDR_BAK = "172.16.236.81";	
	const DEFAULT_SESSION_SERVER_PORT_BAK = 8808;
	
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
    function UserSessionPtlogin2V4($appID,$iUin=0,$sKey=NULL,$sClientIP=NULL,$SessionServerAddr=self::DEFAULT_SESSION_SERVER_ADDR,$SessionServerAddrBak=self::DEFAULT_SESSION_SERVER_ADDR_BAK)
	//function UserSessionPtlogin2V4($appID)
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
		$inBufLength = self::encode($uin, $userIp, $skey, $appID, $seq, $inBuf, CMD_SESSION_VERIFY4);
		// modified by parkerzhu 2011.11.4 for relay server auto switch
		// modified by parkerzhu 2010.1.7 for redirect server begin
        //$str = null;
        //if(function_exists("shmop_open"))
        //{
        //    $tok = ftok("/usr/local/oss_dev/config/osslogic.cfg", pack("c", 8));
        //    $shmid = shmop_open($tok, "a", 0644, 1024);
        //    if($tok && $shmid) {
        //        $str = trim(shmop_read($shmid, 0, 32));
        //    }
        //}
        //if(!empty($str)) {
        //    $pair = explode("|", $str);
        //    $sessionHost = $pair[0];
        //    $sessionPort = $pair[1];
        //}
        //else {
    	//	$ServerAddr = self::GetTargetServer(self::getSequence());
		//	$sessionHost = $ServerAddr["ip"];
		//	$sessionPort = $ServerAddr["port"];
			/*if(self::getSequence()%2==0){
				$sessionHost = self::DEFAULT_SESSION_SERVER_ADDR;
				$sessionPort = self::DEFAULT_SESSION_SERVER_PORT;
			}else{
				$sessionHost = self::DEFAULT_SESSION_SERVER_ADDR_BAK;
				$sessionPort = self::DEFAULT_SESSION_SERVER_PORT_BAK;
			}*/
        //}
		// modified by parkerzhu end
		//if(!SocketAPI::udpPackage($sessionHost, $sessionPort, $inBuf, $outBuf, $errMsg,true))
		//if(!SocketAPI::udpPackageTimeout($sessionHost, $sessionPort, $inBuf, $outBuf, 5000, $errMsg))
		//{
		//	return false;
		//}
        $relayServer = new RelayServerSocketAPI();
        $ret = $relayServer->udpPackage(PROID_USERSSION_PTLOGIN2, $inBuf, $outBuf, $errMsg, 5000);
        if($ret < 0)
		{
            $ret = $relayServer->udpPackage(PROID_USERSSION_PTLOGIN2_BAK, $inBuf, $outBuf, $errMsg, 5000);
            if($ret < 0)
            {
			return false;
		}
        }
        // modified by parkerzhu end
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
	/*
	typedef struct                                                   
	{                                                                
		unsigned int dwSocketid;                                     
		unsigned int dwSeq;     //发送校验的seqence，每次发送完应递增                       
		unsigned int dwUin;                                          
		unsigned int dwUserIp;   //用户ip
		int iAppID;								//业务申请appid,不正确不能通过验证

		unsigned short wVersion;                //版本号 填1                     
		unsigned short wSubCmd;                 // 1 获得用户登录时间 2 获得用户详细资料                     
		unsigned int dwOption;  				// dwOption &0x00000001 编码开关 0 utf8，1 gbk)
												// dwOption &0x00000002 是否touch 0 touch 1 只验不touch
		char sReserve[8];                                            
	}SessionHead;         
	*/
	protected function encode($uin, $userIP, &$skey, $appID, $seq, &$buf, $cmd)
	{
		//echo bin2hex(pack("N",2202080778))."<br/>";
		//echo bin2hex(pack("N","2202080778"))."<br/>";
		$abuf = &pack("nnLnNnNnN", $cmd, 0, $userIP, 0, 0, 0, 0, 0, 0);
		//SessionHead
		$abuf = $abuf . pack("NNNNNnnNa8", (double)$uin, $seq, (double)$uin, $userIP, $appID, 1, 2, 0, '');
		$abuf = $abuf . pack("n", strlen($skey)) . $skey . pack("c", ETX);
		$pakLength = 3 + strlen($abuf);
		$buf = pack("cn", STX, $pakLength) . $abuf;
		return $pakLength;
	}
	/*
	typedef struct
	{
		unsigned int dwLoginTime;  				//登录时间
		unsigned int dwLastaccessTime;			//最后访问时间
		char cAge;								//年龄
		char cGender;		
		unsigned int dwFace;					//脸谱
		char cPassPort_len;
		char sPassPort[PASSPORT_LEN];					//增值中心位
		char cNickName_len;
		char sNickName[MAX_NICK_LEN+1];					//昵称编码
		char cEmail_len;						
		char sEmail[MAX_EMAIL_LEN+1];						//邮箱
	}UserInfo;*/
	protected function decode(&$buf, $recvLen, $uin, $seq, &$nick, &$face)
	{
		$res = &unpack("cblank/n14heads/NdwSocketid/Nseq/Nuin/NdwUserIp/NAppID/nwVersion/nwSubCmd/NdwOption/a8sReserve/Cres", $buf);
		/*echo "dwSocketid:".$res['dwSocketid']."<br/>";
		echo "seq:".$res['seq']."<br/>";
		echo "uin:".$res['uin']."<br/>";
		echo "dwUserIp:".$res['dwUserIp']."<br/>";
		echo "AppID:".$res['AppID']."<br/>";
		echo "wVersion:".$res['wVersion']."<br/>";
		echo "wSubCmd:".$res['wSubCmd']."<br/>";
		echo "dwOption:".$res['dwOption']."<br/>";
		echo "res:".$res['res']."<br/>";*/
		$res["uin"]=sprintf('%u', $res['uin']);		
		if($res['res'] == VERIFY_OK && $res["uin"] == $uin && $res['seq'] == $seq && $res['wVersion'] == SUBCMD_NO_INFO_VERIFY && $res['wSubCmd'] == SUBCMD_DETAIL_INFO_VERIFY)		
		{			
			$personInfo = unpack("NdwLoginTime/NdwLastaccessTime/Cage/Cgender/Nface", substr($buf, RESULT_HEAD_LENGTH, RESULT_PERSONAL_LENGTH));
			/*echo "dwLoginTime:".$personInfo['dwLoginTime']."<br/>";
			echo "dwLastaccessTime:".$personInfo['dwLastaccessTime']."<br/>";
			echo "age:".$personInfo['age']."<br/>";
			echo "gender:".$personInfo['gender']."<br/>";
			echo "dwFace:".$personInfo['dwFace']."<br/>";*/
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
			//echo "passPort:".$passPort."<br/>";
		}
		if($currPos < $recvLen)
		{
			$nickName = self::getString($buf, $currPos);
			//echo "nickName:".$nickName."<br/>";
		}
		if($currPos < $recvLen)
		{
			$mail = self::getString($buf, $currPos);
			//echo "mail:".$mail."<br/>";
		}
		if($currPos < $recvLen)
		{
			$errMsg = self::getString($buf, $currPos);
			//echo "errMsg:".$errMsg."<br/>";
		}
		$face = $personInfo['face'];
		$nick = $nickName;
		return true;
	}

	// 返回第一个随机选取的server
	// 同时设置下次调用的是另一个server, 用rand做参数是为了在server1 和server2 之间负载均衡
	protected function GetTargetServer($rand)
	{
		$sAddrServer1["ip"] = self::DEFAULT_SESSION_SERVER_ADDR;
		$sAddrServer1["port"] = self::DEFAULT_SESSION_SERVER_PORT;
		$sAddrServer2["ip"] = self::DEFAULT_SESSION_SERVER_ADDR_BAK;
		$sAddrServer2["port"] = self::DEFAULT_SESSION_SERVER_PORT_BAK;
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
