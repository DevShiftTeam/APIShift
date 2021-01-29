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
                // Stores the current position of the graph on the screen
                graph_position: {
                    x: 0,
                    y: 0
                },
                // Defines the camera origin relative to the initial 0,0 position
                camera: {
                    x: 0,
                    y: 0
                },
                init_camera: {
                    x: 0,
                    y: 0
                },
                init_pointer: {
                    x: 0,
                    y: 0
                },
                init_pointer_first: {
                    x: 0,
                    y: 0
                },
                init_pointer_second: {
                    x: 0,
                    y: 0
                }
            }
        },
        created () {
            // Store this object with a global reference
            window.graph_view = this;

            // for(var x in [...Array(200).keys()]) {
            //     this.items.push({
            //         name: "w" + x,
            //         index: (Number(x) + 1),
            //         position: {
            //             x: Math.floor(Math.random() * Math.floor(1000)),
            //             y: Math.floor(Math.random() * Math.floor(800))
            //         }
            //     })
            // }
            this.front_z_index = this.items.length;
        },
        mounted () {
            this['graphview'] = this;
            console.log(this.$refs);
        },
        methods: {

            /**
             * User interactions
             */
            pointer_down(event) {
                console.log(this.$refs);
                // Add event to event cache, determine interactive target
                this.tap_counter++;
                
                // Update graph position
                let rect = this.$el.getBoundingClientRect();
                this.graph_position = {
                    x: rect.left,
                    y: rect.top
                };
                
                // Handle mobile zooming
                if(this.tap_counter === 2) {
                    this.init_pointer_first = Object.assign({}, this.init_pointer);
                    this.init_pointer_second.x = event.clientX;
                    this.init_pointer_second.y = event.clientY;

                    // Calculate center
                    this.init_pointer.x = (this.init_pointer_first.x + this.init_pointer_second.x) / 2 - this.graph_position.x - this.camera.x;
                    this.init_pointer.y = (this.init_pointer_first.y + this.init_pointer_second.y) / 2 - this.graph_position.y - this.camera.y;
                    
                    this.drag_handler = this.pointer_scale;
                    return;
                }

                // Get initiale pointer coordinates
                this.init_pointer.x = event.clientX;
                this.init_pointer.y = event.clientY;
                this.init_camera = Object.assign({}, this.camera);

                // Proceeds only if not dragging any other object
                if(this.drag_handler != window.empty_function) return;

                this.drag_handler = this.pointer_move;
            },
            pointer_move(event) {
                this.camera.x = this.init_camera.x + event.clientX - this.init_pointer.x;
                this.camera.y = this.init_camera.y + event.clientY - this.init_pointer.y;
            },
            pointer_scale (event) {
                // Calculate previous distance vector
                let prev_diff = {
                    x: this.init_pointer_first.x - this.init_pointer_second.x,
                    y: this.init_pointer_first.y - this.init_pointer_second.y
                };

                // Calculate new point
                if(Math.abs(this.init_pointer_first.y - event.clientY) + Math.abs(this.init_pointer_first.x - event.clientX)
                    < Math.abs(this.init_pointer_second.y - event.clientY) + Math.abs(this.init_pointer_second.x - event.clientX)) {
                    this.init_pointer_first.x = event.clientX;
                    this.init_pointer_first.y = event.clientY;
                }
                else {
                    this.init_pointer_second.x = event.clientX;
                    this.init_pointer_second.y = event.clientY;
                }

                // Calculate new distance vector
                let new_diff = {
                    x: this.init_pointer_first.x - this.init_pointer_second.x,
                    y: this.init_pointer_first.y - this.init_pointer_second.y
                };

                // Update scale center
                this.init_pointer.x -= - this.graph_position.x - this.camera.x;
                this.init_pointer.y -= - this.graph_position.y - this.camera.y;
                let temp = Object.assign({}, this.init_pointer);
                this.init_pointer.x = (this.init_pointer_first.x + this.init_pointer_second.x) / 2;
                this.init_pointer.y = (this.init_pointer_first.y + this.init_pointer_second.y) / 2;

                // Move Camera
                this.camera.x += this.init_pointer.x - temp.x;
                this.camera.y += this.init_pointer.y - temp.y;

                // Update scale
                let delta_scale = Math.sqrt(prev_diff.x * prev_diff.x + prev_diff.y * prev_diff.y) /
                    Math.sqrt(new_diff.x * new_diff.x + new_diff.y * new_diff.y);
                this.init_pointer.x += - this.graph_position.x - this.camera.x;
                this.init_pointer.y += - this.graph_position.y - this.camera.y;
                graph_view.scale *= 1 / delta_scale;
            },
            pointer_up(event) {
                this.tap_counter = 0;
                // Reset drag event to none
                this.drag_handler = window.empty_function;
            },
            wheel (event) {
                this.init_pointer.x = event.clientX - this.graph_position.x - this.camera.x;
                this.init_pointer.y = event.clientY - this.graph_position.y - this.camera.y;

                var delta = event.deltaY;
                if (event.deltaMode > 0) delta *= 100;

                var sign = Math.sign(delta), speed = 1;
                var deltaAdjustedSpeed = Math.min(0.25, Math.abs(speed * delta / 128));

                graph_view.scale *= (1 - sign * deltaAdjustedSpeed);
            },
            /**
             * Control functions 
             */
            create_line ( from_index = 0, to_index = 0, options = { item_to_relation: false, relation_to_item: false, item_to_enum: false }) {
                    const graphview     = this;    
                    const line_uid = `${from_index}c${to_index}`;  
                    let src_instance, dest_instance; 

                    var loop_untill_ok = () => {
                        src_instance  = graphview.$refs[from_index];
                        dest_instance = graphview.$refs[to_index];

                        if (!src_instance || !dest_instance) {
                            requestAnimationFrame(loop_untill_ok);
                        }
                        else {
                            src_instance.add_line(line_uid);
                            dest_instance.add_line(line_uid);
                            graphview.lines.push({from_index, to_index, options});
                        }
                    }
                    loop_untill_ok();
            },
            yes() {
                return 'yes';
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
                console.log(this.items.filter( i => i.is_relation === false));
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
        <div id="graph_center"
            :style="{ 'top': camera.y + 'px', 'left': camera.x + 'px'}">
                <component
                v-for="(item, index) in items"
                :is="item_comp"
                :key="index"
                :ref="item.index"
                :relative="init_pointer"
                :scale="scale"
                :name="item.name"
                :index="item.index"
                :position="item.position">
                </component>
                <component
                v-for="(item, index) in relations"
                :is="relation_comp"
                :key="index"
                :ref="item.index"
                :relative="init_pointer"
                :relate_from="item.relate_from"
                :relate_to="item.relate_to"
                :relate_type="item.relate_type"
                :scale="scale"
                :name="item.name"
                :index="item.index"
                :position="item.position">
                </component>
                <component
                v-for="(line, index) in lines"
                :is="line_comp"
                :key="index"
                :ref="`${line.from_index}c${line.to_index}`" 
                :from_index="line.from_index"
                :to_index="line.to_index"
                :relative="init_pointer"
                :scale="scale">
                </component>
                
        </div>
    </div>
</template>

<style scoped>
/* Please style this crap, with style */
#graph_view, #graph_center {
    position: relative;
    width: 100%;
    height: 100%;
}
</style>