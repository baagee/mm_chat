<template>
<div class="login_box" >
    <alert :alert_open="alert_open" :alert_msg="alert_msg" @closeAlert='alert_open=false'></alert>
<div style="padding-top:8%;">
  <h1 style='color:#7e57c2'>秋名山-登录</h1>
  <div style="
  height: 200px;  
  ">
  <!-- logo -->
        <img src="/static/assets/logo.png" width="140px;" style="margin-top: 30px;">
  </div>
</div>

<div>
  <mu-text-field label="输入邮箱" type="email"   labelFloat v-model="my_email" @keyup.native.enter="login()"/>
  <br>
  <mu-text-field label="输入密码" type="password" labelFloat v-model="my_password" @keyup.native.enter="login()"/>
  <br>
  <mu-raised-button label="登录" class="demo-raised-button" primary @click="login()"/>
  <br>
  <a style="
  margin-top: 10px;
        display: inline-block;  
    cursor: pointer;
  " @click="register()">注册</a>
</div>
</div>
</template>
<script>
import Alert from './alert';
export default {
  name: "login",
  components:{
      Alert
  },
  data() {
    return {
      my_email: "",
      alert_open:false,
      alert_msg:'',
      my_password:''
    };
  },
  methods: {
    login() {
      var sendData = {
        email: this.my_email,
        password: this.my_password
      };

      this.$axios
        .post("/user/login", this.$qs.stringify(sendData))
        .then(response => {
          console.log(response.data);
          if (response.data.err_no > 0) {
            this.alertMsg(response.data.err_msg);
          } else if (response.data.err_no == 0) {
            // 登录成功set_myself_info
            this.$store.commit('set_is_login',true)
            this.$store.commit('open_socket',response.data.data)
          } else {
          }
        })
        .catch(error => {
            this.alertMsg('未知错误');          
        });
    },
    alertMsg(msg){
      this.alert_open=true;
      this.alert_msg=msg
    },
    register(){
      this.$emit('register')
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

