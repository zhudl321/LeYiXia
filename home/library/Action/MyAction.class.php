<?php
/**
 * 用户类
 * Enter description here ...
 * @author Administrator
 *
 */
class MyAction extends BaseAction{
	public function index(){
		$this->ck_login(false);
		$m=M('share');
    	$where['id']=array('gt',0);
    	$where['type']=array('neq','sucai');
    	$where['uid']=array('eq',$_SESSION['user']['id']);
    	$limit=10;
    	$order='id DESC';
    	$field=array('id','uid','title','img_id','add_time','content','comment_count','le','nu','fav');
    	$url='my/page';
    	$data=$this->_page($m, $where, $limit, $order, $field, $url);
    	foreach ($data['list'] as $k=>$v){
    		$data['list'][$k]['tag']=M('share_tags')->where(array('share_id'=>$v['id']))->select();
    	}
    	$this->assign('data',$data);
    	$this->display();
	}
	
	
	public function info(){
		$this->ck_login();
		$name=I('get.name');
		$uid=$_SESSION['user']['id'];
		$this->$name($uid);
		$this->display($name);
	}
	
	
	public function comment($uid){
		$this->ck_login();
		$m=M('share_comment');
		$where['uid']=array('eq',$uid);
		$limit=10;
    	$order='id DESC';
    	$field=array('id','share_id','fid','comment','uid','add_time');
    	$url='my/comment/page';
    	$data=$this->_page($m, $where, $limit, $order, $field, $url);
    	$this->assign('data',$data);
	}
	
	public function fav($uid){
		$this->ck_login();
		$share_arr=M('user_fav')->where(array('uid'=>$uid))->field('share_id')->select();
		foreach ($share_arr as $v){
			$share_id[]=$v['share_id'];
		}
		$m=M('share');
		$where['type']=array('neq','sucai');
    	$where['id']=array('in',$share_id);
    	$where['uid']=array('eq',$_SESSION['user']['id']);
    	$limit=10;
    	$order='id DESC';
    	$field=array('id','uid','title','img_id','add_time','content','comment_count','le','nu','fav');
    	$url='my/fav/page';
    	$data=$this->_page($m, $where, $limit, $order, $field, $url);
    	foreach ($data['list'] as $k=>$v){
    		$data['list'][$k]['tag']=M('share_tags')->where(array('share_id'=>$v['id']))->select();
    	}
    	$this->assign('data',$data);
	}
	
	public function userinfo(){
		$uid=I('get.uid');
		$m=M('share');
    	$where['id']=array('gt',0);
    	$where['uid']=array('eq',$uid);
    	$limit=10;
    	$order='id DESC';
    	$field=array('id','uid','title','img_id','add_time','content','comment_count','le','nu','fav');
    	$url='user/page/'.$uid;
    	$data=$this->_page($m, $where, $limit, $order, $field, $url);
    	foreach ($data['list'] as $k=>$v){
    		$data['list'][$k]['tag']=M('share_tags')->where(array('share_id'=>$v['id']))->select();
    	}
    	$this->assign('uid',$uid);
    	$this->assign('data',$data);
    	$this->display();
	}
}
?>