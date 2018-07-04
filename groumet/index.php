<?php
session_start();
ini_set('display_errors',0);
header("Content-Type:text/html; charset=utf-8");

include "assets/libs/class.php";
include "assets/libs/function.php";
include "assets/libs/config.php";

$send = new SendDataApi ();
$send->urlTxt = $apiUrl; // API網址

//取得地區
$data = array();
$paras = array();
$data ["action"] = "1001"; // API代碼
$data ["paras"] = $paras; // 欲傳送的資料
$result = $send->operateAPI($data); // 送出
$resultObj_city = json_decode($result, 1);

//取得分類
$data = array();
$paras = array();
$data ["action"] = "1002"; // API代碼
$data ["paras"] = $paras; // 欲傳送的資料
$result = $send->operateAPI($data); // 送出
$resultObj_type = json_decode($result, 1);

$city_text = array(); //城市名稱id->text
$country_text = array(); //地區名稱id -> text
$food_type_text = array(); //食物小類別名稱id -> text

foreach ($resultObj_city["result"] as $key =>$val){ //城市
	$city_text[$val["id"]] = $val["area_name"];
	$country = $val["country"];
	foreach ($country as $c_key =>$c_val){ //地區
		$country_text[$c_val["id"]]["text"] = $c_val["area_name"];
		$country_text[$c_val["id"]]["f_id"] = $val["id"];
		$country_text[$c_val["id"]]["f_text"] = $val["area_name"];
	}
}

foreach ($resultObj_type["result"] as $key =>$val){ //食物大類別
	$food_type = $val["food_name_type"];
	foreach ($food_type as $c_key =>$c_val){ //食物小類別
		$food_type_text[$c_val["id"]]["text"] = $c_val["food_name"];
		$food_type_text[$c_val["id"]]["f_id"] = $val["food_name"];
	}
}

if($_POST["country"] != null || $_POST["food_type"]!= null || $_GET["city"] != null){
	/******************分頁功能↓***********************/
	if ($_POST["page_size"] == ""){
		$per = 20;//每頁顯示項目數量
	} else {
		$per = $_POST["page_size"];
	}
	if ($_POST["page_count"] == ""){			//假如$_GET["page"]未設置
		$page = 1; //則在此設定起始頁數
	}else{
		$page = intval($_POST["page_count"]);	//確認頁數只能夠是數值資料
	}
	/******************分頁功能↑***********************/
	if($_POST["country"] != null || $_POST["food_type"] != null ){
		unset($_GET);
	}
	
	//取得店家
	$data = array();
	$paras = array();
	$data ["action"] = "1003"; // API代碼
	$paras["city"] = $_GET["city"];
	$paras["country"] = $_POST["country"];
	$paras["food_type"] = $_POST["food_type"];
	$paras["page_size"] = $per;
	$paras["page_num"] = $page;
	$data ["paras"] = $paras; // 欲傳送的資料
	$result = $send->operateAPI($data); // 送出
	$resultObj_shop = json_decode($result, 1);
	/*******************分頁功能↓**********************/
	$data_nums = $resultObj_shop["total"];	 //統計總比數
	$pages = ceil($data_nums/$per); 					 //取得應該總共有幾頁
	/*******************分頁功能↑**********************/
	
	//標題顯示處理
	$title = "";
	$city = "";
	$this_country = explode(",",$_POST["country"]);
	$this_food = explode(",",$_POST["food_type"]);
	if($_POST != null){
		if(count($this_country) == 1 && count($this_food)== 1){
			if(isset($_GET["city"])){
				$title = $city_text[$_GET["city"]];
			}else{
				$city = $country_text[$_POST["country"]]["f_text"];
				$country = $country_text[$_POST["country"]]["text"];
				$food = $food_type_text[$_POST["food_type"]]["f_id"];
				$food_type = $food_type_text[$_POST["food_type"]]["text"];
				$title = "$city/$country";
				if($food != null) $title .= "/$food/$food_type";
			}
			
			
		}else{
			$title = "美食查詢結果";
		}
	}
	if(isset($_GET["city"])){
		$title = $city_text[$_GET["city"]];
	}
	
}


?>
<!DOCTYPE html>
<html lang="zh-tw">
    <head>
        <meta charset="UTF-8">
        <meta name="format-detection" content="telephone=no,address=no,email=no">
        <meta name="viewport" content="width=device-width,initial-scale=1.0">
        <link rel="stylesheet" href="css/common.css">
        <link rel="stylesheet" href="css/search.css">
        <title>美食查詢-<?=$title ?>-</title>
    </head>
    <body>
        <header>
            <h1>
            	<?php 
            		echo '<span>'.$title.'</span>';
            	?>
            </h1>
            <div class="headerLogo">
                <a href=""><img src="img/logo.png" alt="LOGO"></a>
            </div>
        </header>
        <div class="f-breadCrumbs u-container">
            <ul>
            	<?php 
            		if($_POST != null || isset($_GET["city"])){
						if(count($this_country) == 1 || isset($_GET["city"])){
							if(isset($_GET["city"])){
								$city = $city_text[$_GET["city"]];
								$city_id = $_GET["city"];
							}else{
								$city = $country_text[$_POST["country"]]["f_text"];
								$city_id = $country_text[$_POST["country"]]["f_id"];
							}
							echo '<li><a href="#">首頁</a></li>
					                <li>></li>
					                <li><a href="index.php?city='.$city_id.'">'.$city.'</a></li>
					                <li>></li>
					                <li><span>查詢結果</span></li>';
						}else{
							echo '<li><a href="#">首頁</a></li>
					                <li>></li>
					                <li><span>查詢結果</span></li>';
						}
					}else{
						echo '<li><a href="index.php">首頁</a></li>';
					}
            	?>
            </ul>
        </div>
        <div class="f-mainContents u-container">
            <div class="mainWrap">
                <h2><?=$title?></h2>
                <div class="m-pager">
                    <ul>
                    <?php
                    	if($page > 1) echo '<li><a href="#">＜</a></li>';
                    	for($i=1;$i<=$pages;$i++){
							if($page == $i){
								echo '<li><span>'.$i.'</span></li>';
							}else{
								echo '<li><a href="#">'.$i.'</a></li>';
							}
						}
						if($pages > 1 && $page < $pages) echo '<li><a href="#">＞</a></li>';
                    ?>
                    </ul>
                </div>
                <div class="m-shopList">
                    <ul>
                    	<?php 
                    		$cookie_like = explode(",", $_COOKIE["like"]);
                    		foreach ($resultObj_shop["result"] as $key =>$val){
								$like = (in_array($val["id"], $cookie_like))?("is-active"):("");
								echo '<li class="shopData">
			                            <div class="shopHead">
			                                <a href="">'.$val["shop_name"].'</a>
			                            </div>
			                            <div class="shopDetail">
			                                <div class="photo">
			                                    <img src="img/shop/'.$val["photo_num"].'.png" alt="">
			                                </div>
			                                <div class="info">
			                                    <div class="shopTitle">
			                                        <p>'.$val["shop_text"].'</p>
			                                    </div>
			                                    <table>
			                                        <tr>
			                                            <th>分類</th>
			                                            <td>'.$val["food_type"].'</td>
			                                        </tr>
			                                        <tr>
			                                            <th>標籤</th>
			                                            <td>
			                                                <ul class="foodTags">
			                                                    <li>'.$val["food_tag"].'</li>
			                                                </ul>
			                                            </td>
			                                        </tr>
			                                        <tr>
			                                            <th>營業時間</th>
			                                            <td>'.$val["open_time"].'</td>
			                                        </tr>
			                                        <tr>
			                                            <th>地址</th>
			                                            <td>'.$val["address"].'</td>
			                                        </tr>
			                                    </table>
			                                    <div class="linkBtn">
			                                        <button id="'.$val["id"].'" class="like u-hoverOpacity '.$like.'">LIKE</button>
			                                    </div>
			                                </div>
			                            </div>
			                        </li>';
							}
                    	?>
                    </ul>
                </div>
                <div class="m-pager">
                    <ul>
                    <?php 
                    	for($i=1;$i<=$pages;$i++){
							if($page == $i){
								echo '<li><span>'.$i.'</span></li>';
							}else{
								echo '<li><a href="">'.$i.'</a></li>';
							}
						}
                    ?>
                    </ul>
                </div>
            </div>
            <div class="rightWrap">
           	 <form id="sc_form" name="sc_form" method="post">
                <div class="r-searchMenu">
                    <p class="searchTitle"><span>查詢</span></p>
                    <div class="searchArea">
                        <p class="typeTitle">地點</p>
                        <ul class="main">
                        	<?php 
                        		foreach ($resultObj_city["result"] as $key =>$val){
									echo '<li class="m-list">
			                                <p class="mainList"><span>'.$val["area_name"].'</span></p>
			                                <ul class="sub">
			                                    <li>
			                                        <input type="checkbox" id="s-area0" class="all_area">
			                                        <label for="s-area0" >全'.$val["area_name"].'</label>
			                                    </li>';
											$country = $val["country"];
											foreach ($country as $c_key => $c_val){
												$check = (in_array($c_val["id"], $this_country))?("checked"):("");
												echo '<li>
				                                        <input type="checkbox" id="s-area1" '.$check.'>
				                                        <label for="s-area1">'.$c_val["area_name"].'</label>
														<input type="hidden" class="code" value="'.$c_val["id"].'">
				                                    </li>';
											}
											echo '</ul>
			                          		  </li>';
								}
                        	?>
                        </ul>
                        <input type="hidden" class="country_data" name="country" value="">
                    </div>
                    <div class="searchFood">
                        <p class="typeTitle">分類</p>
                        <ul class="main">
                        	<?php 
                        		foreach ($resultObj_type["result"] as $key =>$val){
									echo '<li class="m-list">
			                                <p class="mainList"><span>'.$val["food_name"].'</span></p>
			                                <ul class="sub">			                                    
												<li>
			                                        <input type="checkbox" id="s-genre0" class="all_area">
			                                        <label for="s-genre0">全'.$val["food_name"].'</label>
			                                    </li>';
											$type = $val["food_name_type"];
											foreach ($type as $t_key => $t_val){
												$check = (in_array($t_val["id"], $this_food))?("checked"):("");
												echo '<li>
				                                        <input type="checkbox" id="s-genre1" '.$check.'>
				                                        <label for="s-genre1">'.$t_val["food_name"].'</label>
														<input type="hidden" class="code" value="'.$t_val["id"].'">
				                                    </li>';	
											}
			                                echo '</ul>
			                            </li>';
								}
                        	?>
                        </ul>
                        <input type="hidden" class="food_type_data" name="food_type" value="">
                        <input type="hidden" class="page" name="page_count" value="<?=isset($_POST["page_count"])?($_POST["page_count"]):(1) ?>">
                    </div>
                    </form>
                    <div class="btnArea">
                        <button class="searchBtn" type="button">查詢</button>
                        <button class="rankingBtn" type = "button" >排行榜</button>
                    </div>
                </div>
               
            </div>
        </div>
        <footer class="f-footer">
            <div class="footerLogo">
                <a href="">
                    <img src="img/logo.png" alt="LOGO">
                </a>
            </div>
        </footer>
    </body>
    <script  src="https://code.jquery.com/jquery-3.3.1.js"></script>
    <script  src="https://cdnjs.cloudflare.com/ajax/libs/jquery-cookie/1.4.1/jquery.cookie.js"></script>
    
    <script>
    var s_like_array = new Array();
    $(document).ready(function(){
		check();
	});

    //選單展開
	$('.mainList').click(function(){
		if($(this).parent().hasClass('is-active')){
			$(this).parent().removeClass('is-active');
		}else{
			$(this).parent().addClass('is-active');
		}

	})
	//城市全選
	$('.all_area').click(function(){
		if($(this).prop("checked")){
			$(this).parent().parent().find('li input').prop("checked", true);
		}else{
			$(this).parent().parent().find('li input').prop("checked", false);
		}
	})
	//查詢按鈕
	$('.searchBtn').click(function(){
		check();
		$('.page').val(1);
		$('#sc_form').submit();
	})
	//切換頁碼
	$('.m-pager li a').click(function(){
		var num = new Number($('.page').val());
		if($(this).text() == "＜"){
			$('.page').val((num-1));
		}else if($(this).text() == "＞"){
			$('.page').val((num+1));
		}else{
			$('.page').val($(this).text());
		}
		check();
		$('#sc_form').submit();
	})
	//按下Like
	$('.like').click(function(){
		if(!$(this).hasClass('is-active')){
			var s_id = $(this).attr('id');
			$(this).addClass('is-active');
			if(typeof $.cookie('like') !== "undefined"){
				s_like_array = $.cookie('like');
				s_like_array += ','+s_id;
				$.cookie('like', s_like_array, { expires: 365 });
			}else{
				$.cookie('like', s_id, { expires: 365 });
			}
		}
	})	
	function check(){ //確認選取值
		var country_data = new Array();
		var food_data = new Array();
		$('.searchArea').find('input:checkbox').each(function(){
			if($(this).prop("checked")){
				$(this).closest('.m-list').addClass('is-active');
				var value = $(this).parent().find('.code').val();
				if(typeof value !== "undefined"){
					country_data.push(value);
				}
			}
		})
		$('.searchFood').find('input:checkbox').each(function(){
			if($(this).prop("checked")){
				$(this).closest('.m-list').addClass('is-active');
				var value = $(this).parent().find('.code').val();
				if(typeof value !== "undefined"){
					food_data.push(value);
				}
			}
		})
		$('.country_data').val(country_data);
		$('.food_type_data').val(food_data);
	}
	
    </script>
    
    
    
    
    
</html>