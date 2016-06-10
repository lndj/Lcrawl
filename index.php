<?php
/**
 * This is a test file.
 * 
 * @author    Ning Luo <luoning@luoning.me>
 * @copyright This code is copyright to me.
 * @license   MIT
 * @package   lndj/Lcrawl
 */

require 'vendor/autoload.php';

use Lndj\Lcrawl;

//stu_id
$stu_id = '201404739';
//your password 
$password = 'xxxxxx';

$user = ['stu_id' => $stu_id, 'stu_pwd' => $password];

$client = new Lcrawl('http://xuanke.lzjtu.edu.cn/', $user, false);

$client->login();

$all = $client->setUa('Lcrawl Spider V2.0.2')->getAll();
//setTimeOut()
//setReferer
//set...
dd($all);

// dd($client->login());
// $client->getSchedule();
// $client->getCet();


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