<?php
ini_set('display_errors',0);
header("Content-Type:text/html; charset=utf-8");
include '../libs/class.php';
include '../libs/config.php';
include '../libs/MagicCrypt.php';
include '../libs/function.php';  
include '../libs/Web_class.php';

$inputStr=file_get_contents("php://input");//接收資料
$inputData=json_decode($inputStr,1);
$verifyStr = "";
if($inputData["site_domain"]!=""){//選擇站台
	$db="groumet";
	$dbUser="root";
	$dbPwd="";
	$receiveVerifyKey = "123";
	$log_action =explode("/","ALL/9001");
	$need_action = explode(",",$log_action[0]);
	$ignore_action = explode(",",$log_action[1]);
}


foreach ($inputData["paras"] as $key => $val) {
	$verifyStr .= $val;
}

$verifyKey=md5($verifyStr.$receiveVerifyKey);

if($inputData["key"]!= $verifyKey){
	return 0;
}

$dataArray=$inputData["paras"];	//接收資料
$action = $inputData["action"];	//動作代碼
$dataArray["site_domain"] = $inputData["site_domain"];
//------------API呼叫紀錄---------------
$log_sw = false;
if(($need_action[0] == "ALL" || in_array($action, $need_action)) && $need_action[0] != "" && !in_array($action, $ignore_action)){
	$log_sw = true;
	$log_queryStr="INSERT INTO `log_action_recode`(`domain`, `action`, `paras`, `call_date`) VALUES ('$dataArray[site_domain]','$action','".intodbTrans(json_encode($dataArray,JSON_UNESCAPED_UNICODE))."','".date("Y-m-d H:i:s")."')";
	$logIdInt=insertData($log_queryStr);
}


switch ($action) {	//依動作代碼執行API
	case "1001":	//取得地點
		$api=new WebClass();
		$result=$api->GetCity($dataArray);
		break;
	case "1002":	//取得食物類別
		$api=new WebClass();
		$result=$api->GetFoodType($dataArray);
		break;
	case "1003":	//取得店家
		$api=new WebClass();
		$result=$api->GetShop($dataArray);
		break;
		
}


//------------API呼叫紀錄 回傳結果---------------
//------------API呼叫紀錄 回傳結果---------------
if($log_sw){
	$log_queryStr="UPDATE `log_action_recode` SET `callback_date`='".date("Y-m-d H:i:s")."',`result` = '".mb_strlen(intodbTrans(json_encode($result)),"utf8")."' WHERE `id` = '$logIdInt'";
	queryData($log_queryStr);
	
	$log_queryStr="UPDATE `log_action_recode` SET `result` = concat(`result`,' - ','".intodbTrans(json_encode($result))."') WHERE `id` = '$logIdInt'";
	queryData($log_queryStr);
}

echo json_encode($result);



?>