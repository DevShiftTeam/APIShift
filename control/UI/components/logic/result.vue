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
        mixins: [APIShift.API.getMixin('graph/graph_element')],
        data () {
            return {
                drawer: null,
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
            all_loaded: function() { },
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
        }
    }
</script>

<template>
    <div class="function" style="overflow:hidden" color="#8789ff" :class="{  }"
        :style="transformation" 
        @pointerdown.prevent="drag_start"
        @contextmenu.prevent="on_context"
        @pointerup.prevent="drag_end">
        <div id="header"
                @input="on_input">
                <v-avatar size="25" left class="avatar darken-4">R</v-avatar>

                <!-- Function name -->
                <span 
                    :style="{ marginLeft: '10px', paddingRight: '10px'}">
                    RESULT
                </span>
        </div>
    </div>
</template>

<style scoped>
/* Please style this crap, with style */
.avatar{
    background-color: black;
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
    background: gray;
    box-shadow: 50px 50px 50px rgba(255, 242, 94, 0); /* Removing weird trace on chrome */
    border-radius: 2px;
    padding: 5px;
    border-radius: 100px;
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