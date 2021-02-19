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

const loginVue=require("../login.vue");

    window.empty_function = (event) => {};

    // This shit is made for scripting
    module.exports = {
        data () {
            return {
                drawer: null,
                // Components 
                item_comp: APIShift.API.getComponent('orm/item', true),
                enum_comp: APIShift.API.getComponent('orm/enum', true),
                group_comp: APIShift.API.getComponent('orm/group', true),
                enum_type_comp: APIShift.API.getComponent('orm/enum_type', true),
                line_comp: APIShift.API.getComponent('orm/line', true),
                selection_comp: APIShift.API.getComponent('orm/selection_box', true),
                sidemenu_comp: APIShift.API.getComponent('orm/sidemenu', true),
                items: [
                    { name: "wait", id: 1, rect: { x: 20, y: 0, width: 0, height: 0 }, is_relation: false, data: {} },
                    { name: "haha", id: 2, rect: { x: 220, y: 200, width: 0, height: 0}, is_relation: false, data: {} },
                    { name: "rela", id: 3, rect: { x: 120, y: 50 , width: 0, height: 0}, is_relation: true, data: {
                        from: { id: 1, type: 'i' },
                        to: { id: 2, type: 'i' },
                        type: 0
                    } }
                ],  
                enums: [
                    { name: "Enum", id: 1, rect: { x: 50, y: 100, width: 0, height: 0},
                        data: {
                            types: [
                                {id: 1}
                            ],
                            connected: [
                                { type: 'i', id: 1 }
                            ]
                    }}
                ],
                enum_types: [
                    { name: 'Type', id: 1, rect: {  x: 100, y: 100, width: 0, height: 0}, data: { enum_id: 1 }},
                    { name: 'Type', id: 2, rect: {  x: 200, y: 100, width: 0, height: 0}, data: { enum_id: null }}
                ],
                groups: [
                    { 
                        name: 'Group', id: 1, rect: { x: 0, y: 0, width: 0, height: 0},
                        data: { 
                            contained_elements: [
                                {type: 'i', id: 1}, 
                                {type: 'i', id: 2}, 
                                {type: 'i', id: 3}
                            ]
                        }
                    }
                ],
                points: [], 
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
                    position: { x: 0, y: 0 },
                    data: { width: 0, height: 0 }
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
                void_point: { x: -Number.MAX_SAFE_INTEGER, y: -Number.MAX_SAFE_INTEGER },  
                init_rect: {x:0, y:0},
                cursor_state: {type: "default"}
            }
        },
        created () {
            // Store this object with a global reference
            window.graph_view = this;
            window.origin_position = {
                x: 0,
                y: 0
            };

            this.front_z_index = this.items.length;
        },
        mounted () {
            this.init_rect = this.$el.getBoundingClientRect();
            this.update_graph_position();

            window.addEventListener('keydown', this.key_down);
        },
        beforeDestroy () {
            window.removeEventListener('keydown', this.key_down);
        },
        methods: {
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
                    window.init_pointer_first = Object.assign({}, window.init_pointer);
                    window.init_pointer_second = {
                        x: event.clientX,
                        y: event.clientY
                    }
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
                    let mouse = {x: window.init_pointer.x - graph_position.x, y: window.init_pointer.y - graph_position.y };
                    let differential = {x: center_rect.x - graph_position.x, y: center_rect.y - graph_position.y};
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
                let change = 1 / (Math.sqrt((prev_diff.x * prev_diff.x + prev_diff.y * prev_diff.y) /
                    (new_diff.x * new_diff.x + new_diff.y * new_diff.y)));
                let new_scale = this.scale * change;
        
                // Keep the scale on bound
                if (new_scale < 0.2 || new_scale > 2 ) {
                    return;
                }
                this.scale = new_scale;

                // Move Camera
                this.camera.x += (window.init_pointer.x - mid.x) * (1 - change) + window.init_pointer.x - window.temp_pointer.x;
                this.camera.y += (window.init_pointer.y - mid.y) * (1 - change) + window.init_pointer.y - window.temp_pointer.y;
                window.temp_pointer = Object.assign({}, window.init_pointer);
            },
            pointer_up(event) {
                document.removeEventListener('pointerup', this.pointer_up);

                this.tap_counter = 0;

                if (this.cursor_state.type === 'select') {
                    graph_view.$refs['s_box'].end_select();
                }

                this.cursor_state = Object.assign({}, {type: 'default'});
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
                if (new_scale < 0.2 || new_scale > 2 ) {
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
                    this.items.forEach(item => {
                        let item_instance = graph_view.$refs[`i${item.id}`];
                        if (item_instance.selected && item_instance.group_owner === null) {
                            contained_elements.push({ type: 'i', id: item.id });
                            item_instance.selected = false;
                        }
                        console.log(item_instance.group_owner);
                    });
                    console.log(contained_elements);
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
            pan_by (dx, dy) {
                this.camera.x += dx;
                this.camera.y += dy;
            },
            /**
             * Control functions 
             */
            create_line ( src_info = { type: '', from_id: 0}, dest_info = {type: '', to_id: 0}, settings = {  }) {
                    var line_uid, from_uid, to_uid;

                    // String concatination 
                    from_uid  = src_info.type + src_info.id;
                    to_uid = dest_info.type + dest_info.id;

                    // Line already in list 
                    line_uid = `${from_uid}-${to_uid}`;
                    if (graph_view.lines.find((l) => l.line_uid === line_uid)) return;

                    // Add line to system 
                    graph_view.lines.push({line_uid, src_info, dest_info, settings});
            },
            delete_line ( line_uid ) {  
                    // Queue deletion to next frame execution due to potential race conditions
                    setTimeout(() => graph_view.lines = graph_view.lines.filter((line) => line.line_uid !== line_uid),0);
            },
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
                    console.log();
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
                    y: rect.y
                };
            },
            /**
             * Test whether 2 graph elements are hitting each other
             * @param {String} uid_1, @param {String} uid_1
             * @returns {Boolean} 
             */
            hittest: function(uid_1, uid_2){
                const comp_1 = graph_view.$refs[uid_1];
                const comp_2 = graph_view.$refs[uid_2];

                if (!comp_1 || !comp_2) return;

                var rect1 = comp_1.$el.getBoundingClientRect();
                var rect2 = comp_2.$el.getBoundingClientRect();

                var xOverlap = Math.max(0, Math.min(rect1.right , rect2.right) - Math.max(rect1.left, rect2.left));
                var yOverlap = Math.max(0, Math.min(rect1.bottom, rect2.bottom) - Math.max(rect1.top, rect2.top));

                return  (xOverlap * yOverlap) > 0 ;
            }
        },
        watch: {
            front_z_index: function(newValue) {
                app.$refs.navigator.updateIndex(newValue + 1);
                app.$refs.footer.updateIndex(newValue + 1);
                window.handler.updateIndex(newValue + 1);
            },
            cursor_state: function (state) {
                document.body.classList.add('reset-all-cursors');
                this.$el.classList.remove('cursor_default');
                this.$el.classList.remove('cursor_delete');
                this.$el.classList.remove('cursor_create');
                this.$el.classList.remove('cursor_select');

                if ( state.type === 'default') {
                    this.$el.classList.add('cursor_default');
                    document.body.classList.remove('reset-all-cursors');
                }
                if ( state.type === 'delete') {
                    this.$el.classList.add('cursor_delete');
                }
                if ( state.type === 'create') {
                    this.$el.classList.add('cursor_create');
                }
                if ( state.type === 'select') {
                    this.$el.classList.add('cursor_select');
                }
            }
        }
    }
</script>

<template ref="graphview">
    <div id="graph_view"
            @wheel.prevent="wheel"
            @pointermove.prevent="drag_handler"
            @touchmove.prevent="() => {}"
            @pointerdown="pointer_down"
            @pointerup="pointer_up"
            @pointercancel="pointer_up"
            :style="{ 'overflow' : 'hidden' }">
        <component ref="sidemenu" 
            :is="sidemenu_comp">
        </component>
        <!-- The center element allow us to create a smart camera that positions the elements without needed to re-render for each element -->
        <div ref="gv_center" id="graph_center" :style="{ 'transform': 'translate(' + camera.x + 'px, ' + camera.y + 'px) scale(' + scale + ')'}">
            <component
                v-for="(item) in items"
                :is="item_comp"
                :is_relation="item.is_relation"
                :key="'i'+item.id"
                :uid="'i'+item.id"
                :name="item.name"
                :index="item.index"
                :rect="item.rect"
                :data="item.data">
            </component> 
            <component
                v-for="(enum_type) in enum_types"
                :is="enum_type_comp"
                :key="'t'+enum_type.id"
                :uid="'t'+enum_type.id"
                :name="enum_type.name"
                :index="enum_type.index"
                :rect="enum_type.rect"
                :data="enum_type.data">
            </component>
            <component
                v-for="(enum_c) in enums"
                :is="enum_comp"
                :key="'e'+enum_c.id"
                :uid="'e'+enum_c.id"
                :name="enum_c.name"
                :index="enum_c.index"
                :rect="enum_c.rect"
                :data="enum_c.data">
            </component> 
            <component
                v-for="(group) in groups"
                :is="group_comp"
                :key="'g'+group.id"
                :uid="'g'+group.id"
                :name="group.name"
                :index="group.index"
                :rect="group.rect"
                :data="group.data">
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
                    v-for="(line) in lines"
                    :is="line_comp"
                    :key="line.line_uid"
                    :uid="line.line_uid" 
                    :src_info="line.src_info"
                    :dest_info="line.dest_info"
                    :settings="line.settings">
                </component> 
            </svg>
        </div>
        <div ref="gv_menu" id="gv_menu">
            
        </div>
        <component ref="s_box" 
            :is="selection_comp">
        </component>
    </div>
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