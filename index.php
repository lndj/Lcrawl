<?php

require 'vendor/autoload.php';

use Lndj\Lcrawl;
use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use GuzzleHttp\Pool;
use Symfony\Component\DomCrawler\Crawler;
use Doctrine\Common\Cache\FileCache ;


$stu_id = '201201148';
$password = 'luowei2008';

$user = ['stu_id' => $stu_id, 'stu_pwd' => $password];
$config = [
	'ua' => 'Lzjtuxzs Spider v2.0.0',
	'timeout' => 5.0,
	'cacheCookie' => false,
	'cachePrefix' => 'Luonning-'
];

$client = new Lcrawl('http://xuanke.lzjtu.edu.cn/', $user, $config);


$client->login();
// $client->getSchedule();
// $client->getCet();
$client->getAll();
