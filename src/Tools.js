function _convert(str) {
    str = str.replace(/\</g, '&lt;');
    str = str.replace(/\>/g, '&gt;');
    str = str.replace(/\n/g, '<br/>');
    str = str.replace(/\[emoji_([0-9]*)\]/g, '<img src="/static/assets/emoji/1 ($1).gif"/>');
    return str;
}

export default {
    convert: _convert
}