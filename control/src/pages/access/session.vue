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
        mixins: [APIShift.API.getMixin('access/rule')],
        data() {
            return {
                sessions_access_list: [],
                search: '',
                loader: {
                    visible: false,
                    message: "",
                    processes: 0
                },
                headers: [
                    { text: 'State Name', value: 'name', },
                    { text: 'Authorization Task', value: 'task_name' },
                    { text: 'Actions', value: 'actions', sortable: false }
                ],
                in_edit: null,
                delete_dialog: false,
                edit_dialog: false,
                discard_dialog: false
            }
        },
        created() {
            APIShift.Loader.changeLoader("access_sessions", this.loader);
            window.sahandler = this;
            this.updateSessionTasks();            
        },
        methods: {
            updateSessionTasks: function() {
                APIShift.API.request("Admin\\Access\\Session", "getSessionTasks", {}, function (response) {
                    if(response.status == APIShift.API.status_codes.SUCCESS) {
                        sahandler.sessions_access_list = Object.assign([], response.data);
                    } else {
                        APIShift.API.notify(response.data, "error");
                    }
                }, true);
            },
            discard: function() {
                if(!this.discard_dialog) {
                    this.discard_dialog = true;
                    return;
                }
                this.edit_dialog = false;
                this.discard_dialog = false;
                this.access_names = []; // Empty access names
            },
            save: function() {
                if(this.in_edit.controller === ""
                    || this.in_edit.method === ""
                    || this.in_edit.type === "") {
                    APIShift.API.notify("Please fill in valid data", 'warning');
                    return;
                }
                
                APIShift.API.request("Admin\\Access\\Session", "editAccessRule", {
                        id: this.in_edit.id,
                        type: this.in_edit.type,
                        rule: this.in_edit.type != 'Function' ? this.getAccessNameByValue(this.in_edit.rule) : this.in_edit.rule
                    }, function(response) {
                    if(response.status === APIShift.API.status_codes.SUCCESS) {
                        APIShift.API.notify(response.data, 'success');
                    }
                    else {
                        APIShift.API.notify(response.data, 'error');
                    }
                    sahandler.updateSessionTasks();
                });

                this.edit_dialog = false;
                this.access_names = []; // Empty access names
                return;
            },
            editAccessRule: function(access_rule) {
                this.edit_dialog = true;
                this.in_edit = {
                    type: this.getRuleType(access_rule),
                    rule: null,
                    id: access_rule.id
                }
                this.getAvailableRulesForType(this.in_edit.type);
            },
            removeAccessRule: function(rule_id) {
                if(!this.delete_dialog) {
                    this.in_edit = Object.assign({}, this.sessions_access_list.find(r => r.id === rule_id));
                    this.delete_dialog = true;
                    return;
                }

                APIShift.API.request("Admin\\Access\\Session", "removeAccessRule", { id: this.in_edit.id }, function(response) {
                    if(response.status === APIShift.API.status_codes.SUCCESS) {
                        APIShift.API.notify(response.data, 'success');
                    }
                    else {
                        APIShift.API.notify(response.data, 'error');
                    }
                    sahandler.updateSessionTasks();
                });
                this.delete_dialog = false;
            }
        }
    };
</script>

<template>
        <v-data-table
            class="mx-auto ca_table" elevation-2 max-height="75%"
            :headers="headers"
            :items="sessions_access_list"
            :search="search">
            <template v-slot:top>
                <v-app-bar>
                    <v-menu offset-y>
                        <template v-slot:activator="{ on }">
                            <v-toolbar-title v-on="on">
                                <v-btn>
                                    {{ holder.getPageTitle() }}
                                    
                                    <v-icon right>mdi-menu-down</v-icon>
                                </v-btn>
                            </v-toolbar-title>
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
                </v-app-bar>

                <!-- Edit/Add dialog -->
                <v-dialog v-if="edit_dialog" v-model="edit_dialog" max-width="500px">
                    <v-card>
                        <v-card-title><span class="headline">{{ "Edit" }}</span></v-card-title>

                        <v-card-text>
                            <v-container>
                                <v-row>
                                    <v-col cols="12" sm="6" md="6">
                                        <v-select @change="getAvailableRulesForType(in_edit.type)" v-model="in_edit.type" :items="access_types" label="Authentication type"></v-select>
                                    </v-col>
                                    <v-col cols="12" sm="6" md="6">
                                        <v-autocomplete v-if="in_edit.type != 'Function'" v-model="in_edit.rule" :items="access_names" item-text="text" item-value="val" :label="in_edit.type"></v-autocomplete>
                                        <v-text-field v-else v-model="in_edit.rule" label="Function"></v-text-field>
                                    </v-col>
                                </v-row>
                            </v-container>
                        </v-card-text>

                        <v-card-actions>
                            <v-spacer></v-spacer>
                            <v-btn color="primary" text @click="save()">{{ "Save" }}</v-btn>
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
                            <v-btn color="primary" text @click="discard()">Discard</v-btn>
                            <v-btn text @click="discard_dialog = false">Cancel</v-btn>
                        </v-card-actions>
                    </v-card>
                </v-dialog>
            </template>

            <template v-slot:[`item.task_name`]="{ item }">
                <v-chip>{{ getRuleType(item) }}</v-chip>
                <span>{{ getRuleName(item) }}</span>
            </template>

            <template v-slot:[`item.actions`]="{ item }">
                <v-icon @click="editAccessRule(item)">
                    mdi-pencil-circle
                </v-icon>
                <!-- Delete dialog -->
                <v-dialog v-model="delete_dialog" max-width="500px">
                    <template v-slot:activator="{ on }">
                        <v-icon v-on="on" @click="removeAccessRule(item.id)">
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
                            <v-btn color="primary" text @click="removeAccessRule(item.id)">Remove</v-btn>
                            <v-btn text @click="delete_dialog = false">Cancel</v-btn>
                        </v-card-actions>
                    </v-card>
                </v-dialog>
            </template>
        </v-data-table>
</template>
<style scoped>
.ca_table {
    width: 100%;
}
</style>