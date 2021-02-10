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
        mixins: [APIShift.API.getMixin('orm/graph_element')],
        props: {
            name: String,
        },
        data () {
            return {
                drawer: null,
            }
        },
        created () {
            const self = this;
            var id = parseInt(this.uid.substring(1));
            this.expanded_functions.drag_start = (event) => {
                let enum_id = graph_view.enum_types.find(e => {return e.id === id});
                const parent_enum = graph_view.$refs['e'+enum_id.enum_id];

                if (parent_enum) {
                    parent_enum.detach_type(id); 
                }
            };
            this.expanded_functions.drag = (event) => {
                const enum_elements = graph_view.$el.querySelectorAll('.enum');
                for (const enum_el of enum_elements) {
                    // TODO: Hightlight enum
                    // if (graph_view.hittest(self.uid, enum_el.ref)) {
                    // }
                }
            };
            this.expanded_functions.drag_end = (event) => {
                const enum_elements = graph_view.$el.querySelectorAll('.enum');

                for (const enum_el of enum_elements) {
                    if (graph_view.hittest(self.uid, enum_el.ref)) {
                        let enum_instance = graph_view.$refs[enum_el.ref];
                        enum_instance.attach_type(id);  
                        break;
                    }
                }
            };
        }, 
        mounted () {

        },
        methods: {
            render_needed () {
            },
            move_to (xpos, ypos) {
                this.$props.position.x = xpos;
                this.$props.position.y = ypos;
            }
        }
    }
</script>

<template>
    <div class="type" color="#8789ff"
        :style="transformation"
        @pointerdown.prevent="drag_start"
        @pointerup.prevent="drag_end">
            <v-avatar left class="type_type darken-4 grey">T</v-avatar>
            <div style="display: inline;">{{ uid }}</div>
    </div>
</template>

<style scoped>
/* Please style this crap, with style */
.type_type {
    text-align: center;
    display: inline;
    padding-left: 7px;
    padding-right: 7px;
}

.type {
    border: solid white 1px;
    border-radius: 10px;
    padding: 5px;
    display: inline-block;
    position: absolute;
    cursor: copy !important;
    background: #8789ff;
}

</style>