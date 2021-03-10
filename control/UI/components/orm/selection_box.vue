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
                rect: {
                    x:  -Number.MAX_SAFE_INTEGER,
                    y:  -Number.MAX_SAFE_INTEGER,
                    width: 0,
                    height: 0
                },
                selectables: null, // Should be represented as {Info, Rect}
                ds: { x: 0, y: 0 },
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
                this.init_pointer   = Object.assign({}, { x: event.clientX, y: event.clientY }); 
                this.graph_position = Object.assign({}, window.graph_position); 

                // Set graph view drag handler
                graph_view.drag_handler = this.on_select; 
                
                // Start graph view scroll manager and pass handler 
                graph_view.scroll_manager.start(this.on_scroll, 20);

                this.last_event = event;
            },
            on_select (event) {
                this.set_rect(event);

                let cmp_rects = function(rect1, rect2){
                    return !(
                        ((rect1.y + rect1.height) < (rect2.y)) ||
                        (rect1.y > (rect2.y + rect2.height)) ||
                        ((rect1.x + rect1.width) < rect2.x) ||
                        (rect1.x > (rect2.x + rect2.width))
                    );
                }


                this.get_selectables().forEach(({info, rect}) => {
                    if (cmp_rects(this.rect, rect)) {
                        if (!graph_view.$refs[info.type+info.id].get_group()) graph_view.$refs[info.type+info.id].selected = true;
                    } else {
                        graph_view.$refs[info.type+info.id].selected = false;
                    }
                });

                this.last_event = event;
            },
            end_select () {
                this.rect.x = -Number.MAX_SAFE_INTEGER;
                this.rect.y  = -Number.MAX_SAFE_INTEGER;
                this.ds = Object.assign({}, {x: 0, y: 0});
                graph_view.cursor_state = Object.assign({}, {type: 'default'});

                // Stop scroll handler
                graph_view.scroll_manager.stop();

                this.selectables = null;
            },
            on_scroll () { 
                // If drag hasnt occured yet - no change in position
                if (this.rect.x === this.rect.y) return;


                let mouse = { x: this.last_event.pageX - window.graph_position.x, y: this.last_event.pageY - window.graph_position.y };
                if (mouse.x < 20) {
                    this.ds.x += 5;
                    graph_view.move_camera_by(5, 0);
                }
                if (mouse.x > window.graph_position.width - 20 ) {
                    this.ds.x += -5;
                    graph_view.move_camera_by(-5, 0);
                }
                if (mouse.y < 20) {
                    this.ds.y += 5;
                    graph_view.move_camera_by(0, 5);
                }
                if (mouse.y > window.graph_position.height - 20 ) {
                    this.ds.y += -5;
                    graph_view.move_camera_by(0, -5);
                }
                this.set_rect(this.last_event);
            },
            set_rect(event) {
                this.rect.width  = Math.abs(event.clientX - this.ds.x - this.init_pointer.x);
                this.rect.height = Math.abs(event.clientY - this.ds.y - this.init_pointer.y);

                let calcLeft, calcTop;
                if (event.clientX > this.init_pointer.x + this.ds.x) {
                    calcLeft = this.init_pointer.x - graph_position.x + this.ds.x;
                } else {
                    calcLeft = this.init_pointer.x - graph_position.x + this.ds.x - this.rect.width; 
                };

                if (event.clientY > this.init_pointer.y + this.ds.y) {
                    calcTop = this.init_pointer.y - graph_position.y + this.ds.y;
                } else {
                    calcTop = this.init_pointer.y - graph_position.y + this.ds.y - this.rect.height; 
                }

                this.rect.x = calcLeft;
                this.rect.y = calcTop;
            },

            get_selectables () {
                if(!this.selectables) {
                    const items = graph_view.items.map(item => {
                        let info = {type: 'i', id: item.id};
                        let rect = graph_view.inverse_transformation(info);
                        return {info,rect};
                    });
                    const groups = graph_view.groups.map(item => {
                        let info = {type: 'g', id: item.id};
                        let rect = graph_view.inverse_transformation(info);
                        return {info,rect};
                    });

                    this.selectables = [...items, ...groups];
                }
                return this.selectables;
            }
        }, 
        computed: {
            transformation () {
                return {
                    top: `${this.rect.y}px`,
                    left:  `${this.rect.x}px`,
                    height:`${this.rect.height}px`,
                    width:`${this.rect.width}px`,
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