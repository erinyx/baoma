<?php


function check_issupplier()
{
	$account_name = $GLOBALS['user_info']['merchant_name'];
	$account = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier_account where account_name = '".$account_name."' and is_effect = 1 and is_delete = 0");
	if($account)
	{
		$s_account_info = es_session::get("account_info");
		if(intval($s_account_info['id'])==0)
		{
			showErr("您已经是商家会员，请登录",0,url("biz"));
		}
		else
		app_redirect(url("biz"));
	}

}
class joinModule extends BizBaseModule
{
		
	
	public function index()
	{		
		check_issupplier();
		app_redirect(url("biz","join#step1"));
//		$GLOBALS['tmpl']->assign("page_title","商家申请");		
//		$GLOBALS['tmpl']->display("biz/biz_join.html");
	}
	
	public function step1()
	{		
		check_issupplier();
		$user_id = intval($GLOBALS['user_info']['id']);
		if($user_id==0)
		{
			es_session::set('before_login',$_SERVER['REQUEST_URI']);
			app_redirect(url("shop","user#login"));
		}
		
		$cate_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_cate where is_effect = 1 and is_delete = 0 order by sort desc");
		$GLOBALS['tmpl']->assign("cate_list",$cate_list);
		
		$deal_city_list = get_deal_citys();
		$GLOBALS['tmpl']->assign("city_list",$deal_city_list['ls']);
		
		$GLOBALS['tmpl']->assign("step",1);
		
		$GLOBALS['tmpl']->assign("page_title","商家入驻");		
		$GLOBALS['tmpl']->display("biz/biz_join_step1.html");
	}
	
	public  function step2()
	{
		check_issupplier();
		$user_id = intval($GLOBALS['user_info']['id']);
		if($user_id==0)
		{
			app_redirect(url("shop","user#login"));
		}
		$location_id = intval($_REQUEST['location_id']);
		$location = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier_location where id = ".$location_id." and is_effect = 1");
		if($location)
		{
			$account_info = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier_account where account_name = '".$GLOBALS['user_info']['merchant_name']."'");
			if($account_info&&$location['supplier_id']!=$account_info['supplier_id'])
			{
				showErr("这家商户不是您的，您不能认领");
			}
			else
			{				
				$data['name'] = $location['name'];
				$data['deal_cate_id'] = $location['deal_cate_id'];
				$deal_cate_type_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."deal_cate_type_location_link where location_id = ".$location['id']);
				foreach($deal_cate_type_list as $type)
				{
					$data['deal_cate_type_id'][] = $type['deal_cate_type_id'];
				}
				$area_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."supplier_location_area_link where location_id = ".$location['id']);
				foreach($area_list as $area)
				{
					$data['area_id'][] = $area['area_id'];
				}
				$data['address'] = $location['address'];
				$data['xpoint'] = $location['xpoint'];
				$data['ypoint'] = $location['ypoint'];
				$data['tel'] = $location['tel'];
				$data['open_time'] = $location['open_time'];
				$data['location_id'] = $location['id'];
				$data['city_id'] = intval($location['city_id']);
			}
		}
		elseif($_POST)
		{
			$data['name'] =  addslashes(htmlspecialchars(trim($_REQUEST['name'])));
			$data['deal_cate_id'] = intval($_REQUEST['deal_cate_id']);
			foreach($_REQUEST['deal_cate_type_id'] as $type)
			{
				$data['deal_cate_type_id'][] = intval($type);
			}
			foreach($_REQUEST['area_id'] as $area)
			{
				$data['area_id'][] = intval($area);
			}
			$data['address'] = addslashes(htmlspecialchars(trim($_REQUEST['address'])));
			$data['xpoint'] = doubleval($_REQUEST['xpoint']);
			$data['ypoint'] = doubleval($_REQUEST['ypoint']);
			$data['tel'] = addslashes(htmlspecialchars(trim($_REQUEST['tel'])));
			$data['open_time'] = addslashes(htmlspecialchars(trim($_REQUEST['open_time'])));
			$data['location_id'] = 0;
			$data['city_id'] = intval($_REQUEST['city_id']);
		}
		else
		{
			app_redirect(url("biz","join#step1"));
		}
		
		$GLOBALS['tmpl']->assign("base_data",base64_encode(serialize($data)));		
		$GLOBALS['tmpl']->assign("step",2);		
		$GLOBALS['tmpl']->assign("page_title","签协议");		
		$GLOBALS['tmpl']->display("biz/biz_join_step2.html");		
	}
	
	
	public  function step3()
	{
		check_issupplier();
		$user_id = intval($GLOBALS['user_info']['id']);
		if($user_id==0)
		{
			app_redirect(url("shop","user#login"));
		}		
		$base_data =  addslashes(htmlspecialchars(trim($_REQUEST['base_data'])));
		
		$data = unserialize(base64_decode($base_data));
		
		$location_id = intval($data['location_id']);
		//获取文件大小限制
		$file_max_upload = intval(app_conf("MAX_IMAGE_SIZE")/1024);
		$GLOBALS['tmpl']->assign("file_max_upload",$file_max_upload);
		$GLOBALS['tmpl']->assign("location_id",$location_id);
		$GLOBALS['tmpl']->assign("base_data", $base_data);
		$GLOBALS['tmpl']->assign("step",3);
		
		$GLOBALS['tmpl']->assign("page_title","填信息");		
		$GLOBALS['tmpl']->display("biz/biz_join_step3.html");
	}
	
	public function step4()
	{
		check_issupplier();
		$user_id = intval($GLOBALS['user_info']['id']);
		if($user_id==0)
		{
			app_redirect(url("shop","user#login"));
		}
		
//		print_r( unserialize(base64_decode($_REQUEST['base_data'])));exit;
		
		//上传处理
		//创建attachment目录
		if($_POST)
		{ /*
			if (!is_dir(APP_ROOT_PATH."public/attachment")) { 
		             @mkdir(APP_ROOT_PATH."public/attachment");
		             @chmod(APP_ROOT_PATH."public/attachment", 0777);
		        }
			
		    $dir = to_date(get_gmtime(),"Ym");
		    if (!is_dir(APP_ROOT_PATH."public/attachment/".$dir)) { 
		             @mkdir(APP_ROOT_PATH."public/attachment/".$dir);
		             @chmod(APP_ROOT_PATH."public/attachment/".$dir, 0777);
		        }
		        
		    $dir = $dir."/".to_date(get_gmtime(),"d");
		    if (!is_dir(APP_ROOT_PATH."public/attachment/".$dir)) { 
		             @mkdir(APP_ROOT_PATH."public/attachment/".$dir);
		             @chmod(APP_ROOT_PATH."public/attachment/".$dir, 0777);
		        }
		     
		    $dir = $dir."/".to_date(get_gmtime(),"H");
		    if (!is_dir(APP_ROOT_PATH."public/attachment/".$dir)) { 
		             @mkdir(APP_ROOT_PATH."public/attachment/".$dir);
		             @chmod(APP_ROOT_PATH."public/attachment/".$dir, 0777);
		        }
			*/
			if($_REQUEST['h_license']!='')
		    {
				$save_data['h_license'] = addslashes(htmlspecialchars(trim(replace_public($_REQUEST['h_license']))));
		    }
		    else {
		    	$messages[] = "营业执照必需上传";
		    }
		    if($_REQUEST['h_other_license']!='')
		    {
				$save_data['h_other_license'] = addslashes(htmlspecialchars(trim(replace_public($_REQUEST['h_other_license']))));
		    }
		    if($_REQUEST['h_supplier_logo']!='')
		    {
				$save_data['h_supplier_logo'] = addslashes(htmlspecialchars(trim(replace_public($_REQUEST['h_supplier_logo']))));
		    }
		    if($_REQUEST['h_supplier_image']!='')
		    {
				$save_data['h_supplier_image'] = addslashes(htmlspecialchars(trim(replace_public($_REQUEST['h_supplier_image']))));
		    }
			
		    if(count($messages)==0)
		    {
		    	$base_data = unserialize(base64_decode($_REQUEST['base_data']));
		    	$save_data['name'] = $base_data['name'];
		    	$save_data['cate_config'] = serialize(array('deal_cate_id'=>$base_data['deal_cate_id'],'deal_cate_type_id'=>$base_data['deal_cate_type_id']));
		   		$save_data['location_config'] = serialize($base_data['area_id']);
		   		$save_data['address'] = $base_data['address'];
		   		$save_data['tel'] = $base_data['tel'];
		   		$save_data['open_time'] = $base_data['open_time'];
		   		$save_data['xpoint'] = $base_data['xpoint'];
		   		$save_data['ypoint'] = $base_data['ypoint'];
		   		$save_data['location_id'] = $base_data['location_id'];
		   		$save_data['city_id'] = $base_data['city_id'];
		   		$save_data['user_id'] = $user_id;
		   		$save_data['create_time'] = get_gmtime();
		   		$save_data['h_name'] = addslashes(htmlspecialchars(trim($_REQUEST['h_name'])));
		   		$save_data['h_faren'] = addslashes(htmlspecialchars(trim($_REQUEST['h_faren'])));
		   		$save_data['h_user_name'] = addslashes(htmlspecialchars(trim($_REQUEST['h_user_name'])));
		   		$save_data['h_tel'] = addslashes(htmlspecialchars(trim($_REQUEST['h_tel'])));
		   		$save_data['h_bank_info'] = addslashes(htmlspecialchars(trim($_REQUEST['h_bank_info'])));
		   		$save_data['h_bank_name'] = addslashes(htmlspecialchars(trim($_REQUEST['h_bank_name'])));
		   		$save_data['h_bank_user'] = addslashes(htmlspecialchars(trim($_REQUEST['h_bank_user'])));

		   		if($save_data['h_name']=='')
		   		$messages[] = "请填写企业名称";
		   		
		   		if($save_data['h_faren']=='')
		   		$messages[] = "请填写法人姓名";
		   		
		   		if($save_data['h_bank_info']=='')
		   			$messages[] = "请填写开户行帐号";
		   		
		   		if($save_data['h_bank_name']=='')
		   			$messages[] = "请填写开户行名称";
		   		
		   		if($save_data['h_bank_user']=='')
		   			$messages[] = "请填写开户行户名";
		   		
		   		if(count($messages)==0)	   		
		   		{
					$GLOBALS['db']->autoExecute(DB_PREFIX."supplier_submit",$save_data);
					app_redirect(url("biz","join#step4",array("s"=>"over")));
		   		}
				else
				{
					$GLOBALS['tmpl']->assign("error_messages",$messages);
				}
		    }
		    else 
		    {
		    	$GLOBALS['tmpl']->assign("error_messages",$messages);
		    }
		}	
	    
		$GLOBALS['tmpl']->assign("step",4);		
		$GLOBALS['tmpl']->assign("page_title","完成");		
		$GLOBALS['tmpl']->display("biz/biz_join_step4.html");
//		print_r($_REQUEST);
//		print_r( unserialize(base64_decode($_REQUEST['base_data'])));
	}
	
	public function load_sub_cate()
	{
		$cate_id = intval($_REQUEST['id']);
		$type_list = $GLOBALS['db']->getAll("select t.* from ".DB_PREFIX."deal_cate_type as t left join ".DB_PREFIX."deal_cate_type_link as l on l.deal_cate_type_id = t.id where l.cate_id = ".$cate_id);
		$html = "";
		foreach($type_list as $item)
		{
			$html.="<input type='checkbox' name='deal_cate_type_id[]' value='".$item['id']."' />".$item['name']."&nbsp;&nbsp;";
		}

		header("Content-Type:text/html; charset=utf-8");
		echo $html;
	}
	
	public function load_city_area()
	{
		$city_id = intval($_REQUEST['id']);
		$area_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."area where city_id = ".$city_id." and pid = 0 order by sort desc");
		$html = "";
		if($area_list)
		{
			$html = "<select name='area_id[]'>";
			foreach($area_list as $item)
			{
				$html .= "<option value='".$item['id']."'>".$item['name']."</option>";
			}
			$html.="</select>";
		}
		header("Content-Type:text/html; charset=utf-8");
		echo $html;
		
	}
	
	public function load_quan_list()
	{
		$area_id = intval($_REQUEST['id']);
		$area_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."area where pid = ".$area_id." order by sort desc");
		$html = "";
		foreach($area_list as $item)
		{
			$html.="<input type='checkbox' name='area_id[]' value='".$item['id']."' />".$item['name']."&nbsp;&nbsp;";
		}

		header("Content-Type:text/html; charset=utf-8");
		echo $html;
	}
}
?>