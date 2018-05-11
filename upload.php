<?php
/**
 *  author: BaAGee
 *  createTime: 2018/5/9 23:28
 */
header('Access-Control-Allow-Origin:http://localhost:8080');
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
            return false;
        }
        return true;
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
            return false;
        }
        return $new_file;
    }

    public function upload(){
    	if(!$this->checkType($_FILES[$this->upload_type]['type'])){
        	$_FILES[$this->upload_type]['error']=6;
        }
        if ($_FILES[$this->upload_type]['error'] > 0) {
            switch ($_FILES[$this->upload_type]['error']) {
                case 1 :
                case 2 :
                	return ['res'=>false,'err_msg'=>'上传文件过大，max='.ini_get('upload_max_filesize')];
                case 3 :
	                return ['res'=>false,'err_msg'=>'上传文件丢失'];
                case 4 :
	                return ['res'=>false,'err_msg'=>'无文件被上传'];
                case 6 :
	                return ['res'=>false,'err_msg'=>'文件格式不正确'];
                case 7 :
	                return ['res'=>false,'err_msg'=>'上传文件储存失败'];
            }
        }

    	$this->checkPath();
        $size=$_FILES[$this->upload_type]['size'];
        $tmp_name=$_FILES[$this->upload_type]['tmp_name'];
        $ext=strchr($_FILES[$this->upload_type]['name'],'.');
        if(!$this->checkSize($size)){
            return ['res'=>false,'err_msg'=>'文件不能超过'.$this->max_size.'Kb'];
        }else{
        	$res=$this->moveFile($tmp_name,$ext);
            if($res===false){
                return ['res'=>false,'err_msg'=>'文件上传失败'];
            }else{
                return ['res'=>true,'img_path'=>$res];
            }
        }
    }

}

$upload_dir='images';
$upload_type='image';
try{
	$fileUpload=new FileUpload($upload_dir,$upload_type);
	$res=$fileUpload->upload();
	die(json_encode($res,JSON_UNESCAPED_UNICODE));
}catch(Exception $e){
	die(json_encode(['res'=>false,'err_msg'=>$e->getMessage()],JSON_UNESCAPED_UNICODE));
}