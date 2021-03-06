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
    export default {
        // This prop is used to allow updates from the root vue app
        props: {
            alerts: {
                type: Array
            }
        },
        data () {
            return {
                drawer: null
            }
        },
        created () {
            
        }
    }
</script>

<template>
        <v-app class="alert_container">
            <v-alert
                v-for="alert in alerts" :key="alert.id"
                :type="alert.color" :value="alert.show"
                :dismissible="alert.dismissible !== undefined && alert.dismissible" border="left">
                {{ alert.content }}
            </v-alert>
        </v-app>
</template>

<style scoped>
.alert_container {
    position: fixed;
    top: 10px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 100;
    background: none !important;
}

/* Override stupid shit */
.alert_container > * {
    min-height: 0px !important;
    height: auto;
}
</style>