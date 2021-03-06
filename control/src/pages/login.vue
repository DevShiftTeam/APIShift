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
export default {
  data() {
    return {
      drawer: null,
      // Installation steps data
      credentials: {
        name: "APIShift CP Login",
        fields: [
          // regex is used for validating the data before sending it to server for further validation
          {
            label: "Username",
            name: "login",
            icon: "mdi-account",
            type: "text",
            data: "",
          },
          {
            label: "Password",
            name: "password",
            icon: "mdi-lock",
            type: "password",
            data: "",
            regex:
              "^(((?=.*[a-z])(?=.*[A-Z]))|((?=.*[a-z])(?=.*[0-9]))|((?=.*[A-Z])(?=.*[0-9])))(?=.{8,})",
            format: "At least 8 characters, with numbers and characters",
          },
        ],
      },
      // Installers personal loader
      loader: {
        visible: false,
        message: "",
        processes: 0,
      },
      // Determines whether an action concerning stage check or jump occurs
      step_action: false,
    };
  },
  created() {
    if (APIShift.logged_in) app.$router.push("/main");
    APIShift.setSubtitle("Login");
  },
  updated() {
    // If step action accurred, then focus on the selected field
    // Should happen after DOM re-render
    if (this.step_action) {
      this.focusOnIncorrectField();
      this.step_action = false;
    }
  },
  methods: {
    /**
     * Check if current step data is filled, returns the incorrect field
     * We call it the incorrect step, so that algorithmically we already know to which field to focus on
     * for the user
     * @param {Number} step_id The step ID to check
     * @param {boolean} update Update the user using a notification
     * @returns The ID of the incorrect field or -1 when all are corrent
     */
    findIncorrectField: function(update = true) {
      var password_check = "";

      for (var field_id in this.credentials.fields) {
        var field = this.credentials.fields[field_id];

        // Not empty
        if (field.data == "") {
          if (update)
            APIShift.API.notify(
              "Please fill in all data correctly first",
              "warning"
            );
          return field_id; // return field who is incorrect or empty
        }

        // Regex invalid
        if (
          field.regex !== undefined &&
          !RegExp(field.regex).test(field.data)
        ) {
          if (update)
            APIShift.API.notify(
              "Invalid format in " + field.label + ": " + field.format,
              "warning"
            );
          return field_id; // return field who is incorrect or empty
        }
      }

      return -1;
    },
    focusOnIncorrectField: function(handler = this) {
      var field_id = this.findIncorrectField(false);
      if (field_id != -1)
        $("#" + handler.credentials.fields[field_id].name).focus();
      else $("#" + handler.credentials.fields[0].name).focus();
    },
    /**
     * Check if all data is filled and ready to submit
     */
    isDataFilled: function() {
      if (this.findIncorrectField(false) != -1) return false;
      return true;
    },
    login: function() {
      // Construct data to send
      var to_send = {};

      // Validate data
      if (!this.isDataFilled()) return;

      // Construct message
      for (var field_id in this.credentials.fields)
        to_send[
          this.credentials.fields[field_id].name
        ] = this.credentials.fields[field_id].data;
      to_send["state"] = "ADMIN_STATE"; //  Request to login in admin state

      // Handle login
      APIShift.API.request(
        "Main\\SessionState",
        "changeState",
        to_send,
        (response) => {
          if (response.status == APIShift.API.status_codes.ERROR) {
            APIShift.API.notify("Error: " + response.data, "error");
          } else if (response.status == APIShift.API.status_codes.SUCCESS) {
            APIShift.API.notify("Success! :) " + response.data, "success");
            // Re-initialize devshift system to logged id state
            APIShift.initialize();
            APIShift.Loader.load((resolve, reject) => {
            //   APIShift.logged_in = true;
              this.$store.commit("auth/SET", true);
              app.$router.push("/main");
              this.$store.commit("SET_LOADER", "loader");
              this.$store.commit("SET_NAVIGATOR", "navigator");
              this.$store.commit("SET_FOOTER", "footer");
              resolve(0);
            });
          } else
            APIShift.API.notify(
              APIShift.API.getStatusName(response.status) +
                ": " +
                response.data,
              "error"
            );
        }
      );
    },
    toggleDarkTheme: function() {
      window.app.$vuetify.theme.dark = !window.app.$vuetify.theme.dark;
    },
    isOnDarkMode: function() {
      return window.app.$vuetify.theme.dark;
    },
  },
};
</script>

<template>
  <v-main>
    <v-container fluid fill-height>
      <v-layout align-center justify-center>
        <v-flex xs12 sm8 md4>
          <v-card class="elevation-12">
            <v-toolbar>
              <v-toolbar-title>{{ credentials.name }}</v-toolbar-title>
              <v-spacer></v-spacer>

              <v-tooltip right>
                <template v-slot:activator="{}">
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
                </template>
                <span>Toggle Dark Theme</span>
              </v-tooltip>

              <v-progress-linear
                :active="loader.visible"
                :indeterminate="loader.visible"
                :dismissible="
                  loader.dismissible !== undefined && loader.dismissible
                "
                absolute
                bottom
                color="primary"
              ></v-progress-linear>
            </v-toolbar>

            <v-card-text>
              <v-form>
                <v-text-field
                  v-for="field in credentials.fields"
                  :disabled="loader.visible"
                  :key="
                    field.label /* Need this to satisfy the for loop, shitty error of vue in our situation, but let's keep this clean */
                  "
                  @keyup.enter="login()"
                  :label="field.label"
                  :id="field.name"
                  :name="field.name"
                  :prepend-icon="field.icon"
                  :type="field.type"
                  v-model="field.data"
                ></v-text-field>
              </v-form>
            </v-card-text>

            <v-card-actions>
              <v-spacer></v-spacer>
              <v-btn :disabled="!isDataFilled()" text color="primary" v-on:click="login()"
                >Login</v-btn
              >
            </v-card-actions>
          </v-card>
        </v-flex>
      </v-layout>
    </v-container>
  </v-main>
</template>

<style scoped>
/* Please style this crap, with style */
</style>
