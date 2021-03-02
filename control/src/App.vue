<template>
  <v-app id="inspire">
    <!-- Notifications -->
    <component
      ref="notifications"
      v-if="$root.app_notifications"
      :is="$root.app_notifications"
      :alerts="$root.alerts"
    ></component>

    <!-- Loader -->
    <component
      ref="loader"
      :is="$root.app_loader"
      v-if="$root.app_loader && $root.loader.visible"
      :message="$root.loader.message"
    ></component>

    <!-- Navigator -->
    <component ref="navigator" v-if="$root.app_navigator" :is="$root.app_navigator"></component>

    <!-- Page Body -->
    <router-view></router-view>

    <!-- Footer -->
    <component ref="footer" v-if="$root.app_footer" :is="$root.app_footer"></component>
  </v-app>
</template>

<script>
export default {
  name: "App",
  created() {
  },
};
</script>
