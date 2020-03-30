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
                    { text: 'Controller', value: 'controller', },
                    { text: 'Method', value: 'method', },
                    { text: 'Task name', value: 'name' },
                    { text: 'Actions', value: 'actions', sortable: false }
                ],
                in_edit: 0,
                is_creating: false,
                delete_dialog: false,
                edit_dialog: false,
                discard_dialog: false
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
                    this.edit_dialog = false;
                    return;
                }

                this.is_creating = true;
                this.edit_dialog = true;
            },
            discard: function() {
                if(this.is_creating) this.is_creating = false;
                this.edit_dialog = false;
            },
            save: function() {

            },
            editAccessRule: function(access_rule) {
                this.edit_dialog = true;
            },
            removeAccessRule: function(access_rule) {
                if(!this.delete_dialog) {
                    this.delete_dialog = true;
                    return;
                }

                this.delete_dialog = false;
            }
        },
    };
</script>

<template>
        <v-data-table
            class="mx-auto ca_table" elevation-2
            :headers="headers"
            :items="controller_access_list"
            :search="search">
            <template v-slot:top>
                <v-app-bar>
                    <v-menu offset-y>
                        <template v-slot:activator="{ on }">
                            <v-toolbar-title v-on="on"><v-btn>{{ holder.getPageTitle() }}</v-btn></v-toolbar-title>
                        </template>
                        <v-list>
                            <v-list-item to="/access">Main Page</v-list-item>
                            <v-list-item v-for="(item, key) in holder.sub_pages" :key="key" :to="item.url">{{ item.title }}</v-list-item>
                        </v-list>
                    </v-menu>
                    <v-divider class="mx-4" inset vertical></v-divider>
                    <v-spacer></v-spacer>
                    <v-text-field
                        v-model="search"
                        append-icon="mdi-magnify"
                        label="Search"
                        single-line
                        :loading="loader.visible"
                        :loading-text="loader.message"
                        hide-details></v-text-field>
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
                </v-app-bar>
            </template>
            <template v-slot:item.actions="{ item }">
                <v-icon @click="editAccessRule(item)">
                    mdi-pencil-circle
                </v-icon>
                <!-- Delete dialog -->
                <v-dialog v-model="delete_dialog" max-width="500px">
                    <template v-slot:activator="{ on }">
                        <v-icon v-on="on" @click="removeAccessRule(item)">
                            mdi-delete-circle
                        </v-icon>
                    </template>
                    <v-card>
                        <v-card-title>Sure?</v-card-title>
                        <v-card-text>
                            Deleting an important access rule might lead to an information leak
                        </v-card-text>
                        <v-divider></v-divider>
                        <v-card-actions>
                            <v-spacer></v-spacer>
                            <v-btn color="primary" text @click="removeAccessRule(item)">Remove</v-btn>
                            <v-btn text @click="delete_dialog = false">Cancel</v-btn>
                        </v-card-actions>
                    </v-card>
                </v-dialog>
            </template>

            <!-- Edit dialog -->
            <v-dialog v-model="edit_dialog" max-width="500px">
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
                        <v-btn color="primary" text @click="save()">Save</v-btn>
                        <v-btn text @click="discard()">Cancel</v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>

            <!-- Discard dialog -->
            <v-dialog v-model="discard_dialog" max-width="500px">
                <v-card>
                    <v-card-title>Sure?</v-card-title>
                    <v-card-text>
                        You cannot revert changes you've discarded
                    </v-card-text>
                    <v-divider></v-divider>
                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn color="primary" text @click="discard()">Remove</v-btn>
                        <v-btn text @click="discard_dialog = false">Cancel</v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>
        </v-data-table>
</template>
<style scoped>
.ca_table {
    width: 90%;
}
</style>