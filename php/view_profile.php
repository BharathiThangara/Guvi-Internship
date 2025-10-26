<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
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

    $cacheKey = 'profile:' . $userId;
    $cachedProfile = $redis->get($cacheKey);

    if ($cachedProfile) {
        $profileData = json_decode($cachedProfile, true);
        echo json_encode([
            'success' => true,
            'data' => $profileData,
            'source' => 'redis'
        ]);
        exit;
    }

    $mongoCollection = $mongodb->users;
    $profile = $mongoCollection->findOne(['user_id' => (int)$userId]);

    $profileData = [
        'username' => $username,
        'email' => $email,
        'age' => $profile['age'] ?? '',
        'dob' => $profile['dob'] ?? '',
        'contact' => $profile['contact'] ?? '',
        'address' => $profile['address'] ?? ''
    ];

    $redis->setex($cacheKey, 3600, json_encode($profileData));

    echo json_encode([
        'success' => true,
        'data' => $profileData,
        'source' => 'mongodb'
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>