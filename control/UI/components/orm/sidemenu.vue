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
        },
        data () {
            return {
            }
        },
        created () {
        },
        mounted () {
            graph_view.$refs['sidemenu'] = this;
        },
        methods: {
            action_creator(action_name) {
                switch (action_name) {
                    case 'add-item':
                        graph_view.$refs['item_builder'].build('item');
                        break;
                    case 'add-relation':
                        graph_view.$refs['item_builder'].build('relation');
                        break;
                    case 'delete-element':
                        graph_view.cursor_state = {type: "delete"};
                        break;
                    case 'add-enum':
                        graph_view.cursor_state = {type: "create", data: 'add-enum'};
                        break;
                    case 'add-enum-type':
                        graph_view.cursor_state = {type: "create", data: 'add-enum-type'};
                        break;
                    case 'add-group':
                        graph_view.cursor_state = {type: "select"};
                        break;
                    default:
                        break;
                }
            }
        },
        computed: {

        }
    }
</script>

<template>
    <div id="sidemenu">
        <div class="action" @click="action_creator('add-item')">
            ITEM
        </div>
        <div class="action" @click="action_creator('add-relation')">
            RELATION
        </div>
        <div class="action" @click="action_creator('delete-element')">
            DELETE
        </div>
        <div class="action" @click="action_creator('add-enum')">
            ENUM
        </div>
        <div class="action" @click="action_creator('add-enum-type')">
            ENUM TYPE
        </div>
        <div class="action" @click="action_creator('add-group')">
            GROUP
        </div>
    </div>
</template>

<style scoped>
/* Please style this crap, with style */    
    /* Change this. */
    #sidemenu {
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
</style>