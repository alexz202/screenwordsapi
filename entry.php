<?php

ini_set("display_errors", 'on');
error_reporting(E_ERROR);
//error_reporting(E_ALL);

define( 'debug', true );
if( debug ){
    define( 'CONFIG_PATH', './debug.cfg' );
}
else{
    define( 'CONFIG_PATH', 'ABSOLUTE_PATH.cfg' );
}

define('WEB_ROOT', dirname(__FILE__) );
define('CONFIG_DIR', WEB_ROOT.'/config' );
//define('API_DIR', WEB_ROOT.'/api' );
define( 'ALL_ACTION_INFO', CONFIG_DIR.'/actionConfig.inc.php' );
define( 'ALL_CACHE_INFO', CONFIG_DIR.'/cacheConfig.inc.php' );
require './framework.inc.php';
require './lib/common.php';

// 加载全部config文件
autoLoadFile(WEB_ROOT.'/config/','*.inc.php');
//加载全部lib目录里的公共类
autoLoadFile(WEB_ROOT.'/lib/','*.class.php');
//加载全部dao类
autoLoadFile(WEB_ROOT.'/dao/', '*.php');
//加载全部model类
autoLoadFile(WEB_ROOT.'/module/', '*.php');
//加载全部Logic类
//autoLoadFile(WEB_ROOT.'/logic/','*.php');
//autoLoadFile(WEB_ROOT.'/logic/gamelogic/'.'*.php');//fix load turn
?>
