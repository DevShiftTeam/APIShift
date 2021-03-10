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
        data () {
            return {
                drawer: null,
                enum_hovered: -1,
                attached_enum: -1
            }
        },
        created () {
            window.graph_elements[this.$props.index] = this;
        }, 
        mounted () {
            this.expanded_functions.drag_start = this.drag_start_addition;
            this.expanded_functions.drag = this.drag_addition;
            this.expanded_functions.drag_end = this.drag_end_addition;
        },
        methods: {
            drag_start_addition: function(event) {
                // Remove from attached enum
                if(this.attached_enum != -1) {
                    // Remove from enum
                    let index = window.graph_elements[this.attached_enum].data.types.indexOf(this.$props.id);
                    window.graph_elements[this.attached_enum].data.types.splice(index, 1);

                    // Reset enum sizes
                    window.graph_elements[this.attached_enum].reset_enum_sizes();
                    window.graph_elements[this.attached_enum].reset_type_position();

                    this.attached_enum = -1;
                    graph_view.bring_to_front(this.$props.index);
                }
            },
            drag_addition(event) {
                let enum_found = -1, z_index = 0;
                
                for(let index in [...graph_view.elements.keys()]) {
                    // Skip non-enums
                    if(window.graph_elements[index] === undefined || graph_view.elements[index].component_id != 3)
                        continue;
                    
                    // Check collisions
                    if(graph_view.collision_check(this.get_rect(), window.graph_elements[index].get_rect())
                    && window.graph_elements[index].data.z_index > z_index) {
                        z_index = window.graph_elements[index].data.z_index;
                        enum_found = index;
                    }
                }

                this.enum_hovered = enum_found;
            },
            drag_end_addition(event) {
                if(this.enum_hovered == -1) return;
                
                // Add type to enum
                this.attached_enum = this.enum_hovered;
                window.graph_elements[this.attached_enum].data.types.push(this.$props.id);

                // Reset enum sizes
                window.graph_elements[this.attached_enum].reset_enum_sizes();
                window.graph_elements[this.attached_enum].reset_type_position();

                // Reset hovered enum
                this.enum_hovered = -1;
            },
            on_delete() {
                let id = this.component_id;

                // Detach type from enum 
                if(this.$props.data.enum_id) this.remove_from_enum();
                
                // Finally remove element from screen
                graph_view.enum_types = graph_view.enum_types.filter((enum_type) => enum_type.id !== id);
                delete graph_view.lookup_table['t'][id];
            }
        },
        computed: {
            enum_parent () {
                var enum_id = this.$props.data.enum_id;
                return graph_view.enums.find((enum_) => enum_.id === enum_id);
            }
        }
    }
</script>

<template>
    <div class="type" color="#8789ff" :class="{ type_over_enum: enum_hovered != -1 }"
        :style="transformation" 
        @pointerdown.prevent="drag_start"
        @contextmenu.prevent="on_context"
        @pointerup.prevent="drag_end">
            <v-avatar left class="type_type darken-4 grey">T</v-avatar>
            <div style="display: inline;">{{ name }}</div>
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
    cursor: copy ;
    background: #8789ff;
    box-shadow: 50px 50px 50px rgba(255, 242, 94, 0); /* Removing weird trace on chrome */
}
.type_over_enum {
    opacity: 0.7;
}

</style>