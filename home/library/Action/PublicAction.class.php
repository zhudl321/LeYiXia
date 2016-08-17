<?php
class PublicAction extends BaseAction{

	public function login($type = null){
		import("ORG.SDK.ThinkOauth");
		$sns=ThinkOauth::getInstance($type);
		redirect($sns->getRequestCodeURL());
	}
	public function cklogin(){
		$m=M('user');
		$password=I('post.password');
		$username=I('post.username');
		if ($password==''){
			$this->ajaxReturn('password','请输入密码',0);
		}
		if ($username==''){
			$this->ajaxReturn('username','请输入邮箱账号',0);
		}
		$user=$m->where(array('username'=>$username))->find();
		if (empty($user)){
			$this->ajaxReturn('username','邮箱账号不存在',0);
		}else{
			$password=md5($password);
			if($password==$user['password']){
				$this->login_ok($user['id'],false);
			}else{
				$this->ajaxReturn('password','密码错误',0);
			}
		}
	}
	/**
	 * 登陆成功处理方法
	 * Enter description here ...
	 * @param unknown_type $uid
	 */
	protected function login_ok($uid,$return=true){
		$userinfo=M('user')->find($uid);
		//cookie保存
		//cookie('USERID',$userinfo['uid'],array('expire'=>'1800','domain'=>'leyix.com'));
		//cookie('user',$userinfo,array('expire'=>'1800','domain'=>'leyix.com'));
		//session
		$_SESSION['USERID']=md5($uid);
		$_SESSION['user']=$userinfo;
		if ($return){
			echo '<script type="text/javascript">window.opener.location.reload();window.close();</script>';
		}else{
			$this->ajaxReturn($userinfo,'登陆成功',1);
		}
	}
	/**
	 * 退出登陆
	 * Enter description here ...
	 */
	public function logout(){
		$_SESSION['USERID']=null;
		$_SESSION['user']=null;
		cookie('USERID',$userinfo['uid'],array('expire'=>'-1800','domain'=>'leyix.com'));
		cookie('user',$userinfo,array('expire'=>'-1800','domain'=>'leyix.com'));
		unset($_COOKIE);
		unset($_SESSION);
		echo '<script type="text/javascript">self.location=document.referrer;</script>';
	}
	/**
	 * 登陆回调地址
	 * Enter description here ...
	 * @param unknown_type $type
	 * @param unknown_type $code
	 */
	public function callback($type = null, $code = null){
		(empty($type) || empty($code)) && $this->error('参数错误');
		//加载ThinkOauth类并实例化一个对象
		import("ORG.SDK.ThinkOauth");
		$sns  = ThinkOauth::getInstance($type);
		//腾讯微博需传递的额外参数
		$extend = null;
		if($type == 'tencent'){
			$extend = array('openid' => $this->_get('openid'), 'openkey' => $this->_get('openkey'));
		}
		//请妥善保管这里获取到的Token信息，方便以后API调用
		//调用方法，实例化SDK对象的时候直接作为构造函数的第二个参数传入
		//如： $qq = ThinkOauth::getInstance('qq', $token);
		$token = $sns->getAccessToken($code , $extend);
		$_SESSION['token']=$token;
		$open_info = A('Type', 'Event')->$type($token);
		$sns_status=M('user_bind')->where(array('openid'=>$token['openid']))->find();
		if (empty($sns_status)){
			$open_data['openid']=$token['openid'];
			$open_data['md5id']=md5($open_data['openid']);
			$open_data['type']=$open_info['type'];
			$open_data['data']=serialize($open_info);
			$open_data['avatar']=$open_info['head'];
			$open_data['nick']=$open_info['nick'];
			$sns_id=M('user_bind')->add($open_data);
			$sns_md5id=M('user_bind')->where(array('id'=>$sns_id))->getField('md5id');
			$this->open_login($sns_md5id);
		}else{
			if ($sns_status['uid']=='0'){
				$this->open_login($sns_status['md5id']);
				//redirect(U('Public/bind',array('data'=>$sns_status['md5id'])));
			}else{
				$this->login_ok($sns_status['uid']);
			}
		}
		//dump($token);
		//dump($user_info);
	}
    
	public function open_login($md5id){
		$open_info=M('user_bind')->where(array('md5id'=>$md5id))->find();		
		$data['nick']=$open_info['nick'];
		$uid=M('user')->add($data);
		$down_avatar=$this->downloadImage($open_info['avatar'], __ROOT_PATH__.'/uploads/avatar/'.md5($uid));
        $user_save['avatar']=$down_avatar['filename'];
        M('user')->where(array('id'=>$uid))->save($user_save);
        $open_save['uid']=$uid;
		M('user_bind')->where(array('id'=>$open_info['id']))->save($open_save);
		$this->login_ok($uid);
	}
	
	public function bind(){
		$data=I('get.data');
		$type=I('get.type');
		$info=M('user_bind')->field('avatar,nick,md5id')->where(array('md5id'=>$data))->find();
		if (empty($info)){
			$this->error('操作错误');
		}
		$this->assign('info',$info);
		$this->display();
	}

	public function bind_ok(){
		$m=D('user');
		$data=$m->create();
		if ($data){
            $md5id=I('post.bind_id');
            $username=I('post.username');
            $data['password']=md5($data['password']);
            $username_info=$m->where(array('username'=>$username))->find();
            if (isset($username_info)){
            	$open_data['uid']=$username_info['id'];
            	M('user_bind')->where(array('md5id'=>$md5id))->save($open_data);
            	$open_info=M('user_bind')->where(array('md5id'=>$md5id))->find();
            	$user_save['nick']=$open_info['nick'];
            	$down_avatar=$this->downloadImage($open_info['avatar'], __ROOT_PATH__.'/uploads/avatar/'.md5($uid));
            	$user_save['avatar']=$down_avatar['filename'];
            	$m->where(array('id'=>$username_info['id']))->save($user_save);
            	$_SESSION['USERID']=md5($username_info['id']);
		        $_SESSION['user']=$username_info;
            	$this->success('绑定成功了',U('/'));
            }else{
            	$uid=$m->add($data);
            	$open_data['uid']=$uid;
            	M('user_bind')->where(array('md5id'=>$md5id))->save($open_data);
            	$open_info=M('user_bind')->where(array('md5id'=>$md5id))->find();
            	$user_save['nick']=$open_info['nick'];
            	$down_avatar=$this->downloadImage($open_info['avatar'], __ROOT_PATH__.'/uploads/avatar/'.md5($uid));
            	$user_save['avatar']=$down_avatar['filename'];
            	$m->where(array('id'=>$uid))->save($user_save);
            	$_SESSION['USERID']=md5($uid);
		        $_SESSION['user']=$m->find($uid);
            	$this->success('绑定成功了吧..........',U('/'));
            }
		}

	}
/**
	 * 下载远程图片
	 * @param string $url 图片的绝对url
	 * @param string $filepath 文件的完整路径（包括目录，不包括后缀名,例如/www/images/test） ，此函数会自动根据图片url和http头信息确定图片的后缀名
	 * @return mixed 下载成功返回一个描述图片信息的数组，下载失败则返回false
	 */
	private function downloadImage($url, $filepath) {
		//服务器返回的头信息
		$responseHeaders = array();
		//原始图片名
		$originalfilename = '';
		//图片的后缀名
		$ext = '';
		$ch = curl_init($url);
		//设置curl_exec返回的值包含Http头
		curl_setopt($ch, CURLOPT_HEADER, 1);
		//设置curl_exec返回的值包含Http内容
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		//设置抓取跳转（http 301，302）后的页面
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		//设置最多的HTTP重定向的数量
		curl_setopt($ch, CURLOPT_MAXREDIRS, 2);

		//服务器返回的数据（包括http头信息和内容）
		$html = curl_exec($ch);
		//获取此次抓取的相关信息
		$httpinfo = curl_getinfo($ch);
		curl_close($ch);
		if ($html !== false) {
			//分离response的header和body，由于服务器可能使用了302跳转，所以此处需要将字符串分离为 2+跳转次数 个子串
			$httpArr = explode("\r\n\r\n", $html, 2 + $httpinfo['redirect_count']);
			//倒数第二段是服务器最后一次response的http头
			$header = $httpArr[count($httpArr) - 2];
			//倒数第一段是服务器最后一次response的内容
			$body = $httpArr[count($httpArr) - 1];
			$header.="\r\n";

			//获取最后一次response的header信息
			preg_match_all('/([a-z0-9-_]+):\s*([^\r\n]+)\r\n/i', $header, $matches);
			if (!empty($matches) && count($matches) == 3 && !empty($matches[1]) && !empty($matches[1])) {
				for ($i = 0; $i < count($matches[1]); $i++) {
					if (array_key_exists($i, $matches[2])) {
						$responseHeaders[$matches[1][$i]] = $matches[2][$i];
					}
				}
			}
			//获取图片后缀名
			if (0 < preg_match('{(?:[^\/\\\\]+)\.(jpg|jpeg|gif|png|bmp)$}i', $url, $matches)) {
				$originalfilename = $matches[0];
				$ext = $matches[1];
			} else {
				if (array_key_exists('Content-Type', $responseHeaders)) {
					if (0 < preg_match('{image/(\w+)}i', $responseHeaders['Content-Type'], $extmatches)) {
						$ext = $extmatches[1];
					}
				}
			}
			//保存文件
			if (!empty($ext)) {
				$filepath .= ".$ext";
				//如果目录不存在，则先要创建目录
				//CFiles::createDirectory(dirname($filepath));
				$local_file = fopen($filepath, 'w');
				if (false !== $local_file) {
					if (false !== fwrite($local_file, $body)) {
						fclose($local_file);
						$sizeinfo = getimagesize($filepath);
						$new_name = pathinfo($filepath, PATHINFO_BASENAME);
						$type = end(explode('.', $new_name));
						return array(
						//'filepath' => realpath($filepath),
						//'width' => $sizeinfo[0],
						//'height' => $sizeinfo[1],
						//'orginalfilename' => $originalfilename,
	                        	'filename' => $new_name,
						//'type' => $type,
						//'ext' => '.'.$type,
						//'size' => $sizeinfo['bits'],
						//'hash' => hash_file('md5',realpath($filepath)),
						//'mime' => $sizeinfo['mime']
						);
					}
				}
			}
		}
		return false;
	}
}
?>