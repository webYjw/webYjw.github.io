<?php
header("Content-Type: text/json;charset=utf-8");
$callback=empty($_REQUEST['callback'])?"":$_REQUEST['callback'];
require_once "text.php";
$jssdk = new JSSDK('wx1aa0cca1a108225c', '2d110fb32776fff5161e2927c57dd4a5');
$signPackage = $jssdk->GetSignPackage();
$arr = array("status"=>1,"message"=>"成功","result"=>$signPackage);
echo json_encode($arr);