/**
 * Created by Administrator on 2016/7/19.
 */


$(document).ready(function(){
    function floatDiv(){
        $(".user").on("click",function(){
            $(".aw-popover,.triangle").toggle();
        })
    };
    floatDiv();
    //点击其他地方也隐藏
    $(document).on('click',function(e){
        $(".aw-popover,.triangle").hide();
    });
    $(".user").on('click',function(e){
        e.stopPropagation();
    });
     


    //登录注册tab切换
    $(".tab li").on("click",function(){
        var index=$(this).index();
        $(this).addClass("active").siblings().removeClass("active");
        $(".lg-inbox").eq(index).addClass("active").siblings().removeClass("active");
    });

   //验证
   var yz={
       layeropen:function(msg){
        layer.open({
            type: 1,
            content: '<div class="tipbox"><div class="tp-word">'+msg+'</div><div class="bt-btn">好</div></div>',
            title: ['', 'display:none'],
            area: 'auto',
            success: function (layero, index) {
                $(".bt-btn").on("click", function () {
                    layer.closeAll();
                })

            }
        })
      },
      valid:function(name){
          switch (name){
              //登录
              case "lg-btn":
                    {
                        var txtid=$(".logo-from .txtid").val();
                        var txtpwd=$(".logo-from .txtpwd").val();
                        var txtyz=$(".logo-from .txtyz").val();
                        var reg1=/^1[3|4|5|7|8]\d{9}$/; //手机格式
                        var reg2= /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;//邮箱格式
                        var reg3=/^[A-Za-z|0-9|\S]+$/; //6-16位，英文（区分大小写）、数字或常用符号
                        if(txtid=='') {
                            yz.layeropen('请输入手机号码/邮箱/用户名');
                        }else if(!reg1.test(txtid) && !reg2.test(txtid)){
                            yz.layeropen('手机号码/邮箱格式不正确');
                        }else if(txtpwd==''){
                            yz.layeropen('请输入密码');
                        }else if(!(reg3.test(txtpwd) && (txtpwd.length)>5 && (txtpwd.length<17))){
                            yz.layeropen('密码格式不正确');
                        }
						//else if(txtyz==''){yz.layeropen('请输入验证码');}
						else{
                            $.ajax({
                                type:"POST",
                                url:"/account/ajax/login_process/",
                                data:{user_name:$("input[name=user_name]").val(),
                                    return_url:$("input[name=return_url]").val(),
                                    net_auto_login:1,
                                    password:$("input[name=password]").val(),
                                    seccode_verify:$("input[name=seccode_verify]").val(),
                                    _post_type:'ajax',
                                    _is_mobile:true,},
                                success:function(data){
                                    data=$.parseJSON(data);
                                    if(data.errno==-1){
                                        yz.layeropen(data.err);
                                        $(".yzm.fr img").trigger('click')
                                    }else if(data.errno==-2){
										yz.layeropen(data.err);
										$(".lib").show();
									}else{
                                        window.location.href='';
                                    }
                                },
                                });
                        }
                    }
               break;
              //注册
              case "reg-btn":
              {
                  var txtid=$(".reg-from .txtid").val();
                  var txtpwd=$(".reg-from .txtpwd").val();
                  var txtname=$(".reg-from .txtname").val();
                  var txtyz=$(".reg-from .txtyz").val();
                  var checkb=$(".reg-from .checkb")
                  var reg1=/^1[3|4|5|7|8]\d{9}$/; //手机格式
                  var reg2= /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;//邮箱格式
                  var reg3=/^[A-Za-z|0-9|\S]+$/; //6-16位，英文（区分大小写）、数字或常用符号

                  var reg4=/^[A-Za-z|0-9|\u4e00-\u9fa5]+$/;  //匹配英文，数字，或中文
                  //计算字符个数
                  var total= 0,num1= 0,num2=0;
                  var arry=txtname.split("");
                  var len=arry.length;
                  for(var i=0;i<len;i++){
                      if(/^[\u4e00-\u9fa5]+$/i.test(arry[i])){
                          num1=num1+2;
                      }else{
                          num2=num2+1;
                      }
                  }
                  total=num1+num2;
                  if(txtid=='') {
                      yz.layeropen('请输入手机号码/邮箱');
                  }else if(!reg1.test(txtid) && !reg2.test(txtid)){
                      yz.layeropen('手机号码/邮箱格式不正确');
                  }else if(txtname==''){
                      yz.layeropen('请输入用户名');
                  }else if( !(reg4.test(txtname) && (total<15) && (total>1))){
                      yz.layeropen('用户名格式不正确');
                  }else if(txtpwd==''){
                      yz.layeropen('请输入密码');
                  }else if(!(reg3.test(txtpwd) && (txtpwd.length)>5 && (txtpwd.length<17))){
                      yz.layeropen('密码格式不正确');
                  }else if(txtyz==''){
                      yz.layeropen('请输入验证码');
                  }else if(!checkb.prop("checked")){
                      yz.layeropen('请阅读并同意'+'<a href="">《七丽时尚网服务协议》</a>');
                  }else{
                      $.ajax({
                          type:"POST",
                          url:"/account/ajax/register_process/",
                          data:{
                              agreement_chk:$("input[name=agreement_chk]").val(),
                              user_name:$("input[name=user_name]").val(),
                              user_mobile:$("input[name='user_mobile']").val(),
                              email:$("input[name='email']").val(),
                              password:$("input[name=password]").val(),
                              seccode_verify:$("input[name=seccode_verify]").val(),
                              _post_type:'ajax',
                              _is_mobile:true,},
                          success:function(data){
                              data=$.parseJSON(data);
                              if(data.errno==1){

                                  window.location.href=data.rsm['url'];
                              }else{
                                  /*window.location.href='';*/
                                  yz.layeropen(data.err);
                                  $(".yzm.fr img").trigger('click')
                              }
                          },
                      });
                  }
              }
              break;
              //发布
              case "pb-btn":
              {
                  var obj1=$(".fb-from .txt").val();
                  var count=$(".tag-bar .lab").length;
                  //计算字符个数
                  var total= 0,num1= 0,num2=0;
                  var arry=obj1.split("");
                  var len=arry.length;
                  for(var i=0;i<len;i++){
                      if(/^[\u4e00-\u9fa5]+$/i.test(arry[i])){
                          num1=num1+2;
                      }else{
                          num2=num2+1;
                      }
                  }
                  total=num1+num2;

                  if(obj1==""){
                      yz.layeropen('请输入问题标题');
                  }else if(total<10){
                      yz.layeropen('问题标题字数不得少于5个字');
                  }else if(count<1){
                      yz.layeropen('请选择话题');
                  }
              }
              //注册切换手机或邮箱
              case "txtid":
              {
                  var reg1=/^1[3|4|5|7|8]\d{9}$/; //手机格式
                  var reg2= /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;//邮箱格式
                  var yzm=$(".reg-from .yzm");
                  var btnhq=$(".reg-from .btn-hq");
                  var txtid=$(".reg-from .txtid").val();
                  if(reg1.test(txtid)){
                      btnhq.show();
                      yzm.hide();
                      $("#uname").attr('name','user_mobile');
                  }
                  if(reg2.test(txtid)){
                      btnhq.hide();
                      yzm.show();
                      $("#uname").attr('name','email');
                  }
              }
          }
      }

    }
function getYzm(){
    var countdown=60;
    function settime(obj) {
        if (countdown == 0) {
            obj.removeAttribute("disabled");
            obj.value="再次发送";
            countdown = 60;
            return;
        } else {
            obj.setAttribute("disabled", true);
            obj.value=countdown +"s";
            countdown--;
        }
        setTimeout(function() {settime(obj) },1000)
    }
    $(".btn-hq").on("click",function(){
        var obj=this;
        settime(obj);

        $.ajax({
            type: 'POST',
            url: '/account/ajax/postverify/' ,
            data:  {
                mobile : $("#uname").val(),

            },
            success: function(data){
                yz.layeropen(data.info);
            },

            dataType: 'json',
        });
    })

}

getYzm();
    //登录验证
    $("#lg-btn").on("click",function(){
        yz.valid('lg-btn');
    });

    //注册验证
    $("#reg-btn").on("click",function(){
        yz.valid('reg-btn');
    });

    //发布验证
    $(".pb-btn").on("click",function(){
        yz.valid('pb-btn');
    });

    //注册切换手机或邮箱
    $(".reg-from .txtid").blur(function(){
        yz.valid('txtid');
    });




    //首页设置点击
    $(".tip .set").on("click",function(){
		if(!user_key_id)
		{
			return false;
		}else{
				var obj=$(".tip .tp-box");
				if(obj.is(":hidden")){
					obj.slideDown();
				}else{
					obj.slideUp();
				}
		    }
    });

    //首页设置标签点击
    $(".tip .lablist .lab").on("click",function(){
        if($(this).hasClass("active")){
            $(this).removeClass("active");
        }else{
            $(this).addClass("active");
        }
    });

    //首页设置保存点击
    $(".tip .save-box .save-btn").on("click",function(){
        $(".tip .tp-box").hide();
    });
	
		//设置字体大小
	function setFont(){
		$(".tip .lablist .lab").each(function(){
			 var lablength=$(this).text().length;
			 if(lablength>2){
				 $(this).css("fontSize",0.1+"rem");
			 }
		})
	}
	setFont();


    /*//赞效果
    $(".tplist li .zan").on("click",function(){
        if($(this).hasClass("active")){
            $(this).removeClass("active");
        }else{
            $(this).addClass("active");
        }
    });*/

    //展开收起
    function readOpt(){
        $(document).on("click",".boxys .words,.boxys .show-img",function(){

            if($(this).hasClass('wordsque')){
                return false;
            }
            $(this).parents(".boxys ").find(".con-box").show();
            $(this).parents(".boxys ").find(".words").hide();
            $(this).parents(".boxys").find(".hide_word,.show-img").hide();
            $("html,body").stop().animate({"scrollTop":$(this).parents(".boxys ").offset().top},300);
        });

        $(document).on("click",".boxys .pack-up",function(){

            if($(this).hasClass('wordsque')){
                return false;
            }
            $(this).parents(".boxys ").find(".con-box").hide();
            $(this).parents(".boxys").find(".hide_word,.words,.show-img").show();
            $("html,body").stop().animate({"scrollTop":$(this).parents(".boxys ").offset().top},300);
        });
    };
    readOpt();

	//评论点击
	function plOpt(){
		$(document).on("click",".tplist .cz .pl",function(){
			var obj=$(this).parents(".boxys").find(".pl-box");
			var obj1=$(this).parents(".boxys").find(".yq-box");
			if(obj.is(":hidden")){
				$(this).find(".txt").text("收起评论");
				obj1.hide();
				obj.show();
			}else{
				$(this).find(".txt").text("评论");
				obj.hide();
			}
		});
	}
	plOpt();

    //邀请点击
    $(".tplist .cz .yq").on("click",function(){

        if(!user_key_id)
        {
            window.location.href="/?/account/";
        }else {
            var obj = $(this).parents(".boxys").find(".yq-box");
            var obj1 = $(this).parents(".boxys").find(".pl-box");
            var obj2 = $(this).siblings(".pl").find(".txt");
            if (obj.is(":hidden")) {
                obj1.hide();
                obj2.text("评论");
                obj.show();
            } else {
                obj.hide();
            }
        }
    });

    //回复点击
    $(".pl-word .hf").on("click",function(){
       $(this).parent().find(".ptxta-boxt").show();
    });
    //取消，发布点击
    $(".pl-word .btn").on("click",function(){
        $(this).parent().hide();
    });
/*
    //赞点击
    $(".zanb").on("click",function(){
        var count= parseInt($(this).find(".count").text());
        $(this).addClass("active");
        $(this).find(".count").text(count+1);
    });

*/
    //关注点击
   /* function fcusfc(obj){

        if(!user_key_id)
        {
            window.location.href="/account/";
        }else{
            var noobj=obj.find(".no");
        var yesobj=obj.find(".yes");
        var loadobj= obj.find(".load");
        obj.find(".txt").text("关注中");

        if(noobj.is(":hidden")){
            yesobj.hide();
            loadobj.show();
            setTimeout(function(){
                loadobj.hide();
                noobj.show();
                obj.find(".txt").text("取消关注");
            },2000);
        }else{
            noobj.hide();
            yesobj.show();
            obj.find(".txt").text("关注");
        }
        }
    };*/
    $(".focus-btn").on("click",function(){
        fcusfc($(this));
    });

    //换一批
    $(".change").on("click",function(){
        $(this).find(".shuffle").addClass("rta");
        setTimeout(function(){
            $(".shuffle").removeClass("rta");
        },1000)
        var index=parseInt(3*Math.random()); //获取0-2之间的随机整数
       var obj= $(this).parents(".seaans").find(".seaans-list");
        obj.eq(index).addClass("active").siblings(".seaans-list").removeClass("active");
    });







	$('.ajax').on("click",function(){      //取话题加ACTIVE 特效  @key0716
		if(!user_key_id)
		{
			window.location.href="/account/";
		}else{
			 htmlobj=$.ajax({url:"/my/getuserid/",async:false});
			 var data = eval (htmlobj.responseText);
			 for(i=0;i<data.length;i++)
			    {
				 var fid = '.topic'+data[i]['topic_id'];
				 $(fid).addClass("active");
				}
			}
    });  

})

//关注点击
function fcusfc(obj){
    if(!user_key_id)
    {
        window.location.href="/account/";
    }else {
        var noobj = obj.find(".no");
        var yesobj = obj.find(".yes");
        var loadobj = obj.find(".load");
        obj.find(".txt").text("关注中");

        if (noobj.is(":hidden")) {
            yesobj.hide();
            loadobj.show();
            setTimeout(function () {
                loadobj.hide();
                noobj.show();
                obj.find(".txt").text("取消关注");
            }, 2000);
        } else {
            noobj.hide();
            yesobj.show();
            obj.find(".txt").text("关注");
        }
    }
};

function djpl(obja){

    var obj=obja.parents(".boxys").find(".pl-box");
    var obj1=obja.parents(".boxys").find(".yq-box");
    if(obj.is(":visible")){
        obja.find(".txt").text("收起评论");
        obj1.hide();
        obj.show();
    }else{

        obja.find(".txt").text("评论");
        obj.hide();
    }
}

//获取验证码


//图片预加载
$(document).ready(function(){
Echo.init({
        offset: 1000,
        throttle: 0
    });
})

//首页图片SRC处理
function changeImg(id){
	var str=$('#changeSrc'+id).html();
	$('#changeSrc'+id).empty();
	$('#changeSrc'+id).append(str.replace(/src_for_loading/g, "src"));
}
$(document).ready(function(){
    Echo.init({
        offset: 100,
        throttle: 0
    });
});

//协议弹框
$(document).on("click","#xy",function(){
    $.get('/magreement.html', function(data) {
        layer.open({
            skin: 'xybox',
            type: 1,
            content:data,
            title: ['七丽时尚网服务协议', 'font-size:0.16rem;background:#666;height:0.4rem;line-height:0.4rem;color:#fff;border:none;text-align:center;padding:0;'],
            area: ['80%', '3rem'],
            success: function (layero, index) {
                $(".no-btn").on("click", function () {
                    layer.closeAll();
                });
            }
        });
    });

});

//用户中心编辑
var df = {
      upfile:function(a, b, c) {
          $(".filePrew").change(function(a) {
              var d, e, f, g;
              if ("undefined" == typeof FileReader) return popup.prompt("你的浏览器不支持FileReader接口！"), 
              $(".filePrew")[0].setAttribute("disabled", "disabled"), void 0;
              for (d = a.target.files, f = 0; e = d[f]; f++) e.type.match("image.*") && (g = new FileReader(), 
              g.onload = function(a) {
                  return function(d) {
                      var e, f = document.createElement("img");
                      return document.createElement("p"), f.title = a.name, f.src = d.target.result, e = $(b).find(".pic"), 
                      e.find("img").length <= 0 ? (e.append(f), "function" === $.type(c) && c(), void 0) :(e.empty(), 
                      e.prepend(f), "function" === $.type(c) && c(), void 0);
                  };
              }(e), g.readAsDataURL(e), $(".upImage").delegate("p", "click", function() {
                  $(this).remove();
              }));
          });
      }
  }
  
  //通用提示弹框
function layermsg(msg,str){
	switch (str){
		case 1:
			var pdleft=$(".pdleft").offset().left+$(".pdleft").width()/2-50;
			layer.msg(msg, {
				time: 1000 ,
				offset: ['400px', pdleft]
			})
			break;
		case 2:
			layer.msg(msg, {
				time: 1000
			})
			break;
	}
}