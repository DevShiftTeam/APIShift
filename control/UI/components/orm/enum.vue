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

    // This shit is made for scripting
    module.exports = {
        mixins: [APIShift.API.getMixin('orm/graph_element')],
        props: {
            name: String,
            data: Object
        },
        data () {
            return {
                drawer: null,
                enum_types: [],
                start_pos: {x: 0, y: 0},
                container_height: 0,
                container_width: 0
            }
        },
        created () {
            const self = this;
            this.expanded_functions.drag_start = (event) => {
                self.start_pos = Object.assign({},self.$props.position);
                self.update_types();
                self.align_types();    
            };
            this.expanded_functions.drag = (event) => {
                self.align_types();
            };
            this.expanded_functions.drag_end = (event) => {
                const item_elements = [ ...graph_view.$el.querySelectorAll('.item'),
                                        ...graph_view.$el.querySelectorAll('.relation')];

                for (const item_el of item_elements) {
                    if (graph_view.hittest(self.uid, item_el.ref)) {
                        // Create enum_item connection
                        let item_instance = graph_view.$refs[item_el.ref];
                        graph_view.item_enums.push({ enum_id: self.component_id, item_id: item_instance.component_id }); 
                        graph_view.create_line(self.component_id, item_instance.component_id, {enum_to_item: true});   
                        
                        // Move to back origin place 
                        self.$props.position.x = self.start_pos.x;
                        self.$props.position.y = self.start_pos.y;
                        self.update_types();
                        self.align_types();    
                        break;
                    }
                }

            };
        }, 
        mounted () {
            const self = this;

            this.z_index = 10;
            // Align attached types to element
            this.update_types();
            this.align_types();

            for (let index = 0; index < graph_view.item_enums.length; index++) {
                const item_enum = graph_view.item_enums[index];
                if (item_enum.enum_id === this.component_id) {
                    graph_view.create_line(item_enum.enum_id, item_enum.item_id, { enum_to_item: true });
                }
            }

            this.$watch('container_height', this.on_rect_change);
            this.$watch('container_width', this.on_rect_change);
        },
        methods: {
            attach_type (type_id) {
                this.enum_types = [];
                for (const enum_type of graph_view.enum_types) {
                    if (enum_type.id === type_id) {
                        enum_type.enum_id = this.component_id;
                    }
                }
                this.update_types();
                this.align_types();
            },
            detach_type (type_id) {
                this.enum_types = [];
                for (const enum_type of graph_view.enum_types) {
                    if (enum_type.id === type_id ) {
                        enum_type.enum_id = null;
                    }
                }
                this.update_types();
                this.align_types();
            },
            align_types () {
                const self = this;
                var accumulated_offset = 0;
                this.container_height = 0;
                this.container_width = 0;
                // Move attached types
                for (const enum_type_id of this.enum_types) {
                    const type_instance = graph_view.$refs['t'+enum_type_id];
                    accumulated_offset += 40;
                    self.container_width = Math.max(self.container_width, type_instance.$el.offsetWidth);
                    self.container_height = accumulated_offset;
                    type_instance.move_to(self.$props.position.x + 5 , self.$props.position.y + accumulated_offset);
                    type_instance.index = self.index + 1;
                }
            },
            update_types () {
                let self = this;
                this.enum_types = [];
                for (const enum_type of graph_view.enum_types) {
                    if (enum_type.enum_id === this.component_id) {
                        this.enum_types.push(enum_type.id);
                    }
                }
                
            },
            on_delete() {
                
            },
            on_rect_change () {
                requestAnimationFrame(this.update_lines);
            },
            render_needed () {
            }
        },
        watch: {

        }
    }
</script>

<template>
    <div class="enum" color="#8789ff" :class="{ ghost_mode }"
        :style="transformation"
        @pointerdown.prevent="drag_start"
        @pointerup.prevent="drag_end">
            <v-avatar left class="enum_type darken-4 red" >E</v-avatar>
            <div style="display: inline;">{{ uid }}</div>
            <div class="enum_types" :style="{'height': `${container_height + 5}px`,'width': `${container_width}px`}"></div>
    </div>
</template>

<style scoped>
/* Please style this crap, with style */
.enum_type {
    text-align: center;
    display: inline;
    padding-left: 7px;
    padding-right: 7px;
}

.enum {
    border: solid white 1px;
    border-radius: 10px;
    padding: 5px;
    display: inline-block;
    position: absolute;
    cursor: copy !important;
    background: #8789ff;
    box-shadow: 50px 50px 50px rgba(255, 242, 94, 0); /* Removing weird trace on chrome */
}

.enum.highlight {

}
.enum.ghost_mode {
    opacity: 0.7;
}
</style>