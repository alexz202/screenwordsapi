<?php
// 0 操作成功
define( "SUCCEED", 0 );

/**
 * 用户相关
 */


/**
 * 业务逻辑相关
 */
define( "NOTINVAILD",1);
define( "EXIST_SCREENWORDS",2);
define( "WORD_EXIST",3);
define( "ERROR_ADDWORD",4);


/**
 * 服务器致命错误
 */
// -1 unknown error
// -2 access denied  /* 用户大量非法请求 */
// -3 action does not support
// -4 服务器维护
// -5 system error
// -6 用户没有该道具
// -7 用户伪造请求

define( "FATAL_ERROR_UNKNOWN_ERROR", -1 );
define( "FATAL_ERROR_USER_ACCESS_DENIED", -2 );
define( "FATAL_ERROR_INVALID_ACTION", -3 );
define( "FATAL_ERROR_SYSTEM_MANTAIN", -4 );
define( "FATAL_ERROR_SYSTEM_ERROR", -5 );
define( "FATAL_ERROR_USER_TOOL_INVALID", -6 );
define( "FATAL_ERROR_FAKE_PARAMS", -7 );

?>
