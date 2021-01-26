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

    // This shit is made for scripting
    module.exports = {
        data () {
            return {
                drawer: null,
                item_comp: APIShift.API.getComponent('orm/item', true),
<<<<<<< HEAD
                items: [ ],
=======
                items: [
                    { is_relation: false, name: "wait", z_index: 0 },
                    { is_relation: false, name: "haha", z_index: 1 }
                ],
>>>>>>> e78eac51e1449ce6c6c08cedff2c553e20cb3d4b
                relative: {
                    x: 0,
                    y: 0
                },
                scale: 1,
                /* Drag & Drop functional data */
                event_list: [],
                drag_handler: (event) => {},
                mouse_pressing: false,
                graph_rect: {},
                // Defines the camera - when moving the whole graph
                camera: {
                    x: 0,
                    y: 0
                },
                init_relative_camera: {
                    x: 0,
                    y: 0
                }
            }
        },
        created () {
<<<<<<< HEAD
            
=======
            // Store this object with a global reference
            window.graph_view = this;
            graph_view.drag_handler = graph_view.pointer_move;

            for(var x in [...Array(100).keys()]) {
                this.items.push({
                    is_relation: false, name: "w" + x
                })
            }
        },
        mounted () {
            this.$el.type = "graphview";
            this.$refs['graphview'] = this;
            this.graph_rect = this.$el.getBoundingClientRect();
        },
        methods: {
            pointer_down(event) {
                // Add event to event cache, determine interactive target 
                this.event_list.push(event);

                // viewport panning / element movement 
                if (this.event_list.length === 1 && event.ctrlKey) {
                    this.remove_event(event);
                }

                this.init_relative_camera.x = event.clientX - this.graph_rect.left - this.camera.x;
                this.init_relative_camera.y = event.clientY - this.graph_rect.top - this.camera.y;
                this.mouse_pressing = true;
            },
            pointer_move(event) {
                if(!this.mouse_pressing) return;
                
                if (this.event_list.length === 1) {
                    this.camera.x = event.clientX - this.graph_rect.left - this.init_relative_camera.x;
                    this.camera.y = event.clientY - this.graph_rect.top - this.init_relative_camera.y;
                }
                if (this.event_list.length === 2) {
                    // Scaling Logic, cooming soon //
                        
                }

                this.update_event(event);
            },
            pointer_up(event) {
                // Remove event from event cache
                this.remove_event(event);
                this.mouse_pressing = false;
                graph_view.drag_handler = graph_view.pointer_move;

                if (this.event_list.length < 2) {
                    this.event_list = [];
                }
            },
            // Every pointer having his own pointer ID, we can use it for multi-pointer events manipulations
            remove_event(event) {
                for (var i = 0; i < this.event_list.length; i++) {
                    if (event.pointerId == this.event_list[i].pointerId) {
                        this.event_list.splice(i, 1);
                        break;
                    }
                }
            },
            update_event(event) {
                for (var i = 0; i < this.event_list.length; i++) {
                    if (event.pointerId == this.event_list[i].pointerId) {
                        this.event_list[i] = event;
                        break;
                    }
                }
            },
            wheel (event) {
                this.relative.x = event.clientX - this.graph_rect.left;
                this.relative.y = event.clientY - this.graph_rect.top;

                var delta = event.deltaY;
                if (event.deltaMode > 0) delta *= 100;

                var sign = Math.sign(delta), speed = 1;
                var deltaAdjustedSpeed = Math.min(0.25, Math.abs(speed * delta / 128));

                graph_view.scale *= (1 - sign * deltaAdjustedSpeed);
            },
            yes() {
                return 'yes';
            }
>>>>>>> e78eac51e1449ce6c6c08cedff2c553e20cb3d4b
        }
    }
</script>

<template ref="graphview">
    <div id="graphview"
        @wheel.prevent="wheel"
        @pointermove="drag_handler"
        @pointerdown="pointer_down"
        @pointerup="pointer_up"
        :style="{ 'padding-top': camera.y + 'px', 'padding-left': camera.x + 'px'}">
        <component
            v-for="item in items"
            :is="item_comp"
            :key="item.name"
            :ref="item.name"
            :relative="relative"
            :is_relation="item.is_relation"
            :scale="scale"
            :name="item.name">
            
            </component>
    </div>
</template>

<style scoped>
/* Please style this crap, with style */
#graphview {
    position: relative;
    width: 100%;
    height: 100%;
}
</style>