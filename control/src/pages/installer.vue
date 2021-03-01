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

    // This shit is made for scripting
    module.exports = {
        data () {
            return {
                drawer: null,
                // Installation steps data
                steps: [
                    {
                        name: "Admin configurations",
                        fields: [
                            // regex is used for validating the data before sending it to server for further validation
                            {label: "Admin User", name: "login", icon: "person", type: "text", data: "admin",
                             regex:"^[a-z0-9_-]{5,16}$",format:"At least 5 characters,Max is 16 characters"
                            },
                            {
                                label: "Admin Password", name: "password", icon: "lock", type: "password", data: "",
                                regex: "^(((?=.*[a-z])(?=.*[A-Z]))|((?=.*[a-z])(?=.*[0-9]))|((?=.*[A-Z])(?=.*[0-9])))(?=.{8,})", format: "At least 8 characters, with numbers and characters"
                            },
                            { label: "Repeat Password", name: "repeat_password", icon: "mdi-repeat", type: "password", data: "" }
                        ]
                    },
                    {
                        name: "Database configurations",
                        fields: [
                            {label: "DB Host", name: "db_host", icon: "mdi-server", type: "text", data: "127.0.0.1" },
                            {label: "DB Post", name: "db_port", icon: "mdi-cloud", type: "number", data: 3306 },
                            {label: "DB User", name: "db_user", icon: "mdi-account", type: "text", data: "root" },
                            {label: "DB Name", name: "db_name", icon: "mdi-database", type: "text", data: "" },
                            { label: "DB Password", name: "db_pass", icon: "mdi-lock", type: "password", data: "", can_empty: true }
                        ]
                    },
                    {
                        name: "Cache Configurations",
                        fields: [
                            {label: "Cache Host", name: "cc_host", icon: "mdi-database", type: "text", data: "127.0.0.1", values: [1, 2] },
                            {label: "Cache Port", name: "cc_port", icon: "mdi-cloud", type: "text", data: 6379, values: [1, 2] },
                            {label: "Cache Pass", name: "cc_pass", icon: "mdi-lock", type: "password", data: "", values: [1] }
                        ],
                        selector: { label: "Cache System", name: "cc_system", items: [ 
                                { text: "No Cache", value: 3 },
                                { text: "APCu", value: 0 },
                                { text: "Redis", value: 1 },
                                { text: "Memcached", value: 2 }
                            ], data: 3, icon: ""}
                    },
                    {
                        name: "Site data",
                        fields: [
                            {label: "Site Title", name: "site_name", icon: "mdi-web", type: "text", data: "" },
                            {label: "Site Description", name: "site_desc", icon: "mdi-form-textboxt", type: "text", data: "" },
                            {label: "Site Keys", name: "site_keys", icon: "mdi-key-variant", type: "text", data: "" }
                        ]
                    }
                ],
                // Current step
                current_step: 0,
                // Installers personal loader
                loader: {
                    visible: false,
                    message: "",
                    processes: 0
                },
                // Determines whether an action concerning stage check or jump occurs
                step_action: false
            }
        },
        created () {
            app.apishift.setSubtitle("Installer");
            APIShift.Loader.changeLoader("installer", this.loader);
            APIShift.Loader.close("installer");
        },
        updated () {
            // If step action accurred, then focus on the selected field
            // Should happen after DOM re-render
            if(this.step_action) {
                this.focusOnIncorrectField();
                this.step_action = false;
            }
        },
        methods: {
            /**
             * Check if current step data is filled, returns the incorrect field
             * We call it the incorrect step, so that algorithmically we already know to which field to focus on
             * for the user
             * @param {Number} step_id The step ID to check
             * @param {boolean} update Update the user using a notification
             * @returns The ID of the incorrect field or -1 when all are corrent
             */
            findIncorrectField: function (step_id = null, update = true) {
                var password_check = "";

                for(field_id in this.steps[step_id].fields)
                {
                    var field = this.steps[step_id].fields[field_id];

                    // Skip fields not competent with the selector
                    if(this.steps[step_id].selector !== undefined && !field.values.includes(this.steps[step_id].selector.data))
                        continue;
                    
                    // Not empty
                    if(!field.can_empty && field.data == "")
                    {
                        if(update) APIShift.API.notify("Please fill in all data correctly first", "warning");
                        return field_id; // return field who is incorrect or empty
                    }

                    // Regex invalid
                    if(field.regex !== undefined && !RegExp(field.regex).test(field.data)) {
                        if(update) APIShift.API.notify("Invalid format: " + field.format, "warning");
                        return field_id; // return field who is incorrect or empty
                    }

                    // Check if password repeat is identical if present
                    if (field.name == "password" || field.name == "repeat_password")
                    {
                        if(password_check == "") password_check = field.data;
                        else if (password_check != field.data)
                        {
                            if(update) APIShift.API.notify("Passwords do not match", "warning");
                            return field_id;
                        }
                    }
                }

                return -1;
            },
            focusOnIncorrectField: function (handler = this) {
                var field_id = this.findIncorrectField(handler.current_step, false);
                if(field_id != -1) $('#' + handler.steps[handler.current_step].fields[field_id].name).focus();
                else $('#' + handler.steps[handler.current_step].fields[0].name).focus();
            },
            /**
             * Validate and move into next step
             */
            nextStep: function() {
                var isFilled = this.findIncorrectField(this.current_step);
                if(this.current_step != this.steps.length - 1 && isFilled == -1)
                {
                    this.current_step++;
                    this.step_action = true;
                }
                else this.focusOnIncorrectField();
            },
            /**
             * Move to previous steps
             */
            prevStep: function() {
                if(this.current_step != 0) {
                    this.current_step--;
                    this.step_action = true;
                }
                else this.focusOnIncorrectField();
            },
            // Jump to a specific stage
            jumpStage: function (stage_id, update = true) {
                // Verify range
                if(stage_id > this.steps.length - 1 || stage_id < 0) return;
                // Next steps need the previous ones filled
                if(stage_id > this.current_step)
                {
                    // Check if previous steps are clear
                    var isFilled = true;
                    for(var i = this.current_step; i <= stage_id; i++)
                    {
                        isFilled = isFilled && this.findIncorrectField(i, update) == -1;
                        // Jump to first uncleared step
                        if(!isFilled)
                        {
                            stage_id = i;
                            break;
                        }
                    }
                }
                // Change stage
                if(stage_id != this.current_step) {
                    this.current_step = stage_id;
                    this.step_action = true;
                }
            },
            /**
             * Check if all data is filled and ready to submit
             */
            isDataFilled: function() {
                for (var step in this.steps) if(this.findIncorrectField(step, false) != -1) return false;
                return true;
            },
            installSystem: function() {
                // Construct data to send
                var to_send = {};

                // Validate data
                if(!this.isDataFilled()) return;

                // Construct message
                for(var step_id in this.steps) {
                    for(var field_id in this.steps[step_id].fields)
                        to_send[this.steps[step_id].fields[field_id].name] = this.steps[step_id].fields[field_id].data;
                    // Add field selector data
                    if(this.steps[step_id].selector !== undefined) to_send[this.steps[step_id].selector.name] = this.steps[step_id].selector.data;
                }

                // Handle installation
                APIShift.Loader.show();
                APIShift.API.request("Main\\Installer", "runInstallation", to_send, function (response) {
                    if(response.status == APIShift.API.status_codes.ERROR) {
                        APIShift.API.notify("Error: " + response.data, "error");
                    }
                    else if (response.status == APIShift.API.status_codes.SUCCESS) {
                        APIShift.API.notify("Success! :) " + response.data, "success");
                        location.reload(); // Reload the page to move to login
                    }
                    else APIShift.API.notify(APIShift.API.getStatusName(response.status) + ": " + response.data, "error");
                    APIShift.Loader.close("installer");
                });
            },
            toggleDarkTheme: function() {
                window.app.$vuetify.theme.dark = !(window.app.$vuetify.theme.dark);
            }
        }
    }
</script>

<template>
    <v-main class="center-screen">
        <v-container fluid fill-height>
            <v-layout align-center justify-center>
                <div>
                    <v-card min-width="400px" min-height="300px" class="elevation-12">
                        <v-toolbar>
                            <v-toolbar-title>Install System > {{ steps[current_step].name }}</v-toolbar-title>
                            <v-spacer></v-spacer>

                            <v-tooltip bottom>
                            <template v-slot:activator="{ on }">
                                <v-btn class="lightbulb" v-on="on" icon large target="_blank" v-on:click="toggleDarkTheme()">
                                    <v-icon v-if="app.$vuetify.theme.dark">mdi-lightbulb</v-icon>
                                    <v-icon v-else>fas fa-moon</v-icon>
                                </v-btn>
                            </template>
                            <span>Toggle Dark Theme</span>
                            </v-tooltip>

                            <v-tooltip v-if="current_step != 0" bottom>
                            <template v-slot:activator="{ on }">
                                <v-btn class="prev_step" v-on="on" icon large target="_blank" v-on:click="prevStep()">
                                <v-icon>mdi-chevron-left</v-icon>
                                </v-btn>
                            </template>
                            <span>Previous Step</span>
                            </v-tooltip>

                            <v-tooltip v-if="current_step != steps.length - 1" bottom>
                            <template v-slot:activator="{ on }">
                                <v-btn class="next_step" v-on="on" icon large target="_blank" v-on:click="nextStep()">
                                <v-icon>mdi-chevron-right</v-icon>
                                </v-btn>
                            </template>
                            <span>Next Step</span>

                            </v-tooltip>

                            <v-progress-linear
                                :active="loader.visible"
                                :indeterminate="loader.visible"
                                :dismissible="loader.dismissible !== undefined && loader.dismissible"
                                absolute
                                bottom
                                color="primary"
                            ></v-progress-linear>
                        </v-toolbar>

                        <v-card-text>
                            <v-form>
                                <v-select
                                    v-if="steps[current_step].selector !== undefined"
                                    :items="steps[current_step].selector.items"
                                    :label="steps[current_step].selector.label"
                                    :prepend-icon="steps[current_step].selector.icon"
                                    v-model="steps[current_step].selector.data"
                                    ></v-select>
                                <v-text-field
                                    v-for="field in steps[current_step].fields"
                                    :disabled="loader.visible || (steps[current_step].selector !== undefined && !field.values.includes(steps[current_step].selector.data))"
                                    :key="field.label /* Need this to satisfy the for loop, shitty error of vue in our situation, but let's keep this clean */"
                                    @keyup.enter="current_step == steps.length - 1 ? installSystem() : nextStep()"
                                    :label="field.label" :id="field.name" :name="field.name" :prepend-icon="field.icon" :type="field.type"
                                    v-model="field.data"></v-text-field>
                            </v-form>
                        </v-card-text>

                        <v-card-actions v-if="isDataFilled()">
                            <v-spacer></v-spacer>
                            <v-btn text color="primary accent-4" v-on:click="installSystem()">Submit</v-btn>
                        </v-card-actions>
                    </v-card>
                </div>
            </v-layout>
        </v-container>
    </v-main>
</template>

<style scoped>
/* Please style this crap, with style */
.next_step {
    margin-right: 0px !important;
}
.prev_step {
    margin-right: 0px !important;
}
.center-screen{
        padding: 0px;
    display: flex;
    height: 100%;
    width: 100%;
    align-self: center;
    justify-content: center;
}
</style>