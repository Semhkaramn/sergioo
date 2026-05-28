<?php
// JSON veriyi oku
$data_file = 'data/sites.json';
$data = [];

if (file_exists($data_file)) {
    $data = json_decode(file_get_contents($data_file), true);
}

$hero = $data['hero'] ?? [];
$social_links = $data['social_links'] ?? [];
$banners = $data['banners'] ?? [];
$sections = $data['sections'] ?? [];
$categories = $data['categories'] ?? [];
$popup = $data['popup'] ?? [];
$sites = array_filter($data['sites'] ?? [], function($site) {
    return $site['active'] === true;
});

// Türkçe karakterleri küçük harfe çeviren fonksiyon
function turkishToLower($text) {
    $search  = array('İ', 'I', 'Ğ', 'Ü', 'Ş', 'Ö', 'Ç');
    $replace = array('i', 'ı', 'ğ', 'ü', 'ş', 'ö', 'ç');
    $text = str_replace($search, $replace, $text);
    return mb_strtolower($text, 'UTF-8');
}

// Açıklamalardaki br etiketlerini satır sonlarına çeviren fonksiyon
function convertBrToNewlines($text) {
    if (empty($text)) return $text;
    $text = preg_replace('/<br\s*\/?>/i', "\n", $text);
    $text = htmlspecialchars($text);
    $text = nl2br($text);
    return $text;
}

// Section'lara göre siteleri ayır
$section1_sites = [];
$section2_sites = [];

if (isset($categories['section1'])) {
    foreach ($categories['section1'] as $siteId) {
        foreach ($sites as $site) {
            if ($site['id'] == $siteId) {
                $section1_sites[] = $site;
                break;
            }
        }
    }
}

if (isset($categories['section2'])) {
    foreach ($categories['section2'] as $siteId) {
        foreach ($sites as $site) {
            if ($site['id'] == $siteId) {
                $section2_sites[] = $site;
                break;
            }
        }
    }
}

// Banner'ları sıralama için sıralayıp siteyle eşleştir (sadece aktif banner'lar)
usort($banners, function($a, $b) {
    $orderA = $a['order'] ?? 999;
    $orderB = $b['order'] ?? 999;
    return $orderA - $orderB;
});

$banner_sites = [];
foreach ($banners as $banner) {
    if (isset($banner['active']) && $banner['active']) {
        foreach ($sites as $site) {
            if ($site['id'] == $banner['site_id']) {
                $banner_sites[] = array_merge($banner, ['site' => $site]);
                break;
            }
        }
    }
}

// Sosyal medya platform isimleri
$social_names = [
    'telegram' => 'Telegram',
    'instagram' => 'Instagram',
    'youtube' => 'YouTube',
    'twitter' => 'Twitter',
    'discord' => 'Discord',
    'whatsapp' => 'WhatsApp',
    'facebook' => 'Facebook',
    'tiktok' => 'TikTok'
];

// Quicklinks verisi
$active_slots = [];
if (isset($data['slots'])) {
    foreach ($data['slots'] as $slot) {
        if (isset($slot['active']) && $slot['active']) {
            $active_slots[] = $slot;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="shortcut icon" href="img/siteicon.png?v=<?php echo time(); ?>" id="site-favicon" />
    <title>Harley Casino - Güvenilir Sponsor Bahis Siteleri</title>
    <meta name="robots" content="max-image-preview:large"/>
    <meta name="description" content="Harley Casino - Güvenilir bahis siteleri ve sponsorlar"/>
    
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-EBPHRH4R76"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
    
      gtag('config', 'G-EBPHRH4R76');
    </script>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />

    <!-- Ana Stil Dosyası -->
    <link rel="stylesheet" href="assets/css/style.css?v=<?= time() ?>" />

    <!-- Loading Screen Styles -->
    <style>
        .page-loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #0c1929 0%, #152238 50%, #0c1929 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            transition: opacity 0.5s ease, visibility 0.5s ease;
        }
        .page-loader.hidden {
            opacity: 0;
            visibility: hidden;
        }
        .loader-content {
            text-align: center;
        }
        .loader-neon-text {
            font-family: 'Outfit', sans-serif;
            font-size: clamp(2rem, 8vw, 4rem);
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 4px;
            color: #fff;
            text-shadow:
                0 0 5px #ef4444,
                0 0 10px #ef4444,
                0 0 20px #ef4444,
                0 0 40px #ef4444,
                0 0 80px #ef4444;
            animation: neonPulse 1.5s ease-in-out infinite, neonFlicker 3s linear infinite;
        }
        .loader-neon-text span {
            color: #3b82f6;
            text-shadow:
                0 0 5px #3b82f6,
                0 0 10px #3b82f6,
                0 0 20px #3b82f6,
                0 0 40px #3b82f6,
                0 0 80px #3b82f6;
        }
        .loader-subtitle {
            margin-top: 15px;
            font-family: 'Outfit', sans-serif;
            font-size: 0.9rem;
            font-weight: 500;
            letter-spacing: 6px;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.6);
            animation: fadeInUp 1s ease-out 0.3s both;
        }
        .loader-line {
            width: 100px;
            height: 3px;
            background: linear-gradient(90deg, transparent, #ef4444, #3b82f6, #ef4444, transparent);
            margin: 25px auto 0;
            border-radius: 2px;
            animation: lineGlow 1.5s ease-in-out infinite;
        }
        @keyframes neonPulse {
            0%, 100% {
                opacity: 1;
                transform: scale(1);
            }
            50% {
                opacity: 0.9;
                transform: scale(1.02);
            }
        }
        @keyframes neonFlicker {
            0%, 19%, 21%, 23%, 25%, 54%, 56%, 100% {
                text-shadow:
                    0 0 5px #ef4444,
                    0 0 10px #ef4444,
                    0 0 20px #ef4444,
                    0 0 40px #ef4444,
                    0 0 80px #ef4444;
            }
            20%, 24%, 55% {
                text-shadow: none;
            }
        }
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        @keyframes lineGlow {
            0%, 100% {
                opacity: 0.5;
                transform: scaleX(0.8);
            }
            50% {
                opacity: 1;
                transform: scaleX(1);
            }
        }
    </style>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<body>
    <!-- Loading Screen -->
    <div class="page-loader" id="pageLoader">
        <div class="loader-content">
            <div class="loader-neon-text">Harley <span>Casino</span></div>
            <div class="loader-subtitle">Güvenilir Sponsorlar</div>
            <div class="loader-line"></div>
        </div>
    </div>

    <!-- HEADER -->
    <header class="site-header">
        <div class="container">
            <div class="header-inner">
                <a href="/" class="header-brand">
                    <img src="img/logo.png" alt="Harley Casino" class="header-logo-img" />
                    <div class="header-brand-text">
                        <span class="brand-name">Harley <span class="brand-accent">Casino</span></span>
                        <span class="brand-tagline">Güvenilir Sponsorlar</span>
                    </div>
                </a>


            </div>
        </div>
    </header>



    <main>
        <!-- Search Section -->
        <section class="section search-section">
            <div class="container">
                <div class="search-wrapper">
                    <div class="search-input-wrapper">
                        <input type="text" class="search-input" id="searchInput" placeholder="Site ara..." autocomplete="off">
                        <i class="fas fa-search search-icon"></i>
                        <button class="search-clear" id="searchClear">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <!-- Arama Sonuçları Dropdown -->
                    <div class="search-results" id="searchResults">
                        <div class="search-results-grid">
                            <?php foreach ($sites as $site): ?>
                            <a href="<?= htmlspecialchars($site['link']) ?>" target="_blank" class="search-result-item" data-name="<?= htmlspecialchars(turkishToLower($site['name'])) ?>" data-name-original="<?= htmlspecialchars($site['name']) ?>">
                                <div class="search-result-logo">
                                    <img src="img/logo/<?= htmlspecialchars($site['logo']) ?>" alt="<?= htmlspecialchars($site['name']) ?>" />
                                </div>
                                <div class="search-result-info">
                                    <span class="search-result-name"><?= htmlspecialchars($site['name']) ?></span>
                                </div>
                            </a>
                            <?php endforeach; ?>
                        </div>
                        <div class="search-no-result" id="searchNoResult">
                            <i class="fas fa-search"></i>
                            <span>Sonuç bulunamadı</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Banners -->
        <?php if (!empty($banner_sites)): ?>
        <section class="section section-banners">
            <div class="container">
                <div class="banners-grid">
                    <?php foreach ($banner_sites as $index => $banner): ?>
                    <a href="<?= htmlspecialchars($banner['link']) ?>" target="_blank" class="banner-card" data-banner-logo="img/logo/<?= htmlspecialchars($banner['site']['logo']) ?>">
                        <div class="banner-glow"></div>
                        <div class="banner-content">
                            <div class="banner-text">
                                <?= $banner['title'] ?>
                            </div>
                            <div class="banner-logo">
                                <img src="img/logo/<?= htmlspecialchars($banner['site']['logo']) ?>" alt="<?= htmlspecialchars($banner['site']['name']) ?>" />
                            </div>
                        </div>

                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <!-- Section 1 - Ana Sponsorlar -->
        <section class="section section-sponsors" id="section1">
            <div class="container">
                <div class="section-header">
                    <h2 class="section-title">
                        <?= $sections['section1']['title'] ?? 'ANA <span>Sponsorlar</span>' ?>
                    </h2>
                    <div class="section-line"></div>
                </div>
                <div class="sponsors-grid sponsors-grid-main">
                    <?php foreach ($section1_sites as $index => $site): ?>
                    <div class="sponsor-card" data-logo="img/logo/<?= htmlspecialchars($site['logo']) ?>" data-name="<?= htmlspecialchars(turkishToLower($site['name'])) ?>">
                        <div class="card-shine"></div>
                        <div class="card-border"></div>
                        <div class="card-particles"></div>
                        <div class="card-content">

                            <div class="card-logo">
                                <img src="img/logo/<?= htmlspecialchars($site['logo']) ?>" alt="<?= htmlspecialchars($site['name']) ?>" />
                            </div>
                            <div class="card-info">
                                <div class="card-divider"></div>
                                <p class="card-desc"><?= convertBrToNewlines($site['description']) ?></p>
                            </div>
                            <a href="<?= htmlspecialchars($site['link']) ?>" target="_blank" class="card-btn" data-site-id="<?= $site['id'] ?>">
                                <span>Siteye Git</span>
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="no-results" id="noResults1">
                    <i class="fas fa-search"></i>
                    <h3>Sonuç Bulunamadı</h3>
                    <p>Aradığınız kriterlere uygun site bulunamadı.</p>
                </div>
            </div>
        </section>

        <!-- Section 2 - VIP Sponsorlar -->
        <?php if (!empty($section2_sites)): ?>
        <section class="section section-vip" id="section2">
            <div class="container">
                <div class="section-header section-header-vip">
                    <h2 class="section-title section-title-vip">
                        <?= $sections['section2']['title'] ?? 'VIP <span>Sponsorlar</span>' ?>
                    </h2>
                    <div class="section-line section-line-vip"></div>
                </div>
                <div class="sponsors-grid sponsors-grid-vip">
                    <?php foreach ($section2_sites as $index => $site): ?>
                    <div class="sponsor-card sponsor-card-vip" data-logo="img/logo/<?= htmlspecialchars($site['logo']) ?>" data-name="<?= htmlspecialchars(turkishToLower($site['name'])) ?>">
                        <div class="vip-badge"><i class="fas fa-star"></i> VIP</div>
                        <div class="card-shine card-shine-vip"></div>
                        <div class="card-border card-border-vip"></div>
                        <div class="card-particles"></div>
                        <div class="card-content">
                            <div class="card-logo card-logo-vip">
                                <img src="img/logo/<?= htmlspecialchars($site['logo']) ?>" alt="<?= htmlspecialchars($site['name']) ?>" />
                            </div>
                            <div class="card-info">
                                <div class="card-divider card-divider-vip"></div>
                                <p class="card-desc"><?= convertBrToNewlines($site['description']) ?></p>
                            </div>
                            <a href="<?= htmlspecialchars($site['link']) ?>" target="_blank" class="card-btn card-btn-vip" data-site-id="<?= $site['id'] ?>">
                                <span>Hemen Katıl</span>
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="no-results" id="noResults2">
                    <i class="fas fa-search"></i>
                    <h3>Sonuç Bulunamadı</h3>
                    <p>Aradığınız kriterlere uygun site bulunamadı.</p>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <!-- Quicklinks Section - Now at the bottom -->
        <?php if (!empty($active_slots)): ?>
        <section class="section section-quicklinks">
            <div class="container">
                <div class="section-header">
                    <h2 class="section-title section-title-quicklinks">
                        Hızlı <span>Bağlantılar</span>
                    </h2>
                    <div class="section-line"></div>
                </div>
                <div class="quicklinks-grid">
                    <?php foreach ($active_slots as $slot): ?>
                    <a href="<?= htmlspecialchars($slot['link']) ?>" target="_blank" class="quicklink-card">
                        <div class="quicklink-icon">
                            <i class="fab fa-<?= $slot['type'] ?>"></i>
                        </div>
                        <div class="quicklink-content">
                            <div class="quicklink-title"><?= $slot['title'] ?></div>
                            <div class="quicklink-text"><?= htmlspecialchars($slot['text']) ?></div>
                        </div>

                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>
    </main>

    <?php
    // Popup
    if (!empty($popup) && isset($popup['active']) && $popup['active'] && isset($popup['site_id'])):
        $popupSite = null;
        foreach ($sites as $site) {
            if ($site['id'] == $popup['site_id']) {
                $popupSite = $site;
                break;
            }
        }

        $popupNumber = $popup['number'] ?? '5 0 0 T L';
        $popupTitle = $popup['title'] ?? 'D E N E M E';
        $popupSubtitle = $popup['subtitle'] ?? 'B O N U S U';

        if ($popupSite):
    ?>
    <div class="popup-overlay" id="popup" aria-hidden="true">
        <div class="popup-container">
            <button class="popup-close" id="popupClose"><i class="fas fa-times"></i></button>
            <div class="popup-content">
                <div class="popup-glow"></div>
                <div class="popup-logo">
                    <img src="img/logo/<?= htmlspecialchars($popupSite['logo']) ?>" alt="<?= htmlspecialchars($popupSite['name']) ?>" />
                </div>
                <div class="popup-number"><?= htmlspecialchars($popupNumber) ?></div>
                <div class="popup-title"><?= htmlspecialchars($popupTitle) ?></div>
                <div class="popup-subtitle"><?= htmlspecialchars($popupSubtitle) ?></div>
                <a href="<?= htmlspecialchars($popupSite['link']) ?>" target="_blank" class="popup-btn">
                    <span>Hemen Katıl</span>
                </a>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <?php endif; ?>

    <footer class="site-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-brand">
                    <img src="img/logo.png" alt="Harley Casino" />
                    <p>Güvenilir bahis siteleri ve sponsorlarınız için tek adres.</p>
                </div>


            </div>
            <div class="footer-bottom">
                <p>Copyright &copy; <?= date('Y') ?> Harley Casino - <a href="https://t.me/xangeiletisim" target="_blank">Xange İletişim</a></p>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="assets/js/libs.js"></script>
    <script>
    // Türkçe karakterleri küçük harfe çeviren fonksiyon
    function turkishToLower(str) {
        const charMap = {
            'İ': 'i', 'I': 'ı', 'Ğ': 'ğ', 'Ü': 'ü', 'Ş': 'ş', 'Ö': 'ö', 'Ç': 'ç',
            'i': 'i', 'ı': 'ı', 'ğ': 'ğ', 'ü': 'ü', 'ş': 'ş', 'ö': 'ö', 'ç': 'ç'
        };
        let result = '';
        for (let i = 0; i < str.length; i++) {
            const char = str[i];
            result += charMap[char] || char.toLowerCase();
        }
        return result;
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Hide loading screen
        const pageLoader = document.getElementById('pageLoader');

        // Wait for images and colors to be extracted
        setTimeout(() => {
            pageLoader.classList.add('hidden');
            setTimeout(() => {
                pageLoader.style.display = 'none';
            }, 500);
        }, 800);



        // Search Functionality with Turkish character support
        const searchInput = document.getElementById('searchInput');
        const searchClear = document.getElementById('searchClear');
        const sponsorCards = document.querySelectorAll('.sponsor-card');
        const noResults1 = document.getElementById('noResults1');
        const noResults2 = document.getElementById('noResults2');
        const searchResults = document.getElementById('searchResults');
        const searchNoResult = document.getElementById('searchNoResult');
        const searchResultItems = document.querySelectorAll('.search-result-item');

        function filterCards(searchTerm) {
            searchTerm = turkishToLower(searchTerm.trim());
            let section1Visible = 0;
            let section2Visible = 0;

            sponsorCards.forEach(card => {
                const name = card.getAttribute('data-name') || '';
                const isVip = card.classList.contains('sponsor-card-vip');

                if (searchTerm === '' || name.includes(searchTerm)) {
                    card.classList.remove('hidden');
                    if (isVip) section2Visible++;
                    else section1Visible++;
                } else {
                    card.classList.add('hidden');
                }
            });

            // Show/hide no results messages
            if (noResults1) {
                noResults1.classList.toggle('visible', section1Visible === 0 && searchTerm !== '');
            }
            if (noResults2) {
                noResults2.classList.toggle('visible', section2Visible === 0 && searchTerm !== '');
            }

            // Show/hide clear button
            searchClear.classList.toggle('visible', searchTerm !== '');
        }

        // Search Dropdown with Turkish character support
        function filterSearchDropdown(searchTerm) {
            searchTerm = turkishToLower(searchTerm.trim());
            let found = 0;
            searchResultItems.forEach(item => {
                const name = item.getAttribute('data-name') || '';
                if (searchTerm !== '' && name.includes(searchTerm)) {
                    item.style.display = '';
                    found++;
                } else {
                    item.style.display = 'none';
                }
            });
            if (searchTerm === '') {
                searchResults.classList.remove('active');
            } else {
                searchResults.classList.add('active');
            }
            if (found === 0 && searchTerm !== '') {
                searchNoResult.classList.add('visible');
            } else {
                searchNoResult.classList.remove('visible');
            }
        }

        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                filterCards(e.target.value);
                filterSearchDropdown(e.target.value);
            });
            searchInput.addEventListener('focus', (e) => {
                if (searchInput.value.trim() !== '') {
                    searchResults.classList.add('active');
                }
            });
            searchInput.addEventListener('blur', (e) => {
                setTimeout(() => {
                    searchResults.classList.remove('active');
                }, 200);
            });
        }

        if (searchClear) {
            searchClear.addEventListener('click', () => {
                searchInput.value = '';
                filterCards('');
                filterSearchDropdown('');
                searchInput.focus();
            });
        }

        // Search result item click closes dropdown
        searchResultItems.forEach(item => {
            item.addEventListener('click', () => {
                searchResults.classList.remove('active');
            });
        });

        // Popup
        const popup = document.getElementById('popup');
        const popupClose = document.getElementById('popupClose');

        if (popup) {
            // Show popup after 2 seconds
            setTimeout(() => {
                popup.classList.add('active');
            }, 2000);

            if (popupClose) {
                popupClose.addEventListener('click', () => {
                    popup.classList.remove('active');
                });
            }

            // Close on overlay click
            popup.addEventListener('click', (e) => {
                if (e.target === popup) {
                    popup.classList.remove('active');
                }
            });
        }

        // Header scroll effect
        const header = document.querySelector('.site-header');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });

        // Smooth scroll
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Card color extraction from logo
        function extractColors() {
            document.querySelectorAll('.sponsor-card').forEach(card => {
                const logoImg = card.querySelector('.card-logo img');
                if (logoImg && logoImg.complete) {
                    setCardColor(card, logoImg);
                } else if (logoImg) {
                    logoImg.addEventListener('load', () => setCardColor(card, logoImg));
                }
            });
            // Banner renk çıkarma
            document.querySelectorAll('.banner-card').forEach(card => {
                const logoImg = card.querySelector('.banner-logo img');
                if (logoImg && logoImg.complete) {
                    setBannerColor(card, logoImg);
                } else if (logoImg) {
                    logoImg.addEventListener('load', () => setBannerColor(card, logoImg));
                }
            });
        }

        function setCardColor(card, img) {
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            canvas.width = img.naturalWidth || 100;
            canvas.height = img.naturalHeight || 100;

            try {
                ctx.drawImage(img, 0, 0);
                const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height).data;

                let colorCounts = {};
                for (let i = 0; i < imageData.length; i += 4) {
                    const r = imageData[i];
                    const g = imageData[i + 1];
                    const b = imageData[i + 2];
                    const a = imageData[i + 3];

                    // Skip transparent, white-ish, and black-ish colors
                    if (a < 128) continue;
                    if (r > 200 && g > 200 && b > 200) continue;
                    if (r < 30 && g < 30 && b < 30) continue;

                    // Check if it's a colorful pixel
                    const max = Math.max(r, g, b);
                    const min = Math.min(r, g, b);
                    if (max - min < 30) continue; // Skip gray tones

                    const key = `${Math.round(r/10)*10},${Math.round(g/10)*10},${Math.round(b/10)*10}`;
                    colorCounts[key] = (colorCounts[key] || 0) + 1;
                }

                // Find dominant color
                let dominantColor = null;
                let maxCount = 0;
                for (const [color, count] of Object.entries(colorCounts)) {
                    if (count > maxCount) {
                        maxCount = count;
                        dominantColor = color;
                    }
                }

                if (dominantColor) {
                    const [r, g, b] = dominantColor.split(',').map(Number);
                    card.style.setProperty('--card-accent', `rgb(${r}, ${g}, ${b})`);
                    card.style.setProperty('--card-accent-light', `rgba(${r}, ${g}, ${b}, 0.15)`);
                    card.style.setProperty('--card-accent-glow', `rgba(${r}, ${g}, ${b}, 0.4)`);
                }
            } catch (e) {
                // CORS or other error - use default color
            }
        }

        // Banner renk çıkarma fonksiyonu
        function setBannerColor(card, img) {
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            canvas.width = img.naturalWidth || 100;
            canvas.height = img.naturalHeight || 100;

            try {
                ctx.drawImage(img, 0, 0);
                const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height).data;

                let colorCounts = {};
                for (let i = 0; i < imageData.length; i += 4) {
                    const r = imageData[i];
                    const g = imageData[i + 1];
                    const b = imageData[i + 2];
                    const a = imageData[i + 3];

                    // Skip transparent, white-ish, and black-ish colors
                    if (a < 128) continue;
                    if (r > 200 && g > 200 && b > 200) continue;
                    if (r < 30 && g < 30 && b < 30) continue;

                    // Check if it's a colorful pixel
                    const max = Math.max(r, g, b);
                    const min = Math.min(r, g, b);
                    if (max - min < 30) continue; // Skip gray tones

                    const key = `${Math.round(r/10)*10},${Math.round(g/10)*10},${Math.round(b/10)*10}`;
                    colorCounts[key] = (colorCounts[key] || 0) + 1;
                }

                // Find dominant color
                let dominantColor = null;
                let maxCount = 0;
                for (const [color, count] of Object.entries(colorCounts)) {
                    if (count > maxCount) {
                        maxCount = count;
                        dominantColor = color;
                    }
                }

                if (dominantColor) {
                    const [r, g, b] = dominantColor.split(',').map(Number);
                    card.style.setProperty('--banner-accent', `rgb(${r}, ${g}, ${b})`);
                    card.style.setProperty('--banner-accent-light', `rgba(${r}, ${g}, ${b}, 0.15)`);
                    card.style.setProperty('--banner-accent-glow', `rgba(${r}, ${g}, ${b}, 0.4)`);
                }
            } catch (e) {
                // CORS or other error - use default color
            }
        }

        // Run color extraction
        setTimeout(extractColors, 300);

        // Analytics Tracking
        fetch('admin/api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'track_visit' })
        }).catch(() => {});

        // Track clicks
        document.querySelectorAll('.card-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const siteId = this.getAttribute('data-site-id');
                const section = this.closest('#section1') ? 'section1' : 'section2';
                if (siteId) {
                    fetch('admin/api.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'track_click', site_id: parseInt(siteId), location: section })
                    }).catch(() => {});
                }
            });
        });
    });
    </script>
</body>
</html>
