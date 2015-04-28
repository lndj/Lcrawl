<?php
namespace Lcrawl\Classes;

/**
 * 数据库存储类
 * @Created by Luoning.
 * @Author: Luoning
 * @Mail luoning@luoning.me
 * @Date: 2015-04-04
 * @Time: 14:34
 * @Blog: Http://www.luoning.me
 */

class mysql{
	/*
	*	@param $field_name 数据库字段名，$data 需要存储的数据
	*/
	
	public static function saveData($jwid,$field_name,$data){

		self::getSql();

		$time = time();
		$sql = "SELECT * FROM jiaowu WHERE jwid ='{$jwid}'";
		$result = mysql_query($sql);
		$row = mysql_fetch_array($result);
		if (empty($row)) {
			$sql = "INSERT INTO `jiaowu`(`id`, `jwid`,`{$field_name}`,`lastGetTime`) VALUES (null, '{$jwid}','{$data}','{$time}')";
			if(!mysql_query($sql)){
			echo "存储失败！";
			}	

		}else{

			$sql = "UPDATE jiaowu SET {$field_name} = '{$data}'   WHERE jwid = '{$jwid}'";
			$sql_time = "UPDATE jiaowu SET lastGetTime = '{$time}'   WHERE jwid = '{$jwid}'";
			if(!mysql_query($sql) || !mysql_query($sql_time))
				echo "数据更新失败！";
		}	
	}


	/**数据库链接方法*/
	private static function getSql(){

		$dbname = DB_NAME;
		$host = DB_HOST;
		$port = DB_PORT;
		$user = DB_USER;
		$pwd = DB_PASSWORD;
		$charset = DB_CHARSET;

		/*接着调用mysql_connect()连接服务器*/
		$link = @mysql_connect("{$host}:{$port}",$user,$pwd,true);
		if(!$link) {
					die("Connect Server Failed: " . mysql_error($link));
				   }
		/*连接成功后立即调用mysql_select_db()选中需要连接的数据库*/
		if(!mysql_select_db($dbname,$link)) {
					die("Select Database Failed: " . mysql_error($link));
				   }
		mysql_query("set character set".$charset);
	}
}