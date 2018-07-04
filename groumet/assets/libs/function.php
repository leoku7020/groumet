<?php
include_once 'MagicCrypt.php';
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
	$link = mysql_connect ( $dbIP, $dbUser, $dbPwd);
	
	mysql_set_charset ( 'utf8', $link );
	$db_selected = mysql_select_db ( $db, $link );
	mysql_query ( "SET NAMES 'UTF8'" );
	mysql_query ( "SET CHARACTER SET UTF8" );
	$res = mysql_query ( $queryStr);
	if($res==false){
		printErr(mysql_error());
		mysql_close();
		return false;
	}
	for ($i = 0; $i < mysql_num_rows($res); $i ++) {
		$r = mysql_fetch_assoc($res);
		$output[] = $r;
	}
	mysql_close();
	
	//添加檢查欄位為base64的動作
	if(is_array($output) && count($output)>0){//檢查是否為陣列，以及是否有資料
		$output2=(Array)$output;
		$transField=array();
		foreach ($output2[0] as $key=>$val){//取得轉換過的欄位
			if(isUrlEncode($val)==1){//是轉換過的，要轉碼
				array_push($transField,$key);
			}			
		}
		if(count($transField)>0){//反轉
			for ($i=0;$i<count($output2);$i++){
				for ($j=0;$j<count($transField);$j++){
					$output2[$i][$transField[$j]]=urldecode($output2[$i][$transField[$j]]);
				}
			}
		}
	}
	return $output2;	
}
function queryData($queryStr){//刪除、修改、新增資料用
	global $dbIP,$dbUser,$dbPwd,$db;
	$link = mysql_connect ( $dbIP, $dbUser, $dbPwd);

	mysql_set_charset ( 'utf8', $link );
	
	$db_selected = mysql_select_db ( $db, $link );
	mysql_query ( "SET NAMES 'UTF8'" );
	mysql_query ( "SET CHARACTER SET UTF8" );
	
	$res = mysql_query ( $queryStr);
	if($res==false){
		printErr(mysql_error());
		mysql_close();
		return false;
	}
	mysql_close();
	return true;
}
function insertData($queryStr){//新增資料用,回傳id
	global $dbIP,$dbUser,$dbPwd,$db;
	$link = mysql_connect ( $dbIP, $dbUser, $dbPwd);

	mysql_set_charset ( 'utf8', $link );

	$db_selected = mysql_select_db ( $db, $link );
	mysql_query ( "SET NAMES 'UTF8'" );
	mysql_query ( "SET CHARACTER SET UTF8" );

	$res = mysql_query ( $queryStr);
	if($res==false){
		printErr(mysql_error());
		mysql_close();
		return false;
	}
	$id=mysql_insert_id();
	mysql_close();
	return $id;
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

function checkDateFormat($input_date){
		//正則
		$reg_one = "/^(\d{4})\/(\d{1,2})\/(\d{1,2}) ([01][0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])$/";
		$reg_two = "/^(\d{4})\/(\d{1,2})\/(\d{1,2})$/";
	
		//檢查格式後用php function做日期檢查
		if (preg_match($reg_one, $input_date, $matches) || preg_match($reg_two, $input_date, $matches))
		{
			if (checkdate($matches[2], $matches[3], $matches[1])) {
				return 1;
			}
		}
		return 0;
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
	//return urldecode(decode($global["aes_id_key"], $id));
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

//---------文章類別排序------------
function sort_type($data){
	$ck = 0;
	foreach ($data as $key => $val){
		if($val["parent_id"] != "0")break;
		$ck++;
	}
	if($ck == count($data)) return $data;
	foreach($data as $key => $val){
		if($data[$key]["parent_id"] != "0" && count($data[$key]["child"]) == $data[$key]["child_count"] ){
			$data[$data[$key]["parent_id"]]["child_arr"][count($data[$data[$key]["parent_id"]]["child_arr"])] = $data[$key];
			$data[$data[$key]["parent_id"]]["child_count"] += 1+$data[$key]["child_count"]*1;
			unset($data[$key]);
		}
	}
	return sort_type($data);
}

function show_type($data,$now,$end,$lv){
	global $type_sel_arr;
	if($now == $end) return "";
	$sel_str = "";
	if(in_array(DecodeId($data[$now]["id"]), $type_sel_arr))$sel_str = "selected";
	$html_str = "<option value='".$data[$now]["id"]."' title='".$data[$now]["name"]."' $sel_str>".$lv."└─".$data[$now]["name"]."</option>";
	if(count($data[$now]["child_arr"]) != 0){
		$child_str = show_type($data[$now]["child_arr"],0,count($data[$now]["child_arr"]),$lv."　　");
		if($child_str != "") $html_str .= $child_str;
	}
	return $html_str.show_type($data,$now+1,$end,$lv);
}




//---------轉為金額專用數字格式----------
function trimzero($num){
	if ($num < 0 && $num > -1) $f = "-";
	$num = round($num, 4);							//四捨五入至小數第二位
	$str = sprintf("%s", $num);						//數字轉字串
	list($int, $dec) = explode(".", $str);			//拆解字串格式為：整數.小數
	$int = number_format($int);						//將整數部分每 3 位加逗號
	if ($dec == "") return $int;					//若無小數則返回整數
	return $f.trim($int.".".$dec);					//重組格式後返回
}

//---------moon哥加密方法-------------
function encrypt($data) {//加密函數
	$key = "123145";
	$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
	$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
	
	if(32 !== strlen($key)) $key = hash('SHA256', $key, true);
	if(16 !== strlen($iv)) $iv = hash('MD5', $iv, true);
	$padding = 16 - (strlen($data) % 16);
	$data .= str_repeat(chr($padding), $padding);
	return base64_encode(trim(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $data, MCRYPT_MODE_ECB, $iv)));
}




?>