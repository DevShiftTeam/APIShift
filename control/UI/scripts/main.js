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
window.app = new Vue({
    el: "#app",
    router: new VueRouter({
        routes: [],
    }),
    vuetify: new Vuetify({
        theme: {
            dark: true,
            light: {
                primary: "#003060"
            },
            themes: {
                dark: {
                    primary: "#a8daff"
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
        this.apishift = new APIShift(this.loader);

        // Link components to apishift
        APIShift.Loader.load((resolve, reject) => {
            app.app_notifications = APIShift.API.getComponent("notifications");
            resolve(0);
        });

        APIShift.Loader.load((resolve, reject) => {
            // Add loaded routes & pages
            app.$router.addRoutes(APIShift.admin_routes);

            // Handle first load of page
            if (!APIShift.installed) {
                app.apishift.setSubtitle("Installer");
                app.$router.push("/installer");
            } else if (!APIShift.logged_in) {
                app.apishift.setSubtitle("Login");
                app.$router.push("/login");
            } else if (app.$route.path == "/") app.$router.push("/main");

            //  Don't load other components if system isn't installed
            if (APIShift.load_components) {
                app.app_loader = APIShift.API.getComponent("loader");
                app.app_navigator = APIShift.API.getComponent("navigator");
                app.app_footer = APIShift.API.getComponent("footer");
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
                else if (
                    ( to.path == "/database/model_editor" || to.path == "/database/model_editor/" ||
                      to.path == "/logic/task_editor" || to.path == "/logic/task_editor/" ) && 
                    APIShift.logged_in &&
                    APIShift.installed
                ) {
                    let p = APIShift.API.getMixin('graph/graph_view', true);
                    if (typeof(p.then) === 'function') p.then( () => next());
                    else next();
                }
                else {
                    // Construct current page title
                    if (window.nav_holder !== undefined) {
                        let page_holder = Object.values(nav_holder.pages).find(function(
                            r
                        ) {
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
});