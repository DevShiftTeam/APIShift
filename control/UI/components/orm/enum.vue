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
                init_height: 0,
                init_width: 0
            }
        },
        created () {
            window.graph_elements[this.$props.index] = this;
            this.expanded_functions.drag_start = this.drag_start_addition;
            this.expanded_functions.drag = this.drag_addition;
        }, 
        mounted () {
            graph_view.elements_loaded++;
        },
        methods: {
            all_loaded: function() {
                this.init_height = this.$el.offsetHeight * 3;
                this.init_width = -1;
                // Set enum size and type positions
                this.reset_enum_sizes();
                this.reset_type_position();
            },
            drag_start_addition: function(event) {
                // Bring types to front of enum
                for(let type in this.$props.data.types) {
                    let type_id = this.$props.data.types[type];
                    let index = graph_view.elements.findIndex((elem) => elem.id == type_id && elem.component_id == 2);
                    graph_view.bring_to_front(index);
                }
            },
            drag_addition: function(event) {
                this.reset_type_position();
            },
            reset_enum_sizes: function() {
                this.occupied_height = this.init_height;
                this.occupied_width = this.init_width;

                // Get all type objects connected to this enum & calculate height & max width
                for(let type in this.$props.data.types) {
                    // Find type index
                    let type_id = this.$props.data.types[type];
                    let index = graph_view.elements.findIndex((elem) => elem.id == type_id && elem.component_id == 2);

                    // Calculate width & height
                    let rect = window.graph_elements[index].get_rect();
                    window.graph_elements[index].attached_enum = this.$props.index;
                    this.occupied_height += rect.height + 7;
                    if(this.occupied_width - 14 < rect.width) this.occupied_width = rect.width + 14; // 14 for 7 pixel padding at each side
                }
            },
            reset_type_position: function() {
                // Move types with enum & update their z-index to heigher than enum
                let current_position_height = this.$props.data.position.y + this.init_height;

                graph_view.bring_to_front(this.$props.index);
                
                for(let type in this.$props.data.types) {
                    let type_id = this.$props.data.types[type];
                    let index = graph_view.elements.findIndex((elem) => elem.id == type_id && elem.component_id == 2);

                    graph_view.elements[index].data.position.y = current_position_height;
                    graph_view.elements[index].data.position.x
                        = this.$props.data.position.x + (this.occupied_width / 2) - (window.graph_elements[index].get_rect().width / 2);

                    graph_view.bring_to_front(index);
                    current_position_height += window.graph_elements[index].get_rect().height + 7;
                }
            }
        },
        computed: {
            sizes: function() {
                return {
                    'width': this.occupied_width == -1 ? 'auto' : this.occupied_width + 'px',
                    'height': this.occupied_height + 'px'
                };
            }
        }
    }
</script>

<template>
        <div class="enum" color="#8789ff"
            :style="[transformation, sizes]"
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