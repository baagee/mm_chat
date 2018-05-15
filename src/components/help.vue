<template>
    <div class="login_box" style="text-align:left">
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
    <h3>5：如何更换头像？</h3>
    <p>
      在登录页点击头像进入头像选择，然后点击中意的头像就ok啦
    </p>
</div>
<br>
<div style="text-align:center">
      <mu-raised-button label="我了解啦" @click='closeHelper()' primary/>
    </div>
  </div>
</template>

<script>
export default {
  name: "helper",
  data() {
    return {};
  },
  methods: {
    closeHelper() {
      this.$emit("closeHelper");
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
