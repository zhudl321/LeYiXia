<?php
/**
 *
 * Enter description here ...
 * @author Administrator
 *
 */
class PullAction extends BaseAction {
	public function index(){
			/**
			 *  QueryList使用示例
			 *  
			 * 入门教程:http://doc.querylist.cc/site/index/doc/4
			 * 
			 * QueryList::Query(采集的目标页面,采集规则[,区域选择器][，输出编码][，输入编码][，是否移除头部])
			* //采集规则
			* $rules = array(
			*   '规则名' => array('jQuery选择器','要采集的属性'[,"标签过滤列表"][,"回调函数"]),
			*   '规则名2' => array('jQuery选择器','要采集的属性'[,"标签过滤列表"][,"回调函数"]),
			*    ..........
			*    [,"callback"=>"全局回调函数"]
			* );
			 */
			
			require THINK_PATH.'/Extend/Library/ORG/QueryList/vendor/autoload.php';
			$foo=  new  QL\QueryList();

			//采集某页面所有的图片
			$res = $foo->Query('http://lengxiaohua.com/new',array(
			    'content' => array('.para_can','text')
			    ))->data;
			
			$this ->url_post($res);
			
			

	}


	public function url_post($res =array()){
		$model=M('share');
		$data =array();
		$data['add_time']=time();
		$data['status']="1";
		$data['img_id']=0;
		$count =0;
		foreach ($res as $k => $v) {
			$qian=array(" ","　","\t","\n","\r");
			$hou=array("","","","","");
			$content =str_replace($qian,$hou,$v['content']);
			$getid = $model ->where(array('content'=>$v['content']))->getField('id');
			if($content !='' && empty($getid)){
				$data['uid']=rand(1,50);
				$data['content']=$v['content'];
				$share_id=$model->add($data);
				$count++;
			}
			continue;
		}
		echo "Pull Article ".$count." the data OK";
	}
	

}