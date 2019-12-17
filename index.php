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

$user = [
    'stu_id'  => '1610612057',
    'stu_pwd' => 'xzx595...'
];
$client = new Lcrawl('http://jwxt.hzu.gx.cn/',$user, true);
//获取所有
$data = $client->getSchedule();
dd($data);

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