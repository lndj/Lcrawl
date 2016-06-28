# 简介

[![Join the chat at https://gitter.im/lndj/Lcrawl](https://badges.gitter.im/lndj/Lcrawl.svg)](https://gitter.im/lndj/Lcrawl?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)

使用PHP实现正方教务系统爬虫功能。

可能是正方教务系统最优雅的一只爬虫。

#项目地址：

http://lcrawl.lzjtuhand.com

http://www.luoning.me/lcrawl.html

# 安装

使用 `composer` 进行安装：
`composer require lndj/lcrawl`

要体验最新功能，可以执行：
```shell
git clone https://github.com/lndj/Lcrawl.git
cd Lcrawl
composer install
```
> 注意：请先安装 `composer`

# Example

```php
<?php

 // Require the composer autoload file
require './vendor/autoload.php';

$stu_id = '201201148';
$password = 'xxxxxxxx';

$user = ['stu_id' => $stu_id, 'stu_pwd' => $password];

$client = new Lcrawl('http://xuanke.lzjtu.edu.cn/', $user);

//登陆 -- 没有开启会话缓存，必须调用登陆方法。
$client->login();

//获取所有数据
$all = $client->setUa('Lcrawl Spider V2.0.2')->getAll();


// $client->getSchedule();
// $client->getCet();


``` 

在请求过程中，你还可以设置 `Referer/Timeout` 等 `header` 信息，直接采用链式调用即可。

# 会话缓存

在请求过程中，可以启用会话缓存功能，可以有效减少教务系统会话开启数量。
```php
//实例化过程中传入第三个值
$client = new Lcrawl('http://xuanke.lzjtu.edu.cn/', $user, true);

$all = $client->setUa('Lcrawl Spider V2.0.2')->setTimeOut(3.0)->getAll();
```

# 高级用法

为达到在登陆一次后的一段时间内，不需要再次执行登陆操作便可直接获取数据，减少教务网请求量，可以使用会话缓存。

首先，在实例化Lcrawl时，传入第三个参数为 `true` 。
例如：

```php
//实例化过程中传入第三个值
$client = new Lcrawl('http://xuanke.lzjtu.edu.cn/', $user, true);
```

第三个参数即表示开启会话缓存。

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
