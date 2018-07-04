<?php
class SelectClass
{
	function user_login($data){	//會員登入
		global $dbUser,$dbPwd,$db;
		
		$username = $data["username"];
		$password = $data["password"];
						
		$queryStr="SELECT * FROM `admin` WHERE `user`= '$username' AND `pwd` = '$password'";
		$result=selectData($queryStr);
		
		if(count($result)>0){				 //有資料
			$data=array();
			$data["errCode"] = "0"; 		 //錯誤代碼 0成功，1以上失敗
			$data["total"] = count($result); //資料總比數
			$data["result"] = array();
			$data["result"] = $result;		 //回傳的資料
			
		}else{								 //無資料
			$data=array();
			$data["id"] = $agent_id;
			$data["errCode"] = "1"; 		 //錯誤代碼 0成功，1以上失敗		
		}
		
		return $data;	
	}
	function select_user($data){	//會員
		global $dbUser,$dbPwd,$db;
		
		$queryStr="SELECT * FROM `user` WHERE 1";
		$result=selectData($queryStr);
		
		if(count($result)>0){				 //有資料
						
			$data=array();
			$data["errCode"] = "0"; 		 //錯誤代碼 0成功，1以上失敗
			$data["total"] = count($result); //資料總比數
			$data["result"] = array();
			$data["result"] = $result;		 //回傳的資料
			
		}else{								 //無資料
			$data=array();		
			
			$data["id"] = $agent_id;
			$data["errCode"] = "1"; 		 //錯誤代碼 0成功，1以上失敗		
		}
		return $data;	
	}
	function select_one_user($data){
		global $dbUser,$dbPwd,$db;
		
		$agent_id = $data["user_id"];
		
		$queryStr="SELECT * FROM `user` WHERE id='$agent_id'";
		$result=selectData($queryStr);
		
		if(count($result)>0){				 //有資料
						
			$data=array();
			$data["errCode"] = "0"; 		 //錯誤代碼 0成功，1以上失敗
			$data["total"] = count($result); //資料總比數
			$data["result"] = array();
			$data["result"] = $result;		 //回傳的資料
			
		}else{								 //無資料
			$data=array();		
			$data["id"] = $agent_id;
			$data["errCode"] = "1"; 		 //錯誤代碼 0成功，1以上失敗		
		}
		
		return $data;
	}
	
	function get_page_text($data){	//取得網頁欄位標題
		global $dbUser,$dbPwd,$db,$main_db;
		
		$url_name = $data["url_name"];
		$lang_code = $data["lang_code"];
		
		$db=$main_db;	//覆蓋使用總後台資料庫

		$queryStr="SELECT * FROM `system_feature` LEFT JOIN `page_text_lang` ON `system_feature`.`id` = `page_text_lang`.`system_feature_id` WHERE `system_feature`.`file_name`='$url_name' AND `page_text_lang`.`lang_id`='$lang_code'";
		
		$result=selectData($queryStr);
		if(count($result)>0){				 //有資料
				
			$data=array();
			$data["errCode"] = "0"; 		 //錯誤代碼 0成功，1以上失敗
			$data["result"] = $result;		 //回傳的資料
		}else{								 //無資料
			$data=array();
			$data["errCode"] = "1"; 		 //錯誤代碼 0成功，1以上失敗
		}
		return $data;
	}
	
}
?>