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
            // rect: Object,
            data: Object,
            select_end_addition: Function
        },
        data () {
            return {
                rect: {
                    x:  0,
                    y:  0,
                    width: 0,
                    height: 0
                },
                last_event: {},
            }
        },
        created () {
            window.selection_box = this;
        },
        methods: {
            start_select (event) {
                // Set graph view drag handler
                graph_view.drag_handler = this.on_select; 
                
                // Start graph view scroll manager and pass handler 
                if(!graph_view.scroll_manager.is_running()) graph_view.scroll_manager.start(this.on_scroll, 20);

                window.addEventListener('pointerup', this.end_select);
                window.addEventListener('pointercancel', this.end_select);
                
            },
            on_select (event) {
                // Calculate rect boundries
                this.set_rect(event);

                // Memorize event
                this.last_event = event;

                // Set flag
                graph_view.selection_active = true;   

            },
            end_select () {
                graph_view.cursor_state = Object.assign({}, {type: 'default'});

                let selected_indices = [];
                for (const index in graph_view.elements) {
                    let element = window.graph_elements[index];
                    if (!element.is_selected) continue
                    selected_indices.push(index);
                }

                let accumulated_group_index = 0;
                for (const index of selected_indices) {
                    accumulated_group_index += graph_elements[index].parent_group_index || 0 + graph_elements[index].group_index || 0;
                }

                if(selected_indices.length > 0) {
                    let common_group_index = (graph_elements[selected_indices[0]].group_index || 0) + (graph_elements[selected_indices[0]].parent_group_index || 0);
                    let is_same_group = accumulated_group_index / selected_indices.length == common_group_index;
                    if (is_same_group) {
                        let last_id = -1, id_list = selected_indices.map(index => graph_view.elements[index].id);
                        for(let index in graph_view.elements) {
                            let el = graph_view.elements[index];
                            if((el.component_id == 1 || el.component_id == 0 || el.component_id == 4) && last_id < el.id)
                                last_id = el.id;
                        };
                        
                        for (let grp_index of selected_indices) {
                            if (graph_view.elements[grp_index].component_id == 4) graph_view.elements[grp_index].data.parent = last_id + 1;
                        }

                        graph_view.elements.push({
                            id: last_id + 1, component_id: 4, name: "Group", data: {
                                position: window.mouse_on_graph,
                                elements: id_list,
                                parent: common_group_index != -1 ? graph_view.elements[common_group_index].id : 0,
                                z_index: graph_view.elements.length + 1
                            }
                        });
                    }
                }

                // Stop scroll handler
                graph_view.scroll_manager.stop();

                // Deselect all selected elements
                for(let index of selected_indices) {     
                    window.graph_elements[index].is_selected = false;
                }

                // Fold selection box  
                window.removeEventListener('pointerup', this.end_select);
                window.removeEventListener('pointercancel', this.end_select);           
                graph_view.selection_active = false;   
            },
            on_scroll () { 
                // Irrelevant pre drag
                if (!graph_view.selection_active) return;

                if (this.last_event.clientX - window.graph_position.x < 20) {
                    window.init_pointer.x += 5;
                    graph_view.move_camera_by(5, 0);
                }
                if (this.last_event.clientX - window.graph_position.x > window.graph_position.width - 20 ) {
                    window.init_pointer.x -= 5;
                    graph_view.move_camera_by(-5, 0);
                }
                if (this.last_event.clientY - window.graph_position.y < 20) {
                    window.init_pointer.y += 5;
                    graph_view.move_camera_by(0, 5);
                }
                if (this.last_event.clientY - window.graph_position.y > window.graph_position.height - 20 ) {
                    window.init_pointer.y -= 5;
                    graph_view.move_camera_by(0, -5);
                }

                this.set_rect(this.last_event);
            },
            set_rect(event) {
                this.rect.width  = Math.abs(event.clientX - window.init_pointer.x);
                this.rect.height = Math.abs(event.clientY - window.init_pointer.y);

                // Calcultate left-most position
                this.rect.x = event.clientX > window.init_pointer.x ? 
                    window.init_pointer.x - window.graph_position.x
                    : 
                    window.init_pointer.x - window.graph_position.x - this.rect.width;

                // Calculate top-most position
                this.rect.y =  event.clientY > window.init_pointer.y ? 
                    window.init_pointer.y - window.graph_position.y
                    : 
                    window.init_pointer.y - window.graph_position.y - this.rect.height;

                // Show element
                let my_rect = this.$el.getBoundingClientRect(), grp_indices = [];
                for (const index in graph_view.elements) {
                    let element = graph_view.elements[index];

                    if (!(element.component_id == 0 || element.component_id == 1 || element.component_id == 4) || element.is_deleted) continue;

                    let el_rect = window.graph_elements[index].$el.getBoundingClientRect();     
                    if (element.component_id === 4) {
                        let group_rect = window.graph_elements[index].$el.querySelector('#group_info').getBoundingClientRect();
                        if (graph_view.collision_check(my_rect,group_rect)) {
                            window.graph_elements[index].is_selected  = true;
                            for (let grp_index of window.graph_elements[index].group_indices) {
                                grp_indices.push(grp_index);
                            }
                            for (let el_index of window.graph_elements[index].element_indices) {
                                window.graph_elements[el_index].is_selected = false;
                            }
                        }
                        else
                            window.graph_elements[index].is_selected  = false;
                    }
                    else if (graph_view.collision_check(my_rect,el_rect)) 
                        window.graph_elements[index].is_selected  = true;
                    else 
                        window.graph_elements[index].is_selected  = false;
                }

                // Deselected nested groups in selected groups
                for (const grp_index of grp_indices) {
                    window.graph_elements[grp_index].is_selected  = false;
                }
            },
        }, 
        computed: {
            transformation () {
                return {
                    top: `${this.rect.y}px`,
                    left:  `${this.rect.x}px`,
                    height:`${this.rect.height}px`,
                    width:`${this.rect.width}px`,
                    'z-index':  graph_view.elements.length + 1
                }
            }
        },
    }
</script>

<template>
    <div class="s_box" color="#8789ff" :style="transformation"
        @pointerup.prevent="end_select"> 
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

    height: 100vh;
    width: 100vw;
    opacity: 0;
}
</style>