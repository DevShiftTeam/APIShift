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
                index: 10
            }
        },
        created () {
            
        },
        methods: {
            updateIndex: function(new_index) {
                this.index = new_index;
            }
        }
    }
</script>

<template>
<div class="footer"
    :style="{'z-index': index}">
    <v-fab-transition>
      <v-btn
        key="mdi-chevron-up"
        color="purple"
        fab
        dark
        bottom
        right
      >
        <v-icon>mdi-chevron-up</v-icon>
      </v-btn>
    </v-fab-transition>
</div>
</template>

<style scoped>
/* Please style this crap, with style */
.footer {
    position: fixed;
    bottom: 0;
    right: 0;
    padding: 5px;
}
</style>