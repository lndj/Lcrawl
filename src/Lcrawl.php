<?php
/**
 * This is a lib to crawl the Academic Network Systems.
 * You can easely achieve the querying of grade/schedule/cet/free classroom ...
 *
 * @author Ning Luo <luoning@luoning.me>
 * @link https://github.com/lndj/Lcrawl
 * @package lndj/Lcrawl
 * @category spider | crawl
 * @license MIT
 */

namespace Lndj;

use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use GuzzleHttp\Pool;
use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\FilesystemCache;

class Lcrawl
{
    use Parser, BuildRequest;

    private $client;

    private $base_uri; //The base_uri of your Academic Network Systems. Like 'http://xuanke.lzjtu.edu.cn/'

    private $timeout = 3.0;

    private $ua = 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.94 Safari/537.36';

    private $stu_id;

    private $password;

    private $cacheCookie = false; // Is cookie cached

    private $cache; //Doctrine\Common\Cache\Cache

    private $cachePrefix = 'Lcrawl';

    function __construct($base_uri, $user, $config = [])
    {
        //Set the base_uri.
        $this->base_uri = $base_uri;

        //Set the stu_id and password
        if (is_array($user) && $user['stu_id'] && $user['stu_pwd']) {
            $this->stu_id = $user['stu_id'];
            $this->password = $user['stu_pwd'];
        } elseif (is_object($user) && $user->stu_id && $user->stu_pwd) {
            $this->stu_id = $user->stu_id;
            $this->password = $user->stu_pwd;
        } else {
            throw new Exception("You must give Lcrawl the user info, like ['stu_id' => '2012xxxxx', 'stu_pwd' => 'xxxx']", 1);
        }
        //Set the config, like cacheCookie/UA/Timeout
        if (!empty($config)) {
            foreach ($config as $con => $value) {
                $this->$con = $value;
            }
        }
        $client_param = [
            // Base URI is used with relative requests
            'base_uri' => $this->base_uri,
            // You can set any number of default request options.
            'timeout'  => $this->timeout,
            'headers' => [
                'User-Agent'   => $this->ua,
                'Accept'       => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'Referer'      => $this->base_uri . 'default_ysdx.aspx',
                'Content-Type' => 'application/x-www-form-urlencoded'
            ],
        ];
        //If don't cache cookies, set cookies true, every request use cookie by default way.
        if (!$this->cacheCookie) {
            $client_param['cookies'] = true;
        }
        $this->client = new Client($client_param);
    }

    /**
     * Get cookie from cache or login.
     * @param bool $forceRefresh
     * @return string
     */
    public function getCookie($forceRefresh = false)
    {
        $cacheKey = $this->cachePrefix . $this->stu_id;
        $cached = $this->getCache()->fetch($cacheKey);
        if ($forceRefresh || empty($cached)) {
            $jar = $this->login();
            //Cache the cookieJar 3000 s.
            $this->getCache()->save($cacheKey, serialize($jar), 3000);
            return $jar;
        }
        return unserialize($cached);
    }

    /**
     * Set the cache manager.
     * @param Doctrine\Common\Cache\Cache
     * @return Lcrawl
     */
     public function setCache(Cache $cache)
    {
        $this->cache = $cache;
        return $this;
    }

    /**
     * Return the cache manager.
     * @param void
     * @return \Doctrine\Common\Cache\Cache
     */
    public function getCache()
    {
        return $this->cache ?: $this->cache = new FilesystemCache(sys_get_temp_dir());
    }

    /**
     * Login, and get the cookie jar.
     * @param void
     * @return $this or $jar
     */
    public function login()
    {
        //Get the hidden value from login page.
        $response = $this->client->get('default_ysdx.aspx');
        $viewstate = $this->parserHiddenValue($response->getBody());
        $query = [
             'form_params' => [
                 '__VIEWSTATE'      => $viewstate,
                 'TextBox1'         => $this->stu_id,
                 'TextBox2'           => $this->password,
                 'RadioButtonList1' => iconv('utf-8', 'gb2312', '学生'),
                 'Button1'          => iconv('utf-8', 'gb2312', '登录'),
             ]
        ];
        //If set to cache cookie
        if ($this->cacheCookie) {
            $jar = new \GuzzleHttp\Cookie\CookieJar;
            $query['cookies'] = $jar;
        }
        //Post to login
        $this->client->request('POST', 'default_ysdx.aspx', $query);

        return $this->cacheCookie ? $jar : $this;
    }

    /**
     * By Concurrent requests, to get all the data.
     * @return Array
     */
    public function getAll()
    {
        $requests = [
            'schedule' => $this->buildGetRequest('xskbcx.aspx', [], true),
            'cet' => $this->buildGetRequest('xsdjkscx.aspx', [], true),
        ];
        $results = Promise\unwrap($requests);

        //Parser the data we need.
        $schedule = $this->getSchedule();
        $cet = $this->getCet();

        return compact('schedule', 'cet');
    }

    /**
     * Get the schedule data
     * @return Array
     */
    public function getSchedule()
    {
        /**
         * Default: get the current term schedule data by GET
         * If you want to get the other term's data, use POST
         * TODO: use POST to get other term's data
         */
        $response = $this->buildGetRequest('xskbcx.aspx');
        return $this->parserSchedule($response->getBody());
    }

    /**
     * Get the CET data.
     * @return type|Object
     */
    public function getCet()
    {
        $response = $this->buildGetRequest('xsdjkscx.aspx');
        return $this->parserCommonTable($response->getBody());
    }
}

/**
 * Just a debug function
 * @param Obeject/Array/string $arr
 * @param String $hint debug hint
 * @return void
 */
function dd($arr,$hint = '')
{
    if (is_object($arr) || is_array($arr)) {
        echo "<pre>";
        print_r($arr);
        echo PHP_EOL . $hint;
        echo "</pre>";
    } else {
        var_dump($arr);
        echo PHP_EOL . $hint;
    }
}
