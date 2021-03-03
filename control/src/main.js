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
import Vue from "vue";
import App from "./App.vue";
import Vuetify from "vuetify/lib";
import VueRouter from "vue-router";
import i18n from "./locale/i18n";
import store from "./store/index";
import { APIShift as APIShiftClass } from "./scripts/APIShift.js";
import Vuebar from 'vuebar';

Vue.use(Vuebar);
Vue.use(VueRouter);
Vue.use(Vuetify);

let vue = new Vue({ store });
window.app = new Vue({
    i18n,
    store: store,
    router: new VueRouter({
        routes: [],
    }),
    vuetify: new Vuetify({
        theme: {
            dark: true,
            light: {
                primary: "#003060",
            },
            themes: {
                dark: {
                    primary: "#a8daff",
                },
            },
        },
    }),
    props: {
        source: String,
    },
    data: {
        // System alerts
        alerts: [],
        // Determines if loader is visible
        loader: {
            visible: false,
            message: "",
            processes: 0,
        },
        app_loader: Vue.extend({ template: "<div></div>" }),
        app_navigator: Vue.extend({ template: "<div></div>" }),
        app_footer: Vue.extend({ template: "<div></div>" }),
        app_notifications: Vue.extend({ template: "<div></div>" }),
        apishift: null,
    },
    created() {
        // Initialize APIShift Engine
        this.apishift = new APIShiftClass(this.loader);
        window.APIShift = this.apishift;
        this.apishift.initialize();
        // Link components to apishift
        APIShift.Loader.load((resolve, reject) => {
            app.app_notifications = APIShift.API.getComponent("notifications");
            resolve(0);
        });

        APIShift.Loader.load((resolve, reject) => {
            // Add loaded routes & pages
            this._router.addRoutes(APIShift.admin_routes);

            console.log(this.apishift.logged_in);
            // Handle first load of page
            if (!this.apishift.installed) {
                this.apishift.setSubtitle("Installer");
                if (app.$route.path != "/installer") app.$router.push("/installer");
            } else if (!this.apishift.logged_in) {
                this.apishift.setSubtitle("Login");
                if (app.$route.path != "/login") app.$router.push("/login");
            } else if (app.$route.path == "/") app.$router.push("/main");

            //  Don't load other components if system isn't installed
            if (APIShift.load_components) {
                this.$store.commit(
                    "SET_LOADER",
                    "loader"
                );
                this.$store.commit(
                    "SET_NAVIGATOR",
                    "navigator"
                );
                this.$store.commit(
                    "SET_FOOTER",
                    "footer"
                );
            }

            // Navigation gaurd for control panel
            app.$router.beforeEach((to, from, next) => {
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
                            app.apishift.setSubtitle(page_holder.name);
                    }
                    // Move to next page if everything's in place
                    next();
                }
            });

            resolve(0);
        });
    },
    methods: {},
    render: (h) => h(App),
}).$mount("#app");

export default window.app;