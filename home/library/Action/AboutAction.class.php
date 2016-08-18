<?php

class AboutAction extends BaseAction {
	public function index(){
		$type=I('get.type');
		$this->assign('type',$type);
		$this->display();
	}



}