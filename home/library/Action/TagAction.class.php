<?php
class TagAction extends BaseAction{
    public function index(){
    	$this->hot_tag(100);
    	$this->display();
    }
	public function tag_share(){
		$name=I('get.name',strip_tags());
		if ($name=='') {
			redirect(U('/tag'));
		}else{
			$tag_id=M('tags')->where(array('name'=>$name))->getField('id');
			$share_arr=M('share_tags')->field('share_id')->where(array('tag_id'=>$tag_id))->select();
			foreach ($share_arr as $v){
				$share_id[]=$v['share_id'];
			}
			$m=M('share');
			$where['id']=array('in',$share_id);
			$limit='30';
			$order='add_time DESC';
			$field=array('id','uid','title','img_id','add_time','content','comment_count','le','nu','fav');
			$data=$this->_page($m, $where, $limit, $order, $field, $url);
			foreach ($data['list'] as $k=>$v){
				$data['list'][$k]['tag']=M('share_tags')->where(array('share_id'=>$v['id']))->select();
			}
			$this->assign('data',$data);
			$this->assign('title',$title);
			$this->hot_tag(40);
			$this->assign('title',$name.'-');
			$this->display('Index:index');
		}
	}
}