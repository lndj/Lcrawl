<?php

require 'vendor/autoload.php';

use Lndj\Lcrawl;
use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use GuzzleHttp\Pool;
use Symfony\Component\DomCrawler\Crawler;
use Doctrine\Common\Cache\FileCache ;
// $client = new Lcrawl('http://xuanke.lzjtu.edu.cn/');

$stu_id = '201201148';
$password = 'luowei2008';


// $client->login($stu_id, $password);
// // $client->getSchedule();
// // $client->getCet();
// $client->getAll();


$client = new Client([
    // Base URI is used with relative requests
    'base_uri' => 'http://xuanke.lzjtu.edu.cn/',
    // You can set any number of default request options.
    'timeout'  => 2.0,
    'cookies' => true,
    'headers' => [
        'User-Agent'   => 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.94 Safari/537.36',
        'Accept'       => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
        'Referer'      => 'http://xuanke.lzjtu.edu.cn/default_ysdx.aspx',
        'Content-Type' => 'application/x-www-form-urlencoded'
    ], 
]);

/*$jar = new \GuzzleHttp\Cookie\CookieJar;
//Get the hidden value from login page.
$response = $client->get('default_ysdx.aspx');
$body = $response->getBody();

$crawler = new Crawler((string)$body);
$crawler = $crawler->filterXPath('//*[@id="form1"]/input');
$viewstate = $crawler->attr('value');

//Post to login
$response = $client->request('POST', 'default_ysdx.aspx', [
     'form_params' => [
     	'__VIEWSTATE'      => $viewstate,
     	'TextBox1'         => $stu_id,
     	'TextBox2'	       => $password,
     	'RadioButtonList1' => iconv('utf-8', 'gb2312', '学生'),
     	'Button1'          => iconv('utf-8', 'gb2312', '登录'),
     ],
     'cookies' => $jar,
]);
*/


// $jar = serialize($jar);
// file_put_contents($stu_id . '.txt', $jar);


// die;
$jar = file_get_contents($stu_id . '.txt');
$jar = unserialize($jar);

// var_dump($jar);
$response = $client->get('xskbcx.aspx', [
	 'query' => ['xh' => $stu_id],
	 'cookies' => $jar
]);
$body = $response->getBody();
$data = parserSchedule($body);
echo "<pre>";
print_r($data);
echo "</pre>";




function parserSchedule($body)
{
	$crawler = new Crawler((string)$body);
	$crawler = $crawler->filter('#Table1');
	$schedule = $crawler->children();
	
	$format_arr = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
	$data = [];
	$data_line = [];

	//loop the row
	for ($i=2; $i <= 10; $i++) { 
		if ($i % 2 === 0) {
			//Every 4 lines lack 1 row
			if ($i % 4 === 0) {
				for ($j=1; $j <= 7; $j++) { 
					$schedule_info = $schedule->eq($i)->children()->eq($j)->html();
					array_push($data_line, $schedule_info);
				}	
				continue;
			}
			//Loop the line
			for ($j=2; $j <= 8; $j++) { 
				$schedule_info = $schedule->eq($i)->children()->eq($j)->html();
				array_push($data_line, $schedule_info);
			}
		}
	}
	//Formate the data array.
	$data = array_chunk($data_line,5);
	return array_combine($format_arr, $data);
}