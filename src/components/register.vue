<template>
  <div class="login_box" >
    <alert :alert_open="alert_open" :alert_msg="alert_msg" @closeAlert='alert_open=false'></alert>
   <div style="padding-top:1%;">
  <h1 style='color:#7e57c2'>秋名山-注册</h1>
    <div style="
    height: 200px;  
    ">
        <img src="/static/assets/logo.png" width="140px;" style="margin-top: 30px;">    
    
    <div>
      <mu-text-field label="输入邮箱" type="email"  labelFloat v-model="my_email" @keyup.native.enter="register()"/>
      <br>
      <mu-text-field label="输入昵称" type="text" labelFloat v-model="my_nickname" @keyup.native.enter="register()"/>
      <br>
      <mu-text-field label="输入密码" type="password"  labelFloat v-model="my_password" @keyup.native.enter="register()"/>
      <br>
      <mu-text-field label="确认密码" type="password"  labelFloat v-model="my_rpassword" @keyup.native.enter="register()"/>
      <br>
      <mu-raised-button label="注册" class="demo-raised-button" primary @click="register()"/>
      <br>
      <a style="
      margin-top: 10px;
        display: inline-block;
        cursor: pointer;
      " @click="login()">登录</a>
    </div>

    </div>
  </div>

  </div>
</template>
<script>
import { Toast } from "mint-ui";
import Alert from './alert';
export default {
  name: "register",
  components:{
      Alert
  },
  data () {
    return {
      my_rpassword:'',
      my_email:'',
      my_nickname:'',
      my_password:'',
      alert_msg:'',
      alert_open:false,
    }
  },
  methods: {
    login(){
      this.$emit('login')
    },
    alertMsg(msg){
      this.alert_open=true;
      this.alert_msg=msg
    },
    registerHandle(){
      var sendData = {
        email: this.my_email,
        password: this.my_password,
        rpassword: this.my_rpassword,
        nickname: this.my_nickname
      };

      this.$axios
        .post("/user/register", this.$qs.stringify(sendData))
        .then(response => {
          console.log(response.data);
          if (response.data.err_no > 0) {
            this.alertMsg(response.data.err_msg);
          } else if (response.data.err_no == 0) {
            // 注册成功
            this.alertMsg('注册成功，正在前往登录');
            setTimeout(this.login,1500)
          } else {
          }
        })
        .catch(error => {
            this.alertMsg('未知错误');          
        });
    },
    register(){
      var len = this.my_nickname.replace(/[\u0391-\uFFE5]/g, "aa").length;
      console.log(len);
      if (len > 0 && len <= 14) {
        this.registerHandle();
      } else {
        // 昵称太长
        this.alertMsg("昵称太长，不要超过4个汉字或者8个英文字符");
      }
    }
  }
};
</script>

<style scoped>
.login_box {
  text-align: center;
  width: 100%;
  margin: 0;
  z-index: 99999;
  height: 100%;
}
</style>

