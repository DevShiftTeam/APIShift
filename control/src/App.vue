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
      :is="app_loader"
      v-if="app_loader_name && loader.visible"
      :message="loader.message"
    ></component>

    <!-- Navigator -->
    <component
      ref="navigator"
      v-if="app_navigator_name"
      :is="app_navigator"
    ></component>

    <!-- Page Body -->
    <router-view></router-view>

    <!-- Footer -->
    <component
      ref="footer"
      v-if="app_footer_name"
      :is="app_footer"
    ></component>
  </v-app>
</template>

<script>
export default {
  name: "App",
  created() {},
  computed: {
    app_loader_name() {
      return this.$store.getters["LOADER"];
    },
    app_loader() {
      let loader = this.app_loader_name;
      if (loader) return APIShift.API.getComponent(loader);
      return null;
    },
    app_footer_name() {
      return this.$store.getters["FOOTER"];
    },
    app_footer() {
      let footer = this.app_footer_name;
      if (footer) return APIShift.API.getComponent(footer);
      return null;
    },
    app_navigator_name() {
      return this.$store.getters["NAVIGATOR"];
    },
    app_navigator() {
      let nav = this.app_navigator_name;
      if (nav) return APIShift.API.getComponent(nav);
      return null;
    },
    loader(){
        return this.$root.loader;
    }
  },
};
</script>
