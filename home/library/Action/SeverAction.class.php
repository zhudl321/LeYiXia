<?php
/**
 * 全局核心服务类
 * Enter description here ...
 * @author Administrator
 *
 */
class SeverAction extends BaseAction{
	public function box(){
		$name=I('get.name');
		$data=I('get.data');
		$this->assign('data',$data);
		$this->hot_tag();
		$this->display($name);
	}
	public function img_url(){
		//$this->ck_login();
		$url=I('post.url');
		$data=get_headers($url,true);		
		$type=explode('/',$data['Content-Type']);
		if ($type['0']=="image"){
			$this->ajaxReturn(1,$url,1);
		}else{
			$this->ajaxReturn(0,'图片地址有误',0);
		}
	}
	public function up_img(){
		//$this->ck_login();
		import('ORG.Net.UploadFile');
		import('ORG.Util.Image.ThinkImage'); 
		$img = new ThinkImage();
		$upload = new UploadFile();// 实例化上传类
		$upload->maxSize  = 2048000;// 设置附件上传大小
		$upload->allowExts  = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
		$upload->savePath =  './uploads/img/';// 设置附件上传目录
		if(!$upload->upload()) {// 上传错误提示错误信息
			echo $upload->getErrorMsg();
		}else{// 上传成功 获取上传文件信息
			$info =  $upload->getUploadFileInfo();
			$img->open('./uploads/img/'.$info['0']['savename'])->water('./sy.png',rand(7,9))->save('./uploads/img/'.$info['0']['savename']);
		
		$img_data=array();
		$img_data['filename']=$info['0']['savename'];
		$img_data['ext']=$info['0']['extension'];
		$data['data']=serialize($img_data);
		$data['uid']=$_SESSION['user']['id'];
		$data['status']='-1';
		$img_id=M('share_img')->add($data);
		$callback= array('img_id'=>$img_id,'img_name'=>$info['0']['savename']);
		$this->ajaxReturn($callback,'',1);
		}
		//$this->ajaxReturn(1,'保存成功',1);
		// 保存表单数据 包括附件数据
		/*$User = M("User"); // 实例化User对象
		 $User->create(); // 创建数据对象
		 $User->photo = $info[0]['savename']; // 保存上传的照片根据需要自行组装
		 $User->add(); // 写入用户数据到数据库
		 $this->success('数据保存成功！');*/

	}
	public function up_sucai(){
		//$this->ck_login();
		import('ORG.Net.UploadFile');
		$upload = new UploadFile();// 实例化上传类
		$upload->maxSize  = 3145728 ;// 设置附件上传大小
		$upload->allowExts  = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
		$upload->savePath =  './uploads/sucai/';// 设置附件上传目录
		if(!$upload->upload()) {// 上传错误提示错误信息
			$this->error($upload->getErrorMsg());
		}else{// 上传成功 获取上传文件信息
			$info =  $upload->getUploadFileInfo();
		
		$img_data=array();
		$img_data['filename']=$info['0']['savename'];
		$data['data']=serialize($img_data);
		$data['uid']=$_SESSION['user']['id'];
		$data['status']='-1';
		$data['type']='sucai';
		$img_id=M('share_img')->add($data);
		echo $img_id;
		}
		//$this->ajaxReturn(1,'保存成功',1);
		// 保存表单数据 包括附件数据
		/*$User = M("User"); // 实例化User对象
		 $User->create(); // 创建数据对象
		 $User->photo = $info[0]['savename']; // 保存上传的照片根据需要自行组装
		 $User->add(); // 写入用户数据到数据库
		 $this->success('数据保存成功！');*/

	}
	public function url_post(){
		$this->ck_login();
		$title=I('post.title');
		if ($title==''){
			$this->ajaxReturn(0,'请输入标题',0);
		}
		$img_type=I('post.img_type');
		$source=I('post.img','0');
		$tags=I('post.tag');
		$content=I('post.content',strip_tags);
		if ($source=='0'){
			if ($content==''){
				$this->ajaxReturn(0,'说点什么吧！！',0);
				exit();
			}
			$img_id=$source;
		}else{
			if ($img_type=='1'){
				$img_id=$source;
			}
			else{
				$source_id=M('share_img')->where(array('source'=>$source))->find();//先判断此源图片地址是否已经存在，如果存在着不保存
				if (!empty($source_id)){
					$img_id=$source_id['id'];
				}else{
					$img=$this->downloadImage($source,__ROOT_PATH__.'/uploads/img/'.md5(time()));
					$share_img_data['data']=serialize($img);
					$share_img_data['source']=$source;
					$share_img_data['uid']=$_SESSION['user']['id'];
					$img_id=M('share_img')->add($share_img_data);
				}
			}
		}
		$d=M('share');
		$data=$d->create();
		if ($data){
			$data['uid']=$_SESSION['user']['id'];
			$data['add_time']=time();
			$data['status']="1";
			$data['img_id']=$img_id;
			//$data['content']=strip_tags($data['content']);
			$share_id=$d->add($data);
			if ($share_id){
				//分享成功
				M('share_img')->where(array('id'=>$img_id))->setField('status','1');
				$this->add_tag($share_id,$tags);
				$this->user_count($_SESSION['user']['id'],'integral','5','1');//用户积分+5
				$this->user_count($_SESSION['user']['id'],'share','1','1');//用户分享+1
				$this->ajaxReturn(1,'提交成功',1);
			}
		}else{
			$this->ajaxReturn(0,$d->getError(),0);
		}
	}
	/**
	 * 保存标签数据
	 * Enter description here ...
	 * @param unknown_type $share_id
	 * @param unknown_type $tags
	 */
	protected function add_tag($share_id,$tags){
		$tag_arr=explode(" ", $tags);
		$tag_arr=array_filter($tag_arr);
		foreach ($tag_arr as $v){
			$tag_info=M('tags')->where(array('name'=>$v))->find();//先查询此tag是否存在
			if (!empty($tag_info)){
				$share_data['share_id']=$share_id;
				$share_data['tag_id']=$tag_info['id'];
				$share_tag_status=M('share_tags')->where(array('share_id'=>$share_id,'tag_id'=>$tag_info['id']))->find();
				if (empty($share_tag_status)){//每个分享只能保存一个相同的tag
					M('share_tags')->add($share_data);
				}
				$user_data['uid']=$_SESSION['user']['id'];
				$user_data['tag_id']=$tag_info['id'];
				$user_tag_info=M('user_tags')->where(array('uid'=>$_SESSION['user']['id'],'tag_id'=>$tag_info['id']))->find();
				if (empty($user_tag_info)){
					M('user_tags')->add($user_data);
				}
				M('tags')->where(array('id'=>$tag_info['id']))->setInc('user_count');
				M('tags')->where(array('id'=>$tag_info['id']))->setInc('share_count');
			}else{
				$tag_data['name']=$v;
				$tag_data['v_name']=pinyin($v);
				$tag_id=M('tags')->add($tag_data);
				$share_data['share_id']=$share_id;
				$share_data['tag_id']=$tag_id;
				$share_tag_status=M('share_tags')->where(array('share_id'=>$share_id,'tag_id'=>$tag_id))->find();
				if (empty($share_tag_status)){
					M('share_tags')->add($share_data);
				}
				$user_data['uid']=$_SESSION['user']['id'];
				$user_data['tag_id']=$tag_id;
				$user_tag_info=M('user_tags')->where(array('uid'=>$_SESSION['user']['id'],'tag_id'=>$tag_id))->find();
				if (empty($user_tag_info)){
					M('user_tags')->add($user_data);
				}
				M('tags')->where(array('id'=>$tag_id))->setInc('user_count');
				M('tags')->where(array('id'=>$tag_id))->setInc('share_count');
			}
		}
	}
	/**
	 * 修改头像
	 * Enter description here ...
	 */
	public function save_avatar(){
		$this->ck_login();
		$uid=$_SESSION['user']['id'];
		$input = file_get_contents('php://input');
		$data = explode('--------------------', $input);
		$avatar_path=__ROOT_PATH__.'/uploads/avatar/';
		$avatar_path_avatar=$_SESSION['USERID'].'.jpg';
		$avatar_name=$_SESSION['USERID'].'.jpg';
		@file_put_contents($avatar_path.$avatar_name, $data[0]);
		$m=M('user');
		$datas['id']=$uid;
		$datas['avatar']=$avatar_name;
		$m->save($datas);
		$this->ajaxReturn(1,'','修改成功');
	}
	/**
	 * 用户签到操作
	 * Enter description here ...
	 */
	public function user_sign(){
		$this->ck_login();
		$uid=$_SESSION['user']['id'];
		$day=get_day();
		$status=M('user_sign')->where(array('uid'=>$uid,'day'=>$day))->find();
		if (isset($status)){
			$this->ajaxReturn(0,'已签到',0);
		}else{
			$data['uid']=$uid;
			$data['day']=$day;
			M('user_sign')->add($data);
			$count=M('user_sign')->where(array('day'=>$day))->count();
			$this->user_count($_SESSION['user']['id'],'integral','10','1');//用户积分+1
			$this->user_count($_SESSION['user']['id'],'sign','1','1');//用户签到+1
			$this->ajaxReturn(1,'签到成功!您是今天第'.$count.'位签到者！',1);
		}
	}
	/**
	 * 获取评论列表
	 * Enter description here ...
	 */
	public function get_comment(){
		$id=I('get.id');
		$m=M('share_comment');
		$list=$m->where(array('share_id'=>$id))->limit(10)->order('add_time DESC')->select();
		$count=$m->where(array('share_id'=>$id))->count();
		$this->assign('count',$count-10);
		$this->assign('list',$list);
		$this->assign('id',$id);
		$this->display();
	}
	public function get_page_comment($show=true){
		$id=I('get.share_id');
		$get_order=I('get.order');
		$m=M('share_comment');
		$where['share_id']=$id;
		$order=$get_order.' DESC';
		$field=array('id','share_id','uid','fid','comment','add_time','back');
		$url='';
		$data=$this->_page($m, $where, $limit, $order, $field, $url);
		$this->assign('list',$data);
		if ($show){
			$this->display();
		}
		dump($data);
	}
	public function add_comment(){
		$this->ck_login();
		$d=D('share_comment');
		$validate = array(
		array('comment','require','请输入评论内容！'),
		array('fid','require','提交参数{fid}有误！'),
		array('share_id','require','提交参数(share_id)有误！'),
		);
		$d->setProperty("_validate",$validate);
		$data=$d->create();
		if ($data) {
			$data['uid']=$_SESSION['user']['id'];
			$data['add_time']=time();
			$data['add_ip']=get_client_ip();
			$data['status']=1;
			$comment_id=$d->add($data);
			if (isset($comment_id)){
				M('share')->where(array('id'=>$data['share_id']))->setInc('comment_count');
				//$comment_info=$d->find($comment_id);
				$return=$this->return_ok_comment($comment_id);
				/*$return['uid']=$comment_info['uid'];
				 $return['avatar']=get_user($comment_info['uid'],'avatar');
				 $return['id']=$comment_info['id'];
				 $return['comment']=$comment_info['comment'];
				 $return['add_time']=$comment_info['add_time'];*/
				$this->user_count($_SESSION['user']['id'],'comment','1','1');//用户评论+1
				$this->user_count($_SESSION['user']['id'],'integral','1','1');//用户积分+1
				$this->ajaxReturn($return,'评论成功',1);
			}else{
				$this->ajaxReturn(0,'评论失败!',0);
			}
		}else{
			$this->ajaxReturn(0,$d->getError(),0);
		}
	}
	public function del_comment(){
		$id=I('post.id');
		$uid=$_SESSION['user']['id'];
		$d_uid=M('share_comment')->where(array('id'=>$id))->find();
		if ($d_uid['uid']==$uid){
			M('share_comment')->where(array('id'=>$id))->delete();
			M('share')->where(array('id'=>$d_uid['share_id']))->setDec('comment_count');
			$this->user_count($uid,'comment','1','2');//用户评论数-1
			$this->user_count($uid,'integral','5','2');//用户评积分-5
			$this->ajaxReturn(1,'删除成功!',1);
		}else{
			$this->ajaxReturn(0,'删除失败!'.$d_uid['uid'].'-------'.$uid,0);
		}
	}

	public function comment_back(){
		$id=I('post.id');
		$ip=get_client_ip();
		$ck=$_COOKIE['back_'.$ip.$id];
		if (empty($ck)){
			$status=M('share_comment')->where(array('id'=>$id))->setInc('back');
			if ($status) {
				$back=M('share_comment')->where(array('id'=>$id))->getField('back');
				$_COOKIE['back_'.$ip.$id]=$id.$ip;
				$this->ajaxReturn($back,'这评论亮了',1);
			}else{
				$this->ajaxReturn(0,'操作失败，也许是你的RP太差了',0);
			}
		}else{
			$this->ajaxReturn(0,'你已经点亮过了！',0);
		}
	}
	/**
	 * 对分享的观点操作
	 * Enter description here ...
	 */
	public function share_click(){
		$share_id=I('post.share_id');
		$type=I('post.type');
		$uid=$_SESSION['user']['id'];
		$ip=get_client_ip();
		$su=array('le'=>'乐一乐，快乐一整天','nu'=>'怒，就是要这么霸气');
		$er=array('le'=>'你乐过了，乐一下其他的吧。','nu'=>'你怒过了，就放过我吧');
		//先判断是否存在记录
		$where['share_id']=$share_id;
		if (isset($uid)){
			$where['uid']=$uid;
		}else{
			$where['ip']=$ip;
		}
		$status=M('user_click')->where($where)->find();
		if (isset($status)){
			$this->ajaxReturn(0,$er[$status['type']],0);
		}else{
			$data['share_id']=$share_id;
			$data['type']=$type;
			$data['uid']=$uid;
			$data['ip']=$ip;
			$data['time']=time();
			$click=M('user_click')->add($data);
			if ($click){
				M('share')->where(array('id'=>$share_id))->setInc($type);
				$this->user_count($_SESSION['user']['id'],$type,'1','1');//用户评论+1
				$this->user_count($_SESSION['user']['id'],'integral','1','1');//用户积分+1
				$share_count=M('share')->field('le,nu')->where(array('id'=>$share_id))->find();
				$this->ajaxReturn($share_count,$su[$type],1);
			}else{
				$this->ajaxReturn(0,'操作失败！',0);
			}
		}
	}
	/**
	 * 收藏操作
	 * Enter description here ...
	 */
	public function fav(){
		$this->ck_login();
		$data['share_id']=I('post.share_id');
		$data['uid']=$_SESSION['user']['id'];
		$status=M('user_fav')->where($data)->find();
		if (isset($status)){
			$this->ajaxReturn(0,'已经收藏过了',0);
		}else{
			M('user_fav')->add($data);
			M('share')->where(array('id'=>$data['share_id']))->setInc('fav');
			$this->user_count($_SESSION['user']['id'],'fav','1','1');//用户积分+1
			$this->user_count($_SESSION['user']['id'],'integral','1','1');//用户积分+1
			$this->ajaxReturn(1,'收藏成功',1);
		}
	}
	protected function return_ok_comment($comment_id){
		$comment=M('share_comment')->find($comment_id);
	/*$html='<li class="by_user"> <a href="'.U('/user/'.$comment['uid'].'').'" title="'.get_user($comment['uid'],'nick').'"><img src="'.__ROOT__.'/uploads/avatar/'.get_user($comment['uid'],'avatar').'" alt="" width="40"></a>
      <div class="messageArea"> <span class="aro"></span>
        <div class="infoRow"> <span class="name"><strong>'.get_user($comment['uid'],'nick').'</strong>:</span> <span class="time">'.date('Y-m-d H:i',$comment['add_time']).'</span>
          <div class="clear"></div>
        </div>
        '.$comment['comment'].'
        </div>
      <div class="clear"></div>
    </li>';*/
	$html='<li style="padding:0px;">
    <a href="'.U('/user/'.$comment['uid'].'').'" title="">
        <img src="'.__ROOT__.'/uploads/avatar/'.get_user($comment['uid'],'avatar').'" style="width:40px;" alt="'.get_user($comment['uid'],'nick').'">
        <span class="contactName">
            <strong>'.get_user($comment['uid'],'nick').'<span style="display:none;">举报</span></strong>
            <i>'.$comment['comment'].'</i>
        </span>
        <span class="clear"></span>
    </a>
</li>';
		return $html;
	}
	/**
	 * 去除数组中的空值
	 * Enter description here ...
	 * @param $var
	 */
	function filter($var)
	{
		if($var == '')
		{
			return false;
		}

		return true;
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
						'ext' => $type,
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