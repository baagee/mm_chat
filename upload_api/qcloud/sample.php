<?php
/**
 * Created by PhpStorm.
 * User: dangliuhui
 * Date: 2018/3/4
 * Time: 下午12:23
 */

include './cos/Api.php';
use qcloud\cos\Api;

//bucketname
$bucket = 'chat-room';
//uploadlocalpath
$src = './hello.txt';
//cospath
$dst = '/testfolder/hello.txt';
//downloadlocalpath
$dst2 = 'hello2.txt';
//cosfolderpath
$folder = '/testfolder';
//config your information
$config = array(
    'app_id' => '1256151484',
    'secret_id' => 'AKID6MYZQQM9cy98oPx9Vfd7f6LQM7g8Lots',
    'secret_key' => 'whzMbiiWAwcBaBqMgHm5sY8iD1CMQJ0Y',
    'region' => 'bj',   // bucket所属地域：华北 'tj' 华东 'sh' 华南 'gz'
    'timeout' => 60
);

date_default_timezone_set('PRC');
$cosApi = new Api($config);

// Create folder in bucket.
$ret = $cosApi->createFolder($bucket, $folder);
var_dump($ret);

// Upload file into bucket.
$ret = $cosApi->upload($bucket, $src, $dst);
var_dump($ret);

// // Download file
// $ret = $cosApi->download($bucket, $dst, $dst2);
// var_dump($ret);
// unlink($dst2);

// // List folder.
// $ret = $cosApi->listFolder($bucket, $folder);
// var_dump($ret);

// // Update folder information.
// $bizAttr = "";
// $ret = $cosApi->updateFolder($bucket, $folder, $bizAttr);
// var_dump($ret);

// // Update file information.
// $bizAttr = '';
// $authority = 'eWPrivateRPublic';
// $customerHeaders = array(
//     'Cache-Control' => 'no',
//     'Content-Type' => 'application/pdf',
//     'Content-Language' => 'ch',
// );
// $ret = $cosApi->update($bucket, $dst, $bizAttr, $authority, $customerHeaders);
// var_dump($ret);

// // Stat folder.
// $ret = $cosApi->statFolder($bucket, $folder);
// var_dump($ret);

// // Stat file.
// $ret = $cosApi->stat($bucket, $dst);
// var_dump($ret);

// // Copy file.
// $ret = $cosApi->copyFile($bucket, $dst, $dst . '_copy');
// var_dump($ret);

// // Move file.
// $ret = $cosApi->moveFile($bucket, $dst, $dst . '_move');
// var_dump($ret);

// // Delete file.
// $ret = $cosApi->delFile($bucket, $dst . '_copy');
// var_dump($ret);
// $ret = $cosApi->delFile($bucket, $dst . '_move');
// var_dump($ret);

// // Delete folder.
// $ret = $cosApi->delFolder($bucket, $folder);
// var_dump($ret);