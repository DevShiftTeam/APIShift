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
                items: [
                    { name: "wait", id: 1,index: 0, position: { x: 20, y: 0 }, is_relation: false, data: {} },
                    { name: "haha", id: 2, index: 1, position: { x: 400, y: 0 }, is_relation: false, data: {} },
                    { name: "rela", id: 3, index: 2, position: { x: 120, y: 50 }, is_relation: true, data: {
                        from: 1,
                        to: 2,
                        type: 0
                    } }
                ],  
                item_enums: [{ enum_id: 1, item_id: 2}],
                enums: [
                    { name: "Enum", id: 1, index: 4, position: { x: 50, y: 100 }}
                ],
                enum_types: [
                    { name: 'Type', id: 1, index: 5, position: { x: 100, y: 100 }, enum_id: 1 },
                    { name: 'Type', id: 2, index: 6, position: { x: 200, y: 100 }, enum_id: null }
                ],
                group_items: [
                    {group_id: 1, item_id: 1}, 
                    {group_id: 1, item_id: 2},
                    {group_id: 1, item_id: 3} 
                ],
                groups: [
                    { name: 'Group', id: 1, index: 7, position: {x: 0, y: 0} }
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
                selection_box: {
                    position: { x: 0, y: 0 },
                    data: { width: 0, height: 0 }
                },
                cursor_state: 'default'
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

        },

        methods: {

            /**
             * User interactions
             */
            pointer_down(event) {
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
            
                console.log(init_pointer);

                if (this.cursor_state === 'select') {
                    graph_view.$refs['s_box'].start_select(event);
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
                this.scale *= change;

                // Move Camera
                this.camera.x += (window.init_pointer.x - mid.x) * (1 - change) + window.init_pointer.x - window.temp_pointer.x;
                this.camera.y += (window.init_pointer.y - mid.y) * (1 - change) + window.init_pointer.y - window.temp_pointer.y;
                window.temp_pointer = Object.assign({}, window.init_pointer);
            },
            pointer_up(event) {
                this.tap_counter = 0;

                if (this.cursor_state === 'select') {
                    graph_view.$refs['s_box'].end_select();
                }
                // Reset drag event to none
                this.drag_handler = window.empty_function;
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
                this.scale *= change;
                
                // Move camera to fit mouse as scaling center
                this.camera.x += (window.init_pointer.x - mid.x) * (1 - change);
                this.camera.y += (window.init_pointer.y - mid.y) * (1 - change);
            },
            /**
             * Control functions 
             */
            create_line ( from_id = 0, to_id = 0, settings = { item_to_relation: false, relation_to_item: false, enum_to_item: false }) {
                    var line_uid, from_uid, to_uid;
                    var src_instance, dest_instance;

                    // Build line unique id from params 
                    if ( settings.enum_to_item ) {
                        from_uid = `e${from_id}`;
                        to_uid   = `i${to_id}`;
                    } 
                    else if (settings.item_to_relation || settings.relation_to_item) {
                        from_uid = `i${from_id}`;
                        to_uid   = `i${to_id}`;
                    } 
                    else return;

                    // Line already in list 
                    line_uid = `${from_uid}-${to_uid}`;
                    if (graph_view.lines.find((l) => l.line_uid === line_uid)) return;

                    // Add line to system 
                    src_instance  = graph_view.$refs[from_uid];
                    dest_instance = graph_view.$refs[to_uid];
                    src_instance.add_line(line_uid);
                    dest_instance.add_line(line_uid);
                    graph_view.lines.push({line_uid, from_uid, to_uid, settings});
            },
            remove_line ( line_uid ) {
                
                    let src_instance  = graph_view.$refs[from_index];
                    let dest_instance = graph_view.$refs[to_index];
                    // graph_view.lines.push({from_index, to_index, settings});
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
            item_enums: function(item_enums) {
                let new_connection = item_enums.slice(-1).pop();
                graph_view.create_line(new_connection.enum_id, new_connection.item_id, {enum_to_item: true});
            },
            group_items: function(item_enums) {
                
            },
            cursor_state: function (state) {
                if ( state === 'default') {
                    this.$el.style.cursor = 'auto';
                }
                if ( state === 'delete') {
                    this.$el.style.cursor = 'not-allowed';
                }
                if ( state === 'create') {
                    this.$el.style.cursor = 'copy';
                }
                if ( state === 'select') {
                    this.$el.style.cursor = 'se-resize';
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
            :style="{ 'overflow' : 'hidden' }">
        <!-- The center element allow us to create a smart camera that positions the elements without needed to re-render for each element -->
        <div ref="gv_center" id="graph_center" :style="{ 'transform': 'translate(' + camera.x + 'px, ' + camera.y + 'px) scale(' + scale + ')'}">
            <component
                v-for="(item, index) in items"
                :is="item_comp"
                :is_relation="item.is_relation"
                :key="index"
                :uid="'i'+item.id"
                :name="item.name"
                :index="item.index"
                :position="item.position"
                :data="item.data">
            </component>
            <component
                v-for="(enum_type, index) in enum_types"
                :is="enum_type_comp"
                :key="index"
                :uid="'t'+enum_type.id"
                :name="enum_type.name"
                :index="enum_type.index"
                :position="enum_type.position">
            </component>
            <component
                v-for="(enum_c, index) in enums"
                :is="enum_comp"
                :key="index"
                :uid="'e'+enum_c.id"
                :name="enum_c.name"
                :index="enum_c.index"
                :position="enum_c.position"
                :data="enum_c.data">
            </component>
            <component
                v-for="(group, index) in groups"
                :is="group_comp"
                :key="index"
                :uid="'g'+group.id"
                :name="group.name"
                :index="group.index"
                :position="group.position">
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
                    :uid="line.line_uid" 
                    :from_uid="line.from_uid"
                    :to_uid="line.to_uid"
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
</style>