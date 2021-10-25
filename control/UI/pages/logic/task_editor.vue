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

    window.empty_function = (event) => {};

    // This shit is made for scripting
    module.exports = {
        mixins: [APIShift.API.getMixin("graph/graph_view", true)],
        data () {
            return {
                drawer: null,
                // Components
                components: [
                    APIShift.API.getComponent('logic/DS/table', true),
                    APIShift.API.getComponent('logic/DS/array', true),
                    APIShift.API.getComponent('logic/C/function', true),
                    APIShift.API.getComponent('logic/result', true),
                    APIShift.API.getComponent('logic/flow', true),
                    APIShift.API.getComponent('graph/point', true)
                    // APIShift.API.getComponent('logic/C/rule', true),
                ],
                line_comp: APIShift.API.getComponent('graph/line', true),
                selection_comp: APIShift.API.getComponent('graph/selection_box', true),
                side_menu_comp: APIShift.API.getComponent('graph/side_menu', true),
                context_menu_comp: APIShift.API.getComponent('graph/context_menu',true),
                elements: [
                    // Data Sources - Tables
                    { id: 1, component_id: 0, name: "admin_users", data: {
                            entries: [
                                {
                                    val: 'PASSWORD',
                                    to: { id: 1, component_id: 2, con: 0 }
                                }
                            ],
                            position: { x: 450, y: -50 },
                        }
                    },
                    // Data Sources - Arrays
                    { id: 1, component_id: 1, name: "$REQUEST", data: {
                            entry: 'pass' ,
                            position: { x: -50, y: 200 },
                            to: { id: 1, component_id: 2 },

                            entries: [
                                {
                                    val: 'hash',
                                    to: { id: 1, component_id: 2, con: 1 }
                                },
                                {
                                    val: 'pass',
                                    to: { id: 1, component_id: 2, con: 1 }
                                }
                            ]
                        }
                    },
                    { id: 2, component_id: 1, name: "$REQUEST", data: {
                            entry: 'user',
                            position: { x: -50, y: 0 },
                        }
                    },
                    // Functions
                    { id: 1, component_id: 2, name: "password_verify", data: {
                            params: ["hash", "password"],
                            position: { x: 400, y: 180 },
                            to: {id: 0, component_id: 3}
                        }
                    },
                    // Flows
                    { id: 1, component_id: 4, name: "username", data: {
                            position: { x: 180, y: 180 },
                            from: { id: 2, component_id: 1, con: 0 },
                            to: { id: 1, component_id: 0, con: 0 },
                        }
                    },
                    { id: 2, component_id: 4, name: "hash", data: {
                            position: { x: 300, y: 300 },
                            from: { id: 1, component_id: 0, con: 0 },
                            to: { id: 1, component_id: 2, con: 0 },
                        }
                    },
                    { id: 2, component_id: 4, name: "password", data: {
                            position: { x: 350, y: 350 },
                            from: { id: 1, component_id: 1, con: 0 },
                            to: { id: 1, component_id: 2, con: 0 },
                        }
                    },
                    // Result
                    { id: 0, component_id: 3, data: {
                            position: { x: 700, y: 130 },
                        }
                    },
                    // Points - generated on runtime
                    // { id: -1, component_id: 5, data: {
                    //         position: { x: 700, y: 130 },
                    //     }
                    // },
                ],
                lines: [

                ],
                relative: {
                    x: 0,
                    y: 0
                },
                // Index of the most frontal element
                front_z_index: 0,
                scale: 0.85,
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
                context_menu: {
                    actions: [],
                    position: { x: 0, y: 0 },
                    is_active: false
                },
                scroll_manager: {
                    id: null,
                    interval: 20,
                    params: [],
                    cb: window.empty_function,
                    position_function(time) {
                        return 
                    },
                    start: function(cb, interval) {
                        if (cb) this.cb = cb;
                        if (interval) this.iv = interval;
                        this.id = window.setInterval(this.cb, this.interval);
                    },
                    stop: function() {
                        clearInterval(this.id);
                        this.id = null;
                    },
                    is_running: function () {
                        return !!this.id; 
                    }
                },
                cursor_state: "auto",
                elements_loaded: 0,
                first_load: false,
                action_selected: false,
                current_action: window.empty_function,
                edit_dialog: true,
                is_creating: false,
                in_edit: false
            }
        },
        created () {
            // Store this object with a global reference
            window.graph_elements = {};
            window.graph_lines = {};
            window.graph_view = this;
            this.current_action = window.empty_function;
            
            this.context_menu.actions = [
    
            ];
            this.side_menu_actions = [
                    {
                        starter: () => {
                            graph_view.cursor_state = "create";
                            graph_view.current_action = () => {
                                let highest_id = 0;
                                for (const element of graph_view.elements) {
                                    if (element.component_id != 0 || element.component_id != 1 || element.component_id != 4) continue;
                                    if (element.id > highest_id) highest_id = element.id;
                                }

                                graph_view.elements.push({
                                    id: highest_id + 1, component_id: 0, name: "Item", data: {
                                        position: window.mouse_on_graph,
                                        z_index: graph_view.elements.length
                                    }
                                });
                            }
                        },
                        icon: 'mdi-graph',
                        text: "Create Procedure" 
                    },
                    {
                        starter: () => {
                            graph_view.cursor_state = "create";
                            graph_view.current_action = () => {
                                let highest_id = 0;

                                for (const element of graph_view.elements) {
                                    if (element.component_id != 0 && element.component_id != 1 && element.component_id != 4) continue;
                                    if (element.id > highest_id) highest_id = element.id;
                                }

                                graph_view.elements.push({
                                    id: highest_id + 1, component_id: 1, name: "Relation", data: {
                                        position: window.mouse_on_graph,
                                        type: 0,
                                        z_index: graph_view.elements.length
                                    }
                                });
                            };
                        },
                        icon: 'fas fa-database',
                        text: "Create Data Source" 
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
                                    let obj_rect = window.graph_elements[index].get_rect;
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
                        icon: 'fas fa-table',
                        text: "Create Data Entry" 
                    },
                    {
                        starter: () => {
                            graph_view.cursor_state = "create";
                            graph_view.current_action = () => {
                                let highest_id = 0;
                                for (const element of graph_view.elements) {
                                    if (element.component_id != 3) continue;
                                    if (element.id > highest_id) highest_id = element.id;
                                }

                                graph_view.elements.push({
                                    id: highest_id + 1, component_id: 3, name: "Enum", data: {
                                        position: window.mouse_on_graph,
                                        types: [],
                                        connected: [],
                                        z_index: graph_view.elements.length
                                    }
                                });
                            };
                        },
                        icon: 'fas fa-microchip',
                        text: "Create Process" 
                    }, 
                    {
                        starter: () => {
                            graph_view.cursor_state = "create";
                            graph_view.current_action = () => {
                                for (const element of graph_view.elements) {
                                    if (element.component_id != 2) continue;
                                    if (element.id > highest_id) highest_id = element.id;
                                }

                                graph_view.elements.push({
                                    id: highest_id + 1, component_id: 2, name: "Type", data: {
                                        position: window.mouse_on_graph,
                                        z_index: graph_view.elements.length
                                    }
                                });
                            }
                        },
                        icon: 'fas fa-tasks',
                        text: "Create Task"
                    },
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
                // De-activate context menu if present
                this.context_menu.is_active = false;

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
                // De-activate context menu if present
                this.context_menu.is_active = false;

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
            },
            update_process_data () {
                APIShift.API.request("Admin\\Logic\\Processes", "getProcessData", {id: holder.process_list[0].id}, function(response) {
                    if(response.status == APIShift.API.status_codes.SUCCESS) {
                        graph_view.elements = [];
                        graph_view.lines = [];

                        let connections_id = response.data.process_connections.map(pc => pc.connection);
                        let connections = connections_id.map(cid => response.data.connections[cid]);
                        let connection_node_types = response.data.connection_node_types;
                        let connection_types = {...response.data.connection_types, 0: {name: "Flow"}};
                    
                        for (const index in connections) {
                            let type = connection_types[connections[index].connection_type].name;
                            let counters = {functions: 0, rules: 0, processes: 0, tasks: 0, tables: 0, arrays: 0}
                            switch (type) {
                                case "Flow":
                                    
                                    break;
                                case "Function": 
                    
                                    let element = { 
                                            id: ++counters.functions, component_id: 2, name: "password_verify", data: {
                                                params: connections[index].params,
                                                position: { x: 400, y: 180 },
                                            }
                                        };
                                    
                                    graph_view.elements.push(element);
                                    break;
                                case "Rule": 
                                    break;
                                case "Process": 
                                    break;
                                case "Task": 
                                    break;
                                default:
                                    break;
                            }
                            console.log(type);
                            // Construct Flows
                            // let flow_elements = connections.filter(con => con.connection_type);
                        }
                        
                        
                        // this.process_list = response.data;
                    }
                    else APIShift.API.notify(response.data, 'error');
                });
            },
            on_save() {

            },
            dialog_save () {},
            dialog_discard () {
                this.edit_dialog = false;
            }
        },
        watch: {}
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
                            <v-list-item to="/logic">Main Page</v-list-item>
                            <v-list-item v-for="(item, key) in holder.sub_pages" :key="key" :to="item.url">{{ item.title }}</v-list-item>
                        </v-list>
                    </v-menu>
                    <v-divider class="mx-4" inset vertical></v-divider>
                    <v-menu offset-y style="z-index: 100">
                        <template v-slot:activator="{ on }">
                            <v-toolbar-title v-on="on">
                                <v-btn>
                                    {{ holder.getProcessName() }}
                                    
                                    <v-icon right>mdi-menu-down</v-icon>
                                </v-btn>
                            </v-toolbar-title>
                        </template>
                        <v-list>
                            <v-list-item v-for="(item, key) in holder.process_list" :key="key" :to="item.url">{{ item.name }}</v-list-item>
                        </v-list>
                    </v-menu>
                    <v-divider class="mx-4" inset vertical></v-divider>
                    <v-spacer></v-spacer>
                    <v-btn @click="on_save">SAVE</v-btn>
                </v-app-bar>

                <!-- Create/Edit dialog -->
                <v-dialog max-width="500px">
                    <v-card>
                        <v-card-title><span class="headline">{{ is_creating ? "Create" : "Edit" }} Task</span></v-card-title>
                        <v-card-text>
                            <v-container>
                                <v-row>

                                </v-row>
                            </v-container>
                        </v-card-text>

                        <v-card-actions>
                            <v-spacer></v-spacer>
                            <v-btn color="primary" text @click="dialog_save()">{{ is_creating ? "Create" : "Save" }}</v-btn>
                            <v-btn text @click="dialog_discard()">Cancel</v-btn>
                        </v-card-actions>
                    </v-card>
                </v-dialog>

                <!-- Body -->
                <div class="overflow-box">
                    <div id="graph_view"
                        @wheel.prevent="wheel"
                        @pointermove.prevent="drag_handler"
                        @touchmove.prevent="() => {}"
                        @pointerdown="pointer_down"
                        @pointerup="pointer_up"
                        @pointercancel="pointer_up"
                        @contextmenu.prevent="window.empty_function"
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

                            <!-- Lines -->
                            <component
                                v-for="(line, index) in lines"
                                :key="index"
                                :index="index"
                                :is="line_comp"
                                :src_ref="window.graph_elements[line.from_index]"
                                :dest_ref="window.graph_elements[line.to_index]"
                                :line_ref="line"
                                :data="line.data">
                            </component>
                        </div>
                        <component ref="s_box" 
                            v-show="selection_active"
                            :is="selection_comp">
                        </component>
                        <component ref="c_menu"
                            v-show="context_menu.is_active"
                            :actions="context_menu.actions"
                            :position="context_menu.position"
                            :is="context_menu_comp"> 
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

.ge_text {
    display: inline-block;
  width: fit-content; 

    padding: 0;
}
.ge_text input {
    width: fit-content;
    padding: 0;
}

.ge_text .v-text-field__details {
    min-height: 0px;
}

.ge_text .v-text-field__details .v-messages{
    min-height: 0px;
}
</style>