<template>
    <div>
        <mu-dialog :open="true" :title="userData.nickname+' - 个人信息'" scrollable  @close="closePersonalPage()">
            <div class="index" style="padding-top: 10px; width:400px">
                <div class="top" :style="{backgroundImage: `url(${userData.avatar})`}">
                <div class="c">
                    <mu-avatar :src="userData.avatar"
                            :size="96" />
                    <span class="name">{{userData.nickname}}</span>
                </div>
                <hr style="height:1px;border:none;border-top:1px dashed #9c9c9c;;"/>
                
                </div>
                <div class="content">
                    <div class="item">
                        <div>
                        <mu-list-item title="性别"
                                        :describeText="userData.phone"
                                        disabled>
                            <mu-icon value="voicemail"
                                    color="#2e2c6b"
                                    slot="left" />
                        </mu-list-item>
                        </div>
                    </div>
                
                    <div class="item">
                        <div>
                        <mu-list-item title="地址"
                                        :describeText="userData.address"
                                        disabled>
                            <mu-icon value="location_on"
                                    color="#2e2c6b"
                                    slot="left" />
                        </mu-list-item>
                        </div>
                    </div>
                
                    <div class="item">
                        <div>
                        <mu-list-item title="生日"
                                        :describeText="userData.birthday"
                                        disabled>
                            <mu-icon value="cake"
                                    color="#2e2c6b"
                                    slot="left" />
                        </mu-list-item>
                        </div>
                    </div>

                    <div class="item">
                        <div>
                        <mu-list-item title="标签"
                                        :describeText="userData.tags"
                                        disabled>
                            <mu-icon value="cake"
                                    color="#2e2c6b"
                                    slot="left" />
                        </mu-list-item>
                        </div>
                    </div>

                    <div class="item">
                        <div>
                        <mu-list-item title="简介"
                                        :describeText="userData.remark"
                                        disabled>
                            <mu-icon value="cake"
                                    color="#2e2c6b"
                                    slot="left" />
                        </mu-list-item>
                        </div>
                    </div>
                </div>
                <hr style="height:1px;border:none;border-top:1px dashed #9c9c9c;margin-bottom: 15px;"/>
                <div>
                    <mu-raised-button label="聊天" @click="chatThis(user_id,userData.nickname,is_group?'group':'user')" class="demo-raised-button" primary style="width:49%"/>
                    <mu-raised-button label="删除" class="demo-raised-button" secondary style="width:49%"/>
                </div>
            </div>
        </mu-dialog>
    </div>
</template>

<script>
    export default{
        name:"PersonalPage",
        props:['user_id','is_group'],
        data(){
            return {
                userData:{
                    nickname:'的方式',
                    birthday:'2019:09:09',
                    address:'sdfghdf',
                    avatar:'http://localhost:8080/static/assets/avatar/1%20(45).jpg',
                    tags:'4534,3645,45675',
                    remark:'无俄日呕吐问题已经俄洛伊回家诶而退藕越塔家咯你无俄日呕吐问题已经俄洛伊回家诶而退藕越塔家咯你无俄日呕吐问题已经俄洛伊回家诶而退藕越塔家咯你无俄日呕吐问题已经俄洛伊回家诶而退藕越塔家咯你'
                }
            }
        },
        methods:{
            closePersonalPage(){
                this.$emit('closePersonalPage')
            },
            chatThis(id,name,flag){
                this.$emit('closePersonalPage')                
                this.$emit('chatThis',id,name,flag)
            }
        },
        created(){
            console.log('获取个人信息,user_id='+this.user_id+' is_group='+this.is_group)
        }
    }
</script>

<style scoped>
.top{
    position: relative;
    /* background-image: url('http://localhost:8080/static/assets/avatar/1%20(45).jpg'); */
    background-size: cover
}
.c{
    width: 100%;
    text-align: center;
}
.name{
    display:block;
    color:#000;
}
.mu-tab-link-highlight{
    display: none!important;
    background-color: #ffffff;
}
</style>


