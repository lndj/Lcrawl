<?php
namespace Lcrawl;

use Lcrawl\Classes\Lcrawl;

/**
 * 正方教务系统数据查询入口
 * @Created by Luoning.
 * @Author: Luoning
 * @Mail luoning@luoning.me
 * @Date: 2015-04-04
 * @Time: 13:23
 * @Blog: Http://www.luoning.me
 */

//引入配置文件
include_once __DIR__.'/config.php';
//引入自动载入函数
include_once __DIR__.'/autoloader.php';
//调用自动载入函数
AutoLoader::register();

//初始化执行方法
$Lcrawl = new Lcrawl;



