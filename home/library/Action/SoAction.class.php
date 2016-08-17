<?php
class SoAction extends BaseAction{
	public function key(){
		$n=I('get.s',strip_tags);
		$m=M('share');
    	$where['title']=array('like','%'.$n.'%');
    	$limit=10;
    	$order='id DESC';
    	$field=array('id','uid','title','img_id','add_time','content','comment_count','le','nu','fav');
    	$url='so/page/'.$n;
    	$data=$this->_page($m, $where, $limit, $order, $field, $url);
    	foreach ($data['list'] as $k=>$v){
    		$data['list'][$k]['tag']=M('share_tags')->where(array('share_id'=>$v['id']))->select();
    	}
    	$this->assign('data',$data);
    	$this->display();
	}
}