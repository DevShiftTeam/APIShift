<script>
    /**
     * APIShift Engine v1.0.0
     * (c) 2020-present Sapir Shemer, DevShift (devshift.biz)
     * Released under the MIT License with the additions present in the LICENSE.md
     * file in the root folder of the APIShift Engine original release source-code
     * @author Sapir Shemer
     */
    module.exports = {
        data() {
            return {
                states_collection: {},
                editor_previous: {},
                current_parent: 0,
                in_edit: 0,
                delete_dialog: false,
                discard_dialog: false
            }
        },
        created() {
            window.handler = this;
            APIShift.API.request("SessionState", "getAllSessionStates", {}, function(response) {
                if(response.status == true) {
                    handler.states_collection = response.data;
                }
                else {
                    APIShift.API.notify(response.data, 'error');
                }
            }, true);
        },
        methods: {
            createState: function() {

            },
            saveState: function () {

            },
            startStateEdit: function(id) {
                if(this.in_edit != 0) {
                    APIShift.API.notify("Edit in progress", 'error');
                    return;
                }
                if(this.states_collection[id] === undefined) {
                    APIShift.API.notify("State doesn't exist", 'error');
                    return;
                }

                this.in_edit = id;
                // Store copy of the item undergoing edit
                this.editor_previous = Object.assign({}, this.states_collection[this.in_edit]);
            },
            saveStateEdit: function() {
                this.in_edit = 0;
            },
            discardStateEdit: function() {
                // Show dialog if there are unsaved changes
                if(!this.discard_dialog
                && (this.editor_previous.name != this.states_collection[this.in_edit].name
                || this.editor_previous.active_time != this.states_collection[this.in_edit].active_time
                || this.editor_previous.inactive_time != this.states_collection[this.in_edit].inactive_time)) {
                    this.discard_dialog = true;
                    return;
                }

                // Close dialog & revert changes
                this.discard_dialog = false;
                this.states_collection[this.in_edit] = this.editor_previous;
                this.in_edit = 0;
            },
            deleteState: function(id) {
                // Close dialog
                this.delete_dialog = false;
            }
        },
    };
</script>

<template>
    <v-content>
        <v-container class="session-display" fluid fill-height>
            <v-card class="mx-auto" width="90%" min-height="75%" elevation="2">
                <!-- Header -->
                <v-app-bar>
                    <v-toolbar-title>Manage Sessions</v-toolbar-title>
                    <v-spacer></v-spacer>
                    <v-tooltip top>
                        <template #activator="{ on }">
                            <v-btn icon v-on="on">
                                <v-icon>mdi-plus-circle</v-icon>
                            </v-btn>
                        </template>
                        <span>Add new session state</span>
                    </v-tooltip>
                </v-app-bar>

                <!-- Body -->
                <div class="overflow-box" v-bar :class="$vuetify.theme.dark == true ? 'dark_bar' : 'light_bar'">
                    <div>
                        <v-layout class="mx-auto" align-start justify-center row wrap>
                            <!-- Iterate through session states and show them -->
                            <v-hover v-for="(val, key) in states_collection" :key="key" v-slot:default="{ hover }" v-if="val.parent == current_parent">
                                <v-card outlined class="px-0 session-card" :elevation="hover ? 16 : 2">
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
                                                <v-btn icon v-on="on" @click="delete_dialog = true">
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
                                                    <v-btn color="primary" text @click="deleteState(key)">Remove</v-btn>
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
                                        <v-btn text color="purple accent-4" width="100%">
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

.overflow-box {
    height: 65vh;
}

</style>