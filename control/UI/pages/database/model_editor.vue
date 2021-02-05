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
                relation_comp: APIShift.API.getComponent('orm/relation', true),
                enum_comp: APIShift.API.getComponent('orm/enum', true),
                group_comp: APIShift.API.getComponent('orm/group', true),
                type_comp: APIShift.API.getComponent('orm/enum_type', true),
                line_comp: APIShift.API.getComponent('orm/line', true),
                items: [
                    { name: "wait", index: 0, position: { x: 0, y: 0 } },
                    { name: "haha", index: 1, position: { x: 0, y: 0 } }
                ],
                relations: [
                    { name: "wait", relate_from: 0, relate_to: 1, relate_type: 0, index: 2, position: { x: 0, y: 0 } }
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
                }
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
            create_line ( from_index = 0, to_index = 0, settings = { item_to_relation: false, relation_to_item: false, item_to_enum: false }) {
                    const line_uid = `${from_index}c${to_index}`;

                    let src_instance  = graph_view.$refs[from_index];
                    let dest_instance = graph_view.$refs[to_index];

                    src_instance.add_line(line_uid);
                    dest_instance.add_line(line_uid);
                    graph_view.lines.push({from_index, to_index, settings});
            },
            // Update graph position
            update_graph_position() {
                let rect = this.$el.getBoundingClientRect();
                // Stores the current position of the graph on the screen
                window.graph_position = {
                    x: rect.x,
                    y: rect.y
                };
            }
        },
        watch: {
            front_z_index: function(newValue) {
                app.$refs.navigator.updateIndex(newValue + 1);
                app.$refs.footer.updateIndex(newValue + 1);
                window.handler.updateIndex(newValue + 1);
            }
        },
        computed: {
            basic_items: function () {
                return this.items.filter( i => i.is_relation === false);
            },
            relation_items: function () {
                return this.items.filter( i => i.is_relation === true);
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
                :key="index"
                :ref="item.index"
                :name="item.name"
                :index="item.index"
                :position="item.position">
            </component>
            <component
                v-for="(item, index) in relations"
                :is="relation_comp"
                :key="index"
                :ref="item.index"
                :relate_from="item.relate_from"
                :relate_to="item.relate_to"
                :relate_type="item.relate_type"
                :name="item.name"
                :index="item.index"
                :position="item.position">
            </component>
            <svg id="svg_viewport">
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
                    :ref="`${line.from_index}c${line.to_index}`" 
                    :from_index="line.from_index"
                    :to_index="line.to_index"
                    :settings="line.settings"
                    :scale="scale">
                </component>
            </svg>
        </div>
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