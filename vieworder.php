<?php 
session_start ();
//echo "session openid:".$_SESSION['order_openid'];
//$_COOKIE["order_openid"] = "0001";
//setcookie('order_openid','0001',time()+3600*24*2);//for test 
$_SESSION['order_openid']= '0001';
require_once("getuser.php");

//echo "openid:".$openid;//test

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="IE=5; IE=8">
<link rel="stylesheet" href="css/edit.css" type="text/css">
<title>查看未完成订单</title>
</head>
<body>

	<nav id="top">
		查看未完成订单<b onclick=location.reload();></b>
	</nav>
	<div id="topsSec">
	    <div id="history">
			<button type="button" onclick=location.href="history.php">历史记录</button>
		</div>
		<div id="date">
		   <label for="">从<input id="val1" type="date" name="date" placeholder="Start" /></label>
		   <label for="">到<input id="val2" type="date" name="date" placeholder="End"/></label>
		   <button type="button">确认</button>
		</div>
	</div>
	<div
		class="container col-lg-10 col-lg-offset-1 col-md-10 col-md-offset-1 col-sm-12 col-xs-12"
		id="edit-main" style="clear: both;">
        <?php
			header ( 'content-type:text/html;charset=utf-8' );
			require 'orderclass.php';
// 			require 'test.php';
			// 调用函数，查询该业务员的所有订单
// 			getorder ( $openid );
			//getorder('0001');
			getorder($_SESSION['order_openid']);//added by sunhaibin ,2015,5,31,20:43
			
			/**********************显示所有的订单信息**********************/
			function getorder($enum) {
				// 实例化数据库操作类
				$db = new DB ();
				// 查询所有的订单号
// 				$sql1 = "select FOrderNumber from  t_zhsp_orderMain where FEmployeeNumber={$enum} order by FDate desc";
				//$sql1="select TOP 10 * from (select a.FID,row_number() over(order by Date desc) as rownum  from (select FID,FOrderNumber,FDate as Date from t_zhsp_orderMain  where FEmployeeNumber='{$enum}' and FStatus = 0 or FStatus= 1 or FStatus= 2 ) as a left join t_zhsp_order_status_notify as n ON a.FOrderNumber=n.FOrderNumber where( n.FStatus!=3 AND n.Fstatus!=4) OR (n.FStatus IS NULL)) as b";
				//按日期、订单号排序
				$sql1="select TOP 10 * from (select a.FID,row_number() over(order by a.FOrderNumber desc) as rownum  from (select FID,FOrderNumber,FDate as Date from t_zhsp_orderMain  where FEmployeeNumber='{$enum}' and (FStatus = 0 or FStatus= 1 or FStatus= 2) ) as a left join t_zhsp_order_status_notify as n ON a.FOrderNumber=n.FOrderNumber where( n.FStatus!=3 AND n.Fstatus!=4) OR (n.FStatus IS NULL)) as b";
// 				echo $sql1;
				$result1 = $db->execsql ( $sql1 );
// 				echo $sql1;die;
				$num_main = count ( $result1 ); // 计算该业务员有几个订单 
// 				echo $num_main;
				//默认显示十条订单信息
				/* if ($num_main<=10)
				{
					$shownum=$num_main;
				}else{
					$shownum=10;
				} */
				// 循环实现显示每一个主订单信息
				for($i = 0; $i < $num_main; $i ++) {
					$ord=order_main::getOneMain ( $result1, $i ); // 第i个具体主订单的显示函数
// 					echo $ord->FStatus;
							$key=show_order($ord,$idkey);
							$idkey=$key+1;
				}
			}
			function show_order($data,$key1){
			// 循环实现显示每一个主订单信息
					switch ($data->FStatus) {
						case 0 :
							$res='未通过';
							break;
						case 1 :
							$res='已通过';
							break;
						case 2 :
							$res='已分解';
							break;
						case 3 :
							$res='已发货';
							break;
						case 4 :
							$res='已作废';
							break;
						case 5 :
							$res = '已发货';
							break;
						default :
							$res = NULL;
							break;
					}
					$str1="<dl>";
					echo $str1;
					$str='<div class="top">
				        <h4 >订单编号:'.$data->FOrderNumber.'</h4>
				          <ul>
				          <li><label for="">下单时间:</label><input type="text" value=' .$data->FSource. $data->FDate .' disabled></li>';
					          
				    $str.=' <li class="type"><label for="">订单状态:</label><input type="text" value="' . $res .'" disabled></li>
				          <li><label for="">客户名称:</label><input type="text" value="' . $data->FName .'" disabled></li>
				          <li class="add"><label for="">备注:</label><input type="text" value="' . $data->FRemark . ' "disabled><span>修改</span></li>
				          </ul>
				          <button class="top-btn" ></button>
				          </div>';
					echo $str;
					$num_sub = count ( $data->suborders); // 计算某一主订单的子订单数目					
						
					//循环显示每一个子订单信息
					for($k = 0; $k < $num_sub; $k ++) {
						$ods = getOneSub ( $data, $k ); // 调用显示第k个子订单信息的函数
						
						$db=new DB();
						//查找t_zhsp_AccountInfo表中，该子订单所对应的食品厂ID
						$sql="select FID from t_zhsp_AccountInfo where FDBDesc='{$ods->FAccountName}'";
						$res=$db->execsql($sql);
							
						//查找t_zhsp_OrderSub表中，该子订单所对应的FID
						$sql_fid="select FID from t_zhsp_OrderSub where FProductNumber='{$ods->FProductNumber}' and FOrderNumber='{$data->FOrderNumber}' and FAccountId='{$res[0]['FID']}'";
						$res_fid=$db->execsql($sql_fid);
						$key=$key1+$k;
						$str='<dt id='.$key.'>产品编号:'.$ods->FProductNumber.'
	                    		  <button class="modify" id="modify'.$key.'"></button>
	                    	  </dt>';
							
						$str.='<dd style="position:relative;" class="acm'.$key.' tan">
								<label for="">食品厂：</label><input type="text" id="suggest_inputc'.$key.'"  onkeyup=changjia("#suggest_inputc'.$key.'","#suggest_ulc'.$key.'","#suggest_input'.$key.'") value="'.$ods->FAccountName.' "disabled>
								<ul id="suggest_ulc'.$key.'" class="suggest_ulc">
				                </ul>
								</dd>  ';
						$str.='<dd style="position:relative;"><label for="">订单产品：</label><input type="text"  onBlur=account("#account'.$key.'") id="suggest_input'.$key.'" class="modify2" onfocus=mode("#suggest_input'.$key.'","#suggest_ul'.$key.'") value="'.$ods->FName.'--'.$ods->FModel.'" disabled>
				            	 <ul id="suggest_ul'.$key.'" class="suggest_ul">
		           				 </ul>
				                </dd>';
						$str.='<dd><label for="">订单单价：</label><input type="text" onfocus=account() class="modify2" value="'.$ods->FPrice.'" disabled><b>元</b></dd>';
						if ($data->FStatus==2){
							$str.='<dd><label for="">分解前数量：</label><input type="text" class="modify2" value="'.$ods->FPQty.'" disabled><b>'.$ods->FUnit.'</b></dd>';
						}
						$str.='<dd><label for="">订单数量：</label><input type="text"  class="modify2" value="'.$ods->FQty.'" disabled><b>'.$ods->FUnit.'</b></dd>';
						$str.='<dd class="tan"><label for="">备注：</label><input type="text" class="modify2" value="'.$ods->FRemark.'" disabled></dd>
				            <dd><label for=""></label><input type="hidden" value="'.$res_fid[0]['FID'].'" ></dd>';
						echo $str;
					}
					$tostr='<div class="bottom">
					        <a><button>撤回</button></a>
					        <a><button>删除</button></a>
			           		</div>';
					echo $tostr; 
					$str2="</dl>";
					echo $str2; 
					return $key;
				}	
				
		?>         
	</div>
	<div id="top-face" ></div>
	<div id="ajax"></div>
	<div style="clear: both;"></div>
	<button id="moreup" style="">往前翻页</button>
    <button id="more" style="">往后翻页</button>
    <button id="timemoreup" style="display: none;">时间往前更多</button>
    <button id="timemore" style="display: none;">时间往后更多</button>
</body>
<script type="text/javascript" src="bootstrap/js/jquery-2.1.1.min.js"></script>
<script type="text/javascript" id="find" class="find" src="js/edit.js"></script>
<script type="text/javascript" src="js/editchangjia.js"></script>
<script type="text/javascript" src="js/editmode.js"></script>
<script type="text/javascript">
window.onload=function(){
	sessionStorage.setItem("up",1);
	sessionStorage.setItem("timeup",1);
}
</script>

</html>