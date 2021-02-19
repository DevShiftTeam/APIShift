<script>
/**
 * APIShift Engine v1.0.0
 *
 * Copyright 2020-present Sapir Shemer, DevShift (devshift.biz)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @author Sapir Shemer
 */

// This shit is made for scripting
module.exports = {
  data() {
    return {
      drawer: 1,
      pages: {},
      index: 10,
    };
  },
  created() {
    window.nav_holder = this;
    // Load control panel pages
    this.updatePages();
  },
  computed: {
    showNavBar: {
      get: function () {
        return this.drawer > 0;
      },
      set: function (newValue) {
        this.drawer = newValue;
      },
    },
    noParentPages() {
      if (this.pages) {
        return Object.filter(this.pages, (f) => f.parent === 0);
      }
      return [];
    },
  },
  methods: {
    updateIndex: function (new_index) {
      this.index = new_index;
    },
    updatePages: function () {
      APIShift.API.request(
        "Admin\\Control",
        "getPages",
        {},
        function (response) {
          if (response.status == APIShift.API.status_codes.SUCCESS) {
            nav_holder.pages = Object.assign({}, response.data);
            // Add routes
            for (let item in response.data) {
              // Construct path to page
              if (response.data[item].parent === 0) {
                APIShift.admin_routes.push({
                  path: "/" + response.data[item].path,
                  component: APIShift.API.getPage(
                    response.data[item].path,
                    true
                  ),
                });
              } else {
                let routeData = APIShift.admin_routes.find(
                  (r) =>
                    r.path ===
                    "/" + response.data[response.data[item].parent].path
                );
                if (routeData.children === undefined) routeData.children = [];
                routeData.children.push({
                  path: response.data[item].path,
                  component: APIShift.API.getPage(
                    response.data[response.data[item].parent].path +
                      "/" +
                      response.data[item].path,
                    true
                  ),
                });
              }
            }
            // Update routes
            app.$router.addRoutes(APIShift.admin_routes);
          } else {
            APIShift.API.notify(
              APIShift.API.getStatusName(response.status) +
                ": " +
                response.data,
              "error"
            );
          }
        }
      );
    },
    toggleDarkTheme: function () {
      window.app.$vuetify.theme.dark = !window.app.$vuetify.theme.dark;
    },
    isOnDarkMode: function () {
      return window.app.$vuetify.theme.dark;
    },
    logout: function () {
      APIShift.API.request(
        "Main\\SessionState",
        "changeState",
        { state: "DEFAULT_VIEWER" },
        function (response) {
          if (response.status == 1) {
            APIShift.API.notify("Logged out", "success");
            // Load login screen
            APIShift.logged_in = false;
            app.$router.push("login");
            window.app.app_loader = null;
            window.app.app_footer = null;
            window.app.app_navigator = null;
          } else {
            APIShift.API.notify(
              APIShift.API.getStatusName(response.status) +
                ": " +
                response.data,
              "error"
            );
          }
        }
      );
    },
  },
};
</script>

<template>
  <div>
    <v-app-bar app clipped-left>
      <v-app-bar-nav-icon
        @click.stop="drawer = (drawer + 1) % 3"
      ></v-app-bar-nav-icon>
      <v-toolbar-title>
        <v-avatar tile style="height: auto; margin-right: 10px">
          <img src="../images/apishift-logo.png" alt="APIShift" />
        </v-avatar>
        APIShift Control Panel
      </v-toolbar-title>
      <v-btn
        class="lightbulb"
        icon
        large
        target="_blank"
        v-on:click="toggleDarkTheme()"
      >
        <v-icon v-if="isOnDarkMode()">fas fa-lightbulb</v-icon>
        <v-icon v-else>fas fa-moon</v-icon>
      </v-btn>
    </v-app-bar>

    <v-navigation-drawer
      v-model="showNavBar"
      app
      clipped
      :mini-variant="drawer != 1"
      :style="{ 'z-index': index }"
    >
      <v-list dense>
        <!-- Home option -->
        <v-list-item link to="/main">
          <v-list-item-action>
            <v-icon class="mdi-home">mdi-home</v-icon>
          </v-list-item-action>
          <v-list-item-content>
            <v-list-item-title> Dashboard </v-list-item-title>
          </v-list-item-content>
        </v-list-item>
        <v-divider class="my-2"></v-divider>

        <v-list-item
          v-for="page in noParentPages"
          :key="page.id"
          link
          :to="'/' + page.path"
        >
          <v-list-item-action>
            <v-icon>{{ page.icon }}</v-icon>
          </v-list-item-action>
          <v-list-item-content>
            <v-list-item-title>
              {{ page.name }}
            </v-list-item-title>
          </v-list-item-content>
        </v-list-item>

        <!-- Logout option -->
        <v-divider class="my-2"></v-divider>
        <v-list-item link @click="logout()">
          <v-list-item-action>
            <v-icon>fas fa-power-off</v-icon>
          </v-list-item-action>
          <v-list-item-content>
            <v-list-item-title> Logout </v-list-item-title>
          </v-list-item-content>
        </v-list-item>
      </v-list>
    </v-navigation-drawer>
  </div>
</template>

<style scoped>
/* Please style this crap, with style */
.lightbulb {
  position: absolute;
  right: 30px;
}
</style>
