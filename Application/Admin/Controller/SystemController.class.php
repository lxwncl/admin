<?php
/**
 * Created by PhpStorm.
 * User: lxwn
 * Date: 2018/5/17
 * Time: 下午6:23
 */

namespace  Admin\Controller;

class SystemController extends AdminBaseController
{
	public function _initialize()
	{
		parent::_initialize(); // TODO: Change the autogenerated stub
	}

	public function index()
	{
		$this->display();
	}
}