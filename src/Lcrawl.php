<?php
/**
 * This is a lib to crawl the Academic Network Systems.
 * You can achieve easely the querying of grade/schedule/cet/free classroom ...
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
use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\FilesystemCache;

use Lndj\Supports\Log;
use Lndj\Traits\Parser;
use Lndj\Traits\BuildRequest;

class Lcrawl
{
    use Parser, BuildRequest;

    //成绩查询uri
    const ZF_GRADE_URI = 'xscjcx.aspx';

    //考试查询uri
    const ZF_EXAM_URI = 'xskscx.aspx';

    //四六级成绩查询uri
    const ZF_CET_URI = 'xsdjkscx.aspx';

    //课表查询uri
    const ZF_SCHEDULE_URI = 'xskbcx.aspx';

    private $client;

    private $base_uri; //The base_uri of your Academic Network Systems. Like 'http://xuanke.lzjtu.edu.cn/'

    private $login_uri = 'default_ysdx.aspx';

    private $main_page_uri = 'xs_main.aspx';

    private $headers = [
        'timeout' => 3.0,
        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.94 Safari/537.36',
        'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
        'Content-Type' => 'application/x-www-form-urlencoded'
    ];

    private $stu_id;

    private $password;

    private $cacheCookie = false; // Is cookie cached

    private $cache; //Doctrine\Common\Cache\Cache

    private $cachePrefix = 'Lcrawl';

    //The login post param
    private $loginParam = [];

    function __construct($base_uri, $user, $isCacheCookie = false, $loginParam = [])
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
            Log::error('Login Param error!', $user);
            throw new Exception("You must give Lcrawl the user info, like ['stu_id' => '2012xxxxx', 'stu_pwd' => 'xxxx']", 1);
        }
        $client_param = [
            // Base URI is used with relative requests
            'base_uri' => $this->base_uri,
        ];

        //If this value is true, Lcrawl will cache the cookie jar when logining.
        $this->cacheCookie = $isCacheCookie;

        //If don't cache cookies, set cookies true, every request use cookie by default way.
        if (!$this->cacheCookie) {
            $client_param['cookies'] = true;
        }

        //Set the login post param
        if (!empty($loginParam)) {
            $this->loginParam = $loginParam;
        }

        $this->client = new Client($client_param);
    }

    /**
     * Get cookie from cache or login.
     *
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
     *
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
     *
     * @param void
     * @return \Doctrine\Common\Cache\Cache
     */
    public function getCache()
    {
        return $this->cache ?: $this->cache = new FilesystemCache(sys_get_temp_dir());
    }

    /**
     * Set the UserAgent.
     *
     * @param string $ua
     * @return Object $this
     */
    public function setUa($ua)
    {
        $this->headers['User-Agent'] = $ua;
        return $this;
    }

    /**
     * Get the User-Agent value.
     *
     * @return type
     */
    public function getUa()
    {
        return $this->headers['User-Agent'];
    }

    /**
     * Set the Timeout.
     *
     * @param type $time
     * @return type
     */
    public function setTimeOut($time)
    {
        if (!is_numeric($time)) {
            //Should throw a Exception?
            renturn;
        }
        $this->headers['timeout'] = $time;
        return $this;
    }

    /**
     * Get the Timeout.
     *
     * @return type
     */
    public function getTimeOut()
    {
        return $this->headers['timeout'];
    }

    /**
     * Set the Login uri. The default uri is default_ysdx.aspx.
     *
     * @param type $uri
     * @return type
     */
    public function setLoginUri($uri)
    {
        $this->login_uri = $uri;
        return $this;
    }

    /**
     * Get the login uri.
     *
     * @return type
     */
    public function getLoginUri()
    {
        return $this->login_uri;
    }

    /**
     * Set the Referer header.
     *
     * @param type $referer
     * @return type
     */
    public function setReferer($referer)
    {
        $this->headers['referer'] = $referer;
        return $this;
    }

    /**
     * Get the Referer header.
     *
     * @return type
     */
    public function getReferer()
    {
        return $this->headers['Referer'];
    }

    /**
     * Set the cache cookie prefix, default is Lcrawl.
     *
     * @param type $prefix
     * @return type
     */
    public function setCachePrefix($prefix)
    {
        $this->cachePrefix = $prefix;
        return $this;
    }

    /**
     * Get the cache cookie prefix, default is Lcrawl.
     *
     * @return type
     */
    public function getCachePrefix()
    {
        return $this->cachePrefix;
    }

    /**
     * Set the main page uri, the default value is 'xs_main.aspx'
     *
     * @param string $uri
     * @return type
     */
    public function setMainPageUri($uri)
    {
        $this->main_page_uri = $uri;
        return $this;
    }

    /**
     * Get the main page uri, the default value is 'xs_main.aspx'
     *
     * @return string
     */
    public function getMainPageUri()
    {
        return $this->main_page_uri;
    }

    /**
     * Login, and get the cookie jar.
     *
     * @param void
     * @return $this or $jar
     */
    public function login()
    {
        //Get the hidden value from login page.
        $response = $this->client->get($this->login_uri);
        $viewstate = $this->parserHiddenValue($response->getBody());


        //The default login post param
        $loginParam = [
            'viewstate' => '__VIEWSTATE',
            'stu_id' => 'TextBox1',
            'passwod' => 'TextBox2',
            'role' => 'RadioButtonList1',
            'button' => 'Button1'
        ];

        if (!empty($this->loginParam)) {
            $loginParam = $this->loginParam;
        }

        $form_params = [
            $loginParam['viewstate'] => $viewstate,
            $loginParam['stu_id'] => $this->stu_id,
            $loginParam['passwod'] => $this->password,
            $loginParam['role'] => iconv('utf-8', 'gb2312', '学生'),
            $loginParam['button'] => iconv('utf-8', 'gb2312', '登录'),
        ];

        $query = [
            'form_params' => $form_params
        ];

        //If set to cache cookie
        if ($this->cacheCookie) {
            $jar = new \GuzzleHttp\Cookie\CookieJar;
            $query['cookies'] = $jar;
        }
        //Post to login
        $result = $this->client->request('POST', $this->login_uri, $query);

        //Is logining successful?
        $response = $this->client->get($this->main_page_uri, ['allow_redirects' => false, 'query' => ['xh' => $this->stu_id]]);

        switch ($response->getStatusCode()) {
            case 200:
                return $this->cacheCookie ? $jar : $this;
                break;
            case 302:
                Log::info('The password is wrong!', $query);
                throw new \Exception('The password is wrong!', 1);
                break;
            default:
                Log::error('Maybe the data source is broken!', $response);
                throw new \Exception('Maybe the data source is broken!', 1);
                break;
        }
    }

    /**
     * By Concurrent requests, to get all the data.
     *
     * @return Array
     */
    public function getAll()
    {
        $requests = [
            'schedule' => $this->buildGetRequest(self::ZF_SCHEDULE_URI, [], $this->headers, true),
            'cet' => $this->buildGetRequest(self::ZF_CET_URI, [], $this->headers, true),
            'exam' => $this->buildGetRequest(self::ZF_EXAM_URI, [], $this->headers, true),
        ];
        // Wait on all of the requests to complete. Throws a ConnectException
        // if any of the requests fail
        $results = Promise\unwrap($requests);

        // Wait for the requests to complete, even if some of them fail
        // $results = Promise\settle($requests)->wait();

        //Parser the data we need.
        $schedule = $this->parserSchedule($results['schedule']->getBody());
        $cet = $this->parserCommonTable($results['cet']->getBody());
        $exam = $this->parserCommonTable($results['exam']->getBody());

        return compact('schedule', 'cet', 'exam');
    }

    /**
     * Get the grade data. This function is request all of grade.
     *
     * @return type
     */
    public function getGrade()
    {
        //Get the hidden value.
        $response = $this->buildGetRequest(self::ZF_GRADE_URI, [], $this->headers);
        $viewstate = $this->parserOthersHiddenValue($response->getBody());

        $post['__EVENTTARGET'] = '';
        $post['__EVENTARGUMENT'] = '';
        $post['__VIEWSTATE'] = $viewstate;
        $post['hidLanguage'] = '';
        $post['ddlXN'] = '';
        $post['ddlXQ'] = '';
        $post['ddl_kcxz'] = '';
        $post['btn_zcj'] = iconv('utf-8', 'gb2312', '历年成绩');

        $response = $this->buildPostRequest(self::ZF_GRADE_URI, [], $post, $this->headers);

        return $this->parserCommonTable($response->getBody(), '#Datagrid1');
    }

    /**
     * Get the schedule data
     *
     * @return Array
     */
    public function getSchedule()
    {
        /**
         * Default: get the current term schedule data by GET
         * If you want to get the other term's data, use POST
         * TODO: use POST to get other term's data
         */
        $response = $this->buildGetRequest(self::ZF_SCHEDULE_URI, [], $this->headers);
        return $this->parserSchedule($response->getBody());
    }

    /**
     * Get the CET data.
     * @return type|Object
     */
    public function getCet()
    {
        $response = $this->buildGetRequest(self::ZF_CET_URI);
        return $this->parserCommonTable($response->getBody());
    }

    /**
     * Get the default term exam data by GET.
     * If We need another term's data, use POST. //TODO
     *
     * @return type
     */
    public function getExam()
    {
        $response = $this->buildGetRequest(self::ZF_EXAM_URI);
        return $this->parserCommonTable($response->getBody());
    }
}

/**
 * Just a debug function
 *
 * @param Obeject /Array/string $arr
 * @param String $hint debug hint
 * @return void
 */
function dd($arr, $hint = '')
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
