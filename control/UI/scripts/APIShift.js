/**
 * APIShift Engine v1.0.0
 * (c) 2020-present Sapir Shemer, DevShift (devshift.biz)
 * Released under the MIT License with the additions present in the LICENSE.md
 * file in the root folder of the APIShift Engine original release source-code
 * @author Sapir Shemer
 */


// Contains:
// Loader
// Header
// APIHandler

let MyServer = "https://" + location.host;

/**
 * APIShift library constructor
 */
class APIShift {
    constructor(loader = app.loader) {
        // Set default loader
        APIShift.Loader.changeLoader("main", loader);
        // Initialize
        APIShift.Loader.message("Loading System");
        this.initialize();
    }

    initialize() {
        APIShift.Loader.show("main"); // Show main loader
        // Load statuses and check admin mode
        return APIShift.Loader.load(() => {
            // Set admin mode in case the user is in admin page
            if (location.pathname.indexOf("/control") == 0) {
                APIShift.admin_mode = true;
                // Load default components
                APIShift.components['notifications'] = httpVueLoader(APIShift.API.getComponent("notifications"));
            }

            /**
             * Fill in API data from server
             */
            APIShift.API.request("Status", "getAllStatuses", {}, function (response) {
                switch (response.status) {
                    case 0:
                        APIShift.API.notify("Error: " + response.data, "error");
                        break;
                    case 1:
                        for(let status in response.data)
                            APIShift.API.status_codes[response.data[status].name] = 4 + response.data[status].id;
                            APIShift.load_components = true;
                        break;
                    case 2:
                        // Redirect user to admin page if system is not installed
                        if (!APIShift.admin_mode) location.href = MyServer + "/control/index.html";
                        // Route to installation page
                        else {
                            APIShift.admin_routes.push({
                                path: '/installer',
                                component: httpVueLoader(APIShift.API.getPage("installer"))
                            });
                            APIShift.installed = false; // Don't continue loading system if not installed
                        }
                }
            }, true);

            // Preceding code is only for the CP
            if(!APIShift.admin_mode) {
                APIShift.Loader.close("main"); // Close main loader
                return;
            }

            // Installation check
            if(!APIShift.installed) {
                APIShift.Loader.close("main"); // Close main loader
                return;
            }

            // Load default pages
            APIShift.admin_routes.push({
                path: '/main',
                component: httpVueLoader(APIShift.API.getPage("main"))
            });
            APIShift.admin_routes.push({
                path: '/login',
                component: httpVueLoader(APIShift.API.getPage("login"))
            });

            // Check that session state allows to proceed
            if(this.isSessionState(1)) {
                APIShift.load_components = true;
                APIShift.logged_in = true;
            } else {
                APIShift.load_components = false; // Don't load other system components before login
                APIShift.Loader.close("main"); // Close main loader
                return;
            }

            // Load admin components
            APIShift.components['footer'] = httpVueLoader(APIShift.API.getComponent("footer"));
            APIShift.components['navigator'] = httpVueLoader(APIShift.API.getComponent("navigator"));
            APIShift.components['loader'] = httpVueLoader(APIShift.API.getComponent("loader"));
            APIShift.Loader.close("main"); // Close main loader
        });
    };

    /**
     * Compare to check the session state id
     */
    isSessionState(state_id) {
        let result = false;
        APIShift.API.request("SessionState", "getCurrentSessionState", {}, function (response) {
            if(response.status == 1) result = response.data == state_id;
            else APIShift.API.notify(APIShift.API.getStatusName(response.status) + ": " + response.data, "error");
        }, true);
        return result;
    }
};

/**
 * Loader manages loading stages in your page.
 * It is an algorithmic representation for managing loaders as objects that are binded to the view by a view-model (VueJS, ReactJS, Angualr...)
 * It can load all the operations requested in managed timing (using chain processes and more).
 * The algorithmics of the Loader focuses mostly on the variable "processes" present in the loader object, which is a counter
 * of the number of running processes, this counter is incremented when a loader is called, and decremented when a process is finished
 */
class Loader {
    constructor() {
        // Collection of the loaders in the screen
        this.loader_manager = {};
        // Currently selected loader key
        this.current_loader = "";
    }

    /**
     * Connect the loader object to a different loader
     * @param {String} name The name to identify the loader by
     * @param {Object} new_loader The loader variable to track, in the format of { visible: Boolean, message: String }
     */
    changeLoader(name, new_loader = {}) {
        // Create a loader if doesn't exists
        this.addLoader(name, new_loader);

        // Change loader used
        this.current_loader = name;
    }

    /**
     * Add loader object to collection
     * @param {String} name The name to identify the loader by
     * @param {Object} new_loader The loader variable to track, in the format of { visible: Boolean, message: String }
     */
    addLoader(name, new_loader = {}) {
        if(this.loader_manager[name] === undefined && new_loader == null) return;
        else if(this.loader_manager[name] === undefined) {
            let current = this.loaderExists(new_loader);
            if(current !== false)
            {
                Object.defineProperty(this.loader_manager, current, Object.getOwnPropertyDescriptor(this.loader_manager, name));
                delete this.loader_manager[current];
            } else {
                // Attach loader and create process counter
                this.loader_manager[name] = new_loader;
                this.loader_manager[name].processes = 0;
                if(this.loader_manager[name].visible === undefined) this.loader_manager[name].visible = false;
                if(this.loader_manager[name].message === undefined) this.loader_manager[name].message = "";
            }
        }
        else if(this.loader_manager[name] !== new_loader && new_loader != null)
        {
            // Close previous loader
            this.loader_manager[name].visible = false;
            // Change loader
            this.loader_manager[name] = new_loader;
            // Pass loading state to new loader
            this.loader_manager[name].visible = this.loader_manager[name].processes == 0;
        }
    }

    /**
     * Check if loader already present
     * @returns {String/Boolean} False or the key of the existing loader
     */
    loaderExists(loader = {}) {
        for(let key in this.loader_manager) {
            if(this.loader_manager[key] === loader) return key;
        }
        return false;
    }

    /**
     * Toggle the view of the loader
     */
    toggle(loader_name = this.current_loader) {
        if (this.loader_manager[loader_name].visible) this.close(loader_name);
        else this.show(loader_name);
    }

    /**
     * Show the loader in view
     */
    show(loader_name = this.current_loader) {
        this.loader_manager[loader_name].processes++;
        if(!this.loader_manager[loader_name].visible) this.loader_manager[loader_name].visible = true;
    }

    /**
     * Remove loader from view
     */
    close(loader_name = this.current_loader) {
        this.loader_manager[loader_name].processes--;
        if(this.loader_manager[loader_name].processes == 0
            && this.loader_manager[loader_name].visible) this.loader_manager[loader_name].visible = false;
    }

    /**
     * Load a function and show the loader if defined
     * @param {Function} handlerMethod The method to run when loading
     * @param {String} loader_name The loader name to use at this loading
     * @param {Boolean} background Run in background and don't show the loader on screen
     */
    load(handlerMethod = function() {}, loader_name = this.current_loader, background = false) {
        // Promise is indepenent of any other promise so no need to keep
        let tempProm = new Promise(function (resolve) {
            if (!background) APIShift.Loader.show(loader_name);
            resolve();
        });
        tempProm.then(handlerMethod);
        tempProm.then(function () {
            // When done close loader
            if (!background) APIShift.Loader.close(loader_name);
        });
        return tempProm;
    }

    /**
     * Show loader for a defined amount of time
     * @param {number} ms Time to show the loader for
     * @param {fucntion} after Function to run after timer ends
     * @param {fucntion} before Function to run with timer
     * @param {boolean} loader_name Loader to use for operation
     */
    timer(ms, after = function() {}, before = function() {}, loader_name = this.current_loader) {
        APIShift.Loader.show(loader_name);
        setTimeout(function() {
            before();
            APIShift.Loader.close(loader_name);
            after();
        }, ms);
    }

    /**
     * Assign message to loader screen
     * @param {String} message The message to show
     */
    message(message, loader_name = this.current_loader) {
        this.loader_manager[loader_name].message = message;
    }
};

/**
 * APIHandler holds helper functions for communicating with the server
 * Holds API specific data & handling functions
 */
class APIHandler {
    constructor() {
        this.status_codes = {
            ERROR: 0,
            SUCCESS: 1,
            NOT_INSTALLED: 2,
            DB_CONNECTION_FAILED: 3,
            INVALID_CONFIG_FILE: 4
        };
    }

    /**
     * Returns the name of the status code given
     * @param {int} status_code 
     */
    getStatusName(status_code) {
        for(let StatusName in this.status_codes)
            if(this.status_codes[StatusName] == status_code) return StatusName;
        return "STATUS_CODE_NOT_FOUND";
    }

    /**
     * Retreive data from the API
     * @param {string} controller The controller to use
     * @param {string} method The method to call
     * @param {object} attached_data Data to send to the method
     * @param {function} HandlerMethod Function to handle the response or error
     * @param {boolean} use_loader Open loader until request finishes
     */
    request(controller, method, attached_data = {}, handlerMethod = function (response) {}, use_loader = false) {
        if (use_loader) APIShift.Loader.show();
        // Define request function without running it
        return $.ajax({
            type: "POST",
            url: MyServer + "/machine/API.php?c=" + controller + "&m=" + method,
            data: attached_data,
            async: !use_loader,
            dataType: "json",
            success: function (response) {
                handlerMethod(response);
            },
            error: function () {
                handlerMethod({
                    status: 0,
                    data: "Request couldn't be complete"
                });
            },
            complete: function () {
                if (use_loader) APIShift.Loader.close();
            }
        });
    }

    /**
     * Show live notification on screen
     * @param {string} message Message body of notification
     * @param {string} type Notification class
     * @param {int} timeout Time to wait till closing the notification
     */
    notify(message, type, timeout = 5000) {
        // If an alert exists then exit
        for (let alert in app.alerts) {
            if (app.alerts[alert].content == message && app.alerts[alert].color == type && app.alerts[alert].show)
                return;
        }

        // Used for the v-for loop as indicator for order
        let MyID = new Date().getTime();

        // Push alert
        app.alerts.push({
            color: type,
            content: message,
            show: true,
            id: MyID
        });

        // Close alert after timeout
        setTimeout(function (message) {
            for (let alert in app.alerts) {
                if (app.alerts[alert].content == message) {
                    app.alerts[alert].show = false;
                    app.alerts.splice(alert, 1);
                    return;
                }
            }
        }, timeout, message);
    }

    /**
     * Get full path of the page
     * @param {string} page_name Page name to get path to
     */
    getPage(page_name) {
        return "UI/pages/" + page_name + ".vue";
    }

    /**
     * Get full path of the component
     * @param {string} component_name Page name to get path to
     */
    getComponent(component_name) {
        return "UI/components/" + component_name + ".vue";
    }

    hasOwnProperty(obj, prop) {
        var proto = Object.getPrototypeOf(obj) || obj.constructor.prototype;
        return (prop in obj) && (!(prop in proto) || proto[prop] !== obj[prop]);
    }
};

/**
 * Determines if the user is working in admin page
 * Admin mode is client based, admin operations are authenticated by the server separately
 */
APIShift.admin_mode = false;
/**
 * Pages are loaded using the loader from the API
 */
APIShift.pages = {};
APIShift.admin_routes = [];
APIShift.components = {};
APIShift.load_components = true;
APIShift.logged_in = false;
APIShift.installed = true;
/**
 * Helpers
 */
APIShift.Loader = new Loader();
APIShift.API = new APIHandler();