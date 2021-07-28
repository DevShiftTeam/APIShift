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
        mixins: [APIShift.API.getMixin('graph/graph_element')],
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
            graph_view.elements_loaded++;

            if(graph_view.first_load) {
                this.all_loaded();
                graph_view.bring_to_front(this.$props.index);
            }
        },
        methods: {
            all_loaded: function() { },
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
                    if(window.graph_elements[index] === undefined || graph_view.elements[index].component_id != 3 || graph_view.elements[index].is_deleted)
                        continue;
                    
                    // Check collisions
                    if(graph_view.collision_check(this.get_rect, window.graph_elements[index].get_rect)
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
                let my_id = graph_view.elements[this.$props.index].id;


                // Reset enum sizes
                if (this.attached_enum != -1) {
                    window.graph_elements[this.attached_enum].data.types = window.graph_elements[this.attached_enum].data.types.filter(id => id !== my_id);
                    window.graph_elements[this.attached_enum].reset_enum_sizes();
                    window.graph_elements[this.attached_enum].reset_type_position();
                }

                // Removing element from screen
                graph_view.$set(graph_view.elements[this.$props.index], 'is_deleted', true);
            },
            on_context_addition () {
                graph_view.contextmenu.actions = [
                    {
                        starter: () => {
                            this.is_edit_mode = true;
                            graph_view.contextmenu.is_active = false;
                        },
                        name: 'Edit',
                        icon: 'mdi-pencil',
                    },
                    {
                        starter: () => {
                            graph_view.contextmenu.is_active = false;
                        },
                        name: 'Duplicate',
                        icon: 'mdi-content-duplicate',
                    },
                    {
                        starter: () => {
                            this.on_delete();
                            graph_view.contextmenu.is_active = false;
                        },
                        name: 'Delete',
                        icon: 'mdi-delete-outline',
                    },
                ]
            }
        }
    }
</script>

<template>
    <div class="type" color="#8789ff" :class="{ type_over_enum: enum_hovered != -1 }"
        :style="transformation" 
        @pointerdown.prevent="drag_start"
        @contextmenu.prevent="on_context"
        @dblclick.prevent="is_edit_mode = true"
        @pointerup.prevent="drag_end">
            <v-avatar left class="type_type darken-4 grey">T</v-avatar>
            <div 
                @input="on_input"
                @blur="is_edit_mode = false" 
                :contenteditable="is_edit_mode"
                style="display: inline-block;">
                    {{name}}
            <div>
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