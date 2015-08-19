<?php 
session_start ();
//echo "session openid:".$_SESSION['order_openid'];
$_SESSION['order_openid']= '0001';//for test 
require_once("getuser.php");

//echo "openid:".$openid;//test

?>
<!DOCTYPE html>
<html lang="zh">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="IE=5; IE=8">
<title>下订单</title>
<link rel="stylesheet" href="css/index.css" type="text/css">
</head>
<body>
<nav id="top">下订单<i></i></nav>
   <div id="searchSuggest" >
		<form id="suggest_form" action="orderdeal.php" method="post">
			<div><label>产品规格:</label>
			     <input type="text" name="namemodel" id="suggest_input"  autocomplete="off"/ >
                 <!-- 模糊搜索结果展示块 -->
			     <ul id="suggest_ul" class="suggest_ul" >
		         </ul>
			</div>
	        <div id="num">
		        <label>订货数量:</label> 
		        <input type="text" style="width: 30%;"  onfocus=getproduct() name="FQty" id="FQty">
		        <b></b>
	        </div>
	    	<div id="num1"><label>产品单价:</label> <input type="text" style="width: 30%;"  placeholder="以'元'为单位" name="FPrice" id="FPrice"></div>
	    	<div style="display: none"><label>食品厂:</label>
	    	    <select  id="sel">
	    	    <option value=''>请选择一个食品厂</option>
	    	    </select>
			</div>
	    	<div><label for="">备注:</label>
	    		 <input type="text" name="FRemark"></div>
		    <div id="btn">
		    	<input type="submit" name="suborder" id="suborder" value="加入订单">
		    	<a href="check.php" >查看订单</a>
		    	<span id="msg"></span>
		    </div>
	    </form>
	    <div id="output"></div>
    </div>
    <div id="ajax"></div>
</body>
<script type="text/javascript" src="js/jquery-1.7.2.min.js"></script>
<script type="text/javascript" src="js/jquery.form.min.js"></script>
<script type="text/javascript" src="js/searchSuggest.js"></script>
<script type="text/javascript" src="js/changjia.js"></script>
<script type="text/javascript" src="js/kehu.js"></script>
<script type="text/javascript">
$("#searchSuggest div").eq(0).find("input").focus(function(){
	$(this).next().hide();
	//alert(123);
	$(this).val("");
})
$("#top i").click(function(){
	history.back();
})
$("#searchSuggest div:nth-child(2)").find("input").focus(function(){//this调用方法的对象
	var val=$(this).parent().prev().find("input").val();
	console.log(val);
	if(val!=null){
         $(this).parent().next().next().find("option").eq(0).text(val);
         var fun=$(this).parent().prev().find("input");
          $.ajax({
               url:'ajax.php?act=accountInfo',
               Type:'POST',
               dataType:'text',
               data:{"val":val},
               success:function(data){
            	   $("#sel").empty();
            	   $("#sel").append(data);
                },
                error:function(){
                   alert("error");    
                }
              })
		}
})
$(function(){
	var options = { 
        beforeSubmit:  showRequest,  // pre-submit callback 
        success:       showResponse,  // post-submit callback
		resetForm: true, 
		dataType:  'json' 
    }; 
    // bind to the form's submit event 
    $('#suggest_form').submit(function() { 
        $(this).ajaxSubmit(options); 
        return false; 
    }); 
});
// pre-submit callback 
function showRequest(formData, jqForm, options) { 
	var suggest_input = $("#suggest_input").val();
	if(suggest_input==""){
		$("#msg").html("产品规格不能为空");
		return false;
	}
	var FQty = $("#FQty").val();
	if(FQty==""){
		$("#msg").html("订货数量不能为空");
		return false;
	}
	var FPrice = $("#FPrice").val();
	if(FPrice==""){
		$("#msg").html("产品单价不能为空");
		return false;
	}
	//var num = new RegExp("^[0-9]*$"); 
	var num = new RegExp("^(([0-9]+\.[0-9]*[1-9][0-9]*)|([0-9]*[1-9][0-9]*\.[0-9]+)|([0-9]*[1-9][0-9]*))$");
	if(!num.test(FQty)){
		$("#msg").html("订货数量请输入纯数字");
		return false;
	}
	if(!num.test(FPrice)){
		$("#msg").html("产品单价请输入纯数字");
		return false;
	}
	$("#msg").html("正在提交...");
    return true; 
} 
// post-submit callback 
function showResponse(responseText, statusText)  { 
	$("#msg").html(responseText.mes); 
	/* 清空文本框的值 */
	$("input text").val="";
} 
function getproduct(){
	var val=$("#suggest_input").val();
	 $.ajax({
         url:'ajax.php?act=product',
         Type:'POST',
         dataType:'text',
         data:{"val":val},
         success:function(data){
        	 $("#num").find("b").empty();
             $("#num").find("b").append(data);
          },
        })
}
</script>
</html>