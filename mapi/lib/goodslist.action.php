<?php
class goodslist{
	public function index(){
		require_once APP_ROOT_PATH.'app/Lib/shop_lib.php'; 
		
		$catalog_id = intval($GLOBALS['request']['catalog_id']);//商品分类ID
		$city_id = intval($GLOBALS['request']['city_id']);//城市分类ID	
		$quan_id = intval($GLOBALS['request']['quan_id']); //商圈id		
		$page = intval($GLOBALS['request']['page']); //分页
		$keyword = strim($GLOBALS['request']['keyword']);
		
		
		
		
		$page=$page==0?1:$page;
		
		$page_size = PAGE_SIZE;
		$limit = (($page-1)*$page_size).",".$page_size;
	
		$condition = " deal_type = 0 ";
		if($keyword)
		{
			$kws_div = div_str($keyword);
			foreach($kws_div as $k=>$item)
			{
				$kws[$k] = str_to_unicode_string($item);
			}
			$ukeyword = implode(" ",$kws);
			$condition .=" and  (match(tag_match,name_match,locate_match,shop_cate_match) against('".$ukeyword."' IN BOOLEAN MODE) or name like '%".$keyword."%')";
		}
		
		$merchant_id = intval($GLOBALS['request']['merchant_id']);
		if($merchant_id>0)
		{
			$deal_ids = $GLOBALS['db']->getOne("select group_concat(deal_id) from ".DB_PREFIX."deal_location_link where location_id = ".$merchant_id);
			if($deal_ids)
			{

				$condition .= " and id in (".$deal_ids.") ";
			}
			else
			{
				$condition .=" and id ='' ";
			}
		}
		//根据传入的商圈ID来搜索该商圈下的商品
		if ($quan_id > 0){
			$sql_q = "select name from ".DB_PREFIX."area where id = ".intval($quan_id);
			$q_name = $GLOBALS['db']->getOne($sql_q);
			$q_name_unicode = str_to_unicode_string($q_name);
			$condition .=" and (match(locate_match) against('".$q_name_unicode."' IN BOOLEAN MODE))";
		}
		
		$deals = get_goods_list($limit,$catalog_id,$condition,"sort desc,id desc",false,$city_id);
		$list = $deals['list'];
		//var_dump($list);die;
		$count= $deals['count'];
		
		$page_total = ceil($count/$page_size);
		
		$root = array();
		$root['return'] = 1;

		
		$goodses = array();
		foreach($list as $item)
		{
			//$goods = array();
			$goods = getGoodsArray($item);
			$goodses[] = $goods;
		}
		$root['item'] = $goodses;
		$root['page'] = array("page"=>$page,"page_total"=>$page_total);

		
		output($root);
		
	}
}