<template>
  <div id="app" @click="show_emoji=false">

<alert :alert_open="alert_open" :alert_msg="alert_msg" @closeAlert='alert_open=false'></alert>

  <!-- 图片放大 -->
  <mu-dialog :open="show_img" @close="show_img=false">
    <img :src="big_img" style="width:100%">
  </mu-dialog>

  <mu-dialog :open="show_send_img_confirm" @close="show_send_img_confirm=false" title="确定要发送这张图吗？" scrollable>
    <img :src="base64_img" style="width:100%">
    <mu-flat-button slot="actions" @click="show_send_img_confirm=false"  label="取消"/>
    <mu-flat-button label="确定" slot="actions" primary @click="sendBase64Image()"/>
  </mu-dialog>

<helper @closeHelper='closeHelper' v-if="show_help"></helper>

<login v-if="!is_login" ></login>


<mu-appbar title="秋名山-请不要酒后开车" v-if="is_login">
  <span slot="right" style="margin-right:10px">{{myself.info.nickname}}</span>
      <mu-avatar :src="'/static/assets/avatar/1 ('+myself.info.avatar_id+').jpg'" slot="right" />

  <mu-icon-menu slot="right" icon="more_vert" :anchorOrigin="{horizontal: 'right', vertical: 'top'}"
      :targetOrigin="{horizontal: 'right', vertical: 'top'}">
    <!-- <mu-menu-item title="设置" leftIcon="settings" /> -->
    <mu-menu-item title="帮助" leftIcon="help_outline" @click="show_help=true"/>
    <mu-divider />
    <mu-menu-item title="退出" leftIcon="power_settings_new" @click="logout()"/>
  </mu-icon-menu>
</mu-appbar>


<mu-row gutter id="dropbox">
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
      <mu-linear-progress v-if="uploading" mode="determinate" :size='5' :value="progress"></mu-linear-progress>
<div class="message_input" style="width: 100%;position: absolute;
    bottom: 4px;">
    <!-- emoji -->
    <div style="
    position: absolute;
    z-index: 100;
    width: 41%;
    right: 1px;
    box-shadow: 0 0 10px #cacaca;
    background-color: #f3f3f3;
    top: -176px;
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

  <mu-text-field multiLine :rows="3" :rowsMax="3" hintText="请输入消息，记得按ctrl+enter快捷键发送哦" fullWidth style="width:100%;background-color: #f1f1f1;padding-right: 90px;padding-left: 5px;" v-model="message" @keyup.native.ctrl.enter="sendMessage()"/>
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

import Helper from "./components/help";
import Login from "./components/login";
import Alert from "./components/alert";
import COS from "cos-js-sdk-v5";
import browserMD5File from "browser-md5-file";

export default {
  name: "App",
  data() {
    return {
      acceptImgExt:['jpeg','png','jpg','gif'],
      message: "",
      at_map: {},
      to: [],
      search_keywoyds: "",
      alert_open: false,
      alert_msg: "",
      show_emoji: false,
      show_img: false,
      big_img: "",
      show_help: false,
      show_send_img_confirm: false,
      base64_img: "",
      progress: 0,
      uploading: false,
      cos: null,
      Bucket: "chat-room-1256151484",//改成你自己的
      Region: "ap-beijing"//改成你自己的
    };
  },
  components: {
    Helper,
    Login,
    Alert
  },
  methods: {
    closeHelper() {
      this.show_help = false;
    },
    alert(msg) {
      this.alert_open = true;
      this.alert_msg = msg;
    },
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
    createImgTag(src) {
      var error = "/static/assets/error.png";
      var tag =
        "<img style='max-width:300px;cursor: pointer;' src='" +
        src +
        "' onerror=\"this.src='" +
        error +
        "'\">";
      return tag;
    },
    // 发送图片
    publishImage(imgUrl) {
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
        avatar_id: this.myself.info.avatar_id,
        // 如果to 目标用户数组为空，则在线所有人都能接受
        to: this.uniqueArray(this.to)
      };
      this.$socket.send(JSON.stringify(send));
      send.message = "[img]:" + imgUrl;
      this.$socket.send(JSON.stringify(send));
      this.message = "";
      this.to = [];
    },
    uploadImageHandle(file, filename) {
      Toast("开始上传发送");
      this.uploading = true;
      // 上传前检测文件是否存在，不存在就上传
      this.cos.getObject(
        {
          Bucket: this.Bucket /* 必须 */,
          Region: this.Region /* 必须 */,
          Key: "/images/" + filename,
          Range: "bytes=1-3" /* 非必须 */
        },
        (err, data) => {
          if (err == null && data.statusCode > 200 && data.statusCode < 300) {
            // 文件已经上传过了。
            console.log("文件已经上传过了");
            this.progress = 100;
            // 直接返回路径
            console.log(data);
            var imgUrl =
              "https://" +
              this.Bucket +
              ".cos." +
              this.Region +
              ".myqcloud.com/images/" +
              filename;
            console.log(imgUrl);
            this.publishImage(imgUrl);
            this.uploading = false; //隐藏进度条
            this.progress = 0;
          } else {
            console.log("文件没上传过, 开始上传");
            // 开始上传
            this.cos.putObject(
              {
                Bucket: this.Bucket /* 必须 */,
                Region: this.Region /* 必须 */,
                Key: "/images/" + filename /* 必须 */,
                StorageClass: "STANDARD",
                Body: file, // 上传文件对象
                onProgress: progressData => {
                  console.log(JSON.stringify(progressData));
                  // {"loaded":27151,"total":27151,"speed":88152.6,"percent":1}
                  this.progress = progressData.percent * 100;
                }
              },
              (err, data) => {
                console.log(data);
                this.uploading = false; //隐藏进度条
                this.progress = 0; //重置为0
                if (
                  err == null &&
                  data.statusCode >= 200 &&
                  data.statusCode < 300
                ) {
                  // success
                  // console.log(data.Location);
                  this.publishImage(data.Location);
                } else {
                  this.alert("发送图片未知原因失败")
                  console.log(err);
                }
              }
            );
          }
        }
      );
    },
    // 上传图片
    uploadImage(e) {
      let file = e.target.files[0];
      console.log(file);
      var ext = file.type
        .substring(file.type.lastIndexOf("/") + 1)
        .toLowerCase();
        if(this.acceptImgExt.indexOf(ext)==-1){
          this.alert('不支持发送'+ext+'格式的文件');
          return false;
        }
      browserMD5File(file, (err, md5) => {
        // 文件名用md5区分唯一性。
        console.log(err);
        if (err != null) {
          console.log("出错：error:" + err);
        } else {
          var filename = md5 + "." + ext;
          console.log("filename=" + filename);
          this.uploadImageHandle(file, filename);
        }
      });
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
      document.getElementsByClassName("mu-text-field-textarea")[0].focus();
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
        avatar_id: this.myself.info.avatar_id,
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
        this.alert("网络连接失败，请刷新");
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

    // 退出登录
    logout() {
      this.$socket.close();
      localStorage.removeItem("my_nickname");
      localStorage.removeItem("avatar_id");
      location.reload();
    },
    // 粘贴上传base64图片sendBase64Image
    sendBase64Image() {
      this.show_send_img_confirm = false;
      var arr = this.base64_img.split(","),
        mime = arr[0].match(/:(.*?);/)[1],
        bstr = atob(arr[1]),
        n = bstr.length,
        ext = mime.substring(mime.lastIndexOf("/") + 1).toLowerCase(),
        u8arr = new Uint8Array(n);
      while (n--) {
        u8arr[n] = bstr.charCodeAt(n);
      }
      var filename = "tmp." + ext;
      var file_s = new File([u8arr], filename, { type: mime });
      browserMD5File(file_s, (err, md5) => {
        // 文件名用md5区分唯一性。
        console.log(err);
        if (err != null) {
          console.log("出错：error:" + err);
        } else {
          filename = md5 + "." + ext;
          console.log("filename=" + filename);
          this.uploadImageHandle(file_s, filename);
        }
      });
      this.base64_img = "";
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
    // 粘贴上传
    document.addEventListener("paste", event => {
      if (event.clipboardData || event.originalEvent) {
        //not for ie11  某些chrome版本使用的是event.originalEvent
        var clipboardData =
          event.clipboardData || event.originalEvent.clipboardData;
        if (clipboardData.items) {
          // for chrome
          var items = clipboardData.items,
            len = items.length,
            blob = null;

          //阻止默认行为即不让剪贴板内容在div中显示出来
          // event.preventDefault();

          //在items里找粘贴的image,据上面分析,需要循环
          for (var i = 0; i < len; i++) {
            if (items[i].type.indexOf("image") !== -1) {
              //getAsFile() 此方法只是living standard firefox ie11 并不支持
              blob = items[i].getAsFile();
            }
          }
          if (blob !== null) {
            var reader = new FileReader();
            reader.onload = event => {
              // event.target.result 即为图片的Base64编码字符串
              var base64_str = event.target.result;
              var arr = base64_str.split(","),
                mime = arr[0].match(/:(.*?);/)[1],
                ext = mime.substring(mime.lastIndexOf("/") + 1).toLowerCase();
              if(this.acceptImgExt.indexOf(ext)==-1){
                this.alert('不支持发送'+ext+'格式的文件');
                return false;
              }
              this.base64_img = base64_str;
              this.show_send_img_confirm = true;
            };
            reader.readAsDataURL(blob);
          }
        }
      }
    });
    // 拖拽上传
    var dropbox = document.getElementById("dropbox");
    dropbox.addEventListener(
      "dragenter",
      function(e) {
        e.stopPropagation();
        e.preventDefault();
      },
      false
    );
    dropbox.addEventListener(
      "dragover",
      function(e) {
        e.stopPropagation();
        e.preventDefault();
      },
      false
    );
    dropbox.addEventListener(
      "drop",
      e => {
        e.stopPropagation();
        e.preventDefault();
        var file = e.dataTransfer.files[0];
        if (file != undefined) {
          var fr = new FileReader();
          fr.readAsDataURL(file);
          fr.onload = e => {
            this.base64_img = e.target.result;
            var arr = this.base64_img.split(","),
              mime = arr[0].match(/:(.*?);/)[1],
              ext = mime.substring(mime.lastIndexOf("/") + 1).toLowerCase();
            if(this.acceptImgExt.indexOf(ext)==-1){
              this.alert('不支持发送'+ext+'格式的文件');
              return false;
            }
            this.show_send_img_confirm = true;
          };
        }
      },
      false
    );
    // 腾讯对象存储
    this.cos = new COS({
      // 必选参数
      getAuthorization: (options, callback) => {
        // 异步获取签名
        this.$axios
          .get("/auth.php", {
            params: {
              method: (options.Method || "get").toLowerCase(),
              pathname: options.Key
            }
          })
          .then(response => {
            console.log(response.data);
            callback(response.data);
          })
          .catch(e => {
            this.alert("发送图片功能暂时不能使用")
            console.log(e);
          });
      },
      ProgressInterval: 500
    });
  }
};
</script>

<style>
/*滚动条 start*/
::-webkit-scrollbar {
  /* display: none; */
  width: 10px;
  height: 4px;
  background-color: #f5f5f5;
}
/*定义滚动条轨道 内阴影+圆角*/
::-webkit-scrollbar-track {
  -webkit-box-shadow: inset 0 0 6px rgba(0, 0, 0, 0.3);
  background: #fff;
}
/*定义滑块 内阴影+圆角*/
::-webkit-scrollbar-thumb {
  border-radius: 3px;
  -webkit-box-shadow: inset 0 0 6px rgba(0, 0, 0, 0.3);
  background-color: rgb(204, 204, 204);
}
::-webkit-scrollbar-thumb:hover {
  border-radius: 3px;
  -webkit-box-shadow: inset 0 0 6px rgba(0, 0, 0, 0.3);
  background-color: rgb(204, 204, 204);
}
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

.mu-text-field-multiline {
  height: 79px;
}
.emoji_box {
  background-color: #fff;
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
  /* background-color: #7e57c2; */
  border: 1px solid #7e57c2;
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
