<?php
class login{
	public function index()
	{
		require_once APP_ROOT_PATH."system/libs/user.php";
		$email = strim($GLOBALS['request']['email']);//用户名或邮箱
		$pwd = strim($GLOBALS['request']['pwd']);//密码
		$city_name =strim($GLOBALS['request']['city_name']);//城市名称
		
		if($email!="" && $pwd!=""){
			
			$result = do_login_user($email,$pwd);
			
			if($result['status'])
			{
				$user_data = es_session::get('user_info');
				$root['return'] = 1;
				$root['user_login_status'] = 1;//用户登陆状态：1:成功登陆;0：未成功登陆
				$root['info'] = "用户登陆成功";	
				$root['uid'] = $user_data['id'];
				$root['user_name'] = $user_data['user_name'];
				$root['user_pwd'] = $user_data['user_pwd'];
				$root['user_email'] = $user_data['email'];
				$root['user_money'] = $user_data['money'];				
				$root['user_money_format'] = format_price($user_data['money']);//用户金额
				$root['user_score'] = $user_data['score'];//用户积分
				$root['home_user']['fans'] = $user_data['focused_count'];
				$root['home_user']['photos'] = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."topic_image where user_id = ".$user_data['id']);
				$root['home_user']['goods'] = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."topic where user_id = ".$user_data['id']." and topic_group = 'Fanwe' and is_delete = 0 and is_effect = 1");
				$root['home_user']['follows'] = $user_data['focus_count'];	
				$root['home_user']['favs'] = $GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."topic where user_id = ".$user_data['id']." and fav_id <> 0");
				
				$root['home_user']['user_avatar'] = get_abs_img_root(get_muser_avatar($user_data['id'],"big"));
				$root['user_avatar'] = get_abs_img_root(get_muser_avatar($user_data['id'],"big"));
				
				if(strim($GLOBALS['request']['sina_id'])!='')
				{
					if($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user where sina_id = '".strim($GLOBALS['request']['sina_id'])."'")==0)
					{
						$access_token =  trim($GLOBALS['request']['access_token']);
						$GLOBALS['db']->query("update ".DB_PREFIX."user set sina_id = '".strim($GLOBALS['request']['sina_id'])."',sina_token = '".$access_token."' where id = ".$user_data['id']);				
					
					}
					
	
				}
				if(strim($GLOBALS['request']['tencent_id'])!='')
				{
					if($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user where tencent_id = '".strim($GLOBALS['request']['tencent_id'])."'")==0)
					{
					$GLOBALS['db']->query("update ".DB_PREFIX."user set tencent_id = '".strim($GLOBALS['request']['tencent_id'])."' where id = ".$user_data['id']);
				
					$openid = trim($GLOBALS['request']['openid']);
					$openkey = trim($GLOBALS['request']['openkey']);
			 		$access_token =  trim($GLOBALS['request']['access_token']);
					$GLOBALS['db']->query("update ".DB_PREFIX."user set t_access_token ='".$access_token."',t_openkey = '".$openkey."',t_openid = '".$openid."', login_ip = '".get_client_ip()."',login_time= ".get_gmtime()." where id =".$user_data['id']);	
					}
					
				}
				
				if(strim($GLOBALS['request']['qq_id'])!='')
				{
					if($GLOBALS['db']->getOne("select count(*) from ".DB_PREFIX."user where qq_id = '".strim($GLOBALS['request']['qq_id'])."'")==0)
					{
						$GLOBALS['db']->query("update ".DB_PREFIX."user set qq_id = '".strim($GLOBALS['request']['qq_id'])."' where id = ".$user_data['id']);				
					}
				}
			}
			else
			{
				if($result['data'] == ACCOUNT_NO_EXIST_ERROR)
				{
					$err = "会员不存在";
				}
				if($result['data'] == ACCOUNT_PASSWORD_ERROR)
				{
					$err = "密码错误";
				}
				if($result['data'] == ACCOUNT_NO_VERIFY_ERROR)
				{
					$err = "会员未通过验证";
				}
				$root['return'] = 0;
				$root['user_login_status'] = 0;//用户登陆状态：1:成功登陆;0：未成功登陆
				$root['info'] = $err;		
				$root['uid'] = 0;
				$root['user_name'] = $email;
				$root['user_email'] = $email;
				
				
			}
			
			if(strim($GLOBALS['request']['sina_id'])!='')
			{
				$root['login_type'] = "Sina";
			}
			if(strim($GLOBALS['request']['tencent_id'])!='')
			{
				$root['login_type'] = "Tencent";
			}
		}else{
			$root['return'] = 0;
			$root['user_login_status'] = 0;//用户登陆状态：1:成功登陆;0：未成功登陆
			$root['info'] = "用户或密码不能为空";
			$root['uid'] = 0;
			$root['user_name'] = $email;
		}
		if($email){
			$root['page_title']="我的账户";
			$root['user_avatar'] = get_abs_img_root(get_muser_avatar($user_data['id'],"big"));
		}else{
			$root['page_title']="登陆";
			
			$api = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."api_login where class_name = 'Qqv2'");
			if ($api){
				$class_name = $api['class_name'];
				if(file_exists(APP_ROOT_PATH."system/api_login/".$class_name."_api.php"))
				{
					require_once APP_ROOT_PATH."system/api_login/".$class_name."_api.php";
					$api_class = $class_name."_api";
					$api_obj = new $api_class($api);
					$url = $api_obj->get_api_url();
					//$root['qq_login1'] = $url;
					
					$domain = app_conf("PUBLIC_DOMAIN_ROOT")==''?get_domain().APP_ROOT.$GLOBALS['IMG_APP_ROOT']:app_conf("PUBLIC_DOMAIN_ROOT");
					$url = str_replace("./public/",$domain."/public/",$url);
					$url = str_replace('sjmapi/', '', $url);
					
					$root['qq_login'] = $url;
				}				
			}
		}
		$root['city_name']=$city_name;
		$root['act'] = "login";
		output($root);		
	}
}
?>