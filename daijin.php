<?php 
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------

require './system/common.php';
require './app/Lib/YouhuiApp.class.php';

$_REQUEST['ctl']="index";
$_REQUEST['act']="daijin_index";

//实例化一个网站应用实例
$AppWeb = new YouhuiApp(); 

?>