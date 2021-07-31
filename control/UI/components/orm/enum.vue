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
        mixins: [APIShift.API.getMixin('graph/line_parent'), APIShift.API.getMixin('graph/container_element')],
        data () {
            return {
                drawer: null,
                occupied_width: -1,
                occupied_height: 0,
                init_height: 0,
                init_width: 0,
                line_indices: []
            }
        },
        created () {
            window.graph_elements[this.$props.index] = this;
            
            this.expanded_functions.drag_start = this.drag_start_addition;
            this.expanded_functions.drag = this.drag_addition;
            this.expanded_functions.drag_end = this.drag_end_addition;
            this.expanded_functions.on_context = this.on_context_addition;
            this.expanded_functions.on_delete = this.on_delete_addition;
        },
        mounted () {
            graph_view.elements_loaded++;

            if(graph_view.first_load) {
                this.all_loaded();
                graph_view.bring_to_front(this.$props.index);
            }
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
            drag_end_addition: function (event) {
                let target_element = -1, z_index = 0;

                for(let index in [...graph_view.elements.keys()]) {
                    let cmp_id = graph_view.elements[index].component_id;
                    // Skip non-item nor relations & self or undefined
                    if(window.graph_elements[index] === undefined || (cmp_id != 0 && cmp_id != 1 && cmp_id != 4) || graph_view.elements[index].is_deleted)
                        continue;
                    
                    // Check collisions
                    if (window.graph_elements[index].data.z_index > z_index && graph_view.collision_check(this.get_rect, window.graph_elements[index].get_rect)) {
                        target_element = parseInt(index);
                        z_index = graph_view.elements[index].data.z_index;
                    }
                }

                // Collision with a collidable element happended
                if (target_element !== -1 && !this.$props.data.connected.find(id => id === graph_view.elements[target_element]?.id)) {
                    let graph_rect = graph_view.$el.querySelector('#graph_center').getBoundingClientRect();


                    // Create line & update data 
                    this.create_line(target_element,
                    {
                        is_stroked: true,
                        dest_point_generator: () => {
                            let target_ref = window.graph_elements[target_element];
                            let src_point = Object.assign({}, this.from_position);
                            let dest_point = { x: target_ref.$props.data.position.x + target_ref.get_rect.width / 2 , y: target_ref.$props.data.position.y + target_ref.get_rect.height / 2 };

                            let segment_segment_intersection = function(src_point, dest_point, p1, p2) {
                                // if the lines intersect, the result contains the x and y of the intersection (treating the lines as infinite) and booleans for whether line segment 1 or line segment 2 contain the point
                                var denominator, a, b, numerator1, numerator2, result = {
                                    x: null,
                                    y: null
                                };

                                let line1dy = dest_point.y - src_point.y, line1dx = dest_point.x - src_point.x;
                                let line2dy = p2.y - p1.y, line2dx = p2.x - p1.x;
                                
                                denominator = ((line2dy) * (line1dx)) - ((line2dx) * (line1dy));
                                
                                // test whether two lines are parallel or coincident
                                if (denominator == 0) {
                                    return null;
                                }
                                
                                a = src_point.y - p1.y;
                                b = src_point.x - p1.x;
                                numerator1 = (line2dx * a) - (line2dy * b);
                                numerator2 = (line1dx * a) - (line1dy * b);

                                a = numerator1 / denominator;
                                b = numerator2 / denominator;

                                // if we cast these lines infinitely in both directions, they intersect here:
                                result.x = src_point.x + (a * (line1dx));
                                result.y = src_point.y + (a * (line1dy));
 
                                // lines intersects at segments
                                return (a > 0 && a < 1) && (b > 0 && b < 1) ? result : null;
                            }

                            let r1 = target_ref.$props.data.position;
                            let r2 = { x: target_ref.$props.data.position.x + target_ref.get_rect.width, y: target_ref.$props.data.position.y };
                            let r3 = { x: target_ref.$props.data.position.x + target_ref.get_rect.width, y: target_ref.$props.data.position.y + target_ref.get_rect.height };
                            let r4 = { x: target_ref.$props.data.position.x, y: target_ref.$props.data.position.y + target_ref.get_rect.height };

                            // Determine interection point
                            let intersection = segment_segment_intersection(src_point,dest_point,r1,r2);
                            if(intersection == null) intersection = segment_segment_intersection(src_point,dest_point,r2,r3);
                            if(intersection == null) intersection = segment_segment_intersection(src_point,dest_point,r3,r4);
                            if(intersection == null) intersection = segment_segment_intersection(src_point,dest_point,r4,r1);
                            if(intersection == null) intersection = { x: target_ref.get_rect.x + target_ref.get_rect.width / 2 , y: target_ref.get_rect.y + target_ref.get_rect.height - target_ref.init_height };
                            
                            return intersection;
                        }
                    }, true);
                    
                    this.$props.data.connected.push(graph_view.elements[target_element].id);

                    // Move enum back to origin position
                    this.$props.data.position.x += (window.init_pointer.x - event.clientX) / graph_view.scale;
                    this.$props.data.position.y += (window.init_pointer.y - event.clientY) / graph_view.scale;
                    this.reset_type_position();
                }
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
                    let rect = window.graph_elements[index].get_rect;
                    window.graph_elements[index].attached_enum = this.$props.index;
                    this.occupied_height += rect.height + 7;
                    if(this.occupied_width - 14 < rect.width) this.occupied_width = rect.width + 14; // 14 for 7 pixel padding at each side
                }

                // Refresh cached rect computed property
                this.ui_refresher++;
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
                        = this.$props.data.position.x + (this.occupied_width / 2) - (window.graph_elements[index].get_rect.width / 2);

                    graph_view.bring_to_front(index);
                    current_position_height += window.graph_elements[index].get_rect.height + 7;
                }
            },
            on_delete_addition () {
                // Detach attached types
                for(let type in this.$props.data.types) {
                    let type_id = this.$props.data.types[type];
                    let index = graph_view.elements.findIndex((elem) => elem.id == type_id && elem.component_id == 2);
                    window.graph_elements[index].attached_enum = -1;
                }

                // Delete lines from graph
                this.line_indices = this.line_indices.filter(line_index => {
                    graph_view.$set(graph_view.lines[line_index], 'is_deleted', true);
                });

                // Delete element from graph
                graph_view.$set(graph_view.elements[this.$props.index], 'is_deleted', true);
            },
            remove_connection_addition (index) {
                let id = graph_view.elements.findIndex(element => element.id == id && (element.component_id == 0 || element.component_id == 1 || element.component_id == 4));

                // Delete line to connected element
                let line_indices = [];
                this.line_indices.forEach(line_index => {
                    if (graph_view.lines[line_index].to_index == element_index)
                        graph_view.$set(graph_view.lines[line_index], 'is_deleted', true);
                    else
                        line_indices.push(line_index);
                });
                this.line_indices = line_indices;

                // Remove conection from internal data
                this.$props.data.connected = this.$props.data.connected.filter(connected_id => connected_id != id);
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
        },
        computed: {
            from_position: function() {
                return {
                    x: this.$props.data.position.x + Math.max(this.occupied_width, this.$el.offsetWidth) / 2,
                    y: this.$props.data.position.y + (this.occupied_height) / 2
                };
            },
            sizes: function() {
                return {
                    'min-width': this.occupied_width == -1 ? 'auto' : this.occupied_width + 'px',
                    'height': this.occupied_height + 'px'
                };
            },
        }
    }
</script>

<template>
        <div class="enum" color="#8789ff"
            :style="[transformation, sizes]"
            @pointerdown.prevent="drag_start"
            @contextmenu.prevent="on_context"
            @dblclick.prevent="is_edit_mode = true"
            @pointerup.prevent="drag_end">
                <v-avatar left class="enum_type darken-4 red" >E</v-avatar>
                <div 
                    @input="on_input"
                    @blur="on_blur" 
                    :contenteditable="is_edit_mode"
                    style="display: inline-block;">
                        {{name}}
                <div>
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