<?php
class QueryClass
{
	public $urlTxt = "http://dsl.hq-game.com/libs_Test/complete/api/index.php";//網址

	function insertUser($dataArray){			
		/*
		$today = date("Y-m-d H:i:s");
		$user=$dataArray["user"];
		$pwd=$dataArray["pwd"];
		$name=$dataArray["name"];
		$email=$dataArray["email"];
		$phone=$dataArray["phone"];
		$qq=$dataArray["qq"];
		$regip=getClientIP();
		
		$queryStr="SELECT * FROM `user` where `user`='$user'";
		$result=selectData($queryStr);
		
		if(count($result)!=0){//重覆帳號
			$data["errCode"]=1;	
			return $data;	 		
		}
		
		$queryStr="INSERT INTO `user`(`id`, `agent_id`, `level_id`, `user`, `pwd`, `name`, `email`, `phone`, `qq`, `sex`, `status`, `register_ip`, `login_ip`, `login_dt`, `add_dt`, `mod_dt`) VALUES ('','0','1','$user','$pwd','$name','$email','$phone','$qq','0','1','$regip','','$today','$today','$today')";
		$result=queryData($queryStr);
		$data=array();
		$data["errCode"] = "0"; 		 //錯誤代碼 0成功，1以上失敗
		$data["total"] = count($result); //資料總比數
		$data["result"] = array();
		$data["result"] = $result;
		return $data;	
		*/
	}

	
	
}
?>