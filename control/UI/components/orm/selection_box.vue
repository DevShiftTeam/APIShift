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
            rect: Object
        },
        data () {
            return {
                rect: {
                    x:  -Number.MAX_SAFE_INTEGER,
                    y:  -Number.MAX_SAFE_INTEGER,
                    width: 0,
                    height: 0
                },
                selectables: null, 
                ds: { x: 0, y: 0 },
                last_event: null,
                graph_rect: { x: 0, y: 0 }
            }
        },
        created () {
            graph_view['selection_box'] = this;
        },
        mounted () {

        },
        methods: {
            start_select (event) {
                this.init_pointer   = Object.assign({}, { x: event.clientX, y: event.clientY }); 
                this.graph_position = document.getElementById('graph_view').getBoundingClientRect();

                // Set graph view drag handler
                graph_view.drag_handler = this.on_select; 
                
                window.mouse_on_graph

                // Start graph view scroll manager and pass handler 
                // graph_view.scroll_manager.start(this.on_scroll, 20);

                this.last_event = event;
            },
            on_select (event) {
                this.$props.rect.width  = Math.abs(event.clientX - this.ds.x - this.init_pointer.x);
                this.$props.rect.height = Math.abs(event.clientY - this.ds.y - this.init_pointer.y);

                // Calcultate left-most position
                this.$props.rect.x = event.clientX > this.init_pointer.x + this.ds.x ? 
                    this.init_pointer.x - this.graph_position.x + this.ds.x 
                    : 
                    this.init_pointer.x - this.graph_position.x + this.ds.x - this.$props.rect.width;

                // Calculate top-most position
                this.$props.rect.y = event.clientY > this.init_pointer.y + this.ds.y ? 
                    this.init_pointer.y - this.graph_position.y + this.ds.y 
                    : 
                    this.init_pointer.y - this.graph_position.y + this.ds.y - this.$props.rect.height;

                let my_rect = this.$el.getBoundingClientRect();
                for (const index in graph_view.elements) {
                    let element = window.graph_elements[index];
                    if ((element.component_id != 0 || element.component_id != 1 || element.component_id != 4) && element.is_deleted) continue;

                    let el_rect = element.$el.getBoundingClientRect();     

                    if (graph_view.elements[index].component_id === 4) {
                        let group_rect = {
                            x: graph_view.elements[index].data.position.x,
                            y: graph_view.elements[index].data.position.y + element.get_rect().height - element.init_height,
                            height: element.init_height,
                            width: element.get_rect().width
                        };                     
                        if (graph_view.collision_check(my_rect,group_rect))
                            element.selected = true;
                        else
                            element.selected = false;
                    }
                }

            },
            end_select () {
                this.$props.rect.x = -Number.MAX_SAFE_INTEGER;
                this.$props.rect.y  = -Number.MAX_SAFE_INTEGER;
                this.ds = Object.assign({}, {x: 0, y: 0});
                graph_view.cursor_state = Object.assign({}, {type: 'default'});

                for (const index in graph_view.elements) {
                    let element = window.graph_elements[index];
                    if (!element.is_selected) continue;

                    let el_rect = element.$el.getBoundingClientRect();                    
                    if (!element.is_selected) {
                        element.selected = true;
                    } else { 
                        element.selected = false;
                    }
                }
                
                // Stop scroll handler
                graph_view.scroll_manager.stop();

                this.selectables = null;
            },
            on_scroll () { 
                // If drag hasnt occured yet - no change in position
                if (this.$props.rect.x === this.$props.rect.y) return;

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
            },

            get_selectables () {
                if(!this.selectables) {
                    const items = graph_view.elements.filter(item => {
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
                    top: `${this.$props.rect.y}px`,
                    left:  `${this.$props.rect.x }px`,
                    height:`${this.$props.rect.height}px`,
                    width:`${this.$props.rect.width}px`,
                    'z-index':  1000
                }
            }
        },
    }
</script>

<template>
    <div class="s_box" color="#8789ff"
        :style="transformation"
        @pointerup="end_select"> 
        <div class="wrapper"></div>
    </div>
    
</template>

<style scoped>
/* Please style this crap, with style */

.s_box {
    position: absolute;
    transform-origin: 0 0;
    border: dotted white 1px;
    background: rgba(255,255,255,0.1);
}
.wrapper {
    position: absolute;
    height: 120%;
    width: 120%;
    opacity: 0;
}
</style>