<template>
    
  <mu-dialog :open="alert_open" title="提示">
      {{alert_msg}}
    <mu-flat-button label="确定" slot="actions" primary @click="close()"/>
  </mu-dialog>
</template>
    
<script>
    export default{
        name:'alert',
        data(){
            return {
                
            }
        },
        props:['alert_open','alert_msg'],
        methods:{
            close(){
                this.$emit('closeAlert')
            }
        }
    }
</script>


