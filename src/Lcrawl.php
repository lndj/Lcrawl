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

    private $base_uri;

    //登录页
    private $login_uri = 'default2.aspx';

    private $code_uri = 'CheckCode.aspx';
    //主页
    private $main_page_uri = 'xs_main.aspx';

    //设置头信息
    private $headers = [
        'timeout' => 3.0,
        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.75 Safari/537.36',
        'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
        'Content-Type' => 'application/x-www-form-urlencoded',
        'referer'   =>  'http://jwxt.hzu.gx.cn/'
    ];

    //登录隐藏
    private $stu_id;

    private $password;
    //访问页面的sessionid
    private $sessionId;

    private $cacheCookie = false; // Is cookie cached

    private $cache; //Doctrine\Common\Cache\Cache

    private $cachePrefix = 'Lcrawl';

    //The login post param
    private $loginParam = [];

    /**
     * Lcrawl constructor.
     * @param $base_uri
     * @param $user
     * @param bool $isCacheCookie
     * @param array $loginParam
     * @throws \Exception
     */
    public function __construct($base_uri, $user, $isCacheCookie = false, $loginParam = [])
    {

        //Set SessionId
        $this->sessionId = $this->getSessionId($base_uri);

        //Set the base_uri.
        $this->base_uri = $base_uri."($this->sessionId)/";

        //Set the stu_id and password
        if (is_array($user) && $user['stu_id'] && $user['stu_pwd']) {
            $this->stu_id = $user['stu_id'];
            $this->password = $user['stu_pwd'];
        } elseif (is_object($user) && $user->stu_id && $user->stu_pwd) {
            $this->stu_id = $user->stu_id;
            $this->password = $user->stu_pwd;
        } else {
            throw new \Exception("You must give Lcrawl the user info, like ['stu_id' => '2016xxxxx', 'stu_pwd' => 'xxxx']", 1);
        }

        $client_param = [
            // Base URI is used with relative requests
            'base_uri' => $this->base_uri,
            'headers'  => $this->headers
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
     * Login, and get the cookie jar.
     *
     * @author 勇敢的小笨羊
     * @return \GuzzleHttp\Cookie\CookieJar|Lcrawl
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function login()
    {
        //设置cookie
        $jar = new \GuzzleHttp\Cookie\CookieJar();
        $cookies = [
            'ASP.NET_SessionId' => $this->sessionId ,
        ];
        //cookie组成 设置域
        $cookieJar = $jar->fromArray($cookies,'jwxt.hzu.gx.cn');

        //获取隐藏__VIEWSTATE
        $stateresponse = $this->client->request('get',$this->login_uri,['cookies' => $cookieJar]);
        $viewstate = $this->parserHiddenValue($stateresponse->getBody());

        //获取验证码
        $coderesponse = $this->client->request('get',$this->code_uri,['cookies' => $cookieJar]);
        $codeBody = $coderesponse->getBody();

        //取出验证码
        $fp = fopen("verifyCode.jpg","w");

        fwrite($fp,$codeBody);
        fclose($fp);

        sleep(15);
        //获取验证码
        $code = file_get_contents("code.txt");

        //构建登录表单数据
        $loginParam = [
            '__VIEWSTATE'       => $viewstate,
            'txtUserName'       => $this->stu_id,
            'Textbox1'          => '',
            'TextBox2'          => $this->password,
            'txtSecretCode'     => $code,
            'RadioButtonList1'  => utf8('学生'),
            'Button1'           => '',
            'lbLanguage'        => '',
            'hidPdrs'           => '',
            'hidsc'             => '',

        ];

        //传入表单
        if (!empty($this->loginParam)) {
            $loginParam = $this->loginParam;
        }
        //提交登录带上cookie
        $query = [
            'form_params' => $loginParam,
            'cookies' =>   $cookieJar
        ];

        //提交登录信息
        $this->client->request('POST',$this->login_uri, $query);
        //登录成功检测登录状态?
        $response = $this->client->get($this->main_page_uri, ['allow_redirects' => false,'cookie'=> $cookieJar, 'query' => ['xh' => $this->stu_id]]);

        // 能访问首页  说明登陆成功
        switch ($response->getStatusCode()) {
            case 200:
                return $this->cacheCookie ? $cookieJar : $this;
                break;
            case 302:
                throw new \Exception('The password is wrong!', 1);
                break;
            default:
                throw new \Exception('Maybe the data source is broken!', 1);
                break;
        }
    }

    /**
     * By Concurrent requests, to get all the data.
     *
     * @author 勇敢的小笨羊
     * @return array
     * @throws \Exception
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
        //$results = Promise\unwrap($requests);

        // Wait for the requests to complete, even if some of them fail
        $results = Promise\settle($requests)->wait();

        //Parser the data we need.
        $schedule = $this->parserSchedule($results['schedule']->getBody());
        $cet = $this->parserCommonTable($results['cet']->getBody());
        $exam = $this->parserCommonTable($results['exam']->getBody());

        return compact('schedule', 'cet', 'exam');
    }

    /**
     * Get the grade data. This function is request all of grade.
     *
     * @author 勇敢的小笨羊
     * @return array
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
        $post['ddlXN'] = '';  //学年
        $post['ddlXQ'] = '';  //学期
        $post['ddl_kcxz'] = '';
        $post['btn_zcj'] = utf8('历年成绩');

        $response = $this->buildPostRequest(self::ZF_GRADE_URI, [], $post, $this->headers);

        return $this->parserCommonTable($response->getBody(), '#Datagrid1');
    }

    /**
     * Get the schedule data
     *
     * @author 勇敢的小笨羊
     * @return Traits\Array
     */
    public function getSchedule()
    {
        /**
         * Default: get the current term schedule data by GET
         * If you want to get the other term's data, use POST
         * TODO: use POST to get other term's data
         */
        $param['gnmkdm'] = 'N121603';
        $param['xm'] = utf8('许祖兴');
        $response = $this->buildGetRequest(self::ZF_SCHEDULE_URI, $param, $this->headers);
        return $this->parserSchedule($response->getBody());
    }

    /**
     * Get the CET data.
     *
     * @author 勇敢的小笨羊
     * @return array
     */
    public function getCet()
    {
        $response = $this->buildGetRequest(self::ZF_CET_URI);
        return $this->parserCommonTable($response->getBody());
    }

    /**
     * Get the default term exam data by GET.
     *
     * @author 勇敢的小笨羊
     * @return array
     */
    public function getExam()
    {
        $response = $this->buildGetRequest(self::ZF_EXAM_URI);
        return $this->parserCommonTable($response->getBody());
    }

    /**
     *  Get cookie from cache or login.
     *
     * @author 勇敢的小笨羊
     * @param bool $forceRefresh
     * @return \GuzzleHttp\Cookie\CookieJar|Lcrawl|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getCookie($forceRefresh = false)
    {
        $cacheKey = $this->cachePrefix . $this->stu_id;
        $cached = $this->getCache()->fetch($cacheKey);
        if ($forceRefresh || empty($cached)) {
            //登录获取cookie
            $cookiejar = $this->login();
            //储存cookie 到缓存
            $this->getCache()->save($cacheKey, serialize($cookiejar), 300);
            return $cookiejar;
        }
        return unserialize($cached);
    }

    /**
     * Set the cache manager.
     *
     * @param Cache $cache
     * @return $this
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
     * Get base_uri sessionId
     *
     * @author 勇敢的小笨羊
     * @param $base_uri
     * @return mixed
     */
    public function getSessionId($base_uri)
    {
        $curl   = curl_init();
        curl_setopt($curl, CURLOPT_URL, $base_uri);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_HEADER, 1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($curl);
        curl_close($curl);
        //匹配跳转链接
        preg_match('/Location: \/\((.*)\)/', $data,$temp);
        //Set SessionId
        return $temp[1];
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param Client $client
     */
    public function setClient($client)
    {
        $this->client = $client;
    }

    /**
     * @return string
     */
    public function getBaseUri()
    {
        return $this->base_uri;
    }

    /**
     * @param string $base_uri
     */
    public function setBaseUri($base_uri)
    {
        $this->base_uri = $base_uri;
    }

    /**
     * @return string
     */
    public function getLoginUri()
    {
        return $this->login_uri;
    }

    /**
     * @param string $login_uri
     */
    public function setLoginUri($login_uri)
    {
        $this->login_uri = $login_uri;
    }

    /**
     * @return string
     */
    public function getCodeUri()
    {
        return $this->code_uri;
    }

    /**
     * @param string $code_uri
     */
    public function setCodeUri($code_uri)
    {
        $this->code_uri = $code_uri;
    }

    /**
     * @return string
     */
    public function getMainPageUri()
    {
        return $this->main_page_uri;
    }

    /**
     * @param string $main_page_uri
     */
    public function setMainPageUri($main_page_uri)
    {
        $this->main_page_uri = $main_page_uri;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param array $headers
     */
    public function setHeaders($headers)
    {
        $this->headers = $headers;
    }

    /**
     * @return mixed
     */
    public function getStuId()
    {
        return $this->stu_id;
    }

    /**
     * @param mixed $stu_id
     */
    public function setStuId($stu_id)
    {
        $this->stu_id = $stu_id;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param mixed $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @return bool
     */
    public function isCacheCookie()
    {
        return $this->cacheCookie;
    }

    /**
     * @param bool $cacheCookie
     */
    public function setCacheCookie($cacheCookie)
    {
        $this->cacheCookie = $cacheCookie;
    }

    /**
     * @return string
     */
    public function getCachePrefix()
    {
        return $this->cachePrefix;
    }

    /**
     * @param string $cachePrefix
     */
    public function setCachePrefix($cachePrefix)
    {
        $this->cachePrefix = $cachePrefix;
    }

    /**
     * @return array
     */
    public function getLoginParam()
    {
        return $this->loginParam;
    }

    /**
     * @param array $loginParam
     */
    public function setLoginParam($loginParam)
    {
        $this->loginParam = $loginParam;
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

/**
 * gb2312() 函数用来获取转化字符编码gb2312
 */
function gb2312($sArg=''){
    $string=iconv('gb2312', 'utf-8', $sArg);
    return $string;
}

/**
 * utf8() 函数用来获取转化字符编码utf8
 */
function utf8($sArg=''){
    $string=iconv('utf-8', 'gb2312', $sArg);
    return $string;
}