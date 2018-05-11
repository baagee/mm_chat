<template>
  <div id="app">

    <mu-dialog :open="alert_open" title="提示">
      {{alert_msg}}
    <mu-flat-button label="确定" slot="actions" primary @click="alert_close()"/>
  </mu-dialog>

<div class="login_box" v-if="!is_login">
<div style="padding-top:8%;">
  <h1 style='color:#7e57c2'>简易聊天室</h1>
  <img :src="'/static/assets/avatar/1 ('+mt_rand+').jpeg'" width="20%" style="border-radius:50%;">
  <div>
    <mu-flat-button label="更换头像" class="demo-raised-button" @click="change_avatar()"/>
     </div>
</div>
<div style="margin-top:3%">
  <mu-text-field label="输入昵称" labelFloat v-model="my_nickname"/>
  <br>
  <mu-raised-button label="开始聊天" class="demo-raised-button" primary @click="login()"/>
</div>
</div>



<mu-appbar title="在线聊天室">
  <!-- <mu-flat-button label="我的" slot="right"/> -->
  <!-- <mu-icon-button icon="expand_more" slot="right"/> -->
  <span slot="right" style="margin-right:10px">{{my_nickname}}</span>
      <mu-avatar :src="'/static/assets/avatar/1 ('+mt_rand+').jpeg'" slot="right" />
  <mu-icon-menu slot="right" icon="more_vert">
      <mu-menu-item title="退出" @click="logout()"/>
      <!-- <mu-menu-item title="帮助" /> -->
    </mu-icon-menu>
</mu-appbar>


<mu-row gutter>
    <mu-col width="100" tablet="40" desktop="30" >
      <div style="width:100%">
        <input type="text" v-model="searchKeywoyds" placeholder="搜索在线用户" style="    width: 100%;
    height: 35px;
    margin: 3px 0;
    border: 1px solid #f1f1f1;
    padding-left: 10px;">
      </div>

      <div style="overflow-y:scroll;    height: 494px;    background-color: #f1f1f1;">
          <mu-list>
    <!-- <mu-sub-header>在线人员</mu-sub-header> -->
    <mu-list-item  v-for="(user,index) in search(searchKeywoyds)" :key="index" :title="user.nickname">
      <mu-avatar :src="'/static/assets/avatar/1 ('+user.avatar_id+').jpeg'" slot="leftAvatar"/>
      <mu-icon value="chat_bubble"  slot="right" @click="chat_this(user.user_id)"/>
      <!-- v-if="user.user_id!=myself.info.user_id" -->
    </mu-list-item>
  </mu-list>
      </div>
  
    </mu-col>
    <mu-col width="100" tablet="33" desktop="70">
      <div class="message_box" id="message_content" style="
          height: 450px;
    overflow-y: scroll;
    border: 1px solid #f1f1f1;">
    

    <mu-list-item :disableRipple="true" v-for="(chat,index) in chat_list" :key="index">
        <mu-avatar :src="'/static/assets/avatar/1 ('+chat.avatar_id+').jpeg'" :slot="chat.self ? 'rightAvatar':'leftAvatar'" />
        <span :slot="chat.self ? 'after':'title'">
          <div style='font-size:12px;color:rgba(0,0,0,.54);text-align:right;margin-bottom:3px' v-if="chat.self">[{{chat.time}}] {{chat.nickname}}</div>
          <div style='font-size:12px;color:rgba(0,0,0,.54);margin-bottom:3px' v-else>{{chat.nickname}} [{{chat.time}}]</div>
                    <span class="content" style="color: rgba(0, 0, 0, .9)" :style="!chat.self?'font-size:14px;':''">
                      {{chat.message}}
                    </span>
        </span>
      </mu-list-item>
      </div>
<div class="message_input" style="    height: 85px;">
  <mu-text-field hintText="消息" multiLine :rows="3" :rowsMax="3" fullWidth style="width:80%;background-color: #f1f1f1;" v-model="message" @keyup.enter="send_message()"/>
  <mu-raised-button label="发送消息" class="demo-raised-button" @click="send_message()" primary style="    bottom: 35px;
    width: 19%;"/>
</div>

    </mu-col>
  </mu-row>




  </div>
</template>

<script>
import { mapState } from "vuex";
export default {
  name: "App",
  data() {
    return {
      // online_users: ,
      message: "",
      to: [],
      searchKeywoyds: "",
      isLogin: false,
      alert_open: false,
      alert_msg: "",
      my_nickname: "",
      mt_rand: 1
    };
  },
  methods: {
    login_handle() {
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
          avatar_id: this.mt_rand
        };
        console.log("登录发送的数据：", login_data);
        this.$socket.send(JSON.stringify(login_data));
      } else {
        alert("网络连接失败，请刷新");        
        return false;
      }
    },
    // 关闭弹框提示
    alert_close() {
      this.alert_open = false;
    },
    // 数组去重
    unique_array(array) {
      if (array.length == 0) {
        return [];
      }
      array.sort();
      var re = [array[0]];
      for (var i = 1; i < array.length; i++) {
        if (array[i] !== re[re.length - 1]) {
          re.push(array[i]);
        }
      }
      return re;
    },
    // 和这个人聊天
    chat_this(id) {
      var user = this.online_users.filter(user => {
        if (user.user_id == id) {
          return user;
        }
      });
      if (user.length > 0) {
        this.message += " @" + user[0].nickname + " ";
        this.to.push(id);
      }
    },
    // 发送消息
    send_message() {
      console.log(this.to);
      var send = {
        action: "chat",
        nickname: this.myself.info.nickname,
        message: this.message,
        avatar_id: this.mt_rand,
        // 如果to 目标用户数组为空，则在线所有人都能接受
        to: this.unique_array(this.to)
      };
      if (this.$socket.readyState == 1) {
        console.log("发送消息：" + send);
        // 发送socket消息
        this.$socket.send(JSON.stringify(send));
        this.message = "";
      }else{
        alert("网络连接失败，请刷新");
        return false;
      }
    },
    // 搜索在线人员
    search(searchKeywoyds) {
      return this.online_users.filter(user => {
        // 判断字符串是否在
        if (user.nickname.includes(searchKeywoyds)) {
          return user;
        }
      });
    },
    // 滚动条永远在最下面
    scrollToBottom: function() {
      this.$nextTick(() => {
        var div = document.getElementById("message_content");
        div.scrollTop = div.scrollHeight;
      });
    },
    // 登录
    login() {
      var len = this.my_nickname.replace(/[\u0391-\uFFE5]/g, "aa").length;
      console.log(len);
      if (len > 0 && len <= 8) {
        // 昵称合法，保存本地缓存
        localStorage.setItem("my_nickname", this.my_nickname);
        // 发送socket登录
        this.login_handle();
      } else {
        // 昵称太长
        this.alert_open = true;
        this.alert_msg = "昵称太长，不要超过4个汉字或者8个英文字符";
      }
    },
    // 退出登录
    logout() {
      this.$socket.close();
      localStorage.removeItem("my_nickname");
      this.isLogin = false;
      this.my_nickname = "";
      this.mt_rand = 1;
      location.reload();
    },
    // 改变头像
    change_avatar() {
      this.mt_rand = parseInt(Math.random() * 81) + 1;
      localStorage.setItem("mt_rand", this.mt_rand);
    }
  },
  computed: {
    ...mapState(["online_users", "chat_list", "myself", "is_login"])
  },
  watch: {
    chat_list: "scrollToBottom"
  },
  mounted() {
    var div = document.getElementById("message_content");
    div.scrollTop = div.scrollHeight;
    // 获取随机数
    this.mt_rand = localStorage.getItem("mt_rand");
    if (this.mt_rand == null) {
      // 没有随机数 就生成
      this.mt_rand = parseInt(Math.random() * 80) + 1;
      localStorage.setItem("mt_rand", this.mt_rand);
    }
    // 获取缓存昵称
    var my_nickname = localStorage.getItem("my_nickname");
    console.log(my_nickname);
    if (my_nickname == null) {
      // 缓存昵称不存在未登录
    } else {
      // 昵称存在
      this.my_nickname = my_nickname;
      // store.commit("set_is_login", true);      
      // todo 刷新保留登录状态
      console.log(this.$socket.readyState)
      // if (this.$socket.readyState == 1) {
      //   var login_data = {
      //     action: "login",
      //     nickname: this.my_nickname,
      //     avatar_id: this.mt_rand
      //   };
      //   console.log("登录发送的数据：", login_data);
      //   this.$socket.send(JSON.stringify(login_data));
      // } else {
      //   alert("网络连接失败，请刷新");        
      //   return false;
      // }
    }
  }
};
</script>

<style>
#app {
  font-family: "Avenir", Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  margin: 2% 20%;
  max-height: 600px;
  overflow: hidden;
  border: 1px solid rgb(241, 241, 241);
}
.content {
  display: inline-block;
  padding: 15px;
  /* margin: 0; */
  background: #fff;
}
.mu-item-after {
  margin-right: 12px !important;
}
.login_box {
  text-align: center;
  width: 100%;
  margin: 0;
  z-index: 99999;
  height: 700px;
  /* background-color: #7e57c2; */
}
.mu-text-field-multiline{
  height:79px;
}
</style>
