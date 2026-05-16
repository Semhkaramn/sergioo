<?php
// Kullanıcı işlemleri için fonksiyonlar

define('USERS_FILE', __DIR__ . '/../data/users.json');

// Kullanıcı verilerini oku
function getUsers() {
    if (!file_exists(USERS_FILE)) {
        return ['users' => [], 'next_id' => 1];
    }
    $data = json_decode(file_get_contents(USERS_FILE), true);
    return $data ?: ['users' => [], 'next_id' => 1];
}

// Kullanıcı verilerini kaydet
function saveUsers($data) {
    return file_put_contents(USERS_FILE, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// Kullanıcı kaydı
function registerUser($username, $email, $password) {
    $data = getUsers();

    // Kullanıcı adı kontrolü
    foreach ($data['users'] as $user) {
        if (strtolower($user['username']) === strtolower($username)) {
            return ['success' => false, 'message' => 'Bu kullanıcı adı zaten kullanılıyor.'];
        }
        if (strtolower($user['email']) === strtolower($email)) {
            return ['success' => false, 'message' => 'Bu e-posta adresi zaten kayıtlı.'];
        }
    }

    // Validasyonlar
    if (strlen($username) < 3 || strlen($username) > 20) {
        return ['success' => false, 'message' => 'Kullanıcı adı 3-20 karakter arasında olmalı.'];
    }

    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        return ['success' => false, 'message' => 'Kullanıcı adı sadece harf, rakam ve alt çizgi içerebilir.'];
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Geçerli bir e-posta adresi girin.'];
    }

    if (strlen($password) < 6) {
        return ['success' => false, 'message' => 'Şifre en az 6 karakter olmalı.'];
    }

    // Yeni kullanıcı oluştur
    $newUser = [
        'id' => $data['next_id'],
        'username' => $username,
        'email' => $email,
        'password' => password_hash($password, PASSWORD_DEFAULT),
        'points' => 0,
        'level' => 1,
        'telegram_id' => null,
        'telegram_username' => null,
        'telegram_verified' => false,
        'telegram_code' => null,
        'avatar' => null,
        'created_at' => date('Y-m-d H:i:s'),
        'last_login' => null,
        'total_messages' => 0,
        'daily_messages' => 0,
        'last_message_date' => null
    ];

    $data['users'][] = $newUser;
    $data['next_id']++;

    if (saveUsers($data)) {
        return ['success' => true, 'message' => 'Kayıt başarılı! Giriş yapabilirsiniz.', 'user_id' => $newUser['id']];
    }

    return ['success' => false, 'message' => 'Bir hata oluştu. Lütfen tekrar deneyin.'];
}

// Kullanıcı girişi
function loginUser($username, $password) {
    $data = getUsers();

    foreach ($data['users'] as &$user) {
        if (strtolower($user['username']) === strtolower($username) || strtolower($user['email']) === strtolower($username)) {
            if (password_verify($password, $user['password'])) {
                // Son giriş zamanını güncelle
                $user['last_login'] = date('Y-m-d H:i:s');
                saveUsers($data);

                return [
                    'success' => true,
                    'message' => 'Giriş başarılı!',
                    'user' => [
                        'id' => $user['id'],
                        'username' => $user['username'],
                        'email' => $user['email'],
                        'points' => $user['points'],
                        'level' => $user['level'],
                        'avatar' => $user['avatar'],
                        'telegram_verified' => $user['telegram_verified'],
                        'telegram_username' => $user['telegram_username'],
                        'created_at' => $user['created_at']
                    ]
                ];
            } else {
                return ['success' => false, 'message' => 'Şifre hatalı.'];
            }
        }
    }

    return ['success' => false, 'message' => 'Kullanıcı bulunamadı.'];
}

// Kullanıcı bilgilerini getir (ID ile)
function getUserById($id) {
    $data = getUsers();

    foreach ($data['users'] as $user) {
        if ($user['id'] == $id) {
            return [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'points' => $user['points'],
                'level' => $user['level'],
                'avatar' => $user['avatar'],
                'telegram_verified' => $user['telegram_verified'],
                'telegram_username' => $user['telegram_username'],
                'telegram_id' => $user['telegram_id'],
                'created_at' => $user['created_at'],
                'last_login' => $user['last_login'],
                'total_messages' => $user['total_messages'] ?? 0
            ];
        }
    }

    return null;
}

// Kullanıcı güncelle
function updateUser($id, $updates) {
    $data = getUsers();

    foreach ($data['users'] as &$user) {
        if ($user['id'] == $id) {
            foreach ($updates as $key => $value) {
                if ($key !== 'id' && $key !== 'password') {
                    $user[$key] = $value;
                }
            }
            saveUsers($data);
            return ['success' => true, 'message' => 'Profil güncellendi.'];
        }
    }

    return ['success' => false, 'message' => 'Kullanıcı bulunamadı.'];
}

// Şifre değiştir
function changePassword($id, $oldPassword, $newPassword) {
    $data = getUsers();

    foreach ($data['users'] as &$user) {
        if ($user['id'] == $id) {
            if (!password_verify($oldPassword, $user['password'])) {
                return ['success' => false, 'message' => 'Mevcut şifre hatalı.'];
            }

            if (strlen($newPassword) < 6) {
                return ['success' => false, 'message' => 'Yeni şifre en az 6 karakter olmalı.'];
            }

            $user['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
            saveUsers($data);
            return ['success' => true, 'message' => 'Şifre başarıyla değiştirildi.'];
        }
    }

    return ['success' => false, 'message' => 'Kullanıcı bulunamadı.'];
}

// Telegram bağlama kodu oluştur
function generateTelegramCode($userId) {
    $data = getUsers();
    $code = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));

    foreach ($data['users'] as &$user) {
        if ($user['id'] == $userId) {
            $user['telegram_code'] = $code;
            saveUsers($data);
            return $code;
        }
    }

    return null;
}

// Puan ekle
function addPoints($userId, $points, $reason = '') {
    $data = getUsers();

    foreach ($data['users'] as &$user) {
        if ($user['id'] == $userId) {
            $user['points'] += $points;

            // Seviye hesapla (her 100 puan = 1 seviye)
            $user['level'] = floor($user['points'] / 100) + 1;

            saveUsers($data);
            return ['success' => true, 'new_points' => $user['points'], 'new_level' => $user['level']];
        }
    }

    return ['success' => false];
}

// Seviye hesapla
function calculateLevel($points) {
    return floor($points / 100) + 1;
}

// Sonraki seviye için gereken puan
function pointsToNextLevel($points) {
    $currentLevel = calculateLevel($points);
    $nextLevelPoints = $currentLevel * 100;
    return $nextLevelPoints - $points;
}

// Session başlat ve kontrol et
function initUserSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

// Kullanıcı giriş yapmış mı?
function isLoggedIn() {
    initUserSession();
    return isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0;
}

// Giriş yapmış kullanıcıyı getir
function getCurrentUser() {
    initUserSession();
    if (!isLoggedIn()) {
        return null;
    }
    return getUserById($_SESSION['user_id']);
}

// Oturumu başlat
function startUserSession($user) {
    initUserSession();
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['login_time'] = time();
}

// Oturumu sonlandır
function endUserSession() {
    initUserSession();
    session_unset();
    session_destroy();
}
