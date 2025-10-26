<?php
define('MYSQL_HOST', 'localhost');
define('MYSQL_USER', 'root');
define('MYSQL_PASS', '');
define('MYSQL_DB', 'internship_db');

define('MONGO_HOST', 'localhost');
define('MONGO_PORT', 27017);
define('MONGO_DB', 'internship_db');

define('REDIS_HOST', '127.0.0.1');
define('REDIS_PORT', 6379);

$mysqlConn = new mysqli(MYSQL_HOST, MYSQL_USER, MYSQL_PASS, MYSQL_DB);
if ($mysqlConn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'MySQL Connection failed: ' . $mysqlConn->connect_error]));
}
$mysqlConn->set_charset('utf8mb4');

require_once __DIR__ . '/../vendor/autoload.php';

try {
    $mongoClient = new MongoDB\Client("mongodb://" . MONGO_HOST . ":" . MONGO_PORT);
    $mongodb = $mongoClient->{MONGO_DB};

    $collectionNames = $mongodb->listCollectionNames();
    if (!in_array('users', iterator_to_array($collectionNames))) {
        $mongodb->createCollection('users');
        error_log("MongoDB: 'users' collection created automatically.");
    }
} catch (Exception $e) {
    die(json_encode(['success' => false, 'message' => 'MongoDB Connection failed: ' . $e->getMessage()]));
}

try {
    if (class_exists('Redis')) {
        $redis = new Redis();
        $connected = $redis->connect(REDIS_HOST, REDIS_PORT);
        if (!$connected) {
            throw new Exception('Could not connect to Redis server');
        }
        $redis->ping();
    } else {
        throw new Exception('Redis extension not loaded');
    }
} catch (Exception $e) {
    error_log('Redis Connection failed: ' . $e->getMessage());
    die(json_encode(['success' => false, 'message' => 'Redis Connection failed: ' . $e->getMessage()]));
}
?>
