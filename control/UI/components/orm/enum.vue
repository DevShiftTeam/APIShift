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

    // This shit is made for scripting
    module.exports = {
        mixins: [APIShift.API.getMixin('orm/graph_element')],
        data () {
            return {
                drawer: null,
                occupied_width: 0,
                occupied_height: 0,
                type_elements: {}
            }
        },
        created () {
            window.graph_elements[this.$props.index] = this;
        }, 
        mounted () {
            this.occupied_height = 0;
            this.occupied_width = this.$el.offsetWidth;

            // Get all type objects connected to this enum & calculate height & max width
            for(let type in this.$props.data.types) {
                // Store element
                let type_id = this.$props.data.types[type];
                let index = graph_view.elements.findIndex((elem) => elem.id == type_id && elem.component_id == 2);
                this.type_elements[type_id] = window.graph_elements[index];

                // Calculate width & height
                let rect = this.type_elements[type_id].$el.getBoundingClientRect();
                this.occupied_height += rect.height;
                if(this.occupied_width - 14 < rect.width) max_width = rect.width + 14; // 14 for 7 pixel padding at each side
            }
        },
        methods: {
        }
    }
</script>

<template>
        <div class="enum" color="#8789ff"
        :style="transformation"
            @pointerdown.prevent="drag_start"
            @contextmenu.prevent="on_context"
            @pointerup.prevent="drag_end">
                <v-avatar left class="enum_type darken-4 red" >E</v-avatar>
                <div style="display: inline;">{{ name }}</div>
        </div>
</template>

<style scoped>

/* Please style this crap, with style */
.enum_type {
    text-align: center;
    display: inline;
    padding-left: 7px;
    padding-right: 7px;
}

.enum {
    border: solid white 1px;
    border-radius: 10px;
    padding: 5px;
    display: inline-block;
    position: absolute;
    cursor: copy ;
    background: #8789ff;
    box-shadow: 50px 50px 50px rgba(255, 242, 94, 0); /* Removing weird trace on chrome */
}

.enum.ghost_mode {
    opacity: 0.7;
}
</style>