<?php
/**
 * Created by PhpStorm.
 * User: lxwn
 * Date: 2018/9/13
 * Time: 上午11:32
 */
namespace  Install\Controller;

use Think\Controller;

class IndexController extends Controller
{
	public $lock_name    =   'install.lock';

	public function _initialize()
	{
//		if (file_exists(RUNTIME_PATH.$this->lock_name)) {
//			header('Location: ' . U('Admin/Index/index'));exit;
//		}
//		file_put_contents(RUNTIME_PATH.$this->lock_name,' ');
	}

	public function index()
	{
		for($i=1;$i<10;$i++){
			if($i%2 == 0){
				sleep(2);
			}
			showmsg($i);
		}
		exit;
		$data                           =   [];
		$icon_correct                   =   '<i class="fa fa-check correct"></i> ';
		$icon_error                     =   '<i class="fa fa-close error"></i> ';
		//php版本、操作系统版本
		$data['phpversion']             =   @phpversion();
		$data['os']                     =   PHP_OS;
		//环境检测
		$err_msg                        =   '';
		if (class_exists('pdo')) {
			$data['pdo']                =   $icon_correct . '已开启';
		} else {
			$data['pdo']                =   $icon_error . '未开启';
			$err_msg                    =   'pdo 未开启';
		}
		//扩展检测
		if (extension_loaded('pdo_mysql')) {
			$data['pdo_mysql']          =   $icon_correct . '已开启';
		} else {
			$data['pdo_mysql']          =   $icon_error . '未开启';
			$err_msg                    =   'pdo_mysql 未开启';
		}
		if (extension_loaded('curl')) {
			$data['curl']               =   $icon_correct . '已开启';
		} else {
			$data['curl']               =   $icon_error . '未开启';
			$err_msg                    =   'curl 未开启';
		}
		if (extension_loaded('mbstring')) {
			$data['mbstring']           =   $icon_correct . '已开启';
		} else {
			$data['mbstring']           =   $icon_error . '未开启';
			$err_msg                    =   'mbstring 未开启';
		}
		//设置获取
		if (ini_get('file_uploads')) {
			$data['upload_size']        =   $icon_correct . ini_get('upload_max_filesize');
		} else {
			$data['upload_size']        =   $icon_error . '禁止上传';
		}
		if (ini_get('allow_url_fopen')) {
			$data['allow_url_fopen']    =   $icon_correct . '已开启';
		} else {
			$data['allow_url_fopen']    =   $icon_error . '未开启';
			$err_msg                    =   'allow_url_fopen 未开启';
		}
		//函数检测
		if (function_exists('file_get_contents')) {
			$data['file_get_contents']  =   $icon_correct . '已开启';
		} else {
			$data['file_get_contents']  =   $icon_error . '未开启';
			$err_msg                    =   'file_get_contents 未开启';
		}
		if (function_exists('session_start')) {
			$data['session']            =   $icon_correct . '已开启';
		} else {
			$data['session']            =   $icon_error . '未开启';
			$err_msg                    =   'session 未开启';
		}
		//检测文件夹属性
		$checklist                      =   [
			'./Application/Runtime',
			'./Application/Admin/Conf/mysql.php',
			'./Application/Admin/Conf/redis.php',
			'./Application/Admin/Conf/code.php',
			'./Application/Install/Conf/web.sql',
		];

		$new_checklist = [];
		foreach ($checklist as $dir) {
			if (is_dir($dir)) {
				$testdir = "./" . $dir;
				create_dir($testdir);
				if (testwrite($testdir)) {
					$new_checklist[$dir]['w']   =   true;
				} else {
					$new_checklist[$dir]['w']   =   false;
					$err_msg                    =   $dir.' 不可写';
				}
				if (is_readable($testdir)) {
					$new_checklist[$dir]['r']   =   true;
				} else {
					$new_checklist[$dir]['r']   =   false;
					$err_msg                    =   $dir.' 不可读';
				}
			} else {
				if (is_writable($dir)) {
					$new_checklist[$dir]['w']   =   true;
				} else {
					$new_checklist[$dir]['w']   =   false;
					$err_msg                    =   $dir.' 不可写';
				}
				if (is_readable($dir)) {
					$new_checklist[$dir]['r']   =   true;
				} else {
					$new_checklist[$dir]['r']   =   false;
					$err_msg                    =   $dir.' 不可读';
				}
			}
		}

		$data['checklist']                      =   $new_checklist;
		$data['err_msg']                        =   $err_msg;
		$this->assign($data);
		$this->display();
	}

	public function step1()
	{


		$this->display();
	}

	public function step2()
	{

	}

	/**
	 * 测试数据库
	 * @throws
	 *
	 */
	public function testdb()
	{
		$db_info    =   I('post.');
		$dsn        =   "mysql:host={$db_info['db_host']};prot={$db_info['db_port']}";
		try{
			$dbh    =   new \PDO($dsn, $db_info['db_user'], $db_info['db_pwd']);//连接数据库
			$this->ajaxReturn(array('code'=>1,'msg'=>'连接成功'));
		} catch (\Exception $e){
			$this->ajaxReturn(array('code'=>$e->getCode(),'msg'=>$e->getMessage()));
		}
	}


}