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

    export default {
        data() {
            return {
                item: 3,
                items: [
                    {
                        icon: 'mdi-account',
                        title: 'Manage Sessions',
                        subtitle: 'Create session structures and hierarchy as you desire easily'
                    },
                    {
                        icon: 'mdi-database',
                        title: 'Manage Database Structure & Objects',
                        subtitle: 'Create Item & Relations between to define application\'s data'
                    },
                    {
                        icon: 'mdi-lock',
                        title: 'Manage Access To Controllers & Data',
                        subtitle: 'Define which functions and data a user can access'
                    }
                ]
            };
        },
        created() {
            APIShift.setSubtitle("Dashboard");
            APIShift.Loader.changeLoader("main");
        },
        methods: {

        }
    };
</script>

<template>
    <v-main>
        <v-container class="main_container" fluid>
            <v-card class="mx-auto" width="50%" color="light-blue lighten-1"><v-toolbar-title class="version_header">Version 1.0</v-toolbar-title></v-card>
            <v-card class="mx-auto" width="80%" tile>
                <v-list three-line>
                    <v-subheader>New Control Panel Features</v-subheader>
                    <v-list-item-group v-model="item" color="primary">
                        <v-list-item v-for="(item, i) in items" :key="i">
                                <v-list-item-icon>
                                    <v-icon >{{ item.icon }}</v-icon>
                                </v-list-item-icon>
                            <v-list-item-content>
                                <v-list-item-title v-text="item.title"></v-list-item-title>
                                <v-list-item-subtitle v-text="item.subtitle"></v-list-item-subtitle>
                            </v-list-item-content>
                        </v-list-item>
                    </v-list-item-group>
                </v-list>
            </v-card>
        </v-container>
    </v-main>
</template>
<style scoped>
.version_header {
    text-align: center;
}

.main_container > * {
    margin-bottom: 20px;
}
</style>