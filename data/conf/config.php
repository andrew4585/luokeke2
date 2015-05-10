<?php	
return array (
  'SP_DEFAULT_THEME' => 'simplebootx_mobile',
  'DEFAULT_THEME' => NULL,
  'SP_ADMIN_STYLE' => NULL,
  'URL_MODEL' => 2,
  'URL_HTML_SUFFIX' => NULL,
  'UCENTER_ENABLED' => 0,
  'COMMENT_NEED_CHECK' => 0,
  'COMMENT_TIME_INTERVAL' => 0,
  'MOBILE_TPL_ENABLED' => 1,
	'THINK_SDK_QQ' =>
	array (
			'APP_KEY' => '101201968',
			'APP_SECRET' => '3e3215c3b35b1dfa4fdd1a7c5cab968f',
			'CALLBACK' => 'http://'.$_SERVER['HTTP_HOST'].__ROOT__."/Api/oauth/callback/type/qq",
	),
	'THINK_SDK_SINA' =>
	array (
			'APP_KEY' => '946092280',
			'APP_SECRET' => 'b75e7b0416606a10fc549386bc773228',
			'CALLBACK' => 'http://'.$_SERVER['HTTP_HOST'].__ROOT__.'/Api/oauth/callback/type/sina',
	),
);?>