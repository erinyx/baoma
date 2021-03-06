<?php 
/*
 * 返回json 值，需要注意的事项
普通值， 对象， 数组

如果为空时（json格式）

普通值 返回：null  如： $root['val'] = null;
对象 返回：null 或 {}   如： $root['obj'] = new stdClass();
数组 返回： []   如： $root['arr'] = array();

返回 数组 时，下标应该从： 0 开始

//如：重新按按顺序排序
$payment_list = array();
foreach($root['order_parm']['payment_list'] as $k=>$v)
{
$payment_list[] = $v;
}
$root['order_parm']['payment_list'] = $payment_list;

*/

require '../system/common.php';
require '../app/Lib/common.php';
require './lib/functions.php';
//缓存的名称以MAPI_开头
require './lib/logUtils.php';

define('AS_LOG_DIR', APP_ROOT_PATH.'sjmapi/log/');
define('AS_DEBUG', true);

if (isset($_REQUEST['i_type']))
{
	$i_type = intval($_REQUEST['i_type']);
}

if ($i_type == 1)
{
	$request = $_REQUEST;
}
else
{
	if (isset($_REQUEST['requestData']))
	{
		if ($i_type == 2)
		{
			$request = json_decode(trim($_REQUEST['requestData']), 1);		
		}else
		{			
			//$_REQUEST['requestData'] ="eyJ1c2VyX2luZm8iOiJ7XCJpZFwiOjE3Mjk0OTAxNjAsXCJpZHN0clwiOlwiMTcyOTQ5MDE2MFwiLFwic2NyZWVuX25hbWVcIjpcIkNoaWdhb1wiLFwibmFtZVwiOlwiQ2hpZ2FvXCIsXCJwcm92aW5jZVwiOlwiMzVcIixcImNpdHlcIjpcIjFcIixcImxvY2F0aW9uXCI6XCLnpo/lu7og56aP5beeXCIsXCJkZXNjcmlwdGlvblwiOlwiXCIsXCJ1cmxcIjpcImh0dHA6XC9cL3d3dy5teXhpbGkuY29tXCIsXCJwcm9maWxlX2ltYWdlX3VybFwiOlwiaHR0cDpcL1wvdHAxLnNpbmFpbWcuY25cLzE3Mjk0OTAxNjBcLzUwXC8wXC8xXCIsXCJwcm9maWxlX3VybFwiOlwiY2hpZ2FvXCIsXCJkb21haW5cIjpcImNoaWdhb1wiLFwid2VpaGFvXCI6XCJcIixcImdlbmRlclwiOlwibVwiLFwiZm9sbG93ZXJzX2NvdW50XCI6OTgsXCJmcmllbmRzX2NvdW50XCI6NDMwLFwic3RhdHVzZXNfY291bnRcIjo1NjUsXCJmYXZvdXJpdGVzX2NvdW50XCI6MCxcImNyZWF0ZWRfYXRcIjpcIlR1ZSBBcHIgMTMgMTc6MTc6MzMgKzA4MDAgMjAxMFwiLFwiZm9sbG93aW5nXCI6ZmFsc2UsXCJhbGxvd19hbGxfYWN0X21zZ1wiOmZhbHNlLFwiZ2VvX2VuYWJsZWRcIjp0cnVlLFwidmVyaWZpZWRcIjpmYWxzZSxcInZlcmlmaWVkX3R5cGVcIjotMSxcInJlbWFya1wiOlwiXCIsXCJzdGF0dXNcIjp7XCJjcmVhdGVkX2F0XCI6XCJTYXQgTWFyIDIzIDE2OjIwOjA3ICswODAwIDIwMTNcIixcImlkXCI6MzU1OTA0ODc0ODY1Mjk5NCxcIm1pZFwiOlwiMzU1OTA0ODc0ODY1Mjk5NFwiLFwiaWRzdHJcIjpcIjM1NTkwNDg3NDg2NTI5OTRcIixcInRleHRcIjpcIiPoiIzlsJbkuIrnmoTpn6nlm70jIOWFs+mUrueci+aYr+S4jeaYr+e7v+iJsuaXoOaxoeafk++8jOacieayoeaciea3u+WKoOWJgu+8gSDor6bmg4U6aHR0cDpcL1wvdC5jblwvellreXB0eFwiLFwic291cmNlXCI6XCI8YSBocmVmPVxcXCJodHRwOlwvXC9hcHAud2VpYm8uY29tXC90XC9mZWVkXC80QWJBRlZcXFwiIHJlbD1cXFwibm9mb2xsb3dcXFwiPuW+ruivnemimDxcL2E+XCIsXCJmYXZvcml0ZWRcIjpmYWxzZSxcInRydW5jYXRlZFwiOmZhbHNlLFwiaW5fcmVwbHlfdG9fc3RhdHVzX2lkXCI6XCJcIixcImluX3JlcGx5X3RvX3VzZXJfaWRcIjpcIlwiLFwiaW5fcmVwbHlfdG9fc2NyZWVuX25hbWVcIjpcIlwiLFwicGljX3VybHNcIjpbXSxcImdlb1wiOm51bGwsXCJhbm5vdGF0aW9uc1wiOlt7XCJzb3VyY2VcIjp7XCJuYW1lXCI6XCLoiIzlsJbkuIrnmoTpn6nlm71cIixcImFwcGlkXCI6XCI0MzhcIixcInVybFwiOlwiaHR0cDpcL1wvaHVhdGkud2VpYm8uY29tXC8yOTU4MlwifX0se1wiaHVhdGlcIjp7XCJmcm9tXCI6XCJ0b3BpY19wdWJsaXNoXCJ9fV0sXCJyZXBvc3RzX2NvdW50XCI6MCxcImNvbW1lbnRzX2NvdW50XCI6MCxcImF0dGl0dWRlc19jb3VudFwiOjAsXCJtbGV2ZWxcIjowLFwidmlzaWJsZVwiOntcInR5cGVcIjowLFwibGlzdF9pZFwiOjB9fSxcImFsbG93X2FsbF9jb21tZW50XCI6dHJ1ZSxcImF2YXRhcl9sYXJnZVwiOlwiaHR0cDpcL1wvdHAxLnNpbmFpbWcuY25cLzE3Mjk0OTAxNjBcLzE4MFwvMFwvMVwiLFwidmVyaWZpZWRfcmVhc29uXCI6XCJcIixcImZvbGxvd19tZVwiOmZhbHNlLFwib25saW5lX3N0YXR1c1wiOjAsXCJiaV9mb2xsb3dlcnNfY291bnRcIjoxLFwibGFuZ1wiOlwiemgtY25cIixcInN0YXJcIjowLFwibWJ0eXBlXCI6MCxcIm1icmFua1wiOjAsXCJibG9ja193b3JkXCI6MH0iLCJyZWZyZXNoX3RpbWUiOiIxMzcxNDA5MjE2IiwiYWNjZXNzX3NlY3JldCI6IjVkY2RjMTlmMDhjZmU3ZThkM2MxNTM2YzAxZGYwYTk0IiwidHlwZSI6ImFuZHJvaWQiLCJhY3QiOiJzeW5jbG9naW4iLCJzaW5hX2lkIjoiMTcyOTQ5MDE2MCIsImFjY2Vzc190b2tlbiI6IjIuMDBhNWxDdEJpOHV4X0JjNThiYTA2OGRiQ3BKdjlCIiwibG9naW5fdHlwZSI6IlVTU2luYSJ9";
			$request = base64_decode((trim($_REQUEST['requestData'])));		
			$request = json_decode($request, 1);
		}
	}else
	{
		$request = $_REQUEST;
	}
}

//$domain = app_conf("PUBLIC_DOMAIN_ROOT")==''?get_domain().APP_ROOT:app_conf("PUBLIC_DOMAIN_ROOT");
//file_put_contents(APP_ROOT_PATH. "sjmapi/log/bm_".strftime("%Y%m%d%H%M%S",time()).".txt",print_r($domain,true));

$m_config = getMConfig();//初始化手机端配置
define('VERSION',1); //接口版本号,float 类型
define("CACHE_TIME",60); //动态数据缓存时间，300秒

if (intval($m_config['page_size']) == 0){
	define('PAGE_SIZE',20); //分页的常量
}else{
	define('PAGE_SIZE',intval($m_config['page_size'])); //分页的常量
}

//echo $request['act']; exit;
$class = strtolower(strim($request['act']));
$act2 = strtolower(strim($request['act_2']))?strtolower(strim($request['act_2'])):"";
$city_id = intval($request['city_id']);
define('ACT',$class); //act常量
define('ACT_2',$act2);


if(false) 
{
	$url = get_domain().APP_ROOT."/index.php?requestData=".$_REQUEST['requestData']."&r_type=2";
	$api_log = array();
	$api_log['api'] = $url;
	$api_log['act'] = $class;
	$GLOBALS['db']->autoExecute(DB_PREFIX."api_log", $api_log, 'INSERT');
}


//公共初始化
if(file_exists("./lib/".$class.".action.php"))
{
	require_once "./lib/".$class.".action.php";	
	if(class_exists($class))
	{
		$obj = new $class;		
		if(method_exists($obj,"index"))
		{
			$obj->index();
		}
		else
		{
			header("Content-Type:text/html; charset=utf-8");
			exit("Hack attemp!");
		}
	}
	else
	{
		header("Content-Type:text/html; charset=utf-8");
		exit("Hack attemp!");
	}
}
else
{
	header("Content-Type:text/html; charset=utf-8");
	exit("Hack attemp!".ACT);
}

?>