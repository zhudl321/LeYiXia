<?php
/**
 * 全局基础类
 * Enter description here ...
 * @author Administrator
 *
 */
class BaseAction extends Action{
	function _page($m,$where,$limit,$order,$field,$url)
	{
	 import('ORG.Util.Page');
	 $count      = $m->where($where)->count();
	 $Page       = new Page($count,$limit);
	 $Page->url = $url;
	 $Page->setConfig('theme','%upPage% %linkPage% %downPage%');
	 $show       = $Page->show();
	 $list = $m->where($where)->field($field)->order($order)->limit($Page->firstRow.','.$Page->listRows)->select();
	 $r=array('list'=>$list,'page'=>$show,'count'=>$count);
	 return $r;
	}
	
	public function hot_tag($limit='10'){
		$list = M('tags')->order('share_count DESC')->limit($limit)->select();
		$this->assign('hot_tag',$list);
	}
	public function ck_login($type=true){
		$user=$_SESSION['user'];
		if (isset($user)){
			return true;
		}else{
			if ($this->isAjax()){
			$this->ajaxReturn(0,'请登录',0);
			exit();
			}else{
				redirect(U('/'));
			}
		}
	}
	/**
	 * 用户各项统计操作
	 * Enter description here ...
	 * @param unknown_type $uid 用户ID
	 * @param unknown_type $name 操作字段
	 * @param unknown_type $number 操作数值 默认1
	 * @param unknown_type $type 操作类型  1为setInc 2为setDec
	 */
	public function user_count($uid,$name,$number='1',$type='1'){
		if (isset($uid)){
		$status=M('user_count')->where(array('uid'=>$uid))->find();
		if (empty($status)){
			$data['uid']=$uid;
			M('user_count')->add($data);
		}
		if ($name=='integral'){
			$integral_info=M('user_integral')->where(array('uid'=>$uid))->getField('integral');
			if ($integral_info>='150'){//用户明天积分最多增加
				$number='0';
			}
			$integral_data['uid']=$uid;
			$integral_data['integral']=$number+$integral_info;
			$integral_data['time']=get_day(0);
			if (empty($integral_info)){
			M('user_integral')->add($integral_data);
			}else{
			M('user_integral')->where(array('uid'=>$uid))->save($integral_data);
			}
		}
		if ($type=='2'){
			M('user_count')->where(array('uid'=>$uid))->setDec($name,$number);
		}else{
			M('user_count')->where(array('uid'=>$uid))->setInc($name,$number);
		}
	}
	}
	/**
	 * 发送有关于用户的消息
	 * Enter description here ...
	 * @param unknown_type $uid  当前操作用户ID
	 * @param unknown_type $get_uid  接收用户ID
	 * @param unknown_type $type  消息类型
	 */
	public function user_inform($uid,$get_uid,$type){
		
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
	public function _empty(){
		redirect(U('/'));
	}
}
?>