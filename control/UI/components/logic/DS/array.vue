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
        mixins: [APIShift.API.getMixin('graph/line_parent')],
        data () {
            return {
                drawer: null,
                is_edit_mode: {
                    name: false,
                    entry: false
                },
                edit_name_width: 0,
                edit_entry_width: 0,
                header_min_width: 0
            }
        },
        created () {
            window.graph_elements[this.$props.index] = this;
        }, 
        mounted () {
            this.expanded_functions.drag_start = this.drag_start_addition;
            this.expanded_functions.drag = this.drag_addition;
            this.expanded_functions.drag_end = this.drag_end_addition;
            graph_view.elements_loaded++;

            if(graph_view.first_load) {
                this.all_loaded();
                graph_view.bring_to_front(this.$props.index);
            }
        },
        methods: {
            all_loaded: function() { 
                let to_index = this.$props.data.to !== undefined ?
                    graph_view.elements.findIndex((elem) => elem.id === this.$props.data.to.id && elem.component_id === this.$props.data.to.component_id) : -1;              

                if (to_index != -1){
                    // Draw lines to elements
                    setTimeout(() => {
                        this.to_line_index = this.create_line(to_index ,{
                                is_curvy: true,
                                is_stroked: false,
                                is_interactive: true
                        }, true);
                    });
                }


            },
            drag_start_addition: function(event) {

            },
            drag_addition(event) {

            },
            drag_end_addition(event) {

            },
            on_delete() {
                let my_id = graph_view.elements[this.$props.index].id;

                // Reset enum sizes
                if (this.attached_enum != -1) {
                    window.graph_elements[this.attached_enum].data.types = window.graph_elements[this.attached_enum].data.types.filter(id => id !== my_id);
                    window.graph_elements[this.attached_enum].reset_enum_sizes();
                    window.graph_elements[this.attached_enum].reset_type_position();
                }

                // Removing element from screen
                graph_view.$set(graph_view.elements[this.$props.index], 'is_deleted', true);
            },
            on_context_addition () {
                graph_view.context_menu.actions = [
                    {
                        starter: () => {
                            this.is_edit_mode = true;
                            graph_view.context_menu.is_active = false;
                        },
                        name: 'Edit',
                        icon: 'mdi-pencil',
                    },
                    {
                        starter: () => {
                            graph_view.context_menu.is_active = false;
                        },
                        name: 'Duplicate',
                        icon: 'mdi-content-duplicate',
                    },
                    {
                        starter: () => {
                            this.on_delete();
                            graph_view.context_menu.is_active = false;
                        },
                        name: 'Delete',
                        icon: 'mdi-delete-outline',
                    },
                ]
            },
            replace_connected_expanded(current_index, replace_index) {
                window.graph_elements[replace_index].expanded_functions.drag_end = this.on_point_drag_end;
            },
            on_point_drag_end () {
                let point_index = this.point_indices[this.point_indices.length - 1];

                let target_element = -1, z_index = 0;

                for(let index in [...graph_view.elements.keys()]) {
                    let cmp_id = graph_view.elements[index].component_id;
                    // Skip non-connections
                    if(window.graph_elements[index] === undefined ||  (cmp_id != 2 && cmp_id != 3 && cmp_id != 4) || graph_view.elements[index].is_deleted)
                        continue;
                    
                    // Check collisions
                    if (window.graph_elements[index].data.z_index > z_index && graph_view.collision_check(graph_elements[point_index].get_rect, window.graph_elements[index].get_rect)) {
                        target_element = index;
                        z_index = graph_view.elements[index].data.z_index;
                    }
                }
                
                // Drop on a connectable element
                let res = window.graph_elements[target_element]?.on_point_drop(point_index);
                if (res)
                {
                    graph_view.$set(graph_view.elements[point_index], 'is_deleted', true);
                }
                // graph_view.$set(graph_view.elements[point_index], 'is_deleted', true);
            },
        },
        watch: {
            'is_edit_mode.name' (is_focused) {
                if (is_focused) {
                    requestAnimationFrame(() =>  {
                        this.edit_name_width = this.$el.querySelector('#name').offsetWidth;
                        this.$el.querySelector('#name-input').focus();
                    });
                }
                
            },
            'is_edit_mode.entry' (is_focused) {
                if (is_focused) {
                    requestAnimationFrame(() =>  {
                        this.edit_entry_width = this.$el.querySelector('#entry').offsetWidth;
                        this.$el.querySelector('#entry-input').focus();
                    });
                }
            }
        },
        computed: {
            from_position: function() {
                return {
                    x: this.$props.data.position.x + this.get_rect.width,
                    y: this.$props.data.position.y + this.get_rect.height / 2
                };
            },
            to_position: function() {
                return {
                    x: this.$props.data.position.x,
                    y: this.$props.data.position.y + this.get_rect.height / 2
                };
            }
        },
    }
</script>

<template>
    <div class="array" style="overflow:hidden" color="#8789ff" :class="{  }"
        :style="transformation" 
        @pointerdown.prevent="drag_start"
        @contextmenu.prevent="on_context"
        @pointerup.prevent="drag_end">
        <div id="header" :style="{minWidth: header_min_width + 'px'}"
                @input="on_input">
                <v-avatar size="25" left class="avatar darken-4">A</v-avatar>

                <!-- Array name -->
                <v-text-field
                    id="name-input"
                    v-model="name"
                    v-show="is_edit_mode.name"
                    @blur="is_edit_mode.name = false"
                    :style="{'width': edit_name_width + 'px', marginLeft: '10px'}"
                    @input="requestAnimationFrame(() => edit_name_width = $el.querySelector('#name').offsetWidth)">
                </v-text-field>
                <b 
                    @dblclick.prevent="is_edit_mode.name = true"
                    :style="{ marginLeft: '10px' }"
                    v-show="!is_edit_mode.name">
                    {{name}}
                </b>
                <span id="name" style="position: absolute; top: -120%;">{{name}}</span>

                &nbsp;
                <!-- Entry name -->
                <b>['&nbsp;</b>
                    <v-text-field
                        id="entry-input"
                        v-model="data.entry"
                        v-show="is_edit_mode.entry"
                        @blur="is_edit_mode.entry = false"
                        class="nopadding"
                        :style="{'width': edit_entry_width + 'px'}"
                        @input="requestAnimationFrame(() => edit_entry_width = $el.querySelector('#entry').offsetWidth)">
                    </v-text-field>
                    <span 
                        v-show="!is_edit_mode.entry"
                        @dblclick.prevent="is_edit_mode.entry = true;">
                        {{data.entry}}
                    </span>
                <b>&nbsp;']</b>
                <span id="entry" style="position: absolute; top: -120%;">{{data.entry}}</span>

        </div>
    </div>
</template>

<style scoped>
/* Please style this crap, with style */
.avatar{
    background-color: #083D77;
}

#header {
    display: flex;
    flex-direction: row;
    border-bottom: 2px solid rgba(	100, 100, 220, 0.7);
    padding-bottom: 1.5px;
}

#content {
    display: flex;
    flex-direction: row; 
}

.array {
    position: absolute;
    cursor: copy ;
    background: #8789ff;
    box-shadow: 50px 50px 50px rgba(255, 242, 94, 0); /* Removing weird trace on chrome */
    border-radius: 2px;
    padding: 5px;
}

.type_over_enum {
    opacity: 0.7;
}


.v-messages {
    min-height: 0px
}

.v-text-field {
    padding-top: 0px; 
    margin-top: 0px;
}
.v-text-field input {
    padding: 0;
}
.v-text-field__details {
    min-height: 0px;
}
.v-input__control {
    flex-direction: column;
}
.v-input__slot {
    margin: 0;
}

.v-text-field .v-input__append-inner {
    margin: 0;
}

.v-text-field .v-input__append-inner {
    padding-left: 0px;
}
.v-input__append-inner {
    position: absolute;
}

.v-text-field input {
    padding: 0;
    line-height: inherit;
}
/* .nopadding.v-text-field input {
    padding: 0;
} */

</style>