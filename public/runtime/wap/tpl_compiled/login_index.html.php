<?php echo $this->fetch('./inc/header.html'); ?>
<div class="wrap">
	
	<div class="content">
	<div class="comment_list_txt1">
		<div id="Tab1">
		<div class="Menubox">
		<ul>
		   <li id="one1" class="hover">账号登录<a style="float:right;padding-right:10px;" href='<?php
echo parse_url_tag("u:index|register#index|"."".""); 
?>'>注册</a></li>
		</ul>
		</div>
		 <div class="Contentbox">  
		   <div id="con_one_1" class="hover">
				<div class="inputtxt">
				<div class="inputpc"><i class="fa fa-user"></i></div>	
				<div class="input_sr"><input type="text" id = "email" placeholder="请输入邮箱或昵称" name="email" ></div>
				</div>
				<div class="inputtxt"> 
				<div class="inputpc"><i class="fa fa-lock"></i></div>
				<div class="input_sr"><input type="password" id = "pwd" placeholder="请输入密码" name="pwd"></div>	 
				</div>
				
				<div class="btn_login">
				<input type="Button" value="登录" onclick = "javascript:do_login()" style="background: none;">
				</div>
		   </div>
		 </div>
		</div>             
		
	 </div>
	</div>
</div>
<script type="text/javascript">
function  do_login(){		
	var obj1=$("#email").val();
	var obj2=$("#pwd").val();
	if(!obj1){
		alert("请填写账户或邮箱");
		return false;	
	}
	if(!obj2){
		alert("请填写密码");
		return false;	
	}	
	
	var query = new Object();
	query.email = obj1;
	query.pwd = obj2;
	query.post_type = "json";
	var ajaxurl = '<?php
echo parse_wap_url_tag("u:index|login|"."".""); 
?>';
	//alert(ajaxurl);
	
	$.ajax({
		url:ajaxurl,
		data:query,
		type:"Post",
		dataType:"json",
		success:function(data){
			alert(data.info);
			if(data.user_login_status == 1){
				if(document.referrer.indexOf("login") > 0){
					window.location.href = "<?php
echo parse_wap_url_tag("u:index|index#index|"."".""); 
?>";
				}else{
					location.replace(document.referrer);
				}
			}
		}
		,error:function(){
			alert("服务器提交错误");
		}
	});
	return false;
}
    
</script>

<?php echo $this->fetch('./inc/footer.html'); ?> 