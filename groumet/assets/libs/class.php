<?php
class SendDataApi
{
	public $urlTxt = "http://money_api2.perfect-ace.com/api/index.php";//傳送網址 預設而已無所謂
	public $productKey="123";//API的使用KEY

	function operateApi($dataArray)//操作用的api
	{
		global $global;
		$dataArray["site_domain"]=$_SERVER['HTTP_HOST'];
// 		$dataArray["site_domain"]="one.yaoubet.com";
		$dataArray["key"]=$this->keyEncode($dataArray);
		$sendStr=json_encode($dataArray);
		$result = $this->sendData($sendStr, $this->urlTxt);
		return $result;
	}

	function sendData($post, $urlTxt)//寄送的函式
	{
		$url = $urlTxt;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		$result = curl_exec($ch);
		curl_close($ch);
		return $result;
	}
	function keyEncode($array){//取驗證碼的方式
		$str="";
		foreach ($array["paras"] as $val){
			$str.=$val;
		}
		$str.=$this->productKey;
		return md5($str);
	}
}
?>