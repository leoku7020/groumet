<?php
class WebClass		
{
	function GetCity($paras){		//取得地區資料	
		global $global;
		
		//取得城市
		$queryStr = "SELECT * FROM `area_mains` where 1 ";
		$city = selectData($queryStr);
		
		foreach ($city as $key =>$val){
			$id = $val["id"];
			$queryStr = " SELECT * FROM `area_subs` where `master_id` = '$id' ";
			$country = selectData($queryStr);
			$city[$key]["country"] = $country;
		}
		
		$data=array();
		if(count($city) > 0){
			$data[$global["errCode"]] = "1000";		 //回傳的資料
			$data["result"] = $city;		 //回傳的資料
		}else{
			$data[$global["errCode"]] = "9999";		 //回傳的資料
		}		
		return $data;
	}
	
	function GetFoodType($paras){	//取得分類
		global $global;
		
		//取得食物分類
		$queryStr = "SELECT * FROM `food_mains` where 1 ";
		$food_type = selectData($queryStr);
		
		foreach ($food_type as $key =>$val){
			$id = $val["id"];
			$queryStr = "  SELECT * FROM `food_subs` where `master_id` = '$id' ";
			$food_name_type = selectData($queryStr);
			$food_type[$key]["food_name_type"] = $food_name_type;
		}
		
		$data=array();
		if(count($food_type) > 0){
			$data[$global["errCode"]] = "1000";		 //回傳的資料
			$data["result"] = $food_type;		 //回傳的資料
		}else{
			$data[$global["errCode"]] = "9999";		 //回傳的資料
		}
		return $data;
	}
	
	function GetShop($paras){ //取得店家
		
		global $global;
		$sub_area = $paras["country"];
		$food_type = $paras["food_type"];
		$city = $paras["city"];
		$size = $paras["page_size"];
		$page = $paras["page_num"];
		
		$where_str = "";
		if($city != null) $where_str .= " and `main_area` in ($city) ";
		if($sub_area != null) $where_str .= " and `sub_area` in ($sub_area) ";
		if($food_type != null) $where_str .= " and `main_food` in($food_type) ";
		//取得總筆數
		$queryStr = " SELECT count(*) as total FROM `shop_mains` where  1 $where_str";
		$total = selectData($queryStr)[0]["total"];
		
		//頁碼計算
		$page_sum = ($page-1)*$size;	//分頁_頁數
		$limitStr = "limit 20";
		if($page != "" && $size != "") $limitStr = "limit $page_sum,$size";
		
		//取得店家
		$queryStr = "SELECT `shop_mains`.`id`,`shop_mains`.shop_name,`shop_mains`.`main_title`
					,`shop_mains`.`shop_text`,`shop_mains`.`tel`,`shop_mains`.`address`,`shop_mains`.`station`,`shop_mains`.`holiday`,`shop_mains`.`open_time`
					,`shop_mains`.`shop_info`
					,(SELECT `food_name` FROM `food_subs` where `id` = `shop_mains`.`main_food`) as food_type
					,(SELECT `food_name` FROM `food_subs` where `id` = `shop_foods`.`food_id`) as food_tag
					,`shop_photos`.`photo_num`
					FROM `shop_mains` 
					LEFT JOIN `shop_foods` 
					ON `shop_mains`.id=`shop_foods`.`shop_table_id`
					LEFT JOIN `shop_photos`
					ON `shop_mains`.id = `shop_photos`.`shop_table_id`
					where 1 $where_str
					$limitStr ";
		$shop = selectData($queryStr);
		
		$data=array();
		if(count($shop) > 0){
			$data[$global["errCode"]] = "1000";		 //回傳的資料
			$data["total"] = $total;
			$data["result"] = $shop;		 //回傳的資料
		}else{
			$data[$global["errCode"]] = "9999";		 //回傳的資料
		}
		return $data;
		
	}
}
?>
