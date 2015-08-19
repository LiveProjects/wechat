<?php
header ( 'content-type:text/html;charset=utf-8' );
session_start ();
require 'dbaccess.php';
$db = new DB ();
/**
 * *****************点击加入订单************************************
 */
if (! empty ( $_POST ['namemodel'] ) && ! empty ( $_POST ['FQty'] ) && ! empty ( $_POST ['FPrice'] )) {
	/**
	 * ***********检测产品规格、帐套名称输入是否正确*****************
	 */
	if (strstr ( $_POST ['namemodel'], "--" )) {
		$data = explode ( "--", $_POST ['namemodel'] );
		$FName = trim($data [0]); // 产品名称  去除空格
		$FModel = trim($data [1]); // 产品规格 去除空格
		$sql = "SELECT FNumber,FUnit FROM t_zhsp_product WHERE FName = '{$FName}' AND FModel = '{$FModel}'";
		$FNumberArr = $db->getRow ( $sql );
		( empty ( $FNumberArr ))  ? $arr ['mes'] = 0 : $arr ['mes'] = 1;
		//如果有产品编号，查询生产此产品的工厂
		$sql="SELECT p.FAccountId,a.FDBDesc FROM t_zhsp_product as p inner join t_zhsp_AccountInfo as a ON p.FAccountId=a.FID WHERE FNumber='{$FNumberArr['FNumber']}'";
		$rows=$db->execsql($sql);
		//得出产品的库存
		foreach ($rows as $v){
			$kq_sql="select  FAvlQty,FUnit from  t_zhsp_inventory where FAccountId='{$v['FAccountId']}' and FItemNo='{$FNumberArr['FNumber']}'";
			$row=$db->getRow($kq_sql);
			if (empty($row)){
				$row['FDBDesc']=$v['FDBDesc'];
				$row['FAvlQty']=0;
				$row['FUnit']='库存';
			}
			$row['FDBDesc']=$v['FDBDesc'];
			$FAvlQty[]=$row;
		}
		$_POST['FAvlQty']=$FAvlQty;
	} else {
		$arr ['mes'] = 0;
	}
	if ($arr ['mes'] == 0) {
		$arr ['mes'] = "产品规格输入错误";
		echo json_encode ( $arr );
	} else {
		//去除输入的空格
		$_POST['namemodel']=trim($_POST['namemodel']);
		$_POST['FQty']=trim($_POST['FQty']);
		$_POST['FPrice']=trim($_POST['FPrice']);
// 		$_POST['FDBDesc']=trim($_POST['FDBDesc']);
		$_POST['FRemark']=trim($_POST['FRemark']);
		$_POST['FUnit']=trim($FNumberArr['FUnit']);
		
		$newpost = array();
		
		//$newpost['namemodel'] = "111";
		
		$newpost['namemodel'] = $_POST['namemodel'];
		$newpost['FQty'] = $_POST['FQty'];
		$newpost['FPrice'] = $_POST['FPrice'];
		$newpost['FRemark'] = $_POST['FRemark'];
		$newpost['FUnit'] = $_POST['FUnit'];
		$newpost['FAvlQty'] = $_POST['FAvlQty'];
		$newpost['FName'] = $_POST['FName'];
		
		
		if (!empty($_SESSION)){
			$num=count($_SESSION);
			foreach ( $_SESSION as $k=>$v ){
				if (($newpost['namemodel']===$_SESSION[$k]['namemodel'])){
					$flag=1;
					$arr['mes']="加入失败，商品重复";
					echo json_encode ( $arr );
					break;
					
				}
			}
			if ($flag!=1){
				//加入session
				$_SESSION [uniqid(true)] = $newpost;
				$arr ['mes'] = "加入成功";
				echo json_encode ( $arr );
			}
		}else {
			//加入session
			$_SESSION [uniqid(true)] = $newpost;
			$arr ['mes'] = "加入成功";
			echo json_encode ( $arr );
		}
		
	}
}
/**
 * *****************点击提交订单************************************
 */
if (! empty ( $_POST ['ordermain'] )) {
	if($_POST['FDBDesc']==null){
		die("<script>alert('提交条件不符合，请重新提交！');history.go(-1)</script>");
	}
	//$FEmployeeNumber="0001";
	$FEmployeeNumber = $_SESSION["order_openid"]; //added by sunhaibin,2015,5,31,20:29
	
	$user_id = $_SESSION["order_openid"];
	unset($_SESSION["order_openid"]);
	// 判断是否存在子订单
	if (empty($_SESSION)){
	    $_SESSION["order_openid"] = $user_id;
		die ( "<script>alert('请先加入子订单');window.location='check.php'</script>" );
		return;
		
		}
	// 判断客户输入是否正确
	$FName=trim($_POST['FName']);//去除输入的空格
	$FNumbersql = "SELECT FNumber FROM t_zhsp_Customer WHERE FName='{$FName}'";
	$FNumberArr = $db->getRow ( $FNumbersql );
	if (empty ( $FNumberArr )){
	$_SESSION["order_openid"] = $user_id;
	    die ( "<script>alert('客户输入不正确');window.location='check.php'</script>" );
		}
	// 得到客户的编码
	$FNumber = $FNumberArr ['FNumber'];
	// 得到员工编码
	
	// 生成唯一订单编号
	$now=date("Y-m-d",time());
	$from = $now." 00:00:00";
	$to = $now." 23:59:59";
// 	$datefg=explode(" ", $now);
// 	echo $datefg[0];
	//$countsql="SELECT count(FID) as number FROM t_zhsp_orderMain WHERE FEmployeeNumber = '".$FEmployeeNumber."' and FDate >= '".$from."' and FDate <= '".$to."'";
 	//echo $countsql;die;
	$countsql="SELECT FOrderNumber FROM t_zhsp_orderMain WHERE FEmployeeNumber = '".$FEmployeeNumber."' and FDate >= '".$from."' and FDate <= '".$to."' order by FOrderNumber desc";
	
	$rows= $db->getRow($countsql);
	if(count($rows) > 0){
		$temp = $rows['FOrderNumber'];
		$temp = substr($temp,-3);
		//echo "temp:".$temp;
		//echo "temp int".(int)$temp;
		//die();
		$oid = ((int)$temp)+1;
		
	}else{
		$oid = 1;
	}
	
	//$oid=$rows['number']+1;
	if ($oid<10){
		$oid="00".$oid;
	}elseif ($oid>=10 && $oid<100){
		$oid="0".$oid;
	}else {
		$oid=$oid;
	}
	//echo "oid:".$oid;
	//die();
	$ordernum = date("Ymd",time()).$FEmployeeNumber.$oid;
	// 生成交货日期，延期5天
	$date = time () + 5 * 34 * 3600;
	// 提交订单结果
	$res = array ();
	/**
	 * *************根据帐套名称得到帐套编号******************
	 */
	$FDBDesc=trim($_POST['FDBDesc']);//去除输入空格
	$FIdsql = "SELECT FId FROM t_zhsp_AccountInfo WHERE FDBDesc='{$FDBDesc}'";
	$FIdArr = $db->getRow ( $FIdsql );
	$FAccountId = $FIdArr ['FId'];
	//数据提交到子订单
	$suborder_fail = false;
	foreach ( $_SESSION as $v ) {
	
	 //if(!array_key_exists("namemodel",$temp_array)
		//   continue;
		/**
		 * *************判断是否交给计划部处理******************
		 */
		
		   
		if (!strstr ( $_POST ['FDBDesc'], "调理品" )) {
			switch ($v ['FUnit']) {
				case "公斤" :
					$v ['FQty'] >= 1000 ? $FSourceArr []  = 2 : $FSourceArr [] = 1;
					break;
				case "吨" :
					$v ['FQty'] >= 1 ? $FSourceArr [] = 2 : $FSourceArr [] = 1;
					break;
				case "斤" :
					$v ['FQty'] >= 2000 ? $FSourceArr []=2 : $FSourceArr [] = 1;
					break;
				case "千克" :
					$v ['FQty'] >= 1000 ? $FSourceArr [] = 2 : $FSourceArr [] = 1;
					break;
				default :
					break;
			}
		} else {
			$FSourceArr [] = 2;
		}
		/**
		 * *************根据产品名称和产品规格得到产品编号******************
		 */
		$data = explode ( "--", $v ['namemodel'] );
		$FName = trim($data [0]); // 产品名称  去除空格
		$FModel = trim($data [1]); // 产品规格 去除空格
		$sql = "SELECT FNumber FROM t_zhsp_product WHERE FName = '{$FName}' AND FModel = '{$FModel}'";
		$FNumberArr = $db->getRow ( $sql );
		$FProductNumber = $FNumberArr ['FNumber']; // 得到产品编号
		/**
		 * *************构造插入数据库中的数据结构******************
		 */
		$v ['FAccountId'] = $FAccountId;
		$v ['FProductNumber'] = $FProductNumber; // 产品编号
		$v ['FOrderNumber'] = $ordernum; // 订单号
		//$v ['FDate'] = date ( "Y-m-d H:i:s", $date ); // 交货日期
		$v ['FDate'] = date ( "Y-m-d", $date ); // 交货日期，只精确到天
		//$v ['FAdviceConsignDate'] = date ( "Y-m-d H:i:s", $date ); // 建议交货日期
		$v ['FAdviceConsignDate'] = date ( "Y-m-d", $date ); // 建议交货日期，只精确到天
		$v ['FDataFlag'] = 1; 
		$v ['FUpdateFlag'] = 1; 
		unset ( $v ['namemodel'] );
		unset ( $v ['FName'] );
		unset($v['FAvlQty']);
		unset($v['FUnit']);
		/**
		 * *************将数据插入子订单数据库******************
		 */
		$resultsub = $db->insert ( "t_zhsp_OrderSub", $v ); // 将子订单内容插入数据库
		if ($resultsub != false) {
			$res [] = 1;
		} else {
			$res [] = 0;
			$suborder_fail = true;
			break;
			//echo "子订单失败";
			//die();
		}
	}
	/**
	 * *************构造主订单数据结构******************
	 */
if (!$suborder_fail && !empty($_SESSION)){
	$m = array ();
	in_array ( 2, $FSourceArr ) ? $m ['FSource'] = 2 : $m ['FSource'] = 1;
	$m ['FCustomerNumber'] = $FNumber;
	//$m ['FDate'] = date ( "Y-m-d H:i:s", time () );
	$m ['FDate'] = date ( "Y-m-d", time () ); //订单日期，只精确到天
	$m ['FOrderNumber'] = $ordernum;
	$m ['FAccountId'] = $FAccountId;
	$m ['FEmployeeNumber'] = $FEmployeeNumber;    //员工编号
	$m ['FDataFlag'] = 1;
	$m ['FUpdateFlag'] = 1;
	if (! empty ( $_POST ['FMRemark'] )) {
		$m ['FRemark'] = trim($_POST ['FMRemark']);
	}
	/**
	 * *************构造状态表数据结构******************
	 */
	$m ['FSource'] == 1 ?  $m ['FStatus'] = 1 : $m ['FStatus'] = 0;
	/**
	 * *************判断主订单是否提交成功******************
	 */
	if ($db->insert ( "t_zhsp_OrderMain", $m )) {
		$res [] = 1;
	} else {
		$res [] = 0;
		//echo "主订单失败";
			//die();
	}
}
	/**
	 * *************判断主、子订单是否都提交成功******************
	 */
	if (in_array ( 0, $res )) {
		//echo "<script>alert('订单提交失败');history.go(-1)</script>";
		echo "<script>alert('订单提交失败，请重新下单');window.location='check.php'</script>";
		session_destroy ();
		
	} else {
		echo "<script>alert('订单提交成功');window.location='order.php'</script>";
		session_destroy ();
	}
	$_SESSION["order_openid"] = $user_id;
}
?>