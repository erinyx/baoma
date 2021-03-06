<?php
class youhuilist
{
	public function index()
	{

		$root = array();
		$root['return'] = 1;
		
		
		$city_id = intval($GLOBALS['request']['city_id']);
		$quan_id = intval($GLOBALS['request']['quan_id']);
		$cate_id = intval($GLOBALS['request']['cate_id']);
		$keyword = strim($GLOBALS['request']['keyword']);
		$city_name =strim($GLOBALS['request']['city_name']);//城市名称
		
		$order_type=$GLOBALS['request']['order_type'];
		
		$ytop = $latitude_top = floatval($GLOBALS['request']['latitude_top']);//最上边纬线值 ypoint
		$ybottom = $latitude_bottom = floatval($GLOBALS['request']['latitude_bottom']);//最下边纬线值 ypoint
		$xleft = $longitude_left = floatval($GLOBALS['request']['longitude_left']);//最左边经度值  xpoint
		$xright = $longitude_right = floatval($GLOBALS['request']['longitude_right']);//最右边经度值 xpoint
		$m_distance = doubleval($GLOBALS['request']['m_distance']);   //范围(米)
		$ypoint =  $m_latitude = doubleval($GLOBALS['request']['m_latitude']);  //ypoint 
		$xpoint = $m_longitude = doubleval($GLOBALS['request']['m_longitude']);  //xpoint
		
		
		
		$page = intval($GLOBALS['request']['page']); //分页
		
		$page=$page==0?1:$page;
		
		$page_size = PAGE_SIZE;
		$limit = (($page-1)*$page_size).",".$page_size;
				
		$pi = 3.14159265;  //圆周率
		$r = 6378137;  //地球平均半径(米)		

		
		$sql_count = "select count(*) from ".DB_PREFIX."youhui ";
		
		$sql = "select id,supplier_id as merchant_id,begin_time,youhui_type,total_num,end_time,name as title,list_brief as content,icon as merchant_logo,create_time,xpoint,ypoint,address as api_address,icon as image_1,
				 (ACOS(SIN(($ypoint * $pi) / 180 ) *SIN((ypoint * $pi) / 180 ) +COS(($ypoint * $pi) / 180 ) * COS((ypoint * $pi) / 180 ) *COS(($xpoint * $pi) / 180 - (xpoint * $pi) / 180 ) ) * $r) as distance  
				from ".DB_PREFIX."youhui ";
		
		$now = get_gmtime();
		$where = "1 = 1 and is_effect = 1 and begin_time<".$now." and (end_time = 0 or end_time > ".$now.")";

			
		if($city_id>0)
		{			
				$ids = load_auto_cache("deal_city_belone_ids",array("city_id"=>$city_id));
				if($ids)
				{
				$where .= " and city_id in (".implode(",",$ids).")";
				}
		}

			
		if($quan_id > 0)
		{
			$ids = load_auto_cache("deal_quan_ids",array("quan_id"=>$quan_id));
			$quan_list2 = $GLOBALS['db']->getAll("select `name` from ".DB_PREFIX."area where id in (".implode(",",$ids).")");
			$unicode_quans = array();
			foreach($quan_list2 as $k=>$v){
				$unicode_quans[] = str_to_unicode_string($v['name']);
			}
			$kw_unicode = implode(" ", $unicode_quans);
			$where .= " and (match(locate_match) against('".$kw_unicode."' IN BOOLEAN MODE)) ";
		}
		
		if ($cate_id > 0)
			$where .= " and deal_cate_id = $cate_id";
		
		if($keyword)
		{				
			$kws_div = div_str($keyword);
			foreach($kws_div as $k=>$item)
			{
				$kw[$k] = str_to_unicode_string($item);
			}
			$ukeyword = implode(" ",$kw);
			$where.=" and match(name_match) against('".$ukeyword."'  IN BOOLEAN MODE) or name like '%".$keyword."%' ";
		}
		$merchant_id = intval($GLOBALS['request']['merchant_id']);
			if($merchant_id>0)
			{
				$youhui_ids = $GLOBALS['db']->getOne("select group_concat(youhui_id) from ".DB_PREFIX."youhui_location_link where location_id = ".$merchant_id);
				if($youhui_ids)
				{
					$where .= " and id in (".$youhui_ids.") ";
				}
				else
				{
					$where .= " and id = 0 ";
				}
			}
		
		$sql_count.=" where ".$where;
		$sql.=" where ".$where;
		
		
		if($xpoint>0)/*排序  default 智能（默认），nearby  离我  都 是按距离升级排序*/
		{
			$orderby = " order by distance asc ";
		}
		else
		{
			$orderby = " order by sort desc,id desc";
		}
		
		/*排序*/
		if($order_type=='avg_point')
			$orderby= " order by avg_point desc,id desc ";
		elseif($order_type=='newest')
			$orderby= " order by create_time desc,id desc ";
		elseif($order_type=='buy_count')
			$orderby= " order by view_count desc,id desc ";		

		$sql.= $orderby. " limit ".$limit;
		
		//echo $sql; exit;
				
		$total = $GLOBALS['db']->getOne($sql_count);
		$page_total = ceil($total/$page_size);

		
		$list = $GLOBALS['db']->getAll($sql);
		$youhui_list = array();
		foreach($list as $item){
			$youhui_list[] = m_youhuiItem($item);
		}
		
		/*输出分类*/
		$bcate_list = getCateList();
		/*输出商圈*/
		$quan_list=getQuanList($city_id);
		
		$root['bcate_list']= $bcate_list;
		$root['quan_list']= $quan_list;
		$root['item'] = $youhui_list;
		$root['page'] = array("page"=>$page,"page_total"=>$page_total,"page_size"=>$page_size);
		$root['page_title']="优惠券列表";//fwb 2014-08-27
		$root['city_name']=$city_name;
		output($root);
	}
}
?>