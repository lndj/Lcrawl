<?php

/**
 * This a lib to crawl the Academic Network Systems.
 * You can easely achieve the querying of grade/schedule/cet/free classroom ...
 * 
 * @author Ning Luo <luoning@Luoning.me>
 * @link https://github.com/lndj/Lcrawl
 * @license  MIT
 */ 

namespace Lndj;

use Symfony\Component\DomCrawler\Crawler;

/**
 * This is trait to parser data from HTML.
 */
trait  Parser {
    /**
     * Paser the schedule data.
     * @param Object $body 
     * @return Array
     */
    public function parserSchedule($body)
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

    /**
     * Parser the common table, like grade, cet, chooseClass, etc.
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
     * @param type $body 
     * @return type
     */
    public function parserHiddenValue($body)
    {
        $crawler = new Crawler((string)$body);
        return $crawler->filterXPath('//*[@id="form1"]/input')->attr('value');
    }
}