
<!DOCTYPE html>
<html lang="zh">
<head>
<script type="text/javascript">
var flag =true;
function checksubmit(){
if(!flag){
    alert("提交两次");
	return false;
  
  }
  flag=false;
   $('#ordermain').attr('disabled','disabled');
   return true;
  
 
  //$('#ordermain').hide();
  
  //$('#form1').submit();
  //document.getElementById("form1").submit();
  //document.form1.submit();
  //document.form1.submit();
   
}

</script>
<style>
li {
list-style-type:none;
}
</style>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="IE=5; IE=8">
<link rel="stylesheet" href="css/index.css" type="text/css">
<link rel="stylesheet" href="css/check.css" type="text/css">
<script type="text/javascript" src="bootstrap/js/jquery-2.1.1.min.js"></script>
<script type="text/javascript" src="js/editmode.js"></script>
<script type="text/javascript" src="js/editchangjia.js"></script>
<script type="text/javascript" src="js/kehu.js"></script>
<title>查看加入订单</title>
</head>
<body>
    <div id="top-face" ></div>
    <div id="ajax"></div>
	<nav id="top">
		查看加入订单<b onclick=location.reload();></b><i></i>
		<script>//返回按钮设置
		$("#top i").click(function(){
			history.back();
		})</script>
	</nav>
	<div
		class="container col-lg-10 col-lg-offset-1 col-md-10 col-md-offset-1 col-sm-12 col-xs-12"
		id="check-main">
		
		<?php
		require 'dbaccess.php';
//开启session，并获值
		session_start ();
		$user_id = $_SESSION["order_openid"];
	    unset($_SESSION["order_openid"]);
		//若session不为空，则显示订单列表
		if (empty ( $_SESSION )) {
		    $_SESSION["order_openid"] = $user_id;
			die("<center><h1>您的订单为空</h1></center>") ;
			
		} else {
			$_SESSION["order_openid"] = $user_id;
			//将session里面的多个订单信息，循环显示到界面上
			$index = 0;
			//print_r($_SESSION);
			foreach ( $_SESSION as $K=>$v ){
				//拆分字符串，获得食品厂名称
// 				$data1=explode('--', $_SESSION[$K]['FDBDesc']);
// 				$fdb=$data1[0];
				if($K == "order_openid")
				  continue;
				$namemodel=$_SESSION[$K]['namemodel'];
				//if($namemodel != ""){
				$data=explode('--', $namemodel);
				$name=$data[0];
				$model=$data[1];
				
				$sql="select FNumber,FUnit from t_zhsp_product where FName='{$name}' and FModel='{$model}'";
				$db=new DB();
				$res=$db->getRow($sql);
				$str1 = "<dl>";
				echo $str1;
				$index2 = 0;
				foreach ($_SESSION[$K]['FAvlQty'] as $kc){
					$kc['FAvlQty']=round($kc['FAvlQty'],2);
					$kcs.="<input value='".$kc['FDBDesc'].":".$kc['FAvlQty'].$kc['FUnit']."' disabled>";
					$FDB[$index][$index2]=$kc['FDBDesc'];
					$index2++;
				}
				$index++;
				
				$str2 = "<div class='top' id='$K'>
				<h4>产品编号：".$res['FNumber']."</h4>
				<ul>
		          <li style='position:relative;' class='list1' ><label for=''>产品规格：</label><input id='suggest_input$K'  type='text'  onfocus=mode('#suggest_input$K','#suggest_ul$K') value='".$_SESSION[$K]['namemodel']." 'disabled required>
		          	<ul id='suggest_ul$K' class='suggest_ul' style='background:#D2E9FF;z-index:10000000'>
		            
		            </ul>
		           </li>
					<li  class='list1'><label for=''>订货数量：</label><input type='text' value='".$_SESSION[$K]['FQty']."{$res['FUnit']}' disabled  required></li>
					<li  class='list1'><label for=''>产品单价：</label><input type='text' value='".$_SESSION[$K]['FPrice']."元' disabled  required placeholder='以 元 为单位'></li>
					<li  class='list1' style='position:relative;display:none'><label for=''>食品厂：</label><input type='text' id='suggest_inputc$K' onkeydown=changjia('#suggest_inputc$K','#suggest_ulc$K','#suggest_input$K') onblur='blur()' value='".$fdb."' disabled  required>
				    <ul id='suggest_ulc$K' class='suggest_ulc'  style='background:#D2E9FF;'>
		            </ul>
				    </li>
					<li  class='list1'><label for=''>备注：</label><input type='text' value='".$_SESSION[$K]['FRemark']."' disabled></li>
					<li  class='list1' id='kc{$K}'><label for=''>库存：</label>$kcs</li>
				</ul>
				
				<span class='bottom'>
					<a href='javascript:void();' onclick=del('{$K}')><button>删除</button></a>
					
                    <a href='javascript:void();' onclick=modify('{$K}')><button>修改</button></a>
				</span>
				</div>";
				$kcs=null;
				echo $str2;
				$str3 = "</dl>";
				echo $str3;
				//}
			}
		}
		?>
		<?php
		
		//echo "test".count($FDB,0);
		//print_r($FDB);
		//print_r($FDB[0]);
		$rdb = array();
		  for($i=0;$i<count($FDB,0);$i++){
		  //foreach ($FDB as $v){
		  //print_r($FDB[$i]);
		  
		    if($i==0)
		      $rdb = $FDB[$i];
		    else
		      $rdb = array_intersect($rdb,$FDB[$i]);
		      
		  }	
		  //echo "db:".count($rdb);
		 //print_r($rdb);
		 /**
		  for($j=0;$j<count($rdb);$j++){
		  	echo $rdb[$i]."<br>";
		  	
		  }
		  **/

		?>
	</div>
	<!-- 完成订单 -->
    <div id="searchSuggest" class="finish" >
	    <form action="orderdeal.php" method="post" >
	     <?php //$sql="SELECT FDBDesc FROM t_zhsp_AccountInfo"; $Fdbs=$db->execsql($sql);?>
		    <div>
		    	<label>食品厂:</label>
		    	<select name="FDBDesc">
		    	<?php 
		    	//var_dump($rdb);
		    	$rdb=array_values($rdb);
		    	if(count($rdb) == 0)
		    		echo "<option value=''>没有可选的食品厂</option>";
		    	for($i=0;$i<count($rdb);$i++){
		    		
		    	 echo "<option value='".$rdb[$i]."'>".$rdb[$i]."</option>";
		    		
		    	}
		    	
		    	?>
		    	
		    	</select>
		    	<?php //print_r($FDB);?>
			   
		    </div>
		    <div>
		    	<label>客户:</label>
			    <input type="text" name="FName" id="suggest_inputk" autocomplete="off"/ >
                <!-- 模糊搜索结果展示块 -->
			    <ul id="suggest_ulk" style="z-index:10000000">
		        </ul>
		    </div>
		    <div>
		        <label for="">备注:</label>
			    <input type="text" name="FMRemark">
			</div>
	        <div id="btncheck">
		    	<input type="submit" name="ordermain" onclick="return checksubmit():" value="提交订单">
		    </div>
	    </form>
    </div>
<!-- 点击提交订单 -->
	    <center>
		    <button id="sub"  class='bottombtn' onclick="finish()"><font id="eric" size=5" >完成订单</font></button>
	    </center>
	    <div id="ajax"></div>
 <!-- 点击删除按钮 -->
       <script>

       $(".list1").find("input").focus(function(){
             $(this).val("");
           })
    function del(id){  
    	 confirm_ = confirm('确定删除此订单吗？');
    	 if(confirm_){
             $.ajax({
                 type:"POST",
                 url:'ajax.php?act=del&id='+id,
                 success:function(data){
                    $("#del"+id).remove();
                    (data == true) ? $("#"+id).remove() :  alert("删除失败");
                 }
             });
         }
       
    }
    </script>
    <!-- 点击完成订单 -->
    <script>
    /***********************************input样式*********************************/
    function asd(){
    	$(".top ul li:last-child").find("input").eq(0).nextAll();
    	}
    asd();
    /***********************************弹框显示*********************************/
    $("dl div li:nth-child(5)").delegate(this,'click',function(e){
        //alert(1232321);
    	 if($(this).find('input').attr('disabled')=="disabled"){
    		 e.stopPropagation();//冒泡兼容问题
    		 var ss="";
    		 var leng=$(this).find("input").length;
    		 for(var i=0;i<leng;i++){
    			 var a=$(this).find("input").eq(i).val();
    			 //var br=&lt;br/&gt;
    			 ss=ss+a;
        		 }
    		 //alert(ss);
    		 $("#top-face").css('display','block').text(ss);
    		 $("#top-face").siblings().css('opacity','0.4');
    	 }
	 })
	 $("dl div li:nth-child(6)").delegate(this,'click',function(e){
        //alert(1232321);
    	 if($(this).find('input').attr('disabled')=="disabled"){
    		 e.stopPropagation();//冒泡兼容问题
    		 var ss="";
    		 var leng=$(this).find("input").length;
    		 for(var i=0;i<leng;i++){
    			 var a=$(this).find("input").eq(i).val();
    			 //var br=&lt;br/&gt;
    			 ss=ss+a;
        		 }
    		 //alert(ss);
    		 $("#top-face").css('display','block').text(ss);
    		 $("#top-face").siblings().css('opacity','0.4');
    	 }
	 })
	 $("#top-face").click(function(){
		 $("#top-face").fadeOut(200);
		 $("#top-face").siblings().css('opacity','1');
	 })
	 
    function finish(){
      $(".finish").css("display","block");
      $("#sub").css("display","none");
    }
   
    function modify(id){
        //$("#"+id+" ul li input").removeAttr("disabled");
    	var name=$("#"+id+" span").find("button").eq(1);
    	
    	if(name.text()=="修改"){
    		$("#"+id+" ul li input").removeAttr('disabled');
    		$("#"+id+" ul li:last-child").find("input").attr('disabled','disabled');
            name.text("完成");
        	}else if(name.text()=="完成"){
        		//$("#"+id+" ul li input").attr('disabled','disabled');
        		var use=$("#"+id+" ul li[class='list1']");
        		var that=$(this);
        		$.ajax({
                    url:'ajax.php?act=mod&id='+id,
                    Type:'POST',
                    dataType:'text',
                    data:{"val1":use.eq(0).find("input").val(),
                        "val2":use.eq(1).find("input").val(),
                        "val3":use.eq(2).find("input").val(),
                        "val4":use.eq(3).find("input").val(),
                        "val5":use.find("input").eq(4).val()},
                    success:function(data){
                           if(data){
                                 alert("修改成功");
                                 alert(data);
                                 console.log("123321"+data);
                                 var v=data.indexOf("<");
                                 console.log("索引："+v);
                                 console.log("差值"+val);
                                 var val="产品编号："+data.substr(0,v);
                                 adddan(id,val);
                                 var val1=data.substring(v);
                                 $("#kc"+id).html(val1);
                                 asd();
                                 $("#"+id+" ul li input").attr('disabled','disabled');
                               }else{
                            	   alert("修改失败，请检查产品规格输入是否正确");
                            	   name.text("完成");
//                             	   $("#"+id+" ul li input").attr('disabled','disabled');
                               }
                        }
            		});
                name.text("修改");
            	}        
    }
    //修改单号
    function adddan(dan,val){
 	   var s=document.getElementById(dan);
 	   console.log(s.childNodes[1].nodeName);
 	   s.childNodes[1].childNodes.item(0).nodeValue=val;
 	   console.log("h4的值为"+s.childNodes[1].childNodes[0].nodeValue);
    }
    /***********************************动态显示*********************************/
    $(document) .delegate("dl div.top ul li:nth-child(2) input",'focus',function(){
         var val=$(this).parent().prev().find("input").val();
         console.log(val);
         var that=$(this);
         $.ajax({
             url:'ajax.php?act=FUnit',
             dataType:'text',
             Type:'POST',
             data:{"val":val},
             success:function(data){
                 that.attr('placeholder',"单位为"+data);
                 var len=data.length;
                 window.o=data;
                 
                 },
             error:function(err){
                 alert(err.message);
                 }
             })
        })
      $("dl div.top ul li").eq(1).find("input").blur(function(){
           var va=$(this).val();
           $(this).val(va+o);
//            o=null;
        })
      $("dl div.top ul li").eq(2).find("input").blur(function(){
           var va=$(this).val();
           $(this).val(va+"元");
        })
   
</script>
</body>
</html>