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
                states_collection: {},
                editor_previous: {},
                current_parent: 0,
                parents_array: [],
                in_edit: 0,
                delete_dialog: false,
                discard_dialog: false,
                adding_state: false
            }
        },
        created() {
            window.handler = this;
            this.updateSessionStates();
        },
        methods: {
            /**
             * Set which children states are being viewed
             */
            setParentView: function(parent) {
                if(this.in_edit != 0) {
                    this.discardStateEdit();
                    return;
                }
                if(parent == 0) {
                    this.parents_array = []; // Empty parents
                    this.current_parent = 0;
                    return;
                }
                if(this.states_collection[parent] == undefined) {
                    APIShift.API.notify("Parent doesn't exist", 'error');
                    return;
                }

                this.parents_array = []; // Empty parents
                let parent_holder = this.states_collection[parent];
                this.parents_array.push({ id: parent , name: parent_holder.name});
                while(parent_holder.parent != 0) {
                    this.parents_array.push({ id: parent_holder.parent , name: this.states_collection[parent_holder.parent].name});
                    parent_holder = this.states_collection[parent_holder.parent];
                }
                this.current_parent = parent;
            },
            updateSessionStates: function() {
                APIShift.API.request("Admin\\SessionState", "getAllSessionStates", {}, function(response) {
                    if(response.status == true) {
                        handler.states_collection = Object.assign({}, response.data);
                    }
                    else {
                        APIShift.API.notify(response.data, 'error');
                    }
                });
            },
            createState: function() {
                // Show discard state dialog
                if(this.in_edit != 0) {
                    this.discard_dialog = true;
                    return;
                }

                let biggest = 0;
                for(key in this.states_collection) if(key > biggest) biggest = key;
                this.adding_state = true;
                
                // Add next ID
                this.in_edit = Number.parseInt(biggest) + 1;
                this.states_collection[this.in_edit] = {
                    name: 'new' + (Object.keys(this.states_collection).length + 1),
                    active_timeout: 0,
                    inactive_timeout: 0,
                    parent: this.current_parent
                };
                this.editor_previous = Object.assign({}, this.states_collection[this.in_edit]);
            },
            startStateEdit: function(id) {
                if(this.in_edit != 0) {
                    this.discardStateEdit();
                    return;
                }
                if(this.states_collection[id] === undefined) {
                    APIShift.API.notify("State doesn't exist", 'error');
                    return;
                }

                this.in_edit = id;
                // Store copy of the item undergoing edit
                this.editor_previous = JSON.parse(JSON.stringify(this.states_collection[this.in_edit]));
            },
            saveStateEdit: function() {
                if(this.editor_previous.name == this.states_collection[this.in_edit].name
                && this.editor_previous.active_timeout == this.states_collection[this.in_edit].active_timeout
                && this.editor_previous.inactive_timeout == this.states_collection[this.in_edit].inactive_timeout
                && !this.adding_state) {
                    this.in_edit = 0;
                    APIShift.API.notify("Nothing to save", 'warning');
                    return;
                }

                // Create a new state
                if(this.adding_state) {
                    this.adding_state = false
                    APIShift.API.request("Admin\\SessionState", "addSessionState", this.states_collection[this.in_edit], function(response) {
                        if(response.status == true) {
                            APIShift.API.notify(response.data, 'success');
                        }
                        else {
                            APIShift.API.notify(response.data, 'error');
                        }
                        handler.in_edit = 0;
                        handler.updateSessionStates();
                    }, true);
                    return;
                }
                // Edit existing state
                let to_send = {};
                // Construct data to update
                to_send.id = this.in_edit;
                if(this.editor_previous.name != this.states_collection[this.in_edit].name)
                    to_send.name = this.states_collection[this.in_edit].name;
                if(this.editor_previous.active_timeout != this.states_collection[this.in_edit].active_timeout)
                    to_send.active_timeout = this.states_collection[this.in_edit].active_timeout;
                if(this.editor_previous.inactive_timeout != this.states_collection[this.in_edit].inactive_timeout)
                    to_send.inactive_timeout = this.states_collection[this.in_edit].inactive_timeout;
                // Update
                APIShift.API.request("Admin\\SessionState", "updateSessionState", to_send, function(response) {
                    if(response.status == APIShift.API.status_codes.SUCCESS) {
                        APIShift.API.notify(response.data, 'success');
                    }
                    else {
                        APIShift.API.notify(response.data, 'error');
                    }
                    handler.in_edit = 0;
                    handler.updateSessionStates();
                }, true);
            },
            discardStateEdit: function() {
                // Show dialog if there are unsaved changes
                if(!this.discard_dialog
                && ((this.editor_previous.name != this.states_collection[this.in_edit].name
                || this.editor_previous.active_timeout != this.states_collection[this.in_edit].active_timeout
                || this.editor_previous.inactive_timeout != this.states_collection[this.in_edit].inactive_timeout) || this.adding_state)) {
                    this.discard_dialog = true;
                    return;
                }

                // Close dialog & revert changes
                if(this.adding_state) {
                    delete this.states_collection[this.in_edit];
                    this.adding_state = false;
                }
                else {
                    this.states_collection[this.in_edit].name = this.editor_previous.name;
                    this.states_collection[this.in_edit].active_timeout = this.editor_previous.active_timeout;
                    this.states_collection[this.in_edit].inactive_timeout = this.editor_previous.inactive_timeout;
                    delete this.editor_previous;
                }
                this.in_edit = 0;
                this.discard_dialog = false;
            },
            deleteState: function(id) {
                if(!this.delete_dialog) {
                    this.in_edit = id;
                    this.delete_dialog = true;
                    return;
                }

                APIShift.API.request("Admin\\SessionState", "removeSessionState", { 'id' : this.in_edit }, function(response) {
                    if(response.status == true) {
                        APIShift.API.notify(response.data, 'success');
                    }
                    else {
                        APIShift.API.notify(response.data, 'error');
                    }
                    handler.in_edit = 0;
                    handler.updateSessionStates();
                }, true);
                // Close dialog
                Vue.delete(this.states_collection, id);
                this.delete_dialog = false;
            }
        },
    };
</script>

<template>
    <v-content>
        <v-container class="session-display" fluid fill-height>
            <v-card class="mx-auto" width="90%" min-height="75%" elevation-2>
                <!-- Header -->
                <v-app-bar>
                    <v-toolbar-title><v-btn @click="setParentView(0)">Manage Sessions</v-btn> <v-btn text v-for="(item, key) in parents_array" :key="key" @click="setParentView(item.id)">> {{ item.name }}</v-btn></v-toolbar-title>
                    <v-spacer></v-spacer>
                    <v-tooltip top>
                        <template #activator="{ on }">
                            <v-btn icon v-on="on" @click="createState()">
                                <v-icon v-if="adding_state">mdi-close-circle</v-icon>
                                <v-icon v-else>mdi-plus-circle</v-icon>
                            </v-btn>
                        </template>
                        <span v-if="adding_state">Discard new session state</span>
                        <span v-else>Add new session state</span>
                    </v-tooltip>
                </v-app-bar>

                <!-- Body -->
                <div class="overflow-box" v-bar>
                    <div>
                        <v-layout class="mx-auto" align-start justify-center row wrap>
                            <!-- Iterate through session states and show them -->
                            <v-hover v-for="(val, key) in states_collection" :key="key" v-slot:default="{ hover }" v-if="val !== undefined && val.parent == current_parent">
                                <v-card outlined class="px-0 session-card" :elevation="hover ? 24 : 4">
                                    <!-- Session state header with name & actions -->
                                    <v-toolbar>
                                        <v-toolbar-title>{{ val.name }}</v-toolbar-title>
                                        <v-spacer></v-spacer>
                                        <!-- Discard changes -->
                                        <v-tooltip v-if="key == in_edit" top>
                                            <template #activator="{ on }">
                                                <v-btn icon v-on="on" @click="discardStateEdit()">
                                                    <v-icon>mdi-close-circle</v-icon>
                                                </v-btn>
                                            </template>
                                            <span>Discard Changes</span>
                                        </v-tooltip>
                                        <v-dialog v-model="discard_dialog" max-width="450px">
                                            <v-card>
                                                <v-card-title>Sure?</v-card-title>
                                                <v-card-text>
                                                    You cannot go back to changes you've discarded
                                                </v-card-text>
                                                <v-divider></v-divider>
                                                <v-card-actions>
                                                    <v-spacer></v-spacer>
                                                    <v-btn color="primary" text @click="discardStateEdit()">Discard</v-btn>
                                                    <v-btn text @click="discard_dialog = false">Cancel</v-btn>
                                                </v-card-actions>
                                            </v-card>
                                        </v-dialog>
                                        <!-- Edit state dialog & trigger -->
                                        <v-tooltip top>
                                            <template #activator="{ on }">
                                                <v-btn v-if="key == in_edit" icon v-on="on" @click="saveStateEdit()">
                                                    <v-icon>mdi-checkbox-marked-circle</v-icon>
                                                </v-btn>
                                                <v-btn v-else icon v-on="on" @click="startStateEdit(key)" :disabled="in_edit != key && in_edit != 0">
                                                    <v-icon>mdi-pencil-circle</v-icon>
                                                </v-btn>
                                            </template>
                                            <span v-if="key == in_edit">Save Changes</span>
                                            <span v-else>Edit</span>
                                        </v-tooltip>
                                        <!-- Remove state dialog & trigger -->
                                        <v-tooltip top>
                                            <template #activator="{ on }">
                                                <v-btn icon v-on="on" @click="deleteState(key)" :disabled="(key == in_edit && adding_state) || (in_edit != key && in_edit != 0)">
                                                    <v-icon>mdi-minus-circle</v-icon>
                                                </v-btn>
                                            </template>
                                            <span>Remove</span>
                                        </v-tooltip>
                                        <v-dialog v-model="delete_dialog" max-width="450px">
                                            <v-card>
                                                <v-card-title>Sure?</v-card-title>
                                                <v-card-text>
                                                    Deleting the session state will remove all authorization processes attached to it.
                                                </v-card-text>
                                                <v-divider></v-divider>
                                                <v-card-actions>
                                                    <v-spacer></v-spacer>
                                                    <v-btn color="primary" text @click="deleteState()">Remove</v-btn>
                                                    <v-btn text @click="delete_dialog = false">Cancel</v-btn>
                                                </v-card-actions>
                                            </v-card>
                                        </v-dialog>
                                        <!-- Authorization trigger -->
                                        <v-tooltip top>
                                            <template #activator="{ on }">
                                                <v-btn icon v-on="on" to="/access">
                                                    <v-icon>mdi-lock</v-icon>
                                                </v-btn>
                                            </template>
                                            <span>Define Authorization Process</span>
                                        </v-tooltip>
                                    </v-toolbar>

                                    <v-card-text>
                                        <v-form>
                                            <v-tooltip top>
                                                <template #activator="{ on }">
                                                    <v-text-field v-on="on"
                                                        class="session_field"
                                                        type="text" label="name"
                                                        v-model="val.name"
                                                        :disabled="in_edit != key"></v-text-field>
                                                </template>
                                                <span>Session Name</span>
                                            </v-tooltip>
                                            <v-tooltip top>
                                                <template #activator="{ on }">
                                                    <v-text-field v-on="on"
                                                        class="session_field"
                                                        type="number"
                                                        label="active timeout"
                                                        v-model="val.active_timeout"
                                                        :disabled="in_edit != key"></v-text-field>
                                                </template>
                                                <span>Timeout When User Active (s)</span>
                                            </v-tooltip>
                                            <v-tooltip top>
                                                <template #activator="{ on }">
                                                    <v-text-field v-on="on"
                                                        class="session_field"
                                                        type="number"
                                                        label="inactive timeout"
                                                        v-model="val.inactive_timeout"
                                                        :disabled="in_edit != key"></v-text-field>
                                                </template>
                                                <span>Timeout When User Inactive (s)</span>
                                            </v-tooltip>
                                        </v-form>
                                    </v-card-text>

                                    <v-card-actions>
                                        <v-btn text color="blue accent-4" width="100%">
                                            Edit Structure
                                        </v-btn>
                                        <v-btn text color="purple accent-4" width="100%" @click="setParentView(key)">
                                            View Children
                                        </v-btn>
                                    </v-card-actions>
                                </v-card>
                            </v-hover>
                        </v-layout>
                    </div>
                </div>
            </v-card>
        </v-container>
    </v-content>
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