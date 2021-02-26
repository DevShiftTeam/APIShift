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
        props: {
            name: String,
        },
        data () {
            return {
                drawer: null,
            }
        },
        created () {   
            var id = this.component_id;
            var component_info = this.component_info;

            // If contained in enum, remove from enum and null the corresponding data 
            this.expanded_functions.drag_start = (event) => {
                this.remove_from_enum();
            };
            this.expanded_functions.drag = (event) => {

            };

            // If hits Enum element, add it to Enum
            this.expanded_functions.drag_end = (event) => {
                const hitting_enum = graph_view.enums.find(e => graph_view.hittest({type:'e', id: e.id}, component_info));
                // Enum element has been hitted
                if (hitting_enum) {
                    // Type is not contained - add it to enum
                    if (!hitting_enum.data.types.find(t => t.id === id)) {
                        hitting_enum.data.types.push({ id });
                        this.$props.data.enum_id = hitting_enum.id;
                    }
                }
            };
        }, 
        mounted () {
            this.z_index = 11;
        },
        methods: {
            render_needed () {
            },
            on_context() {

            },
            remove_from_enum () {
                let id = this.component_id;
                if(this.$props.data.enum_id) {
                    let enum_info = {type: 'e', id: this.$props.data.enum_id};
                    let element_enum = graph_view.get_element_by_info(enum_info);
                    element_enum.data.types = element_enum.data.types.filter(t => t.id !== id);
                    this.$props.data.enum_id = null;
                }
            },
            on_delete() {
                let id = this.component_id;

                // Detach type from enum 
                if(this.$props.data.enum_id) this.remove_from_enum();
                
                // Finally remove element from screen
                graph_view.enum_types = graph_view.enum_types.filter((enum_type) => enum_type.id !== id);
                delete graph_view.lookup_table['t'][id];
            },
            move_to (xpos, ypos) {
                this.$props.position.x = xpos;
                this.$props.position.y = ypos;
            }
        },
        computed: {
            enum_parent () {
                var enum_id = this.$props.data.enum_id;
                return graph_view.enums.find((enum_) => enum_.id === enum_id);
            }
        }
    }
</script>

<template>
    <div class="type" color="#8789ff" :class="{ ghost_mode }"
        :style="transformation" 
        @pointerdown.prevent="drag_start"
        @contextmenu.prevent="on_context"
        @pointerup.prevent="drag_end">
            <v-avatar left class="type_type darken-4 grey">T</v-avatar>
            <div style="display: inline;">{{ name }}</div>
    </div>
</template>

<style scoped>
/* Please style this crap, with style */
.type_type {
    text-align: center;
    display: inline;
    padding-left: 7px;
    padding-right: 7px;
}

.type {
    border: solid white 1px;
    border-radius: 10px;
    padding: 5px;
    display: inline-block;
    position: absolute;
    cursor: copy ;
    background: #8789ff;
    box-shadow: 50px 50px 50px rgba(255, 242, 94, 0); /* Removing weird trace on chrome */
}
.type.ghost_mode {
    opacity: 0.7;
}

</style>