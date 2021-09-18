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
        mixins: [ APIShift.API.getMixin('graph/line_parent', true   )],
        data () {
            return {
                drawer: null,
                is_edit_mode: {
                    name: false,
                    entry: false
                },
                columns: [
                    'ID',
                    'USERNAME',
                    'PASSWORD',
                    'CREATED_AT'
                ],
                column: 'USERNAME',
                edit_name_width: 0,
                edit_entry_width: 0,
                header_min_width: 0
            }
        },
        created () {
            window.graph_elements[this.$props.index] = this;
        }, 
        mounted () {
            let target_index = function (self, target) {
                return graph_view.elements.findIndex(e => e.id == self.$props.data[target].id && e.component_id == self.$props.data[target].component_id);
            };
        
            this.expanded_functions.drag_start = this.drag_start_addition;
            this.expanded_functions.drag = this.drag_addition;
            this.expanded_functions.drag_end = this.drag_end_addition;
            graph_view.elements_loaded++;

            if(graph_view.first_load) {
                this.all_loaded();
                graph_view.bring_to_front(this.$props.index);
            };
        },
        methods: {
            all_loaded: function() { 
                // Step 1: Determine from and to & fill missing points
                let from_index = this.$props.data.from !== undefined ?
                    graph_view.elements.findIndex((elem) => elem.id == this.$props.data.from.id && elem.component_id == this.$props.data.from.component_id)
                    :
                    this.create_point(); // Create & attach point near relation

                let to_index = this.$props.data.to !== undefined ?
                    graph_view.elements.findIndex((elem) => elem.id == this.$props.data.to.id && elem.component_id == this.$props.data.to.component_id)
                    :
                    this.create_point(false); // Create & attach point near relation


                // Step 2: Position self in-between
                this.$props.data.position = {
                    x: (graph_view.elements[from_index].data.position.x + graph_view.elements[to_index].data.position.x) / 2,
                    y: (graph_view.elements[from_index].data.position.y + graph_view.elements[to_index].data.position.y) / 2 
                };
                

                // Draw lines to elements
                setTimeout(() => {
                    let from_offset, to_offset;

                    from_offset = 
                    this.from_line_index = this.create_line(from_index,{
                            is_curvy: true,
                            is_stroked: false,
                            is_persistent: true,
                    }, false);
                    
                    this.to_line_index = this.create_line(to_index,{
                            is_curvy: true,
                            is_stroked: false,
                            is_persistent: true,
                            arrow_head: "many-arrow-head2"
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
        watch: {
            // 'is_edit_mode.name' (is_focused) {
            //     if (is_focused) {
            //         requestAnimationFrame(() =>  {
            //             this.edit_name_width = this.$el.querySelector('#name').offsetWidth;
            //             this.$el.querySelector('#name-input').focus();
            //         });
            //     }
                
            // },
            // 'is_edit_mode.entry' (is_focused) {
            //     if (is_focused) {
            //         requestAnimationFrame(() =>  {
            //             this.edit_entry_width = this.$el.querySelector('#entry').offsetWidth;
            //             this.$el.querySelector('#entry-input').focus();
            //         });
            //     }
            // }
        }
    }
</script>

<template>
    <div class="function" style="overflow:hidden" color="#8789ff" :class="{  }"
        :style="transformation" 
        @pointerdown.prevent="drag_start"
        @contextmenu.prevent="on_context"
        @pointerup.prevent="drag_end">
        <div id="header" :style="{minWidth: header_min_width + 'px'}"
                @input="on_input">
                <v-avatar size="25" left class="avatar darken-4">F</v-avatar>
                <!-- Function name -->
                <!-- <v-select
                    class="center"
                    style="width: 100%;"
                    value="SELECT"
                    v-model="column"
                    :items="columns"
                    @change="ui_refresher++"
                ></v-select> -->
            <div 
            @input="on_input"
            @blur="on_blur" 
            :contenteditable="is_edit_mode"
            style="display: inline-block;">
                {{name}}
            <div>
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
    padding-bottom: 1.5px;
}

#content {
    display: flex;
    flex-direction: row; 
}

.function {
    position: absolute;
    cursor: copy ;
    background: orange;
    box-shadow: 50px 50px 50px rgba(255, 242, 94, 0); /* Removing weird trace on chrome */
    border-radius: 2px;
    padding: 5px;
    border-radius: 1000px;
    font-size: 15px;
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

.v-input {
    flex: none;
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

.v-application--is-ltr .v-text-field .v-input__append-inner {
    padding-left: 0px;
}

.v-select__selections input {
    display: none;
}

.v-select__selections {
    min-width: 65px;
}
.v-select__selection--comma {
    margin: 0;
}

.center .v-select__selection--comma {
    margin-left: auto;
    margin-right: auto;
}

.bold .v-select__selection--comma {
    font-weight: bold;
}

.v-input .v-select__slot {
    width: none;
}

.row.demo-row {
    display: flex;
    flex-wrap: nowrap;
    padding-top: 2.5px;
    padding-bottom: 2.5px;
    border-bottom: 2px solid rgba(	255, 255, 255, 0.2);
}

.row.demo-row:last-child {
  border-style: none !important;
}

.row.demo-row .v-btn::before {
  background-color: transparent;
}
#content > div > .demo-row > button {
  background-color: transparent;
  height: unset !important;
}

#content > div > .demo-row > button > span{
    padding: 2.5px;
}

.row.demo-row button {
  background-color: transparent;
}

.demo-row .v-select__slot {
    flex-direction: row-reverse;
}

.demo-row .connector {
  height: 15px;
  width: 15px;
  background-color: #bbb;
  border-radius: 50%;
  display: inline-block;
  margin-top: 6.5px;
  position: absolute;
  transition: all .2s ease-in-out;
  right: 0px;
  transform: translateX(50%);
}
.demo-row .connector:hover {
    transform: translateX(50%) scale(1.11);
}


.rule {
    margin-right: 10px;
    margin-left: 10px;
}
/* .v-select__slot {
    width: 100px;
} */

.v-input__slot::before {
  border-style: none !important;
}

.v-text-field>.v-input__control>.v-input__slot:after {
      border-style: none !important;
}

/* .nopadding.v-text-field input {
    padding: 0;
} */

</style>