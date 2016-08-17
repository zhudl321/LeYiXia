<?php
class ShareModel extends Model{
	protected $_validate = array(
     array('img','require','图片必须'),
     array('title','require','请输入标题！'),
    );
}
?>