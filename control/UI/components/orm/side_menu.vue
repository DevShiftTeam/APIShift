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
     * @author Ilan Dazanashvili
     */

    module.exports = {
        props: {
            actions: Array,
        },
        data () {
            return {
                drawer: 0,
                show_side_menu: false,
            }
        },
        created () {
        },
        methods: {
            run_handler: function(func_handler) {
                this.show_side_menu = false;
                graph_view.drag_end_lock = true;
                func_handler();
            },
            toggleDarkTheme: function() {
                window.app.$vuetify.theme.dark = !(window.app.$vuetify.theme.dark);
            },
            isOnDarkMode () {
                return window.app.$vuetify.theme.dark;
            }
        }
    }
</script>

<template>
        <div class="gv_side_menu">
            <v-btn height="50px" style="min-width:0;width:56px;" v-bind:class="{ 'active-border' : show_side_menu }"
              tile @click.stop="show_side_menu = !show_side_menu;"><v-icon>fas fa-wrench</v-icon></v-btn>
            <v-navigation-drawer class="gv_nav_drawer" v-model="show_side_menu" mini-variant :style="{'z-index': 9999}">
                <v-list dense>
                    <v-tooltip top v-for="action in actions" :key="action.name">
                        <template #activator="{ on }">
                            <v-list-item link @click="run_handler(action.starter)" v-on="on">
                                <v-list-item-action>
                                    <v-icon>{{ action.icon }}</v-icon>
                                </v-list-item-action>
                                <v-list-item-content>
                                    <v-list-item-title>
                                        {{ action.name }}
                                    </v-list-item-title>
                                </v-list-item-content>
                            </v-list-item>
                        </template>
                        <span>{{action.text}}</span>
                    </v-tooltip>
                </v-list>
            </v-navigation-drawer>
        </div>
</template>

<style scoped>
/* Please style this crap, with style */    
    /* Change this. */
    .active-border{
        border-right: 1px solid #4e4e4e;
    }
    #show_menu {
        position: absolute;
        min-width: 50px;
        left: 0;
        top: 50%;
        transform: translateY(-50%);
        background: white;
        display: flex;
        flex-direction: column;
    }
    .action {
        background: black;
        padding: 5px;
        margin: 5px;
        text-align: center;
        cursor: pointer;
    }

    .gv_side_menu {
        position: absolute;
        width: fit-content;
        height: 100%;
        z-index: 90;
    }
</style>