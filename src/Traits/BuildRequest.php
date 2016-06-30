<?php

/**
 * This is a lib to crawl the Academic Network Systems.
 * You can achieve easely the querying of grade/schedule/cet/free classroom ...
 * 
 * @author Ning Luo <luoning@Luoning.me>
 * @link https://github.com/lndj/Lcrawl
 * @license  MIT
 */ 

namespace Lndj\Traits;

/**
 * This is a trait to build get & post request.
 */
trait BuildRequest {

   /**
    * Build the get request.
    * 
    * @param type|string $uri 
    * @param type|array $param 
    * @param type|array $headers 
    * @param type|bool $isAsync 
    * @return type
    */
    public function buildGetRequest($uri, $param = [], $headers = [], $isAsync = false)
    {
        $query_param = array_merge(['xh' => $this->stu_id], $param);
        $query = [
            'query' => $query_param,
            'headers' => $headers,
        ];
        if ($this->cacheCookie) {
            $query['cookies'] = $this->getCookie();
        }
        //If use getAll(), use the Async request.
        return $isAsync 
        ? $this->client->getAsync($uri, $query) 
        : $this->client->get($uri, $query);
    }

    public function buildPostRequest($uri, $query, $param, $headers = [], $isAsync = false)
    {
        $query_param = array_merge(['xh' => $this->stu_id], $query);
        $post = [
            'query' => $query_param,
            'headers' => $headers,
            'form_params' => $param,
        ];

        //If opened cookie cache
        if ($this->cacheCookie) {
            $post['cookies'] = $this->getCookie();
        }

        //If use getAll(), use the Async request.
        return $isAsync 
        ? $this->client->postAsync($uri, $post) 
        : $this->client->post($uri, $post);
    }
}