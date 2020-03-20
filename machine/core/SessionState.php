<?php
/**
 * APIShift Engine v1.0.0
 * (c) 2020-present Sapir Shemer, DevShift (devshift.biz)
 * Released under the MIT License with the additions present in the LICENSE.md
 * file in the root folder of the APIShift Engine original release source-code
 * @author Sapir Shemer
 */

namespace APIShift\Core;

/**
 * Manages everything aroung your different session states & data
 */
class SessionState {
    // Hardcoded states
    public const DEFAULT_VIEWER = 0;
    public const NO_STATE = -1;

    /**
     * Load all the default session variables into state
     * 
     * @return void
     */
    public static function loadDefaults()
    {
        // Start session
        if(session_status() == PHP_SESSION_NONE) session_start();
        // Get current state
        $state = self::getSessionState();
        if($state != self::NO_STATE) {
            // Handle other state timeouts
            if ($state > 0) {
                $state_collection = CacheManager::get("StateCollection"); // Load from cache
                // Find if session really exists
                if(isset($state_collection[$state])) {
                    if(
                        // Timeout if a user was inactive by the timeout rule attached to the session
                        ($state_collection[$state]['inactive_timeout'] > 0
                        && $_SERVER['REQUEST_TIME'] - $_SESSION['lastActivity'] > $state_collection[$state]['inactive_timeout'])
                        // Timeout if the time since the state was validated & authenticated passed the user defined rule
                        || ($state_collection[$state]['active_timeout'] > 0
                        && $_SERVER['REQUEST_TIME'] - $_SESSION['whenActivated'] > $state_collection[$state]['active_timeout'])
                    ) {
                        session_unset();
                        session_destroy();
                        session_start();
                    }
                }
                // `else` Cannot happen in theory as we determine the state ID in the changeState function
                // as part of the collection, but let's leave this check here just in case developers mess
                // around too much
                else {
                    session_unset();
                    session_destroy();
                    Status::message(Status::ERROR, "Internal Error! Couldn't find inner state");
                }
            }

            // Update last request time and return
            $_SESSION['lastActivity'] = $_SERVER['REQUEST_TIME'];
            return;
        }
        
        // Set state as default viewer if there is no state
        $_SESSION['state'] = self::DEFAULT_VIEWER;
    }

    /**
     * Get state id by its name
     * 
     * @param $name The name of the state to retrieve the data of
     * @return int The state ID or -1 if not found
     */
    public static function getStateID($name) {
        $state_collection = CacheManager::get('StateCollection');
        foreach($state_collection as $id => $state) if($state['name'] == $name) return $id;
        return -1;
    }

    /**
     * Set and validate a specific state
     * 
     * @param string $state_name Name of the state to move to
     * @param array $params Parameters to pass to the authorization process
     * @return void
     */
    public static function changeState($state_name)
    {
        SessionState::LoadDefaults();
        
        if($state_name == "DEFAULT_VIEWER") $_SESSION['state'] = self::DEFAULT_VIEWER;
        else {
            // Find state id in collection
            $state_id = -1;
            $state_collection = CacheManager::get('StateCollection');
            foreach($state_collection as $id => $state) if($state['name'] == $state_name) $state_id = $id;

            // In case state not found return error
            if($state_id == -1) Status::message(Status::ERROR, "State couldn't be found");

            // Run and validate processes
            if(!Task::validateResult(Task::run($state_collection[$state_id]['auth_task'])))
                Status::message(Status::ERROR, "Authorization failed, please check credentials");
            // TODO: Add necessary parameters to session from params/database

            // Save session active timeout
            if($state_collection[$state_id]['active_timeout'] > 0) {
                // $_SESSION['whenActivated'] defines the last time since the state has validate
                $_SESSION['whenActivated'] = $_SERVER['REQUEST_TIME'];
            }

            // Change state
            $_SESSION['state'] = $state_id; // The plus one is to make it start after the admin state
        }
    }

    /**
     * Returns the current session state ID or -1 in case session isn't set
     * 
     * @return int
     */
    public static function getSessionState() {
        if(isset($_SESSION['state'])) return $_SESSION['state'];
        return self::NO_STATE;
    }
}

?>