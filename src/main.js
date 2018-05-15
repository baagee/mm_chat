// The Vue build version to load with the `import` command
// (runtime-only or standalone) has been set in webpack.base.conf with an alias.
import Vue from 'vue'
import App from './App'
import { Toast } from 'mint-ui';
import 'mint-ui/lib/style.css'
import Axios from 'axios'
import Qs from 'qs'
import Vuex from 'vuex'
import Tools from './Tools'
import MuseUI from 'muse-ui'
import 'muse-ui/dist/muse-ui.css'
if (navigator.userAgent.match(/(phone|pad|pod|iPhone|iPod|ios|iPad|Android|Mobile|BlackBerry|IEMobile|MQQBrowser|JUC|Fennec|wOSBrowser|BrowserNG|WebOS|Symbian|Windows Phone)/i)) {
  alert('请使用电脑访问');
  document.write('<h1 style="text-align:center;margin-top:20%;color:red">请使用电脑访问，谢谢</h1>');
} else {
  Vue.prototype.$qs = Qs

  // 在这里配置你的服务器地址
  const HOST = 'chat.baagee.vip'
  // const HOST = '192.168.117.142'
  const BASE_URL = 'http://' + HOST
  Axios.defaults.baseURL = BASE_URL
  Axios.defaults.withCredentials = true
  Vue.prototype.$axios = Axios


  Vue.use(Vuex);

  const store = new Vuex.Store({
    state: {
      is_login: false,
      online_users: [
      ],
      myself: {
        info: {
        },
      },
      chat_list: [
      ]
    },
    mutations: {
      add_online_user(state, user) {
        state.online_users.unshift(user)
      },
      add_chat_msg(state, chat_msg) {
        state.chat_list.push(chat_msg)
      },
      set_myself_info(state, myself_info) {
        state.myself.info = myself_info
      },
      set_is_login(state, is_login) {
        state.is_login = is_login
      }
    }
  })

  var ws = 'ws://' + HOST + ':8989'
  var socket = new WebSocket(ws)
  socket.onopen = function (event) {
    var interval = setInterval(function () {
      // 定时发送心跳包
      if (socket.readyState == 1) {
        socket.send(JSON.stringify({
          action: 'heart'
        }))
      } else {
        clearInterval(interval)
      }
    }, 8000)
    console.log('连接成功')
    // 获取缓存昵称
    var my_nickname = localStorage.getItem("my_nickname");
    var avatar_id = localStorage.getItem("avatar_id");
    if (my_nickname != null) {
      if (socket.readyState == 1) {
        var login_data = {
          action: "login",
          nickname: my_nickname,
          avatar_id: avatar_id
        };
        console.log("登录发送的数据：", login_data);
        socket.send(JSON.stringify(login_data));
      } else {
        alert("网络连接失败，请刷新");
        return false;
      }
    }
  }

  socket.onmessage = function (event) {
    console.log('获取到信息')
    var getMsg = JSON.parse(event.data)
    console.log(getMsg)
    if (getMsg.action == 'login' && store.state.is_login == false) {
      // 登录的消息
      var online_users = getMsg.online_users;
      var user = {};
      online_users.forEach(user => {
        user = JSON.parse(user);
        store.commit("add_online_user", user);
      });
      store.commit("set_is_login", true);
      store.commit("set_myself_info", getMsg.myself);
    } else if (store.state.is_login == true) {
      if (getMsg.action == 'logout_other') {
        // 其他人下线 查找索引 刪除
        var index = store.state.online_users.findIndex(user => {
          if (user.user_id == getMsg.user_id) {
            return true;
          }
        });
        if (index !== -1) {
          Toast('网友:' + store.state.online_users[index].nickname + ' 下线');
          store.state.online_users.splice(index, 1);
        }
      } else if (getMsg.action == 'user_online') {
        // 有人上线
        var index = store.state.online_users.findIndex(user => {
          if (user.user_id == getMsg.user_id) {
            return true;
          }
        });
        if (index === -1) {
          store.commit("add_online_user", getMsg.user_info);
          Toast('新网友:' + getMsg.user_info.nickname + ' 上线');
        } else {
          console.log('此user_id 已经在列表里了')
        }
      } else if (getMsg.action = 'chat') {
        // 收到消息
        if (getMsg.message.message.indexOf('[img]:') === 0) {
          getMsg.message.type = 'img'
          // var img_url = BASE_URL + getMsg.message.message.substring(7)
          var img_url = getMsg.message.message.substring(6)
          getMsg.message.message = img_url
        } else if (getMsg.message.message.indexOf('[url]:') !== -1) {
          getMsg.message.type = 'url';
          getMsg.message.message = getMsg.message.message.substring(6)
        } else {
          getMsg.message.type = 'text'
          // 解析表情
          getMsg.message.message = Tools.convert(getMsg.message.message)
        }
        if (getMsg.message.message.indexOf('@'+store.state.myself.info.nickname) !== -1) {
          getMsg.message['at_you'] = true;
        }
        if ('at_you' in getMsg.message) {
          Tools.notice(getMsg.message.nickname + ' 给你发了一条消息，注意查看哦^_^', '/static/assets/avatar/1 (' + getMsg.message.avatar_id + ').jpg');
        }
        store.commit("add_chat_msg", getMsg.message);
      }
    }
    // todo 重连
  }
  socket.onclose = function (event) {
    console.log('socket.onclose 连接关闭,退出登录')
    store.commit("set_is_login", false);
  }

  socket.onerror = function (event, error) {
    console.log(error)
  }
  Vue.prototype.$socket = socket

  Vue.use(MuseUI)

  Vue.config.productionTip = false

  /* eslint-disable no-new */
  new Vue({
    el: '#app',
    // router,
    store,
    components: { App },
    template: '<App/>'
  })
}
