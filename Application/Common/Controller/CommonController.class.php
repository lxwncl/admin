<?php
/**
 * Created by PhpStorm.
 * User: lxwn
 * Date: 2018/5/9
 * Time: 下午3:40
 */

namespace  Common\Controller;

use Think\Controller;

class CommonController extends Controller
{
	public function _initialize()
	{

	}

	public function ajaxNewReturn($code = 0, $msg = '', $data = [], $type = '', $json_option = 0) {

		$data       =   array(
			'code'      =>      $code,
			'msg'       =>      $msg,
			'data'      =>      $data,
		);

		if(empty($type)) $type  =   C('DEFAULT_AJAX_RETURN');
		switch (strtoupper($type)){
			case 'JSON' :
				// 返回JSON数据格式到客户端 包含状态信息
				header('Content-Type:application/json; charset=utf-8');
				exit(json_encode($data,$json_option));
			case 'XML'  :
				// 返回xml格式数据
				header('Content-Type:text/xml; charset=utf-8');
				exit(xml_encode($data));
			case 'JSONP':
				// 返回JSON数据格式到客户端 包含状态信息
				header('Content-Type:application/json; charset=utf-8');
				$handler  =   isset($_GET[C('VAR_JSONP_HANDLER')]) ? $_GET[C('VAR_JSONP_HANDLER')] : C('DEFAULT_JSONP_HANDLER');
				exit($handler.'('.json_encode($data,$json_option).');');
			case 'EVAL' :
				// 返回可执行的js脚本
				header('Content-Type:text/html; charset=utf-8');
				exit($data);
			default     :
				// 用于扩展其他返回格式数据
				Hook::listen('ajax_return',$data);
		}
	}


	public function output($code, $data = [], $type = '', $json_option = 0)
	{
		$code_msg       =       C('code');
		$msg            =       $code_msg[$code];

		if (empty($msg)) {
			$msg        =       $code;
			$code       =       99998;
		}

		$this->ajaxNewReturn($code , $msg, $data, $type, $json_option);
	}

	protected function newRedirect($url,$params=array(),$delay=0,$msg='') {
		header('Content-type:text/html;charset=utf-8');
		$code           =   C('code');
		$msg            =   $code[$msg] ?   $code[$msg] :   $msg;
		$url            =   U($url,$params);
		redirect($url,$delay,$msg);
	}


}