# 简介

使用PHP实现正方教务系统爬虫功能。
Dev分支正在开发中……
使用简单！注释完整！

#项目地址：

http://lcrawl.lzjtuhand.com

http://www.luoning.me/lcrawl.html

# 安装

使用 `composer` 进行安装：
`composer require lndj/Lcrawl`

# Example

```php
<?php

 // 
include './vendor/autoload.php';

use Lndj\Lcrawl;

//教务账号密码
$stu_id = '201201148';
$password = '***********';

$base_uri = 'http://xuanke.lzjtu.edu.cn/';

$Lcrawl = new Lcrawl($base_uri);

$Lcrawl->login($stu_id, $password);

//获取课表
$schedule = $Lcrawl->getSchedule();
//获取CET
$cet = $Lcrawl->getCet();

//获取所有数据，并发异步获取
$all = $Lcrawl->getAll();
``` 
你还可以设置UA/TimeOut等；

```php
$Lcrawl->setUserAgent('Some Agent');
$Lcrawl->setTimeOut(2);
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