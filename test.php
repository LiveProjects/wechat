<?php
//https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx09d90e818f463049&redirect_uri=http://sdustsun.sinaapp.com/test.php&response_type=code&scope=snsapi_base&state=1#wechat_redirect
//https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=wx09d90e818f463049&corpsecret=wSR3r6OTa3ZTo1snK-z2jmjioXFwpR2hglYUCwME3tdhM-ld41kc6UETLJSjIB7H
//https://qyapi.weixin.qq.com/cgi-bin/user/getuserinfo?access_token=mVx0obsVh8tiAPjLlqPyGOejwnAe-g6Mc12XD4r7zJXWKdNHWG4q43A36P9i9RiS&code=dc9199ffe684c03372107af5e5127dfb&agentid=2
$appid="wx36dfcb7d371f9bbe";
$secret="jfJsbQnqUCeWy0CCRJr23S3zSPDFKUWnHLzN8jqIwWCiYvFdKBC6lMZnNCx3ifTb";
//$cookie_name="order_openid";
$session_name="order_openid";
/**
if(isset($_COOKIE[$cookie_name])){
	$openid=$_COOKIE[$cookie_name];
	//echo "read from cookie:".$openid;
}
**/

	$url="https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid={$appid}&corpsecret={$secret}";
	$ch=curl_init();
	curl_setopt($ch,CURLOPT_URL,$url);
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
	$a=curl_exec($ch);
	$strjson=json_decode($a);
	$access_token=$strjson->access_token;
	

	$code=$_GET['code'];
	$url="https://qyapi.weixin.qq.com/cgi-bin/user/getuserinfo?access_token={$access_token}&code={$code}&agentid=2";
	curl_setopt($ch,CURLOPT_URL,$url);

	curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
	$a=curl_exec($ch);
	$strjson=json_decode($a);
	$openid=$strjson->UserId;
	//echo "json:".$strjson;
	
	curl_close($ch);
	//setcookie($cookie_name,$openid,time()+3600*24*2);
	$_SESSION[$session_name] = $openid;
	echo "access_token:".$access_token;
	echo "code:".$_GET['code'];
	echo "openid:".$openid;


?>