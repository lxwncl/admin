<?php
/**
 * Created by PhpStorm.
 * User: lxwn
 * Date: 2018/5/15
 * Time: 下午2:56
 */

/*
 *
*/
function checkFieldBeEmpty($data, $field_arr, &$msg)
{
	$data_keys      =   array_keys($data);
	$msg            =   '';
	foreach($field_arr as $k=>$v){
		if (!in_array($v, $data_keys)) {
			$msg    =   "{$v}不存在";
			break;
		}
	}
	if ($msg) {
		return true;
	} else {
		return false;
	}

}

function testwrite($d)
{
	$tfile = "_test.txt";
	$fp    = @fopen($d . "/" . $tfile, "w");
	if (!$fp) {
		return false;
	}
	fclose($fp);
	$rs = @unlink($d . "/" . $tfile);
	if ($rs) {
		return true;
	}
	return false;
}

/**
 * 建立文件夹
 *
 * @param string $path
 *
 * @return bool
 */
function create_dir($path)
{
	if (is_dir($path)) {
		return true;
	}
	$path    = dir_path($path);
	$temp    = explode('/', $path);
	$cur_dir = '';
	$max     = count($temp) - 1;
	for ($i = 0; $i < $max; $i++) {
		$cur_dir .= $temp[$i] . '/';
		if (@is_dir($cur_dir)) {
			continue;
		}
		@mkdir($cur_dir, 0777, true);
		@chmod($cur_dir, 0777);
	}
	return is_dir($path);
}

/**
 * 返回路径
 *
 * @param string $path
 *
 * @return string
 */
function dir_path($path)
{
	$path = str_replace('\\', '/', $path);
	if (substr($path, -1) != '/') {
		$path = $path . '/';
	}
	return $path;
}

/**
 * 执行sql文件
 *
 * @param \think\db\ $db
 * @param string     $file
 * @param string     $tablepre
 *
 * @return mixed
 */
function execute_sql($db, $file, $tablepre)
{
	//读取SQL文件
	$sql = file_get_contents(Env::get('app_path') . request()->module() . '/data/' . $file);
	$sql = str_replace("\r", "\n", $sql);
	$sql = explode(";\n", $sql);
	//替换表前缀
	$default_tablepre = "yf_";
	$sql              = str_replace(" `{$default_tablepre}", " `{$tablepre}", $sql);
	//开始安装
	showmsg('开始安装数据库...');
	foreach ($sql as $item) {
		$item = trim($item);
		if (empty($item)) {
			continue;
		}
		preg_match('/CREATE TABLE `([^ ]*)`/', $item, $matches);
		if ($matches) {
			$table_name = $matches[1];
			$msg        = "创建数据表{$table_name}";
			if (false !== $db->execute($item)) {
				showmsg($msg . ' 完成');
			} else {
				session('error', true);
				showmsg($msg . ' 失败！', 'error');
			}
		} else {
			$db->execute($item);
		}
	}
}


/**
 * 实时显示提示信息
 *
 * @param  string $msg   提示信息
 * @param  string $class 输出样式（success:成功，error:失败）
 */
function showmsg($msg, $class = '')
{
	echo $msg."<br/>";
	flush();
	ob_flush();
}