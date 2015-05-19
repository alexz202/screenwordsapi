<?php
/**
 * Created by PhpStorm.
 * User: zhualex
 * Date: 15/5/11
 * Time: 上午10:36
 */
//$words=file_get_contents('nfsscreenword.csv');
$fp=fopen('nfs-new.csv','r') ;
$STR='insert into nfsscreenwords (words) VALUES ';
$wordsarr=array();
while(!feof($fp)){
    $words=fgetcsv($fp);
    //insert;
  foreach($words as $word){
      if(!empty($word)){
          $word=trim($word);
          $wordsarr[]="('$word')";
      }
  }
}
//echo count($wordsarr);
//var_dump($wordsarr);
$wordsstr=implode(',',$wordsarr);
ECHO $STR.$wordsstr;