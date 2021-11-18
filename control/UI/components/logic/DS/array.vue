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
        mixins: [APIShift.API.getMixin('graph/logic/data_source', true)],
        data () {
            return {
                drawer: null,
                in_edit: {
                    name: false,
                    entry_index: -1
                },
                edit_name_width: 0,
                entry_max_width: 0,
                edit_entry_width: 0,
                header_min_width: 0
            }
        },
        created () {
            window.graph_elements[this.$props.index] = this;
            
            window.a = this;
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
            all_loaded () {
                this.create_lines();
            },
            drag_start_addition: function(event) {

            },
            drag_addition(event) {

            },
            drag_end_addition(event) {

            },
            on_delete() {

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
                    y: this.$props.data.position.y
                };
            },
            to_position: function() {
                return {
                    x: this.$props.data.position.x,
                    y: this.$props.data.position.y
                };
            }
        },
    }
</script>

<template>
    <div class="array" color="#8789ff" :class="{  }"
        :style="transformation" 
        @pointerdown.prevent="drag_start"
        @contextmenu.prevent="on_context"
        @pointerup.prevent="drag_end">

        <!-- Header  -->
        <div id="header" :style="{minWidth: header_min_width + 'px'}"
                @input="on_input"
                @dblclick.prevent="is_edit_mode.name = true">
                <v-icon size="25" left class="avatar darken-4">mdi-code-array</v-icon>
                <v-text-field
                    v-model="name"
                    v-show="is_edit_mode.name"
                    :style="{'width': edit_name_width + 'px', marginLeft: 'auto', marginRight: 'auto'}"
                    @input="requestAnimationFrame(() => edit_name_width = $el.querySelector('#name').offsetWidth + 30)"
                    >
                </v-text-field>
                <b :style="{  marginLeft: 'auto', marginRight: 'auto', fontSize: '120%' }" v-show="!is_edit_mode.name">{{name}}</b>
                <span id="name" style="position: absolute; top: -120%; color: rgba(0,0,0,0);">{{name}}</span>
        </div>

        <!-- Entry list -->
        <div id="content"
            @dblclick.prevent="is_edit_mode.entry = true">
            <v-col style="padding-bottom: 0px; padding-top: 0px">
                <v-row  v-for="(entry, index) in $props.data.entries" 
                        :key="index" 
                        class="demo-row no-border" 
                        style="display: flex">
                    <v-text-field
                        class="name-input"
                        v-model="entry.val"
                        v-show="in_edit.entry_index === index"
                        @blur="in_edit.entry_index = -1"
                        :style="{'width': edit_name_width + 'px'}">
                    </v-text-field>
                    <span
                        @dblclick.prevent="in_edit.entry_index = index"
                        v-show="in_edit.entry_index !== index">
                        {{entry.val}}
                    </span>
                    <span 
                        id="name"
                        style="position: absolute; opacity: 0; z-index: -10;">
                        {{entry.val}}
                    </span>
                    <div class="connector output" @pointerdown="on_connector_click(event,index)"></div>
                    <div class="connector input"></div>
                </v-row>
                <v-tooltip top>
                    <template #activator="{ on }">
                        <v-row class="demo-row no-border" block v-on="on" style="padding: 0px;">
                            <v-btn @click="add_entry();" block v-on="on" style="padding: 0px;">
                                <v-icon>mdi-plus-circle</v-icon>
                            </v-btn>
                        </v-row>
                    </template>
                    <span>Add Entry</span>
                </v-tooltip>
            </v-col>
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

.row.demo-row {
    display: flex;
    flex-wrap: nowrap;
    padding-top: 2.5px;
    padding-bottom: 2.5px;
    border-bottom: 2px solid rgba(	255, 255, 255, 0.2);
    justify-content: center;
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

#content > div > .demo-row > button > span > i{
    font-size: 18px;
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
}

.demo-row .connector.input {
    left: -7px;
}

.demo-row .connector.output {
    right: -7px;
}


.demo-row .connector:hover {
    transform: scale(1.11);
}

.avatar{
    background-color: #083D77;
}

#header {
    display: flex;
    flex-direction: row;
    border-bottom: 2px solid rgba(	100, 100, 220, 0.7);
    padding-bottom: 1.5px;
}

#header i {
    height: 70%;
    border-radius: 10px;
}

#content {
    display: flex;
    flex-direction: row; 
}

.table {
    position: absolute;
    cursor: copy ;
    background: #8789ff;
    box-shadow: 50px 50px 50px rgba(255, 242, 94, 0); /* Removing weird trace on chrome */
    border-radius: 2px;
    padding: 5px;
    padding-bottom: 0;
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

.v-select__selections input {
    width: 5px;
}


.v-select__selections {
    min-width: 65px;
}
.v-select__selection--comma {
    font-size: 15px;
    margin: 0;
    padding: 0;
    margin-right: 5px;
}

.v-select__selection--comma:last-of-type {
    margin-right: 0px;
}


.bold .v-select__selection--comma {
    font-weight: bold;
}

.v-input .v-select__slot {
    width: none;
}

#input-111 {
    text-align: center;
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

#content > div > .demo-row > button > span > i{
    font-size: 18px;
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
}

.demo-row .connector.input {
    left: -7px;
}

.demo-row .connector.output {
    right: -7px;
}


.demo-row .connector:hover {
    transform: scale(1.11);
}


.rule {
    margin-right: 10px;
    margin-left: 10px;
}


.no-border .v-input__slot::before {
  border-style: none !important;
}

.no-border .v-text-field>.v-input__control>.v-input__slot:after {
      border-style: none !important;
}
.v-input__append-inner .v-input__append-inner {
    display: none;
}
#content div.v-input__slot > div.v-select__slot > div:nth-child(3) {
    display: none;

}

/* .nopadding.v-text-field input {
    padding: 0;
} */

</style>