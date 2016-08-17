<?php
function get_user($uid,$type){
	$info=M('user')->where('id='.$uid)->getField('id,'.$type);
	//$info_tow=M('user_count')->where('uid='.$uid)->getField('uid,'.$type);
	return $info[$uid];
}
function get_user_count($uid,$type,$s=false){
	$info=M('user_count')->where(array('uid'=>$uid))->getField('uid,'.$type);
	if ($s){
		if ($type='integral'){
			$icon=$info[$uid]/150;
			$info[$uid]=floor($icon);
		}
	}
	return $info[$uid];
}
function get_img($img_id,$type){
	$img=M('share_img')->find($img_id);
	$img['data']=unserialize($img['data']);
	return $img['data'][$type];
}
function get_json_img($data){
	//$data=json_decode($json,true);
	//$data=str_replace('[','', $data);
	//$data=str_replace(']','', $data);
	$data=json_decode($data,true);
	return $data;
}
function get_tag($tag_id,$type){
	$info=M('tags')->where('id='.$tag_id)->getField('id,'.$type);
	return $info[$tag_id];
}

function get_day($day='0'){
	$time_=date('Ymd',time());//获取当前日期，格式为20130726
	$time=strtotime($time_);
	$time=$time-($day*86400);
	return $time;
}
function get_sign($uid){
	$status=M('user_sign')->where(array('uid'=>$uid,'day'=>get_day(0)))->find();
	if (isset($status)){
		return ' <a href="javascript:;" class="inline-block btn-checkin active disabled">已签到<span class="pos-ab"></span></a>';
	}else{
		return ' <a href="javascript:;" onClick="Le_Sign();" id="sign" class="inline-block btn-checkin active">签到<span class="pos-ab"></span></a>';
	}
}
function get_comment_replay($fid){
	if ($fid=='0'){
		return false;
	}
	$where['id']=array('eq',$fid);
	$list=M('share_comment')->where($where)->find();
	if (empty($list)){
		$html='<div class="bar quote">
	              <p>评论已被作者删除</p>               
       </div>';
	}else{
		$html='<div class="media"><a class="pull-left span1 hidden-phone" href="'.U('/user/'.$list['uid']).'"><img class="media-object img-rounded" src="__AVATAR__/'.get_user($list['uid'],'avatar').'"></a><div class="media-body"><p class="media-heading"><a href="'.U('/user/'.$list['uid']).'">'.get_user($list['uid'],'nick').'</a></p><div class="media">'.$list['comment'].'</div><hr class="clearfix">'.get_comment_replay_f($list['fid']).'</div></div>';
	}
	return $html;
}
function get_comment_replay_f($fid){
	if ($fid=='0'){
		return false;
	}
	$where['id']=array('eq',$fid);
	$list=M('share_comment')->where($where)->find();
	if (empty($list)){
		$html='<div class="media">
	              <p>评论已被作者删除</p>               
       </div>';
	}else{
		$html='<div class="media"><a class="pull-left span1 hidden-phone" href="'.U('/user/'.$list['uid']).'"><img class="media-object img-rounded" src="__AVATAR__/'.get_user($list['uid'],'avatar').'"></a><div class="media-body"><p class="media-heading"><a href="'.U('/user/'.$list['uid']).'">'.get_user($list['uid'],'nick').'</a></p><div class="media">'.$list['comment'].'</div><hr class="clearfix">'.get_comment_replay($list['fid']).'</div></div>';
	}
	return $html;
}

function get_share($id,$type){
	$info=M('share')->where('id='.$id)->getField('id,'.$type);
	return $info[$id];
}

function get_comment($id,$type){
	$info=M('share_comment')->where('id='.$id)->getField('id,'.$type);
	return $info[$id];
}
/**
 * 字符串截取，支持中文和其他编码
 * @static
 * @access public
 * @param string $str 需要转换的字符串
 * @param string $start 开始位置
 * @param string $length 截取长度
 * @param string $charset 编码格式
 * @param string $suffix 截断显示字符
 * @return string
 */
function str($str, $start=0, $length, $charset="utf-8", $suffix=true) {
	if(function_exists("mb_substr"))
	$slice = mb_substr($str, $start, $length, $charset);
	elseif(function_exists('iconv_substr')) {
		$slice = iconv_substr($str,$start,$length,$charset);
	}else{
		$re['utf-8']   = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
		$re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
		$re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
		$re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
		preg_match_all($re[$charset], $str, $match);
		$slice = join("",array_slice($match[0], $start, $length));
	}
	return $suffix ? $slice.'...' : $slice;
}
function word($str)
{
	//导入类库
	import("ORG.SplitWord.SplitWord");
	$sp = new SplitWord();
	$str=$sp->SplitRMM($str);
	$str=explode(' ',$str);
	foreach ($str as $v){
		if (strlen($v)>'4'){
			$str_key.=$v.',';
		}
	}
	return $str_key;
}


function time_tran($time){
   $date = date("Y-m-d",$time);

   $dur = time() - $time;
   if($dur < 0){
    return $date;
    }elseif($dur < 60){
     return $dur.'秒前';
    }elseif($dur < 3600){
      return floor($dur/60).'分钟前';
    }elseif($dur < 86400){
       return floor($dur/3600).'小时前';
    }elseif($dur < 259200){//3天内
        return floor($dur/86400).'天前';
   }else{
        return $the_time;
   }
    
}

?>