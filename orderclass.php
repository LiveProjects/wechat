<?php
header ( 'content-type:text/html;charset=utf-8' );
require "dbaccess.php";

/*****************order_main的抽象类*****************/
abstract class order_main_ab {
	public $FOrderNumber;
	public $FCustomerNumber;
	public $FName;
	public $FEmployeeNumber;
	public $FDate;
	public $FSource;
	public $FRemark;
	public $FStatus;
	public $FPrice;
	public $suborders;
	Public function order_main() {
		$suborders = array ();
	}
	public function __construct($FOrderNumber, $FCustomerNumber, $FName, $FEmployeeNumber, $FDate, $FSource, $FRemark) {
		$this->FOrderNumber = $FOrderNumber;
		$this->FCustomerNumber = $FCustomerNumber;
		$this->FName = $FName;
		$this->FEmployeeNumber = $FEmployeeNumber;
		$this->FDate = $FDate;
		$this->FSource = $FSource;
		$this->FRemark = $FRemark;
	}
	abstract public function getOneMain($result, $k);
}

/*****************order_sub类*****************/
class order_sub {
	public $FAccountId;
	public $FAccountName; // 帐套名称
	public $FProductNumber;
	public $FName;
	public $FModel;
	public $FUnit;
	public $FQty;
	public $FPQty;
	public $FSendQty;
	public $FSendDate;
	public $FPrice;
	public $FPrice0;
	public $FDate;
	public $FAdviceConsignDate;
	public $FRemark;
	public function __construct($FAccountId,$FPQty, $FAccountName, $FProductNumber, $FName, $FModel, $FUnit,$FQty, $FPrice, $FDate, $FAdviceConsignDate, $FRemark) {
		$this->FAccountId = $FAccountId;
		$this->FAccountName = $FAccountName;
		$this->FProductNumber = $FProductNumber;
		$this->FName = $FName;
		$this->FModel = $FModel;
		$this->FUnit=$FUnit;
		$this->FQty = $FQty;
		$this->FPQty = $FPQty;
		// $this->FSendQty = $FSendQty;
		// $this->FSendDate = $FSendDate;
		$this->FPrice = $FPrice;
		$this->FDate = $FDate;
		$this->FAdviceConsignDate = $FAdviceConsignDate;
		$this->FRemark = $FRemark;
	}
}


/*****************根据业务员编号查询某一主订单信息*****************/
class order_main extends order_main_ab {
	public  function getOneMain($result, $k) {
		// 实例化数据库操作类
		$dbmain = new DB ();
		
		// 提取出第k个订单编号，通过订单FID查询各项信息
		$curFOrderID = $result [$k] ['FID'];
		$sql2 = "select a.FOrderNumber,a.FCustomerNumber,b.FName,a.FEmployeeNumber,convert(char,a.FDate,20) as FDate,a.FSource,a.FRemark from  t_zhsp_orderMain a inner join t_zhsp_Customer b on a.FCustomerNumber=b.FNumber where a.FID='$curFOrderID'";
		$result2 = $dbmain->execsql ( $sql2 );
// 		echo $sql2;
		if (empty($result2 [0] ['FRemark'])){
			$result2 [0] ['FRemark']='无备注信息';
		}
		foreach ( $result2 as $v )
			; // 将得到的二维数组转化为一维数组
		
				                            
		// 实例化数据库类，将查询到的信息存放到对象中
		$odm = new order_main ( $v ['FOrderNumber'], $v ['FCustomerNumber'], $v ['FName'], $v ['FEmployeeNumber'], $v ['FDate'], $v ['FSource'], $v ['FRemark'] );
		// var_dump($odm);
		// 获取最新的订单状态， 存到对象中
		$sql3 = "select FStatus from t_zhsp_order_status_notify where FOrderNumber = '{$v ['FOrderNumber']}'";
		$result3 = $dbmain->execsql ( $sql3 );
// 		echo $sql3;
// 		echo $result3 [0] ['FStatus'];
		$sql5 = "select FStatus from t_zhsp_orderMain where FID='$curFOrderID'";
		$result5 = $dbmain->execsql ( $sql5 );
// 		echo $result5 [0] ['FStatus'];
		
		if (empty($result3 [0] ['FStatus']))
			$odm->FStatus=$result5 [0] ['FStatus'];
		else $odm->FStatus=$result3 [0] ['FStatus'];
			// $odm->FStatus = $result3 [0] ['FStatus'];
		
		// 获取存放产品编码的该主订单的子订单数组，存到对象中
		$sql4 = "select FID from t_zhsp_OrderSub where FOrderNumber='{$v ['FOrderNumber']}'";
		$result4 = $dbmain->execsql ( $sql4 );
		$odm->suborders = $result4;
// 		echo $odm->suborders;
		
		//获取FSource值
		$sql6="select FSource from t_zhsp_orderMain where FID='$curFOrderID'";
		$result6 = $dbmain->execsql ( $sql6 );
		$odm->FSource = $result6[0]['FSource'];
		
// 		$odm->FPrice = getSub ( $odm );
		return $odm;
	}
}

/**********************查询某一子订单信息**********************/
function getOneSub($odmobj, $k) {
	$db_sub = new DB ();
// 	echo $odmobj->suborders;
	$dd = $odmobj->suborders [$k] ['FID']; // 获取某一子订单的产品编号
	                                                  
	// 查出自子订单的信息
// 	$sqlsub1 = "Select a.FAccountId,FPQty,FDBDesc,FProductNumber,b.FName,b.FModel,b.FUnit,FQty,FPrice,a.FRemark from (t_zhsp_OrderSub a inner join  t_zhsp_product b on a.FProductNumber = b.FNumber) inner join t_zhsp_AccountInfo c on a.FAccountId = c.FID where FOrderNumber='$odmobj->FOrderNumber' and FProductNumber='{$dd}'";
	$sqlsub1 = "Select a.FAccountId,FPQty,FDBDesc,FProductNumber,b.FName,b.FModel,b.FUnit,FQty,FPrice,a.FRemark from (t_zhsp_OrderSub a inner join  t_zhsp_product b on a.FProductNumber = b.FNumber) inner join t_zhsp_AccountInfo c on a.FAccountId = c.FID where a.FID='{$dd}'";
	$ressub1 = $db_sub->execsql ( $sqlsub1 );
	$ressub1[0]['FQty']=floor($ressub1[0]['FQty']*100)/100;
	$ressub1[0]['FPQty']=floor($ressub1[0]['FPQty']*100)/100;
	$ressub1[0]['FPrice']=floor($ressub1[0]['FPrice']*100)/100;
	if (empty($ressub1 [0] ['FRemark'])){
		$ressub1 [0] ['FRemark']='无备注信息';
	}
// 	echo $ressub1[0]['FPQty'];
/* echo "qty:".gettype($ressub1[0]['FQty']);
echo '</br>';
echo "price:".gettype($ressub1[0]['FPrice']);
echo "dbdesc:".gettype($ressub1[0]['FDBDesc']); */

	$sqlsub2 = "select FQty as FSendQty,convert(char,FDate,20) as FSendDate,FPrice as FPrice0 from t_zhsp_order_quan_notify where FOrderNumber = '{$odmobj->FOrderNumber}' and FProductNumber = '{$ressub1[0]['FProductNumber']}' and FAccountId = 1 ";
	$ressub2 = $db_sub->execsql ( $sqlsub2 );
// 	print_r($sqlsub2);
	$ressub2[0]['FSendQty']=floor($ressub2[0]['FSendQty']*100)/100;
	$ressub2[0]['FPrice0']=floor($ressub2[0]['FPrice0']*100)/100;
// 	echo $ressub2[0]['FPrice0'];
// 	print_r($ressub2);
// 	echo $sqlsub2;
	
	// 将查出的子订单信息存放到对象中
	$ods = new order_sub ( $ressub1 [0] ['FAccountId'],$ressub1[0]['FPQty'], $ressub1 [0] ['FDBDesc'], $ressub1 [0] ['FProductNumber'], $ressub1 [0] ['FName'], $ressub1 [0] ['FModel'], $ressub1[0]['FUnit'],$ressub1 [0] ['FQty'], $ressub1 [0] ['FPrice'], $ressub1 [0] ['FDate'], $ressub1 [0] ['FAdviceConsignDate'], $ressub1 [0] ['FRemark'] );
	if (empty($ressub2 [0] ['FSendDate'])){
		$ods->FSendDate='无发货时间信息';
	}else {
		$ods->FSendDate = $ressub2 [0] ['FSendDate'];
	}
	if (empty($ressub2 [0] ['FSendQty'])){
		$ods->FSendQty='无发货数量信息';
	}else {
		$ods->FSendQty = $ressub2 [0] ['FSendQty'];
	}
	if (empty($ressub2 [0] ['FPrice0'])){
		$ods->FPrice0='无发货价格信息';
	}else {
		$ods->FPrice0 = $ressub2 [0] ['FPrice0'];
	}
// 	echo $ods->FPrice0;
// 	print_r($ods);
	return $ods;
}

/*****************查询所有的子订单，用于主订单总价的计算*****************/
/* function getSub($odmobj) {
	$num_sub = count ( $odmobj->suborders ); // 计算某一主订单的子订单数目	
	$price = 0; // 定义变量，存储订单总价
	            
	// 循环获取每一个子订单的信息
	for($k = 0; $k < $num_sub; $k ++) {
		$ods = getOneSub ( $odmobj, $k ); // 第k个子订单的显示函数
		$pricesub = $ods->FQty * $ods->FPrice;
		$price += $pricesub;
	}
	return $price;
} */

