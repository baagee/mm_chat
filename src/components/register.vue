<template>
  <div class="login_box" >
    <!-- <alert :alert_open="alert_open" :alert_msg="alert_msg" @closeAlert='alert_open=false'></alert> -->
   <div style="padding-top:1%;">
  <h1 style='color:#7e57c2'>秋名山-注册</h1>
    <div style="
    height: 200px;  
    ">
    <input type="file" style="
    position: absolute;
    z-index: 100;
    cursor: pointer;
    font-size: 0px;
    opacity: 0; 
    left:45%;
    width: 140px;
    height: 140px;
    top: 140px;"  @change="uploadImage" accept="image/jpeg,image/jpg,image/png">
    <p style="color:#a7a6a6;font-size:16px">选择头像</p>
        <img :src="'/static/assets/avatar/1 ('+avatar_id+').jpg'" width="140px;" style="border-radius:50%;cursor:pointer;border: 1px solid #ccc;">

    <div>
      <mu-text-field label="输入邮箱" type="email" :errorText="email_error" labelFloat v-model="my_emailname" @keyup.native.enter="register()"/>
      <br>
      <mu-text-field label="输入昵称" type="email" :errorText="nickname_error" labelFloat v-model="my_nickname" @keyup.native.enter="register()"/>
      <br>
      <mu-text-field label="输入密码" type="password" :errorText="password_error" labelFloat v-model="my_password" @keyup.native.enter="register()"/>
      <br>
      <mu-text-field label="确认密码" type="password" :errorText="rpassword_error" labelFloat v-model="my_rpassword" @keyup.native.enter="register()"/>
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
      avatar_id:'',
      email_error:'',
      password_error:'',
      rpassword_error:'',
      nickname_error:'',
      my_rpassword:'',
      my_emailname:'',
      my_nickname:'',
      my_password:''
    }
  },
  methods: {
    login(){
      this.$emit('login')
    },
    uploadImage(){

    },
    register(){

    }
  },
  mounted() {
    // 获取随机数
    this.avatar_id = localStorage.getItem("avatar_id");
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

