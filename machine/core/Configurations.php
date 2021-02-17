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
 * Contains the core configuration to make the system run
 */
class Configurations {
    /**
     * When true system acts as though itshould be installed, false otehrwise
     */
    const INSTALLED = true;

    /**
     * Database login info
     */
    const DB_HOST = "mysql_web";
    const DB_PORT = 3306;
    const DB_USER = "root";
    const DB_PASS = "123qwe@@";
    const DB_NAME = "apishiftdocker";
    const DB_TYPE = "MySQL";

    /**
     * When true server requires the use oh HTTPS at each request
     */
    const USE_HTTPS = true;

    /**
     * Cache system configurations
     */
    const CACHE_TYPE = CacheManager::APCU;
    const CACHE_HOST = "127.0.0.1";
    const CACHE_PORT = 6379;
    const CACHE_PASS = "";
}