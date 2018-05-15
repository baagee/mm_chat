<template>
<div class="login_box" >
    <alert :alert_open="alert_open" :alert_msg="alert_msg" @closeAlert='alert_open=false'></alert>
<div style="padding-top:8%;">
  <h1 style='color:#7e57c2'>秋名山-入口处</h1>
  <div style="
  height: 200px;  
  ">
  <div v-show="header_select_box" ref="header_select_box" style="
  height: 200px;
  overflow: auto;
  width: 300px;
  -webkit-overflow-scrolling: touch;
  border: 1px solid #d9d9d9;
  display:inline-block;
">
<span class="one_header" v-for="(item,index) in header_list" :key="index">
        <img :src="'/static/assets/avatar/1 ('+item+').jpg'" width="80px;" style="border-radius:50%;cursor:pointer;" @click="selectThisAvatar(item)">
</span>
  <mu-infinite-scroll :scroller="scroller" :loadingText="''" :loading="header_loading" @load="loadMore"/>
  </div>
        <img @click="header_select_box=true" v-show="!header_select_box" :src="'/static/assets/avatar/1 ('+avatar_id+').jpg'" width="140px;" style="margin-top: 30px;border-radius:50%;cursor:pointer;border: 1px solid #ccc;" title="点击我选择头像哦">
  </div>
</div>

<div style="margin-top:3%">
  <mu-text-field label="输入昵称" labelFloat v-model="my_nickname" @keyup.native.enter="login()"/>
  <br>
  <mu-raised-button label="开始聊天" class="demo-raised-button" primary @click="login()"/>
</div>
</div>
</template>
<script>
import { Toast } from "mint-ui";
import Alert from './alert';
export default {
  name: "login",
  components:{
      Alert
  },
  data() {
    const header_list = [];
    for (let i = 1; i <= 20; i++) {
      header_list.push(i);
    }
    return {
      header_list,
      header_num_s: 20,
      header_select_box: false,
      my_nickname: "",
      avatar_id: 1,
      scroller: "",
      header_loading: false,
      alert_open:false,
      alert_msg:''
    };
  },
  methods: {
    login() {
      var len = this.my_nickname.replace(/[\u0391-\uFFE5]/g, "aa").length;
      console.log(len);
      if (len > 0 && len <= 14) {
        // 昵称合法，保存本地缓存
        localStorage.setItem("my_nickname", this.my_nickname);
        // 发送socket登录
        this.loginHandle();
      } else {
        // 昵称太长
        this.alert_open = true;
        this.alert_msg = "昵称太长，不要超过4个汉字或者8个英文字符";
      }
    },
    loginHandle() {
      /*
      0        CONNECTING        连接尚未建立
    1        OPEN            WebSocket的链接已经建立
    2        CLOSING            连接正在关闭
    3        CLOSED            连接已经关闭或不可用
      */
      if (this.$socket.readyState == 1) {
        var login_data = {
          action: "login",
          nickname: this.my_nickname,
          avatar_id: this.avatar_id
        };
        console.log("登录发送的数据：", login_data);
        this.$socket.send(JSON.stringify(login_data));
      } else {
        alert("网络连接失败，请刷新");
        return false;
      }
    },
    // 改变头像
    selectThisAvatar(avatar_id) {
      localStorage.setItem("avatar_id", avatar_id);
      this.avatar_id = avatar_id;
      Toast("头像选择成功");
      this.header_select_box = false;
    },
    loadMore() {
      if (this.header_num_s >= 260) {
        return false;
      }
      this.header_loading = true;
      setTimeout(() => {
        for (let i = this.header_num_s + 1; i <= this.header_num_s + 20; i++) {
          this.header_list.push(i);
        }
        this.header_num_s += 20;
        this.header_loading = false;
      }, 500);
    },
  },
  mounted() {
    this.scroller = this.$refs.header_select_box;

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

