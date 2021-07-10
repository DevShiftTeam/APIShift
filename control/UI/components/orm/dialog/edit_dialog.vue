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


    module.exports = {
        props: {
            item_type: Number
        },
        data () {
            return {
                name: '',
                hovered_index: -1,
            }
        },
        created () {
        },
        mounted () {
          this.name = graph_view.elements[graph_view.in_edit].name;
        },
        computed: {
            transformation: function() {
                return {
                    top: `${this.$props.position.y}px`,
                    left:  `${this.$props.position.x}px`,
                    'z-index':  graph_view.elements.length + 1
                }
            }
        },
        methods: {
            window_pointer_down(event) {
                if (!event.target.closest('#context_menu_primary')) {
                    graph_view.context_menu.is_active = false;
                }
            },
          save () {
            graph_view.elements[graph_view.in_edit].name = this.name;
            graph_view.dialog_open = false;
          }
        },
    }
</script>

<template>
    <v-dialog
      v-model="graph_view.dialog_open"
      width="500"
    >

      <v-card>
        <v-card-title class="text-h5 grey lighten-2">
          Add Item
        </v-card-title>
          <v-container>
              <v-row>
                  <v-col cols="12" sm="12" md="12">
                      <v-text-field v-model="name" label="Name"></v-text-field>
                  </v-col>          
              </v-row>
          </v-container>

        <v-divider></v-divider>

        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn
            color="primary"
            text
            @pointerdown.prevent="save"
          >
            save
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
</template>

<style scoped>
/* Please style this crap, with style */

#context_menu, #context_menu_primary {
    position: absolute;
    background: rgba(0, 0, 0, 0.85);
    transform-origin: 0 0;
    left: 100%;
    top: 0%;
}
</style>