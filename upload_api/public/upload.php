<?php
/**
 *  author: BaAGee
 *  createTime: 2018/5/14 23:15
 */

// header('Access-Control-Allow-Origin:http://localhost:8080');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
header('Content-Type:application/json; charset=utf-8');
header('Access-Control-Allow-Credentials:true');

date_default_timezone_set('PRC');

// 上传文件保存的文件夹名称
$upload_dir='images';

$qc_config = array(
    'app_id' => '',
    'secret_id' => '',
    'secret_key' => '',
    'region' => 'bj',   // bucket所属地域：华北 'tj' 华东 'sh' 华南 'gz'
    'timeout' => 60
);
/*腾讯云bucket*/
$bucket = 'chat-room';

if(empty($_POST)){
    include_once '../lib/FileUpload.php';

    // 上传文件类型，就是表单名称
    $upload_type='image';
    try{
        $fileUpload=new FileUpload($upload_dir,$upload_type,$bucket,$qc_config);
        $res=$fileUpload->upload();
        die(json_encode(['res'=>true,'img_path'=>$res],JSON_UNESCAPED_UNICODE));
    }catch(Exception $e){
        die(json_encode(['res'=>false,'err_msg'=>$e->getMessage()],JSON_UNESCAPED_UNICODE));
    }
}else{
    if(isset($_POST['submission-type']) && $_POST['submission-type']=='paste'){
        include_once '../lib/Base64ImageUpload.php';
        try{
            $baseUpload=new Base64ImageUpload();
            $res=$baseUpload->upload($upload_dir,$bucket,$qc_config);
            die(json_encode(['res'=>true,'img_path'=>$res],JSON_UNESCAPED_UNICODE));
        }catch(Exception $e){
            die(json_encode(['res'=>false,'err_msg'=>$e->getMessage()],JSON_UNESCAPED_UNICODE));
        }
    }else{
        die(json_encode(['res'=>false,'err_msg'=>'不正当访问'],JSON_UNESCAPED_UNICODE));
    }
}