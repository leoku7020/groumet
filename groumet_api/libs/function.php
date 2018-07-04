<?php
function getClientIP() {
	$ipAddress = '';
	if ($_SERVER['HTTP_CLIENT_IP'])
		$ipAddress = $_SERVER['HTTP_CLIENT_IP'];
	else if($_SERVER['HTTP_X_FORWARDED_FOR'])
		$ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
	else if($_SERVER['HTTP_X_FORWARDED'])
		$ipAddress = $_SERVER['HTTP_X_FORWARDED'];
	else if($_SERVER['HTTP_FORWARDED_FOR'])
		$ipAddress = $_SERVER['HTTP_FORWARDED_FOR'];
	else if($_SERVER['HTTP_FORWARDED'])
		$ipAddress = $_SERVER['HTTP_FORWARDED'];
	else if($_SERVER['REMOTE_ADDR'])
		$ipAddress = $_SERVER['REMOTE_ADDR'];
	else
		$ipAddress = 'UNKNOWN';
	return $ipAddress;
}

function selectData($queryStr){//查詢資料用
	global $dbIP,$dbUser,$dbPwd,$db;

	$link = mysqli_connect ( $dbIP, $dbUser, $dbPwd);

	mysqli_set_charset ( $link , 'utf8'  );
	mysqli_query ( $link , "SET NAMES 'UTF8'" );
	mysqli_query ( $link , "SET CHARACTER SET UTF8" );
	$db_selected = mysqli_select_db ( $link ,$db );
	$res = mysqli_query (  $link ,$queryStr );
	
	if($res==false){
		printErr(mysqli_error());
		mysqli_close($link);
		return false;
	}
	for ($i = 0; $i < mysqli_num_rows($res); $i ++) {
		$r = mysqli_fetch_assoc($res);
		$output[] = $r;
	}
	mysqli_close($link);
	
	//添加檢查欄位為base64的動作
	if(is_array($output) && count($output)>0){//檢查是否為陣列，以及是否有資料
		$output2=(Array)$output;
		for ($i=0;$i<count($output2);$i++){
			$transField=array();
// 			foreach ($output2[0] as $key=>$val){//取得轉換過的欄位
			foreach ($output2[$i] as $key=>$val){//取得轉換過的欄位
				if(isUrlEncode($val)==1){//是轉換過的，要轉碼
					array_push($transField,$key);
				}
			}
			if(count($transField)>0){//反轉
// 	 			for ($i=0;$i<count($output2);$i++){
					for ($j=0;$j<count($transField);$j++){
						$output2[$i][$transField[$j]]=urldecode($output2[$i][$transField[$j]]);
						
					}
// 	 			}
			}
		}
	}
	return $output2;	
}
function queryData($queryStr){//刪除、修改、新增資料用
	global $dbIP,$dbUser,$dbPwd,$db,$logSQL;
	$link = mysqli_connect ( $dbIP, $dbUser, $dbPwd);
	mysqli_set_charset ( $link  , 'utf8' );
	
	$db_selected = mysqli_select_db ( $link , $db);
	mysqli_query ( $link , "SET NAMES 'UTF8'" );
	mysqli_query ( $link , "SET CHARACTER SET UTF8" );
	
	$res = mysqli_query ( $link ,$queryStr );
	if (mysqli_affected_rows($link)>0)	$logSQL[] = $queryStr;	//修改紀錄
	if($res==false){
// 		echo "----".mysql_error()."----";
// 		print_r(mysql_error());
		printErr(mysqli_error());
		mysqli_close($link);
		return false;
	}
	mysqli_close($link);
	return true;
}
function queryLog($queryStr,$table_name="",$table_id="",$whereStr=""){//刪除、修改、新增資料用 $logStr 紀錄查詢用
	global $inputData,$site_kind,$record_id;	
	
	if ($whereStr==null)	$whereStr = " WHERE `id` in ($table_id) ";
	
	$logFlag = "0";
	if ($table_name!=null ) $logFlag = "1";
	
	$select = "SELECT * FROM `$table_name` $whereStr";
	
	if ( $logFlag==1 ) {
		$data = selectData($select);		//修改前數值
		foreach ($data as $val){
			$data_befor[$val['id']] = $val; 
			$idArray[$val['id']] = "";
		}
	}
	
	$bool = queryData($queryStr);
	
	if ( $logFlag==1 ) {
		$data = selectData($select);		//修改後數值
		foreach ($data as $val){
			$data_after[$val['id']] = $val;
			$idArray[$val['id']] = "";
		}
	}
	if ( $logFlag==1 ){
		$add_dt = date('Y-m-d H:i:s');
		//-------------主表
		$action     = $inputData["action"];
		$login_user = $inputData['login_user'];
		$page_name  = $inputData['site_page'];
		
		if ($record_id==null){
			$upDate = "INSERT INTO `all_update_record`( `operate_type`, `operate_user`, `page_name`, `action`, `add_dt`) VALUES ('$site_kind','$login_user','$page_name','$action','$add_dt')";
			$record_id = insertData($upDate);
		}
		
		$valStr = "";
		foreach ($idArray as $table_id=>$val){
			$befor = $data_befor[$table_id];
			$after = $data_after[$table_id];
			$befor_d = array();
			$after_d = array();
			foreach ($befor as $key=> $val){
				if ($befor[$key]!=$after[$key] ){
					$befor_d[$key] = $befor[$key];
					$after_d[$key] = $after[$key];
				}
			}
			
			$befor = json_encode($befor,1);
			$after = json_encode($after,1);
			$befor_d = json_encode($befor_d,1);
			$after_d = json_encode($after_d,1);
			
			$befor = intodbTrans($befor);		//編碼寫入
			$after = intodbTrans($after);
			$befor_d = intodbTrans($befor_d);
			$after_d = intodbTrans($after_d);
				
			$status = "1";
			if ( in_array($table_name, array('user_login_record','game_company')) )$status = "0";
			$valStr .= "( '$record_id','$table_name','$table_id','$befor','$after','$befor_d','$after_d','$status','$add_dt' ),";
		}
		$valStr = chop($valStr,",");
		
		$upDate="INSERT INTO `all_update_record_detail`(`record_id`, `table_name`, `table_id`, `data_befor`, `data_after`, `data_diff_befor`,`data_diff_after`, `status`,`add_dt`) VALUES $valStr";
		insertData($upDate);
	}
	return $bool;
}
function insertLog($queryStr,$table_name=""){//新增資料用,回傳id mysqli
	global $inputData,$site_kind,$record_id;
	$action     = $inputData["action"];
	$login_user = $inputData['login_user'];
	$page_name  = $inputData['site_page'];
	$add_dt = date('Y-m-d H:i:s');
	
	$table_id = insertData($queryStr);
	
	$select = "SELECT * FROM `$table_name` WHERE `id` = '$table_id'";
	$after = selectData($select)[0];		//修改後數值
	$after = json_encode($after,1);
	

	$after = intodbTrans($after);
	
	if ($record_id==null){
		$upDate = "INSERT INTO `all_update_record`( `operate_type`, `operate_user`, `page_name`, `action`, `add_dt`) VALUES ('$site_kind','$login_user','$page_name','$action','$add_dt')";
		$record_id = insertData($upDate);
	}
	
	$status = "0";
	if ( in_array($table_name, array('user','person_message','admin','agent','agent_bank','agent_trans_money','out_in_status')) )$status = "1";
	if ( $action==1016 && $table_name=="person_message_record")  $status = "0";
	$upDate="INSERT INTO `all_update_record_detail`(`record_id`, `table_name`, `table_id`, `data_after`, `data_diff_after`, `status`,`add_dt`) VALUES ( '$record_id','$table_name','$table_id','$after','$after','$status','$add_dt' )";
	insertData($upDate);
	
	return $table_id;
}


function insertData($queryStr){//新增資料用,回傳id mysqli
	global $dbIP,$dbUser,$dbPwd,$db,$logSQL;
	$link = mysqli_connect ( $dbIP, $dbUser, $dbPwd );

	mysqli_set_charset ( $link , 'utf8'  );

	$db_selected = mysqli_select_db ( $link , $db);
	mysqli_query ( $link , "SET NAMES 'UTF8'" );
	mysqli_query ( $link , "SET CHARACTER SET UTF8" );

	$res = mysqli_query ( $link ,$queryStr );
	if (mysqli_affected_rows($link)>0)	$logSQL[] = $queryStr;	//修改紀錄
	if($res==false){
		printErr( mysqli_error($link));
		mysqli_close( $link );
		return false;
	}
	$id = mysqli_insert_id( $link );
	mysqli_close( $link );
	return $id;
}

function sqlLog($logSQL){//log紀錄
	global $db,$host_name,$inputStr;
	
	//---不計入--------
// 	$noLOG[] = "log_action_recode";
	foreach ($logSQL as $key=> $queryStr){
		$noStr = "UPDATE `game_user` SET `money`=";
		$n = strpos($queryStr,$noStr);
		if ( is_numeric($n) ) unset($logSQL[$key]);
	}
	//---不計入---end--
	$path = '/var/log/money_api_log/'.date('Ymd').'_SQL_';	//log路径
	$myfile = fopen($path, "x");
	fclose($myfile);
	
	
	$obj = json_decode($inputStr,1);
	$login_user = $obj['login_user'];
	if ($login_user==null) $login_user = $obj['paras']['id'];		//後台登入
	if ($login_user==null) $login_user = $obj['paras']['a33333'];	//前台登入
	

	if (count($logSQL)>0){
		
		$logText = "\r\n\r\n";
		$logText .= 'time = '.date('Y-m-d H:i:s')."\r\n";
		$logText .= 'host_name = '.$host_name."\r\n";
		$logText .= 'site_code = '.$obj['site_domain']."\r\n";
		$logText .= 'login_user = '.$login_user."\r\n";
		$logText .= 'site_page = '.$obj['site_page']."\r\n";
		$logText .= 'json = '.$inputStr."\r\n";
		$logText .= 'db = '.$db."\r\n";
		
		foreach ($logSQL as $queryStr){
			$logText .= 'SQL = '.$queryStr."\r\n";
		}
		error_log($logText ,3,$path);
	}
	return;
}
function printErr($errStr){
	global $isDisplayErr;
	if($isDisplayErr==1){
		echo $errStr;
	}
}
function setReturnObj($code,$result){
	$returnObj=Array();
	$returnObj["errCode"]=$code;
	$returnObj["result"]=$result;
	return $returnObj;		
}
function removeBOM($str = '')
{
	if (substr($str, 0,3) == pack("CCC",0xef,0xbb,0xbf))
		$str = substr($str, 3);

	return $str;
}
function clean($string) {
	$string = str_replace(' ', '', $string); // Replaces all spaces with hyphens.
	// 	$string = preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.

	return $string; // Replaces multiple hyphens with single one.
}
function buildOrderNo($first=""){//唯一碼
	return $first.date('Ymd').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
}
function isUrlEncode($str){//判斷是不是為base64
	if ( urlencode(urldecode($str)) === $str){
		return 1;
	} else {
		return 0;
	}
}

function checkInt($int){//檢查int
	$int = (string)$int;
	$int2=(string)intval($int);
	if($int2===$int){//必需三個等於，二個等於不是我要的給果
		return 1;//沒有含其他的字元
	}else{
		return 0;//含有其他的字元
	}
}
function checkDouble($double){//檢查double
	$double = (string)$double;
	$int2=(string)doubleval($double);
	if($int2===$double){//必需三個等於，二個等於不是我要的給果
		return 1;//沒有含其他的字元
	}else{
		return 0;//含有其他的字元
	}
}
function checkFloat($float){//檢查float
	$float = (string)$float;		
	$int2=(string)floatval($float);
	if($int2===$float){//必需三個等於，二個等於不是我要的給果
		return 1;//沒有含其他的字元
	}else{
		return 0;//含有其他的字元
	}
}
function checkAlnum($str){//檢查字母或數字
	$result=ctype_alnum($str);
	if($result){
		return 1;//沒有含其他的字元
	}else{
		return 0;//含有其他的字元
	}
}
//---------日期格式檢查 迎合格式(2016-01-01 12:30:30 || 2016-01-01 || 2016/01/01 || 2016/01/01 12:30:30 || 2016-01-01 12:30)-----------
function checkDateFormat($input_date){
	//正則
	$reg_one = "/^(\d{4})-(\d{1,2})-(\d{1,2}) ([01][0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])$/";
	$reg_two = "/^(\d{4})-(\d{1,2})-(\d{1,2})$/";
	$reg_three = "/^(\d{4})\/(\d{1,2})\/(\d{1,2})$/";
	$reg_four = "/^(\d{4})\/(\d{1,2})\/(\d{1,2})$/ ([01][0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])$/";
	$reg_five = "/^(\d{4})-(\d{1,2})-(\d{1,2}) ([01][0-9]|2[0-3]):([0-5][0-9])$/";

	//檢查格式後用php function做日期檢查
	if (preg_match($reg_one, $input_date, $matches) || preg_match($reg_two, $input_date, $matches) || preg_match($reg_three, $input_date, $matches) || preg_match($reg_four, $input_date, $matches) || preg_match($reg_five, $input_date, $matches))
	{
		if (checkdate($matches[2], $matches[3], $matches[1])) {
			return 1;
		}
	}
	return 0;
}

//------檢查中文字或符號 1帶有中文字或符號 0沒有------
function checkString($strings){
	$standard_E = "/^([0-9A-Za-z]+)$/";
	if(preg_match($standard_E, $strings, $hereArray)) {
		return 1;
	} else {
		return 0;
	}
}


function intodbTrans($str){//轉換後進資料庫
	return urlencode($str);
}

function EncodeId($id){//編碼id
	global $global;
	//return urlencode(encode($global["aes_id_key"], $id));
	//return rawurlencode(encode($global["aes_id_key"], $id));
	return base64_encode(encode($global["aes_id_key"], $id));
}
function DecodeId($id){//解碼id
	global $global;
	//return decode($global["aes_id_key"],urldecode($id));
	//return decode($global["aes_id_key"],rawurlencode($id));
	return decode($global["aes_id_key"],base64_decode($id));
}


function encodeAES($key,$data){//AES加密
	$encodObj=new MagicCrypt($key);
	return $encodObj->encrypt($data);
}
function decodeAES($key,$data){//AES解密
	$encodObj=new MagicCrypt($key);
	return $encodObj->decrypt($data);
}
function encode($key, $data){//自已的加密
	$rand=rand();
	$data=$rand."_".$data;
	return encodeAES($key, $data);
}
function decode($key, $data){//自已的解密
	$str=decodeAES($key, $data);
	$array=explode("_", $str);
	return $array[1];
}

function encodeIdFor($res, $key){	//轉碼id的迴圈
	
	if(array_key_exists($key,$res[0])){
		for($i=0;$i<count($res);$i++){
			$res[$i][$key] = EncodeId($res[$i][$key]);
		}
	}
	
	return $res;
}

function trans($dataArray){ //轉換語系
	global $global;
	$site_domain = $dataArray["site_domain"];
	$result = $dataArray["result"];
	$queryStr="SELECT `lang` FROM `front_site_url` WHERE `front_domain`= '$site_domain'";
	$lang_result = selectData($queryStr)[0]["lang"];
	
	
	$data = array();
	if($lang_result == "TW"){ //轉繁體
		$data = ch2tw($result);
	}else{
		$data = $result;
	}
	$data[$global["errCode"]] = "1000";    //錯誤代碼 1000成功，2000格式錯誤，9999失敗
	
	return $data;
}

function ch2tw($data){
	foreach ($data as $key=>$val){
		if(is_array($val)){
			$data[$key] = ch2tw($val);
		}else{
			$data[$key] = (gb2312_big5($data[$key]) == "")?($data[$key]):(gb2312_big5($data[$key]));
		}
	}
	return $data;
}

// function is_utf8
function is_utf8($string) {
	return preg_match('%^(?:
[\x09\x0A\x0D\x20-\x7E] # ASCII
| [\xC2-\xDF][\x80-\xBF] # non-overlong 2-byte
| \xE0[\xA0-\xBF][\x80-\xBF] # excluding overlongs
| [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2} # straight 3-byte
| \xED[\x80-\x9F][\x80-\xBF] # excluding surrogates
| \xF0[\x90-\xBF][\x80-\xBF]{2} # planes 1-3
| [\xF1-\xF3][\x80-\xBF]{3} # planes 4-15
| \xF4[\x80-\x8F][\x80-\xBF]{2} # plane 16
)*$%xs', $string);
} 

//簡繁轉換

function big5_gb2312($in) { //轉簡體

	$in = iconv('UTF-8', 'BIG5', $in);

	$in = iconv('BIG5', 'GB2312', $in);

	$out = iconv('GB2312', 'UTF-8', $in);

	return $out;

}



function gb2312_big5($in) { //轉繁體

	$in = iconv('UTF-8', 'GB2312', $in);

	$in = iconv('GB2312', 'BIG5', $in);

	$out = iconv('BIG5', 'UTF-8', $in);

	return $out;

}

?>