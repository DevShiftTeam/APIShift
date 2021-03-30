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

    module.exports = {
        data() {
            return {
                is_main_page: true,
                sub_pages: [
                    { title: "Admin Users", sub_title: "Add/Edit or remove admin users", url: "/settings/admin_users" },
                    { title: "General Settings", sub_title: "Manage default features of the system", url: "/settings/general" },
                    { title: "Manage Pages", sub_title: "Add/Modify or remove pages from control panel", url: "/settings/manage_pages" },
                ]
            }
        },
        created() {
            window.handler = this;
            this.is_main_page = app.$router.currentRoute.path === '/settings';
            window.holder = this;
        },
        beforeRouteUpdate (to, from, next) {
            if(to.path == '/settings' || to.path == '/settings/') this.is_main_page = true;
            else this.is_main_page = false;
            next();
        },
        methods: {
            getPageTitle: function () {
                if(app.$router.currentRoute.path === '/settings') return "Manage Settings";
                for(let key in this.sub_pages) if(this.sub_pages[key].url == app.$router.currentRoute.path) return this.sub_pages[key].title;
                return "Not Found";
            }
        },
    };
</script>

<template>
    <v-main>
        <v-container fluid fill-height>
            <v-card v-if="is_main_page" class="mx-auto" width="90%" min-height="75%" elevation-2>
                <!-- Header -->
                <v-app-bar>
                    <v-menu offset-y>
                        <template v-slot:activator="{ on }">
                            <v-toolbar-title v-on="on">
                                <v-btn>
                                    {{ getPageTitle() }}
                                    
                                    <v-icon right>mdi-menu-down</v-icon>
                                </v-btn>
                            </v-toolbar-title>
                        </template>
                        <v-list>
                            <v-list-item to="/settings">Main Page</v-list-item>
                            <v-list-item v-for="(item, key) in sub_pages" :key="key" :to="item.url">{{ item.title }}</v-list-item>
                        </v-list>
                    </v-menu>
                    <v-spacer></v-spacer>
                </v-app-bar>

                <!-- Body -->
                <div class="overflow-box" v-bar>
                    <div>
                        <v-layout class="mx-auto" align-start justify-center row wrap>
                            <v-card v-for="(item, key) in  sub_pages" :key="key" :to="item.url" class="page-card" elevation-4 outlined>
                                <v-container>
                                    <v-row justify="space-between">
                                        <v-col>
                                            <v-list-item-title>{{ item.title }}</v-list-item-title>
                                            <v-list-item-subtitle>{{ item.sub_title }}</v-list-item-subtitle>
                                        </v-col>
                                    </v-row>
                                </v-container>
                            </v-card>
                        </v-layout>
                    </div>
                </div>
            </v-card>
            <router-view v-else></router-view>
        </v-container>
    </v-main>
</template>
<style scoped>
.page-card {
    margin: 5px;
    min-width: 500px;
}

.page-card:hover {
    cursor: pointer;
}
</style>