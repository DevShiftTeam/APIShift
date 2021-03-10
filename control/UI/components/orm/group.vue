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

    //TODO: Design better z-index system.
    module.exports = {
        mixins: [APIShift.API.getMixin('orm/graph_element')],
        data () {
            return {
                init_height: 0,
                init_width: 0,
                occupied_width: 0,
                occupied_height: 0,
                element_indicies: [],
                selected: false
            }
        },
        created () {
            window.graph_elements[this.$props.index] = this;

            if(this.$props.data.position === undefined) {
                this.$set(this.$props.data, 'position', { x: 0, y: 0 });
            }

            this.expanded_functions.drag_start = this.drag_start_addition;
            this.expanded_functions.drag = this.drag_addition;
        },
        mounted () {
            // Determine initial rect pre bounding setup
            let rect = this.$el.getBoundingClientRect();
            this.init_height = rect.height;
            this.init_width = rect.width;


            for(let elem in this.$props.data.elements) {
                let item_id = this.$props.data.elements[elem];
                let index = graph_view.elements.findIndex(el => el.id === item_id && (el.component_id == 1 || el.component_id == 0));
                if(index == -1) continue;
                window.graph_elements[index].group_index = this.$props.index;
                this.element_indicies.push(index);
                graph_view.bring_to_front(index);
            }

            // Determine new calculated rect & bounds
            this.update_group_size();
        },
        methods: {
            update_group_size () {
                // Calculate size
                let rect = {};

                for(let elem in this.element_indicies) {
                    let index = this.element_indicies[elem];

                    // Get first rect
                    let temp_rect = window.graph_elements[index].get_rect();

                    if(rect.x === undefined) {
                        rect.x = temp_rect.x;
                        rect.y = temp_rect.y;
                        rect.x_end = temp_rect.x + temp_rect.width;
                        rect.y_end = temp_rect.y + temp_rect.height;
                    }
                    else {
                        // Get start of group
                        if(rect.x > temp_rect.x) rect.x = temp_rect.x;
                        if(rect.y > temp_rect.y) rect.y = temp_rect.y;

                        // Get end of group
                        if(rect.x_end < temp_rect.x + temp_rect.width)
                            rect.x_end = temp_rect.x + temp_rect.width;
                        if(rect.y_end < temp_rect.y + temp_rect.height)
                            rect.y_end = temp_rect.y + temp_rect.height;
                    }
                }

                // Update rect data 
                this.$props.data.position.x = rect.x;
                this.$props.data.position.y = rect.y;
                this.occupied_width = rect.x_end - rect.x;
                this.occupied_height = rect.y_end - rect.y;
            },
            drag_start_addition: function(event) {
                for(let elem in this.element_indicies) {
                    let index = this.element_indicies[elem];
                    graph_view.bring_to_front(index);
                    window.graph_elements[index].init_position = Object.assign({}, window.graph_elements[index].data.position);
                }
            },
            drag_addition: function(event) {
                for(let elem in this.element_indicies) {
                    let index = this.element_indicies[elem];
                    window.graph_elements[index].drag(event);
                }
            },
            on_context () {
                
            },
            on_delete () {
                let id = this.component_id;


                // Delete lines from the graph & connected relations recursivly
                this.get_lines().forEach(line => {
                    graph_view.delete_line(line.src_info, line.dest_info);
                });
                
                // Remove item connection from enum
                this.get_enums().forEach(e => {    
                        e.data.connected = e.data.connected.filter( connected => connected.type + connected.id !== this.uid);
                    }
                );

                // Delete element from the graph
                graph_view.groups = graph_view.groups.filter((group) => group.id !== id);
                delete graph_view.lookup_table['g'][id];
            },
        },
        computed: {
            sizes () {
                return {
                    width: this.occupied_width + 'px',
                    height: (this.occupied_height + 24) + 'px'
                }
            }
        }
    }
</script>

<template>
    <div class="group" color="#8789ff"
        :style="transformation" :class="{ selected }"
        @pointerdown.prevent="drag_start"
        @contextmenu.prevent="on_context"
        @pointerup.prevent="drag_end"
        >
        <div class="group_container" :style="sizes">

        </div>

        <div class="group_info">
            <v-avatar left class="group_type darken-4 green">G</v-avatar>
            <div style="display: inline;">{{ name }}</div>
        </div>
    </div>
</template>

<style scoped>
/* Please style this crap, with style */

.group_type {
    text-align: center;
    display: inline;
    padding-left: 7px;
    padding-right: 7px;
}

.group {
    border: solid white 1px;
    border-radius: 10px;
    display: flex;
    flex-direction: column-reverse;
    position: absolute;
    cursor: copy;
    box-shadow: 50px 50px 50px rgba(255, 242, 94, 0); /* Removing weird trace on chrome */
}

.group_info {
    background: #8789ff;
    border-bottom-left-radius: 9px;
    border-bottom-right-radius: 9px;
    padding-right: 5px;
    padding-left: 5px;
    width: 100%;
    position: absolute;
}
.group_container {
    pointer-events: none;
    opacity: 0.5;
    background:#aad6ff;
}

.group.selected {
    outline: dashed white 2px;
}

.group.highlight {

}
</style>