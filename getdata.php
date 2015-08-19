<?php
if (! empty ( $_GET ['flag'] )) {
	productdata ( $_GET ['flag'] );
} else {
}
/**
 * 通过ajax得到数据
 *
 * @param int $flag        	
 */
function productdata($flag) {
	header ( 'content-type:text/html;charset=utf-8' );
	require "dbaccess.php";
	$db = new DB (); // 实例化数据库类
	                 // 如果是得到商品规格数据
	if ($flag == 1) {
		// 商品规格搜索
		$keywords = $_POST ['keywords'];
		// 根据输入的字符和数据库里的数据进行匹配
		$sql_product = "select distinct TOP 10 FName,FModel from  t_zhsp_product where FName like '%" . $keywords . "%' ";
		$res_product = $db->execsql ( $sql_product );
		
		$num_product = count ( $res_product ); // 计算匹配的产品规格的数量
		                                       
		// 先得出第一条结果，再依次得出后面的结果
		$result = "[{'keywords':'" . $res_product [0] ['FName'] . '--' . $res_product [0] ['FModel'] . "'}";
		for($i = 1; $i < $num_product; $i ++) {
			$result .= ",{'keywords':'" . $res_product [$i] ['FName'] . '--' . $res_product [$i] ['FModel'] . "'}";
		}
		$result .= ']';
		
		echo $result;
		
		// 如果得到厂家数据
	} elseif ($flag == 2) {
		// 商品规格搜索
		$arr = $_POST;
		$data1 = explode ( "|", $arr ['FDBDesc'] );
		$res1 ['namemodel'] = $data1 [1]; // 得到产品规格框的输入值
		
		$data2 = explode ( "--", $res1 ['namemodel'] );
		$res_pro ['FName'] = $data2 [0]; // 得到产品名称
		$res_pro ['FModel'] = $data2 [1]; // 得到规格名称
		                                  
		// 根据产品规格得到产品编码
		$sql_pronum = "select FNumber from t_zhsp_product where FName='{$res_pro['FName']}' and FModel='{$res_pro['FModel']}'";
		$res_pronum = $db->execsql ( $sql_pronum );
		
		// 根据产品编码在t_zhsp_product中找到生产该产品的食品厂
		$sql_accid = "select FAccountId from  t_zhsp_product where FNumber='{$res_pronum[0]['FNumber']}'";
		$res_accid = $db->execsql ( $sql_accid );
		$num_acc = count ( $res_accid ); // 计算匹配的食品厂的数量
		                                 
		// 根据查出的食品厂id得到食品厂名称和即时库存
		$sql_ivt = "select  FAvlQty,FUnit from  t_zhsp_inventory where FAccountId='{$res_accid [0] ['FAccountId']}' and FItemNo= '{$res_pronum[0]['FNumber']}'";
		$res_ivt = $db->execsql ( $sql_ivt );
		$sql_acc = "select FDBDesc from  t_zhsp_AccountInfo where FID='{$res_accid[0]['FAccountId']}'";
		$res_acc = $db->execsql ( $sql_acc );
		if (! empty ( $res_ivt )) {
			$res_ivt [0] ['FAvlQty'] = floor ( $res_ivt [0] ['FAvlQty'] * 100 ) / 100;
			
			$result = "[{'keywords':'" . $res_acc [0] ['FDBDesc'] . '--' . $res_ivt [0] ['FAvlQty'] . $res_ivt [0] ['FUnit'] . "'},";
		} else {
			$a [0] = $res_acc [0] ['FDBDesc'];
			$result="[";
		}
		// 先得出第一条结果，再依次得出后面的结果
		for($i = 1; $i < $num_acc; $i ++) {
			$sql_acc = "select FDBDesc from  t_zhsp_AccountInfo where FID='{$res_accid[$i]['FAccountId']}'";
			$res_acc = $db->execsql ( $sql_acc );
			$sql_ivt = "select  FAvlQty,FUnit from  t_zhsp_inventory where FAccountId='{$res_accid [$i] ['FAccountId']}' and FItemNo= '{$res_pronum[0]['FNumber']}'";
			$res_ivt = $db->execsql ( $sql_ivt );
			if (! empty ( $res_ivt )) {
				$res_ivt [0] ['FAvlQty'] = floor ( $res_ivt [0] ['FAvlQty'] * 100 ) / 100;
				$result .= "{'keywords':'" . $res_acc [0] ['FDBDesc'] . '--' . $res_ivt [0] ['FAvlQty'] . $res_ivt [0] ['FUnit'] . "'}";
				if ($i!=$num_acc-1)  $result .=",";
			} else {
				$a [$i] = $res_acc [0] ['FDBDesc'];
			}

			if(($i==$num_acc-1)&(!empty($a))) $result .=",";
		}
		if (! empty ( $a )) {
			for($k = 0; $k < $num_acc; $k ++) {
				if (! empty ( $a [$k] )) {
					$result .= "{'keywords':'" . $a [$k] . '--' . '0' . "'}";
				}
				if ($k!=$num_acc-1) $result .=",";
			}
		}
		$result .= ']';
		
		echo $result;
	} else {
		$keywords = $_POST ['keywords'];
		$sql = "select TOP 10 FName from  t_zhsp_Customer where FName like '%" . $keywords . "%' ";
		// echo $sql;
		$rows = $db->execsql ( $sql );
		$result = '';
		foreach ( $rows as $row ) {
			$result .= ",{'keywords':'" . $row ['FName'] . "'}";
		}
		$result .= ']';
		$result_sub = substr ( $result, 1 );
		$result = "[" . $result_sub;
		echo $result;
	}
}
?>

