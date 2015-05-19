<?php
/*
公共函数
*/

function GetGlobalConfig()
{
    global $g_config;
    if(!isset($g_config)){
        $g_config = parse_ini_file(CONFIG_PATH, true);
    }
    return $g_config;
}

function GetIP()
{
//if(!empty($_SERVER["HTTP_CLIENT_IP"]))
//   $cip = $_SERVER["HTTP_CLIENT_IP"];
//else if(!empty($_SERVER["HTTP_X_FORWARDED_FOR"]))
//   $cip = $_SERVER["HTTP_X_FORWARDED_FOR"];
//else if(!empty($_SERVER["REMOTE_ADDR"]))
   $cip = $_SERVER["REMOTE_ADDR"];
//else
//   $cip = "";
   return $cip;
}

/**
 * GBK转UTF8，传入的数据可为数组或字符串
 * 数组则继续解析到字符串
 * @param $str
 * @return unknown_type
 */
function GBKtoUTF8($str)
{
    if(is_array($str))
    {
        foreach ($str as $value) 
        {
            $value = GBKtoUTF8($value);
        }
        return $str;
    }
    elseif (is_string($str))
    {   
        $str = iconv("GBK", "UTF-8//IGNORE", $str);
        return $str;
    }
    else
    {
        return $str;
    }
}

/**
 * UTF8转GBK，传入的数据可为数组或字符串
 * 数组则继续解析到字符串
 * @param $str
 * @return unknown_type
 */
function UTF8toGBK(&$str)
{
    if(is_array($str))
    {
        foreach ($str as $value) 
        {
            $value = UTF8toGBK($value);
        }
        return $str;			
    }
    elseif (is_string($str))
    {   
        $str = iconv("UTF-8", "GBK//IGNORE", $str);
        return $str;
    }
    else
    {
        return $str;
    }
}

function jsEncode($str)
{
    $trans = array(
        '<' => '&#60;',
        '>' => '&#62;',
        "'" => '&#39;',
        '"' => '&#34;',
        ',' => '&#44;',
        '(' => '&#40;',
        ')' => '&#41;',
        '?' => '&#63;',
        '\\' => '&#92;',
    );
    return strtr($str,$trans);
}

/**
 * javascript redirection
 * @param <type> $url
 */
function js_redirect( $url ) {
    echo "<script type=\"text/javascript\">setTimeout(\"redirect()\",1000);function redirect(){window.location = \"$url\";}</script>";
}


//遍历全部目录下的指定文件，并加载
function autoLoadFile($path, $para){
	$filelist = glob($path.$para);

	$filelistnew =array();
	foreach ($filelist as $v) {
		$namelen = strlen($v);
		$filelistnew[$namelen][] = $v;
	}
//	ksort($filelistnew);

	$filelistresult = array();
	foreach ($filelistnew as $v) {
		$filelistresult = array_merge($filelistresult, $v);
	}
	foreach ($filelistresult as $filename) {
		require($filename);
	}
}

function urlDecoder($arr) {
	foreach ($arr as $key=>$value) {
		$arr["$key"] = urldecode($value);
	}
	return $arr;
}

?>
