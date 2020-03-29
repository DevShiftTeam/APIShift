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
                controller_access_list: [],
                search: '',
                loader: {
                    visible: false,
                    message: "",
                    processes: 0
                },
                headers: [
                    {
                        text: 'Controller',
                        align: 'start',
                        sortable: false,
                        value: 'controller',
                    },
                    {
                        text: 'Method',
                        align: 'start',
                        sortable: false,
                        value: 'method',
                    },
                    { text: 'Task name', value: 'name' }
                ],

            }
        },
        created() {
            APIShift.Loader.changeLoader("access_controllers", this.loader);
            window.cahandler = this;

            APIShift.API.request("Control", "getControllersTasks", {}, function (response) {
                if(response.status == APIShift.API.status_codes.SUCCESS) {
                    cahandler.controller_access_list = Object.assign([], response.data);
                } else {
                    APIShift.API.notify(response.data, "error");
                }
            }, true);
        }
    };
</script>

<template>
    <div>
        <v-card>
            <v-card-title>
                Controllers
                <v-spacer></v-spacer>
                <v-text-field
                    v-model="search"
                    append-icon="mdi-magnify"
                    label="Search"
                    single-line
                    :loading="loader.visible"
                    :loading-text="loader.message"
                    hide-details
                ></v-text-field>
            </v-card-title>

            <v-data-table
                :headers="headers"
                :items="controller_access_list"
                :search="search"
            ></v-data-table>
        </v-card>
    </div>
</template>
<style scoped>

</style>