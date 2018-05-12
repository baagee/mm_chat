<template>
  <div id="app" @click="show_emoji=false">

    <mu-dialog :open="alert_open" title="提示">
      {{alert_msg}}
    <mu-flat-button label="确定" slot="actions" primary @click="alertClose()"/>
  </mu-dialog>

  <!-- 图片放大 -->
  <mu-dialog :open="show_img" @close="show_img=false">
    <img :src="big_img" style="width:100%">
  </mu-dialog>

<div class="login_box" v-if="!is_login">
<div style="padding-top:8%;">
  <h1 style='color:#7e57c2'>在线聊天室</h1>
  <img :src="'/static/assets/avatar/1 ('+mt_rand+').jpg'" width="250px;" style="border-radius:50%;cursor:pointer;border: 1px solid #ccc;" @click="changeAvatar()" title="点击头像更换头像哦">
</div>
<div style="margin-top:3%">
  <mu-text-field label="输入昵称" labelFloat v-model="my_nickname"/>
  <br>
  <mu-raised-button label="开始聊天" class="demo-raised-button" primary @click="login()"/>
</div>
</div>



<mu-appbar title="在线聊天室" v-show="is_login">
  <span slot="right" style="margin-right:10px">{{my_nickname}}</span>
      <mu-avatar :src="'/static/assets/avatar/1 ('+mt_rand+').jpg'" slot="right" />
  <mu-icon-menu slot="right" icon="more_vert">
      <mu-menu-item title="退出" @click="logout()"/>
    </mu-icon-menu>
</mu-appbar>


<mu-row gutter>
    <mu-col width="100" tablet="40" desktop="30"  style="position: absolute;height: 100%;padding-bottom: 105px;">
      <div style="width:100%">
        <input type="text" v-model="search_keywoyds" placeholder="搜索在线用户" style="
        width: 100%;
    height: 35px;
    margin: 3px 0;
    border: 1px solid #f1f1f1;
    padding-left: 10px;">
      </div>

      <div style="background-color: rgb(241, 241, 241);
    height: 100%;
    overflow-y: auto;">
          <mu-list>
    <mu-list-item style="border-bottom:1px dotted #ccc;"  v-for="(user,index) in search(search_keywoyds)" :key="index" :title="user.nickname">
      <mu-avatar :src="'/static/assets/avatar/1 ('+user.avatar_id+').jpg'" slot="leftAvatar"/>
      <mu-icon value="chat_bubble" v-show="user.user_id!=myself.info.user_id" slot="right" @click="chatThis(user.user_id)"/>
    </mu-list-item>
  </mu-list>
      </div>
  
    </mu-col>
    <mu-col width="100" tablet="60" desktop="70" style="position: absolute;
    left: 30%;
    height: 100%;padding-bottom: 155px;">
      <div class="message_box" id="message_content" style="
    overflow-y: auto;
        background-color: #f1f1f1;
    border: 1px solid #f1f1f1;height:100%;">
    

    <mu-list-item :disableRipple="true" v-for="(chat,index) in chat_list" :key="index">
        <mu-avatar :src="'/static/assets/avatar/1 ('+chat.avatar_id+').jpg'" :slot="chat.self ? 'rightAvatar':'leftAvatar'" />
        <span :slot="chat.self ? 'after':'title'" style="width:100%">
          <div style='font-size:12px;color:rgba(0,0,0,.54);text-align:right;margin-bottom:3px' v-if="chat.self">[{{chat.time}}] {{chat.nickname}}</div>
          <div style='font-size:12px;color:rgba(0,0,0,.54);margin-bottom:3px' v-else>{{chat.nickname}} [{{chat.time}}]</div>
                    <span class="content" @click="chat.type=='str'?'':showBigImg(chat.message)"
                    :style="!chat.self?'font-size:14px;':'float: right;'" 
                    v-html="chat.type=='str'?chat.message:createImgTag(chat.message)">
                    </span>
        </span>
      </mu-list-item>
      </div>
<div class="message_input" style="width: 100%;position: absolute;
    bottom: 4px;">
    <!-- emoji -->
    <div style="
    position: absolute;
    z-index: 100;
    width: 40%;
    right: 0px;
    background-color: #f3f3f3;
    top: -150px;
    height: 150px;
    overflow-y: auto;
    " v-show="show_emoji">
    
<span class="emoji_box" v-for="i in 213" :key="i" @click="addEmoji('[emoji_'+i+']')">
  <img :src="'/static/assets/emoji/1 ('+i+').gif'" alt="">
</span>
    </div>

  <!-- 图片按钮 -->
  <input type="file" style="
      position: absolute;
    z-index: 100;
    cursor: pointer;
    right: 38px;
    font-size:0;
    opacity: 0;
    width: 40px;
    height: 48px;"  @change="uploadImage" accept="image/gif,image/jpeg,image/jpg,image/png">
  <mu-icon-button icon="photo_size_select_actual" style="position: absolute;
    top: 0;
    pointer-events:none;
    z-index: 100;
    cursor:pointer;
    right:38px;">
    
    </mu-icon-button>
    <!-- 表情按钮 -->
    <mu-icon-button icon="tag_faces" style="position: absolute;
    top: 0;
    z-index: 100;
    cursor:pointer;
    right:0;" @click.stop="show_emoji=!show_emoji"/>

  <mu-text-field multiLine :rows="3" :rowsMax="3" fullWidth style="width:100%;background-color: #f1f1f1;padding-right: 90px;" v-model="message" @keyup.native.ctrl.enter="sendMessage()"/>
  <mu-raised-button label="发送消息" class="demo-raised-button" @click="sendMessage()" primary style="bottom: 61px;float:right;"/>
</div>

    </mu-col>
  </mu-row>


  </div>
</template>

<script>
import { mapState } from "vuex";
import { Indicator } from "mint-ui";
export default {
  name: "App",
  data() {
    return {
      message: "",
      to: [],
      search_keywoyds: "",
      alert_open: false,
      alert_msg: "",
      my_nickname: "",
      mt_rand: 1,
      show_emoji: false,
      show_img: false,
      big_img: ""
    };
  },
  methods: {
    // 显示大图
    showBigImg(img_path) {
      this.show_img = true;
      this.big_img = img_path;
    },

    createImgTag(src) {
      // var loading='/static/assets/loading.gif';
      var error = "/static/assets/error.png";
      var tag =
        "<img style='max-width:300px' src='" +
        src +
        "' onerror=\"this.src='" +
        error +
        "'\">";
      //  onload=\"this.src='"+loading+"'\"
      return tag;
    },
    // 上传图片
    uploadImage(e) {
      let file = e.target.files[0];
      let param = new FormData(); // 创建form对象
      param.append("image", file, file.name); // 通过append向form对象添加数据
      console.log(param.get("file")); // FormData私有类对象，访问不到，可以通过get判断值是否传进去
      let config = {
        headers: { "Content-Type": "multipart/form-data" }
      };

      var that = this;
      // var file = e.target.files[0];
      var imgSize = file.size / 1024;
      if (imgSize > 1024 * 5) {
        this.alert_open = true;
        this.alert_msg = "请不要上传大于5Mb的图片";
      } else {
        Indicator.open({
          text: "上传中...",
          spinnerType: "fading-circle"
        });
        this.$axios
          .post("/upload.php", param, config)
          .then(response => {
            console.log(response);
            if (response.data.res == true) {
              Indicator.close();
              // 上传成功 发送socket
              var send = {
                action: "chat",
                nickname: this.myself.info.nickname,
                message: "[img]:" + response.data.img_path,
                avatar_id: this.mt_rand,
                // 如果to 目标用户数组为空，则在线所有人都能接受
                to: this.uniqueArray(this.to)
              };
              this.$socket.send(JSON.stringify(send));
              this.message = "";
              this.to = [];
            } else {
              Indicator.close();
              this.alert_open = true;
              this.alert_msg = response.data.err_msg;
            }
          })
          .catch(error => {
            Indicator.close();
            console.log(error);
            alert("抱歉，出现未知错误");
          });
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
    alertClose() {
      this.alert_open = false;
    },
    // 选择表情
    addEmoji(key) {
      this.message += key + " ";
    },
    // 数组去重
    uniqueArray(array) {
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
    test11(){
      alert(222)
    },
    // 和这个人聊天
    chatThis(id) {
      if (id == this.myself.info.user_id) {
        return false;
      }
      if (this.to.indexOf(id) !== -1) {
        return false;
      }
      var user = this.online_users.filter(user => {
        if (user.user_id == id) {
          return user;
        }
      });
      if (user.length > 0) {
        this.message += "@" + user[0].nickname + " ";
        this.to.push(id);
      }
    },
    // 发送消息
    sendMessage() {
      console.log(this.to);
      var send = {
        action: "chat",
        nickname: this.myself.info.nickname,
        message: this.message,
        avatar_id: this.mt_rand,
        // 如果to 目标用户数组为空，则在线所有人都能接受
        to: this.uniqueArray(this.to)
      };
      if (this.$socket.readyState == 1) {
        console.log("发送消息：" + send);
        // 发送socket消息
        this.$socket.send(JSON.stringify(send));
        this.message = "";
        this.to = [];
      } else {
        alert("网络连接失败，请刷新");
        return false;
      }
    },
    // 搜索在线人员
    search(search_keywoyds) {
      return this.online_users.filter(user => {
        // 判断字符串是否在
        if (user.nickname.includes(search_keywoyds)) {
          return user;
        }
      });
    },
    // 滚动条永远在最下面
    scrollToBottom: function() {
      this.$nextTick(() => {
        var div = document.getElementById("message_content");
        div.scrollTop = div.scrollHeight;
        // todo滚动条发图时不能到最下面
      });
    },
    // 登录
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
    // 退出登录
    logout() {
      this.$socket.close();
      localStorage.removeItem("my_nickname");
      this.my_nickname = "";
      this.mt_rand = 1;
      location.reload();
    },
    // 改变头像
    changeAvatar() {
      this.mt_rand = parseInt(Math.random() * 258) + 1;
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
      this.mt_rand = parseInt(Math.random() * 258) + 1;
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
    }
  }
};
</script>

<style>
#app {
  font-family: "Avenir", Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  width: 80%;
  margin: auto;
  position: absolute;
  top: 10px;
  left: 0;
  right: 0;
  bottom: 30px;
  overflow: hidden;
}
.content {
  display: inline-block;
  padding: 10px;
  background: #fff;
  color: rgb(0, 0, 0);
  word-wrap: break-word;
  user-select: text;
  cursor: auto;
  max-width: 100%;
  line-height: 16px;
}
.mu-item-after {
  margin-right: 12px !important;
}
.login_box {
  text-align: center;
  width: 100%;
  margin: 0;
  z-index: 99999;
  height: 100%;
  /* background-color: #7e57c2; */
}
.mu-text-field-multiline {
  height: 79px;
}
.emoji_box {
  background-color: #f3f3f3;
  display: block;
  float: left;
  width: 25px;
  height: 25px;
  cursor: pointer;
  border-bottom: 1px dotted #ccc;
  border-right: 1px dotted #ccc;
}
.emoji_box:hover {
  background-color: #7e57c2;
}
.mu-text-field::-webkit-scrollbar {
  display: none;
}
.mu-item-after {
  max-width: 100%;
}
.mu-dialog {
  overflow-y: auto;
  max-height: 80%;
  max-width: 75%;
  width: auto;
}
</style>
