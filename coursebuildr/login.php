<?php

class DatabaseFactory {
    private static $factory;
    private $database;
    public static function getFactory() {
        if (!self::$factory) { self::$factory = new DatabaseFactory(); }
        return self::$factory;
    }
	public function getConnection() {
        if (!$this->database) {
            try {
	            $options = array(
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING,
                    PDO::ATTR_STRINGIFY_FETCHES => false,
                );
                $this->database = new PDO('mysql:host=127.0.0.1;dbname=--removed-dbname--;port=--removed-dbport--;charset=utf8', '--removed-username--', '--removed-password--', $options);
            } catch (PDOException $e) {
                echo 'Database connection can not be estabilished.' . $e->getCode();
                exit;
            }
        }
        return $this->database;
    }
}
require_once 'NinjaValidator.php';
$verifier = (new \Ninjitsu\Validator($_GET))->check();

if (!$verifier->is_valid()) {
 	die("authentication failed or was not understood."); // a better error messsage / redirector is forthcoming
}

$tier = (int) $verifier->get_tier();
$api = $verifier->is_api();

/* tiers:
 * 0 = free access
 * 1 = copper, basic support, fixed layouts, no dropbox intergration
 * 2 = jade, dropbox support, layout chooser
 * 3 = crystal, pro tier level
 * 4 = anorak, api tier
 */

if ($tier < 1) {
	die("sorry, your subscription was not valid for this application. you must purchase a higher tier.");
}
$database = DatabaseFactory::getFactory()->getConnection();
$sql = "SELECT count(*) FROM plebs WHERE name = :name LIMIT 1";
$query = $database->prepare($sql);
$username = $verifier->get_username();
$useremail = $verifier->get_useremail();
$query->execute(array(
	":name" => $username
));
$count = $query->fetchColumn();
if ($count == 0) {

	// need to make a container for the user
	$sql = "INSERT INTO container (`name`) values (:name)";
	$query = $database->prepare($sql);
	$params = array(
		":name" => $verifier->get_username(),
	);
	$query->execute($params);
	
	// need to make the user
	$sql = "INSERT INTO plebs (`name`,`password`,`email`,`container`,`limit`) values (:name,:password,:email,:container,999)";
	$query = $database->prepare($sql);
	$params = array(
		":name" => $verifier->get_username(),
		":email" => $verifier->get_useremail(),
		":password" => hash("md5", time() . $verifier->get_username()),
		":container" => $verifier->get_username(),
	);
	$query->execute($params);
	
	// clone in the course row for the demo course then copy the folder into the new container for it too ... 
	// TODO
	
}
setcookie("Username", $verifier->get_username(), time() + 1800);
echo "<!DOCTYPE html><html><head><meta http-equiv='refresh' content='0;URL=/engine/pages/index/'></head></html>";

// can't do this because of an IIS bug - php doesn't set a cookie before setting headers
// header('Location: /engine/pages/index/');
