# 简介

使用PHP实现正方教务系统爬虫功能。

Dev分支正在开发中……

可能是正方教务系统最优雅的一只爬虫。

#项目地址：

http://lcrawl.lzjtuhand.com

http://www.luoning.me/lcrawl.html

# 安装

使用 `composer` 进行安装：
`composer require lndj/Lcrawl`

# Example

```php
<?php

 // Require the compose autoload file
require './vendor/autoload.php';

$stu_id = '201201148';
$password = 'xxxxxxxx';

$user = ['stu_id' => $stu_id, 'stu_pwd' => $password];
$config = [
    'ua' => 'Lzjtuxzs Spider v2.0.0', //设置UA
    'timeout' => 5.0, //超时时间
    'cacheCookie' => false, //是否缓存cookies
    'cachePrefix' => 'Luonning-' //缓存前缀
];

$client = new Lcrawl('http://xuanke.lzjtu.edu.cn/', $user, $config);

//登陆,在cacheCookie为false的情况下，必须执行，开启缓存可省略
$client->login();
// $client->getSchedule();
// $client->getCet();
//获取所有数据
$client->getAll();
``` 
# 高级用法

为达到在登陆一次后的一段时间内，不需要再次执行登陆操作便可直接获取数据，减少教务网请求量，可以使用会话缓存。

首先，在实例化Lcrawl时，传入 `$config['cacheCookie']=> true;` 。

本项目使用 doctrine/cache 来完成缓存工作，它支持基本目前所有的缓存引擎。

在我们的 Lcrawl 中的所有缓存默认使用文件缓存，缓存路径取决于PHP的临时目录，如果你需要自定义缓存，那么你需要做如下的事情：

```php

use Doctrine\Common\Cache\RedisCache;

$cacheDriver = new RedisCache();

// 创建 redis 实例
$redis = new Redis();
$redis->connect('redis_host', 6379);

$cacheDriver->setRedis($redis);

//设置使用redis来缓存会话
$client->setCache($cacheDriver);

```
你可以参考doctrine/cache官方文档来替换掉应用中默认的缓存配置：
> 以 redis 为例
> 请先安装 redis 拓展：https://github.com/phpredis/phpredis

### Laravel 中使用

在 Laravel 中框架使用 `predis/predis` ，那么我们就得使用 `Doctrine\Common\Cache\PredisCache`：

```php

use Doctrine\Common\Cache\PredisCache;

$predis = app('redis')->connection();// connection($name), $name 默认为 `default`
$cacheDriver = new PredisCache($predis);

//设置使用redis来缓存会话
$client->setCache($cacheDriver);
```
> 上面提到的 `app('redis')->connection($name)` , 这里的 `$name` 是 `laravel`项目中配置文件 `database.php` 中 `redis` 配置名 `default` ：`https://github.com/laravel/laravel/blob/master/config/database.php#L118`
如果你使用的其它连接，对应传名称就好了。

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