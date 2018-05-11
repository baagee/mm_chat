<template>
<div>
  <mu-appbar title="消息" style="position:fixed;top:0;">
  <!-- <mu-icon-button icon="menu" slot="left"/> -->
  <!-- <mu-flat-button label="expand_more" slot="right"/> -->
  <!-- <mu-icon-button icon="expand_more" slot="right"/> -->
</mu-appbar>


  <mu-list style="margin: 60px 0;">
    <mu-sub-header>最近聊天记录</mu-sub-header>
  
    <mu-list-item title="Mike Li" to="/chat">
      <mu-avatar src="/static/assets/logo.png" slot="leftAvatar"/>
      <span slot="describe">
                        <span style="color: rgba(0, 0, 0, .5)">是地方官史蒂夫</span>
        </span>
        <!--时间与待处理-->
        <div class="item-right"
             slot="right">
          <!--获取到当前聊天队列，最后一条内容的time-->
          <span class="time">23:09</span>
          <!--数据条数-->
          <!--数据需求是为字符串-->
          <mu-badge :content="count" />
        </div>
      <!-- <mu-icon value="chat_bubble" slot="right"/> -->
    </mu-list-item>

  </mu-list>

</div>
</template>

<script>
import { mapState } from 'vuex'
export default {
  name: 'Chat',
  data () {
    return {
      isSwipe: [false, false, false],
      nowMessageList:[
        {
          _id:1,
          avatar:'',
          name:'哈哈'
        }
      ]
    }
  },
  methods:{},
  components:{},
   computed: {
     ...mapState(['count'])
   }
}
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
h1, h2 {
  font-weight: normal;
}
ul {
  list-style-type: none;
  padding: 0;
}
li {
  display: inline-block;
  margin: 0 10px;
}
a {
  color: #42b983;
}
</style>
