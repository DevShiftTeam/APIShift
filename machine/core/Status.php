<?php
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

namespace APIShift\Core;

/**
 * Interface containing functions that provide a standard for notifying about internal & procedure states with data attached for the client to understand
 */
class Status {
    /**
     * Error message ID
     */
    const ERROR = 0;

    /**
     * Success message ID
     */
    const SUCCESS = 1;

    /**
     * No Authorization code
     */
    const NO_AUTH = 2;
    
    /**
     * Not instlled message ID
     */
    const NOT_INSTALLED = 3;

    /**
     * COuldn't connect to DB message ID
     */
    const DB_CONNECTION_FAILED = 4;

    /**
     * Configuration file damaged message ID
     */
    const INVALID_CONFIG_FILE = 5;

    /**
     * Container of the last status output
     */
    private static $output = [ 'status' => Status::SUCCESS, 'data' => "Status haven't changed" ];

    /**
     * Return a status & data to the client & exit
     * 
     * @param int $status_code Status code to assign to the output message
     * @param mixed $data Data to provide along with the status message
     * @param bool $message_and_exit Set FALSE to not stop running when this function is called
     */
    public static function message($status_code = Status::SUCCESS, $data = null, $message_and_exit = true)
    {
        // Update status
        self::$output['status'] = $status_code;
        // Assign message if present
        if($data !== null && $data !== "") self::$output['data'] = $data;
        // Assign default message if exists
        else if($data === null && session_status() != PHP_SESSION_NONE && isset($_SESSION["StatusCollection"][$status_code]))
            $data = $_SESSION["StatusCollection"][$status_code]["default_message"];
        // Assign empty message if non can be applied
        else self::$output['data'] = "";

        // Send data to UI
        if($message_and_exit && $GLOBALS['REQUEST_MODE']) self::respond();
    }

    /**
     * Post the message that was constructed
     */
    public static function respond() {
        echo json_encode(self::$output);
        exit();
    }

    /**
     * Get current status
     * 
     * @return int Current status
     */
    public static function getStatus() { return self::$output['status']; }

    /**
     * Get current status message
     * 
     * @return string Current status message
     */
    public static function getMessage() {return self::$output['data']; }
}

?>