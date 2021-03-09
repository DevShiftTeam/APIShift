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
                    APIShift.API.getComponent('orm/group', true)

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
                    { id: 3, component_id: 0, name: "Testers", data: {
                        position: { x: 20, y: 0 }
                    }},
                    // Relations
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
                    { id: 2, component_id: 2, name: 'testsdfsdfsd2', data: {
                        position: {x: 100, y: 100}
                    }},

                    // Enums
                    { id: 1, component_id: 3, name: 'enum1', data: {
                        position: {x: 50, y: 100},
                        types: [ 1, 2 ]
                    }},

                    /**
                    // Groups
                    { id: 1, component_id: 4, name: 'group', data: {
                        elements: [ 1, 2, 3 ]
                    }},
                    */
                ],
                points: [], 
                lookup_table: { 'i': [], 't': [], 'e': [], 'g': []},
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
                selection_box: {
                    rect: {
                        x: 0, y: 0, width: 0, height: 0
                    }
                },
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
                cursor_state: {type: "default"}
            }
        },
        created () {
            // Store this object with a global reference
            window.graph_elements = {};
            window.graph_view = this;

            // Set initial z-index
            for(var index in this.elements) this.$set(this.elements[index].data, 'z_index', parseInt(index) + 1);
        },
        mounted () {
            this.update_graph_position();

            window.addEventListener('keydown', this.key_down);
        },
        beforeDestroy () {
            window.removeEventListener('keydown', this.key_down);
        },
        methods: {
            bring_to_front: function(element_id) {
                // Ignore if in front
                if(this.elements[element_id].data.z_index !== undefined && this.elements[element_id].data.z_index == this.elements.length) return;

                let current_z_index = this.elements[element_id].data.z_index;

                // Bring other elements to back
                for(let index in this.elements)
                    if(this.elements[index].data.z_index > current_z_index)
                        this.elements[index].data.z_index--;
                

                // Bring to front
                this.elements[element_id].data.z_index = this.elements.length;
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
            
                let cursor_state = Object.assign({}, this.cursor_state);
                if (cursor_state.type === 'select') {
                    graph_view.$refs['s_box'].start_select(event);
                }
                if (cursor_state.type === 'create') {
                    // Determine pointer position in respect to graph transformation
                    let center_rect = document.getElementById('graph_center').getBoundingClientRect();
                    let mouse = { x: window.init_pointer.x - graph_position.x, y: window.init_pointer.y - graph_position.y };
                    let differential = { x: center_rect.x - graph_position.x, y: center_rect.y - graph_position.y};
                    let t_mouse = { x: (mouse.x-differential.x) / this.scale, y: (mouse.y - differential.y) / this.scale};

                    if (cursor_state.data === 'add-enum') {
                        graph_view.create_element_on_runtime('enum', 
                        {
                            data: { connected: [], types: []},
                            rect: {...t_mouse, width: 0, height: 0}
                        });
                    }
                    if (cursor_state.data === 'add-enum-type') {
                        graph_view.create_element_on_runtime('enum-type', 
                        { 
                            data: {enum_id: null},
                            rect: {...t_mouse, width: 0, height: 0}
                        });
                    }
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

                if (this.cursor_state.type === 'select') {
                    graph_view.$refs['s_box'].end_select();
                }

                if(this.cursor_state.data !== 'add-relation') this.cursor_state = Object.assign({}, {type: 'default'});
                // Reset drag event to none
                this.drag_handler = window.empty_function;

                this.scroll_manager.stop();
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
            key_down (event){
                if (event.code === 'KeyG') {
                    // Determine contained elements
                    let contained_elements = [];
                    this.groups.forEach(group => {
                        let group_instance = graph_view.$refs[`g${group.id}`];
                        if (group_instance.selected) {
                            contained_elements.push({ type: 'g', id: group.id });
                            group_instance.selected = false;
                        }
                    });
                    graph_view.create_element_on_runtime('group', {name: 'Group', rect: {x: 0, y: 0, height: 0, width: 0}, data: {contained_elements}})
                }                
            },
            move_camera_by (dx, dy) {
                this.camera.x += dx;
                this.camera.y += dy;
            },
            /**
             * Create new element on screen according to params.
             * @param {String} type The type to show
             * @param {Object} properties Unique element properties to set
             */
            create_element_on_runtime (type, properties = {}) { 
                const common = { name: properties.name, 
                                index: graph_view.front_z_index, 
                                rect: { ...properties.rect }, 
                                data: properties.data };
                
                if (type === 'item') {
                    let item_id = Math.max(...graph_view.items.map(i => i.id),0) + 1;
                    graph_view.items.push({...common, id: item_id});
                }
                if (type === 'enum') {
                    let enum_id = Math.max(...graph_view.enums.map(e => e.id),0) + 1;
                    graph_view.enums.push({...common, id: enum_id});
                }
                if (type === 'enum-type') {
                    let enum_type_id = Math.max(...graph_view.enum_types.map(t => t.id),0) + 1;
                    graph_view.enum_types.push({...common, id: enum_type_id});
                }
                if (type === 'group') {
                    let group_id = Math.max(...graph_view.groups.map(g => g.id),0) + 1;
                    graph_view.groups.push({...common, id: group_id });
                }
            },
            // Update graph position
            update_graph_position() {
                let rect = this.$el.getBoundingClientRect();
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
            /**
             * Function that gets Info about an element and calucaltes its absolute position relative to the graph.
             * @param {Info} info
             * @returns {Rect} 
             */
            // TODO: Calculate rect mathematically
            inverse_transformation (info) { 
                let revert_rect = {
                    x: 0,
                    y: 0,
                    width: 0,
                    height: 0,
                }

                const instance = this.$refs[info.type + info.id];
                if (instance) {
                    const el_rect = instance.$el.getBoundingClientRect();
                    revert_rect = {
                        x: el_rect.x - window.graph_position.x,
                        y: el_rect.y - window.graph_position.y,
                        width: el_rect.width,
                        height: el_rect.height,
                    }
                }
                return revert_rect;
            },
            /**
             * Function that gets element's Info and returns the corresponding Element.
             * The function uses simple caching mechanism for faster Element lookup (O(1) instead of 0(n)).
             * @param {Info} info
             * @returns {Element} 
             */
            get_element_by_info (info) {
                if(!this.lookup_table[info.type][info.id]) {
                    switch (info.type) {
                        case 'i':
                            this.lookup_table['i'][info.id] = graph_view.items.find((i) => i.id === info.id);
                            break;
                        case 'e':
                            this.lookup_table['e'][info.id] = graph_view.enums.find((e) => e.id === info.id);
                            break;
                        case 't':
                            this.lookup_table['t'][info.id] = graph_view.enum_types.find((t) => t.id === info.id);
                            break;
                        case 'g':
                            this.lookup_table['g'][info.id] = graph_view.groups.find((g) => g.id === info.id);
                            break;
                        default:
                            break;
                    }
                }
                return this.lookup_table[info.type][info.id];
            }
        },
        computed: {

        },
        watch: {
            cursor_state: function (state) {
                document.body.classList.add('reset-all-cursors');
                this.$el.classList.remove('cursor_default');
                this.$el.classList.remove('cursor_delete');
                this.$el.classList.remove('cursor_create');
                this.$el.classList.remove('cursor_select');

                this.$el.classList.add('cursor_' + state.type);
                if ( state.type === 'default') document.body.classList.remove('reset-all-cursors');
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
                        :style="{ 'overflow' : 'hidden' }">
                        <component ref="side_menu" :is="side_menu_comp"></component>
                        
                        <!-- The center element allow us to create a smart camera that positions the elements without needed to re-render for each element -->
                        <div ref="gv_center" id="graph_center" :style="{ 'transform': 'translate(' + camera.x + 'px, ' + camera.y + 'px) scale(' + scale + ')'}">
                            <!-- Elements -->
                            <component
                                v-for="(element, index) in elements"
                                :is="components[element.component_id]"
                                :key="index"
                                :index="index"
                                :id="element.id"
                                :name="element.name"
                                :data="element.data">
                            </component>

                            <svg id="svg_viewport" ref="gv_lines">
                                <defs>
                                    <marker id="black-arrow" markerWidth="5" markerHeight="5" refX="0" refY="5"
                                    viewBox="0 0 10 10" orient="auto-start-reverse" style="opacity: 0.85">
                                        <path d="M 0 0 L 10 5 L 0 10 z" />
                                    </marker>
                                    <marker id="arrow" markerWidth="10" markerHeight="10" refX="10" refY="3" orient="auto" markerUnits="strokeWidth">
                                        <path d="M0,0 L0,6 L9,3 z" fill="rgba(255,0,0,0.9)" />
                                    </marker>
                                    <marker id="arrow1" viewBox="0 0 492.004 492.004" markerWidth="5" markerHeight="5" orient="auto-start-reverse">
                                        <path d="M382.678,226.804L163.73,7.86C158.666,2.792,151.906,0,144.698,0s-13.968,2.792-19.032,7.86l-16.124,16.12
                                        c-10.492,10.504-10.492,27.576,0,38.064L293.398,245.9l-184.06,184.06c-5.064,5.068-7.86,11.824-7.86,19.028
                                        c0,7.212,2.796,13.968,7.86,19.04l16.124,16.116c5.068,5.068,11.824,7.86,19.032,7.86s13.968-2.792,19.032-7.86L382.678,265
                                        c5.076-5.084,7.864-11.872,7.848-19.088C390.542,238.668,387.754,231.884,382.678,226.804z"/>
                                    </marker>
                                </defs>
                                <component
                                    v-for="(line, index) in lines"
                                    :is="line_comp"
                                    :key="index"
                                    :src_point="window.graph_elements[line.from_index].from_position"
                                    :dest_point="window.graph_elements[line.to_index].to_position"
                                    :data="line.data">
                                </component> 
                            </svg>
                        </div>
                        <component ref="s_box" 
                            :is="selection_comp"
                            :uid="'s0'"
                            :rect="selection_box.rect">
                        </component>
                    </div>
                </div>
            </v-card>
        </v-container>
</template>

<style scoped>
/* Please style this crap, with style */

/* Disables all cursor overrides when body has this class. */
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