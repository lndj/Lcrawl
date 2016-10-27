<?php

/**
 * This is a lib to crawl the Academic Network Systems.
 * You can easely achieve the querying of grade/schedule/cet/free classroom ...
 * 
 * @author Ning Luo <luoning@Luoning.me>
 * @link https://github.com/lndj/Lcrawl
 * @license  MIT
 */ 

namespace Lndj\Traits;

use Symfony\Component\DomCrawler\Crawler;

/**
 * This is trait to parser data from HTML.
 */
trait  Parser {
    /**
     * Paser the schedule data.
     * 
     * @param Object $body 
     * @return Array
     */
    public function parserSchedule($body)
    {
        $crawler = new Crawler((string)$body);
        $crawler = $crawler->filter('#Table1');
        $page = $crawler->children();
        //delete line 1、2;
        $page = $page->reduce(function (Crawler $node, $i) {
            if ($i == 0 || $i == 1) {
                return false;
            }
        });
        //to array
        $array = $page->each(function (Crawler $node, $i) {
            return $node->children()->each(function (Crawler $node, $j) {
                $span = $node->attr('rowspan');
                return $node->html() . (empty($span) ? '' : "span={$span}");
            });
        });
        /**
         * 处理跨行
         * 遍历数组，如果[j][i]元素跨行，在【j+1】行的【i】位置插入该元素
         * 注意
         * 遍历的顺序是竖直方向(a->e->h->j)；
         * 如果横向遍历，会出现如下情况
         * =========
         *  a b c d
         *  e f g _
         *  h i _ _  
         *  j k l m
         * =========
         * _表示没有元素，是上一行的跨行。
         * 横向遍历时（a->b->c->d），d的跨行会导致第三行还没插入g元素时就已插入d元素
         */
        for ($i = 0; $i < 9; $i++) {//rows
            for ($j = 0; $j < count($array); $j++) {//lines
                if (preg_match("/span=(.?)/", $array[$j][$i], $regs)) { // if rowspan
                    $array[$j][$i] = preg_replace("/span=./", '', $array[$j][$i]);
                    $k = $regs[1];
                    while (--$k > 0) { // insert element to next line
                        $array[$j + $k] = array_merge(array_slice($array[$j + $k], 0, $i),
                            array(preg_replace("/span=./", '', $array[$j][$i])),
                            array_splice($array[$j + $k], $i));
                    }
                }
            }
        }
        return  $array;
    }

    /**
     * Parser the common table, like grade, cet, chooseClass, etc.
     * 
     * @param type|Object $body 
     * @param type|string $selector 
     * @return array
     */
    public function parserCommonTable($body, $selector = '#DataGrid1')
    {
        $crawler = new Crawler((string)$body);
        
        $crawler = $crawler->filter($selector);
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

    /**
     * Parser the hidden value of HTML form.
     * 
     * @param type $body 
     * @return type
     */
    public function parserHiddenValue($body)
    {
        $crawler = new Crawler((string)$body);
        return $crawler->filterXPath('//*[@id="form1"]/input')->attr('value');
    }
}