<?php
/**
 * Created by PhpStorm.
 * User: lxwn
 * Date: 2018/5/9
 * Time: 下午3:42
 */

namespace Admin\Controller;

use Common\Controller\CommonController;

class AdminBaseController extends CommonController
{

	public $admin_user_id   =   null;
	public $menu_field      =   "menu_info";//保存菜单的key
	public $jump_field      =   "jump_info";//保存jump的key
	public $user_id_field   =   "admin_user_id";////保存admin_user_id的key
	public $user_type_field =   "admin_user_type";
	public $size            =   5;
	public $page            =   1;

	public function _initialize()
	{
		parent::_initialize(); // TODO: Change the autogenerated stub
		header('Content-type:text/html;charset=utf-8');
		header("Access-Control-Allow-Origin: *");
		$this->_before_index();
		$this->setAssign();
		layout(true);
	}

	//前置操作方法
	public function _before_index(){
		$this->checkLogin();
		$this->checkAuth();
		$this->setParam();
	}

	public function setParam()
	{
		$this->size         =   I("get.size",$this->size);
		$this->page         =   I("get.page",$this->page);
	}

	public function checkLogin()
	{

		if (empty($this->getCache('admin_user_id')) && (strtolower(CONTROLLER_NAME) != 'login')) {
			$this->newRedirect('Login/index',[],5,30001);
		}else{
			$this->admin_user_id    =   $this->getCache($this->user_id_field);
		}
	}

	public function checkAuth()
	{
		$jump       =   strtolower(CONTROLLER_NAME).'/'.strtolower(ACTION_NAME);
		$jump_all   =   strtolower(CONTROLLER_NAME).'/*';
		$jump_arr   =   $this->getCache($this->jump_field);
		$temp       =   explode('/',$jump_arr[0]);
		$uri        =   $temp[1] == "*" ?   $temp[0].'/'.'index'    :   $temp[0].'/'.$temp[1];
		if (!in_array($jump,$jump_arr) && !(in_array($jump_all,$jump_arr)) && (strtolower(CONTROLLER_NAME)!='login') && ($this->getCache($this->user_type_field) != 1)) {
			$this->newRedirect($uri,[],2,30002);
		}
	}

	public function setAssign()
	{
		$menu_info  =   $this->getCache($this->menu_field);

		foreach($menu_info as $k=>&$v){

			foreach($v['son'] as $kk=>&$vv){

				if (strtolower(CONTROLLER_NAME)==strtolower($vv['jump_arr'][0]) && strtolower(ACTION_NAME)==strtolower($vv['jump_arr'][1])) {
					$v['menu_class']        =   "layui-nav-item layui-nav-itemed";
					break;
				}
			}

			if (strtolower(CONTROLLER_NAME)==strtolower($v['jump_arr'][0]) && strtolower(ACTION_NAME)==strtolower($v['jump_arr'][1])) {
				$v['menu_class']            =   "layui-nav-item layui-this";
			}elseif(empty($v['menu_class'])){
				$v['menu_class']            =   "layui-nav-item";
			}
		}
		
		$system_info        =   M('system_info')->find();
		$this->assign('controller_name', strtolower(CONTROLLER_NAME));
		$this->assign('action_name', strtolower(ACTION_NAME));
		$this->assign('menu_info', $menu_info);
		$this->assign('size', $this->size);
		$this->assign('page', $this->page);
		$this->assign('system_info', $system_info);
	}

	/*
	 * 刷新缓存 菜单 权限
	 */
	public function refreshCache($admin_user_id)
	{
		$this->refreshAuthority($admin_user_id);
		$this->refreshMenu($admin_user_id);
	}

	/*
	 * 刷新菜单缓存
	 */
	public function refreshMenu($admin_user_id)
	{
		$account_info                       =   M('admin_user')->where("id={$admin_user_id}")->find();
		$menu_one                           =   M('menu')->where("level=1")->order("sort desc")->select();
		$role_arr                           =   json_decode($account_info['role_id'],true);
		foreach($menu_one as $k=>&$v){
			$menu_two                       =   M('menu')->where("level=2 and parent_id={$v['id']}")->order("sort desc")->select();
			$v['role_arr']                  =   json_decode($v['role_id'],true);
			$v['jump_arr']                  =   explode('/',$v['jump']);
			$v['son']                       =   $menu_two   ?   $menu_two   :   [];
			foreach($v['son'] as $kk=>&$vv){
				$vv['role_arr']             =   json_decode($vv['role_id'],true);
				$vv['jump_arr']             =   explode('/',$vv['jump']);

				if(($account_info['type'] != 1) && (empty(array_intersect($vv['role_arr'],$role_arr)))){
					unset($menu_one[$k]['son'][$kk]);
				}
			}

			if (($account_info['type'] != 1) && (empty(array_intersect($v['role_arr'],$role_arr)))) {
				unset($menu_one[$k]);
			}
		}
		$this->setCache($this->menu_field,$menu_one);
	}

	public function refreshAuthority($admin_user_id)
	{
		$account_info       =   M('admin_user')->where("id={$admin_user_id}")->find();
		$jump               =   array();
		$authority_id       =   array();

		if ($account_info['type'] == 1) {//超级管理员获取所有权限

			$authority_info     =   M('authority')->select();
			foreach($authority_info as $k=>$v){
				$temp           =   json_decode($v['jump'],true);
				$jump           =   array_merge($jump, $temp);
			}

		} else {

			$role_arr           =   json_decode($account_info['role_id'], true);
			$role_info          =   M('role')->where(array('id'=>['in',$role_arr]))->select();

			foreach($role_info as $k=>$v){
				$temp           =   json_decode($v['authority_id'], true);
				$authority_id   =   array_merge($authority_id, $temp);
			}

			$authority_info     =   M('authority')->where(array('id'=>['in',$authority_id]))->select();

			foreach($authority_info as $k=>$v){
				$temp           =   json_decode($v['jump'],true);
				$jump           =   array_merge($jump, $temp);
			}
		}
		$this->setCache($this->jump_field,$jump);
	}

	public function setCache($key,$val)
	{
		//todo redis 存储
		session($key,$val);
	}

	public function getCache($key)
	{
		//todo redis 读取
		return session($key);
	}

	public function delCache($key)
	{
		//todo redis 删除
		session($key,null);
	}

	/*
	 * 获取css图标
	 *
	 */
	public function getIco($type='array')
	{
		$css_str            =   file_get_contents('./Public/css/font.css');
		$pattern            =   '/(icon-\w*):before/';
		$m2                 =   array();
		preg_match_all($pattern,$css_str,$m2);
		$arr                =   $m2[1];

		if ($type=='json') {
			$ico            =   json_encode($arr);
		}else{
			$ico            =   $arr;
		}

		return $ico;
	}

	public function _empty()
	{
		$msg        =    "不存在控制器:".CONTROLLER_NAME.' 或者不存在方法:'.ACTION_NAME;
		$jump_arr   =   $this->getCache($this->jump_field);
		$temp       =   explode('/',$jump_arr[0]);
		$uri        =   $temp[1] == "*" ?   $temp[0].'/'.'index'    :   $temp[0].'/'.$temp[1];
		$this->newRedirect($uri,[],2,$msg);
	}
}