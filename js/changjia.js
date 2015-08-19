// JavaScript Document

$(function(){
		//载入时隐藏下拉li
		$("#suggest_ulc").hide(0);
});
//Ajax 动态获取关键字
$(function(){
					 
	//监听文本框输入变化
	$("#suggest_inputc").bind('input propertychange',function(){
		//创建ajax对象函数
		function createLink(){
			if(window.ActiveXObject){
				var newRequest = new ActiveXObject("Microsoft.XMLHTTP");
			}else{
				var newRequest = new XMLHttpRequest();
			}
			return newRequest;
		}
		
		//如果文本框为空，不发送请求
		if($("#suggest_inputc").val().length==0){
			$("#suggest_ulc").hide(0);
			return;
		}
		//发送请求
		http_request = createLink();//创建一个ajax对象
		if(http_request){
			var sid = $("#suggest_inputc").val();
			var sid1 = $("#suggest_input").val();
			var eric=$("#suggest_input").text();
			var url = "getdata.php?flag=2";
			var data = "FDBDesc="+sid+"|"+sid1;
			http_request.open("post",url,true);
			http_request.setRequestHeader("content-type","application/x-www-form-urlencoded");
			
			//指定一个函数来处理从服务器返回的结果
			http_request.onreadystatechange = dealresult; //此函数不要括号
			//发送请求
			http_request.send(data);

		}
		//处理返回结果
		function dealresult(){
		if(http_request.readyState==4){
			//等于200表示成功
			//alert("aa");
			if(http_request.status==200){
				if(http_request.responseText=="no"){
					$("#suggest_ulc").hide(0);
					return;
					
				}
				$("#suggest_ulc").show(0);//alert(http_request.responseText);
				var res = eval("("+http_request.responseText+")");
				//alert(http_request.responseText);
				var contents="";
				for(var i=0;i<res.length;i++){
					var keywords = res[i].keywords;
					//alert(skey);
					contents=contents+"<li class='suggest_li"+(i+1)+"'>"+keywords+"</li>";
						
				}
				//alert(contents);
				$("#suggest_ulc").html(contents);
				//$("#suggest_ulc").empty();
				//$("#suggest_ulc").append(contents);
			}
		}
	}
		
		
	});
	
	
	//鼠标
$(function(){
		
	//按下按键后300毫秒显示下拉提示
	$("#suggest_inputc").keyup(function(){/*$("#suggest_form").submit();*/
		setInterval(changehover,300);
		function changehover(){
			$("#suggest_ulc li").hover(function(){ $(this).css("background","#eee");},function(){ $(this).css("background","#fff");});
			$("#suggest_ulc li").click(function(){ $("#suggest_inputc").val($(this).html());});
			$("#suggest_ulc li").click(function(e){ 
				 e.stopPropagation();
				 $(this).parent().fadeOut(0);
			});
		}
	});
	
});

});