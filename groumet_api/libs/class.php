<?php
class SendDataApi
{
	public $urlTxt = "http://kev.hq-game.com/site/api/index.php";//傳送網址 預設而已無所謂
	public $productKey="123";//API的使用KEY
	public $lineMax = "100"; //併發限制
	
	function operateApi($dataArray)//操作用的api
	{
		//$dataArray["site_domain"] = $_SERVER['HTTP_HOST'];
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
		curl_setopt($ch, CURLOPT_TIMEOUT, 600);
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
	
	function operateApiMulti($dataArrayMulti)//操作用的api多併發
	{
		foreach ( $dataArrayMulti as $key=>$dataArray){
			$dataArray["site_domain"]= $_SERVER['HTTP_HOST'];
			$dataArray["key"]=$this->keyEncode($dataArray);
			$sendArray[$key]=json_encode($dataArray);
		}
		$result = $this->sendDataMulti($sendArray, $this->urlTxt);
		return $result;
	}
	
	function sendDataMulti($postArray, $urlTxt)//併發寄送的函式
	{
		$lineMax = $this->lineMax;
	
		if (count($postArray)>$lineMax){	//超過併發限制
			// $postArray 分割
			// 			$postArray =
			$i = '0' ;
			$j = '0' ;
	
			foreach ($postArray as $key=> $post ){		//分割$postArray
				if ( $j>=$lineMax ){
					$i++;
					$j = '0' ;
				}else{
					$j++;
				}
				$postArrayMulti[$i][$key] = $post;
			}
	
			$result = array();
			foreach ($postArrayMulti as $postArray2){
				foreach ($postArray2 as $key=> $post ){
					$ch[$key] = curl_init();
				}
				foreach ($postArray2 as $key=> $post ){
					curl_setopt( $ch[$key] , CURLOPT_URL, $urlTxt );
					curl_setopt( $ch[$key] , CURLOPT_POST, true);
					curl_setopt( $ch[$key] , CURLOPT_POSTFIELDS, $post);
					curl_setopt( $ch[$key] , CURLOPT_RETURNTRANSFER, 1);
					curl_setopt( $ch[$key] , CURLOPT_TIMEOUT, 300);
				}
	
	
				$mh = curl_multi_init();
				foreach ($postArray2 as $key=> $post ){
					curl_multi_add_handle($mh, $ch[$key] );
				}
				do {
					curl_multi_exec($mh,$flag);
				} while ($flag > 0);
				foreach ($postArray2 as $key=> $post ){
					$result[$key]=curl_multi_getcontent($ch[$key]);
					curl_multi_remove_handle($mh, $ch[$key] );
				}
				curl_multi_close($mh);
			}
		}else{
			$result = array();
			foreach ($postArray as $key=> $post ){
				$ch[$key] = curl_init();
			}
			foreach ($postArray as $key=> $post ){
				curl_setopt( $ch[$key] , CURLOPT_URL, $urlTxt );
				curl_setopt( $ch[$key] , CURLOPT_POST, true);
				curl_setopt( $ch[$key] , CURLOPT_POSTFIELDS, $post);
				curl_setopt( $ch[$key] , CURLOPT_RETURNTRANSFER, 1);
				curl_setopt( $ch[$key] , CURLOPT_TIMEOUT, 300);
			}
	
	
			$mh = curl_multi_init();
			foreach ($postArray as $key=> $post ){
				curl_multi_add_handle($mh, $ch[$key] );
			}
			do {
				curl_multi_exec($mh,$flag);
			} while ($flag > 0);
			foreach ($postArray as $key=> $post ){
				$result[$key]=curl_multi_getcontent($ch[$key]);
				curl_multi_remove_handle($mh, $ch[$key] );
			}
			curl_multi_close($mh);
	
	
		}
	
	
		return $result;
	}
}
?>