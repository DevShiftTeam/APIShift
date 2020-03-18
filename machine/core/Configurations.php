<?php
/**
 * APIShift Engine v1.0.0
 * (c) 2020-present Sapir Shemer, DevShift (devshift.biz)
 * Released under the MIT License with the additions present in the LICENSE.md
 * file in the root folder of the APIShift Engine original release source-code
 * @author Sapir Shemer
 */
        
namespace APIShift\Core;
        
class Configurations {
    public const INSTALLED = false;
    public const DB_HOST = "127.0.0.1";
    public const DB_PORT = 3306;
    public const DB_USER = "";
    public const DB_PASS = "";
    public const DB_NAME = "apishift";
    public const DB_TYPE = "MySQL";
    public const USE_HTTPS = true;
    public const CACHE_TYPE = CacheManager::APCU;
    public const CACHE_HOST = "";
    public const CACHE_PORT = "";
    public const CACHE_PASS = "";
}