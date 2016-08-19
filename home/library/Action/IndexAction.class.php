<?php
/**
 *
 * Enter description here ...
 * @author Administrator
 *
 */
class IndexAction extends BaseAction {
	public function index(){

		$showbox = 1;
		$m=M('share');
		$type=I('get.type','new');
		$where =array();
		$where['status'] = 1;
		switch ($type){
			case 'new':
				$showbox = 0;
				$where['add_time']=array('between',array(get_day(30),get_day(-1)));
				$order='add_time DESC';
				$url='new/page';
				$title='乐一下 -';break;
			case 'day':
				$where['add_time']=array('between',array(get_day(0),get_day(-1)));
				$order='le DESC';
				$url='day/page';
				$title='本天最乐 -';break;
			case 'week':
				$where['add_time']=array('between',array(get_day(7),get_day(-1)));
				$order='le DESC';	
				$url='week/page';
				$title='本周最乐 -';break;
			case 'month':
				$where['add_time']=array('between',array(get_day(30),get_day(-1)));
				$order='le DESC';
				$url='month/page';
				$title='本月最乐 -';break;
			case 'shenhuifu':
				$showbox = 0;
				$where['type']= 2;
				$where['add_time']=array('between',array(get_day(30),get_day(-1)));
				$order='add_time DESC';	
				$url='shenhuifu/page';
				$title='神回复 -';break;

			case 'duanzi':
				$showbox = 0;
				$where['type']= 3;
				$where['add_time']=array('between',array(get_day(30),get_day(-1)));
				$order='add_time DESC';	
				$url='duanzi/page';
				$title='段子手 -';break;

			case 'random':
				$showbox = 0;
				$title='随机显示 -';break;
			default:
				$showbox = 0;
				$where['add_time']=array('between',array(get_day(30),get_day(-1)));
				$order='add_time DESC';
				$url='new/page';
				$title='乐一下 -';break;			
		}

		$limit=12;
		$field=array('id','uid','title','img_id','add_time','content','comment_count','le','nu','fav');
		if($type =="random"){
			$data['list'] = $m->where($where)->limit($limit)->order('rand()')->select($field);
		}else{
			$data=$this->_page($m, $where, $limit, $order, $field, $url);
			if(empty($data['list'])){
				$where['add_time']=array('gt',0);
				$data=$this->_page($m, $where, $limit, $order, $field, $url);
			}			
		}

		foreach ($data['list'] as $k=>$v){
			$data['list'][$k]['first_char'] = str($v['content'],0,1,"utf-8",false);
			$data['list'][$k]['content'] = str($v['content'],1,null,"utf-8",false);
			$avatar = get_user($v['uid'],'avatar');
			$data['list'][$k]['avatar'] = $avatar?$avatar:"default.png";
			$nick = get_user($v['uid'],'nick');
			$data['list'][$k]['nick'] = $nick?$nick:"乐友";
			$data['list'][$k]['date'] = time_tran($v['add_time']);
		}
		
		$this->assign('data',$data);
		$this->assign('title',$title);
		$this->assign('type',$type);
		$this->assign('showbox',$showbox);
		$this->display();
	}
	public function le(){
		$id=I('get.id');
		$info=M('share')->where(array('status'=>1))->find($id);
		if (empty($info)){
			$this->error('OMG..这被吃掉了啊');
		}else{
			$info['first_char'] = str($info['content'],0,1,"utf-8",false);
			$info['contents'] = str($info['content'],1,null,"utf-8",false);
			$avatar = get_user($info['uid'],'avatar');
			$info['avatar'] = $avatar?$avatar:"default.png";
			$nick = get_user($info['uid'],'nick');
			$info['nick'] = $nick?$nick:"网友";
			$info['date'] = time_tran($info['add_time']);
		}

		if($info['title'] ==''){
			$title= str($info['content'],0,20,"utf-8",false);
		}else{
			$title =$info['title'];
		}

		$this->assign('title',$title);
		$this->assign('le',$info);
		$m=M('share_comment');
		$where['share_id']=$id;
		$order='add_time DESC';
		$limit='10';
		$field=array('id','share_id','uid','fid','comment','add_time','back');
		$url='le/'.$id.'/p';
		$data=$this->_page($m, $where, $limit, $order, $field, $url);
		$this->assign('list',$data);
		
		$up_id=M('share')->where(array('status'=>1,'id'=>array('lt',$id)))->order('id DESC')->getField('id');
		$next_id=M('share')->where(array('status'=>1,'id'=>array('gt',$id)))->getField('id');
		$this->assign('more',array('up'=>$up_id,'next'=>$next_id));
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