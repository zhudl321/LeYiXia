<?php
class EmptyAction extends BaseAction{
	public function index(){
		   redirect(U('/'));
        }
}