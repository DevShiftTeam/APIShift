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

 /**
  * A global variable used to indicate whether the user requested data using a request
  * or in a PHP file
  */
 $REQUEST_MODE = true;

 require "APIShift.php";

 use APIShift\Core\Authorizer;
 use APIShift\Core\Status;
 use APIShift\Core\Configurations;

// Step 1: Validate request format
if (!isset($_GET["c"]) || !isset($_GET["m"])) Status::message(Status::ERROR, "Invalid Request, Method or Controller not set");
if (!Configurations::INSTALLED && $_GET["c"] != "Installer") Status::message(Status::NOT_INSTALLED, "Not Installed, can only call the Installer controller");

// Step 3: Authorize & run the request
Authorizer::authorizeAndRun($_GET["c"], $_GET["m"], $_POST);

// Step 4: Post message if controller didn't exit application
Status::respond();