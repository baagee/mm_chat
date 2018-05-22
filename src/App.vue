<template>
  <div id="app" @click="show_emoji=false">

<alert :alert_open="alert_open" :alert_msg="alert_msg" @closeAlert='alert_open=false'></alert>

<personal-page @closePersonalPage="show_personal_page=false" @chatThis='chatThis' :user_id="show_user_id" :is_group='is_group' v-if="show_personal_page"></personal-page>

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

<login @register="is_register=true" v-if="!is_login && is_register==false"></login>
<register @login="is_register=false" v-if="is_register" ></register>


<mu-appbar title="秋名山-请不要酒后开车" v-if="is_login">
  <span slot="right" style="margin-right:10px">{{myself.info.nickname}}</span>
      <mu-avatar :src="'/static/assets/avatar/1 ('+myself.info.avatar_id+').jpg'" slot="right" style="cursor:pointer" @click='showPersonalPage(myself.info.user_id,false)'/>

  <mu-icon-menu slot="right" icon="more_vert" :anchorOrigin="{horizontal: 'right', vertical: 'top'}"
      :targetOrigin="{horizontal: 'right', vertical: 'top'}">
    <mu-menu-item title="添加好友" leftIcon="settings" />
    <mu-menu-item title="帮助" leftIcon="help_outline" @click="show_help=true"/>
    <mu-divider />
    <mu-menu-item title="退出" leftIcon="power_settings_new" @click="logout()"/>
  </mu-icon-menu>
</mu-appbar>


<mu-row gutter>
    <mu-col width="100" tablet="40" desktop="30"  style="position: absolute;height: 100%;padding-bottom: 150px;">
      <div style="width:100%">
        <input type="text" v-model="search_keywoyds" :placeholder="searchPlaceHolder" style="
        width: 100%;
    height: 35px;
    margin: 3px 0;
    border: 1px solid #f1f1f1;
    padding-left: 10px;">
    <mu-tabs :value="active_tab" @change="handleTabChange" style="border-bottom: 1px solid white;">
      <mu-tab value="nearchat" title="最近"/>
      <mu-tab value="friends" title="好友"/>      
      <mu-tab value="groups" title="分组"/>
    </mu-tabs>
      </div>

      <!-- 最近联系人 -->
      <div style="background-color: rgb(241, 241, 241);
    height: 100%;
    overflow-y: auto;">
          <mu-list v-if="active_tab=='nearchat'" class='nearsdgsdfg'>
            <mu-list-item style="border-bottom:1px dotted #ccc" :class="active_near_user==user.user_id||active_near_user==user.group_id?'active_near_user':''" 
             v-for="(user,index) in near_chat" :key="index" :title="user.name" @click.stop="user.user_id!=undefined?chatThis(user.user_id,user.name,'near'):chatThis(user.group_id,user.name,'near')">
              <mu-avatar @click='user.user_id!=undefined?showPersonalPage(user.user_id,false):showPersonalPage(user.group_id,true)' :src="user.avatar" slot="leftAvatar"/>
              
                <span class='gdfhdfhder45' style="
    overflow: hidden;
        margin-right: 45px;
    display: block;
    text-overflow: ellipsis;
    white-space: nowrap;
    word-break: keep-all;
        color: #9d9d9d;
                ">dfhgdfjhdfgd是豆腐干反对fhgdfjhdfgdfhgdfjhdfg</span>
                
              <div style="position: relative; right: 10px;" slot="right">
                <span>23:09:12</span>
                <mu-badge content="3" secondary />
              </div>

              <mu-icon value="close" slot="right" @click.stop="closeThisChat(user.user_id,user.group_id)" title="点击删除"/>
            </mu-list-item>
          </mu-list>

          <!-- 我的好友 -->
          <mu-list v-if="active_tab=='friends'">
            <mu-list-item style="border-bottom:1px dotted #ccc;"  v-for="(user,index) in search(search_keywoyds)" :key="index" :title="user.nickname">
              <mu-avatar @click="showPersonalPage(user.user_id,false)" :src="user.avatar" slot="leftAvatar"/>
              <mu-icon value="chat_bubble" v-show="user.online" slot="right" @click="chatThis(user.user_id,user.nickname,'user')" title="点击@我哦"/>
            </mu-list-item>
          </mu-list>

          <!-- 我的群组 -->
          <mu-list class="group_list" v-if="active_tab=='groups'">
            <mu-list-item disabled toggleNested :open='false' style="border-bottom: 1px dotted rgb(204, 204, 204);"
              v-for="(group,index) in search(search_keywoyds)" :key="index" :title="group.name"
            >
                <mu-avatar @click="showPersonalPage(group.group_id,true)" :src="group.avatar" slot="leftAvatar" style="cursor:pointer"/>
                <mu-icon style="cursor:pointer" value="chat_bubble" slot="right"  title="点击开始群聊" @click.stop="chatThis(group.group_id,group.name,'group')"/>         
              <mu-list-item slot="nested" style="border-top:1px dotted #ccc;" title="机器人-小希">
                <mu-avatar :src="'/static/assets/avatar/1 (261).jpg'" slot="leftAvatar"/>
                <mu-icon style="cursor:pointer" value="chat_bubble" slot="right" @click="atThis(-1,'机器人-小希')" title="点击@我哦"/>
              </mu-list-item>

              <mu-list-item slot="nested" style="border-top:1px dotted #ccc;"  v-for="(user,index) in group.users" :key="index" :title="user.nickname">
                <mu-avatar @click="showPersonalPage(user.user_id,false)" :src="user.avatar" slot="leftAvatar"/>
                <mu-icon style="cursor:pointer" value="chat_bubble" slot="right" @click="atThis(user.fd,user.nickname)" title="点击@我哦"/>                
              </mu-list-item>
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
    <div style="    background-color: #f1f1f1;
    height: 30px;
    text-align: center;
    font-size: 20px;
    line-height: 30px;
    border-bottom: 2px solid #fff;" v-text="to_chat_nickname">
    </div>

    <mu-list-item :disableRipple="true" v-for="(chat,index) in active_chat_list" :key="index">
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
import Register from "./components/register";
import Alert from './components/alert';
import PersonalPage from './components/personalPage';

export default {
  name: "App",
  data() {
    return {
      message: "",
      at_map: {},
      show_user_id:'',
      // to: [],
      g_to:'',
      u_to:'',
      search_keywoyds: "",
      alert_open: false,
      show_personal_page: false,
      alert_msg: "",
      show_emoji: false,
      show_img: false,
      big_img: "",
      show_help: false,
      show_send_img_confirm: false,
      base64_img: "",
      active_tab:'nearchat',
      to_chat_nickname:'',
      active_near_user:'',
      active_chat_list:[],
      is_group:false,
      is_register:false
    };
  },
  components: {
    Helper,
    Login,
    Alert,
    PersonalPage,
    Register
  },
  methods: {
    test(a){
      alert(a)
    },
    handleTabChange(val){
      this.active_tab=val
    },
    closeHelper() {
      this.show_help = false;
    },
    // @这个人
    atThis(fd,nickname){

    },
    // 显示大图
    showBigImg(img_path) {
      this.show_img = true;
      this.big_img = img_path;
    },
    // 显示个人主页
    showPersonalPage(user_id,is_group=false){
      this.show_personal_page=true
      this.show_user_id=user_id
      this.is_group=is_group
    },
    createResponseHtmlTag(chat) {
      if (chat.type === "text") {
        var tag = chat.message;
      } else if (chat.type == "url") {
        var tag = '<a target="_blank" href="' + chat.message + '">点击查看</a>';
      } else if (chat.type == "img") {
        var tag = this.createImgTag(chat.message);
      }else{
        var tag = chat.message;
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
    // 上传图片
    uploadImageHandle(param, config) {
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
              avatar_id: this.myself.info.avatar_id,
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
    },  
    // 关闭聊天窗口
    closeThisChat(uid,gid){
      if(uid!=undefined){
        var index = this.near_chat.findIndex(item => {
            if (item.user_id == uid) {
              this.u_to='';
                return true;
            }
        });
      }
      if(gid!=undefined){
        var index = this.near_chat.findIndex(item => {
            if (item.group_id == gid) {
              this.g_to='';
                return true;
            }
        });
      }
      console.log(index);
      
      if(index!==-1){
        if(this.to_chat_nickname==this.near_chat[index].name){
          this.to_chat_nickname=''
          console.log(this.to_chat_nickname)
        }
        this.$store.commit('remove_near_chat',index)
      }
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
        this.uploadImageHandle(param, config);
      }
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
    chatThis(id, name,flag) {
      this.active_tab='nearchat'
      console.log(id)
      var near=null;
      this.to_chat_nickname=name
      this.active_near_user=id
      if(flag!='near'){
        near={
          name:name,
          avatar_id:45,
        }
      }
      if(flag=='group'){
        var tmp='group_'+id;
        this.active_chat_list=eval('this.chat_list.group.'+tmp)
        this.g_to=id;
        near.group_id=id
      }else if(flag=='user'){
        var tmp='user_'+id;
        this.active_chat_list=eval('this.chat_list.user.'+tmp)
        this.u_to=id;
        near.user_id=id        
      }
      
      if(flag!='near'){
        for (var tmpp in this.near_chat) {
          if(this.near_chat[tmpp].user_id==id || this.near_chat[tmpp].group_id==id){
            return false;
          }
        }
        this.$store.commit('add_near_chat',near)
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
        alert("网络连接失败，请刷新");
        return false;
      }
    },
    // 搜索在线人员
    search(search_keywoyds) {
      // 搜索好友
      if(this.active_tab=='friends'){
        return this.friends.filter(user => {
          // 判断字符串是否在
          if(user.user_id!=this.myself.info.user_id){
            if (user.nickname.includes(search_keywoyds)) {
              return user;
            }
          }
        });
      }else if(this.active_tab=="groups"){
        return this.groups.filter(group => {
          console.log(group)
          // 判断字符串是否在
          if (group.name.includes(search_keywoyds)) {
            return group;
          }
        });
      }
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
      var param = {
        img: this.base64_img,
        "submission-type": "paste"
      };
      this.show_send_img_confirm = false;
      this.base64_img = "";
      this.uploadImageHandle(this.$qs.stringify(param), {});
    }
  },
  computed: {
    ...mapState([ "friends","groups", "chat_list", "myself", "is_login","near_chat"]),
    searchPlaceHolder:function(){
      if(this.active_tab=='groups'){
        return '搜索群组';
      }else if(this.active_tab=='friends'){
        return '搜索好友';
      }else if(this.active_tab=='nearchat'){
        return '搜索最近联系人'
      }
    }
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
              //可以在这里写上传逻辑 直接将base64编码的字符串上传（可以尝试传入blob对象，看看后台程序能否解析）
              this.base64_img = base64_str;
              this.show_send_img_confirm = true;
              // this.uploadBase64Image(base64_str);
            };
            reader.readAsDataURL(blob);
          }
        }
      }
    });
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
.mu-tabs{
  background-color: #f1f1f1!important;
}
.mu-tab-active {
    color: #7e57c2!important;
}
.mu-tab-link{
  color:#000;
}
.active_near_user{
  background-color: #7e57c2;
}
.active_near_user .mu-item.has-avatar{
  color:#fff;
}

.active_near_user .mu-item-right{
  color:#fff;
}
.group_list .mu-item-right{
  right:25px;
}
.nearsdgsdfg .mu-item-right{
  right:30px
}
.nearsdgsdfg .active_near_user .gdfhdfhder45{
  color:rgba(255, 255, 255, 0.74)!important;
}
.mu-item-wrapper:hover{
  background-color:rgba(0,0,0,.1);
}
</style>
