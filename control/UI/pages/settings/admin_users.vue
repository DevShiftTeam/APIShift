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
     * @author Ilan Dazanahshvili
     */

    module.exports = {
        data() {
            return {
                admin_list: [],
                search: '',
                loader: {
                    visible: false,
                    message: "",
                    processes: 0
                },
                headers: [
                    { text: 'Username', value: 'username' },
                    { text: 'Created', value: 'created' },
                    { text: 'Actions', value: 'actions' }
                ],
                rules: [v => RegExp("^(((?=.*[a-z])(?=.*[A-Z]))|((?=.*[a-z])(?=.*[0-9]))|((?=.*[A-Z])(?=.*[0-9])))(?=.{8,})").test(v) || 'At least 8 characters, with numbers and characters'],    
                show_flags: {first: false, second: false},
                in_edit: null,
                is_creating: false,
                delete_dialog: false,
                edit_dialog: false,
                discard_dialog: false,
                is_checking: false,
                repeat_password: ''
            }
        },
        created() {
            APIShift.Loader.changeLoader("admin_list", this.loader);
            window.uholder = this;
            this.updateAdmins();
        },
        methods: {
            updateAdmins: function() {
                let self = this;
                APIShift.API.request("Admin\\Settings\\AdminList", "getAdminsList", {}, function (response) {
                    if(response.status == APIShift.API.status_codes.SUCCESS) {
                        uholder.admin_list = Object.assign([], response.data);
                    } else {
                        APIShift.API.notify(response.data, "error");
                    }
                }, true);
            },
            createPage: function () {
                this.is_creating = true;
                this.edit_dialog = true;
                this.in_edit = {
                    username: '',
                    password: ''
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
                if(this.in_edit.username === ""
                    || this.in_edit.password === ""
                    || this.in_edit.password !== this.repeat_password
                    || !RegExp("^(((?=.*[a-z])(?=.*[A-Z]))|((?=.*[a-z])(?=.*[0-9]))|((?=.*[A-Z])(?=.*[0-9])))(?=.{8,})").test(this.in_edit.password)) {
                    APIShift.API.notify("Please fill in valid data", 'warning');
                    return;
                }
                
                let to_send = Object.assign({}, this.in_edit);

                APIShift.API.request("Admin\\Settings\\AdminList", this.is_creating ? "createAdmin" : "editAdmin", to_send, function(response) {
                    if(response.status === APIShift.API.status_codes.SUCCESS) {
                        APIShift.API.notify(response.data, 'success');
                    }
                    else {
                        APIShift.API.notify(response.data, 'error');
                    }
                    uholder.updateAdmins();
                });
                
                this.is_creating = false;
                this.edit_dialog = false;
                this.repeat_password = '';
                return;
            },
            editAdmin: function(admin) {
                this.edit_dialog = true;
                this.in_edit = Object.assign({}, admin);
                this.in_edit.password = '';
                this.in_edit.new_username = this.in_edit.username;
                this.repeat_password = '';
            },
            removeAdmin: function(admin = this.in_edit) {
                if(!this.delete_dialog) {
                    this.in_edit = admin;
                    this.delete_dialog = true;
                    return;
                }

                APIShift.API.request("Admin\\Settings\\AdminList", "removeAdmin", { id: this.in_edit.id }, function(response) {
                    if(response.status === APIShift.API.status_codes.SUCCESS) {
                        APIShift.API.notify(response.data, 'success');
                    }
                    else {
                        APIShift.API.notify(response.data, 'error');
                    }
                    uholder.updateAdmins();
                });
                this.delete_dialog = false;
            }
        }
    };
</script>

<template>
    <v-container>
        <v-data-table
            class="mx-auto ca_table" elevation-2 max-height="75%"
            :headers="headers"
            :items="admin_list"
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
                            <v-list-item to="/settings">Main Page</v-list-item>
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
                            <v-btn icon v-on="on" @click="createPage()">
                                <v-icon v-if="is_creating">mdi-close-circle</v-icon>
                                <v-icon v-else>mdi-plus-circle</v-icon>
                            </v-btn>
                        </template>
                        <span v-if="is_creating">Discard new page creation</span>
                        <span v-else>Add new page</span>
                    </v-tooltip>
                </v-app-bar>

                <!-- Edit/Add dialog -->
                <v-dialog v-if="edit_dialog" v-model="edit_dialog" max-width="500px">
                    <v-card>
                        <v-card-title><span class="headline">{{ is_creating ? "Create New Page" : "Edit" }}</span></v-card-title>
                        <v-card-text>
                            <v-container>
                                <v-row>
                                    <v-col cols="12" sm="12" md="12">
                                        <v-text-field v-if="is_creating" v-model="in_edit.username" label="Username"></v-text-field>
                                        <v-text-field v-if="!is_creating" v-model="in_edit.new_username" label="New Username"></v-text-field>
                                    </v-col>
                                    <v-col cols="12" sm="12" md="12">
                                        <v-text-field :append-icon="show_flags.first ? 'mdi-eye' : 'mdi-eye-off'" 
                                            @click:append="show_flags.first = !show_flags.first"
                                            :type="show_flags.first ? 'text' : 'password'"
                                            :rules="rules" hint="At least 8 characters, with numbers and characters"
                                            v-model="in_edit.password" label="Password"></v-text-field>
                                    </v-col>
                                    <v-col cols="12" sm="12" md="12">
                                        <v-text-field :append-icon="show_flags.second ? 'mdi-eye' : 'mdi-eye-off'"
                                        :type="show_flags.second ? 'text' : 'password'"
                                        @click:append="show_flags.second = !show_flags.second"
                                        :rules="rules" hint="At least 8 characters, with numbers and characters"
                                        v-model="repeat_password" label="Repeat Password"></v-text-field>
                                    </v-col>             
                                </v-row>
                            </v-container>
                        </v-card-text>

                        <v-card-actions>
                            <v-spacer></v-spacer>
                            <v-btn color="primary" text @click="save()">{{ is_creating ? "Create" : "Save" }}</v-btn>
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
                <v-btn icon @click="editAdmin(item)">
                    <v-icon>
                        mdi-pencil-circle
                    </v-icon>
                </v-btn>
        
                <v-btn icon @click="removeAdmin(item)">
                    <v-icon>
                        mdi-delete-circle
                    </v-icon>
                </v-btn>
            </template>
        </v-data-table>
        
            <!-- Delete dialog -->
                <v-dialog v-model="delete_dialog" max-width="500px">
                    <v-card>
                        <v-card-title>Sure?</v-card-title>
                        <v-card-text>
                            Deleting an important page might lead to an information leak
                        </v-card-text>
                        <v-divider></v-divider>
                        <v-card-actions>
                            <v-spacer></v-spacer>
                            <v-btn color="primary" text @click="removeAdmin()">Remove</v-btn>
                            <v-btn text @click="delete_dialog = false">Cancel</v-btn>
                        </v-card-actions>
                    </v-card>
                </v-dialog>
    </v-container>
</template>
<style scoped>
.ca_table {
    width: 100%;
}
</style>