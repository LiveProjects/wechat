<?php
header ( 'content-type:text/html;charset=utf-8' );
require "dbaccess.php";
$db = new DB ();
if ($_GET ['type'] == 1) {
	if (delete ( $_GET ['FOrderNumber'] )) {
		echo "删除成功";
	} else {
		echo "删除失败，请联系技术支持";
	}
}

if ($_GET ['type'] == 2) {
	$c = withdraw ( $_GET ['ordernum'] );
	if ($c) {
		echo "撤回成功";
	} else {
		echo "撤回失败，请联系技术支持";
	}
}

if ($_GET ['type'] == 3) {
	// echo $_GET ['FName'] , $_GET ['FQty'] ,$_GET ['FPrice'] , $_GET ['FAccountName'] ;
	
	if (empty ( $_GET ['FName'] ) || empty ( $_GET ['FQty'] ) || empty ( $_GET ['FPrice'] ))
		die ( '请检查空项！' );
	elseif (is_numeric($_GET ['FQty'])==false|| is_numeric($_GET ['FPrice'])==false){
 		die('输入的数量或单位不正确！');
// echo gettype($_GET ['FQty']).gettype($_GET ['FPrice']);
	}else {
		/**
		 * ************检测产品规格、帐套名称输入是否正确*****************
		 */
		
		$name = $_GET ['FName'];
		$data = explode ( '--', $name );
		$FName = $data [0];
		$FModel = $data [1];
		$sql = "select FNumber from t_zhsp_product where FName='{$FName}' and FModel='{$FModel}'";
		$res = $db->execsql ( $sql ); // 查找是否有对应的产品ID
		if (empty ( $res )) {
			die ( '产品规格输入错误' );
		}
		
		/*
		 * $FDBDescArr = explode ( "--", $FAccountName );
		 * $FDBDesc = $FDBDescArr [0];
		 * // 根据食品厂名称得到食品厂ID
		 * $sql_Id = "select FID from t_zhsp_AccountInfo where FDBDesc='{$FDBDesc}'";
		 * if (empty ( $sql_Id ))
		 * die ( '食品厂输入错误' );
		 */
		if (!strstr ( $_GET ['FAccountName'], "调理品" )) {
			switch ($_GET ['FUnit']) {
				
				case "公斤" :
					$_GET ['FQty'] >= 1000 ? $FSource = 2 : $FSource = 1;
					break;
				case "吨" :
					$_GET ['FQty'] >= 1 ? $FSource = 2 : $FSource = 1;
					break;
				case "斤" :
					$_GET ['FQty'] >= 2000 ? $FSource = 2 : $FSource = 1;
					break;
				case "千克" :
					$_GET ['FQty'] >= 1000 ? $FSource = 2 : $FSource = 1;
					break;
				default :
					break;
			}
		} else {
			$FSource = 2;
		}
		if ($FSource == 2)
			$FStatus = 0;
		else
			$FStatus = 1;
		
		$name = $_GET ['FName'];
		$data = explode ( '--', $name );
		$FName = trim ( $data [0] );
		$FModel = trim ( $data [1] );
		$sql = "select FNumber from t_zhsp_product where FName='{$FName}' and FModel='{$FModel}'";
		$res = $db->execsql ( $sql );
		// 取出修改的返回值
		$b = revise_sub ( trim ( $_GET ['FOrderNumber'] ), trim ( $_GET ['FID_sub'] ), trim ( $_GET ['FQty'] ), trim ( $_GET ['FPrice'] ), trim ( $_GET ['Remark'] ), trim ( $_GET ['FAccountName'] ), $FName, $FModel, $FSource, $FStatus );
		if ($b == 1) {
			echo "修改成功" . $res [0] ['FNumber'] . $FStatus;
			// echo trim($_GET ['FID_sub']);
		} else if ($b == 2) {
			echo "修改失败，商品重复";
		} else {
			echo "修改失败，请联系技术支持";
		}
	}
}

if ($_GET ['type'] == 4) {
	// 取得修改返回值
	$a = revise_main ( $_GET ['FOrderNumber'], $_GET ['FRemark'] );
	if ($a) {
		echo "修改成功";
	} else {
		echo "修改失败，请联系技术支持";
	}
}

/**
 * ****************删除订单******************
 */
function delete($FOrderNumber) {
	global $db;
	$sql_delete1 = "update t_zhsp_orderMain set FStatus =4," . "FDataFlag = -1, FUpdateFlag = 1  " . "where FOrderNumber ='{$FOrderNumber}'";
	// var_dump($sql_delete1);
	$sql_delete2 = "update t_zhsp_OrderSub set " . "FDataFlag = -1, FUpdateFlag = 1  " . "where FOrderNumber ='{$FOrderNumber}'";
	// var_dump($sql_delete2);
	/*
	 * $sql_delete3="update t_zhsp_order_status_notify ".
	 * "set FStatus =4 where FOrderNumber ='{$FOrderNumber}'";
	 * var_dump($sql_delete3);
	 */
	$delete1 = $db->execsql ( $sql_delete1 );
	$delete2 = $db->execsql ( $sql_delete2 );
	if($delete1){
	  $log = new log();
	  $log->writelog($sql_delete1);
	  $log->writelog("success");
	}else{
	  $log = new log();
	  $log->writelog($sql_delete1);
	  $log->writelog("fail");
	}
	if($delete2){
	  $log = new log();
	  $log->writelog($sql_delete2);
	  $log->writelog("success");
	}else{
	  $log = new log();
	  $log->writelog($sql_delete2);
	  $log->writelog("fail");
	}
	if ($delete1 && $delete2){
		return 1;
	}
	else
		return 0;
}
/**
 * *****************撤回订单*****************
 */
function withdraw($FOrderNumber) {
	global $db;
	$sql_withdraw1 = "update t_zhsp_orderMain set FStatus =0," . "FDataFlag = -1, FUpdateFlag = 1 where " . "FOrderNumber ='{$FOrderNumber}'";
	$sql_withdraw2 = "update t_zhsp_OrderSub set " . "FDataFlag = -1, FUpdateFlag = 1 where " . "FOrderNumber ='{$FOrderNumber}'";
	/*
	 * $sql_withdraw3="update t_zhsp_order_status_notify ".
	 * "set FStatus =0 where FOrderNumber ='{$FOrderNumber}'";
	 */
	$withdraw1 = $db->execsql ( $sql_withdraw1 );
	$withdraw2 = $db->execsql ( $sql_withdraw2 );
	if($withdraw1){
	  $log = new log();
	  $log->writelog($sql_withdraw1);
	  $log->writelog("success");
	}else{
	  $log = new log();
	  $log->writelog($sql_withdraw1);
	  $log->writelog("fail");
	}
	if($withdraw2){
	  $log = new log();
	  $log->writelog($sql_withdraw2);
	  $log->writelog("success");
	}else{
	  $log = new log();
	  $log->writelog($sql_withdraw2);
	  $log->writelog("fail");
	}
	if ($withdraw1 && $sql_withdraw2)
		return 1;
	else
		return 0;
}

/**
 * ***************修改主订单******************
 */
function revise_main($FOrderNumber, $FRemark) {
	global $db;
	$sql_revise_main = "update t_zhsp_OrderMain set FRemark='{$FRemark}',FDataFlag=1,FUpdateFlag=1 where FOrderNumber='{$FOrderNumber}'";
	$revise_main = $db->execsql ( $sql_revise_main );
	$sql_othersub="update t_zhsp_OrderSub set FDataFlag=1,FUpdateFlag=1 where FOrderNumber='{$FOrderNumber}'";
	$result_othersub=$db->execsql($sql_othersub);
	if($revise_main){
	  $log = new log();
	  $log->writelog($sql_revise_main);
	  $log->writelog("success");
	}else{
	  $log = new log();
	  $log->writelog($sql_revise_main);
	  $log->writelog("fail");
	}
	if($result_othersub){
	  $log = new log();
	  $log->writelog($sql_othersub);
	  $log->writelog("success");
	}else{
	  $log = new log();
	  $log->writelog($sql_othersub);
	  $log->writelog("fail");
	}
	if ($revise_main && $result_othersub)
		return 1;
	else
		return 0;
}

/**
 * ***************修改子订单******************
 */
// 数量$FQty
// 食品厂$FDBDesc在order_sub中即FAccountName，编号为FAccountId，为t_zhsp_AccountInfo外键，FAcountId
// 价格$FPrice
// 产品名称$FName
// 备注$FRemark
function revise_sub($FOrderNumber, $FID_sub, $FQty, $FPrice, $FRemark, $FAccountName, $FName, $FModel, $FSource, $FStatus) {
	global $db;
	// 根据食品厂名称得到食品厂ID
	$FDBDescArr = explode ( "--", $FAccountName );
	$FDBDesc = $FDBDescArr [0];
	$sql_Id = "select FID from t_zhsp_AccountInfo where FDBDesc='{$FDBDesc}'";
	$FAccountId = $db->execsql ( $sql_Id );
	foreach ( $FAccountId as $key => $V ) {
		$FAccountId = $V ['FID'];
	}
	
	/*
	 * //根据客户名称得到客户编号
	 * $sql_FCustomerNumber="select FNumber from t_zhsp_Customer where FName='{$FName}'";
	 * // var_dump($sql_FCustomerNumber);
	 * $FCustomerNumber=$db->execsql($sql_FCustomerNumber);
	 * foreach ($FCustomerNumber as $key=>$value){
	 * $FNumber=$value['FNumber'];
	 * }
	 */
	
	// 根据产品名称得到产品编号FProductNumber
	$sql_FProductNumber = "select FNumber from t_zhsp_product where FName='{$FName}' and FModel='{$FModel}'";
	
	$FProduct = $db->execsql ( $sql_FProductNumber );
	foreach ( $FProduct as $key => $V ) {
		$FProductNumber = $V ['FNumber'];
	}
	
	// 判断修改的商品是否重复
	$sql = "select FID from t_zhsp_OrderSub where FOrderNumber='{$FOrderNumber}' and FProductNumber='{$FProductNumber}' and FAccountId= '{$FAccountId}' ";
	$res = $db->execsql ( $sql );
	// echo $sql;
	$n = count ( $res );
	if ($n > 1) {
		$val = 0; // 商品重复，不能修改
	} else if ($n == 1) {
		// 修改的商品和修改前的相同
		if ($res [0] ['FID'] == $FID_sub) {
			$val = 1;
		}
	} else {
		$val = 1; // 本订单没有与修改的商品重复的子订单
	}
	if ($val == 1) {
		$sql_revise_sub = "update t_zhsp_OrderSub set FQty=" . $FQty . ",FPrice=" . $FPrice . ",FDataFlag=1,FUpdateFlag=1,FRemark='{$FRemark}'" . ",FProductNumber='{$FProductNumber}'" . ",FAccountId=" . $FAccountId . " where FID='{$FID_sub}'";
		$revise_sub = $db->execsql ( $sql_revise_sub );
		// echo $sql_revise_sub;
		//将同一订单中的其他子订单的数据改掉
		$sql_othersub="update t_zhsp_OrderSub set FDataFlag=1,FUpdateFlag=1 where FOrderNumber='{$FOrderNumber}'";
		$result_othersub=$db->execsql($sql_othersub);
		$sql_revise_main = "update t_zhsp_orderMain set FSource = $FSource,FDataFlag=1,FUpdateFlag=1,FStatus = $FStatus where FOrderNumber = '{$FOrderNumber}'";
		$revise_main = $db->execsql ( $sql_revise_main );
		// echo $sql_revise_main;
		if($revise_main){
		  $log = new log();
		  $log->writelog($sql_revise_main);
		  $log->writelog("success");
		}else{
		  $log = new log();
		  $log->writelog($sql_revise_main);
		  $log->writelog("fail");
		}
		if($result_othersub){
		  $log = new log();
		  $log->writelog($sql_othersub);
		  $log->writelog("success");
		}else{
		  $log = new log();
		  $log->writelog($sql_othersub);
		  $log->writelog("fail");
		}
		if ($revise_sub && $revise_main)
			return 1; // 修改成功
		else
			return 0; // 修改失败
	} else {
		return 2; // 商品重复
	}
}