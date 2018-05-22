<?php
namespace testapp\module\controller;

use extension\captcha\Captcha;

class TestController extends \core\Controller
{
    public function captcha($input)
    {
        $ca = new Captcha();
        $ca->make();
    }

    public function validate($input){
        // 示例
        $rules=[
            'field1'=>['email','邮箱格式不正确'],
            'field2'=>'optional|phone|default[17867676769]',
            'field3'=>'required|str|min[6]|max[13]',
            'field4'=>['required|int|min[18]','年龄不大于18岁'],
            'field5'=>'optional|url|default[http://baagee.vip]',
            'json'=>'json',
            'pass1'=>['str|min[6]|max[13]|equal['.$input['pass2'].']','pass1 pass2两次密码不一样'],
            'sex'=>'optional|int|enum[1,2]',
            'status'=>'int|min[2]|enum[2,3,4]',
            'bankid'=>'number|bankid'
        ];
        $data=$this->validator->validate($input,$rules);

        $this->validator->specify(function() use($input){
            return $input['spe']=='qaz';
        },'自定义验证函数');
        // return json_decode($data['json'],true);
        return ['password'=>'asdgsdf789243789'];
    }

	public function hello($input){
		echo 'hello world';
	}
}
