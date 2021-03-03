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
        mixins: [APIShift.API.getMixin('orm/graph_element')],
        data () {
            return {
                drawer: null,
                group_container: null,
                selected: false,
                element_sizes: {}
            }
        },
        created () {
            window.graph_elements[this.$props.index] = this;
        }, 
        mounted () {
            let rect = this.$el.getBoundingClientRect();
            this.element_sizes = {
                width: rect.width,
                height: rect.height
            };
        },
        methods: {
            get_enums () {
                if (!this.enums) this.enums = graph_view.enums.filter(e => e.data.connected.find(connected => connected.type + connected.id === this.uid));
                return this.enums;
            },
            on_delete() {
                // Delete lines from the graph & connected relations recursivly
                this.get_lines().forEach(line => {
                    let to_instance = graph_view.$refs[line.dest_info.type + line.dest_info.id];
                    let from_instance = graph_view.$refs[line.src_info.type + line.src_info.id];
                    
                    graph_view.delete_line(line.src_info, line.dest_info);

                    if (line.settings.item_to_relation && !this.is_relation) {
                        let relation = to_instance;
                        relation.on_delete();
                    } else if (line.settings.relation_to_item && !this.is_relation) {
                        let relation = from_instance;
                        relation.on_delete(); 
                    }
                });

                // Remove item connection from enum
                this.get_enums().forEach(e => {    
                        e.data.connected = e.data.connected.filter( connected => connected.type + connected.id !== this.uid);
                    }
                );
                
                // Delete element from the graph
                let id = this.component_id;
                graph_view.items = graph_view.items.filter((item) => item.id !== id);
                delete graph_view.lookup_table['i'][id];

                // Remove element from group and recalculate group boundries if exists
                if (this.get_group()) {
                    let group_instance = graph_view.$refs['g' + this.get_group().id];
                    this.get_group().data.contained_elements = this.get_group().data.contained_elements.filter((element) => element.id !== id);
                }
            }
        },
        computed: {
            from_position: function() {
                return {
                    x: this.$props.data.position.x + this.element_sizes.width,
                    y: this.$props.data.position.y + this.element_sizes.height / 2
                };
            },
            to_position: function() {
                return {
                    x: this.$props.data.position.x,
                    y: this.$props.data.position.y + this.element_sizes.height / 2
                };
            }
        }
    }
</script>

<template>
    <div class="item" :class="{ selected , ghost_mode }" color="#8789ff"
        :style="transformation"
        @pointerdown.prevent="drag_start"
        @contextmenu.prevent="on_context"
        @pointerup.prevent="drag_end">
            <v-avatar left class="item_type darken-4 blue">I</v-avatar>
            <div style="display: inline;">{{ name }}</div>
    </div>
</template>

<style scoped>
/* Please style this crap, with style */
.item_type {
    text-align: center;
    display: inline;
    padding-left: 7px;
    padding-right: 7px;
}

.item {
    border: solid white 1px;
    border-radius: 10px;
    padding: 5px;
    display: inline-block;
    position: absolute;
    cursor: copy ;
    background: #8789ff;
    box-shadow: 50px 50px 50px rgba(255, 242, 94, 0); /* Removing weird trace on chrome */
}

.item.selected {
    border: dashed white 2px;
    padding: 4px;
}
.type.ghost_mode {
    opacity: 0.7;
}
</style>