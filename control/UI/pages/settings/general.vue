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
        data() {
            return {
                files: [],
                edit_dialog: false,
                is_importing: null,
                items: [
                    {
                        icon: 'mdi-cached',
                        title: 'Refresh Cache',
                        subtitle: 'Refresh Cache And Continue Using The System',
                        button_text: 'Refresh',
                        action: () => { 
                            gholder.refreshCache();
                        }
                    },
                    {
                        icon: 'mdi-import',
                        title: 'Import Database',
                        subtitle: 'Load SQL File To System & Refresh',
                        button_text: 'Import',
                        action: () => {
                            gholder.edit_dialog = true;
                            gholder.checkbox = false;
                            gholder.is_importing = true;
                        }
                    },
                    {
                        icon: 'mdi-export',
                        title: 'Export Database',
                        subtitle: 'Download SQL File From System',
                        button_text: 'Export',
                        action: () => {
                            gholder.edit_dialog = true;
                            gholder.is_importing = false;
                        }
                    }
                ],
                to_send: null,
                is_busy: false,
                is_checked: false,
                database_list: [],
                selected_db: 'main'
            };
        },
        created() {
            APIShift.Loader.changeLoader("general_settings", this.loader);
            window.gholder = this;
            this.updateDatabases();
        },
        methods: {
            discard: function() {
                if(this.is_creating && !this.discard_dialog) {
                    this.discard_dialog = true;
                    return;
                }
                if(this.is_creating) this.is_creating = false;
                this.edit_dialog = false;
                this.discard_dialog = false;
            },
            updateDatabases: function() {
                APIShift.API.request("Admin\\Database\\DatabaseList", "getDatabaseList", {}, function (response) {
                    if(response.status == APIShift.API.status_codes.SUCCESS) {
                        gholder.database_list =  [ {id: 1, name: 'main'}, ...Object.assign([], response.data)];
                    } else {
                        APIShift.API.notify(response.data, "error");
                    }
                }, true);
            },
            refreshCache: function() {
                APIShift.API.request("Admin\\Settings", "refreshCache", {}, (response) => {
                    APIShift.API.notify(response.data, 'success');
                });
            },
            importDatabase: async function () {
                if (this.is_checked) {
                    APIShift.API.request("Admin\\Settings", "importDatabase", {load_initial: true}, function(response) {
                        if(response.status === APIShift.API.status_codes.SUCCESS) {
                            APIShift.API.notify(response.data, 'success');
                            gholder.edit_dialog = false;
                        }
                        else {
                            APIShift.API.notify(response.data, 'error');
                        }
                        gholder.is_busy = true;
                    });                    
                }
                else if (this.files.length > 0) {
                    to_send = await this.files.pop().arrayBuffer();
                    APIShift.API.request("Admin\\Settings", "importDatabase", {file_blob: to_send}, function(response) {
                        if(response.status === APIShift.API.status_codes.SUCCESS) {
                            APIShift.API.notify(response.data, 'success');
                            gholder.edit_dialog = false;
                        }
                        else {
                            APIShift.API.notify(response.data, 'error');
                        }
                        gholder.is_busy = true;
                    });
                }
            },
            downloadDatabaseFile: function () {
                APIShift.API.request("Admin\\Settings", "exportDatabase", { database_name: gholder.selected_db }, function(response) {
                    if(response.status === APIShift.API.status_codes.SUCCESS) {
                        APIShift.API.notify("Exported successfully", 'success');
                        
                        /// Add a download link, click it and trick browser to download the file 
                        var element = document.createElement('a');
                        element.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(response.data));
                        element.setAttribute('download', gholder.selected_db.concat('.sql'));
                        element.style.display = 'none';
                        document.body.appendChild(element);
                        element.click();
                        document.body.removeChild(element);
    
                        // Close dialog
                        gholder.edit_dialog = false;
                    }
                    else {
                        APIShift.API.notify(response.data, 'error');
                    }
                    gholder.is_busy = true;
                });
            },
            exportAsInitial: function () {
                APIShift.API.request("Admin\\Settings", "exportAsInitialDatabase", { }, function(response) {
                    if(response.status === APIShift.API.status_codes.SUCCESS) {
                        APIShift.API.notify("Exported successfully", 'success');
    
                        // Close dialog
                        gholder.edit_dialog = false;
                    }
                    else {
                        APIShift.API.notify(response.data, 'error');
                    }
                    gholder.is_busy = true;
                });
            }
        },
        computed: {
            database_names: function() {
                return this.database_list.map((db) => db.name);
            }
        },
        watch: {
            edit_dialog: function (edit_dialog) {
                this.selected_db = "main";
            }
        }
    };
</script>

<template>
        <v-container class="main_container" fluid>
            <v-card class="mx-auto" width="25%" color="light-blue lighten-1"><v-toolbar-title class="version_header">General Settings</v-toolbar-title></v-card>
            <v-card class="mx-auto" width="50%" tile>
                <v-list three-line>
                    <v-subheader>General System Settings</v-subheader>
                    <v-list-item-group color="primary">
                        <v-list-item v-for="(item, i) in items" :key="i" v-ripple="false">
                            <v-list-item-icon>
                                <v-icon >{{ item.icon }}</v-icon>
                            </v-list-item-icon>
                            <v-list-item-content>
                                <v-list-item-title v-text="item.title"></v-list-item-title>
                                <v-list-item-subtitle v-text="item.subtitle"></v-list-item-subtitle>
                            </v-list-item-content>
                            <v-spacer></v-spacer>
                            <v-btn
                                elevation="2"
                                @click="item.action">

                                {{ item.button_text }}
                            </v-btn>
                        </v-list-item>
                    </v-list-item-group>
                </v-list>
            </v-card>

            <!-- Import/Export Modal -->
            <v-dialog v-if="edit_dialog" v-model="edit_dialog" max-width="500px">
                <v-card>
                    <v-card-title><span class="headline"> {{ (is_importing ? "Import" : "Export") + " Database"}}</span></v-card-title>
                    <v-card-text>
                        <v-container>
                            <template v-if="is_importing">
                                <v-checkbox v-model="is_checked" label="Load initial SQL file"></v-checkbox>
                                <v-file-input
                                    v-model="files"
                                    color="deep-purple accent-4"
                                    counter
                                    label="File input"
                                    multiple
                                    prepend-icon="mdi-paperclip"
                                    outlined
                                    accept=".sql"
                                    :disabled="is_checked"
                                    :show-size="1000">
                                </v-file-input>
                            </template>
                            <template v-else>
                                <v-select
                                    :items="database_list"
                                    item-text="name"
                                    :label="database_list[0]?.name"
                                    v-model="selected_db"
                                    single-line
                                ></v-select>
                            </template>
                            <v-card-actions>
                                <v-btn 
                                    v-if="is_importing" text
                                    @click="importDatabase()"
                                    :disabled="files.length === 0 && !is_checked">
                                        Import
                                </v-btn>
                                <template v-if="!is_importing">
                                    <v-btn 
                                        text
                                        @click="downloadDatabaseFile()">
                                            Download
                                    </v-btn>
                                    <v-btn 
                                        text
                                        @click="exportAsInitial()">
                                            Export As Initial
                                    </v-btn>
                                </template>
                                
                                <v-spacer></v-spacer>
                                <v-btn text @click="discard()">Cancel</v-btn>
                            </v-card-actions>
                        </v-container>
                    </v-card-text>
                </v-card>
            </v-dialog>
        </v-container>
</template>
<style scoped>
.version_header {
    text-align: center;
}

.main_container > * {
    margin-bottom: 20px;
}
</style>