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
                base_height: 0,
                base_width: 0,
                types: null,
            }
        },
        created () {
            var id = this.component_id;
            var type = this.component_type;
            var start_pos = { x: 0, y: 0};

            this.expanded_functions.drag_start = (event) => {
                start_pos = Object.assign({},this.$props.rect);
            };
            this.expanded_functions.drag = (event) => {
            };
            this.expanded_functions.drag_end = (event) => {
                const enum_info = { id, type };
                const items_infos = graph_view.items.map((i => { return { id: i.id , type: 'i' }}));
                const groups_infos = graph_view.groups.map((i => { return { id: i.id , type: 'g' }}));

                const elements_infos = [...items_infos, ...groups_infos];
                for (const element_info of elements_infos) {
                    if (graph_view.hittest(enum_info, element_info)) {

                        // Element already connected 
                        if (this.$props.data.connected.find ((element) => element.id + element.type === element_info.id + element_info.type)) return;

                        // Create enum - item connection
                        this.$props.data.connected.push(element_info); 
                        graph_view.create_line(this.component_info, element_info, {enum_to_item: element_info.type === 'i', enum_to_group: element_info.type === 'g'});

                        // Move to back origin place 
                        this.$props.rect.x = start_pos.x;
                        this.$props.rect.y = start_pos.y;
                        break;
                    }
                }

            };
        }, 
        mounted () {
            let id = this.component_id;
            let type = this.component_type;
            
            this.base_height = this.$el.offsetHeight;
            this.base_width = this.$el.offsetWidth;

            // Align enum types to element
            this.align_types();

            // Mount lines to connected elements 
            this.$props.data.connected.forEach((element) => {
                graph_view.create_line({id, type}, {id: element.id, type: element.type}, {enum_to_item: true});
            });

            // General z_index for Enum's
            this.z_index = 10;
        },
        methods: {
            align_types (set_rect = false) {
                var accumulated_height = this.base_height;
                var accumulated_width = this.base_width;

                // Move attached types
                for (const type of this.get_types()) {
                    accumulated_width = Math.max(accumulated_width, type.rect.width + 14 /* Padding */); 
                    type.rect.x = this.x_pos + (accumulated_width - type.rect.width) / 2;
                    type.rect.y = this.$props.rect.y + accumulated_height;
                    accumulated_height += type.rect.height;
                }
                this.$props.rect.height = accumulated_height + 7 /* Padding */;
                this.$props.rect.width = accumulated_width; 
            },

            get_types() {
                // Get type list from data and cache it for next usage
                if (!this.types) this.types = this.$props.data.types.map((type_info) => graph_view.enum_types.find(t => t.id === type_info.id));
                return this.types;
            },
            on_delete() {
                let id = this.component_id;

                // Delete connected lines 
                this.get_lines().forEach(line => {
                    graph_view.delete_line(line.src_info, line.dest_info);
                });

                // Detach connected types 
                for (const type of this.get_types()) {
                    type.data.enum_id = null;
                }

                // Finnaly remove element from screen
                graph_view.enums = graph_view.enums.filter((enums) => enums.id !== id);
                delete graph_view.lookup_table['e'][id];
            },
            render_needed () {
            }
            
        },
        computed: {

        },
        watch: {
            '$props.rect.x': function() {
                this.align_types();
            },
            '$props.rect.y': function() {
                this.align_types();
            },
            '$props.data.types': function() {
                this.types = null;
                this.align_types();
            }
        }
    }
</script>

<template>
    <div class="enum" color="#8789ff" :class="{ ghost_mode }"
        :style="transformation"
        @pointerdown.prevent="drag_start"
        @contextmenu.prevent="on_context"
        @pointerup.prevent="drag_end">
        <v-row style="padding-left: 7px; padding-right: 7px;">
            <v-col >
                <v-avatar left class="enum_type darken-4 red" >E</v-avatar>
                <div style="display: inline;">{{ name || 'N' }}</div>
            </v-col>
        </v-row>
        <div class="enum_types" :style="{'height': `${rect.height - base_height}px`, 'width': `${rect.width }px`}"></div>
    </div>
</template>

<style scoped>
/* Please style this crap, with style */
.enum_type {
    text-align: center;
    display: inline;
      justify-content: center;

    padding-left: 7px;
    padding-right: 7px;
}

.enum {
    border: solid white 1px;
    border-radius: 10px;
    display: flex;
    flex-direction: column;
    justify-content: center;
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