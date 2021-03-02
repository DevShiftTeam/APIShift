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
     * @author Sagi Weizmann
     */

    module.exports = {
        data() {
            return {
                database_list: [],
                search: '',
                loader: {
                    visible: false,
                    message: "",
                    processes: 0
                },
                headers: [
                    { text: 'Name', value: 'name' },
                    { text: 'Host', value: 'host' },
                    { text: 'User', value: 'user' },
                    { text: 'Password', value: 'pass' },
                    { text: 'Database', value: 'db' },
                    { text: 'Port', value: 'port' },
                    { text: 'Actions', value: 'actions' }
                    
                ],
                rules: {
                    port: value => (!isNaN(value) && value >= 0 && value <= 65353) || 'Port Should be between (0-65353).',
                },
                in_edit: null,
                is_creating: false,
                delete_dialog: false,
                edit_dialog: false,
                discard_dialog: false,
                is_checking: false
            }
        },
        created() {
            APIShift.Loader.changeLoader("database_list", this.loader);
            window.dbholder = this;
            this.updateDatabases();
        },
        methods: {
            updateDatabases: function() {
                APIShift.API.request("Admin\\Database\\DatabaseList", "getDatabaseList", {}, function (response) {
                    if(response.status == APIShift.API.status_codes.SUCCESS) {
                        dbholder.database_list = Object.assign([], response.data);
                    } else {
                        APIShift.API.notify(response.data, "error");
                    }
                }, true);
            },
            createDatabase: function () {
                this.is_creating = true;
                this.edit_dialog = true;
                this.in_edit = {
                    name: '',
                    host: '',
                    user: '',
                    pass: '',
                    db: '',
                    port: 0
                }
            },
            discard: function() {
                if(this.is_creating && !this.discard_dialog) {
                    this.discard_dialog = true;
                    return;
                }
                if(this.is_creating) this.is_creating = false;
                this.edit_dialog = false;
                this.discard_dialog = false;
            },
            save: function() {
                if(this.in_edit.name === ""
                    || this.in_edit.host === ""
                    || this.in_edit.user === ""
                    || this.in_edit.db === ""
                    || this.in_edit.port === "") {
                    APIShift.API.notify("Please fill in valid data", 'warning');
                    return;
                }
                
                APIShift.API.request("Admin\\Database\\DatabaseList", this.is_creating ? "createDatabase" : "editDatabase", this.in_edit, function(response) {
                    if(response.status === APIShift.API.status_codes.SUCCESS) {
                        APIShift.API.notify(response.data, 'success');
                    }
                    else {
                        APIShift.API.notify(response.data, 'error');
                    }
                    dbholder.updateDatabases();
                });

                this.is_creating = false;
                this.edit_dialog = false;
                return;
            },
            editDatabase: function(database) {
                this.edit_dialog = true;
                this.in_edit = {
                    id: database.id,
                    name: database.name,
                    host: database.host,
                    user: database.user,
                    pass: database.pass,
                    db: database.db,
                    port: database.port
                }
            },
            checkDatabase: function (database) {
                this.is_checking = true;
                APIShift.API.request("Admin\\Database\\DatabaseList", "checkDatabase", database, function(response) {
                    if(response.status === APIShift.API.status_codes.SUCCESS) {
                        APIShift.API.notify(response.data, 'success');
                    }
                    else {
                        APIShift.API.notify(response.data, 'error');
                    }
                    dbholder.is_checking = false;
                });
            },
            removeDatabase: function(database_id) {
                if(!this.delete_dialog) {
                    this.in_edit = Object.assign({}, this.database_list.find(r => r.id === database_id));
                    this.delete_dialog = true;
                    return;
                }

                APIShift.API.request("Admin\\Database\\DatabaseList", "removeDatabase", { id: this.in_edit.id }, function(response) {
                    if(response.status === APIShift.API.status_codes.SUCCESS) {
                        APIShift.API.notify(response.data, 'success');
                    }
                    else {
                        APIShift.API.notify(response.data, 'error');
                    }
                    dbholder.updateDatabases();
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
            :items="database_list"
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
                            <v-list-item to="/database">Main Page</v-list-item>
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
                            <v-btn icon v-on="on" @click="createDatabase()">
                                <v-icon v-if="is_creating">mdi-close-circle</v-icon>
                                <v-icon v-else>mdi-plus-circle</v-icon>
                            </v-btn>
                        </template>
                        <span v-if="is_creating">Discard new session state</span>
                        <span v-else>Add new session state</span>
                    </v-tooltip>
                </v-app-bar>

                <!-- Edit/Add dialog -->
                <v-dialog v-if="edit_dialog" v-model="edit_dialog" max-width="500px">
                    <v-card>
                        <v-card-title><span class="headline">{{ is_creating ? "Create New Database" : "Edit" }}</span></v-card-title>

                        <v-card-text>
                            <v-container>
                                <v-row>
                                    <v-col cols="12" sm="6" md="6">
                                        <v-text-field v-model="in_edit.name" label="Name"></v-text-field>
                                    </v-col>
                                    <v-col cols="12" sm="6" md="6">
                                        <v-text-field v-model="in_edit.host"  label="Host"></v-text-field>
                                    </v-col>
                                    <v-col cols="12" sm="6" md="6">
                                        <v-text-field v-model="in_edit.user"  label="User"></v-text-field>
                                    </v-col> 
                                    <v-col cols="12" sm="6" md="6">
                                        <v-text-field v-model="in_edit.pass" type="password"  label="Password"></v-text-field>
                                    </v-col>
                                    <v-col cols="12" sm="6" md="6">
                                        <v-text-field v-model="in_edit.db" label="Database"></v-text-field>
                                    </v-col>                              
                                    <v-col cols="12" sm="6" md="6">
                                        <v-text-field v-model="in_edit.port" :rules="[rules.port]" hint="(0-65353)" label="Port"></v-text-field>
                                    </v-col>                            
                                </v-row>
                            </v-container>
                        </v-card-text>

                        <v-card-actions>
                            <v-spacer></v-spacer>
                            <v-btn color="primary" text @click="save()">{{ is_creating ? "Create" : "Save" }}</v-btn>
                            <v-btn :disabled="is_checking" text @click="checkDatabase(in_edit)">Check</v-btn>
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

            <template v-slot:item.actions="{ item }">
                <v-btn icon @click="editDatabase(item)">
                    <v-icon>
                        mdi-pencil-circle
                    </v-icon>
                </v-btn>
                <v-btn icon :disabled="is_checking">
                    <v-icon @click="checkDatabase(item)">
                        mdi-check-circle
                    </v-icon>
                </v-btn>
                <!-- Delete dialog -->
                <v-dialog v-model="delete_dialog" max-width="500px">
                    <template v-slot:activator="{ on }">
                        <v-btn v-on="on" icon @click="removeDatabase(item.id)">
                            <v-icon>
                                mdi-delete-circle
                            </v-icon>
                        </v-btn>
                    </template>
                    <v-card>
                        <v-card-title>Sure?</v-card-title>
                        <v-card-text>
                            Deleting an important database might lead to an information leak
                        </v-card-text>
                        <v-divider></v-divider>
                        <v-card-actions>
                            <v-spacer></v-spacer>
                            <v-btn color="primary" text @click="removeDatabase(item.id)">Remove</v-btn>
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