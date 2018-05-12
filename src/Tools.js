function _convert(str) {
    str = str.replace(/\</g, '&lt;');
    str = str.replace(/\>/g, '&gt;');
    str = str.replace(/\n/g, '<br/>');
    str = str.replace(/\[emoji_([0-9]*)\]/g, '<img src="/static/assets/emoji/1 ($1).gif"/>');
    return str;
}

function _notice(message,icon) {
    if (!window.Notification) {
        console.log("浏览器不支持通知！");
    }
    console.log(window.Notification.permission);
    if (window.Notification.permission != 'granted') {
        Notification.requestPermission(function (status) {
            //status是授权状态，如果用户允许显示桌面通知，则status为'granted'
            console.log('status: ' + status);
            //permission只读属性:
            //  default 用户没有接收或拒绝授权 不能显示通知
            //  granted 用户接受授权 允许显示通知
            //  denied  用户拒绝授权 不允许显示通知
            var permission = Notification.permission;
            console.log('permission: ' + permission);
        });
    }else if(Notification.permission == "granted"){
        var n = new Notification("有人@你哦！", { "icon": icon, "body": message });
        n.onshow = function () {
            console.log("显示通知");
            setTimeout(function () { n.close() }, 3000);
        };
        // n.onclick = function () {
        //   alert("打开相关视图");
        //   window.open("/Home/about");
        //   n.close();
        // };
        n.onclose = function () { console.log("通知关闭"); };
        n.onerror = function () {
            console.log('产生错误'); //do something useful 
        };
    }
}

export default {
    convert: _convert,
    notice:_notice
}