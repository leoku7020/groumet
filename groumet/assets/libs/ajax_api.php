<?php
class AjaxApi{
	function updateKeyword($dataArray){
		global $global,$apiUrl;
				
		$data=array();
		$data["action"]="1006";
		$data["paras"]=$dataArray;
		$send=new SendDataApi();
		$send->urlTxt=$apiUrl;
		$result=$send->operateApi($data);
		$resultObj=json_decode($result,1);
		
		$data=array();
		if($resultObj[$global["errCode"]]=="1000"){
			$data["errCode"] = "0"; 		 //錯誤代碼 0成功，1以上失敗
		}else{
			$data["errCode"] = $resultObj[$global["errCode"]]; 		 //錯誤代碼 0成功，1以上失敗
		}
		return $data;
	}
	function updateArticle($dataArray){
		global $global,$apiUrl;
		
		$data=array();
		$data["action"]="1003";
		$data["paras"]=$dataArray;
		$send=new SendDataApi();
		$send->urlTxt=$apiUrl;
		$result=$send->operateApi($data);
		$resultObj=json_decode($result,1);
		
		$data=array();
		if($resultObj[$global["errCode"]]=="1000"){
			$data["errCode"] = "0"; 		 //錯誤代碼 0成功，1以上失敗
		}else{
			$data["errCode"] = $resultObj[$global["errCode"]]; 		 //錯誤代碼 0成功，1以上失敗
		}
		return $data;
	}
	
	function updateSiteStyle($dataArray){
		global $global,$apiUrl;
	
		$data=array();
		$data["action"]="2006";
		$data["paras"]=$dataArray;
		$send=new SendDataApi();
		$send->urlTxt=$apiUrl;
		$result=$send->operateApi($data);
		$resultObj=json_decode($result,1);
	
		$data=array();
		if($resultObj[$global["errCode"]]=="1000"){
			$data["errCode"] = "0"; 		 //錯誤代碼 0成功，1以上失敗
			$data["id"] = (isset($resultObj["id"]))?(EncodeId($resultObj["id"])):($dataArray["id"]);
		}else{
			$data["errCode"] = $resultObj[$global["errCode"]]; 		 //錯誤代碼 0成功，1以上失敗
		}
		return $data;
	
	
	}
	function updateBannergroup($dataArray){
		global $global,$apiUrl;
	
		$data=array();
		$data["action"]="2008";
		$data["paras"]=$dataArray;
		$send=new SendDataApi();
		$send->urlTxt=$apiUrl;
		$result=$send->operateApi($data);
		$resultObj=json_decode($result,1);
	
		$data=array();
		if($resultObj[$global["errCode"]]=="1000"){
			$data["errCode"] = "0"; 		 //錯誤代碼 0成功，1以上失敗
			$data["id"] = (isset($resultObj["id"]))?(EncodeId($resultObj["id"])):($dataArray["id"]);
		}else{
			$data["errCode"] = $resultObj[$global["errCode"]]; 		 //錯誤代碼 0成功，1以上失敗
		}
		return $data;
	
	
	}
	function updateBanner($dataArray){
		global $global,$apiUrl;
	
		$data=array();
		$data["action"]="2010";
		$data["paras"]=$dataArray;
		$send=new SendDataApi();
		$send->urlTxt=$apiUrl;
		$result=$send->operateApi($data);
		$resultObj=json_decode($result,1);
	
		$data=array();
		if($resultObj[$global["errCode"]]=="1000"){
			$data["errCode"] = "0"; 		 //錯誤代碼 0成功，1以上失敗
			$data["id"] = (isset($resultObj["id"]))?(EncodeId($resultObj["id"])):($dataArray["id"]);
		}else{
			$data["errCode"] = $resultObj[$global["errCode"]]; 		 //錯誤代碼 0成功，1以上失敗
		}
		return $data;
	
	
	}
	function updateActivity($dataArray){
		global $global,$apiUrl;
	
		$data=array();
		$data["action"]="2012";
		$data["paras"]=$dataArray;
		$send=new SendDataApi();
		$send->urlTxt=$apiUrl;
		$result=$send->operateApi($data);
		$resultObj=json_decode($result,1);
	
		$data=array();
		if($resultObj[$global["errCode"]]=="1000"){
			$data["errCode"] = "0"; 		 //錯誤代碼 0成功，1以上失敗
			$data["id"] = (isset($resultObj["id"]))?(EncodeId($resultObj["id"])):($dataArray["id"]);
		}else{
			$data["errCode"] = $resultObj[$global["errCode"]]; 		 //錯誤代碼 0成功，1以上失敗
		}
		return $data;
	
	
	}
}



?>