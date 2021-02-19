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
        // This prop is used to allow updates from the root vue app
        props: {
            message: {
                type: String
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
    <div class="loader">
        <v-main>
            <v-container fluid fill-height>
                <v-layout align-center justify-center>
                        <v-progress-circular :size="70" :width="7" color="blue-grey lighten-5" indeterminate></v-progress-circular>
                        <h3>{{ message }}</h3>
                </v-layout>
            </v-container>
        </v-main>
    </div>
</template>

<style scoped>
/* Please style this crap, with style */
.v-progress-circular {
    margin: 1rem;
}

.loader {
    background: #7777777a !important;
    position: fixed;
    z-index: 100;
    width: 100%;
    bottom: 0;
    /* left: 0; */
    top: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>