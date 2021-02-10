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

    //TODO: Design better z-index system.
    module.exports = {
        props: {
            data: Object
        },
        data () {
            return {
                leftbound: 0,
                topbound: 0,
                width: 0,
                height: 0,
                initial_event: null,
                graph_rect: { x: 0, y: 0 } 
            }
        },
        created () {
        },
        mounted () {
            graph_view.$refs['s_box'] = this;
            let rect = graph_view.$el.getBoundingClientRect();
            this.graph_rect.x = rect.x; 
            this.graph_rect.y = rect.y;
        },
        methods: {
            start_select (event) {
                this.initial_event = event;
                graph_view.drag_handler = this.on_select;
                // this.leftbound = window.init_pointer.x - this.graph_rect.x;
                // this.topbound = window.init_pointer.y - this.graph_rect.y
                // this.width = 50;
                // this.height = 50;
            },
            on_select (event) {
                this.set_rect(event);
                graph_view.items.forEach(item => {
                    if (graph_view.hittest('s_box', 'i' + item.id)) {
                        graph_view.$refs['i' + item.id].selected = true;
                    }
                });
            },
            end_select () {
                this.initial_event = null;
                graph_view.cursor_state = 'default';
            },
            set_rect(event) {
                console.log(event.clientX - this.graph_rect.x , event.clientY - this.graph_rect.y);
                this.width = Math.abs(this.initial_event.x - event.clientX);
                this.height = Math.abs(this.initial_event.y - event.clientY);
                this.leftbound = event.clientX >= this.initial_event.x ? this.initial_event.x : event.clientX;
                this.topbound = event.clientY >= this.initial_event.y ? this.initial_event.y : event.clientY;
            }
        }, 
        computed: {
            transformation () {
                return {
                    top: `${this.topbound - this.graph_rect.x}px`,
                    left:  `${this.topbound - this.graph_rect.y}px`,
                    height:`${this.height}px`,
                    width:`${this.width}px`,
                    'z-index':  1000

                }
            }
        },
    }
</script>

<template>
    <div class="s_box" color="#8789ff"
        :style="transformation"
        >
    
</template>

<style scoped>
/* Please style this crap, with style */

.s_box {
    position: absolute;
    left: 50px;
    top: 50px;
    width: 50px;
    height: 50px;
    transform-origin: 0 0;
    background: red;
}
</style>