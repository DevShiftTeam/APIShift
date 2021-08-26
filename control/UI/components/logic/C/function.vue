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
                params: {},
                is_edit_mode: {
                    name: false,
                    entry: false
                },
                input_count: 0, // How many inputs are currently connected
                edit_name_width: 0,
                edit_entry_width: 0,
                header_min_width: 0,
                offsetTop: 0,
                dropped_param: null,
                first_load: true 
            }
        },
        created () {
            window.graph_elements[this.$props.index] = this;
            window.p = this;
        }, 
        mounted () {
            this.expanded_functions.drag_start = this.drag_start_addition;
            this.expanded_functions.drag = this.drag_addition;
            this.expanded_functions.drag_end = this.drag_end_addition;
            graph_view.elements_loaded++;

            let center = this.$el.getBoundingClientRect().y + this.$el.getBoundingClientRect().height / 2;
            let rows = this.$el.querySelector("#inputs > div:nth-child(1)").getBoundingClientRect().y + this.$el.querySelector("#inputs > div:nth-child(1)").getBoundingClientRect().height / 2;
            this.offsetTop = (rows - center) / graph_view.scale;
            
            if(graph_view.first_load) {
                this.all_loaded();
                graph_view.bring_to_front(this.$props.index);
            }
        },
        methods: {
            all_loaded: function() {
                let to_index = this.$props.data.to !== undefined ?
                    graph_view.elements.findIndex((elem) => elem.id === this.$props.data.to.id && elem.component_id === this.$props.data.to.component_id)
                    :
                    this.create_point(false); // Create & attach point near relation                


                // Draw lines to elements
                setTimeout(() => {
                    this.to_line_index = this.create_line(to_index,{
                            is_curvy: true,
                            is_stroked: false,
                    }, true);
                });
            },


            drag_start_addition: function(event) {
                
            },
            drag_addition(event) {

            },
            drag_end_addition(event) {

            },
            on_delete() {
                // Removing element from screen
                graph_view.$set(graph_view.elements[this.$props.index], 'is_deleted', true);
            },
            // Callback function called upon line connection - Note: called post change
            on_line_connected (line_index) {

                // Map connected line to element's parameters
                if (this.first_load && graph_view.lines[line_index].to_index == this.$props.index) {
                    this.line_connector_map[line_index] = (++this.input_count - 1);
                    // Acknoledge view
                    this.$props.data.params[this.input_count - 1].is_con = true;
                    graph_view.lines[line_index].data.offsetTopDest = this.input_count * 20 - 16;

                    this.first_load = this.input_count != this.$props.data.params.map(p => p.is_con).length; 
                }

                setTimeout(() => {
                    this.input_count = this.$props.data.params.filter(p => p.is_con).length;
                });

            },
            // Callback function called upon line disconnection - Note: called post-change
            on_line_disconnected (line_index) {
                if (graph_view.lines[line_index].from_index != this.$props.index && graph_view.lines[line_index].to_index != this.$props.index) {
                    graph_view.lines[line_index].data.offsetTopDest = 0;
                    this.$props.data.params[this.line_connector_map[line_index]].is_con = false;
                    delete this.line_connector_map[line_index];
                }  


                setTimeout(() => {
                    this.input_count = this.$props.data.params.filter(p => p.is_con).length;
                });

            },

            replace_connected_expanded(from_index, to_index) {
                
            },
            on_point_drop (point_index) {
                let point_rect = window.graph_elements[point_index].$el.getBoundingClientRect();
                let droppables = Array.from(this.$el.querySelectorAll(".connector"));

                for (const index in droppables) {
                    if (graph_view.collision_check(point_rect, droppables[index].getBoundingClientRect())) {

                        // Create connection on view 
                        if (!this.$props.data.params[index].is_con) {
                            let line_index = graph_view.elements[point_index].data.line_index;
                            graph_view.lines[line_index].data.offsetTopDest = index * 20 + 4;
                            graph_view.lines[line_index].to_index = this.$props.index;
                            this.line_connector_map[line_index] = parseInt(index);
                            this.$props.data.params[index].is_con = true;

                            return true;
                        }

                    }
                }
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
            }
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
            },
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
    <div class="function error-glow"  style="overflow:hidden" color="#8789ff" :class="{ 'border-error': input_count < $props.data.params.length }"
        :style="transformation" 
        @pointerdown.prevent="drag_start"
        @contextmenu.prevent="on_context"
        @pointerup.prevent="drag_end">
        <div id="header" :style="{minWidth: header_min_width + 'px'}"
                @input="on_input">
                <v-avatar size="25" left class="avatar darken-4">F</v-avatar>

                <!-- Function name -->
                <v-text-field
                    id="name-input"
                    v-model="name"
                    v-show="is_edit_mode.name"
                    @blur="is_edit_mode.name = false"
                    :style="{'width': edit_name_width + 'px', marginLeft: '10px'}"
                    @input="requestAnimationFrame(() => edit_name_width = $el.querySelector('#name').offsetWidth)">
                </v-text-field>
                <span 
                    @dblclick.prevent="is_edit_mode.name = true"
                    :style="{ marginLeft: '10px' }"
                    v-show="!is_edit_mode.name">
                    {{name}}
                </span>
                <span id="name" style="position: absolute; top: -120%;">{{name}}</span>
        </div>
        <div id="params">
            <v-row v-for="(param, index) in $props.data.params" :key="index">
                <div class="connector"></div>
                <span class="input-text">{{param}}</span>
            </v-row>
            <v-row>
            </v-row>
        </div>
    </div>
</template>

<style scoped>
/* Please style this crap, with style */
.avatar{
    background-color: #EE964B;
}

#header {
    display: flex;
    flex-direction: row;
    border-bottom: 2px solid rgba(	100, 100, 220, 0.7);
    padding-bottom: 1.5px;
}

#inputs {
    display: flex;
    flex-direction: column; 
    padding-right: 15px;
    padding-left: 15px;
}

.function {
    position: absolute;
    cursor: copy ;
    background: #8789ff;
    box-shadow: 50px 50px 50px rgba(255, 242, 94, 0); /* Removing weird trace on chrome */
    border-radius: 2px;
    padding: 5px;
    border-radius: 20px;
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

#inputs .connector {
  height: 10px;
  width: 10px;
  background-color: #bbb;
  border-radius: 50%;
  display: inline-block;
  margin-top: 4px;
  position: absolute;
  transition: all .2s ease-in-out;
}

#inputs .connector:hover {
    transform: scale(1.11);
} 

#inputs .input-text {
    line-height: 1.5;
    font-size: smaller;
    margin-left: 15px;
}

@keyframes blink {
    50% {
        box-shadow: 0 0 5px red;
    }
}

.border-error {
    border: 0.1px solid red;
    /* box-shadow: 0 0 0px red; */
    transition: box-shadow 0.5s linear;
    animation-name: blink ;
    animation-duration: .5s ;
    animation-timing-function: step-end ;
    animation-iteration-count: infinite ;
    animation-direction: alternate ;
}


/* .nopadding.v-text-field input {
    padding: 0;
} */

</style>