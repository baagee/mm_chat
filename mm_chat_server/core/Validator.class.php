<?php
/**
 * Created by PhpStorm.
 * User: dangliuhui
 * Date: 2018/1/26
 * Time: 下午6:03
 */

namespace core;

class Validator
{

    /**
     * 批量验证
     * @param array $data 要验证的数据
     * @param array $rules 验证规则
        $rules=[
            'field1'=>['email','邮箱格式不正确'],
            'field2'=>'optional|phone|default[17867676769]',
            'field3'=>'required|str|min[6]|max[13]',
            'field4'=>['required|int|min[18]','年龄不大于18岁'],
            'field5'=>'optional|url|default[http://baagee.vip]',
            'json'=>'json',
            'pass1'=>['str|min[6]|max[13]|equal['.$input['pass2'].']','pass1 pass2两次密码不一样'],
            'sex'=>'optional|int|min[1]',
            'status'=>'int|min[2]|enum[2,3,4]',
        ];
     * @return array|mixed 失败时抛出异常或者成功时返回值数组
     * @throws \Exception
     */
    public function validate($data, $rules)
    {
        foreach ($rules as $key => $item) {
            $err_msg='参数 ['.$key.'] 验证失败';
            if(is_array($item)){
                if(!empty($item[1])){
                    $err_msg=$item[1];
                }
                $arr_tmp=explode('|',$item[0]);
            }else{
                $arr_tmp=explode('|',$item);
            }
            $optional=false;
            if(in_array('optional',$arr_tmp)){
                // 可选参数
                $optional=true;
            }else{
                // 参数必须
                if(!isset($data[$key]) || $data[$key]==''){
                    throw new \Exception($err_msg,CoreErrorCode::SYSTEM_VALIDATE_FAILED);
                }
            }
//            var_dump($key);
            $tmp_rules=[];
            foreach($arr_tmp as $rule){
                if(preg_match('/(.*?)\[(.*)\]/', $rule, $match)){
                    if(strtolower($match[1])==='default' && (!isset($data[$key]) || empty($data[$key]))){
                        $data[$key]=$match[2];// 设置默认值
                    }elseif(in_array($match[1],['enum','equal'])){
                        $tmp_rules['functions'][]=$match[1].'Validator';
                        $tmp_rules['val2']=$match[2];
                    }else{
                        $tmp_rules[$match[1]]=$match[2];
                    }
                }else{
                    if(!in_array($rule,['required','optional'])){
                        $tmp_rules['functions'][]=$rule.'Validator';
                    }
                }
            }

//            echo '-----------------'.$key.PHP_EOL;
            if(isset($tmp_rules['functions']) && !empty($tmp_rules['functions'])){
                foreach($tmp_rules['functions'] as $function){
                    if(($optional && $data[$key]!='') || !$optional){ //如果是【可选参数并且值不为空】或者【必选参数】就验证
                        if(method_exists($this,$function)){
                            unset($tmp_rules['functions']);
    //                        var_dump($tmp_rules);
    //                        var_dump($function);
                            extract($tmp_rules);
                            if(in_array($function,['strValidator','numberValidator','chineseValidator','intValidator'])){
    //                                判断数字或者字符串长度
    //                            var_dump($function,$min??1,$max??PHP_INT_MAX,$data[$key]);
                                $res=$this->$function($min??1,$max??PHP_INT_MAX,$data[$key]);
                                if(!$res){
                                    throw new \Exception($err_msg,CoreErrorCode::SYSTEM_VALIDATE_FAILED);
                                }
                            }elseif(in_array($function,['equalValidator','enumValidator'])){
    //                            var_dump($function,$data[$key],$val2);
                                $res=$this->$function($data[$key],$val2);
                                if(!$res){
                                    throw new \Exception($err_msg,CoreErrorCode::SYSTEM_VALIDATE_FAILED);
                                }
                            }else{
    //                                其他情况下验证
//                                var_dump($function,$data[$key]);
                                if(!$this->$function($data[$key])){
                                    throw new \Exception($err_msg,CoreErrorCode::SYSTEM_VALIDATE_FAILED);
                                }
                            }
                            unset($min);
                            unset($max);
                            unset($val2);
                        }else{
                            throw new \Exception('['.str_replace('Validator','',$function).'] 验证方法不存在',CoreErrorCode::SYSTEM_VALIDATOR_NOT_EXISTS);
                        }
                    }
                }
            }
        }
        return $data;
    }

    /**
     * 判断两个值是否相等
     * @param string $val1
     * @param string $val2
     * @return bool
     */
    public function equalValidator($val1,$val2)
    {
        return $val1===$val2;
    }

    /**
     * 检查val1是否在val2中
     * @param string $val1
     * @param string|array $val2
     * @return bool
     */
    public function enumValidator($val1,$val2)
    {
        if(is_array($val2)){
            return in_array($val1,$val2);
        }else{
            return in_array($val1,explode(',',$val2));
        }
    }

    /**
     * 验证是否为指定长度的字母/数字组合
     * @param int    $minLength 最小长度
     * @param int    $maxLength 最大长度
     * @param string $str       要验证的字符串
     * @return mixed
     */
    public function strValidator($minLength, $maxLength, $str)
    {
        if($maxLength===PHP_INT_MAX){
            $zz="/^[a-zA-Z0-9]{" . $minLength . ",}$/";
        }else{
            $zz="/^[a-zA-Z0-9]{" . $minLength . "," . $maxLength . "}$/";
        }
        if (!preg_match($zz, $str)) {
            return false;
        }
        return true;
    }

    /**
     * 验证是否为指定长度数字
     * @param int    $minLength 最小长度
     * @param int    $maxLength 最大长度
     * @param int    $number    要验证的数值
     * @return mixed
     */
    public function numberValidator($minLength, $maxLength, $number)
    {
        if($maxLength===PHP_INT_MAX){
            $zz="/^[0-9]{" . $minLength . ",}$/";
        }else{
            $zz="/^[0-9]{" . $minLength . "," . $maxLength . "}$/";
        }
        if (!preg_match($zz, $number)) {
            return false;
        }
        return true;
    }

    /**
     * 验证数值范围
     * @param int $min 最小值
     * @param int $max 最大值
     * @param int $int 数字
     * @return bool
     */
    public function intValidator($min,$max,$int)
    {
        $int=(int)$int;
        if($int>=(int)$min && $int<(int)$max){
            return true;
        }
        return false;
    }

    /**
     * 验证是否为指定长度汉字
     * @param int    $minLength 最小长度
     * @param int    $maxLength 最大长度
     * @param string $str       要验证的字符串
     * @return mixed
     */
    public function chineseValidator($minLength, $maxLength, $str)
    {
        if($maxLength===PHP_INT_MAX){
            $zz="/^([\x81-\xfe][\x40-\xfe]){" . $minLength . ",}$/";
        }else{
            $zz="/^([\x81-\xfe][\x40-\xfe]){" . $minLength . "," . $maxLength . "}$/";
        }
        if (!preg_match($zz, $str)) {
            return false;
        }
        return true;
    }

    /**
     * 验证身份证号码
     * @param string $id  身份证号
     * @return mixed
     */
    public function IDCardValidator($id)
    {
        if (!preg_match('/(^([\d]{15}|[\d]{18}|[\d]{17}x)$)/i', $id)) {
            return false;
        }
        return true;
    }

    /**
     * 验证邮件地址
     * @param string $email 邮箱
     * @return mixed
     */
    public function emailValidator($email)
    {
        if (!preg_match('/^[_\.0-9a-z-]+@([0-9a-z][0-9a-z-]+\.)+[a-z]{2,4}$/', $email)) {
            return false;
        }
        return true;
    }

    /**
     * 验证电话号码
     * @param  int   $phone 手机号
     * @return mixed
     */
    public function phoneValidator($phone)
    {
        if (!preg_match("/^0?(13|14|15|17|18)[0-9]{9}$/", $phone)) {
            return false;
        }
        return true;
    }

    /**
     * 验证邮编
     * @param  int   $zip 邮编
     * @return array
     */
    public function zipValidator($zip)
    {
        if (!preg_match("/^[1-9]\d{5}$/", $zip)) {
            return false;
        }
        return true;
    }

    /**
     * 验证url地址
     * @param string $url url
     * @return mixed
     */
    public function urlValidator($url)
    {
        if (!preg_match("/^http[s]?:\/\/[A-Za-z0-9]+\.[A-Za-z0-9]+[\/=\?%\-&_~`@[\]\':+!]*([^<>\"\"])*$/", $url)) {
            return false;
        }
        return true;
    }

    /**
     * 检查输入IP是否符合要求
     * @param string $ip  ip
     * @return mixed
     */
    public function ipValidator($ip)
    {
        if (!(bool)ip2long($ip)) {
            return false;
        }
        return true;
    }

    /**
     * 判断输入的日期是否符合2017-11-15
     * @param  string $date 日期
     * @return array
     */
    public function dateValidator($date)
    {
        $dateArr = explode("-", $date);
        if (is_numeric($dateArr[0]) && is_numeric($dateArr[1]) && is_numeric($dateArr[2])) {
            if (($dateArr[0] >= 1000 && $dateArr[0] <= 10000) && ($dateArr[1] >= 0 && $dateArr[1] <= 12) && ($dateArr[2] >= 0 && $dateArr[2] <= 31)) {

            } else {
                return false;
            }
        }
        return true;
    }

    /**
     * 检查日期是否符合0000-00-00 00:00:00
     * @param string $dateTime 时间日期
     * @return mixed
     */
    public function timeValidator($dateTime)
    {
        $dateTimeArr = explode(' ', $dateTime);
        $dateArr = explode("-", $dateTimeArr[0]);
        if (is_numeric($dateArr[0]) && is_numeric($dateArr[1]) && is_numeric($dateArr[2])) {
            if (($dateArr[0] >= 1000 && $dateArr[0] <= 10000) && ($dateArr[1] >= 0 && $dateArr[1] <= 12) && ($dateArr[2] >= 0 && $dateArr[2] <= 31)) {
            } else {
                return false;
            }
        }
        $timeArr = explode(":", $dateTimeArr[1]);
        if (is_numeric($timeArr[0]) && is_numeric($timeArr[1]) && is_numeric($timeArr[2])) {
            if (($timeArr[0] >= 0 && $timeArr[0] <= 23) && ($timeArr[1] >= 0 && $timeArr[1] <= 59) && ($timeArr[2] >= 0 && $timeArr[2] <= 59)) {
            } else {
                return false;
            }
        }
        return true;
    }

    /**
     * 判断是否为json字符串
     * @param string $json_str json字符串
     * @return bool
     */
    public function jsonValidator($json_str)
    {
        return !is_null(json_decode($json_str));
    }

    /**
     * 验证银行卡号
     * @param string $cardNo 银行卡号
     * @return array
     */
    public function bankIdValidator($cardNo)
    {
        if (!preg_match("/^\d{16,19}$/", $cardNo)) {
            return false;
        }
        return true;
    }

    /**
     * 自定义验证方法
     * @param callable $fun 验证方法
     * @throws \Exception
     */
    public function specify($fun,$err)
    {
        if($fun($err)===false){
            throw new \Exception($err,CoreErrorCode::SYSTEM_VALIDATE_FAILED);
        }
    }
}
