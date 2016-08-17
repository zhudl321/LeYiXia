<?php
class SucaiAction extends BaseAction{
	public function index(){
		$m=M('share');
    	$where['type']=array('eq','sucai');
    	$limit=10;
    	$order='id DESC';
    	$field=array('id','uid','title','img_id','add_time','content','comment_count','le','nu','fav');
    	$url='sucai/page';
    	$data=$this->_page($m, $where, $limit, $order, $field, $url);
    	$this->assign('data',$data);
		$this->assign('title','图片素材-');
		$this->display();
	}
}