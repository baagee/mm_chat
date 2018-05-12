<?php
/**
 *  author: BaAGee
 *  createTime: 2018/5/9 23:28
 */
// 本地开发测试加的

// header('Access-Control-Allow-Origin:http://localhost:8080');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
header('Content-Type:application/json; charset=utf-8');
header('Access-Control-Allow-Credentials:true');


class FileUpload{

    const PATH='./upload/';
    private $max_size=5343800;

    private $upload_type='';
    private $upload_dir='';

    private $allow_type=array(
        'image'=>['image/jpg','image/jpeg','image/png','image/pjpeg','image/gif','image/bmp','image/x-png'],
    );

    public function __construct($upload_dir,$upload_type){
        $this->upload_dir=$upload_dir;
        $this->upload_type=$upload_type;
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

    public function upload(){
    	if(is_uploaded_file($_FILES[$this->upload_type]['tmp_name'])){
    		if(!$this->checkType($_FILES[$this->upload_type]['type'])){
	        	$_FILES[$this->upload_type]['error']=6;
	        }
	        if ($_FILES[$this->upload_type]['error'] > 0) {
	            switch ($_FILES[$this->upload_type]['error']) {
	                case 1 :
	                case 2 :
	                	throw new Exception('上传文件过大');
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

	    	$this->checkPath();
	        $size=$_FILES[$this->upload_type]['size'];
	        $this->checkSize($size);
	        $tmp_name=$_FILES[$this->upload_type]['tmp_name'];
	        $ext=strchr($_FILES[$this->upload_type]['name'],'.');
	        return $this->moveFile($tmp_name,$ext);
    	}else{
    		throw new Exception("上传文件失败#2");
    	}
    }
}
ini_set('date.timezone','Asia/Shanghai');

// 上传文件保存的文件夹名称
$upload_dir='images';
// 上传文件类型，就是表单名称
$upload_type='image';
try{
	$fileUpload=new FileUpload($upload_dir,$upload_type);
	$res=$fileUpload->upload();
	die(json_encode(['res'=>true,'img_path'=>$res],JSON_UNESCAPED_UNICODE));
}catch(Exception $e){
	die(json_encode(['res'=>false,'err_msg'=>$e->getMessage()],JSON_UNESCAPED_UNICODE));
}