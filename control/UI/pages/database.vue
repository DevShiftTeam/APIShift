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
                current_page: 0,
                sub_pages: [
                    { title: "Database Connections", sub_title: "Manage database connections", url: "/database/database_list" },
                    { title: "Data Model Editor", sub_title: "Create database schemas using a smart ORM", url: "/database/model_editor" }
                ],
                index: 10
            };
        },
        beforeRouteUpdate (to, from, next) {
            this.current_page = this.sub_pages.findIndex(p => p.url == to.path);
            next();
        },
        created() {
            if(this.$route.path == '/database' || this.$route.path == '/database/') {
                this.$router.push('/database/database_list');
            }
            window.handler = this;
            // Load necessary comonents
            APIShift.API.getMixin('orm/graph_element', true);
            // Determine page
            this.current_page = this.sub_pages.findIndex(p => p.url == this.$route.path);
            if(this.current_page < 0) this.current_page = 0;
        },
        methods: {
            updateIndex(new_index) {
                this.index = new_index;
            }
        }
    };
</script>

<template>
    <v-content>
        <v-container fluid fill-height>
            <v-card class="mx-auto" width="90%" min-height="75%" elevation-2>
                <!-- Header -->
                <v-app-bar>
                    <v-menu offset-y :style="{ 'z-index' : index }">
                        <template v-slot:activator="{ on }">
                            <v-toolbar-title v-on="on">
                                <v-btn>
                                    {{ sub_pages[current_page].title }}
                                    
                                    <v-icon right>mdi-menu-down</v-icon>
                                </v-btn>
                            </v-toolbar-title>
                        </template>
                        <v-list>
                            <v-list-item v-for="(item, key) in sub_pages" :key="key" :to="item.url">{{ item.title }}</v-list-item>
                        </v-list>
                    </v-menu>
                    <v-spacer></v-spacer>
                </v-app-bar>

                <!-- Body -->
                <div class="overflow-box">
                    <router-view></router-view>
                </div>
            </v-card>
        </v-container>
    </v-content>
</template>
<style scoped>

</style>