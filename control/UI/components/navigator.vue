<script>
    /**
     * APIShift Engine v1.0.0
     * (c) 2020-present Sapir Shemer, DevShift (devshift.biz)
     * Released under the MIT License with the additions present in the LICENSE.md
     * file in the root folder of the APIShift Engine original release source-code
     * @author Sapir Shemer
     */
    
    // This shit is made for scripting
    module.exports = {
        data () {
            return {
                drawer: 1,
                pages: []
            }
        },
        created () {
            window.nav_holder = this;
            // Load control panel pages
            APIShift.API.request("Control", "getPages", {}, function (response) {
                if(response.status == APIShift.API.status_codes.SUCCESS) {
                    nav_holder.pages = [];
                    // Add routes
                    for(let item in response.data) {
                        // Construct path to page
                        if(response.data[item].parent === 0) {
                            nav_holder.pages.push(response.data[item]);
                            APIShift.admin_routes.push({
                                path: "/" + response.data[item].path,
                                component: httpVueLoader(APIShift.API.getPage(response.data[item].path))
                            });
                        } else {
                            let routeData = APIShift.admin_routes.find(r => r.path === "/" + response.data[response.data[item].parent].path);
                            if(routeData.children === undefined) routeData.children = [];
                            routeData.children.push({
                                path: response.data[item].path,
                                component: httpVueLoader(APIShift.API.getPage(response.data[response.data[item].parent].path + "/" + response.data[item].path))
                            });
                        }
                    }
                    // Update routes
                    app.$router.addRoutes(APIShift.admin_routes);
                }
                else {
                    APIShift.API.notify(APIShift.API.getStatusName(response.status) + ": " + response.data, "error");
                }
            });
        },
        computed: {
            showNavBar: {
                get: function() {
                    return this.drawer > 0;
                },
                set: function(newValue) {
                    this.drawer = newValue;
                }
            }
        },
        methods: {
            toggleDarkTheme: function() {
                window.app.$vuetify.theme.dark = !(window.app.$vuetify.theme.dark);
            },
            isOnDarkMode: function () {
                return window.app.$vuetify.theme.dark;
            },
            logout: function() {
                APIShift.API.request("SessionState", "changeState", {state: "DEFAULT_VIEWER"}, function (response) {
                    if(response.status == 1) {
                        APIShift.API.notify("Logged out successfully", "success");
                        // Load login screen
                        APIShift.logged_in = false;
                        app.$router.push('login');
                        window.app.app_loader = null;
                        window.app.app_footer = null;
                        window.app.app_navigator = null;
                    }
                    else {
                        APIShift.API.notify(APIShift.API.getStatusName(response.status) + ": " + response.data, "error");
                    }
                });
            }
        }
    }
</script>

<template>
    <div>
        <v-app-bar app clipped-left>
            <v-app-bar-nav-icon @click.stop="drawer = (drawer + 1) % 3"></v-app-bar-nav-icon>
            <v-toolbar-title>APIShift Control Panel</v-toolbar-title>
            <v-btn class="lightbulb" icon large target="_blank" v-on:click="toggleDarkTheme()">
                <v-icon v-if="isOnDarkMode()">fas fa-lightbulb</v-icon>
                <v-icon v-else>fas fa-moon</v-icon>
            </v-btn>
        </v-app-bar>

        <v-navigation-drawer v-model="showNavBar" app clipped :mini-variant="drawer != 1">
            <v-list dense>
                <!-- Home option -->
                <v-list-item link to="/main">
                    <v-list-item-action>
                        <v-icon>fa fa-home</v-icon>
                    </v-list-item-action>
                    <v-list-item-content>
                        <v-list-item-title>
                            Dashboard
                        </v-list-item-title>
                    </v-list-item-content>
                </v-list-item>
                <v-divider class="my-2"></v-divider>

                <v-list-item v-for="page in pages" :key="page.id" link :to="'/' + page.path" >
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
                        <v-list-item-title>
                            Logout
                        </v-list-item-title>
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