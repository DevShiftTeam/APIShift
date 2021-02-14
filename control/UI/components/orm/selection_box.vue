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
                leftbound: -Number.MAX_SAFE_INTEGER,
                topbound: -Number.MAX_SAFE_INTEGER,
                width: 0,
                height: 0,
                ds: { x: 0, y: 0 },
                // rect: {x1: 0, p},
                mouse_start: { x: 0, y: 0 },
                last_event: null,
                graph_position: { x: 0, y: 0 }
            }
        },
        created () {
        },
        mounted () {

        },
        methods: {
            start_select (event) {
                this.init_pointer = Object.assign({}, { x: event.clientX, y: event.clientY }); 
                this.graph_position = Object.assign({}, { x: graph_position.x, y: graph_position.y }); 

                // Set graph view drag handler
                graph_view.drag_handler = this.on_select; 
                
                // Start graph view scroll manager and pass handler 
                graph_view.scroll_manager.start(this.on_scroll, 20);

                this.last_event = event;
            },
            on_select (event) {
                this.set_rect(event);
                graph_view.items.forEach(item => {
                    if (graph_view.hittest('s_box', 'i' + item.id)) {
                        graph_view.$refs['i' + item.id].selected = true;
                    } else {
                        graph_view.$refs['i' + item.id].selected = false;
                    }
                });
                this.last_event = event;
            },
            end_select () {
                this.leftbound = -Number.MAX_SAFE_INTEGER;
                this.topbound = -Number.MAX_SAFE_INTEGER;
                graph_view.cursor_state = 'default';

                // Stop scroll handler
                graph_view.scroll_manager.stop();
            },
            on_scroll () {
                if (this.leftbound === this.topbound) return;

                let mouse = { x: this.last_event.pageX - window.graph_position.x, y: this.last_event.pageY - window.graph_position.y };

                if (mouse.x < 20) {
                    this.ds.x += 5;
                    graph_view.pan_by(5 , 0);
                }
                if (mouse.x > graph_view.init_rect.width - 20 ) {
                    this.ds.x += -5;
                    graph_view.pan_by(-5 , 0);
                }
                if (mouse.y < 20) {
                    this.ds.y += 5;
                    graph_view.pan_by(0, 5);
                }
                if (mouse.y > graph_view.init_rect.height - 20 ) {
                    this.ds.y += -5;
                    graph_view.pan_by(0, -5 );
                }
                this.set_rect(this.last_event);
            },
            set_rect(event) {
                this.width  = Math.abs(event.clientX - this.ds.x - this.init_pointer.x);
                this.height = Math.abs(event.clientY - this.ds.y - this.init_pointer.y);

                let calcLeft, calcTop;
                if (event.clientX > this.init_pointer.x + this.ds.x) {
                    calcLeft = this.init_pointer.x - graph_position.x + this.ds.x;
                } else {
                    calcLeft = this.init_pointer.x - graph_position.x + this.ds.x - this.width; 
                };

                if (event.clientY > this.init_pointer.y + this.ds.y) {
                    calcTop = this.init_pointer.y - graph_position.y + this.ds.y;
                } else {
                    calcTop = this.init_pointer.y - graph_position.y + this.ds.y - this.height; 
                }

                this.leftbound = calcLeft;
                this.topbound = calcTop;
            }
        }, 
        computed: {
            transformation () {
                return {
                    top: `${this.topbound}px`,
                    left:  `${this.leftbound}px`,
                    height:`${Math.abs(this.height)}px`,
                    width:`${Math.abs(this.width)}px`,
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
    transform-origin: 0 0;
    border: dotted white 1px;
    background: rgba(255,255,255,0.1);
}
</style>