<?php
/**
 *
 * Enter description here ...
 * @author Administrator
 *
 */
class PullAction extends BaseAction {
	public function index(){
			$p=I('get.p',1,intval);
			$tag=I('get.tag',0,intval);

			$tag_arr =array(1001,2001);
			if($tag<=0 || $p<1 || !in_array($tag,$tag_arr)){
					die("是谁动了我的奶酪(⊙o⊙)…");
			}
			$type =0;
			require THINK_PATH.'/Extend/Library/ORG/QueryList/vendor/autoload.php';
			$foo=  new  QL\QueryList();

			if($tag ==1001){
				$res = $foo->Query('http://lengxiaohua.com/new?page_num='.$p,array(
				    'content' => array('.para_can','text')
				    ))->data;			
			}elseif($tag ==2001){
				$type =2;
				$res = $foo->Query('http://shenhf.net/archives/category/zhihu/page/'.$p,array(
				    'title' => array('h2','text'),
				    'content' => array('.article-entry','text'),
				    ))->data;
			}

			$this ->url_post($type,$res);	
			die();
	}

	//采集数据
	public function url_post($type=0,$res =array()){
		$model=M('share');
		$data =array();
		$data['status']=1;
		$data['img_id']=0;
		$data['type']=$type;
		$count =0;
		foreach ($res as $k => $v) {
			$qian=array(" ","　","\t","\n","\r");
			$hou=array("","","","","");
			$content =str_replace($qian,$hou,$v['content']);
			$getid = $model ->where(array('content'=>$v['content']))->getField('id');
			$long = strlen($v['title']);
			if($content !='' && empty($getid) && $long<80){
				$data['uid']=rand(1,50);
				$data['add_time'] = $v['add_time']?$v['add_time']:time();
				$data['title']=trim($v['title']);
				$data['content']=$v['content'];
				$share_id=$model->add($data);
				$count++;
			}
			continue;
		}
		$this->sitemap();//更新sitemap
		echo "Pull Article ".$count." the data OK";
	}
	
	public function sitemap(){
		import('ORG.Util.Sitemap');//加载Sitemap类
		$site = new Sitemap();
		$cate = M('share')->field(array('id'))->where(array('status'=>1))->order("add_time desc")->select();
		
		$site->AddItem(C('SITE_URL').'/',0,'daily');
		$site->AddItem(C('SITE_URL').'/new',2,'Weekly');
		$site->AddItem(C('SITE_URL').'/day',2,'Weekly');
		$site->AddItem(C('SITE_URL').'/random',2,'Weekly');
		$site->AddItem(C('SITE_URL').'/month',2,'Weekly');
		foreach ($cate as $v)
		{
			$site->AddItem(C('SITE_URL').'/le/'.$v['id'], 4,'monthly');
		}
		$site->SaveToFile('./sitemap.xml');
	}



	public function baidu(){
		$where =array();
		$where['add_time']=array('between',array(get_day(0),get_day(-1)));
		$where['status'] =1;
		$where['baidu'] =0;
		$cate = M('share')->field(array('id'))->where($where)->order("add_time desc")->select();

		$urls=array();
		foreach ($cate as $v){
			$urls[] =C('SITE_URL').'/le/'.$v['id'];
			M('share')->where(array('id'=>$v['id']))->setField("baidu",1);
		}

		$api = 'http://data.zz.baidu.com/urls?site=leyixia.cc&token=2ke21oZ9Gj6e38fd';
		$ch = curl_init();
		$options =  array(
		    CURLOPT_URL => $api,
		    CURLOPT_POST => true,
		    CURLOPT_RETURNTRANSFER => true,
		    CURLOPT_POSTFIELDS => implode("\n", $urls),
		    CURLOPT_HTTPHEADER => array('Content-Type: text/plain'),
		);
		curl_setopt_array($ch, $options);
		$result = curl_exec($ch);
		echo $result;

	}




}