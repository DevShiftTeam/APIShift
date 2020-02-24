<?php
/**
 * APIShift Engine v1.0.0
 * (c) 2020-present Sapir Shemer, DevShift (devshift.biz)
 * Released under the MIT License with the additions present in the LICENSE.md
 * file in the root folder of the APIShift Engine original release source-code
 * @author Sapir Shemer
 */

namespace APIShift\Models;
use APIShift\Core\DatabaseManager;
use APIShift\Core\Status;
use Exception;

/**
 * Creates the configuration file for the engine
 */
class Installer {
    /**
     * Load the initial database structure and data for the system to function
     * 
     * @param $db_host Database host
     * @param $db_user Database username
     * @param $db_pass Database password
     * @param $db_port Database port
     * @return void
     */
    public function createDB(string $db_host, string $db_name, string $db_user, string $db_pass, int $port = 3306, string $user, string $pass) {
        // Step 1: Connect to DB
        DatabaseManager::startConnection("main", $db_host, $db_user, $db_pass, $port, $db_name, false);

        try {
            // Create schema if not exists
            if(Status::getStatus() == Status::DB_CONNECTION_FAILED) {
                DatabaseManager::startConnection("main", $db_host, $db_user, $db_pass, $port);
            $add_schema = DatabaseManager::query("main", "CREATE SCHEMA {$db_name}; USE {$db_name};");
                if(!$add_schema) Status::message(Status::ERROR, "Couldn't create DB schema");
            }

            // Step 2: Load sql file of installation, and import the initial data
            $data_to_import = file_get_contents("data/initial.sql");
            if(!$data_to_import) {
                Status::message(
                    Status::ERROR,
                    "Couldn't open initial data SQL file, please check permissions, re-download the system or add missing files"
                );
            }
            DatabaseManager::getInstance("main")->exec($data_to_import);
            
            // Add admin user to DB
            $add_admin = DatabaseManager::query("main",
                "INSERT INTO admin_users (username, password, created) VALUES (:username, :password, NOW())",
                [
                    "username" => $user,
                    "password" => password_hash($pass, PASSWORD_BCRYPT)
                ]
            );
            if(!$add_admin) Status::message(Status::ERROR, "Couldn't upload system user data to DB");
        }
        catch (Exception $e) {
            Status::message(Status::ERROR, "Couldn't upload data to DB " + $e->getMessage());
        }
    }

    /**
     * Modify config file using the installation data
     * 
     * @param $db_host Database host
     * @param $db_user Database username
     * @param $db_pass Database password
     * @param $db_port Database port
     * @param $user Control panel username
     * @param $pass Control panel password
     * @return void
     */
    public function createConfigFile(string $db_host, string $db_name, string $db_user, string $db_pass, int $db_port = 3306) {
        $newConfigFileData = <<<EOT
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
    public const INSTALLED = true;
    public const DB_HOST = "{$db_host}";
    public const DB_PORT = {$db_port};
    public const DB_USER = "{$db_user}";
    public const DB_PASS = "{$db_pass}";
    public const DB_NAME = "{$db_name}";
    public const USE_HTTPS = true;
}
EOT;

        if(!file_put_contents("core/Configurations.php", $newConfigFileData))
            Status::message(Status::ERROR, "Couldn't change the configurations file, please check permissions");
    }
  }
?>