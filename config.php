<?php
namespace Lcrawl;
/**
 * 获取正方教务系统数据主配置文件.
 * @Created by Luoning.
 * @Author: Luoning
 * @Mail luoning@luoning.me
 * @Date: 2015-04-18
 * @Time: 14:16
 * @Blog: Http://www.luoning.me
 */


// **教务网相关配置项 ** //

//教务网免验证码登录页地址
define('LOGIN_URL','http://xuanke.lzjtu.edu.cn/default_ysdx.aspx');

//教务网首页地址
define('INDEX_URL', 'http://xuanke.lzjtu.edu.cn/');

//当前学年
define('YEAR_NOW','2012-2013');

//当前学期，值为1或2
define('TERM_NOW','2');


// ** MySQL 设置 - 具体信息来自您正在使用的主机 ** //

/** 数据库的名称 */
define('DB_NAME', 'yourdbname');

/** MySQL数据库用户名 */
define('DB_USER', 'root');

/** MySQL数据库密码 */
define('DB_PASSWORD', 'passwd');

/** MySQL主机 */
define('DB_HOST', 'localhost');

/** 数据库端口 */
define('DB_PORT', 3306);

/** 创建数据表时默认的文字编码 */
define('DB_CHARSET', 'utf8');

/** 数据库整理类型。如不确定请勿更改 */
define('DB_COLLATE', '');

////-----引入系统所需类库----若PHP版本低于5.3，请取消注释，并删除所有namespace---------------

// //引入登陆教务系统类
// include_once 'classes/Lcrawl.class.php';
// //引入数据库类
// include_once 'classes/mysql.class.php';
// //引入解析数组类
// include_once 'classes/arrayAnalysis.class.php';
