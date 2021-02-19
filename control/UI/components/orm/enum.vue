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
                container_width: 0,
                type_rects: []
            }
        },
        created () {
            var start_pos = { x: 0, y: 0};

            this.expanded_functions.drag_start = (event) => {
                start_pos = Object.assign({},this.$props.rect);
                console.log(start_pos);
                this.set_types();
                this.align_types();    
            };
            this.expanded_functions.drag = (event) => {
                this.align_types();
            };
            this.expanded_functions.drag_end = (event) => {
                const item_elements = [ ...graph_view.$el.querySelectorAll('.item'),
                                        ...graph_view.$el.querySelectorAll('.relation')];

                for (const item_el of item_elements) {
                    if (graph_view.hittest(this.uid, item_el.ref)) {
                        let item_instance = graph_view.$refs[item_el.ref];

                        // Element already connected 
                        if (this.$props.data.connected.find ((element) => element.id === item_instance.component_id)) return;

                        console.log('asdsa');
                        // Create enum - item connection
                        this.$props.data.connected.push({ type: 'i', id: item_instance.component_id}); 
                        graph_view.create_line({type: 'e', id: this.component_id}, { type: 'i', id: item_instance.component_id}, {enum_to_item: true});   
                        
                        // Move to back origin place 
                        this.$props.rect.x = start_pos.x;
                        this.$props.rect.y = start_pos.y;
                        this.align_types();    
                        break;
                    }
                }

            };
        }, 
        mounted () {
            let id = this.component_id;
            let type = this.component_type;

            // Align attached types to element
            this.set_types();
            this.align_types();

            // Mount lines to connected elements 
            this.$props.data.connected.forEach((element) => {
                graph_view.create_line({id, type}, {id: element.id, type: element.type}, {enum_to_item: true});
            });

            // Watch for width & height changes
            this.$watch('container_height', this.on_rect_change);
            this.$watch('container_width', this.on_rect_change);

            // General z_index for Enum's
            this.z_index = 10;
        },
        methods: {
            add_type (type_id) {
                this.$props.data.types.push({id: type_id});
                this.set_types();
                this.align_types();
            },
            remove_type (type_id) {
                console.log(this.$props.data.types, type_id);
                this.$props.data.types = this.$props.data.types.filter((type) => type.id !== type_id);
                console.log(this.$props.data.types, type_id);
                this.set_types();
                this.align_types();
            },
            align_types () {
                const self = this;
                var accumulated_offset = 0;
                this.container_height = 0;
                this.container_width = 0;

                // Move attached types
                for (const type_rect of this.type_rects) {
                    accumulated_offset += 40;
                    self.container_width = Math.max(self.container_width, type_rect.width);
                    self.container_height = accumulated_offset;
                    type_rect.x = self.x_pos + 5,
                    type_rect.y = self.y_pos + accumulated_offset;
                }
            },
            set_types () {
                this.type_rects = [];
                for (const type_obj of this.$props.data.types) {
                    this.type_rects.push(graph_view.enum_types.find((type) => type.id === type_obj.id)?.rect);
                }
            },
            on_delete() {
                console.log(this.component_id);
                let id = this.component_id;

                // Detach connected Items 
                this.get_lines().forEach(line => {
                    graph_view.delete_line(line.line_uid);
                });
                graph_view.item_enums = graph_view.item_enums.filter((item_enum) => item_enum.enum_id !== id);

                // Detach connected types 
                for (const enum_type of graph_view.enum_types) {
                    if (enum_type.enum_id === this.component_id) {
                        this.detach_type(enum_type.id, true);
                    }
                }

                // Finnaly remove element from screen
                graph_view.enums = graph_view.enums.filter((enums) => enums.id !== id);
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
    cursor: copy;
    background: #8789ff;
    box-shadow: 50px 50px 50px rgba(255, 242, 94, 0); /* Removing weird trace on chrome */
}

.enum.highlight {

}
.enum.ghost_mode {
    opacity: 0.7;
}
</style>