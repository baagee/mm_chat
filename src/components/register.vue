<template>
  <div class="login_box" >
    <!-- <alert :alert_open="alert_open" :alert_msg="alert_msg" @closeAlert='alert_open=false'></alert> -->
   <div style="padding-top:1%;">
  <h1 style='color:#7e57c2'>秋名山-注册</h1>
    <div style="
    height: 200px;  
    ">
        <img :src="'/static/assets/avatar/1 ('+avatar_id+').jpg'" width="140px;" style="margin-top: 30px;border-radius:50%;cursor:pointer;border: 1px solid #ccc;">
    
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
    if (this.avatar_id == null) {
      // 没有随机数 就生成
      this.avatar_id = parseInt(Math.random() * 259) + 1;
      localStorage.setItem("avatar_id", this.avatar_id);
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

