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
                search: '',
                tasks_collection: [],
                process_list: [],
                in_edit: -1,
                is_creating: false,
                edit_dialog: {
                    is_open: false,
                    name: '',
                },
                delete_dialog: false,
                discard_dialog: false,
                adding_state: false,
                key_names: [],
                // Installers personal loader
                loader: {
                    visible: false,
                    message: "",
                    processes: 0
                },
            }
        },
        created() {
            window.handler = this;
            this.updateTasksCollection();
            this.updateProcessList();

            APIShift.Loader.changeLoader("task_view", this.loader);
        },
        methods: {
            updateTasksCollection: function() {
                // Get all Tasks and all Processes associated with the Tasks
                APIShift.API.request("Admin\\Logic\\Tasks", "getAllTasks", {}, function(response) {
                    if(response.status == APIShift.API.status_codes.SUCCESS) 
                        handler.tasks_collection = response.data;
                    else 
                        APIShift.API.notify(response.data, 'error');
                });
            },
            onEditSubmit (is_creating = false) {
                let data = {
                    id: is_creating ? -1 : handler.tasks_collection[this.in_edit].id,
                    name: handler.edit_dialog.name,
                    processes: []
                };

                this.edit_dialog.process_list.forEach((process) => {
                    if (process.is_checked) data.processes.push(process.id);
                });

                // Get all Tasks and all Processes associated with the Tasks
                APIShift.API.request("Admin\\Logic\\Tasks", is_creating ? "createTask" : "updateTask", data, function(response) {
                    handler.updateTasksCollection();
                    if(response.status != APIShift.API.status_codes.SUCCESS)
                        APIShift.API.notify(response.data, 'error');
                });
            },
            updateProcessList() {
                APIShift.API.request("Admin\\Logic\\Processes", "getAllProcesses", {}, function(response) {
                    if(response.status == APIShift.API.status_codes.SUCCESS) {
                        handler.process_list = response.data.map(d => ({...d, is_checked: false}));
                    }
                    else APIShift.API.notify(response.data, 'error');
                });
            },
            createTask () {
                handler.is_creating = true; 
                handler.in_edit = handler.process_list.length;
                handler.tasks_collection.push({ id: -1, name: '', processes: []}); 
                handler.edit_dialog.is_open = true;
            },
            deleteTask (task_index) {
                let data = {
                    id: handler.tasks_collection[task_index].id
                };

                // Get all Tasks and all Processes associated with the Tasks
                APIShift.API.request("Admin\\Logic\\Tasks", "deleteTask", data, function(response) {
                    handler.updateTasksCollection();
                    if(response.status != APIShift.API.status_codes.SUCCESS)
                        APIShift.API.notify(response.data, 'error');
                });
            }
        },
        watch: {
            'edit_dialog.is_open' (is_open) {
                if (is_open) {
                    this.edit_dialog.name = this.tasks_collection[this.in_edit].name;
                    this.process_list = this.process_list.map (p => ({...p, is_checked: this.tasks_collection[this.in_edit].processes.findIndex(o => o == p.id) !== -1}));
                } else {
                    this.edit_dialog.name = '';
                    this.process_list.forEach( p => p.is_checked = false);
                }
            }
        }
    }
    
</script>

<template>
        <v-container class="session-display" fluid fill-height>
            <v-card class="mx-auto" width="100%" min-height="75%" elevation-2>
                <!-- Header -->
                <v-app-bar>
                    <v-toolbar-title><v-btn >Task Viewer</v-btn></v-toolbar-title>
                    <v-spacer></v-spacer>
                    <v-tooltip top>
                        <template #activator="{ on }">
                            <v-btn icon v-on="on" @click="createTask()">
                                <v-icon v-if="adding_state">mdi-close-circle</v-icon>
                                <v-icon v-else>mdi-plus-circle</v-icon>
                            </v-btn>
                        </template>
                        <span v-if="adding_state">Discard new session state</span>
                        <span v-else>Add new Task</span>
                    </v-tooltip>
                    
                            <v-progress-linear
                                :active="loader.processes"
                                :indeterminate="loader.processes"
                                :dismissible="loader.dismissible !== undefined && loader.dismissible"
                                absolute
                                bottom
                                color="primary"
                            ></v-progress-linear>
                </v-app-bar>

                <!-- Body -->
                <div class="overflow-box" v-bar>
                    <div>
                        <v-layout class="mx-auto" align-start justify-center row wrap>
                            <!-- Iterate through Task collections and show them -->
                            <v-hover v-for="(val, key) in tasks_collection" :key="key" v-slot:default="{ hover }">
                                <v-card outlined class="px-0 session-card" :elevation="hover ? 24 : 4">
                                    <!-- Task header with name & actions -->
                                    <v-toolbar>
                                        <v-icon>mdi-code-greater-than</v-icon>
                                        <v-spacer></v-spacer>
                                        <v-toolbar-title>{{ val.name  }}</v-toolbar-title>
                                        
                                        <v-spacer></v-spacer>
                                        <!-- Edit Task dialog & trigger -->
                                        <v-tooltip top>
                                            <template #activator="{ on }">
                                                <v-btn icon v-on="on" @click="in_edit = key; edit_dialog.is_open = true;">
                                                    <v-icon>mdi-pencil-circle</v-icon>
                                                </v-btn>
                                            </template>
                                            <span>Edit</span>
                                        </v-tooltip>

                                        <!-- Delete Task -->
                                        <v-tooltip top>
                                            <template #activator="{ on }">
                                                <v-btn icon v-on="on" @click="deleteTask(key)">
                                                    <v-icon>mdi-close-circle</v-icon>
                                                </v-btn>
                                            </template>
                                            <span>Delete</span>
                                        </v-tooltip>
                                    </v-toolbar>

                                    <!-- Task <-> Process table -->
                                    <v-list style="height: 192px; overflow-y:auto" >
                                            <v-list-item v-for="item in val.processes.map(p => handler.process_list.find(process => process.id == p)).filter(p => p)" :key="item">
                                                <v-list-item-action>
                                                    <v-icon>mdi-lightning-bolt</v-icon>
                                                </v-list-item-action>

                                                <v-list-item-content>
                                                    <v-list-item-title>
                                                        {{ item.name }}
                                                    </v-list-item-title>
                                                </v-list-item-content>
                                            </v-list-item>
                                    </v-list>

                                    <v-tooltip top>
                                        <template #activator="{ on }">
                                            <v-btn block v-on="on" style="padding: 0px;">
                                                <v-icon>mdi-plus-circle</v-icon>
                                            </v-btn>
                                        </template>
                                        <span>New Process</span>
                                    </v-tooltip>
                                </v-card>
                            </v-hover>
                            
                            <!-- Delete Task dialog -->
                            <v-dialog v-model="delete_dialog" max-width="450px" >
                                <v-card>
                                    <v-card-title>Sure?</v-card-title>
                                    <v-card-text>
                                        Deleting the session state will break all the state changes happening to it.
                                    </v-card-text>
                                    <v-divider></v-divider>
                                    <v-card-actions>
                                        <v-spacer></v-spacer>
                                        <v-btn color="primary" text>Remove</v-btn>
                                        <v-btn text @click="delete_dialog = false;">Cancel</v-btn>
                                    </v-card-actions>
                                </v-card>
                            </v-dialog>

                            <!-- Edit Task dialog -->
                            <v-dialog max-width="450px" v-model="edit_dialog.is_open" v-if="edit_dialog.is_open && in_edit !== undefined" >
                                <v-card>
                                    <v-card-title>Task Editor</v-card-title>
                                    <v-col>
                                        <v-spacer></v-spacer>
                                        <v-container>
                                            <v-text-field
                                                label="Name"
                                                :value="is_creating ? '' : tasks_collection[in_edit].name"
                                                v-model="edit_dialog.name"
                                            ></v-text-field>

                                            <v-list style="height: 192px; overflow-y:auto">
                                                <v-list-item v-for="(process, index) in process_list" :key="index">
                                                        <v-row>
                                                            <v-list-item-action>
                                                                <v-checkbox v-model="process.is_checked"></v-checkbox>
                                                            </v-list-item-action>

                                                            <v-list-item-content>
                                                                <v-list-item-title>
                                                                    <v-icon>mdi-lightning-bolt</v-icon>
                                                                    {{ process_list.find(p => p.id == process.id)?.name }}
                                                                </v-list-item-title>
                                                            </v-list-item-content>
                                                        </v-row>
                                                </v-list-item>
                                            </v-list>
                                        </v-container>
                                        <v-spacer></v-spacer>

                                    <!-- All Processes scrollable table  -->
                                    <v-tooltip top>
                                        <template #activator="{ on }">
                                            <v-btn block v-on="on" style="padding: 0px;">
                                                <v-icon>mdi-plus-circle</v-icon>
                                            </v-btn>
                                        </template>
                                        <span>New Process</span>
                                    </v-tooltip>
                                    </v-col>
                                    <v-spacer></v-spacer>
                                    <v-divider></v-divider>
                                    <!-- Actions  -->
                                    <v-card-actions>
                                        <v-spacer></v-spacer>
                                        <v-btn @click="edit_dialog.is_open = false;">Close</v-btn>
                                        <v-btn color="primary" @click="onEditSubmit(is_creating); edit_dialog.is_open = undefined;" text>Submit</v-btn>
                                    </v-card-actions>
                                </v-card>
                            </v-dialog>

                            <!-- Discard state dialog -->
                            <v-dialog v-model="discard_dialog" max-width="450px">
                                <v-card>
                                    <v-card-title>Sure?</v-card-title>
                                    <v-card-text>
                                        You cannot go back to changes you've discarded
                                    </v-card-text>
                                    <v-divider></v-divider>
                                    <v-card-actions>
                                        <v-spacer></v-spacer>
                                        <v-btn color="primary" text>Discard</v-btn>
                                        <v-btn text>Cancel</v-btn>
                                    </v-card-actions>
                                </v-card>
                            </v-dialog>
                        </v-layout>
                    </div>
                </div>
            </v-card>
        </v-container>
</template>
<style scoped>

.session-display {
    align-items: baseline;
}

.session-card {
    margin: 5px;
    min-width: 300px;
}

.session_field {
    padding-top: 0;
    padding-bottom: 0;
}



.v-card__actions {
    display: block;
}

.v-card__actions > * {
    margin-left: 0!important;
}

</style>