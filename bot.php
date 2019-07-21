<?php

require_once('./LINEBotTiny.php');

// Line info
$channelSecret = "ba24fd25da7dac425712d62f3e5ee020";
$channelAccessToken = "6o/Zj3cooOLdg2CEHa+2hfGYroZaw4G2ZlHMNhr6Y7Xs4FAbmkrKGY5i+8pgK95q/8gU6OjY2KRyTQRzj8IJfAP7z05WVt1pwiJVPGA/wkVHJmTnI7hHx1J0K3749m7LbHZDF4uOyZ+LlX1AjbI9RgdB04t89/1O/w1cDnyilFU=";

// Google表單資料
$googledataspi = "https://spreadsheets.google.com/feeds/list/1t4H8Ul-yIzvjvmNVtJvMlx9bkvsbO6GjDEUlC6efPH4/od6/public/values?alt=json";

// 建立Client from LINEBotTiny
$client = new LINEBotTiny($channelAccessToken, $channelSecret);

// 取得事件(只接受文字訊息)
foreach ($client->parseEvents() as $event) {
	
	switch ($event['type']) {       
    	case 'message':
        	// 讀入訊息
        	$message = $event['message'];

        	// google-->json
        	$json = file_get_contents($googledataspi);
        	$data = json_decode($json, true);           
        	$store_text="沒有找到相對應的食物"; 
        	$result=array();
        	// 資料起始從feed.entry          
        	foreach ($data['feed']['entry'] as $item) {
            	// 將keywords欄位依,切成陣列
            	$keywords = explode(',', $item['gsx$keywords']['$t']);

            	// 以關鍵字比對文字內容，符合的話將食物寫入
            	foreach ($keywords as $keyword) {
                	if (mb_strpos($message['text'], $keyword) !== false) {                      
                    	$store_text = $item['gsx$foodname']['$t']."\n熱量:".$item['gsx$kcal']['$t']." kcal\n蛋白質:".$item['gsx$protien']['$t']." g\n脂肪:".$item['gsx$fat']['$t']." g\n膳食纖維:".$item['gsx$dietaryfiber']['$t']." g\n膽固醇:".$item['gsx$cholesterol']['$t']." mg\n鈉:".$item['gsx$na']['$t']." mg";                 
               		}
/*
                	if (mb_strpos($message['text'], $keyword) !== false) {
                    
                    	$candidate = array(
                        
                        	'thumbnailImageUrl' => $item['gsx$photourl']['$t'],
                        	'title' => $item['gsx$foodname']['$t'],
                        	//'text' => $item['gsx$kcal]['$t']
                        	'text' => $item['gsx$foodname']['$t'],
                        
                    
                    	);
                    	array_push($result, $candidate);
                	}
*/
            	}
        	}       



        switch ($message['type']) {
            case 'text':
                
                $client->replyMessage(array(
                    'replyToken' => $event['replyToken'],
                    'messages' => array(
                        
                        array(
                            'type' => 'text',
                            'text' => $store_text,
                        )
/*
                        array(
                            'type' => 'template',
                            'template' => array(
                                'type' => 'carousel',
                                'columns' => $result,
                            ),
                            
                        )
*/
                    ),
                ));               
                break;
            default:
                error_log("Unsupporeted message type: " . $message['type']);
                break;
        }
        break;
    	default:
        	error_log("Unsupporeted event type: " . $event['type']);
        	break;
	}
};