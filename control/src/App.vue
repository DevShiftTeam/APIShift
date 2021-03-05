<template>
  <v-app id="inspire">
    <!-- Notifications -->
    <component
      ref="notifications"
      v-if="app_notifications"
      :is="app_notifications"
      :alerts="alerts"
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
    <component ref="footer" v-if="app_footer_name" :is="app_footer"></component>
  </v-app>
</template>

<script>
import { APIShift as APIShiftClass } from "./scripts/APIShift.js";
import Vue from "vue";
export default {
  name: "App",
  data: () => ({
    apishift: null,
    app_notifications: Vue.extend({ template: "<div></div>" }),
    // System alerts
    alerts: [],
    // Determines if loader is visible
  }),
  created() {
    // Initialize APIShift Engine
    this.apishift = new APIShiftClass(this.loader, "APIShift", this);
    window.APIShift = this.apishift;
    this.apishift.initialize();
    // Link components to apishift
    APIShift.Loader.load((resolve, reject) => {
      this.app_notifications = APIShift.API.getComponent("notifications");
      resolve(0);
    });

    APIShift.Loader.load((resolve, reject) => {
      // Add loaded routes & pages
      this.$router.addRoutes(APIShift.admin_routes);
      // Handle first load of page
      if (!this.apishift.installed) {
        this.apishift.setSubtitle("Installer");
        if (this.$route.path != "/installer") this.$router.push("/installer");
      } else if (!this.apishift.logged_in) {
        this.apishift.setSubtitle("Login");
        if (this.$route.path != "/login") this.$router.push("/login");
      } else if (this.$route.path == "/") this.$router.push("/main");

      //  Don't load other components if system isn't installed
      if (APIShift.load_components) {
        this.$store.commit("SET_LOADER", "loader");
        this.$store.commit("SET_NAVIGATOR", "navigator");
        this.$store.commit("SET_FOOTER", "footer");
      }

      // Navigation gaurd for control panel
      this.$router.beforeEach((to, from, next) => {
        // Move to installation if not installed
        if (to.path != "/installer" && !APIShift.installed) next("/installer");
        // Move to login if not authenticated
        else if (
          to.path != "/login" &&
          !APIShift.logged_in &&
          APIShift.installed
        )
          next("/login");
        else {
          // Construct current page title
          if (window.nav_holder !== undefined) {
            let page_holder = Object.values(nav_holder.pages).find(function(r) {
              if (r.parent == 0) return to.path === "/" + r.path;
              let parent_paths = to.path.split("/");
              return (
                parent_paths[1] == nav_holder.pages[r.parent].path &&
                parent_paths[2] == r.path
              );
            });
            if (page_holder !== undefined)
              this.apishift.setSubtitle(page_holder.name);
          }
          // Move to next page if everything's in place
          next();
        }
      });

      resolve(0);
    });
  },
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
    loader() {
      return this.$root.loader;
    },
  },
};
</script>
