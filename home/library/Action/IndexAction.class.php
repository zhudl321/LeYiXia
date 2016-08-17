<?php
/**
 *
 * Enter description here ...
 * @author Administrator
 *
 */
class IndexAction extends BaseAction {
	public function index(){
		$m=M('share');
		$where['type']=array('neq','sucai');
		$where['add_time']=array('between',array(get_day(0),get_day(-1)));
		$order='id DESC';
		$hot=I('get.hot');
		switch ($hot){
			case '1':
				$where['add_time']=array('between',array(get_day(0),get_day(-1)));
				$order='comment_count DESC';
				$title='24小时最热-';
				$url='hot/page';
				break;
			case 'day':
				$where['add_time']=array('between',array(get_day(0),get_day(-1)));
				$order='comment_count DESC';
				$title='24小时最热-';
				$url='hot/day/page';
				break;
			case 'week':
				$where['add_time']=array('between',array(get_day(7),get_day(-1)));
				$order='comment_count DESC';
				$title='一周内最热-';
				$url='hot/week/page';
				break;
			case 'month':
				$where['add_time']=array('between',array(get_day(30),get_day(-1)));
				$order='comment_count DESC';
				$title='一个月内最热-';
				$url='hot/month/page';
				break;
			case 'year':
				$where['add_time']=array('between',array(get_day(365),get_day(-1)));
				$order='comment_count DESC';
				$title='一年内最热-';break;
				$url='hot/year/page';
			case 'history':
				$where['add_time']=array('gt',0);
				$order='comment_count DESC';
				$title='历史最热-';
				$url='hot/history/page';
				break;
		}
		$new=I('get.new');
		switch ($new){
			case '1':
				$where['add_time']=array('gt',0);
				$order='add_time DESC';
				$url='new/page';
				$title='最新-';break;
		}
		$limit=30;
		$field=array('id','uid','title','img_id','add_time','content','comment_count','le','nu','fav');
		$data=$this->_page($m, $where, $limit, $order, $field, $url);
		if(empty($data['list'])){
			$where['add_time']=array('gt',0);
			$data=$this->_page($m, $where, $limit, $order, $field, $url);
		}

		foreach ($data['list'] as $k=>$v){
			//$data['list'][$k]['tag']=M('share_tags')->where(array('share_id'=>$v['id']))->select();
			$data['list'][$k]['first_char'] = str($v['content'],0,1,"utf-8",false);
			$data['list'][$k]['content'] = str($v['content'],1,null,"utf-8",false);
			$avatar = get_user($v['uid'],'avatar');
			$data['list'][$k]['avatar'] = $avatar?$avatar:"default.png";
			$nick = get_user($v['uid'],'nick');
			$data['list'][$k]['nick'] = $nick?$nick:"网友";
			$data['list'][$k]['date'] = time_tran($v['add_time']);
		}
		
		$this->assign('data',$data);
		$this->assign('title',$title);
		//$this->hot_tag(20);
		$this->display();
	}
	public function le(){
		$id=I('get.id');
		$info=M('share')->find($id);
		if (empty($info)){
			$this->error('OMG..这乐图被吃掉了');
		}
		$tag=M('share_tags')->where(array('share_id'=>$id))->select();
		$this->assign('tags',$tag);
		$this->assign('le',$info);
		$m=M('share_comment');
		$where['share_id']=$id;
		$order='add_time DESC';
		$limit='10';
		$field=array('id','share_id','uid','fid','comment','add_time','back');
		$url='le/'.$id.'/p';
		$data=$this->_page($m, $where, $limit, $order, $field, $url);
		$this->assign('list',$data);
		
		//
		
		$up_id=M('share')->where(array('type'=>'share','id'=>array('lt',$id)))->order('id DESC')->getField('id');
		$next_id=M('share')->where(array('type'=>'share','id'=>array('gt',$id)))->getField('id');
		$this->assign('more',array('up'=>$up_id,'next'=>$next_id));
		$this->hot_tag(40);
		$this->display();
	}
	
	public function pic(){
		$m=M('share');
		$where['type']=array('neq','sucai');
		$where['img_id']=array('gt','0');
		$order='id DESC';
		$limit=50;
		$field=array('id','uid','title','img_id','add_time','content','comment_count','le','nu','fav');
		$data=$this->_page($m, $where, $limit, $order, $field, $url);
		foreach ($data['list'] as $k=>$v){
			$data['list'][$k]['tag']=M('share_tags')->where(array('share_id'=>$v['id']))->select();
		}
		$this->assign('data',$data);
		$this->assign('title',$title);
		$this->hot_tag(20);
		$this->display();
	}
}