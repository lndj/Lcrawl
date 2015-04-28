<?php
namespace Lcrawl;

/**
 * 正方教务系统数据查询DEMO
 * @Created by Luoning.
 * @Author: Luoning
 * @Mail luoning@luoning.me
 * @Date: 2015-04-28
 * @Time: 17:50
 * @Blog: Http://www.luoning.me
 */
include 'run.php';


//教务账号密码
$jwid = '201201148';
$jwpwd = '***********';

/*
*   任务分发执行
*   默认序列化存储，数据获取是情反序列化
*   具体可查看 classes/Lcrwl.class.php使用情况
*   请自行选择使用json_encode/json_decode还是serilize/unserilize函数
*/

//获取成绩数据，获取的历年成绩
$Lcrawl->getGrade($jwid,$jwpwd);

//获取考试安排数据
$Lcrawl->getExam($jwid,$jwpwd);

//获取选课安排数据
$Lcrawl->getChooseCourses($jwid,$jwpwd);

//获取等级考试数据
$Lcrawl->getGradeExam($jwid,$jwpwd);

//获取补考安排数据
$Lcrawl->getMakeupExam($jwid,$jwpwd);

//获取课表数据
$Lcrawl->getSchedule($jwid,$jwpwd);

//获取成绩统计数据
$Lcrawl->getGradeCount($jwid,$jwpwd);