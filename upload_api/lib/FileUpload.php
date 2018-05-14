<?php
include __DIR__.'/qcloud/cos/Api.php';
use qcloud\cos\Api;

class FileUpload{

    const PATH='../upload/';
    private $max_size=5343800;

    private $upload_type='';
    private $upload_dir='';
    private $qcloud_conf=[];

    private $allow_type=array(
        'image'=>['image/jpg','image/jpeg','image/png','image/pjpeg','image/gif','image/bmp','image/x-png'],
    );

    public function __construct($upload_dir,$upload_type,$bucket,$qcloud_conf){
        $this->upload_dir=$upload_dir;
        $this->upload_type=$upload_type;
        $this->bucket=$bucket;
        $this->qcloud_conf=$qcloud_conf;
    }

    public function checkPath(){
        if (!file_exists(self::PATH.$this->upload_dir)) {
            mkdir(self::PATH.$this->upload_dir, 0777);
        }
    }

    public function checkSize($fileSize){
        if($fileSize>$this->max_size){
        	throw new Exception('文件不能超过'.$this->max_size.'Kb');
        }
    }

    public function checkType($type){
        if(!in_array($type,$this->allow_type[$this->upload_type])){
            return false;
        }
        return true;
    }

    public function moveFile($tmp_file,$ext){
        $new_file=self::PATH.$this->upload_dir.'/'.time().mt_rand().$ext;
        if(!move_uploaded_file($tmp_file,$new_file)){
        	throw new Exception("上传文件失败#1");
        }
        return $new_file;
    }

    public function upload2Qcloud($tmp_file,$ext){
        // $new_file=self::PATH.$this->upload_dir.'/'.time().mt_rand().$ext;
        
        $src = $tmp_file;
        $day=date('Y-m-d');
        $folder = '/'.$this->upload_dir.'/'.$day;
        $dst = '/'.$this->upload_dir.'/'.$day.'/'.time().mt_rand().$ext;

        date_default_timezone_set('PRC');
        $cosApi = new Api($this->qcloud_conf);
        $ret = $cosApi->createFolder($this->bucket, $folder);
        if($ret['code']!==0 && $ret['message']!=='SUCCESS'){
            throw new Exception("腾讯云创建文件夹失败");
        }

        $ret = $cosApi->upload($this->bucket, $src, $dst);
        if($ret['code']!==0 && $ret['message']!=='SUCCESS'){
            throw new Exception("上传文件到腾讯云失败");
        }
        return $ret['data']['source_url'];
    }

    public function upload(){
    	if(is_uploaded_file($_FILES[$this->upload_type]['tmp_name'])){
    		if(!$this->checkType($_FILES[$this->upload_type]['type'])){
	        	$_FILES[$this->upload_type]['error']=6;
	        }
	        if ($_FILES[$this->upload_type]['error'] > 0) {
	            switch ($_FILES[$this->upload_type]['error']) {
	                case 1 :
	                case 2 :
	                	throw new Exception('上传文件过大，max='.ini_get('upload_max_filesize'));
	                case 3 :
	                	throw new Exception("上传文件丢失");
	                case 4 :
	                	throw new Exception("无文件被上传");
	                case 6 :
	                	throw new Exception("文件格式不正确");
	                case 7 :
	                	throw new Exception("上传文件储存失败");
	            }
	        }

	    	// $this->checkPath();
	        $size=$_FILES[$this->upload_type]['size'];
	        $this->checkSize($size);
	        $tmp_name=$_FILES[$this->upload_type]['tmp_name'];
	        $ext=strchr($_FILES[$this->upload_type]['name'],'.');
            // return $this->moveFile($tmp_name,$ext);
	        return $this->upload2Qcloud($tmp_name,$ext);
    	}else{
    		throw new Exception("上传文件失败#2");
    	}
    }
}