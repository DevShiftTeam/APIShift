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
                item_comp: APIShift.API.getComponent('orm/item', true),
                items: [
                    { is_relation: false, name: "wait", index: 0, position: { x: 0, y: 0 } },
                    { is_relation: false, name: "haha", index: 1, position: { x: 0, y: 0 } }
                ],
                relative: {
                    x: 0,
                    y: 0
                },
                // Index of the most frontal element
                front_z_index: 0,
                scale: 1,
                /* Drag & Drop functional data */
                event_list: {},
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
                }
            }
        },
        created () {
            // Store this object with a global reference
            window.graph_view = this;

            for(var x in [...Array(200).keys()]) {
                this.items.push({
                    is_relation: false,
                    name: "w" + x,
                    index: (Number(x) + 1),
                    position: {
                        x: Math.floor(Math.random() * Math.floor(1000)),
                        y: Math.floor(Math.random() * Math.floor(800))
                    }
                })
            }
            this.front_z_index = this.items.length;
        },
        mounted () {
            this.$el.type = "graphview";
            this.$refs['graphview'] = this;
        },
        methods: {
            pointer_down(event) {
                // Add event to event cache, determine interactive target 
                this.event_list[event.eventId] = event;
                
                // Update graph position
                let rect = this.$el.getBoundingClientRect();
                this.graph_position = {
                    x: rect.left,
                    y: rect.top
                };

                // Get initiale pointer coordinates
                this.init_pointer.x = event.clientX;
                this.init_pointer.y = event.clientY;

                // viewport panning / element movement 
                if (this.event_list.length === 1 && event.ctrlKey) {
                    delete this.event_list[event.eventId];
                }

                // Proceeds only if not dragging any other object
                if(this.drag_handler != window.empty_function) return;

                this.init_camera = Object.assign({}, this.camera);
                this.drag_handler = this.pointer_move;
            },
            pointer_move(event) {
                this.camera.x = this.init_camera.x + event.clientX - this.init_pointer.x;
                this.camera.y = this.init_camera.y + event.clientY - this.init_pointer.y;
            },
            pointer_up(event) {
                console.log(JSON.stringify(this.event_list));
                // Remove event from event cache
                delete this.event_list[event.eventId];
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
            yes() {
                return 'yes';
            }
        },
        watch: {
            front_z_index: function(newValue) {
                app.$refs.navigator.updateIndex(newValue + 1);
                app.$refs.footer.updateIndex(newValue + 1);
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
        <!-- The center element allow us to create a smart camera that positions the elements without needed to re-render for each element -->
        <div id="graph_center"
            :style="{ 'top': camera.y + 'px', 'left': camera.x + 'px'}">
                <component
                v-for="(item, index) in items"
                :is="item_comp"
                :key="index"
                :ref="item.name"
                :relative="init_pointer"
                :is_relation="item.is_relation"
                :scale="scale"
                :name="item.name"
                :index="item.index"
                :position="item.position">
                </component>
                <component
                    v-for="(line, index) in lines"
                    :is="line_comp"
                    :key="index"
                    :ref="name"
                    :src_index="line.src_index"
                    :dest_index="line.dest_index"
                    :scale="scale"
                ></component>
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