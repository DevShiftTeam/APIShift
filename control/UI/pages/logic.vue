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
     * @contributor Ilan Dazanashvili
     */

    module.exports = {
        data() {
            return {
                is_main_page: true,
                sub_pages: [
                    { title: "Create New Process", sub_title: "Create processes using a turing-complete chartflow.", url: "/logic/task_editor" },
                    { title: "Task View", sub_title: "View and edit Tasks", url: "/logic/task_view" },
                    { title: "Controller View", sub_title: "Attach Task to a controller", url: "/logic/controller_view" }
                ],
                process_list: [],   
                mixins_loaded: false,
            }
        },
        created() {
            this.is_main_page = app.$router.currentRoute.path === '/logic';
            window.holder = this;
            this.updateProcessList();

            // Load necessary components
            Promise.all([
                APIShift.API.getMixin('graph/graph_view', true),
                APIShift.API.getMixin('graph/graph_element', true),
                APIShift.API.getMixin('graph/line_parent', true),
                APIShift.API.getMixin('graph/container_element', true),
                APIShift.API.getMixin('graph/logic/data_source', true)
            ]).then(() => {
                this.mixins_loaded = true;
            });
        },
        beforeRouteUpdate (to, from, next) {
            if(to.path == '/logic' || to.path == '/logic/') this.is_main_page = true;
            else this.is_main_page = false;
            next();
        },
        methods: {
            getPageTitle: function () {
                if(app.$router.currentRoute.path === '/logic') return "Manage Logic";
                for(let key in this.sub_pages) if(this.sub_pages[key].url == app.$router.currentRoute.path) return this.sub_pages[key].title;
                return "Not Found";
            },
            getProcessName: function () {
                return holder.process_list[0]?.name;
            },
            updateIndex(new_index) {
                this.index = new_index;
            },
            updateProcessList() {
                APIShift.API.request("Admin\\Logic\\Processes", "getAllProcesses", {}, function(response) {
                    if(response.status == APIShift.API.status_codes.SUCCESS) {
                        holder.process_list = response.data;
                    }
                    else APIShift.API.notify(response.data, 'error');
                });
            },
        },
    };
</script>

<template>
    <v-main>
        <v-container fluid fill-height>
            <v-card v-if="is_main_page" class="mx-auto" width="90%" min-height="75%" elevation-2>

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
            <router-view v-show="!is_main_page && mixins_loaded"></router-view>
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