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
        mixins: [APIShift.API.getMixin('orm/graph_element')],
        data () {
            return {
                base_height: 0,
                base_width: 0,
                selected: false,
                inner_elements: [] // Represents contained element objects
            }
        },
        created () {
            const self = this;
            this.expanded_functions.drag_start = (event) => {

            };
            this.expanded_functions.drag = (event) => {

            };
            this.expanded_functions.drag_end = (event) => {

            };
        },
        mounted () {
            // Determine initial rect pre bounding setup
            let rect = this.$el.getBoundingClientRect();
            this.base_height = rect.height;
            this.base_width = rect.width;

            // Determine new calculated rect & bounds 
            this.set_elements();
            this.set_rect();
            
            // Watch for changes 
            this.$watch('inner_elements', this.set_rect, {deep: true});
        },
        methods: {
            set_elements () {
                this.inner_elements = [];
                // Set inner elements rect's and watch them later on using vue reactivity 
                this.$props.data.contained_elements.forEach((element) => {
                    if (element.type === 'i') this.inner_elements.push(graph_view.items.find((item) => item.id === element.id));
                    if (element.type === 'g') this.inner_elements.push(graph_view.groups.find((group) => group.id === element.id));
                });

                // Delete groups of less than 1 elements 
                // if(this.inner_elements.length <= 1) this.on_delete();
            }, 
            set_rect () {
                // Determine bounds 
                let min_x = Math.min(...this.inner_elements.map((element) => element.rect.x));
                let min_y = Math.min(...this.inner_elements.map((element) => element.rect.y));
                let max_x = Math.max(...this.inner_elements.map((element) => element.rect.x + element.rect.width));
                let max_y = Math.max(...this.inner_elements.map((element) => element.rect.y + element.rect.height));


                // Update rect data 
                this.$props.rect.x = min_x;
                this.$props.rect.y = min_y;
                this.$props.rect.width = max_x - min_x + this.base_width;
                this.$props.rect.height = max_y - min_y + this.base_height;
            },
            on_context () {
                
            },
            move_by (dx, dy) {
                this.$props.data.contained_elements.forEach((element) => {
                    let uid = element.type + element.id;
                    graph_view.$refs[uid].move_by(dx, dy);
                });
            },
            on_delete () {
                let id = this.component_id;

                // Delete element from the graph
                graph_view.groups = graph_view.groups.filter((group) => group.id !== id);
            },
        }, 
        computed: {
            transformation () {
                return {
                    transform: `translate(${this.$props.rect.x}px,${this.$props.rect.y}px)`,
                    'z-index': 1
                }
            }
        }
    }
</script>

<template>
    <div class="group" color="#8789ff"
        :style="transformation" :class="{ selected }"
        @pointerdown.prevent="drag_start"
        @contextmenu.prevent="on_context"
        @pointerup.prevent="drag_end"
        >
        
        <div class="group_info">
            <v-avatar left class="group_type darken-4 green">G</v-avatar>
            <div style="display: inline;">{{ name }}</div>
        </div>
        
        <div class="group_container" 
        :style="{'height': `${rect.height - base_height }px`, 'width': `${rect.width - base_width}px`}">

        </div>
    </div>
</template>

<style scoped>
/* Please style this crap, with style */

.group_type {
    text-align: center;
    display: inline;
    padding-left: 7px;
    padding-right: 7px;
}

.group {
    border: solid white 1px;
    border-radius: 10px;
    display: flex;
    flex-direction: column-reverse;
    position: absolute;
    cursor: copy ;
    box-shadow: 50px 50px 50px rgba(255, 242, 94, 0); /* Removing weird trace on chrome */
}

.group_info {
    margin-top: 1px;
    background: #8789ff;
    border-bottom-left-radius: 9px;
    border-bottom-right-radius: 9px;
    padding-right: 5px;
    padding-left: 5px;
}
.group_container {
    pointer-events: none;
    opacity: 0;
}

.group.selected {
    outline: dashed white 2px;
}

.group.highlight {

}
</style>