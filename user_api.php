<?php
// Kullanıcı API endpoint'i
header('Content-Type: application/json; charset=utf-8');

require_once 'includes/user_functions.php';
initUserSession();

// POST verilerini al
$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? $_POST['action'] ?? '';

$response = ['success' => false, 'message' => 'Geçersiz istek.'];

switch ($action) {
    case 'register':
        $username = trim($input['username'] ?? '');
        $email = trim($input['email'] ?? '');
        $password = $input['password'] ?? '';
        $password_confirm = $input['password_confirm'] ?? '';

        if (empty($username) || empty($email) || empty($password)) {
            $response = ['success' => false, 'message' => 'Tüm alanları doldurun.'];
            break;
        }

        if ($password !== $password_confirm) {
            $response = ['success' => false, 'message' => 'Şifreler eşleşmiyor.'];
            break;
        }

        $response = registerUser($username, $email, $password);
        break;

    case 'login':
        $username = trim($input['username'] ?? '');
        $password = $input['password'] ?? '';

        if (empty($username) || empty($password)) {
            $response = ['success' => false, 'message' => 'Kullanıcı adı ve şifre gerekli.'];
            break;
        }

        $result = loginUser($username, $password);

        if ($result['success']) {
            startUserSession($result['user']);
            $response = [
                'success' => true,
                'message' => 'Giriş başarılı!',
                'user' => $result['user']
            ];
        } else {
            $response = $result;
        }
        break;

    case 'logout':
        endUserSession();
        $response = ['success' => true, 'message' => 'Çıkış yapıldı.'];
        break;

    case 'check_auth':
        if (isLoggedIn()) {
            $user = getCurrentUser();
            $response = [
                'success' => true,
                'logged_in' => true,
                'user' => $user
            ];
        } else {
            $response = [
                'success' => true,
                'logged_in' => false
            ];
        }
        break;

    case 'get_profile':
        if (!isLoggedIn()) {
            $response = ['success' => false, 'message' => 'Giriş yapmalısınız.'];
            break;
        }

        $user = getCurrentUser();
        if ($user) {
            $response = [
                'success' => true,
                'user' => $user,
                'points_to_next_level' => pointsToNextLevel($user['points'])
            ];
        } else {
            $response = ['success' => false, 'message' => 'Kullanıcı bulunamadı.'];
        }
        break;

    case 'update_profile':
        if (!isLoggedIn()) {
            $response = ['success' => false, 'message' => 'Giriş yapmalısınız.'];
            break;
        }

        $updates = [];
        if (isset($input['email'])) {
            $email = trim($input['email']);
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $response = ['success' => false, 'message' => 'Geçerli bir e-posta adresi girin.'];
                break;
            }
            $updates['email'] = $email;
        }

        if (!empty($updates)) {
            $response = updateUser($_SESSION['user_id'], $updates);
        } else {
            $response = ['success' => false, 'message' => 'Güncellenecek bilgi yok.'];
        }
        break;

    case 'change_password':
        if (!isLoggedIn()) {
            $response = ['success' => false, 'message' => 'Giriş yapmalısınız.'];
            break;
        }

        $oldPassword = $input['old_password'] ?? '';
        $newPassword = $input['new_password'] ?? '';

        if (empty($oldPassword) || empty($newPassword)) {
            $response = ['success' => false, 'message' => 'Tüm alanları doldurun.'];
            break;
        }

        $response = changePassword($_SESSION['user_id'], $oldPassword, $newPassword);
        break;

    case 'generate_telegram_code':
        if (!isLoggedIn()) {
            $response = ['success' => false, 'message' => 'Giriş yapmalısınız.'];
            break;
        }

        $code = generateTelegramCode($_SESSION['user_id']);
        if ($code) {
            $response = [
                'success' => true,
                'code' => $code,
                'message' => 'Telegram bağlama kodu oluşturuldu.'
            ];
        } else {
            $response = ['success' => false, 'message' => 'Kod oluşturulamadı.'];
        }
        break;
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
