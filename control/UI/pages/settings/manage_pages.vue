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
                page_list: [],
                search: '',
                loader: {
                    visible: false,
                    message: "",
                    processes: 0
                },
                headers: [
                    { text: 'Name', value: 'name' },
                    { text: 'Path', value: 'modified_path' },
                    { text: 'Icon', value: 'icon' },
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
            APIShift.Loader.changeLoader("page_list", this.loader);
            window.pholder = this;
            this.updatePages();
        },
        methods: {
            updatePages: function() {
                APIShift.API.request("Admin\\Settings\\PageList", "getPagesList", {}, function (response) {
                    if(response.status == APIShift.API.status_codes.SUCCESS) {
                        pholder.page_list = Object.assign([], response.data);
                        
                        for(let page in pholder.page_list) {
                            // Render default value for missing icons
                            if (!pholder.page_list[page].icon) pholder.page_list[page].icon = '-';

                            // Check if parent calculated
                            if(pholder.page_list[page].path_calcualted !== undefined) continue;

                            // Caclculate full path using parents
                            let current_parrent = pholder.page_list[page].parent;
                            pholder.page_list[page].modified_path = pholder.page_list[page].path;
                            
                            while(current_parrent != 0) {
                                // Find element - binary search preferrable
                                let parent_page = pholder.page_list.find((page) => page.id == current_parrent);
                                // Add path
                                pholder.page_list[page].modified_path = parent_page.path + "/" + pholder.page_list[page].modified_path;
                                // Move to next parent
                                if(parent_page.path_calcualted !== undefined) break;
                                current_parrent = parent_page.parent;
                            }

                            // Add calculated notice
                            pholder.page_list[page].path_calcualted = true;
                        }
                    } else {
                        APIShift.API.notify(response.data, "error");
                    }
                }, true);
            },
            createPage: function () {
                this.is_creating = true;
                this.edit_dialog = true;
                this.in_edit = {
                    name: '',
                    path: '',
                    icon: ''
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
                    || this.in_edit.path === ""
                    || this.in_edit.icon === "") {
                    APIShift.API.notify("Please fill in valid data", 'warning');
                    return;
                }

                let to_send = Object.assign({}, this.in_edit);
                to_send.modified_path = undefined;
                to_send.path_calcualted = undefined;

                APIShift.API.request("Admin\\Settings\\PageList", this.is_creating ? "createPage" : "editPage", to_send, function(response) {
                    if(response.status === APIShift.API.status_codes.SUCCESS) {
                        APIShift.API.notify(response.data, 'success');
                    }
                    else {
                        APIShift.API.notify(response.data, 'error');
                    }
                    pholder.updatePages();
                });

                this.is_creating = false;
                this.edit_dialog = false;
                return;
            },
            editPage: function(page) {
                this.edit_dialog = true;
                this.in_edit = page;
            },
            removePage: function(page = this.in_edit) {
                if(!this.delete_dialog) {
                    this.in_edit = page;
                    this.delete_dialog = true;
                    return;
                }

                APIShift.API.request("Admin\\Settings\\PageList", "removePage", { id: this.in_edit.id }, function(response) {
                    if(response.status === APIShift.API.status_codes.SUCCESS) {
                        APIShift.API.notify(response.data, 'success');
                    }
                    else {
                        APIShift.API.notify(response.data, 'error');
                    }
                    pholder.updatePages();
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
            :items="page_list"
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
                                    <v-col cols="12" sm="6" md="6">
                                        <v-text-field v-model="in_edit.name" label="Name"></v-text-field>
                                    </v-col>
                                    <v-col cols="12" sm="6" md="6">
                                        <v-text-field v-model="in_edit.path" label="Path"></v-text-field>
                                    </v-col>
                                    <v-col cols="12" sm="6" md="6">
                                        <v-text-field v-model="in_edit.icon" label="Icon"></v-text-field>
                                    </v-col> 
                                    <v-col cols="12" sm="6" md="6">
                                        <v-select
                                        v-model="in_edit.parent"
                                        :items="[{id: 0, path: 'No Parent'}].concat(page_list)"
                                        item-text="path"
                                        item-value="id"
                                        label="Parent"
                                        required
                                        ></v-select>
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
                <v-btn icon @click="editPage(item)">
                    <v-icon>
                        mdi-pencil-circle
                    </v-icon>
                </v-btn>
                
                        <v-btn icon @click="removePage(item)">
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
                            <v-btn color="primary" text @click="removePage()">Remove</v-btn>
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