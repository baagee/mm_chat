<?php
include __DIR__.'/qcloud/cos/Api.php';
use qcloud\cos\Api;

class Base64ImageUpload{

	private $saveImagePath='';


	public function upload($upload_dir,$bucket,$qc_config){
		$src = $this->saveImage();
        $day=date('Y-m-d');
        $folder = '/'.$upload_dir.'/'.$day;
        $dst = '/'.$upload_dir.'/'.$day.'/'.basename($src);
        $cosApi = new Api($qc_config);
        $ret = $cosApi->createFolder($bucket, $folder);
        if($ret['code']!==0 && $ret['message']!=='SUCCESS'){
            throw new Exception("腾讯云创建文件夹失败");
        }
        $ret = $cosApi->upload($bucket, $src, $dst);
        if($ret['code']!==0 && $ret['message']!=='SUCCESS'){
            throw new Exception("上传文件到腾讯云失败");
        }
        unlink($src);
        return $ret['data']['source_url'];
	}

	public function saveImage(){
		$base64_img = trim($_POST['img']);
        $up_dir = '/tmp/';
        if(preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_img, $result)){
            $type = $result[2];
            if(in_array($type,array('pjpeg','jpeg','jpg','gif','bmp','png'))){
                $new_file = $up_dir.time().mt_rand(1,100).'.'.$type;
                if(file_put_contents($new_file, base64_decode(str_replace($result[1], '', $base64_img)))){
                	return $new_file;
                }else{
                	throw new Exception("图片上传失败");
                }
            }else{
               throw new Exception('图片上传类型错误');
            }
        }else{
        	throw new Exception("文件错误");
        }
	}
}