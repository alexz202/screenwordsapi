<?php
/**
 * TCP Log/Rank Server基类
 *
 * @author ccsong
 */

class baseTCPLogMgr {
	public static function getConfig() {
		$config = GetGlobalConfig();
		return $config['BASE_TCP_CLASS'];
	}

	public static function sendTcpPackage($host,$port,$content,&$output,$timeout,$errMsg) {
		$config  = self::getConfig();
		$res = 0;
		if($config['class']=="Ef") {
			$ef_sock = new EfSocket(array(array("name"=>$host,"port"=>$port)),$timeout/1000);
			$output = $ef_sock->exchange($content);
		} else {
			//union the return code
			$res = SocketAPI::tcpPackageTimeout( $host, $port, $content, $outBuf, $timeout, $errMsg)-1;
			$output=$outBuf;
		}

		return $res;
	}
}
