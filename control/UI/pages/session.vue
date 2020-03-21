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
                current_parent: 0,
                in_edit: 0
            }
        },
        created() {
            window.handler = this;
            APIShift.API.request("SessionState", "getAllSessionStates", {}, function(response) {
                if(response.status == true) {
                    handler.states_collection = response.data;
                } else {
                    APIShift.API.notify("Couldn't retrieve statuses", 'error');
                }
            }, true);
        },
        methods: {
            startEdit: function(id) {
                if(this.in_edit == 0) this.in_edit = id;
                else {
                    APIShift.API.notify("Edit in progress");
                }
            },
            saveEdit: function() {
                this.in_edit = 0;
            }
        },
    };
</script>

<template>
    <v-content>
        <v-container fluid fill-height>
            <v-card class="mx-auto" width="90%" min-height="75%" elevation="2">
                <v-app-bar>
                    <v-toolbar-title>Manage Sessions</v-toolbar-title>
                    <v-spacer></v-spacer>
                    <v-tooltip top>
                        <template v-slot:activator="{ on }">
                            <v-btn icon v-on="on">
                                <v-icon>mdi-plus-circle</v-icon>
                            </v-btn>
                        </template>
                        <span>Add new session state</span>
                    </v-tooltip>
                </v-app-bar>
                <div class="overflow-box" v-bar :class="$vuetify.theme.dark == true ? 'dark_bar' : 'light_bar'">
                    <div>
                        <v-layout class="mx-auto" align-start justify-center row wrap>
                            <v-hover v-for="(val, key) in states_collection" :key="key" v-slot:default="{ hover }" v-if="val.parent == current_parent">
                                <v-card outlined class="px-0 session-card" :elevation="hover ? 16 : 2">
                                    <v-toolbar>
                                        <v-toolbar-title>{{ val.name }}</v-toolbar-title>
                                        <v-spacer></v-spacer>
                                        <v-tooltip top>
                                            <template v-slot:activator="{ on }">
                                                <v-btn v-if="key == in_edit" icon v-on="on" @click="saveEdit()">
                                                    <v-icon>mdi-checkbox-marked-circle</v-icon>
                                                </v-btn>
                                                <v-btn v-else icon v-on="on" @click="startEdit(key)">
                                                    <v-icon>mdi-pencil-circle</v-icon>
                                                </v-btn>
                                            </template>
                                            <span>Edit</span>
                                        </v-tooltip>
                                        <v-tooltip top>
                                            <template v-slot:activator="{ on }">
                                            <v-btn icon v-on="on">
                                                <v-icon>mdi-minus-circle</v-icon>
                                            </v-btn>
                                            </template>
                                            <span>Remove</span>
                                        </v-tooltip>
                                        <v-tooltip top>
                                            <template v-slot:activator="{ on }">
                                            <v-btn icon v-on="on" to="/access">
                                                <v-icon small>fas fa-lock</v-icon>
                                            </v-btn>
                                            </template>
                                            <span>Define Authorization Process</span>
                                        </v-tooltip>
                                    </v-toolbar>

                                    <v-card-text>
                                        <v-form>
                                            <v-tooltip top>
                                                <template v-slot:activator="{ on }">
                                                    <v-text-field v-on="on"
                                                        class="session_field"
                                                        type="text" label="name"
                                                        v-model="val.name"
                                                        :disabled="in_edit != key"></v-text-field>
                                                </template>
                                                <span>Session Name</span>
                                            </v-tooltip>
                                            <v-tooltip top>
                                                <template v-slot:activator="{ on }">
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
                                                <template v-slot:activator="{ on }">
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