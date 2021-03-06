<?php
class tuan_message_list{
	public function index()
	{
		$tuan_id = intval($GLOBALS['request']['tuan_id']);/*商品ID*/
		$page = intval($GLOBALS['request']['page']);/*分页*/
		
		$email = strim($GLOBALS['request']['email']);//用户名或邮箱
		$pwd = strim($GLOBALS['request']['pwd']);//密码
		
		//检查用户,用户密码
		$user = user_check($email,$pwd);
		$user_id  = intval($user['id']);
		
		$root = array();
		$root['return'] = 1;
		
		$page=$page==0?1:$page;
		$page_size = PAGE_SIZE;
		$limit = (($page-1)*$page_size).",".$page_size;
		
		
		$message_re=m_get_message_list($limit," m.rel_table = 'deal' and m.rel_id=".$tuan_id." and m.is_buy = 1",0);/*购买评论*/
		foreach($message_re['list'] as $k=>$v){
			$message_re['list'][$k]['width']= ($v['point'] / 5) * 100;
			$message_re['list'][$k]['create_time']=to_date($v['create_time']);
		}
		$root['message_list']=$message_re['list'];
		$root['message_count']=$message_re['count'];		
		
		//$deal = get_deal($tuan_id);
		
		//星级点评数
		$root['star_1'] = 0;
		$root['star_2'] = 0;
		$root['star_3'] = 0;
		$root['star_4'] = 0;
		$root['star_5'] = 0;
		$root['star_dp_width_1'] = 0;
		$root['star_dp_width_2'] = 0;
		$root['star_dp_width_3'] = 0;
		$root['star_dp_width_4'] = 0;
		$root['star_dp_width_5'] = 0;
		
		$buy_dp_sum = 0.0;
		$buy_dp_group = $GLOBALS['db']->getAll("select point,count(*) as num from ".DB_PREFIX."message where rel_id = ".$tuan_id." and rel_table = 'deal' and pid = 0 and is_buy = 1 group by point");
		foreach($buy_dp_group as $dp_k=>$dp_v)
		{
			$star = intval($dp_v['point']);
			if ($star >= 1 && $star <= 5){
				$root['star_'.$star] = $dp_v['num'];				
				$buy_dp_sum += $star * $dp_v['num'];
				$root['star_dp_width_'.$star] = (round($dp_v['num']/ $message_re['count'],1)) * 100;
			}
		}
		
		//点评平均分
		$root['buy_dp_sum']=$buy_dp_sum;
		$root['buy_dp_avg'] = round($buy_dp_sum / $message_re['count'],1);
		$root['buy_dp_width'] = (round($buy_dp_sum / $message_re['count'],1) / 5) * 100;		
		
		$page_total = ceil($message_re['count']/$page_size);
		
		$root['page'] = array("page"=>$page,"page_total"=>$page_total,"page_size"=>$page_size);
	
		$root['allow_dp'] = 0;//0:不允许点评;1:允许点评
		//判断用户是否购买了这个商品
		if ($user_id > 0){
			$sql = "select count(*) from ".DB_PREFIX."deal_order_item as doi left join ".DB_PREFIX."deal_order as do on doi.order_id = do.id where doi.deal_id = ".intval($tuan_id)." and do.user_id = ".intval($user_id)." and do.pay_status = 2";
			//$root['sql'] = $sql;
			if($GLOBALS['db']->getOne($sql)>0)
			{
				$root['allow_dp'] = 1;
			}
		}
		
		
		
		$root['tuan_id']=$tuan_id;
		$root['page_title']="点评列表";
		
		output($root);
	}
}
?>