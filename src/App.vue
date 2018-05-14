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

  <div v-if="show_help" class="login_box" style="text-align:left">
<div>
    <h3>1：关于发送消息的快捷键</h3>
    <p>
      你不能使用回车（enter）来发送，请使用ctrl+enter(回车)来发送，因为回车是输入框的换行，所以你只能用ctrl+enter快捷键来发送消息了
    </p>
    <h3>2：关于@别人</h3>
    <p>
      <h4>2.1：@别人是什么功能？</h4>
      当你@别人时相当于只给他自己发了消息（私聊），你没@的人是不会受到消息的，如果你谁都没@，则全部在线人员都能接受到你的消息。
      <h4>2.2：如何@别人？</h4>
      在左边的在线用户列表中，每个用户（除了你自己）右边有一个消息的图标，鼠标点击那个会在消息输入框自动加入@xxx，
      <span style="color:red">但是如果你在输入框手动输入@某某是不会起到@的作用的，只有通过鼠标点击消息的标志才起作用</span>
    </p>
    <h3>3：聊天机器人如何使用？</h3>
    <p>
      和@别人一样，你只需要@她就行啦，此时应该注意，你@机器人时所有在线人员都能看到你和机器人的聊天，和@某个用户不一样
    </p>
    <h3>4：给@的人发送图片？</h3>
    <p>
      选择图片发送之前注意要先@某人，千万别发送，然后选择你要发的图片会自动发送的。如果你想只给某人发私密照，但是没有@他直接选择图片，那么全部人员都会看到哦。。
    </p>
    <h3>5：如何更换图像？</h3>
    <p>
      在登录页点击头像进入头像选择，然后点击中意的头像就ok啦
    </p>
</div>
<br>
<div style="text-align:center">
      <mu-raised-button label="我了解啦" @click='show_help=false' primary/>
    </div>
  </div>

<div class="login_box" v-if="!is_login">
<div style="padding-top:8%;">
  <h1 style='color:#7e57c2'>秋名山</h1>
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
        <img @click="header_select_box=true" v-show="!header_select_box" :src="'/static/assets/avatar/1 ('+mt_rand+').jpg'" width="140px;" style="margin-top: 30px;border-radius:50%;cursor:pointer;border: 1px solid #ccc;" title="点击我选择头像哦">
  </div>
</div>

<div style="margin-top:3%">
  <mu-text-field label="输入昵称" labelFloat v-model="my_nickname" @keyup.native.enter="login()"/>
  <br>
  <mu-raised-button label="开始聊天" class="demo-raised-button" primary @click="login()"/>
</div>
</div>



<mu-appbar title="秋名山-请不要酒后开车" v-if="is_login">
  <span slot="right" style="margin-right:10px">{{my_nickname}}</span>
      <mu-avatar :src="'/static/assets/avatar/1 ('+mt_rand+').jpg'" slot="right" />

  <mu-icon-menu slot="right" icon="more_vert" :anchorOrigin="{horizontal: 'right', vertical: 'top'}"
      :targetOrigin="{horizontal: 'right', vertical: 'top'}">
    <!-- <mu-menu-item title="设置" leftIcon="settings" /> -->
    <mu-menu-item title="帮助" leftIcon="help_outline" @click="show_help=true"/>
    <mu-divider />
    <mu-menu-item title="退出" leftIcon="power_settings_new" @click="logout()"/>
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
            <mu-list-item style="border-bottom:1px dotted #ccc;"  title="机器人-小希">
              <mu-avatar :src="'/static/assets/avatar/1 (261).jpg'" slot="leftAvatar"/>
              <mu-icon value="chat_bubble" slot="right" @click="chatThis(-1,'机器人-小希')" title="点击@我哦"/>
            </mu-list-item>
            <mu-list-item style="border-bottom:1px dotted #ccc;"  v-for="(user,index) in search(search_keywoyds)" :key="index" :title="user.nickname">
              <mu-avatar :src="'/static/assets/avatar/1 ('+user.avatar_id+').jpg'" slot="leftAvatar"/>
              <mu-icon value="chat_bubble" v-show="user.user_id!=myself.info.user_id" slot="right" @click="chatThis(user.user_id,user.nickname)" title="点击@我哦"/>
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
                    <span class="content" @click="chat.type=='img'?showBigImg(chat.message):''"
                    :style="!chat.self?'font-size:14px;':'float: right;'" 
                    v-html="createResponseHtmlTag(chat)">
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
    top: -175px;
    height: 175px;
    overflow-y: auto;
    " v-show="show_emoji">
<span v-if="is_login" class="emoji_box" v-for="i in 576" :key="i" @click="addEmoji('[emoji_'+i+']')">
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

  <mu-text-field multiLine :rows="3" :rowsMax="3" hintText="请输入消息，记得按ctrl+enter快捷键发送哦" fullWidth style="width:100%;background-color: #f1f1f1;padding-right: 90px;padding-left: 5px;" v-model="message" @keyup.native.17.13="sendMessage()"/>
  <mu-raised-button label="发送消息" class="demo-raised-button" @click="sendMessage()" primary style="bottom: 61px;float:right;"/>
</div>

    </mu-col>
  </mu-row>


  </div>
</template>

<script>
import { mapState } from "vuex";
import { Indicator } from "mint-ui";
import { Toast } from "mint-ui";
export default {
  name: "App",
  data() {
    const header_list = [];
    for (let i = 1; i <= 20; i++) {
      header_list.push(i);
    }
    return {
      header_list,
      header_num_s: 20,
      header_loading: false,
      scroller: null,
      message: "",
      at_map: {},
      to: [],
      search_keywoyds: "",
      alert_open: false,
      alert_msg: "",
      my_nickname: "",
      mt_rand: 1,
      show_emoji: false,
      show_img: false,
      big_img: "",
      header_select_box: false,
      show_help: false
    };
  },
  methods: {
    // 显示大图
    showBigImg(img_path) {
      this.show_img = true;
      this.big_img = img_path;
    },
    createResponseHtmlTag(chat) {
      if (chat.type === "text") {
        var tag = chat.message;
      } else if (chat.type == "url") {
        var tag = '<a target="_blank" href="' + chat.message + '">点击查看</a>';
      } else if (chat.type == "img") {
        var tag = this.createImgTag(chat.message);
      }
      return tag;
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
              for (var nickname in this.at_map) {
                if (this.message.indexOf(nickname) !== -1) {
                  this.to.push(this.at_map[nickname]);
                }
              }
              var send = {
                action: "chat",
                nickname: this.myself.info.nickname,
                message: this.message,
                avatar_id: this.mt_rand,
                // 如果to 目标用户数组为空，则在线所有人都能接受
                to: this.uniqueArray(this.to)
              };
              this.$socket.send(JSON.stringify(send));
              send.message = "[img]:" + response.data.img_path;
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
    // 和这个人聊天
    chatThis(id, nickname) {
      if (id == this.myself.info.user_id) {
        return false;
      }
      if (this.message.indexOf("@" + nickname) == -1) {
        this.at_map["@" + nickname] = id;
        this.message += "@" + nickname + " ";
      }
    },
    // 发送消息
    sendMessage() {
      for (var nickname in this.at_map) {
        if (this.message.indexOf(nickname) !== -1) {
          this.to.push(this.at_map[nickname]);
        }
      }
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
        this.at_map = {};
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
    selectThisAvatar(avatar_id) {
      localStorage.setItem("mt_rand", avatar_id);
      this.mt_rand = avatar_id;
      Toast("头像选择成功");
      this.header_select_box = false;
    }
  },
  computed: {
    ...mapState(["online_users", "chat_list", "myself", "is_login"])
  },
  watch: {
    chat_list: "scrollToBottom"
  },
  mounted() {
    this.scroller = this.$refs.header_select_box;
    var div = document.getElementById("message_content");
    div.scrollTop = div.scrollHeight;
    // 获取随机数
    this.mt_rand = localStorage.getItem("mt_rand");
    if (this.mt_rand == null) {
      // 没有随机数 就生成
      this.mt_rand = parseInt(Math.random() * 259) + 1;
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
  overflow: hidden;
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
.one_header {
  border: 1px solid #c6c6c6;
  width: 86px;
  height: 86px;
  display: inline-block;
  padding: 3px;
  margin: 2px;
}
.one_header:hover {
  background-color: rebeccapurple;
}
</style>
