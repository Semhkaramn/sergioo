<?php
// Profil Sayfası
require_once 'includes/user_functions.php';
initUserSession();

// Giriş kontrolü
if (!isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$currentUser = getCurrentUser();
$pointsToNextLevel = pointsToNextLevel($currentUser['points']);
$levelProgress = ($currentUser['points'] % 100);

// JSON veriyi oku (header için)
$data_file = 'data/sites.json';
$data = [];
if (file_exists($data_file)) {
    $data = json_decode(file_get_contents($data_file), true);
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="shortcut icon" href="img/siteicon.png?v=<?php echo time(); ?>" />
    <title>Profilim - Harley Casino</title>
    <meta name="robots" content="noindex, nofollow"/>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />

    <!-- Styles -->
    <link rel="stylesheet" href="assets/css/style.css?v=<?= time() ?>" />
    <link rel="stylesheet" href="assets/css/auth.css?v=<?= time() ?>" />

    <style>
        .profile-page {
            min-height: 100vh;
            padding: 120px 0 60px;
        }

        .profile-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .profile-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            font-weight: 700;
            color: white;
            margin: 0 auto 20px;
            box-shadow: 0 10px 40px var(--primary-glow);
            border: 4px solid var(--bg-card);
            position: relative;
        }

        .profile-avatar::after {
            content: '';
            position: absolute;
            inset: -6px;
            border-radius: 50%;
            border: 2px solid var(--primary);
            opacity: 0.5;
        }

        .profile-username {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 8px;
        }

        .profile-email {
            color: var(--text-muted);
            font-size: 1rem;
            margin-bottom: 15px;
        }

        .profile-badges {
            display: flex;
            justify-content: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .badge-level {
            background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
            color: white;
        }

        .badge-telegram {
            background: rgba(0, 136, 204, 0.2);
            color: #0088cc;
            border: 1px solid rgba(0, 136, 204, 0.3);
        }

        .badge-telegram.connected {
            background: rgba(34, 197, 94, 0.2);
            color: #22c55e;
            border-color: rgba(34, 197, 94, 0.3);
        }

        .profile-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: var(--bg-card);
            border: 1px solid var(--border-subtle);
            border-radius: 20px;
            padding: 25px;
            text-align: center;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            border-color: var(--primary);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--bg-glass);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 1.3rem;
            color: var(--primary);
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 5px;
        }

        .stat-label {
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        .profile-sections {
            display: flex;
            flex-direction: column;
            gap: 30px;
        }

        .profile-section {
            background: var(--bg-card);
            border: 1px solid var(--border-subtle);
            border-radius: 20px;
            padding: 30px;
        }

        .section-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .section-title i {
            color: var(--primary);
        }

        /* Level Progress */
        .level-progress {
            margin-top: 20px;
        }

        .level-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 0.9rem;
        }

        .level-current {
            color: var(--text-primary);
            font-weight: 600;
        }

        .level-next {
            color: var(--text-muted);
        }

        .progress-bar {
            height: 12px;
            background: var(--bg-darker);
            border-radius: 6px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--primary), var(--accent));
            border-radius: 6px;
            transition: width 0.5s ease;
        }

        .points-needed {
            text-align: center;
            margin-top: 10px;
            color: var(--text-muted);
            font-size: 0.85rem;
        }

        /* Telegram Section */
        .telegram-connect {
            text-align: center;
            padding: 30px;
        }

        .telegram-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, #0088cc 0%, #00a0dc 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 2.5rem;
            color: white;
            box-shadow: 0 10px 30px rgba(0, 136, 204, 0.3);
        }

        .telegram-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 10px;
        }

        .telegram-desc {
            color: var(--text-muted);
            margin-bottom: 25px;
            line-height: 1.6;
        }

        .telegram-code-box {
            background: var(--bg-darker);
            border: 2px dashed var(--border-subtle);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .telegram-code {
            font-size: 2rem;
            font-weight: 700;
            letter-spacing: 4px;
            color: #0088cc;
            font-family: monospace;
        }

        .telegram-code-hint {
            font-size: 0.85rem;
            color: var(--text-muted);
            margin-top: 10px;
        }

        .telegram-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 14px 28px;
            background: linear-gradient(135deg, #0088cc 0%, #00a0dc 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .telegram-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 136, 204, 0.4);
        }

        .telegram-connected {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            padding: 20px;
            background: rgba(34, 197, 94, 0.1);
            border: 1px solid rgba(34, 197, 94, 0.3);
            border-radius: 12px;
        }

        .telegram-connected i {
            font-size: 1.5rem;
            color: #22c55e;
        }

        .telegram-connected-info {
            text-align: left;
        }

        .telegram-connected-name {
            font-weight: 600;
            color: var(--text-primary);
        }

        .telegram-connected-status {
            font-size: 0.85rem;
            color: #22c55e;
        }

        /* Settings Form */
        .settings-form .form-group {
            margin-bottom: 20px;
        }

        .settings-form .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-secondary);
            font-size: 0.9rem;
        }

        .settings-form .form-input {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid var(--border-subtle);
            border-radius: 12px;
            background: var(--bg-darker);
            color: var(--text-primary);
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .settings-form .form-input:focus {
            outline: none;
            border-color: var(--primary);
        }

        .settings-form .form-input:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .settings-btn {
            padding: 14px 28px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .settings-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px var(--primary-glow);
        }

        .settings-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        /* Activity List */
        .activity-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .activity-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            background: var(--bg-glass);
            border-radius: 12px;
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--bg-darker);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
        }

        .activity-info {
            flex: 1;
        }

        .activity-text {
            color: var(--text-primary);
            font-size: 0.95rem;
        }

        .activity-time {
            color: var(--text-muted);
            font-size: 0.8rem;
        }

        .activity-points {
            font-weight: 600;
            color: #22c55e;
        }

        .no-activity {
            text-align: center;
            padding: 30px;
            color: var(--text-muted);
        }

        /* Back Link */
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--text-muted);
            text-decoration: none;
            margin-bottom: 30px;
            font-size: 0.95rem;
            transition: color 0.3s ease;
        }

        .back-link:hover {
            color: var(--primary);
        }

        /* Alert */
        .profile-alert {
            padding: 14px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .profile-alert-success {
            background: rgba(34, 197, 94, 0.1);
            border: 1px solid rgba(34, 197, 94, 0.3);
            color: #22c55e;
        }

        .profile-alert-error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #ef4444;
        }

        @media (max-width: 768px) {
            .profile-page {
                padding: 100px 0 40px;
            }

            .profile-avatar {
                width: 100px;
                height: 100px;
                font-size: 2.5rem;
            }

            .profile-username {
                font-size: 1.6rem;
            }

            .stat-card {
                padding: 20px;
            }

            .profile-section {
                padding: 20px;
            }
        }
    </style>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<body>
    <!-- HEADER -->
    <header class="site-header scrolled">
        <div class="container">
            <div class="header-inner">
                <a href="/" class="header-brand">
                    <img src="img/logo.png" alt="Harley Casino" class="header-logo-img" />
                    <div class="header-brand-text">
                        <span class="brand-name">Harley <span class="brand-accent">Casino</span></span>
                        <span class="brand-tagline">Güvenilir Sponsorlar</span>
                    </div>
                </a>

                <div class="header-user" id="headerUser">
                    <div class="user-info">
                        <span class="user-name"><?= htmlspecialchars($currentUser['username']) ?></span>
                        <span class="user-points"><i class="fas fa-star"></i> <?= number_format($currentUser['points']) ?> Puan</span>
                    </div>
                    <div class="user-avatar" id="userAvatarBtn">
                        <?= strtoupper(substr($currentUser['username'], 0, 1)) ?>
                    </div>
                    <div class="user-dropdown" id="userDropdown">
                        <div class="dropdown-header">
                            <span class="level-badge"><i class="fas fa-trophy"></i> Seviye <?= $currentUser['level'] ?></span>
                        </div>
                        <a href="profile.php" class="dropdown-item">
                            <i class="fas fa-user"></i>
                            <span>Profilim</span>
                        </a>
                        <a href="profile.php#telegram" class="dropdown-item">
                            <i class="fab fa-telegram"></i>
                            <span>Telegram Bağla</span>
                        </a>
                        <button class="dropdown-item logout" id="logoutBtn">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Çıkış Yap</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main class="profile-page">
        <div class="profile-container">
            <a href="index.php" class="back-link">
                <i class="fas fa-arrow-left"></i>
                Ana Sayfaya Dön
            </a>

            <div id="profileAlert"></div>

            <!-- Profile Header -->
            <div class="profile-header">
                <div class="profile-avatar">
                    <?= strtoupper(substr($currentUser['username'], 0, 1)) ?>
                </div>
                <h1 class="profile-username"><?= htmlspecialchars($currentUser['username']) ?></h1>
                <p class="profile-email"><?= htmlspecialchars($currentUser['email']) ?></p>
                <div class="profile-badges">
                    <span class="badge badge-level">
                        <i class="fas fa-trophy"></i>
                        Seviye <?= $currentUser['level'] ?>
                    </span>
                    <?php if ($currentUser['telegram_verified']): ?>
                    <span class="badge badge-telegram connected">
                        <i class="fab fa-telegram"></i>
                        Telegram Bağlı
                    </span>
                    <?php else: ?>
                    <span class="badge badge-telegram">
                        <i class="fab fa-telegram"></i>
                        Telegram Bağlanmadı
                    </span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Stats -->
            <div class="profile-stats">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="stat-value"><?= number_format($currentUser['points']) ?></div>
                    <div class="stat-label">Toplam Puan</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <div class="stat-value"><?= $currentUser['level'] ?></div>
                    <div class="stat-label">Seviye</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-comment"></i>
                    </div>
                    <div class="stat-value"><?= number_format($currentUser['total_messages'] ?? 0) ?></div>
                    <div class="stat-label">Telegram Mesajı</div>
                </div>
            </div>

            <div class="profile-sections">
                <!-- Level Progress -->
                <div class="profile-section">
                    <h2 class="section-title">
                        <i class="fas fa-chart-line"></i>
                        Seviye İlerlemesi
                    </h2>
                    <div class="level-progress">
                        <div class="level-info">
                            <span class="level-current">Seviye <?= $currentUser['level'] ?></span>
                            <span class="level-next">Seviye <?= $currentUser['level'] + 1 ?></span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?= $levelProgress ?>%;"></div>
                        </div>
                        <p class="points-needed">
                            Sonraki seviyeye <strong><?= $pointsToNextLevel ?></strong> puan kaldı
                        </p>
                    </div>
                </div>

                <!-- Telegram Connection -->
                <div class="profile-section" id="telegram">
                    <h2 class="section-title">
                        <i class="fab fa-telegram"></i>
                        Telegram Bağlantısı
                    </h2>

                    <?php if ($currentUser['telegram_verified']): ?>
                    <div class="telegram-connected">
                        <i class="fab fa-telegram"></i>
                        <div class="telegram-connected-info">
                            <div class="telegram-connected-name">@<?= htmlspecialchars($currentUser['telegram_username']) ?></div>
                            <div class="telegram-connected-status">Telegram hesabınız bağlı</div>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="telegram-connect">
                        <div class="telegram-icon">
                            <i class="fab fa-telegram"></i>
                        </div>
                        <h3 class="telegram-title">Telegram Hesabını Bağla</h3>
                        <p class="telegram-desc">
                            Telegram grubumuzda mesaj yazarak puan kazan!<br>
                            Her mesaj için puan kazanırsın.
                        </p>

                        <div class="telegram-code-box" id="telegramCodeBox" style="display: none;">
                            <div class="telegram-code" id="telegramCode"></div>
                            <p class="telegram-code-hint">Bu kodu Telegram botumuza gönderin</p>
                        </div>

                        <button class="telegram-btn" id="generateCodeBtn">
                            <i class="fas fa-key"></i>
                            Bağlama Kodu Al
                        </button>

                        <p class="telegram-desc" style="margin-top: 20px; font-size: 0.85rem;">
                            Kod aldıktan sonra <a href="#" target="_blank" style="color: #0088cc;">@HarleyCasinoBot</a>'a bu kodu gönderin.
                        </p>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Account Settings -->
                <div class="profile-section">
                    <h2 class="section-title">
                        <i class="fas fa-cog"></i>
                        Hesap Ayarları
                    </h2>
                    <form class="settings-form" id="settingsForm">
                        <div class="form-group">
                            <label class="form-label">Kullanıcı Adı</label>
                            <input type="text" class="form-input" value="<?= htmlspecialchars($currentUser['username']) ?>" disabled>
                        </div>
                        <div class="form-group">
                            <label class="form-label">E-posta Adresi</label>
                            <input type="email" class="form-input" name="email" value="<?= htmlspecialchars($currentUser['email']) ?>">
                        </div>
                        <button type="submit" class="settings-btn">
                            <i class="fas fa-save"></i>
                            Değişiklikleri Kaydet
                        </button>
                    </form>
                </div>

                <!-- Change Password -->
                <div class="profile-section">
                    <h2 class="section-title">
                        <i class="fas fa-lock"></i>
                        Şifre Değiştir
                    </h2>
                    <form class="settings-form" id="passwordForm">
                        <div class="form-group">
                            <label class="form-label">Mevcut Şifre</label>
                            <input type="password" class="form-input" name="old_password" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Yeni Şifre</label>
                            <input type="password" class="form-input" name="new_password" required minlength="6">
                        </div>
                        <button type="submit" class="settings-btn">
                            <i class="fas fa-key"></i>
                            Şifre Değiştir
                        </button>
                    </form>
                </div>

                <!-- Account Info -->
                <div class="profile-section">
                    <h2 class="section-title">
                        <i class="fas fa-info-circle"></i>
                        Hesap Bilgileri
                    </h2>
                    <div class="activity-list">
                        <div class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-calendar-plus"></i>
                            </div>
                            <div class="activity-info">
                                <div class="activity-text">Kayıt Tarihi</div>
                                <div class="activity-time"><?= date('d.m.Y H:i', strtotime($currentUser['created_at'])) ?></div>
                            </div>
                        </div>
                        <?php if ($currentUser['last_login']): ?>
                        <div class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-sign-in-alt"></i>
                            </div>
                            <div class="activity-info">
                                <div class="activity-text">Son Giriş</div>
                                <div class="activity-time"><?= date('d.m.Y H:i', strtotime($currentUser['last_login'])) ?></div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="site-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-brand">
                    <img src="img/logo.png" alt="Harley Casino" />
                    <p>Güvenilir bahis siteleri ve sponsorlarınız için tek adres.</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>Copyright &copy; <?= date('Y') ?> Harley Casino</p>
            </div>
        </div>
    </footer>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const profileAlert = document.getElementById('profileAlert');
        const userAvatarBtn = document.getElementById('userAvatarBtn');
        const userDropdown = document.getElementById('userDropdown');
        const logoutBtn = document.getElementById('logoutBtn');
        const settingsForm = document.getElementById('settingsForm');
        const passwordForm = document.getElementById('passwordForm');
        const generateCodeBtn = document.getElementById('generateCodeBtn');
        const telegramCodeBox = document.getElementById('telegramCodeBox');
        const telegramCode = document.getElementById('telegramCode');

        function showAlert(message, type = 'success') {
            profileAlert.innerHTML = `
                <div class="profile-alert profile-alert-${type}">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
                    <span>${message}</span>
                </div>
            `;
            setTimeout(() => {
                profileAlert.innerHTML = '';
            }, 5000);
        }

        // User dropdown
        if (userAvatarBtn) {
            userAvatarBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                userDropdown.classList.toggle('active');
            });

            document.addEventListener('click', function(e) {
                if (!userDropdown.contains(e.target) && e.target !== userAvatarBtn) {
                    userDropdown.classList.remove('active');
                }
            });
        }

        // Logout
        if (logoutBtn) {
            logoutBtn.addEventListener('click', async function() {
                try {
                    const response = await fetch('user_api.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'logout' })
                    });
                    const result = await response.json();
                    if (result.success) {
                        window.location.href = 'index.php';
                    }
                } catch (error) {
                    console.error('Logout error:', error);
                }
            });
        }

        // Settings form
        if (settingsForm) {
            settingsForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                const formData = new FormData(this);

                try {
                    const response = await fetch('user_api.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            action: 'update_profile',
                            email: formData.get('email')
                        })
                    });
                    const result = await response.json();
                    showAlert(result.message, result.success ? 'success' : 'error');
                } catch (error) {
                    showAlert('Bir hata oluştu.', 'error');
                }
            });
        }

        // Password form
        if (passwordForm) {
            passwordForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                const formData = new FormData(this);

                try {
                    const response = await fetch('user_api.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            action: 'change_password',
                            old_password: formData.get('old_password'),
                            new_password: formData.get('new_password')
                        })
                    });
                    const result = await response.json();
                    showAlert(result.message, result.success ? 'success' : 'error');
                    if (result.success) {
                        this.reset();
                    }
                } catch (error) {
                    showAlert('Bir hata oluştu.', 'error');
                }
            });
        }

        // Generate Telegram code
        if (generateCodeBtn) {
            generateCodeBtn.addEventListener('click', async function() {
                try {
                    const response = await fetch('user_api.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'generate_telegram_code' })
                    });
                    const result = await response.json();

                    if (result.success) {
                        telegramCode.textContent = result.code;
                        telegramCodeBox.style.display = 'block';
                        this.innerHTML = '<i class="fas fa-sync"></i> Yeni Kod Al';
                    } else {
                        showAlert(result.message, 'error');
                    }
                } catch (error) {
                    showAlert('Bir hata oluştu.', 'error');
                }
            });
        }

        // Scroll to hash on load
        if (window.location.hash) {
            const element = document.querySelector(window.location.hash);
            if (element) {
                setTimeout(() => {
                    element.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }, 300);
            }
        }
    });
    </script>
</body>
</html>
