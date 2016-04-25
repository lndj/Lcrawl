#框架简介：


使用PHP+MySQL实现正方教务系统爬虫功能。


目前已经实现通过模拟登陆来获取成绩、课表、选课、考试、等级考试、补考、成绩统计等数据的爬取并过滤存储。


使用简单！注释完整！

#项目地址：

http://lcrawl.lzjtuhand.com

http://www.luoning.me/lcrawl.html

#框架详情：

<code>classes</code>为框架核心类文件；<code>temp</code>为缓存文件夹，存储临时cookie,可定期清除；
<code>autoloader.php</code>框架自动载入文件；<code>run.php</code>框架入口文件，使用时直接<code>include 'run.php';</code>即可。

#使用方法：

```php

<?php

 //载入框架入口 
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
```

# License

MIT License

Copyright (c) [2015] [Ning Luo]

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.