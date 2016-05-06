<?php
/**
 * This a lib to crawl the Academic Network Systems.
 * You can easely achieve the querying grade/schedule/cet/free classroom ...
 * @author Ning Luo <luoning@luoning.me>
 * @license MIT
 */

namespace Lndj;

use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use GuzzleHttp\Pool;
use Symfony\Component\DomCrawler\Crawler;

use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\RequestException;

/**
* 
*/
class Lcrawl
{
	private $client;

	private $base_uri; //The base_uri of your Academic Network Systems. Like 'http://xuanke.lzjtu.edu.cn/'

	private $timeout = 2.0; 

	private $ua = 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.94 Safari/537.36';

	private $stu_id;

	private $password;

	private $cacheCookie = false; // Is cookie cached
	
	function __construct($base_uri)
	{
		$this->base_uri = $base_uri;

		$this->client = new Client([
		    // Base URI is used with relative requests
		    'base_uri' => $this->base_uri,
		    // You can set any number of default request options.
		    'timeout'  => $this->timeout,
		    'cookies' => true,
		    'headers' => [
		        'User-Agent'   => $this->ua,
		        'Accept'       => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
		        'Referer'      => $this->base_uri . 'default_ysdx.aspx',
		        'Content-Type' => 'application/x-www-form-urlencoded'
		    ], 
		]);
	}

	public function setTimeOut($timeout)
	{
		if (!is_numeric($timeout)) {
			throw new Exception('The Timeout value must be a number', 1);
		}
		$this->timeout = $timeout;
	}
	public function setUserAgent($ua)
	{
		$this->ua = $ua;
	}
	public function setCookieCache()
	{
		
	}


	public function login($stu_id, $password)
	{	
		$this->stu_id = $stu_id;
		$this->password = $password;

		//Get the hidden value from login page.
		$response = $this->client->get('default_ysdx.aspx');
		$body = $response->getBody();

		$crawler = new Crawler((string)$body);
		$crawler = $crawler->filterXPath('//*[@id="form1"]/input');
		$viewstate = $crawler->attr('value');

		//Post to login
		$response = $this->client->request('POST', 'default_ysdx.aspx', [
		     'form_params' => [
		     	'__VIEWSTATE'      => $viewstate,
		     	'TextBox1'         => $this->stu_id,
		     	'TextBox2'	       => $this->password,
		     	'RadioButtonList1' => iconv('utf-8', 'gb2312', '学生'),
		     	'Button1'          => iconv('utf-8', 'gb2312', '登录'),
		     ]
		]);
	}

	
	/**
	 *	By Concurrent requests, to get all the data.
	 */
	public function getAll()
	{
		$requests = [
		    'schedule' => $this->scheduleRequest(true),
		    'cet'   => $this->cetRequest(true),
		];
		$results = Promise\unwrap($requests);

		//Parser the data we need.
		$schedule = $this->parserSchedule($results['schedule']->getBody());
		$cet = $this->parserCet($results['cet']->getBody());
		
		//Put data in a array.
		$all_data = [];
		$all_data['schedule'] = $schedule;
		$all_data['cet'] = $cet;

		echo "<pre>";
		print_r($all_data);
		echo "</pre>";

	}

	public function getSchedule()
	{
		// Default: get the current term schedule data by GET
		// If you want to get the other term's data, use POST
		// TODO: use POST to get other term's data 
		$response = $this->scheduleRequest();
		$body = $response->getBody();
		$data = $this->parserSchedule($body);
		echo "<pre>";
		print_r($data);
		echo "</pre>";
	}

	public function getCet()
	{
		$response = $this->cetRequest();
		$body = $response->getBody();
		$data = $this->parserCet($body);
		echo "<pre>";
		print_r($data);
		echo "</pre>";
	}

	private function scheduleRequest($isAsync = false)
	{
		if ($isAsync) {
			return $this->client->getAsync('xskbcx.aspx', [
				 'query' => ['xh' => $this->stu_id]
			]);
		}
		return $this->client->get('xskbcx.aspx', [
			 'query' => ['xh' => $this->stu_id]
		]);
	}

	private function cetRequest($isAsync = false)
	{
		if ($isAsync) {
			return $this->client->getAsync('xsdjkscx.aspx', [
				 'query' => ['xh' => $this->stu_id]
			]);
		}
		return $this->client->get('xsdjkscx.aspx', [
			 'query' => ['xh' => $this->stu_id]
		]);
	}

	private function parserSchedule($body)
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

	private function parserCet($body)
	{
		$crawler = new Crawler((string)$body);
		
		$crawler = $crawler->filter('#DataGrid1');
		$cet = $crawler->children();
		$data = $cet->each(function (Crawler $node, $i) {
		    return $node->children()->each(function (Crawler $node, $j) {
		    	return $node->text();
		    });
		});
		//Unset the title.
		unset($data[0]);
		return $data;
	}
}
