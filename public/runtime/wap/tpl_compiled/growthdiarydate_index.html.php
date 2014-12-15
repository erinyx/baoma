<?php echo $this->fetch('./inc/header_growth.html'); ?>
<style>
.wrap{font: 16px/1.6em Arial, Geneva, Helvetica, sans-serif;}
.current_date{font-size:18px;font-weight:bold;margin-bottom:10px;}
.calendar{width:95%}
.calendar a {display:block; width:100%; height:100%;}
td,th{border-left:1px solid #999;border-bottom:1px solid #999;width:120px;padding:10px 0;text-align:center}
table{border-right:1px solid #999;border-top:1px solid #999}
th{background:#666;color:#fff}
.other-month{background:#eee}
</style>
<script type="text/javascript" src="./js/calendar/jquery.calendar-widget.js"></script>
<script type="text/javascript">
//获取当前年月
var myDate = new Date();
var now_year = myDate.getFullYear();//获取完整的年份(4位,1970-????)
var now_month = myDate.getMonth();//(0-11,0代表1月)

$(function(){
	//页面首次加载时显示当前年月的日历
	$("#current_year").html(now_year);
	$("#current_month").html(now_month+1);
	$('#calendar').calendarWidget({month:now_month,year:now_year});
	getGrowthdairy(now_year,now_month+1);
});

//下一个月
function nextMonth(){
	if(now_month>=0 && now_month<11){
		now_month = now_month+1;		
	}else{
		if(now_month == 11){
			now_month = 0;
			now_year = now_year+1;
		}
	}	
	$("#current_year").html(now_year);
	$("#current_month").html(now_month+1);
	$('#calendar').calendarWidget({month:now_month,year:now_year});
	getGrowthdairy(now_year,now_month+1);
}

//上一个月
function preMonth(){	
	if(now_month == 0){
		now_month = 11;
		now_year = now_year-1;
	}else{
		if(now_month>0 && now_month<=11){
			now_month = now_month-1;		
		}
	}	
	$("#current_year").html(now_year);
	$("#current_month").html(now_month+1);
	$('#calendar').calendarWidget({month:now_month,year:now_year});
	getGrowthdairy(now_year,now_month+1);
}

/*
//当月
function nowMonth(){
	now_year = myDate.getFullYear();
	now_month = myDate.getMonth();
	$("#current_year").html(now_year);
	$("#current_month").html(now_month+1);
	$('#calendar').calendarWidget({month:now_month,year:now_year});
}
*/

//获取日历界面所显示的年月的成长日记
function getGrowthdairy(current_year_val, current_month_year){
	var query = new Object();
	query.current_year = current_year_val;
	query.current_month = current_month_year;
	query.post_type = "json";
	//alert(query.current_year+"--"+query.current_month+"--"+query.post_type);
	var ajaxurl = '<?php
echo parse_wap_url_tag("u:index|growthdiarydate#index|"."".""); 
?>';
	$.ajax({
		url:ajaxurl,
		data:query,
		type:"post",
		dataType:"json",
		success:function(data){
			//alert(data.growth_diary_list[0]['title']);
			$.each(data.growth_diary_list, function(i, item){ 
				//alert(item['record_date']+"--"+item['record_day']); 
				var day_id = "day"+item['record_day'];
				//alert(day_id);
				document.getElementById(day_id).style.backgroundColor ="#f49f35";
				var td_old_content = document.getElementById(day_id).innerHTML;
				var td_new_content = "<a href='index.php?ctl=growthdiaryitem&id="+item['id']+"'>"+td_old_content+"</a>"
				document.getElementById(day_id).innerHTML = td_new_content;
				//alert(td_content);
			});
		},
		error:function(){
			alert("服务器提交错误");
		}
	});	
}
</script>
<div class="header">
	<div class="c-hd">
        <section class="header_left_text">
              <a href='<?php
echo parse_wap_url_tag("u:index|growthdiarylist#index|"."".""); 
?>'>列表</a>
	    </section>
        <section class="logo_img"><?php echo $this->_var['data']['page_title']; ?></section>
        <section class="h_search"><!--这里是头部的隐藏列表，点击可操作-->
			<a href='<?php
echo parse_wap_url_tag("u:index|growthdiarywrite#index|"."".""); 
?>'><i>记录一下</i></a>
	    </section>
     </div>	 
</div>	

			<div class="wrap">			
				<div class="blank8"></div>
				<center>
				<div class="current_date">
					<a href="javascript:void(0);" onclick="preMonth()"><<</a>
					&nbsp;&nbsp;&nbsp;<font id="current_year"></font>年<font id="current_month"></font>月	&nbsp;&nbsp;
					<a href="javascript:void(0);" onclick="nextMonth()">>></a>
					<!--<input type="button" value="当月" onclick="nowMonth()"/>-->
				</div>
				<div class="calendar" id="calendar"></div>
				</center>				
				 
			</div><!--wrap end-->						
<?php echo $this->fetch('./inc/footer.html'); ?>				
			