<?php
session_start ();
// require_once 'dbaccess.php';
require 'orderclass.php';
// require 'test.php';
$db = new DB ();
/**
 * ****************删除订单****************************
 */
if (! empty ( $_GET ['act'] )) {
	$act = $_GET ['act'];
	if ($act == 'del') {
		$id = $_GET ['id'];
		unset ( $_SESSION [$id] );
		if (empty ( $_SESSION [$id] )) {
			echo true;
		} else {
			echo false;
		}
	}
	
	/**
	 * ******************动态显示产品的数量单位**************************
	 */
	
	if ($act == 'product') {
		$data1 = $_GET ['val'];
		$namemodel1 = explode ( '--', $data1 );
		$name = $namemodel1 [0];
		$model = $namemodel1 [1];
		$sql1 = "select FUnit from t_zhsp_product where FName='{$name}' and FModel='{$model}'";
		$res1 = $db->getRow ( $sql1 );
		echo $res1 ['FUnit'];
	}
	/**
	 * ****************修改订单****************************
	 */
	if ($act == 'mod') {
		$id = $_GET ['id'];
		$val = $_GET;
		// 得到的产品规格
		$namemodel = $val ['val1'];
		// 判断修改后的商品是否重复
		$arr = $_SESSION;
		// 去除本身的namemodel
		unset ( $arr [$id] );
		// 循环遍历
		foreach ( $arr as $v ) {
			
			if ($v ['namemodel'] == $namemodel) {
				
				$mes = 0;
				die ( $mes );
			}
		}
		// 判断产品规格或者帐套名称输入是否正确
		if (strstr ( $namemodel, "--" )) {
			$data = explode ( "--", $namemodel );
			$FName = $data [0]; // 产品名称
			$FModel = $data [1]; // 产品规格
			$sql = "SELECT FNumber FROM t_zhsp_product WHERE FName = '{$FName}' AND FModel = '{$FModel}'";
			$FNumberArr = $db->getRow ( $sql );
			if (! empty ( $FNumberArr )) {
				$mes = 1;
				
			} else {
				$mes = 0;
			}
		} else {
			$mes = 0;
		}
		if ($mes == 1) {
			$_SESSION [$id] ['namemodel'] = $val ['val1'];
			$_SESSION [$id] ['FQty'] = ( float ) ($val ['val2']);
			$_SESSION [$id] ['FPrice'] = ( float ) ($val ['val3']);
			// $_SESSION [$id] ['FDBDesc'] = $val ['val4'];
			$_SESSION [$id] ['FRemark'] = $val ['val5'];
			
			$sql = "SELECT p.FAccountId,a.FDBDesc FROM t_zhsp_product as p inner join t_zhsp_AccountInfo as a ON p.FAccountId=a.FID WHERE FNumber='{$FNumberArr['FNumber']}'";
			$rows = $db->execsql ( $sql );
			// 得出产品的库存
			foreach ( $rows as $v ) {
				$kq_sql = "select  FAvlQty,FUnit from  t_zhsp_inventory where FAccountId='{$v['FAccountId']}' and FItemNo='{$FNumberArr['FNumber']}'";
				$row = $db->getRow ( $kq_sql );
				if (empty ( $row )) {
					$row ['FDBDesc'] = $v ['FDBDesc'];
					$row ['FAvlQty'] = 0;
					$row ['FUnit'] = '库存';
				}
				$row ['FDBDesc'] = $v ['FDBDesc'];
				$FAvlQty [] = $row;
			}
			
			$_SESSION [$id] ['FAvlQty'] = $FAvlQty;
			foreach ( $FAvlQty as $kc ) {
				$kc ['FAvlQty'] = round ( $kc ['FAvlQty'], 2 );
				$str .= "<input value='" . $kc ['FDBDesc'] . ":" . $kc ['FAvlQty'] . $kc ['FUnit'] . "' disabled>";
			}
			
			$str = "<li  class='list1' id='kc{$id}'><label for=''>库存：</label>{$str}</li>";
			echo $FNumberArr['FNumber'] ;
			echo $str;
			// echo $FNumberArr ['FNumber'];
		}
	}
	if ($act == "FUnit") {
		$data = explode ( "--", $_GET['val'] );
		$name = $data [0];
		$model = $data [1];
		$sql_unit = "SELECT FUnit FROM t_zhsp_product WHERE FName='{$name}' AND FModel='{$model}'";
		$FUnit = $db->getRow ( $sql_unit );
		$FUnit = $FUnit ['FUnit'];
		echo $FUnit;
	}
	/**
	 * ****************添加订单时动态显示帐套信息****************************
	 */
	if ($act == "accountInfo") {
		$str = "";
		$productArr = $_GET ['val'];
		$data = explode ( "--", $productArr );
		$product = $data [0]; // 得到产品名称
		$model = $data [1]; // 得到产品规格
		$sql_pm = "SELECT FNumber FROM t_zhsp_product WHERE FName='{$product}' AND FModel='{$model}'";
		$FID = $db->getRow ( $sql_pm );
		$FNumber = $FID ['FNumber']; // 得到产品编号
		                             // 根据产品编码在t_zhsp_product中找到生产该产品的食品厂
		$sql_accid = "select FAccountId from  t_zhsp_product where FNumber='{$FNumber}'";
		$res_accid = $db->execsql ( $sql_accid );
		$num_acc = count ( $res_accid ); // 计算匹配的食品厂的数量
		                                 // 根据查出的食品厂id得到食品厂名称和即时库存
		$sql_ivt = "select  FAvlQty,FUnit from  t_zhsp_inventory where FAccountId='{$res_accid [0] ['FAccountId']}' and FItemNo= '{$FNumber}'";
		$res_ivt = $db->execsql ( $sql_ivt );
		$sql_acc = "select FDBDesc from  t_zhsp_AccountInfo where FID='{$res_accid[0]['FAccountId']}'";
		$res_acc = $db->execsql ( $sql_acc );
		
		if (! empty ( $res_ivt )) {
			$res_ivt [0] ['FAvlQty'] = floor ( $res_ivt [0] ['FAvlQty'] * 100 ) / 100;
			
			$result = "<option value='{$res_acc [0] [FDBDesc]}'>{$res_acc [0] [FDBDesc]}--{$res_ivt [0] [FAvlQty]}{$res_ivt [0] [FUnit]} </option>";
		} else {
			$a [0] = $res_acc [0] ['FDBDesc'];
		}
		// 先得出第一条结果，再依次得出后面的结果
		for($i = 1; $i < $num_acc; $i ++) {
			$sql_acc = "select FDBDesc from  t_zhsp_AccountInfo where FID='{$res_accid[$i]['FAccountId']}'";
			$res_acc = $db->execsql ( $sql_acc );
			$sql_ivt = "select  FAvlQty,FUnit from  t_zhsp_inventory where FAccountId='{$res_accid [$i] ['FAccountId']}' and FItemNo= '{$FNumber}'";
			$res_ivt = $db->execsql ( $sql_ivt );
			if (! empty ( $res_ivt )) {
				$res_ivt [0] ['FAvlQty'] = floor ( $res_ivt [0] ['FAvlQty'] * 100 ) / 100;
				$result .= "<option value='{$res_acc [0] [FDBDesc]}'>{$res_acc [0] [FDBDesc]}--{$res_ivt [0] [FAvlQty]}{$res_ivt [0] [FUnit]} </option>";
			} else {
				$a [$i] = $res_acc [0] ['FDBDesc'];
			}
		}
		if (! empty ( $a )) {
			for($k = 0; $k < $num_acc; $k ++) {
				if (! empty ( $a [$k] )) {
					$result .= "<option value='{$a [$k]}'>{$a [$k]}--0 </option>";
				}
			}
		}
		echo $result;
	}
	
	/**
	 * ****************显示历史订单****************************
	 */
	/*
	 * if ($act == 'history') {
	 * $enum='0001';
	 * $db1 = new DB ();
	 * // 查询所有的订单号
	 * $sql1 = "select TOP 10 * from (select FID,row_number() over(order by FDate desc) as rownum from t_zhsp_orderMain where FEmployeeNumber={$enum} ) as a";
	 * $result1 = $db1->execsql ( $sql1 );
	 * $num_main = count ( $result1 ); // 计算该业务员有几个订单
	 *
	 * // 循环实现显示每一个主订单信息
	 * for($i = 0; $i < $num_main; $i ++) {
	 * $ord = order_main::getOneMain ( $result1, $i ); // 第i个具体主订单的显示函数
	 * $key = show_order ( $ord, $idkey );
	 * $idkey = $key + 1;
	 * }
	 * }
	 */
	
	/**
	 * ****************按日期显示未完成订单信息****************************
	 */
	if ($act == 'date1') {
		//$enum = '0001';
		$enum = $_SESSION["order_openid"]; //added by sunhaibin,2015,5,31.20:35
		// 实例化数据库操作类
		$db1 = new DB ();
		$flag =  array();
		// 查询所有的订单号
		$onetime = $_GET ['dateone'] . " 23:59:59";
		$twotime = $_GET ['datetwo'] . " 0:0:0";
		if (($_GET ['dateone'] == null) || ($_GET ['datetwo'] == null)) {
			echo 0;
			die ();
		}
		//$sql1="select TOP 10  * from (select a.FID,row_number() over(order by Date desc) as rownum from (select FID,FOrderNumber,FDate as Date from t_zhsp_orderMain where FEmployeeNumber='{$enum}' and (FStatus = 0 or FStatus= 1 or FStatus= 2) and (FDate<='{$onetime}' and FDate>='{$twotime}') ) as a left join t_zhsp_order_status_notify as n ON a.FOrderNumber=n.FOrderNumber where( n.FStatus!=3 AND n.Fstatus!=4) OR (n.FStatus IS NULL)) as b ";
		$sql1="select TOP 10  * from (select a.FID,row_number() over(order by a.FOrderNumber desc) as rownum from (select FID,FOrderNumber,FDate as Date from t_zhsp_orderMain where FEmployeeNumber='{$enum}' and (FStatus = 0 or FStatus= 1 or FStatus= 2) and (FDate<='{$onetime}' and FDate>='{$twotime}') ) as a left join t_zhsp_order_status_notify as n ON a.FOrderNumber=n.FOrderNumber where( n.FStatus!=3 AND n.Fstatus!=4) OR (n.FStatus IS NULL)) as b ";
		// 		echo $sql1;
		$result1 = $db1->execsql ( $sql1 );
		$num_main = count ( $result1 ); // 计算该业务员有几个订单
// 				echo $num_main;
	
		// 循环实现显示每一个主订单信息
		for($i = 0; $i < $num_main; $i ++) {
			$ord = order_main::getOneMain ( $result1, $i ); // 第i个具体主订单的显示函数
			// 判断条件是否符合所选日期
			$key = show_order ( $ord, $idkey );
			$idkey = $key + 1;
			$flag [$i] = 1;
		}
		if (in_array ( 1, $flag ) == false)
				
			// echo "<h3><center>没有找到符合此条件的订单信息</center></h3>";
			echo 1;
	}
	/**
	 * ****************按日期显示订单信息****************************
	 */
	if ($act == 'date2') {
		//$enum = '0001';
		$enum = $_SESSION["order_openid"]; //added by sunhaibin,2015,5,31.20:35
		// 实例化数据库操作类
		$db1 = new DB ();
		$flag =  array();
		// 查询所有的订单号
		$onetime = $_GET ['dateone'] . " 23:59:59";
		$twotime = $_GET ['datetwo'] . " 0:0:0";
		if (($_GET ['dateone'] == null) || ($_GET ['datetwo'] == null)) {
			echo 0;
			die ();
		}
		//$sql1 = "Select TOP 10 * from (select FID ,row_number() over(order by FDate desc) as rownum from  t_zhsp_orderMain where FEmployeeNumber={$enum} and (FDate<='{$onetime}' and FDate>='{$twotime}')) as a ";
		$sql1 = "Select TOP 10 * from (select FID ,row_number() over(order by FOrderNumber desc) as rownum from  t_zhsp_orderMain where FEmployeeNumber={$enum} and (FDate<='{$onetime}' and FDate>='{$twotime}')) as a ";
// 		echo $sql1;
		$result1 = $db1->execsql ( $sql1 );
		$num_main = count ( $result1 ); // 计算该业务员有几个订单
// 		echo $num_main;
		                                
		// 循环实现显示每一个主订单信息
		for($i = 0; $i < $num_main; $i ++) {
			$ord = order_main::getOneMain ( $result1, $i ); // 第i个具体主订单的显示函数
			                                                // 判断条件是否符合所选日期
			$key = show_order ( $ord, $idkey );
			$idkey = $key + 1;
			$flag [$i] = 1;
		}
		if (in_array ( 1, $flag ) == false)
			
			// echo "<h3><center>没有找到符合此条件的订单信息</center></h3>";
			echo 1;
	}
	/**
	 * ****************更多默认订单****************************
	 */
	if ($act == 'more') {
		// 	echo '890';
		//$enum = '0001';
		$enum = $_SESSION["order_openid"]; //added by sunhaibin,2015,5,31.20:35
		$db = new DB ();
		// 查询所有的订单号
		// 	$shownum = $_GET ['length'];
		$endnum = $shownum + 10;
		$page=$_GET['page'];
		$num=($page-1)*10;
		//$sql1 = "select TOP 10 * from (select  a.FID,row_number() over(order by Date desc) as rownum  from (select FID,FOrderNumber,FDate as Date from t_zhsp_orderMain  where FEmployeeNumber='{$enum}' and FStatus = 0 or FStatus= 1 or FStatus= 2 ) as a left join t_zhsp_order_status_notify as n ON a.FOrderNumber=n.FOrderNumber where( n.FStatus!=3 AND n.Fstatus!=4) OR (n.FStatus IS NULL)) as b where rownum>'{$num}'";
		$sql1 = "select TOP 10 * from (select  a.FID,row_number() over(order by a.FOrderNumber desc) as rownum  from (select FID,FOrderNumber,FDate as Date from t_zhsp_orderMain  where FEmployeeNumber='{$enum}' and FStatus = 0 or FStatus= 1 or FStatus= 2 ) as a left join t_zhsp_order_status_notify as n ON a.FOrderNumber=n.FOrderNumber where( n.FStatus!=3 AND n.Fstatus!=4) OR (n.FStatus IS NULL)) as b where rownum>'{$num}'";
		$result1 = $db->execsql ( $sql1 );
// 		echo $sql1;
		$num_main = count ( $result1 ); // 计算该业务员有几个订单
		// 	echo $num_main;
		if ($num_main == 0) {
			echo - 1;
		}
		for($i = 0; $i < $num_main; $i ++) {
			$ord = order_main::getOneMain ( $result1, $i ); // 第i个具体主订单的显示函数
			$key = show_order ( $ord, $idkey );
			$idkey = $key + 1;
		}
	}
	
	/**
	 * ****************更多历史订单****************************
	 */
	if ($act == 'historymore') {
		//$enum = '0001';
		$enum = $_SESSION["order_openid"]; //added by sunhaibin,2015,5,31.20:35
		$db = new DB ();
		// 查询所有的订单号
		// 	$shownum = $_GET ['length'];
		$endnum = $shownum + 10;
		$page=$_GET['page'];
		$num=($page-1)*10;
		//$sql1 = "select TOP 10 * from (select FID,row_number() over(order by FDate desc) as rownum from t_zhsp_orderMain where FEmployeeNumber={$enum} ) as a where rownum > '{$num}'";
		$sql1 = "select TOP 10 * from (select FID,row_number() over(order by FOrderNumber desc) as rownum from t_zhsp_orderMain where FEmployeeNumber={$enum} ) as a where rownum > '{$num}'";
		$result1 = $db->execsql ( $sql1 );
		$num_main = count ( $result1 ); // 计算该业务员有几个订单
		// 	echo $num_main;
		if ($num_main == 0) {
			echo - 1;
		}
		// echo $num_main;
		// $shown=$_GET['hkey']+1;//获取前一次显示的下一订单在数据库中的位置，用于本次显示的for循环
		// 	$idkey = $_GET ['key'] + 1; // 获取子订单的id,并加一，计算出本次显示的第一个子订单的id
		// 未显示的订单若大于10条则显示10条，若少于10条则全部显示
		for($i = 0; $i < $num_main; $i ++) {
			$ord = order_main::getOneMain ( $result1, $i ); // 第i个具体主订单的显示函数
			$key = show_order ( $ord, $idkey );
			$idkey = $key + 1;
		}
	}
	
	/**
	 * ****************更多未完成时间订单****************************
	 */
	if ($act == 'datemore') {
		//$enum = '0001';
		$enum = $_SESSION["order_openid"]; //added by sunhaibin,2015,5,31.20:35
		$db = new DB ();
		$onetime = $_GET ['dateone'] . " 0:0:0";
		$twotime = $_GET ['datetwo'] . " 23:59:59";
		$page=$_GET['page'];
		$num=($page-1)*10;
		//$sql1="select TOP 10  * from (select a.FID,row_number() over(order by Date desc) as rownum from (select FID,FOrderNumber,FDate as Date from t_zhsp_orderMain where FEmployeeNumber='{$enum}' and (FStatus = 0 or FStatus= 1 or FStatus= 2) and (FDate<='{$onetime}' and FDate>='{$twotime}') ) as a left join t_zhsp_order_status_notify as n ON a.FOrderNumber=n.FOrderNumber where( n.FStatus!=3 AND n.Fstatus!=4) OR (n.FStatus IS NULL)) as b  where rownum > '{$num}'";
		$sql1="select TOP 10  * from (select a.FID,row_number() over(order by a.FOrderNumber desc) as rownum from (select FID,FOrderNumber,FDate as Date from t_zhsp_orderMain where FEmployeeNumber='{$enum}' and (FStatus = 0 or FStatus= 1 or FStatus= 2) and (FDate<='{$onetime}' and FDate>='{$twotime}') ) as a left join t_zhsp_order_status_notify as n ON a.FOrderNumber=n.FOrderNumber where( n.FStatus!=3 AND n.Fstatus!=4) OR (n.FStatus IS NULL)) as b  where rownum > '{$num}'";
		// 	echo $sql1;
		$result1 = $db->execsql ( $sql1 );
		// echo $sql1;
		$num_main = count ( $result1 ); // 计算该业务员有几个订单
// 			echo $num_main;
		if ($num_main == 0) {
			echo - 1;
		}
		for($i = 0; $i < $num_main; $i ++) {
			$ord = order_main::getOneMain ( $result1, $i ); // 第i个具体主订单的显示函数
			$key = show_order ( $ord, $idkey );
			$idkey = $key + 1;
		}
	}
	
	/**
	 * ****************更多时间订单****************************
	 */
	if ($act == 'timemore') {
		//$enum = '0001';
		$enum = $_SESSION["order_openid"]; //added by sunhaibin,2015,5,31.20:35
		$db = new DB ();
		/*
		 * $onetime = $_GET ['dateone'] ;
		 * $twotime = $_GET ['datetwo'] ;
		*/
		$onetime = $_GET ['dateone'] . " 23:59:59";
		$twotime = $_GET ['datetwo'] . " 00:00:00";
		$page=$_GET['page'];
		$num=($page-1)*10;
		//$sql1="Select TOP 10 * from (select FID ,row_number() over(order by FDate desc) as rownum from  t_zhsp_orderMain where FEmployeeNumber={$enum} and (FDate<='{$onetime}' and FDate>='{$twotime}') )as a where rownum > '{$num}'";
		$sql1="Select TOP 10 * from (select FID ,row_number() over(order by FOrderNumber desc) as rownum from  t_zhsp_orderMain where FEmployeeNumber={$enum} and (FDate<='{$onetime}' and FDate>='{$twotime}') )as a where rownum > '{$num}'";
		 //	echo $sql1;
		$result1 = $db->execsql ( $sql1 );
		// echo $sql1;
		$num_main = count ( $result1 ); // 计算该业务员有几个订单
		//echo $num_main;
		if ($num_main == 0) {
			echo - 1;
		}
		for($i = 0; $i < $num_main; $i ++) {
			$ord = order_main::getOneMain ( $result1, $i ); // 第i个具体主订单的显示函数
			$key = show_order ( $ord, $idkey );
			$idkey = $key + 1;
		}
	}
}


/**
 * ****************显示某一订单信息****************************
 */
function show_order($data, $key1) {
	switch ($data->FStatus) {
		case 0 :
			$res = '未通过';
			break;
		case 1 :
			$res = '已通过';
			break;
		case 2 :
			$res = '已分解';
			break;
		case 3 :
			$res = '已发货';
			break;
		case 4 :
			$res = '已作废';
			break;
		case 5 :
			$res = '已发货';
			break;
		default :
			$res = NULL;
			break;
	}
	$str1 = "<dl>";
	echo $str1;
	$str = '<div class="top">
				        <h4>订单编号:' . $data->FOrderNumber . '</h4>
				          <ul>
				          <li><label for="">下单时间:</label><input type="text" value=' . $data->FSource . $data->FDate . ' disabled></li>';
	
	$str .= ' <li class="type"><label for="">订单状态:</label><input type="text" value="' . $res . '" disabled></li>
				          <li><label for="">客户名称:</label><input type="text" value="' . $data->FName . '" disabled></li>
				          <li class="add"><label for="">备注:</label><input type="text" value="' . $data->FRemark . ' "disabled><span>修改</span></li>
				          </ul>
				          <button class="top-btn" ></button>
				          </div>';
	echo $str;
	$num_sub = count ( $data->suborders ); // 计算某一主订单的子订单数目
	                                       
	// 循环显示每一个子订单信息
	for($k = 0; $k < $num_sub; $k ++) {
		$ods = getOneSub ( $data, $k ); // 调用显示第k个子订单信息的函数
		$db = new DB ();
		
		// 查找t_zhsp_AccountInfo表中，该子订单所对应的食品厂ID
		$sql = "select FID from t_zhsp_AccountInfo where FDBDesc='{$ods->FAccountName}'";
		$res = $db->execsql ( $sql );
		
		// 查找t_zhsp_OrderSub表中，该子订单所对应的FID
		$sql_fid = "select FID from t_zhsp_OrderSub where FProductNumber='{$ods->FProductNumber}' and FOrderNumber='{$data->FOrderNumber}' and FAccountId='{$res[0]['FID']}'";
		$res_fid = $db->execsql ( $sql_fid );
		$key = $key1 + $k;
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
		$str .= '<dd><label for="">订单单价：</label><input type="text" onfocus=account() class="modify2" value="' . $ods->FPrice . '" disabled><b>元</b></dd>';
		if ($data->FStatus==2){
			$str.='<dd><label for="">分解前数量：</label><input type="text" class="modify2" value="'.$ods->FPQty.'" disabled><b>'.$ods->FUnit.'</b></dd>';
		}
		$str.='<dd><label for="">订单数量：</label><input type="text"  class="modify2" value="'.$ods->FQty.'" disabled><b>'.$ods->FUnit.'</b></dd>';
		//判断订单状态，若为“未通过”则不显示“发货数量”、“发货时间”
		if($data->FStatus==3||$data->FStatus==5){
			$str.='<dd><label for="">实发时间：</label><input type="text" class="modify2" value='.$ods->FSendDate.' disabled></dd>';
			$str.= '<dd><label for="">实发单价：</label><input type="text" onfocus=account() class="modify2" value="' . $ods->FPrice0. '" disabled><b>元</b></dd>';
			$str.='<dd><label for="">实发数量：</label><input type="text" class="modify2" value="'.$ods->FSendQty.'" disabled><b>'.$ods->FUnit.'</b></dd>';
		}
// 		echo $ods->FSendDate;
		$str.='<dd class="tan"><label for="">备注：</label><input type="text" class="modify2" value="'.$ods->FRemark.'" disabled></dd>
			<dd><label for=""></label><input type="hidden" value="'.$res_fid[0]['FID'].'" ></dd>';
		echo $str;
	}
	$tostr = '<div class="bottom">
					        <a><button>撤回</button></a>
					        <a><button>删除</button></a>
			           		</div>';
	echo $tostr;
	$str2 = "</dl>";
	echo $str2;
	// echo $key;
	return $key;
}