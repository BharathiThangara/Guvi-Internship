<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once 'config.php';

$headers = getallheaders();
$sessionToken = isset($headers['Authorization']) ? trim($headers['Authorization']) : '';

if (empty($sessionToken)) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

try {
    $sessionData = $redis->get('session:' . $sessionToken);

    if (!$sessionData) {
        echo json_encode(['success' => false, 'message' => 'Invalid session']);
        exit;
    }

    $session = json_decode($sessionData, true);
    $userId = $session['user_id'];
    $username = $session['username'];
    $email = $session['email'];

    $mongoCollection = $mongodb->users;

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $profile = $mongoCollection->findOne(['user_id' => (int)$userId]);

        if ($profile) {
            echo json_encode([
                'success' => true,
                'data' => [
                    'username' => $username,
                    'email' => $email,
                    'age' => $profile['age'] ?? '',
                    'dob' => $profile['dob'] ?? '',
                    'contact' => $profile['contact'] ?? '',
                    'address' => $profile['address'] ?? ''
                ]
            ]);
        } else {
            echo json_encode([
                'success' => true,
                'data' => [
                    'username' => $username,
                    'email' => $email,
                    'age' => '',
                    'dob' => '',
                    'contact' => '',
                    'address' => ''
                ]
            ]);
        }
    }

    elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input) {
            echo json_encode(['success' => false, 'message' => 'Invalid input']);
            exit;
        }

        $updateData = [
            'user_id' => (int)$userId,
            'username' => $username,
            'email' => $email,
            'age' => isset($input['age']) ? (int)$input['age'] : null,
            'dob' => $input['dob'] ?? null,
            'contact' => $input['contact'] ?? null,
            'address' => $input['address'] ?? null,
            'updated_at' => new MongoDB\BSON\UTCDateTime()
        ];

        $result = $mongoCollection->updateOne(
            ['user_id' => (int)$userId],
            ['$set' => $updateData],
            ['upsert' => true]
        );

        $cacheKey = 'profile:' . $userId;
        $cacheData = [
            'username' => $username,
            'email' => $email,
            'age' => $updateData['age'],
            'dob' => $updateData['dob'],
            'contact' => $updateData['contact'],
            'address' => $updateData['address']
        ];
        $redis->setex($cacheKey, 3600, json_encode($cacheData));

        echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>