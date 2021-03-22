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
     * @contributor Ilan Dazanashvili
     */

    window.empty_function = (event) => {};

    // This shit is made for scripting
    module.exports = {
        data () {
            return {
                drawer: null,
                // Components
                components: [
                    APIShift.API.getComponent('orm/item', true),
                    APIShift.API.getComponent('orm/relation', true),
                    APIShift.API.getComponent('orm/enum_type', true),
                    APIShift.API.getComponent('orm/enum', true),
                    APIShift.API.getComponent('orm/group', true),
                    APIShift.API.getComponent('orm/point', true)

                ],
                line_comp: APIShift.API.getComponent('orm/line', true),
                selection_comp: APIShift.API.getComponent('orm/selection_box', true),
                side_menu_comp: APIShift.API.getComponent('orm/side_menu', true),
                elements: [
                    // Items
                    { id: 1, component_id: 0, name: "Users", data: {
                            position: { x: 20, y: 0 }
                        }
                    },
                    { id: 2, component_id: 0, name: "Posts", data: {
                            position: { x: 120, y: 50 }
                        }
                    },
                    
                    // Relations
                    { id: 3, component_id: 1, name: "Testers", data: {
                        position: { x: 20, y: 0 },
                        to: 5,
                        type: 0
                    }},
                    { id: 4, component_id: 1, name: "UserPosts", data: {
                            position: { x: 220, y: 200 },
                            from: 1,
                            to: 2,
                            type: 0
                        }
                    },
                    
                    // Enum Types
                    { id: 1, component_id: 2, name: 'test1', data: {
                        position: {x: 100, y: 100}
                    }},
                    { id: 2, component_id: 2, name: 'testsdfs2', data: {
                        position: {x: 100, y: 100}
                    }},
                    { id: 3, component_id: 2, name: '3testing', data: {
                        position: {x: 100, y: 100}
                    }},

                    // Enums
                    { id: 1, component_id: 3, name: 'enum1', data: {
                        position: {x: 200, y: 200},
                        types: [ 1, 2 ],
                        connected: []
                    }},

                    // Groups
                    { id: 5, component_id: 4, name: 'group', data: {
                        elements: [ 1, 2, 4, 6 ]
                    }},
                    { id: 6, component_id: 4, name: 'group', data: {
                        elements: [ 4 ]
                    }}
                ],
                lines: [

                ],
                relative: {
                    x: 0,
                    y: 0
                },
                // Index of the most frontal element
                front_z_index: 0,
                scale: 1,
                /* Drag & Drop functional data */
                tap_counter: 0,
                // Holds the function which renders the dragging event
                drag_handler: window.empty_function,
                // Defines the camera origin relative to the initial 0,0 position
                camera: {
                    x: 0,
                    y: 0
                },
                init_camera: {
                    x: 0,
                    y: 0
                },
                drag_end_lock: false,
                selection_active: false,
                side_menu_actions: [],
                scroll_manager: {
                    id: null,
                    interval: 20,
                    params: [],
                    cb: window.empty_function,
                    start: function(cb, interval) {
                        if (this.is_running) this.stop();
                        if (cb) this.cb = cb;
                        if (interval) this.iv = interval;
                        this.id = window.setInterval(this.cb, this.interval);
                    },
                    stop: function() {
                        clearInterval(this.id);
                        this.id = null;
                    },
                    is_running: function () {
                        return this.id; 
                    }
                },
                cursor_state: "auto",
                elements_loaded: 0,
                first_load: false,
                action_selected: false,
                current_action: window.empty_function
            }
        },
        created () {
            // Store this object with a global reference
            window.graph_elements = {};
            window.graph_view = this;
            this.current_action = window.empty_function;

            this.side_menu_actions = [
                    {
                        starter: () => {
                            graph_view.cursor_state = "create";
                            graph_view.current_action = () => {
                                let last_id = 0;

                                for(let el_index in graph_view.elements) {
                                    let el = graph_view.elements[el_index];
                                    if((el.component_id == 1 || el.component_id != 0 || el.component_id == 4) && last_id < el.id)
                                        last_id = el.id;
                                };

                                graph_view.elements.push({
                                    id: last_id + 1, component_id: 0, name: "Item", data: {
                                        position: window.mouse_on_graph,
                                        z_index: graph_view.elements.length
                                    }
                                });
                            }
                        },
                        icon: 'mdi-plus',
                        text: "Add Item" 
                    },
                    {
                        starter: () => {
                            graph_view.cursor_state = "create";
                            graph_view.current_action = () => {
                                let last_id = 1;

                                for(let el_index in graph_view.elements) {
                                    let el = graph_view.elements[el_index];
                                    if((el.component_id == 1 || el.component_id != 0 || el.component_id == 4) && last_id < el.id)
                                        last_id = el.id;
                                };

                                graph_view.elements.push({
                                    id: last_id + 1, component_id: 1, name: "Relation", data: {
                                        position: window.mouse_on_graph,
                                        type: 0,
                                        z_index: graph_view.elements.length
                                    }
                                });
                            };
                        },
                        icon: 'mdi-arrow-right',
                        text: "Add Relation" 
                    },
                    {
                        starter: () => {
                            graph_view.cursor_state = "delete";
                            graph_view.current_action = (event) => {
                                let target_element = -1, z_index = 0;

                                for(let index in [...graph_view.elements.keys()]) {
                                    // Skip undefined or deleted
                                    if(window.graph_elements[index] === undefined || window.graph_elements[index].is_deleted)
                                        continue;
                                    
                                    // Check collisions
                                    let obj_rect = window.graph_elements[index].get_rect();
                                    if (window.graph_elements[index].data.z_index > z_index && (
                                        obj_rect.x < window.mouse_on_graph.x && obj_rect.x + obj_rect.width > window.mouse_on_graph.x
                                        && obj_rect.y < window.mouse_on_graph.y && obj_rect.y + obj_rect.height > window.mouse_on_graph.y
                                    )) {
                                        target_element = index;
                                        z_index = graph_view.elements[index].data.z_index;
                                    }
                                }
                                
                                // Delete targeted element
                                if (target_element !== -1) window.graph_elements[target_element].on_delete();
                            };
                        },
                        icon: 'mdi-delete-outline',
                        text: "Delete Tool" 
                    },
                    {
                        starter: () => {
                            graph_view.cursor_state = "create";
                            graph_view.current_action = () => {
                                let last_id = 1;

                                for(let el_index in graph_view.elements) {
                                    let el = graph_view.elements[el_index];
                                    if((el.component_id == 3) && last_id < el.id)
                                        last_id = el.id;
                                };

                                graph_view.elements.push({
                                    id: last_id + 1, component_id: 3, name: "Enum", data: {
                                        position: window.mouse_on_graph,
                                        types: [],
                                        connected: [],
                                        z_index: graph_view.elements.length
                                    }
                                });
                            };
                        },
                        icon: 'fas fa-cubes',
                        text: "Add Enum" 
                    }, 
                    {
                        starter: () => {
                            graph_view.cursor_state = "create";
                            graph_view.current_action = () => {
                                let last_id = 1;

                                for(let el_index in graph_view.elements) {
                                    let el = graph_view.elements[el_index];
                                    if((el.component_id == 2) && last_id < el.id)
                                        last_id = el.id;
                                };

                                graph_view.elements.push({
                                    id: last_id + 1, component_id: 2, name: "Type", data: {
                                        position: window.mouse_on_graph,
                                        z_index: graph_view.elements.length
                                    }
                                });
                            }
                        },
                        icon: 'fas fa-cube',
                        text: "Add Enum Type"
                    },
                    {
                        starter: () => {
                            graph_view.cursor_state = "crosshair";
                            graph_view.current_action = window.selection_box.start_select;
                        },
                        icon: 'fa-object-group', 
                        text: "Add Group" 
                    }
                ];
            // Set initial z-index
            for(var index in this.elements) this.$set(this.elements[index].data, 'z_index', parseInt(index) + 1);
        },
        mounted () {
            this.update_graph_position();
        },
        methods: {
            bring_to_front: function(element_index) {
                // Ignore if in front
                if(this.elements[element_index].data.z_index !== undefined && this.elements[element_index].data.z_index == this.elements.length) return;

                let current_z_index = this.elements[element_index].data.z_index;

                // Bring other elements to back
                for(let index in this.elements)
                    if(this.elements[index].data.z_index > current_z_index)
                        this.elements[index].data.z_index--;
                

                // Bring to front
                this.elements[element_index].data.z_index = this.elements.length;
                app.$refs.navigator.updateIndex(this.elements.length + 1);
                app.$refs.footer.updateIndex(this.elements.length + 1);
                window.holder.updateIndex(this.elements.length + 1);
            },
            /**
             * User interactions
             */
            pointer_down(event) {
                document.addEventListener('pointerup', this.pointer_up);

                // Add event to event cache, determine interactive target
                this.tap_counter++;
                
                // Update graph position
                this.update_graph_position();

                // Handle mobile zooming
                if(this.tap_counter === 2) {
                    window.scale_factor = 1;
                    window.scale_init = this.scale;
                    window.init_pointer_first = Object.assign({}, window.init_pointer);
                    window.init_pointer_second = {
                        x: event.clientX,
                        y: event.clientY
                    };
                    window.temp_pointer = {
                        x: (window.init_pointer_first.x + window.init_pointer_second.x) / 2 - window.graph_position.x,
                        y: (window.init_pointer_first.y + window.init_pointer_second.y) / 2 - window.graph_position.y
                    };

                    this.drag_handler = this.pointer_scale;
                    return;
                }

                // Get initiale pointer coordinates
                window.init_pointer = {
                    x: event.clientX,
                    y: event.clientY
                };
                this.init_camera = Object.assign({}, this.camera);

                if(this.current_action != window.empty_function) {
                    // Determine pointer position in respect to graph transformation
                    let camera_rect = document.querySelector('#graph_center').getBoundingClientRect();
                    window.mouse_on_graph = { x: (window.init_pointer.x - camera_rect.x) / (this.scale), y: (window.init_pointer.y - camera_rect.y) / (this.scale)};
                    this.current_action(event);
                    return;
                }

                // Proceeds only if not dragging any other object
                if(this.drag_handler != window.empty_function) return;

                this.drag_handler = this.pointer_move;
            },
            pointer_move(event) {
                this.camera.x = this.init_camera.x + event.clientX - window.init_pointer.x;
                this.camera.y = this.init_camera.y + event.clientY - window.init_pointer.y;
            },
            pointer_scale (event) {
                this.update_graph_position();
                let rect = document.getElementById('graph_center').getBoundingClientRect();
                // Calculate center
                window.init_pointer.x = (window.init_pointer_first.x + window.init_pointer_second.x) / 2 - window.graph_position.x;
                window.init_pointer.y = (window.init_pointer_first.y + window.init_pointer_second.y) / 2 - window.graph_position.y;
                // Middle of graph camera
                let mid = {
                    x: rect.x + rect.width / 2 - window.graph_position.x,
                    y: rect.y + rect.height / 2 - window.graph_position.y
                };
                // Calculate previous distance vector
                let prev_diff = {
                    x: window.init_pointer_first.x - window.init_pointer_second.x,
                    y: window.init_pointer_first.y - window.init_pointer_second.y
                };

                // Calculate new point that moved
                if(Math.abs(window.init_pointer_first.y - event.clientY) + Math.abs(window.init_pointer_first.x - event.clientX)
                    < Math.abs(window.init_pointer_second.y - event.clientY) + Math.abs(window.init_pointer_second.x - event.clientX)) {
                    window.init_pointer_first.x = event.clientX;
                    window.init_pointer_first.y = event.clientY;
                }
                else {
                    window.init_pointer_second.x = event.clientX;
                    window.init_pointer_second.y = event.clientY;
                }

                // Calculate new distance vector using new pointer
                let new_diff = {
                    x: window.init_pointer_first.x - window.init_pointer_second.x,
                    y: window.init_pointer_first.y - window.init_pointer_second.y
                };

                // Update scale
                let change = Math.sqrt(
                        (new_diff.x * new_diff.x + new_diff.y * new_diff.y) /
                        (prev_diff.x * prev_diff.x + prev_diff.y * prev_diff.y)
                    );
                window.scale_factor *= change;
                let new_scale = window.scale_init * window.scale_factor;
        
                // Keep the scale on bound
                if (new_scale < 0.2 || new_scale > 4 ) {
                    return;
                }
                this.scale = new_scale;

                // Move Camera
                this.camera.x += (window.init_pointer.x - mid.x) * (1 - change) + window.init_pointer.x - window.temp_pointer.x;
                this.camera.y += (window.init_pointer.y - mid.y) * (1 - change) + window.init_pointer.y - window.temp_pointer.y;
                window.temp_pointer = Object.assign({}, window.init_pointer);
            },
            pointer_up(event) {
                this.tap_counter = 0;
                
                // Release drag end lock
                this.drag_end_lock = false;

                // Empty current action
                this.current_action = window.empty_function;

                // Reset drag event to none
                this.drag_handler = window.empty_function;

                this.scroll_manager.stop();
                graph_view.cursor_state = "auto";
            },
            wheel (event) {
                // Update graph position
                this.update_graph_position();
                let rect = document.getElementById('graph_center').getBoundingClientRect();
                window.init_pointer = {
                    x: event.clientX - window.graph_position.x,
                    y: event.clientY - window.graph_position.y
                }

                // Middle of graph camera
                let mid = {
                    x: rect.x + rect.width / 2 - window.graph_position.x,
                    y: rect.y + rect.height / 2 - window.graph_position.y
                };

                // Calculate change in scale
                var delta = event.deltaMode > 0 ? event.deltaY * 100 : event.deltaY;
                var sign = Math.sign(delta), speed = 1;
                var deltaAdjustedSpeed = Math.min(0.25, Math.abs(speed * delta / 128));
                let change = (1 - sign * deltaAdjustedSpeed);
                let new_scale = this.scale * change;
                
                // Keep the scale on bound
                if (new_scale < 0.2 || new_scale > 4 ) {
                    return;
                }
                this.scale = new_scale;
                
                // Move camera to fit mouse as scaling center
                this.camera.x += (window.init_pointer.x - mid.x) * (1 - change);
                this.camera.y += (window.init_pointer.y - mid.y) * (1 - change);
            },
            move_camera_by (dx, dy) {
                this.camera.x += dx;
                this.camera.y += dy;
            },
            // Update graph position
            update_graph_position() {
                let rect = document.getElementById('graph_view').getBoundingClientRect();
                // Stores the current position of the graph on the screen
                window.graph_position = {
                    x: rect.x,
                    y: rect.y,
                    width: rect.width,
                    height: rect.height
                };
            },
            /**
             * Test whether 2 graph elements hit each other on the graph.
             * @param {Object} size_object_1
             * @param {Object} size_object_2
             * @returns {Boolean} 
             */
            collision_check: function(size_object_1, size_object_2){
                return (
                    size_object_1.x < size_object_2.x + size_object_2.width &&
                    size_object_1.x > size_object_2.x - size_object_1.width &&
                    size_object_1.y < size_object_2.y + size_object_2.height &&
                    size_object_1.y > size_object_2.y - size_object_1.height
                );
            }
        },
        watch: {
            elements_loaded: function(val) {
                if(val == this.elements.length && !this.first_load)
                {
                    for(let index in window.graph_elements)
                        if(window.graph_elements[index].all_loaded !== undefined) window.graph_elements[index].all_loaded();
                    this.first_load = true;
                }
            }
        }
    }
</script>

<template>
        <v-container fluid fill-height>
            <v-card class="mx-auto" width="100%" min-height="75%" elevation-2>
                <!-- Header -->
                <v-app-bar>
                    <v-menu offset-y style="z-index: 100">
                        <template v-slot:activator="{ on }">
                            <v-toolbar-title v-on="on">
                                <v-btn>
                                    {{ holder.getPageTitle() }}
                                    
                                    <v-icon right>mdi-menu-down</v-icon>
                                </v-btn>
                            </v-toolbar-title>
                        </template>
                        <v-list>
                            <v-list-item to="/database">Main Page</v-list-item>
                            <v-list-item v-for="(item, key) in holder.sub_pages" :key="key" :to="item.url">{{ item.title }}</v-list-item>
                        </v-list>
                    </v-menu>
                    <v-divider class="mx-4" inset vertical></v-divider>
                    <v-spacer></v-spacer>
                </v-app-bar>

                <!-- Body -->
                <div class="overflow-box">
                    <div id="graph_view"
                        @wheel.prevent="wheel"
                        @pointermove.prevent="drag_handler"
                        @touchmove.prevent="() => {}"
                        @pointerdown="pointer_down"
                        @pointerup="pointer_up"
                        @pointercancel="pointer_up"
                        :style="{ 'cursor' : cursor_state }">
                        <component ref="side_menu"
                            :is="side_menu_comp"
                            :actions="side_menu_actions"
                        >
                        </component>
                        
                        <!-- The center element allow us to create a smart camera that positions the elements without needed to re-render for each element -->
                        <div ref="gv_center" id="graph_center" :style="{ 'transform': 'translate(' + camera.x + 'px, ' + camera.y + 'px) scale(' + scale + ')'}">
                            <!-- Elements -->
                            <component
                                v-for="(element, index) in elements"
                                v-show="!element.is_deleted"
                                :is="components[element.component_id]"
                                :key="index"
                                :index="index"
                                :id="element.id"
                                :name="element.name"
                                :data="element.data">
                            </component>

                            <component
                                v-for="(line, index) in lines"
                                :key="index"
                                v-show="!line.is_deleted"
                                :index="index"
                                :is="line_comp"
                                :src_ref="window.graph_elements[line.from_index]"
                                :dest_ref="window.graph_elements[line.to_index]"
                                :data="line.data">
                            </component>
                        </div>
                        <component ref="s_box" 
                            v-show="selection_active"
                            :is="selection_comp">
                        </component>
                    </div>
                </div>
            </v-card>
        </v-container>
</template>

<style scoped>
/* Please style this crap, with style */

/* Disables all cursor overrides when body has this class. */
#graph_view {
    overflow: hidden;
}

#graph_view, #graph_center {
    position: relative;
    width: 100%;
    height: 100%;
    user-select: none;
}
#svg_viewport {
    position: relative; 
    height: 100%; 
    width: 100%;
    overflow: visible;
}

.shadow {
    border: solid white 1px;
    display: inline-block;
    position: absolute;
    cursor: copy ;
    height: 50px;
    width: 50px;
    background: #8789ff;
    box-shadow: 50px 50px 50px rgba(255, 242, 94, 0); /* Removing weird trace on chrome */
}
#graph_view.cursor_delete {
    cursor: not-allowed !important;
}
#graph_view.cursor_select {
    cursor: se-resize !important;
}
#graph_view.cursor_create {
    cursor: copy !important;
}
#graph_view.cursor_default {
    cursor: inherit !important;
}

</style>