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

    module.exports = {
        data() {
            return {
                access_types: [
                    "Function",
                    "State",
                    "Task",
                ],
                access_names: []
            }
        },
        methods: {
            getRuleType(rule) {
                if(rule.task_name == "") return "Not Set";
                if(rule.task_name.indexOf("_") == -1) return "Task";
                if(rule.task_name == 'state_auth') return "State";
                let prefix = rule.task_name.substring(0, rule.task_name.indexOf("_"));
                if(prefix == 'function') return "Function";
                return "Task";
            },
            getRuleName(rule) {
                if(rule.task_name.indexOf("_") == -1) return rule.task_name;
                if(rule.task_name == 'state_auth') return rule.input_name.substring(rule.task_name.indexOf("_") + 1);
                let prefix = rule.task_name.substring(0, rule.task_name.indexOf("_"));
                if(prefix == 'function') return rule.task_name.substring(rule.task_name.indexOf("_") + 1);
                return rule.task_name;
            },
            getAccessNameByValue: function(val) {
                for(let key in this.access_names) if(this.access_names[key].val == val) return this.access_names[key];
                return null;
            },
            getAvailableRulesForType(type) {
                let handler = this;
                switch(type) {
                    case "Function":
                        // Get all available functions
                        this.access_names = [];
                        break;
                    case "State":
                        // Get all available states
                        APIShift.API.request("Admin\\SessionState", "getAllSessionStates", {}, function(response) {
                            handler.access_names = [];
                            if(response.status == APIShift.API.status_codes.SUCCESS) {
                                handler.access_names.push({ text: "DEFAULT_VIEWER", val: 0 }); // Add default state
                                for(key in response.data) {
                                    let current = response.data[key];
                                    let name = response.data[key].name;

                                    while(current.parent != 0) {
                                        name = response.data[current.parent].name + "/" + name;
                                        current = response.data[current.parent];
                                    }

                                    handler.access_names.push({ text: name, val: key});
                                }
                            }
                            else {
                                APIShift.API.notify(response.data, 'error');
                            }
                        });
                        break;
                    default:
                        // Get all available tasks
                        APIShift.API.request("Admin\\Access\\Main", "getAllTasks", {}, function(response) {
                            handler.access_names = [];
                            if(response.status == APIShift.API.status_codes.SUCCESS) {
                                for(key in response.data) {
                                    handler.access_names.push({ text: response.data[key].name, val: response.data[key].id });
                                }
                            }
                            else {
                                APIShift.API.notify(response.data, 'error');
                            }
                        });
                        break;
                }
            }
        }
    };
</script>