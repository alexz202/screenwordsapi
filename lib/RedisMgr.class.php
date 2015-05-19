<?php
/*
 * Redis 操作类
 */

class RedisMgr{
	private static function getLogConfig(){
		$config = GetGlobalConfig();
		return $config['REDIS'];
	}

	public static function isredisEnable(){
		$config = self::getLogConfig();
		if( $config['is_redis'] == 1 ){
			return true;
		}
		else{
			return false;
		}
	}

	private static function connect(){
		$config=self::getLogConfig();
		$redis=new Redis();
		if($redis->connect($config['host'],$config['post'])){
			return $redis;
		}else{
			   OSS_LOG(__FILE__, __LINE__, LP_ERROR, 'redis connect error'. "\n");
		 return false;
		} 
	}
	public static function set($key,$value){
		if($redis=self::connect())
		return  $redis->set($key,$value);
		else
		return false;
	}
	
	public static function get($key){
		if($redis=self::connect())
		return  $redis->get($key);
		else
		return false;
	}
	public static function del($key){
		if($redis=self::connect())
		return  $redis->del($key);
		else
		return false;
	}
	//hashmap
	public static function hSet($object,$field,$value){
		if($redis=self::connect())
		return  $redis->hSet($object,$field,$value);
		else
		return false;
	}
	public static function hGet($object,$field){
		if($redis=self::connect())
		return  $redis->hGet($object,$field);
		else
		return false;
	}
   public static function hDel($object,$field){
		if($redis=self::connect())
		return  $redis->hDel($object,$field);
		else
		return false;
	}
   public static function hExists($object,$field){
		if($redis=self::connect())
		return  $redis->hExists($object,$field);
		else
		return false;
	}
	
	public static function hIncrBy($object,$field,$value){
		if($redis=self::connect())
		return  $redis->hIncrBy($object,$field,$value);
		else
		return false;
	}
	//set
   public static function sAdd($key,$value){
		if($redis=self::connect())
		return  $redis->sAdd($key,$value);
		else
		return false;
	}
	
	public static function sSize($key){
		if($redis=self::connect())
		return  $redis->sSize($key);
		else
		return false;
	}
	
	public static function sIsMember($key,$value){
		if($redis=self::connect())
		return  $redis->sIsMember($key,$value);
		else
		return false;
	}
	
}