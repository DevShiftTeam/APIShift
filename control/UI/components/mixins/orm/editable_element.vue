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


    
    // Notice - This mixin assumes using 'graph_element.vue' mixin

    module.exports = {
        data() {
            return {
                is_edit_on: false
            }
        },
        methods: {
            on_input (event) {
                this.ui_refresher++;
                
                // Update state
                graph_view.elements[this.$props.index].name = event.target.innerText;

                // Update UI dependencies
                if (this.group_index && this.group_index != -1) graph_elements[this.group_index].update_group_size();
                if (this.group_parent_index && this.group_parent_index != -1) graph_elements[this.group_parent_index].update_group_size();
            }
        },
        computed: {

        }
    };
</script>