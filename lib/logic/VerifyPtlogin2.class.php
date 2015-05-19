<?php
//**********************************************************
// File name: VerifyPtlogin2.class.php
// Class name: VerifyPtlogin2
// Create date: 2010/12/28
// Update date: 2012/01/04
// Author: garyzou
// Description: 验证码验证类
//**********************************************************

define('STX', 0x02);
define('ETX', 0x03);
define('CMD_VERIFYCODE', 0x01);
define('CMD_SESSION_VERIFY', 0x13);
define('CMD_SESSION_VERIFY2', 0X16);
define('CMD_SESSION_LOGOUT', 0x12);

define('VERIFY_FAIL', 0);
define('VERIFY_OK', 1);

define('RESULT_HEAD_LENGTH', 43);
define('VERIFY_TIMEOUT', 1);

define('VC_VERIFY_FAIL', VERIFY_FAIL);
define('VC_VERIFY_OK', VERIFY_OK);

class VerifyPtlogin2
{
	const SERVER_TIME_SECOND = 5;
	const DEFAULT_VERIFY_SERVER_ADDR = "172.23.32.42";
	const DEFAULT_VERIFY_SERVER_ADDR_BAK = "172.23.32.44";
	const DEFAULT_VERIFY_PORT = 18888;

	private $m_VerifyServerAddr;
	private $m_VerifyServerPort;
	private $m_VerifyServerAddrBak;
	private $m_VerifyServerPortBak;


	/*!
	* \brief 构造函数
	* \param[in] VerifyServerAddr 验证服务器地址
	* \param[in] VerifyServerPort 验证服务器端口
	* \param[in] VerifyServerAddrBak 验证服务器地址
	* \param[in] VerifyServerPortBak 验证服务器端口
	*/
	function VerifyPtlogin2($VerifyServerAddr=self::DEFAULT_VERIFY_SERVER_ADDR, $VerifyServerPort=self::DEFAULT_VERIFY_PORT, $VerifyServerAddrBak=self::DEFAULT_VERIFY_SERVER_ADDR_BAK, $VerifyServerPortBak=self::DEFAULT_VERIFY_PORT)
	{
        $this->m_VerifyServerAddr = $VerifyServerAddr;
        $this->m_VerifyServerPort = $VerifyServerPort;
        $this->m_VerifyServerAddrBak = $VerifyServerAddrBak;
        $this->m_VerifyServerPortBak = $VerifyServerPortBak;
	}


	/*!
	* \brief 判断验证码是否有效
	* \param[in] sCode 用户输入的验证码
	* \param[in] sSessionID 验证码对应的cookie值
	* \param[in] iAppId 验证码对应的aid值
	* \param[in] userIp 用户端ip
	* \throw OssException
	*/
	public function IsCodeValid($sCode, $sSessionID, $iAppId =0, $userIp=0)
	{
		if(empty($sCode) || empty($sSessionID))
		{
			return false;
		}
		$seq = self::getSequence();
		$userIp = (int)ip2long($userIp);
		$iValidTime = 1200;
		$chSendTimes = 0;
		$inBufLength = self::encode($sCode, $sSessionID, $iValidTime, $userIp, 0, $iAppId, $seq, $chSendTimes, $inBuf, CMD_VERIFYCODE);
		/*//garyzou 2012-01-04 modify
		self::GetTargetServer($seq,$verifyHost,$verifyPort);		
		if(!SocketAPI::udpPackage($verifyHost, $verifyPort, $inBuf, $outBuf, $errMsg, true))		
		{
			return false;
		}*/
		
		$relayServer = new RelayServerSocketAPI();
		$ret = $relayServer->udpPackage(PROID_PTLOGIN2, $inBuf, $outBuf, $errMsg, 5000);
		if($ret < 0)
		{
			$ret = $relayServer->udpPackage(PROID_PTLOGIN2_2, $inBuf, $outBuf, $errMsg, 5000);
			if($ret < 0)
			{
				return false;
			}
		}
		if(empty($outBuf))
		{
			return false;
		}	
		
		$outBufLength = strlen($outBuf);
		if(self::decode($outBuf, $outBufLength, $sCode, $seq, $errMsg))
		{		
			return true;
		}
		else
		{
			return false;
		}
	}

	protected function &getString($buf, &$currPos)
	{
		$len = unpack("Clen", substr($buf, $currPos, 1));
		$currPos += 1;
		$value = &substr($buf, $currPos, $len['len']);
		$currPos += $len['len'];
		return $value;
	}

	protected function &getCode($buf)
	{
		$value = &substr($buf, 29, 4);
		return $value;
	}

	protected function getSequence()
	{
		srand((double)microtime()*1000000);
        return rand();
	}

	protected function encode($szCode, $szVerifySession, $iValidTime, $uiUserIP, $ushUserPort, $iAppID, $uiSeq, $chSendTimes, &$buf, $cmd)
	{
		$abuf = &pack("nnLnNnNnccn", $cmd, 0, $uiUserIP, $ushUserPort, 0, 0, 0, 0, 0, 0, 0);
		$abuf = $abuf . $szCode. pack("NNNc", $uiSeq, $iAppID, $iValidTime, $chSendTimes) . pack("c", strlen($szCode)) . $szCode  . pack("n", strlen($szVerifySession)) . $szVerifySession .  pack("c", ETX);
		$pakLength = 3 + strlen($abuf);
		$buf = pack("cn", STX, $pakLength) . $abuf;
		return $pakLength;
	}

	protected function decode(&$buf, $recvLen, $szCode, $seq, &$errMsg)
	{
		$res = &unpack("cblank/n14heads/Ncode/Nseq/cres/cpReqNums/LpGetImgTime", $buf);
		$res["code"] = $this->getCode($buf);
		if($res['res'] == VERIFY_OK && $res["code"] == $szCode && $res['seq'] == $seq)
		{
			return true;
		}
		else
		{
			$currPos = RESULT_HEAD_LENGTH;
			$errMsg = $this->getString($buf, $currPos);
			//echo "errMsg:$errMsg<br>";
			return false;
		}
	}

	// 返回第一个随机选取的server
	// 同时设置下次调用的是另一个server, 用rand做参数是为了在server1 和server2 之间负载均衡
	protected function GetTargetServer($rand,&$Addr,&$Port)
	{
		static $tmp = 0;
		if($tmp%2 == 0)
		{
			++$tmp;
			if($rand%2 == 0)
			{
				$Addr = $this->m_VerifyServerAddrBak;
				$Port = $this->m_VerifyServerPortBak;
			}
			else
			{
				$Addr = $this->m_VerifyServerAddr;
				$Port = $this->m_VerifyServerPort;
			}
		}
		else
		{
			++$tmp;
			if($rand%2 == 0)
			{
				$Addr = $this->m_VerifyServerAddrBak;
				$Port = $this->m_VerifyServerPortBak;
			}
			else
			{
				$Addr = $this->m_VerifyServerAddr;
				$Port = $this->m_VerifyServerPort;
			}
		}
	}
}
?>
