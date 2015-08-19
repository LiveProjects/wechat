$(document).ready(function sss(){
    
    /************************查看按时间限定记录 ****************************/
	$("#date button").bind('click',function(e){
    	dateone=$(this).prev().find("input").val();
    	datetwo=$(this).prev().prev().find("input").val();
    	var target=e.target;
     	$.ajax({
        	url:'ajax.php?act=date1',
        	dataType:'text',
        	Type:'POST',
        	data:{"dateone":dateone,"datetwo":datetwo,"type":"date"},
        	success:function(data){
        		sessionStorage.timeup=1;
        		sessionStorage.up=1;
        		
        		$("#moreup,#more").hide();
        		$("#edit-main").empty();
        		
        		//隐藏方法
        		$("#more").css('display','none');
        		if(data==0){
        			alert("请输入正确时间");
        		}else if(data==1){
        			alert("没有找到符合此条件的订单信息");
        		}else{
        			$("#edit-main").append(data);
        			status();
        			long();
            		if($("#edit-main dl").length>9){
            			$("#timemore,#timemoreup").show();
            		}
        		}
        	},
            error:function(err){
            	alert(err);
        	}
                  
     	})
	})


/***********************************根据状态添加效果**********************************/
function status(){
    var aaa=$(".top ul li:nth-child(2) label");
	$.each(aaa,function(index,value){
		var a=$(this).next().val();//input里边的值
		var jiahuabuv=parseInt($(this).parent().prev().find('input').val().substr(0,1));//截取计划部的值
		//设置订单时间
		if($(this).parent().prev().find('input').val().substr(1,2)==20){
			var cut=$(this).parent().prev().find('input').val().substr(1);
		    $(this).parent().prev().find('input').val(cut);
		}
		//按钮状态判断
		var bobtn=$(this).parent().parent().parent().nextAll("div").find("button");
		var xiugaibtn=$(this).parent().parent().parent().nextAll("dt").find("button");
		if(jiahuabuv=="2"){
			var btn1=bobtn.eq(0).attr("disabled","disabled");
			var btn2=bobtn.eq(1).attr("disabled","disabled");
			$(this).parent().parent().parent().nextAll("dt").find("button").hide();
		}else{
			if(a=="未通过"){
		         $(this).parent().next().next().find("span").css('display','block');
				 $(this).parent().parent().parent().nextAll("div").find("button").eq(0).attr("disabled","disabled"); 
				}else if(a=="已通过"){
					xiugaibtn.css('display','none');
					
					}else if(a=="已分解"){
						 bobtn.attr("disabled","disabled");
						 xiugaibtn.css('display','none'); 
						}else if(a=="已发货"){
							bobtn.attr("disabled","disabled");
							xiugaibtn.css('display','none');
							}else if(a=="已作废"){
								bobtn.attr("disabled","disabled");
								xiugaibtn.css('display','none');
								}
		}
	})
}
status();

/***********************************弹出判断*********************************/
$("#edit-main").delegate(' dl dt','click',function(e){
	   //alert(34);
	   //e.stopPropagation();
	   //window.event.cancelBubble = true;
	   window.k=$(this).prop("id");
       //alert($(this).nextAll("div").find("button").eq(1).attr("disabled"));
       var liper=$(this).prevAll("div").find("li");
	   try{
		   if(liper.eq(1).find("input").val()=="未通过"&& $(this).nextAll("div").find("button").eq(1).attr("disabled")!="disabled"){//如果订单状态是未通过
			   if($(this).next().css('display')=="none"){
				   $(this).find("button").css('display','block');
			   }else if($(this).next().css('display')=="block"){
				   $(this).find("button").css('display','none'); 
			   }
		   }else if(liper.eq(1).find("input").val()=="已通过"){
			   $(this).parent().find("dt").find("button").css("display","none");
		   }
	   }catch(err){
		   alert(err.message);
	   }
	   	var len=$(this).parent().find("dt").length; 	
	   	$(this).nextUntil("dt").toggle();	   	
	   	$(".bottom").css("display","block");
    });

//展开子订单判断
$(document).delegate(".top-btn",'click',function(){
	
	var fast=$(this).parent().nextAll("dt");
	fast.toggle();
   	$(this).parent().nextAll("dd").fadeOut(0);
   	try{
   		if(fast.css('display')=="block"){
   		   fast.find("button").css("display","none")
	   	}else{
	   		console.log("top-btn延时出错")
		   	}
	   	}catch(err){
	   		alert(err.message)
	   		}
});

 /************************修改子订单****************************/
 
 $("#edit-main").delegate('dl dt button','click',function(e){
	 e.stopPropagation();
	 window.event.cancelBubble = true;
	 //返回食品厂
	 var use="#account"+k;

	   var edit=this;
	   var editbtn=$(this);
	   if($(this).parent().nextUntil("dt").find("input[class='modify2']").attr("disabled")=="disabled"){
		   $(this).parent().nextUntil("dt").find("input[class='modify2']").removeAttr('disabled').css('outline','1px solid pink');
		   /*$(this).css('background-color','green');*/
//		   console.log("弹1");
	   }else if($(this).parent().nextUntil("dt").find("input[class='modify2']").attr("disabled")!="disabled"){
		  
//		   console.log("弹");
		  var FName=$(this).parent().nextAll("dd").eq(1).find("input").val();       
          var FAccountName=$(this).parent().nextAll("dd").eq(0).find("input").val();
          
		  var FOrderNumber=$(this).parent().parent().find("h4").text().substring(5);
		  var FQty=$(this).parent().nextAll("dd").eq(3).find("input").val();
		  var FID_sub=$(this).parent().nextAll("dd").eq(5).find("input").val();
 		  var FPrice=$(this).parent().nextAll("dd").eq(2).find("input").val();
		  var Remark=$(this).parent().nextAll("dd").eq(4).find("input").val();

 		  var unite=$(this).parent().next().next().find("b").text();

		   $.ajax({
		       url:"modorder.php",
		       Type:'POST',
		       dataType:'text',
		       data:{"FName":FName,
		    	     "FAccountName":FAccountName,
		    	     "FOrderNumber":FOrderNumber,
		    	     "FID_sub":FID_sub,
		    	     "FQty":FQty,
		    	     "FPrice":FPrice,
		    	     "Remark":Remark,
		    	     "type":3,
		    	     "FUnit":unite},
		    	     success:function(data){		 	   
			               
			               if(data=="产品规格输入错误"||data=="请检查空项！"||data=="修改失败，商品重复"||data=="修改失败，请联系技术支持"||data=="输入的数量或单位不正确！"){
			            	  alert(data);
//			            	  console.log(data);
			               }else{
			            	   alert(data.substr(0,4));
			            	   editbtn.parent().nextUntil("dt").find("input").attr('disabled','disabled').css('outline','none');
			            	   var pronum="产品编号:"+data.slice(4,-1);
			            	   var valuetwo=editbtn.parent().html();
			            	   
			            	   var id=editbtn.attr("id");
			            	   adddan(id,pronum);
			            	   //var ok=editbtn.parent();
			            	   //alert(ok.childNodes.item(1).nodeType);
			            	   //alert(editbtn.parent().text());
			            	   
			            	   //alert(pronum+valuetwo.substr(16));
			            	   //editbtn.parent().html(pronum+valuetwo.substr(16));
			            	   			            	   
			            	   if(data.substr(-1)==1){//超过一千吨
			            		   editbtn.parent().prevAll("div").find("li").eq(1).find("input").val("已通过");
			            		   editbtn.parent().prevAll("div").find("span").css('display','none');
			            		   
			            		   editbtn.parent().nextAll("div").find("a").eq(0).find("button").removeAttr('disabled');
			            		   editbtn.parent().nextAll("div").find("a").eq(1).find("button").removeAttr('disabled');
			            		   editbtn.parent().parent().find("div").find("span").hide();
			            		   editbtn.hide();
			            		   editbtn.parent().nextAll("dt").find("button").hide();
			            	   }else{
			            		   editbtn.parent().nextAll("dt").find("button").hide();
			            		   editbtn.parent().nextAll("div").find("a").eq(1).find("button").attr('disabled','disabled');
			            		   editbtn.hide();
			            		   editbtn.parent().prevAll("div").find("span").hide();
			            		   
			            	   }
			               }
			           }
		     })
		    
	   }
   });
   function adddan(dan,val){
	   var s=document.getElementById(dan);
	   s.parentNode.childNodes[0].nodeValue=val;
	   //alert(s.parentNode.childNodes[0].nodeValue);
   }
 /***********************************弹框显示*********************************/
    $("#edit-main").delegate('dl div li:nth-child(3)','click',function(e){
    	 
    	 if($(this).find('input').attr('disabled')=="disabled"){
    		 e.stopPropagation();//冒泡兼容问题
    		 var ss=$(this).find("input").val();
    		 //alert(ss);
    		 //$("#top-face").css({'display':'block','z-index':'10000'}).text(ss);
			 //var top = ($(window).height() - $('#top-face').height())/2;   
             //var left = ($(window).width() - $('#top-face').width())/2;   
			 var top = 0;
			 var left = 0;
            var scrollTop = $(document).scrollTop();   
            var scrollLeft = $(document).scrollLeft(); 
			 //var scrollTop = $(document).scrollTop();   
			 //var scrollLeft = $(document).scrollLeft();
             //var  top = scrollTop;
             //var  left = scrollLeft;
             $("#top-face").text(ss);			 
			 $("#top-face").css({'position':'absolute','display':'block','z-index':'10000','height':'auto','width':'80%','top':top+scrollTop,'left':left+scrollLeft,'overflow':'hidden'});
    		 $("#top-face").siblings().css('opacity','0.2').attr('disabled','disabled');
    	 }
	 })
	 
	 $("#edit-main").delegate('dl div li:nth-child(4)','click',function(e){
    	 if($(this).find('input').attr('disabled')=="disabled"){
    		 e.stopPropagation();//冒泡兼容问题
    		 var ss=$(this).find("input").val();
    		 //alert(ss);
    		 //$("#top-face").css('display','block').text(ss);
			 var top = 0;
			 var left = 0;
             var scrollTop = $(document).scrollTop();   
             var scrollLeft = $(document).scrollLeft(); 
			 //var scrollTop = $(document).scrollTop();   
			 //var scrollLeft = $(document).scrollLeft();
             //var  top = scrollTop;
             //var  left = scrollLeft;	
             $("#top-face").text(ss);			 
			 $("#top-face").css({'position':'absolute','float':'left','display':'block','z-index':'10000','height':'80%','width':'80%','top':top+scrollTop,'left':left+scrollLeft,'overflow':'auto'});
    		 $("#top-face").siblings().css('opacity','0.2').attr('disabled','disabled');
    	 }
	 })
	 $("#edit-main").delegate('dl dd.tan','click',function(e){
		 if($(this).find('input').attr('disabled')=="disabled"){
    		 e.stopPropagation();//冒泡兼容问题
    		 var ss=$(this).find("input").val();
    		 //alert(ss);
    		 //$("#top-face").css('display','block').text(ss);
			 var top = 0;
			 var left = 0;
             var scrollTop = $(document).scrollTop();   
             var scrollLeft = $(document).scrollLeft(); 
             $("#top-face").text(ss);			 
			 $("#top-face").css({'position':'absolute','float':'left','display':'block','z-index':'10000','height':'80%','width':'80%','top':top+scrollTop,'left':left+scrollLeft,'overflow':'auto'});
    		 $("#top-face").siblings().css('opacity','0.2').attr('disabled','disabled');
    	 }
	 })
	 
	 
	 $("#top-face").click(function(){
		 $("#top-face").fadeOut(200);
		 $("#top-face").siblings().css('opacity','1').removeAttr('disabled');
	 })
	 
   /***********************************撤回订单**********************************/
	$(document).delegate('.bottom a:nth-child(1)','click',function(){
	   var value1=$(this).parent().parent().find("h4").text().substring(5);
	   var type=$(this).parent().parent().find("li[class='type']").find("input");//订单状态框
	   var btn1=$(this);
	   console.log(123);
	   confirm_=confirm("确定撤回此订单？");
	   if(confirm_)
		   {
			   $.ajax({
			       url:"modorder.php",
			       Type:'POST',
			       dataType:'text',
			       data:{"ordernum":value1,"type":2},
			       success:function(data){
			    	   alert(data);
			    	   type.val("未通过");
			    	   btn1.find("button").attr('disabled','disabled');
			    	   btn1.parent().prevAll("dt,dd").hide();
			    	   if(type.val()=="未通过"){
			    		   type.parent().next().next().find("span").css('display','block');		    		   
			    	   }
			       }
			     })
		   }
	 })

/***********************************删除订单**********************************/
	 $(document).delegate('.bottom a:nth-child(2)','click',function(){
	   var FOrderNumber=$(this).parent().parent().find("h4").text().substring(5);//订单号
	   var type=$(this).parent().parent().find("li[class='type']").find("input");//订单状态
	   var btn1=$(this);
	   confirm_=confirm("确定删除此订单？");
	   if(confirm_)
		   {
			   $.ajax({
			       url:"modorder.php",
			       Type:'POST',
			       dataType:'text',
			       data:{"FOrderNumber":FOrderNumber,"type":1},
			       success:function(data){	
			    	   type.val("已作废");
			           alert(data);
			           if(type.val()=="已作废"){
			    		   type.parent().next().next().find("span").css('display','none');
			    		   btn1.parent().find("button").attr('disabled','disabled');
			    	   }
			        }
			     })
		   }
	 })
	 
	 /***********************************修改主订单备注**********************************/
	 $("#edit-main").delegate('.top ul li:nth-child(4) span','click',function(e){
		 //alert(123);
		 e.stopPropagation();
		 window.event.cancelBubble = true;
		 
		 var FOrderNumber=$(this).parent().parent().prev("h4").text().substring(5);
		 $("#top-face").hide(0);
		 if($(this).text()=="完成"){
			 $("#top-face").hide(0);
			 $("#top-face").siblings().css('opacity','1');
			 var FRemark=$(this).prev().val();
			 $(this).text("修改");
			 $(this).prev().attr('disabled','disabled').attr("autofocus","true");
			 $.ajax({
			       url:"modorder.php",
			       Type:'POST',
			       dataType:'text',
			       data:{"FOrderNumber":FOrderNumber,"FRemark":FRemark,"type":4},
			       success:function(data){	    	  
			           if(data){
			        	     $("#top-face").hide(0);
			        	     $("#top-face").siblings().css('opacity','1');
			                 alert(data);
			               }
			           }
			     })
		 }else if($(this).text()=="修改"){
			 $(this).text("完成");
			 $(this).prev().removeAttr('disabled');
		 }
	 })
	 
	
	/***********************************动态改长度**********************************/
	 function long(){
    	var long=$("#edit-main dl dd input[class='modify2']");
    	
    	long.each(function(index,val){
    		var long=$(this).val().length;
    		var long=long+1;
    		//alert(long);
    		$(this).prop('size',long*2);
    	})
    	
    	long.keydown(function(e){
    		e.stopPropagation(); 
    		$(this).keyup(function(){
    			var bb=$(this).val().length;
    			//alert(bb);
    			$(this).prop('size',bb*2);
    		})
    	 });
    }
    long();
	

/*	*//**********************************动态修改工厂**********************************//*
	function account(id){
		$("#edit-main dt").next().next().find("input").focus(function(){//this调用方法的对象
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
		            	   $(id).empty();
		            	   $(id).append(data);
		                }
		              })
				}
		})
	}*/
	
	/***********************************默认更多订单**********************************/
	var dllen=$("dl").length;
	if(dllen>3){
		var more=document.getElementById("more");
		var moreup=document.getElementById("moreup");
		//var more=document.getElementById("timemore");
		var timemore=document.getElementById("timemore");
		var timemoreup=document.getElementById("timemoreup");
			
		more.onclick=function(){
			++sessionStorage.up;
			aj();
		}
		moreup.onclick=function(){
			if(sessionStorage.up>1){
				--sessionStorage.up;
				aj();
			}else{
				alert("已经是第一页了");
			}
		}
		
		timemore.onclick=function(){
			++sessionStorage.timeup;
//			alert(sessionStorage.timeup);
			aj2();
		}
		timemoreup.onclick=function(){
			if(sessionStorage.timeup>1){
				--sessionStorage.timeup;
//				alert(sessionStorage.timeup);
				aj2();
			}else{
				alert("已经是第一页了");
			}
		}
		
	}
	//共享异步事件
	function aj(){
		$.ajax({
			beforeSend: function(){
				!function(event) {
			        $("#ajax").css('display','block').text("正在加载中...")
			        var load=setInterval(function(){
			        	$("#ajax").text("正在加载中....");
			        	setTimeout(function(){
			        		$("#ajax").text("正在加载中...");
			        	},500)
			        },1000)
			     }()

			},
			
			url:'ajax.php?act=more',
			data:{"page":sessionStorage.up},
			dataType:'text',
			Type:'',
			success:function(data){
				//alert(data);
				$("#ajax").hide();
				clearInterval("load");
				if(data==-1){
					--sessionStorage.up;
					alert("没有更多订单");
				}else{
			        //cleatInterval(load);
					$("#edit-main").empty().append(data);
					status();
        			long();
				}
				
			}
		})
	}
	function aj2(){
		//alert(sessionStorage.timeup);
		$.ajax({
			beforeSend: function(){
				!function(event) {
			        $("#ajax").css('display','block').text("正在加载中...")
			        var load=setInterval(function(){
			        	$("#ajax").text("正在加载中....");
			        	setTimeout(function(){
			        		$("#ajax").text("正在加载中...");
			        	},500)
			        },1000)
			     }()

			},
			
			url:'ajax.php?act=datemore',
			data:{"page":sessionStorage.timeup,"dateone":dateone,"datetwo":datetwo},
			dataType:'text',
			Type:'',
			success:function(data){
				//alert(data);
				$("#ajax").hide();
				if(data==-1){
					--sessionStorage.timeup;
					alert("没有更多订单");
				}else{
			        //cleatInterval(load);
					$("#edit-main").empty().append(data);
					status();
        			long();
				}
				
			}
		})
	}
	
	/**********************************排空表单**********************************/
	$("#edit-main").delegate('dl dd input','focus',function(e){
		e.stopPropagation();
		//alert(123);
		$(this).val("");
	})
	
	/***********************************ajax全局事件**********************************/
	
	$(document).ajaxStart(onStart).ajaxSuccess(onSuccess);
   
    function onStart(event) {
        $("#ajax").css('display','block').text("正在加载中...")
        var load=setInterval(function(){
        	$("#ajax").text("正在加载中....");
        	setTimeout(function(){
        		$("#ajax").text("正在加载中...");
        	},500)
        },1000)
    }
    function onSuccess(event, xhr, settings) {
        $("#ajax").hide();
        //cleatInterval(load);
    }
	/************************************获取单位**********************************/
	$("#edit-main").delegate('dl dt+dd+dd input','focus',function(){
		var isdanwei=$(this).parent().prev().find("input").val();
		var th=$(this);
		$.ajax({
			url:'ajax.php?act=product',
			data:{"val":isdanwei},
			dataType:'text',
			Type:'',
			success:function(data){
				//alert(data);
 				th.next().text(data);
			},
			error:function(){
				alert("单位获取失败");  
			}
		})
			
	});
	/************************************修改顺序**********************************/
	/*function shunxv(){
		var dddd=$("#edit-main dl dt");
		$.each(dddd,function(){
			var num=$(this).nextUntil("dt").length;
			for(var i=0;i<num;i++){
				console.log($(this).nextUntil("dt")[i])
			}
			$(this).after($(this).nextUntil("dt").eq(2));
			
			console.log(num);
		})
	}*/
	//shunxv();

})