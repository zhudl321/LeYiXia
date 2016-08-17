<?php	
	if (!defined("APP_NAME")) exit();
	
    $config 	= require ("./config/config.inc.php");
    $mysql 		= require ("./config/db.inc.php");
	$settings	= require ("./config/settings.inc.php");
	$login      = require ("./config/login.inc.php");
	//$login     =require("login_config.php");
    $array  = array(
	   // 'LAYOUT_ON'=>true,
		'TPM_THEME'=>'mobile',
	    'URL_MODEL'          => $settings['CUSTOM_URL_MODEL'],
	    'URL_PATHINFO_DEPR'  => $settings['CUSTOM_URL_DEPR'],	// PATHINFO模式下，各参数之间的分割符号
	    'URL_HTML_SUFFIX'    => $settings['CUSTOM_URL_SUFFIX'],  // URL伪静态后缀设置
	    'VAR_URL_PARAMS'      => '_URL_', // PATHINFO URL参数变量
        'TMPL_DETECT_THEME'     => true,       // 自动侦测模板主题
        'VAR_TEMPLATE'          => 't',		// 默认模板切换变量
       // 'URL_MODEL'             => 0,  
        
	    'DEFAULT_THEME'		=> $settings['DEFAULT_THEME'],
	    'TMPL_DETECT_THEME' => true,
        'TOKEN_ON'=>true,  // 是否开启令牌验证
		'TOKEN_NAME'=>'__hash__',    // 令牌验证的表单隐藏字段名称
		'TOKEN_TYPE'=>'md5',  //令牌哈希验证规则 默认为MD5
		'TOKEN_RESET'=>true,  //令牌验证出错后是否重置令牌 默认为true
	    /* 数据缓存设置 */
	    'DATA_CACHE_TYPE'       => 'File',  // 数据缓存类型,支持:File|Db|Apc|Memcache|Shmop|Sqlite|Xcache|Apachenote|Eaccelerator

    		
    	'LOAD_EXT_FILE'		=> 'custom,extend', //自动加载扩展函数库
		
	    'LIKE_MATCH_FIELDS'  =>'title|remark',
	    'TAG_NESTED_LEVEL'   =>3,
    		
    
    		//模板路径规则
    		'TMPL_PARSE_STRING'			=> array(
    				'__PUBLIC__'	=> __ROOT__.'/'.$settings['WEB_PUBLIC_PATH'],						//
    				'__JS__'		=> __ROOT__.'/'.$settings['WEB_PUBLIC_PATH'].'/'.APP_NAME.'/js',	
   					'__CSS__'		=> __ROOT__.'/'.$settings['WEB_PUBLIC_PATH'].'/'.APP_NAME.'/css',				//网站公用脚本目录
    				'__UPLOAD_ALBUM__'	=> __ROOT__.'/'.$settings['DIR_UPLOAD_PATH'],				//附件路径
    				'__THEMES__'	=> __ROOT__.'/'.$settings['WEB_PUBLIC_PATH'].'/themes/'.APP_NAME,	//主题默认路径
    				'__ATTACH__'	=> __ROOT__.'/'.$settings['WEB_PUBLIC_PATH'].'/'.$settings['DIR_ATTCH_PATH'],
                    '__AVATAR__'    =>__ROOT__.'/uploads/avatar',
                    '__IMG__'    =>__ROOT__.'/uploads/img',
    				'__SUCAI__'    =>__ROOT__.'/uploads/sucai',
    		),
    		//开启路由
    		'URL_ROUTER_ON'   => true, 
			'URL_ROUTE_RULES' => array( //定义路由规则
    		    'my/:name^index|page/page/:p\d'         =>'My/info',
    		    'my/:name^index|page'         =>'My/info',
    		    'my/page/:p\d'                     => 'My/index',
			    'my'                     => 'My/index',
    		   // ':name/page/:p\d'         =>'Index/index',
    		    'le/:id\d'                =>'Index/le' ,
    		    /***************************/
    		    'good/:good/page/:p\d'       =>'Index/index',
    		    'good/:good'                 =>'Index/index',   		   
    		    'good/paeg/:p\d'             =>'Index/index',
    		    'good'                       =>array('Index/index','good=1'),
    		    'hot/:hot/page/:p\d'         =>'Index/index',
    		    'hot/:hot'                   =>'Index/index',
    		    'hot/page/:p\d'              =>array('Index/index','hot=1'),
    		    'hot'                        =>array('Index/index','hot=1'),
    		    'new/page/:p\d'              =>array('Index/index','new=1'),
    		    'new'                        =>array('Index/index','new=1'),  		   
    		    /********************************************/
    		    'user/page/:uid\d/:p\d'                =>'My/userinfo',
    		    'user/:uid\d'                =>'My/userinfo',
    		    /*****************************************/
    		    'tag/:name'                  =>'Tag/tag_share',
    		    'tag'                        =>'Tag/index',
    		    /*******************************************/
    		    'so/s'                         =>'So/key',
    		    /*******************************************/
    		    'sucai'                      =>'Sucai/index',
    		    'pic'                        =>'Index/pic',
				'login/:type'               =>'Public/login',
				'le_add'               =>'Le/add',
                       'pull'             =>'Pull/index',
			),    		
    );
	
    return array_merge( $config, $settings, $array, $mysql,$login);
?>