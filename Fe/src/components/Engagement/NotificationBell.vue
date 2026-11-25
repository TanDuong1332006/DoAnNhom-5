<template><router-link class="btn btn-light rounded-circle position-relative border-0 p-2" to="/thong-bao" aria-label="Thông báo của tôi" title="Thông báo"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"/></svg><span v-if="unread" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-dark" style="font-size:9px">{{ unread>99?'99+':unread }}</span></router-link></template>
<script>
import { engagementApi } from '../../services/engagement';
import { customerSession } from '../../services/authSessions';
export default {
  data:()=>({unread:0,timer:null}),
  watch:{'$route.fullPath'(){this.refresh();}},
  mounted(){this.refresh();this.timer=setInterval(()=>this.refresh(),60000);window.addEventListener('sofep-inbox-changed',this.refresh);window.addEventListener('storage',this.onStorage);},
  beforeUnmount(){clearInterval(this.timer);window.removeEventListener('sofep-inbox-changed',this.refresh);window.removeEventListener('storage',this.onStorage);},
  methods:{onStorage(event){if(['customer_token','customer_user','auth_token'].includes(event.key))this.refresh();},async refresh(){const token=customerSession()?.token;if(!token){this.unread=0;return;}try{const {data}=await engagementApi('client/thong-bao/count');if(customerSession()?.token===token)this.unread=data.unread;}catch{if(customerSession()?.token===token)this.unread=0;}}}
};
</script>
