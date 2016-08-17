<?php
class LeAction extends BaseAction{
    public function add(){
    	$this->ck_login();
    	$this->hot_tag(25);
    	$this->display();
    }
}