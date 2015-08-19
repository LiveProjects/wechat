function mode(input,ul){
	// JavaScript Document

	$(function(){
			//载入时隐藏下拉li
			$(ul).hide(0);
	});
	
	
	
	//Ajax 动态获取关键字
	$(function(){
						 
		//监听文本框输入变化
		$(input).bind('input propertychange',function(){
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
			if($(input).val().length==0){
				$(ul).hide(0);
				return;
			}
			//发送请求
			http_request = createLink();//创建一个ajax对象
			if(http_request){
				var sid = $(input).val();
				var url = "getdata.php?flag=1";
				var data = "keywords="+sid;
				//alert(data)
				http_request.open("post",url,true);
				http_request.setRequestHeader("content-type","application/x-www-form-urlencoded");
				
				//指定一个函数来处理从服务器返回的结果
				http_request.onreadystatechange = dealresult; //此函数不要括号
				//发送请求
				http_request.send(data);
			}
			//处理返回结果
			function dealresult(){
			if (http_request.readyState!=4) {
				$("#ajax").css('display','block').text("正在加载中...")
		        var load=setInterval(function(){
		        	$("#ajax").text("正在加载中....");
		        	setTimeout(function(){
		        		$("#ajax").text("正在加载中...");
		        	},500)
		        },1000);

			}else if(http_request.readyState==4){
					//等于200表示成功
					$("#ajax").hide();
				    clearInterval("load");
					//alert("aa");
					if(http_request.status==200){
						if(http_request.responseText=="no"){
							$(ul).hide(0);
							return;
							
						}
						$(ul).show(0);//alert(http_request.responseText);
						var res = eval("("+http_request.responseText+")");
						//alert(http_request.responseText);
						var contents="";
						for(var i=0;i<res.length;i++){
							var keywords = res[i].keywords;
							//alert(skey);
							contents=contents+"<li class='suggest_li"+(i+1)+"'>"+keywords+"</li>";
								
						}
						//alert(contents);
						$(ul).html(contents);
						//$("#suggest_ulk").empty();
						//$("#suggest_ulk").append(contents);
					}
				}
			}
					
		});	
		//鼠标
	$(function(){
			
		//按下按键后300毫秒显示下拉提示
		$(input).keyup(function(){/*$("#suggest_form").submit();*/
			setInterval(changehover,300);
			function changehover(){
				$(ul).delegate('li','hover',function(){ 
					$(this).css("background","#eee");},function(){ $(this).css("background","#D2E9FF");
					});
				
				$(ul+" li").click(function(){ 
					$(input).val($(this).html());
					var length=$(input).val().length;
					$(input).prop('size',length*2);
				});
				$(ul+" li").click(function(e){ 
					 e.stopPropagation();
					 $(this).parent().fadeOut(0);
				});
			}
		});
		
	});

	});
}