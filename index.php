<?PHP

function is_mobile(){
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $mobile_agents = Array("240x320","acer","acoon","acs-","abacho","ahong","airness","alcatel","amoi","android","anywhereyougo.com","applewebkit/525","applewebkit/532","asus","audio","au-mic","avantogo","becker","benq","bilbo","bird","blackberry","blazer","bleu","cdm-","compal","coolpad","danger","dbtel","dopod","elaine","eric","etouch","fly ","fly_","fly-","go.web","goodaccess","gradiente","grundig","haier","hedy","hitachi","htc","huawei","hutchison","inno","ipad","ipaq","ipod","jbrowser","kddi","kgt","kwc","lenovo","lg ","lg2","lg3","lg4","lg5","lg7","lg8","lg9","lg-","lge-","lge9","longcos","maemo","mercator","meridian","micromax","midp","mini","mitsu","mmm","mmp","mobi","mot-","moto","nec-","netfront","newgen","nexian","nf-browser","nintendo","nitro","nokia","nook","novarra","obigo","palm","panasonic","pantech","philips","phone","pg-","playstation","pocket","pt-","qc-","qtek","rover","sagem","sama","samu","sanyo","samsung","sch-","scooter","sec-","sendo","sgh-","sharp","siemens","sie-","softbank","sony","spice","sprint","spv","symbian","tablet","talkabout","tcl-","teleca","telit","tianyu","tim-","toshiba","tsm","up.browser","utec","utstar","verykool","virgin","vk-","voda","voxtel","vx","wap","wellco","wig browser","wii","windows ce","wireless","xda","xde","zte");
    $is_mobile = false;
    foreach ($mobile_agents as $device) {
        if (stristr($user_agent, $device)) {
            $is_mobile = true;
            break;
        }
    }
    return $is_mobile;
}
if(is_mobile()){ //跳转至wap分组
    define('THEME_NAME','Leyix');
}else{
    define('THEME_NAME','Leyix');      
}
//调试模式
	define( 'APP_DEBUG',		false );
	define( 'NO_CACHE_RUNTIME',	true );
	
	//主配置项目
	define( 'APP_NAME', 		'home' );							// 项目名称
	define( 'APP_PATH', 		'./' . APP_NAME . '/' );					// 项目目录
	define( 'COMMON_PATH', 		APP_PATH . 'common/' );						// 项目公共目录
	define( 'LIB_PATH', 		APP_PATH . 'library/' );					// 项目类库目录
	define( 'CONF_PATH', 		APP_PATH . 'configs/' );					// 项目配置目录
	define( 'LANG_PATH', 		APP_PATH . 'language/' );					// 项目语言包目录
	define( 'TMPL_PATH', 		APP_PATH . 'themes/' );						// 项目模板目录
	define( 'HTML_PATH', 		APP_PATH . 'static/' );						// 项目静态目录
	define( 'RUNTIME_PATH', 	'./cache/' . APP_NAME . '/' );				// 项目临时文件主目录
	
	//网站缓存配置
	define( 'LOG_PATH', 		RUNTIME_PATH . 'logs/' );					// 项目日志目录
	define( 'TEMP_PATH', 		RUNTIME_PATH . 'temp/' );					// 项目缓存目录
	define( 'DATA_PATH', 		RUNTIME_PATH . 'data/' );					// 项目数据目录
	define( 'CACHE_PATH', 		RUNTIME_PATH . 'cache/' );					// 项目模板缓存目录

	define( '__ROOT_PATH__' , str_replace("\\", '/', dirname(__FILE__) ) );
	
	//运行项目
	require ( './core/leyix.php' );
?>