<template>
  <div v-if="isUat" role="note" style="background:#fff3cb;color:#66501b;padding:7px 16px;text-align:center;font-size:12px">SOFEP UAT cô lập · Dữ liệu thử nghiệm · Không phải website chính</div>
  <component :is="layout">
    <router-view></router-view>
  </component>
</template>

<script>
const default_layout = "default";
export default {
  name: 'App',
  data: () => ({ isUat: import.meta.env.VITE_ENGAGEMENT_UAT === 'true' }),
  computed: {
    layout() {
      return (this.$route.meta.layout || default_layout) + "-layout";
    },
  },
};
</script>

<style>
</style>