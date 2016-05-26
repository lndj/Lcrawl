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
$stu_id = '201201148';
//your password 
$password = 'luowei2008';

$user = ['stu_id' => $stu_id, 'stu_pwd' => $password];

$client = new Lcrawl('http://xuanke.lzjtu.edu.cn/', $user, true);

// echo sys_get_temp_dir();

// dd($client->login());
// $client->getSchedule();
// $client->getCet();
dd($client->getAll());

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