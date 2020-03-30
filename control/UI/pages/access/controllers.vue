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
                controller_access_list: [],
                search: '',
                loader: {
                    visible: false,
                    message: "",
                    processes: 0
                },
                headers: [
                    {
                        text: 'Controller',
                        align: 'start',
                        sortable: false,
                        value: 'controller',
                    },
                    {
                        text: 'Method',
                        align: 'start',
                        sortable: false,
                        value: 'method',
                    },
                    { text: 'Task name', value: 'name' }
                ],
                in_edit: 0,
                is_creating: false,
                dialog: false
            }
        },
        created() {
            APIShift.Loader.changeLoader("access_controllers", this.loader);
            window.cahandler = this;

            APIShift.API.request("Control", "getControllersTasks", {}, function (response) {
                if(response.status == APIShift.API.status_codes.SUCCESS) {
                    cahandler.controller_access_list = Object.assign([], response.data);
                } else {
                    APIShift.API.notify(response.data, "error");
                }
            }, true);
        },
        methods: {
            createAccessRule: function () {
                if(this.is_creating) {
                    this.is_creating = false;
                    this.dialog = false;
                    return;
                }

                this.is_creating = true;
                this.dialog = true;
            },
            discard: function() {
                if(this.is_creating) this.is_creating = false;
                this.dialog = false;
            },
            save: function() {

            },
            editAccessRule: function() {

            },
            removeAccessRule: function() {
                
            }
        },
    };
</script>

<template>
    <div>
        <v-data-table
            :headers="headers"
            :items="controller_access_list"
            :search="search">
            <template v-slot:top>
                <v-toolbar>
                    <v-card-title>Controllers</v-card-title>
                    <v-divider class="mx-4" inset vertical></v-divider>
                    <v-spacer></v-spacer>
                    <v-text-field
                        v-model="search"
                        append-icon="mdi-magnify"
                        label="Search"
                        single-line
                        :loading="loader.visible"
                        :loading-text="loader.message"
                        hide-details
                    ></v-text-field>
                    <v-spacer></v-spacer>
                    <v-tooltip top>
                        <template #activator="{ on }">
                            <v-btn icon v-on="on" @click="createAccessRule()">
                                <v-icon v-if="is_creating">mdi-close-circle</v-icon>
                                <v-icon v-else>mdi-plus-circle</v-icon>
                            </v-btn>
                        </template>
                        <span v-if="is_creating">Discard new session state</span>
                        <span v-else>Add new session state</span>
                    </v-tooltip>
                </v-toolbar>
            </template>
        </v-data-table>

        <!-- Edit dialog -->
        <v-dialog v-model="dialog" max-width="500px">
            <v-card>
                <v-card-title><span class="headline">{{ is_creating ? "Add New" : "Edit" }}</span></v-card-title>

                <v-card-text>
                    <v-container>
                        <v-row>

                        </v-row>
                    </v-container>
                </v-card-text>

                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn text @click="discard()">Cancel</v-btn>
                    <v-btn text @click="save()">Save</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </div>
</template>
<style scoped>

</style>