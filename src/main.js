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
  // const HOST = 'chat.baagee.vip'
  const HOST = '192.168.117.142'
  const BASE_URL = 'http://xxsphp.vm/api'
  Axios.defaults.baseURL = BASE_URL
  Axios.defaults.withCredentials = true
  Vue.prototype.$axios = Axios


  Vue.use(Vuex);

  const store = new Vuex.Store({
    state: {
      is_login: false,
      socket:null,
      friends: [
        {
          nickname: '哈哈哈',
          avatar: 'dgsdgfd',
          user_id: 52,
          fd: 9,
          online: true,
        },
        {
          nickname: '嘿嘿嘿',
          avatar: 'dgsdgfd',
          user_id: 22,
          fd: null,// 只有在线用户才有fd
          online: false,
        }
      ],
      groups: [
        {
          name: '分组1',
          group_id: 90,
          avatar: 'sfghsdfhd',
          users: [
            {
              nickname: '嘿嘿嘿',
              avatar: 'dgsdgfd',
              user_id: 22,
              fd: null,
              online: true,
              // is_friend:true
            },
            {
              nickname: '8883',
              avatar: 'dgsdgfd',
              user_id: 56,
              fd: 67,
              online: false,
              // is_friend:false              
            },
          ]
        }
      ],
      myself: {
        info: {
        },
      },
      chat_list: {
        'group': {
          group_166: [
            {
              'nickname': '哈哈哈',
              avatar: '23',
              time: '09:12:23',
              user_id: 1,
              message: '合适的过得好不好山东话啥的格式',
              self:false
            },
            {
              'nickname': '我自己',
              avatar: '23',
              time: '09:12:23',
              user_id: 1,
              message: '合适的过得好不好山东话啥的格式',
              self:true              
            },
            {
              'nickname': '嘿嘿嘿',
              avatar: '23',
              time: '09:12:23',
              user_id: 2,
              message: '合适的过得好不好山东话啥的格式',
              self:false                            
            },
          ]
        },
        'user': {
          user_52: [
            {
              nickname: '哈哈哈',
              avatar: '23',
              time: '09:12:23',
              user_id: 1,
              message: '合适的过得好不好山东话啥的格式',
              self:false
            },
            {
              nickname: '我自己',
              avatar: '23',
              time: '09:19:23',
              user_id: 14,
              message: 'hasdjghsdf发生的风格说得很好士大夫就更好了开始',
              self:true
            }
          ],
          user_166: [
            {
              nickname: '嘿嘿嘿',
              avatar: '23',
              time: '09:12:23',
              user_id: 12,
              message: '合适的过得好不好山东话啥的格式',
              self:false
            }
          ],
        }
      },
      near_chat: [
        {
          name: '哈哈哈',
          avatar: 'http://localhost:8080/static/assets/avatar/1%20(248).jpg',
          user_id: 52,
          group_id: null,
        },
        {
          name: '哈分组1',
          avatar: 'http://localhost:8080/static/assets/avatar/1%20(249).jpg',
          user_id: null,
          group_id: 166,
        },
      ]
    },
    mutations: {
      add_online_friends(state, user) {
        // state.friends.unshift(user)
      },
      add_chat_msg(state, chat_msg) {
        state.chat_list.push(chat_msg)
      },
      add_near_chat(state, chat) {
        state.near_chat.unshift(chat)
      },
      remove_near_chat(state, index) {
        state.near_chat.splice(index, 1);
      },
      set_myself_info(state, myself_info) {
        state.myself.info = myself_info
      },
      set_is_login(state, is_login) {
        state.is_login = is_login
      },
      // 登录后连接websocket服务器
      open_socket(state,user_info){
        var ws = 'ws://' + HOST + ':8989'
        var socket = new WebSocket(ws)
        state.socket=socket
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
          console.log('连接成功',user_info)
          if (socket.readyState == 1) {
            user_info.action='login';
            console.log("登录发送的数据：", user_info);
            socket.send(JSON.stringify(user_info));
          } else {
            alert("网络连接失败，请刷新");
            return false;
          }
        }

        socket.onmessage = function (event) {
            console.log('获取到信息')
            var getMsg = JSON.parse(event.data)
            console.log(getMsg)
        }
      }
    }
  })

  // var ws = 'ws://' + HOST + ':8989'
  // var socket = new WebSocket(ws)
  // socket.onopen = function (event) {
  //   var interval = setInterval(function () {
  //     // 定时发送心跳包
  //     if (socket.readyState == 1) {
  //       socket.send(JSON.stringify({
  //         action: 'heart'
  //       }))
  //     } else {
  //       clearInterval(interval)
  //     }
  //   }, 8000)
  //   console.log('连接成功')
  //   // 获取缓存昵称
  //   var my_nickname = localStorage.getItem("my_nickname");
  //   var avatar_id = localStorage.getItem("avatar_id");
  //   if (my_nickname != null) {
  //     if (socket.readyState == 1) {
  //       var login_data = {
  //         action: "login",
  //         nickname: my_nickname,
  //         avatar_id: avatar_id
  //       };
  //       console.log("登录发送的数据：", login_data);
  //       socket.send(JSON.stringify(login_data));
  //     } else {
  //       alert("网络连接失败，请刷新");
  //       return false;
  //     }
  //   }
  // }

  // socket.onmessage = function (event) {
  //   console.log('获取到信息')
  //   var getMsg = JSON.parse(event.data)
  //   console.log(getMsg)
  //   if (getMsg.action == 'login' && store.state.is_login == false) {
  //     // 登录的消息
  //     var online_users = getMsg.online_users;
  //     var user = {};
  //     online_users.forEach(user => {
  //       user = JSON.parse(user);
  //       // store.commit("add_online_user", user);
  //     });
  //     store.commit("set_is_login", true);
  //     store.commit("set_myself_info", getMsg.myself);
  //   } else if (store.state.is_login == true) {
  //     if (getMsg.action == 'logout_other') {
  //       // 其他人下线 查找索引 刪除
  //       var index = store.state.online_users.findIndex(user => {
  //         if (user.user_id == getMsg.user_id) {
  //           return true;
  //         }
  //       });
  //       if (index !== -1) {
  //         Toast('网友:' + store.state.online_users[index].nickname + ' 下线');
  //         store.state.online_users.splice(index, 1);
  //       }
  //     } else if (getMsg.action == 'user_online') {
  //       // 有人上线
  //       var index = store.state.online_users.findIndex(user => {
  //         if (user.user_id == getMsg.user_id) {
  //           return true;
  //         }
  //       });
  //       if (index === -1) {
  //         store.commit("add_online_user", getMsg.user_info);
  //         Toast('新网友:' + getMsg.user_info.nickname + ' 上线');
  //       } else {
  //         console.log('此user_id 已经在列表里了')
  //       }
  //     } else if (getMsg.action = 'chat') {
  //       // 收到消息
  //       if (getMsg.message.message.indexOf('[img]:') === 0) {
  //         getMsg.message.type = 'img'
  //         // var img_url = BASE_URL + getMsg.message.message.substring(7)
  //         var img_url = getMsg.message.message.substring(6)
  //         getMsg.message.message = img_url
  //       } else if (getMsg.message.message.indexOf('[url]:') !== -1) {
  //         getMsg.message.type = 'url';
  //         getMsg.message.message = getMsg.message.message.substring(6)
  //       } else {
  //         getMsg.message.type = 'text'
  //         // 解析表情
  //         getMsg.message.message = Tools.convert(getMsg.message.message)
  //       }
  //       if (getMsg.message.message.indexOf('@' + store.state.myself.info.nickname) !== -1) {
  //         getMsg.message['at_you'] = true;
  //       }
  //       if ('at_you' in getMsg.message) {
  //         Tools.notice(getMsg.message.nickname + ' 给你发了一条消息，注意查看哦^_^', '/static/assets/avatar/1 (' + getMsg.message.avatar_id + ').jpg');
  //       }
  //       store.commit("add_chat_msg", getMsg.message);
  //     }
  //   }
  //   // todo 重连
  // }
  // socket.onclose = function (event) {
  //   console.log('socket.onclose 连接关闭,退出登录')
  //   store.commit("set_is_login", false);
  // }

  // socket.onerror = function (event, error) {
  //   console.log(error)
  // }
  // Vue.prototype.$socket = socket

  Vue.use(MuseUI)

  Vue.config.productionTip = false

  /* eslint-disable no-new */
  new Vue({
    el: '#app',
    store,
    components: { App },
    template: '<App/>'
  })
}
