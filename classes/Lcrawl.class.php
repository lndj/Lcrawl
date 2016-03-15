<?php
namespace Lcrawl\Classes;
/**
 * 正方教务系统模拟登陆及爬虫类.
 * @Created by Luoning.
 * @Author: Luoning
 * @Mail luoning@luoning.me
 * @Date: 2015-04-18
 * @Time: 14:12
 * @Blog: Http://www.luoning.me
 */

class Lcrawl{


	static $cookie_jar;

	function __construct(){
		self::$cookie_jar = tempnam('./temp', 'cookie');

	}

	/**
	*	课表信息查询
	*	$is_save 是否存储数据库 1/0
	*/
	public function getSchedule($jwid,$jwpwd){
		
		$cookie_jar = self::getLogin($jwid,$jwpwd);		

		$url_kbcx = INDEX_URL."xskbcx.aspx?xh={$jwid}";
		if ($cookie_jar !== false) {
			$ch=curl_init($url_kbcx);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
			curl_setopt($ch,CURLOPT_REFERER,$url_kbcx);
			curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_jar);
			$str=curl_exec($ch);
			curl_close($ch);

			//将历年成绩页面数据转换为UTF-8编码
			$str=mb_convert_encoding($str, "utf-8", "gb2312");	

			//将网页的表格内容解析为数组
			$str = arrayAnalysis::get_td_array($str);

			if (is_array($str)) {
				array_shift($str);
			}else{
				exit;
			}	
			//存储数据库并return			
			//serialize()序列化存储，取用后使用unserialize()
			$str = serialize($str);						
			mysql::saveData($jwid,'schedule',$str);
			
			return $str;			
		}	
	}


	/*	$is_save 是否存储数据库 1/0  
	*
	*	教务网默认查询当前学年《第一学期》的补考安排，可GET获取，其余学期的需POST请求，参数：
	*	__EVENTTARGET:xqd
	*	__EVENTARGUMENT://空
	*	__VIEWSTATE://隐藏域值
	*	xnd:2014-2015 //默认当前学年，其余传值无效
	*	xqd:2	
	*/
	
	public function getMakeupExam($jwid,$jwpwd){

		$cookie_jar = self::getLogin($jwid,$jwpwd);
		//$cookie_jar = self::$cookie_jar;
		$url_bkcx = INDEX_URL."XsBkKsCx.aspx?xh={$jwid}";
		if ($cookie_jar !== false) {

			/**一下为默认GET获取当前学年第一学期补考安排*/
			/*$ch=curl_init($url_bkcx);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
			curl_setopt($ch,CURLOPT_REFERER,$url_bkcx);
			curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_jar);
			$str=curl_exec($ch);
			curl_close($ch);

			$str=mb_convert_encoding($str, "utf-8", "gb2312");

			$td=arrayAnalysis::get_td_array($str);

			//if (is_array($td)) {

				array_shift($td);

			}else{
				exit;
			}

			return $td;*/


			/**以下通过POST方式获取任意学期补考安排*/
			$ch=curl_init($url_bkcx);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
			curl_setopt($ch,CURLOPT_REFERER,$url_bkcx);
			curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_jar);
			$str=curl_exec($ch);
			curl_close($ch);
			

			$pattern = '/<input type="hidden" name="__VIEWSTATE" value="(.*)" \/>/i';
			preg_match($pattern, $str, $matches);
			
			$view_size = sizeof($matches);
			// var_dump($view_size);
			if ($view_size > 1) {
				$viewstate = urlencode($matches[1]);
			}else{
				echo "网络故障!";
				exit;
			}

			$makeup_view = urlencode($matches[1]);
			//print_r($makeup_view);

			/**补考安排查询教务网比较奇葩哈，参数xnd也即学年只可选择当前学年，其余传值无效*/
			$postdata="__EVENTTARGET=xqd&__EVENTARGUMENT=&__VIEWSTATE={$makeup_view}&xnd=".YEAR_NOW."&xqd=".TERM_NOW;

			$ch=curl_init($url_bkcx);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
			curl_setopt($ch,CURLOPT_REFERER,$url_bkcx);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
			curl_setopt($ch,CURLOPT_COOKIEFILE,$cookie_jar);
			$str=curl_exec($ch);
			curl_close($ch);

			//将历年成绩页面数据转换为UTF-8编码
			$str=mb_convert_encoding($str, "utf-8", "gb2312");	

			//将网页的表格内容解析为数组
			$str = arrayAnalysis::get_td_array($str);
			if (is_array($str)) {
				array_shift($str);
			}else{
				exit;
			}
			//存储数据库并return			
			//serialize()序列化存储，取用后使用unserialize()
			$str = serialize($str);						
			mysql::saveData($jwid,'makeupExam',$str);
			
			return $str;
		}
	}

	/**等级考试信息只是获取教务网数据，四六级成绩只针对本校在校生
	*	$is_save 是否存储数据库 1/0
	*/

	public static function getGradeExam($jwid,$jwpwd){
		
		$cookie_jar = self::getLogin($jwid,$jwpwd);
		//$cookie_jar = self::$cookie_jar;
		$url_djkscx = INDEX_URL."xsdjkscx.aspx?xh={$jwid}";
		if ($cookie_jar !== false) {
			$ch=curl_init($url_djkscx);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
			curl_setopt($ch,CURLOPT_REFERER,$url_djkscx);
			curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_jar);
			$str=curl_exec($ch);
			curl_close($ch);

			//将历年成绩页面数据转换为UTF-8编码
			$str=mb_convert_encoding($str, "utf-8", "gb2312");	

			//将网页的表格内容解析为数组
			$str = arrayAnalysis::get_td_array($str);

			if (is_array($str)) {
				array_shift($str);
			}else{
				exit;
			}
			//存储数据库并return			
			//serialize()序列化存储，取用后使用unserialize()
			$str = serialize($str);						
			mysql::saveData($jwid,'gradeExam',$str);
			
			return $str;				
		}
	}


	/*  获取正方教务系统成绩统计信息.
	*	$is_save 是否存储数据库：1/0
	*/
	public static  function getGradeCount($jwid,$jwpwd){

		$cookie_jar = self::getLogin($jwid,$jwpwd);
		
		$url_cjcx = INDEX_URL."xscjcx.aspx?xh={$jwid}";

		if ($cookie_jar !== false){
			//到达成绩查询选择项页面
			$ch=curl_init($url_cjcx);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
			curl_setopt($ch,CURLOPT_REFERER,$url_cjcx);
			curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_jar);
			$str=curl_exec($ch);
			curl_close($ch);

			//进入成绩统计，先抓取隐藏域value
			$pattern = '/<input type="hidden" name="__VIEWSTATE" value="(.*)" \/>/i';
			preg_match($pattern, $str, $matches);

			$view_size = sizeof($matches);
			// var_dump($view_size);
			if ($view_size > 1) {
				$viewstate = urlencode($matches[1]);
			}else{
				echo "网络故障!";
				exit;
			}

			$newview = urlencode($matches[1]);
			//print_r($newview);

			$button=iconv('utf-8', 'gb2312', '成绩统计');
			$postdata = "__VIEWSTATE={$newview}&hidLanguage=&ddlXN=&ddlXQ=2&ddl_kcxz=&Button1=".$button;

			$ch=curl_init($url_cjcx);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
			curl_setopt($ch,CURLOPT_REFERER,$url_cjcx);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
			curl_setopt($ch,CURLOPT_COOKIEFILE,$cookie_jar);
			$str=curl_exec($ch);
			curl_close($ch);


			//将历年成绩页面数据转换为UTF-8编码
			$str=mb_convert_encoding($str, "utf-8", "gb2312");	

			//将网页的表格内容解析为数组
			$str = arrayAnalysis::get_td_array($str);
			
			if (is_array($str)) {
				array_shift($str);
			}else{
				exit;
			}
			//存储数据库并return			
			//serialize()序列化存储，取用后使用unserialize()
			$str = serialize($str);						
			mysql::saveData($jwid,'gradeCount',$str);
			
			return $str;	
		}
	}


	/*
	*	进入历年成绩页面，抓取成绩
	*	$is_save 是否存储数据库：1/0
	*/

	public static function getGrade($jwid,$jwpwd){		
		
		$cookie_jar = self::getLogin($jwid,$jwpwd);
		
		if ($cookie_jar !== false) {
				
			$url_cjcx = INDEX_URL."xscjcx.aspx?xh={$jwid}";
			//到达成绩查询选择项页面
			$ch=curl_init($url_cjcx);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
			curl_setopt($ch,CURLOPT_REFERER,$url_cjcx);
			curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_jar);
			$str=curl_exec($ch);
			curl_close($ch);
			
			//进入历年成绩，先抓取隐藏域value
			$pattern = '/<input type="hidden" name="__VIEWSTATE" value="(.*)" \/>/i';
			preg_match($pattern, $str, $matches);
			$view_size = sizeof($matches);
			// var_dump($view_size);
			if ($view_size > 1) {
				$viewstate = urlencode($matches[1]);
			}else{
				echo "网络故障!";
				exit;
			}
			$newview = urlencode($matches[1]);
			//print_r($newview);

			$button=iconv('utf-8', 'gb2312', '历年成绩');
			$postdata="__VIEWSTATE={$newview}&hidLanguage=&ddlXN=&ddlXQ=2&ddl_kcxz=&btn_zcj=".$button;
			
			$ch=curl_init($url_cjcx);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
			curl_setopt($ch,CURLOPT_REFERER,$url_cjcx);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
			curl_setopt($ch,CURLOPT_COOKIEFILE,$cookie_jar);
			$str=curl_exec($ch);
			curl_close($ch);

			//将历年成绩页面数据转换为UTF-8编码
			$str=mb_convert_encoding($str, "utf-8", "gb2312");	

			//将网页的表格内容解析为数组
			$str = arrayAnalysis::get_td_array($str);
			
			if (is_array($str)) {
				array_shift($str);
			}else{
				exit;
			}
			//存储数据库并return			
			//serialize()序列化存储，取用后使用unserialize()
			$str = serialize($str);						
			mysql::saveData($jwid,'grade',$str);

			return $str;
		}
	}

	/**
	*	默认查询当前学期的考试安排，可GET获取，其余学期的需POST请求，参数：
	*	__EVENTTARGET:xqd
	*	__EVENTARGUMENT://空
	*	__VIEWSTATE://隐藏域获取
	*	xnd:2014-2015
	*	xqd:1
	*
	*/
	public static function getExam($jwid,$jwpwd){
	
		$cookie_jar = self::getLogin($jwid,$jwpwd);
		if ($cookie_jar !== false) {
			$url_kscx = INDEX_URL."xskscx.aspx?xh={$jwid}";

			$ch=curl_init($url_kscx);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
			curl_setopt($ch,CURLOPT_REFERER,$url_kscx);
			curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_jar);
			$str=curl_exec($ch);
			curl_close($ch);			
			
			//将历年成绩页面数据转换为UTF-8编码
			$str=mb_convert_encoding($str, "utf-8", "gb2312");	

			//将网页的表格内容解析为数组
			$str = arrayAnalysis::get_td_array($str);
			
			if (is_array($str)) {
				array_shift($str);
			}else{
				exit;
			}
			//存储数据库并return			
			//serialize()序列化存储，取用后使用unserialize()
			$str = serialize($str);						
			mysql::saveData($jwid,'exam',$str);

			return $str;	
		}
	}

	/**
	*	默认查询当前学期的选课情况，可GET获取，其余学期的需POST请求，参数：
	*	__EVENTTARGET:ddlXN
	*	__EVENTARGUMENT://空
	*	__VIEWSTATE:隐藏域值
	*	ddlXN:2013-2014
	*	ddlXQ:1
	*
	*/
	public static function getChooseCourses($jwid,$jwpwd){

		$cookie_jar = self::getLogin($jwid,$jwpwd);
		$url_xk = INDEX_URL."xsxkqk.aspx?xh={$jwid}";
		if ($cookie_jar !== false) {
			$ch=curl_init($url_xk);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
			curl_setopt($ch,CURLOPT_REFERER,$url_xk);
			curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_jar);
			$str=curl_exec($ch);
			curl_close($ch);

			//将历年成绩页面数据转换为UTF-8编码
			$str=mb_convert_encoding($str, "utf-8", "gb2312");	

			//将网页的表格内容解析为数组
			$str = arrayAnalysis::get_td_array($str);
			
			if (is_array($str)) {
				array_shift($str);
			}else{
				exit;
			}
			//存储数据库并return			
			//serialize()序列化存储，取用后使用unserialize()
			$str = serialize($str);						
			mysql::saveData($jwid,'chooseCourses',$str);

			return $str;	
		}
	}

	private static function getLogin($jwid,$jwpwd){

		//定义Cookie存储路径,绝对路径
		//$cookie_jar = dirname(__FILE__)."/jw.cookie";
		//$cookie_jar = tempnam('./temp', 'cookie');
		$cookie_jar = self::$cookie_jar;
	    //将cookie存入文件
	    $url = LOGIN_URL;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_jar);
		$content = curl_exec($ch);
		curl_close($ch);
		
		//取出cookie，一起提交给服务器，让服务器以为是浏览器打开登陆页面
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_jar);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$ret = curl_exec($ch);
		curl_close($ch);

		//login 
		$pattern = '/<input type="hidden" name="__VIEWSTATE" value="(.*)" \/>/i';
		preg_match($pattern, $ret, $matches);
		
		$view_size = sizeof($matches);
		// var_dump($view_size);
		if ($view_size > 1) {
			$viewstate = urlencode($matches[1]);
		}else{
			echo "网络故障!";
			exit;
		}
		
		//print_r($viewstate);
		
		$post = "__VIEWSTATE={$viewstate}&TextBox1={$jwid}&TextBox2={$jwpwd}&RadioButtonList1=%D1%A7%C9%FA&Button1=&lbLanguage=";    
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, INDEX_URL."(gsv5oxfexlttmv32ll1nbcy4)/default_ysdx.aspx");
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_jar);
		$result=curl_exec($ch);
		curl_close($ch);

		/*
		*	验证密码是否正确
		**/
		if(preg_match("/xs_main/",$result)){
			return $cookie_jar;;
		}else{
			echo "密码错误或网络故障，登陆失败，学号：".$jwid."，使用密码：".$jwpwd."<br />";
			return false;
		}
	}
}