<?php 


class TuanBaseModule{
	public function __construct()
	{
		
		if(app_conf("PUBLIC_DOMAIN_ROOT")!='')
		{
			global $syn_image_ci;
			global $curl_param;
			//global $syn_image_idx;
			$syn_image_idx = 0;
			$syn_image_ci  =  curl_init(app_conf("PUBLIC_DOMAIN_ROOT")."/es_file.php");
			curl_setopt($syn_image_ci, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($syn_image_ci, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($syn_image_ci, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($syn_image_ci, CURLOPT_NOPROGRESS, true);
			curl_setopt($syn_image_ci, CURLOPT_HEADER, false);
			curl_setopt($syn_image_ci, CURLOPT_POST, TRUE);
			curl_setopt($syn_image_ci, CURLOPT_TIMEOUT, 1);
			curl_setopt($syn_image_ci, CURLOPT_TIMECONDITION, 1);
			$curl_param['username'] = app_conf("IMAGE_USERNAME");
			$curl_param['password'] = app_conf("IMAGE_PASSWORD");
			$curl_param['act'] = 2;
		}
		
		$GLOBALS['tmpl']->assign("MODULE_NAME",MODULE_NAME);
		$GLOBALS['tmpl']->assign("ACTION_NAME",ACTION_NAME);
		
		$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/data/page_static_cache/");
		$GLOBALS['dynamic_cache'] = $GLOBALS['fcache']->get("APP_DYNAMIC_CACHE_".APP_INDEX."_".MODULE_NAME."_".ACTION_NAME);
		$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/data/avatar_cache/");
		$GLOBALS['dynamic_avatar_cache'] = $GLOBALS['fcache']->get("AVATAR_DYNAMIC_CACHE"); //头像的动态缓存
		
		//输出导航菜单
		$nav_list = get_nav_list();				
		$nav_list= init_nav_list($nav_list);	
		$GLOBALS['tmpl']->assign("nav_list",$nav_list);
		
		//输出在线客服与时间
		$qq = explode("|",app_conf("ONLINE_QQ"));
		$msn = explode("|",app_conf("ONLINE_MSN"));
		$GLOBALS['tmpl']->assign("online_qq",$qq);
		$GLOBALS['tmpl']->assign("online_msn",$msn);
		
		//输出页面的标题关键词与描述
		$GLOBALS['tmpl']->assign("shop_info",get_shop_info());
		
		//输出帮助
		$deal_help = get_help();
		$GLOBALS['tmpl']->assign("deal_help",$deal_help);
		
		//输出系统文章
		$system_article = get_article_list(8,0,"ac.type_id = 3","",true);
		$GLOBALS['tmpl']->assign("system_article",$system_article['list']);
		
		//输出热门关键词
		$hot_kw = app_conf("SHOP_SEARCH_KEYWORD");
		$hot_kw = preg_split("/[ ,]/i",$hot_kw);
		$GLOBALS['tmpl']->assign("hot_kw",$hot_kw);
		
		
	if(MODULE_NAME=="deal"&&ACTION_NAME=="index"||
		MODULE_NAME=="deals"&&ACTION_NAME=="index"||
		MODULE_NAME=="dhapi"&&ACTION_NAME=="index"||
		MODULE_NAME=="index"&&ACTION_NAME=="index"||
		MODULE_NAME=="message"&&ACTION_NAME=="index"||
		MODULE_NAME=="order"&&ACTION_NAME=="index"||
		MODULE_NAME=="search"&&ACTION_NAME=="index"||
		MODULE_NAME=="second"&&ACTION_NAME=="index")
		{
			set_gopreview();
		}
	}
	
	public function index()
	{
		showErr("invalid access");
	}
	public function __destruct()
	{
		if(isset($GLOBALS['fcache']))
		{
			$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/data/page_static_cache/");
			$GLOBALS['fcache']->set("APP_DYNAMIC_CACHE_".APP_INDEX."_".MODULE_NAME."_".ACTION_NAME,$GLOBALS['dynamic_cache']);
			if(count($GLOBALS['dynamic_avatar_cache'])<=500)
			{
				$GLOBALS['fcache']->set_dir(APP_ROOT_PATH."public/runtime/data/avatar_cache/");
				$GLOBALS['fcache']->set("AVATAR_DYNAMIC_CACHE",$GLOBALS['dynamic_avatar_cache']); //头像的动态缓存
			}
		}
		
		if(app_conf("PUBLIC_DOMAIN_ROOT")!='')
		{
			if(count($GLOBALS['curl_param']['images'])>0)
			{
				$GLOBALS['curl_param']['images'] =  base64_encode(serialize($GLOBALS['curl_param']['images']));
				curl_setopt($GLOBALS['syn_image_ci'], CURLOPT_POSTFIELDS, $GLOBALS['curl_param']);
				$rss = curl_exec($GLOBALS['syn_image_ci']);
			}
			curl_close($GLOBALS['syn_image_ci']);
			echo $rss;exit;
		}
		unset($this);
	}
}
?>