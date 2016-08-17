<?php

define('URL_CALLBACK', 'http://www.leyix.com/index.php?m=Public&a=callback&type=');

return array(

//腾讯QQ登录配置
	'THINK_SDK_QQ' => array(
		'APP_KEY'    => '100494047', //应用注册成功后分配的 APP ID
		'APP_SECRET' => 'db93916ec5736a97e6989728333cd2a4', //应用注册成功后分配的KEY
		'CALLBACK'   => URL_CALLBACK . 'qq',
),
//腾讯微博配置
	'THINK_SDK_TENCENT' => array(
		'APP_KEY'    => '801236614', //应用注册成功后分配的 APP ID
		'APP_SECRET' => '573e394a43f29e9b49beb603db00a8a9', //应用注册成功后分配的KEY
		'CALLBACK'   => URL_CALLBACK . 'tencent',
),
//新浪微博配置
	'THINK_SDK_SINA' => array(
		'APP_KEY'    => '446036218', //应用注册成功后分配的 APP ID
		'APP_SECRET' => 'fbff644f10b1d94bba8a2c79fd5fc8a0', //应用注册成功后分配的KEY
		'CALLBACK'   => URL_CALLBACK . 'sina',
),
//网易微博配置
	'THINK_SDK_T163' => array(
		'APP_KEY'    => 'lMpe5kWfjjGIQsRm', //应用注册成功后分配的 APP ID
		'APP_SECRET' => 'y5pRzpArgwRBy1XyUPLhkImss4RUiqkl', //应用注册成功后分配的KEY
		'CALLBACK'   => URL_CALLBACK . 't163',
),
//人人网配置
	'THINK_SDK_RENREN' => array(
		'APP_KEY'    => 'be5f690d36284a8494be4729c382f6bc', //应用注册成功后分配的 APP ID
		'APP_SECRET' => 'aa4fa0863ed74835b43f99b90f5b7da2', //应用注册成功后分配的KEY
		'CALLBACK'   => URL_CALLBACK . 'renren',
),
//360配置
	'THINK_SDK_X360' => array(
		'APP_KEY'    => '1cb82bcb76e63e9878e8320d9fb7438c', //应用注册成功后分配的 APP ID
		'APP_SECRET' => '10417d219dcde6c3e6b9a4525ab7fdb6', //应用注册成功后分配的KEY
		'CALLBACK'   => URL_CALLBACK . 'x360',
),
//豆瓣配置
	'THINK_SDK_DOUBAN' => array(
		'APP_KEY'    => '00db7ff2b390bf4002927fd81ab23dc0', //应用注册成功后分配的 APP ID
		'APP_SECRET' => '03047d2a3222076f', //应用注册成功后分配的KEY
		'CALLBACK'   => URL_CALLBACK . 'douban',
),
//Github配置
	'THINK_SDK_GITHUB' => array(
		'APP_KEY'    => '', //应用注册成功后分配的 APP ID
		'APP_SECRET' => '', //应用注册成功后分配的KEY
		'CALLBACK'   => URL_CALLBACK . 'github',
),
//Google配置
	'THINK_SDK_GOOGLE' => array(
		'APP_KEY'    => '', //应用注册成功后分配的 APP ID
		'APP_SECRET' => '', //应用注册成功后分配的KEY
		'CALLBACK'   => URL_CALLBACK . 'google',
),
//MSN配置
	'THINK_SDK_MSN' => array(
		'APP_KEY'    => '', //应用注册成功后分配的 APP ID
		'APP_SECRET' => '', //应用注册成功后分配的KEY
		'CALLBACK'   => URL_CALLBACK . 'msn',
),
//点点配置
	'THINK_SDK_DIANDIAN' => array(
		'APP_KEY'    => '', //应用注册成功后分配的 APP ID
		'APP_SECRET' => '', //应用注册成功后分配的KEY
		'CALLBACK'   => URL_CALLBACK . 'diandian',
),
//淘宝网配置
	'THINK_SDK_TAOBAO' => array(
		'APP_KEY'    => '21153941', //应用注册成功后分配的 APP ID
		'APP_SECRET' => '0b0839677ff43708dd2e72611dbc5dd4', //应用注册成功后分配的KEY
		'CALLBACK'   => URL_CALLBACK . 'taobao',
),
//百度配置
	'THINK_SDK_BAIDU' => array(
		'APP_KEY'    => 'Yevht1FMuicdMBIXhFKcwSSp', //应用注册成功后分配的 APP ID
		'APP_SECRET' => 'dj7T2VTEP6wFb49Uh9XSGH8gMnSGFnLI', //应用注册成功后分配的KEY
		'CALLBACK'   => URL_CALLBACK . 'baidu',
),
//开心网配置
	'THINK_SDK_KAIXIN' => array(
		'APP_KEY'    => '942123287524c629baf2c1618d40727c', //应用注册成功后分配的 APP ID
		'APP_SECRET' => 'd5c09337f96a2ba30c1bc92fc6db5777', //应用注册成功后分配的KEY
		'CALLBACK'   => URL_CALLBACK . 'kaixin',
),
//搜狐微博配置
	'THINK_SDK_SOHU' => array(
		'APP_KEY'    => '', //应用注册成功后分配的 APP ID
		'APP_SECRET' => '', //应用注册成功后分配的KEY
		'CALLBACK'   => URL_CALLBACK . 'sohu',
),

);
?>