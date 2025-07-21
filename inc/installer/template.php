<?php
    /**
     * PDS Theme Installer Template
     * Main HTML layout for the installer interface
     * @since PDS Theme 1.0
    */

    if (!defined('ABSPATH')) : die('You are not allowed to call this page directly.'); endif;
    //=====> Get Current Step <=====//
    $current_step = isset($_GET['step']) ? sanitize_key($_GET['step']) : 'welcome';
    //=====> Get Installer URL <=====//
    $installer_url = get_template_directory_uri() . '/inc/installer';
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php _e('PDS Multimedia - معالج التثبيت', 'phenix'); ?></title>
    <!-- Installer Styles -->
    <link rel="stylesheet" href="<?php echo esc_url($installer_url); ?>/installer.css">
</head>
<body class="pds-installer-body" dir="<?php echo is_rtl() ? 'rtl' : 'ltr'; ?>">
    <!-- Installer Wrapper -->
    <div class="pds-installer-wrapper" style="margin: 50px auto; max-width: 1100px;">
        <!-- Header -->
        <header class="pds-installer-header">
            <div class="container">
                <div class="installer-logo">
                    <h1><?php _e('PDS Multimedia', 'phenix'); ?></h1>
                    <span><?php _e('معالج التثبيت', 'phenix'); ?></span>
                </div>
                <div class="installer-steps">
                    <div class="step <?php echo $current_step === 'welcome' ? 'active' : ($current_step !== 'welcome' ? 'completed' : ''); ?>">
                        <span class="step-number">1</span>
                        <span class="step-title"><?php _e('مرحباً', 'phenix'); ?></span>
                    </div>
                    <div class="step <?php echo $current_step === 'plugins' ? 'active' : ($current_step === 'content' || $current_step === 'complete' ? 'completed' : ''); ?>">
                        <span class="step-number">2</span>
                        <span class="step-title"><?php _e('الإضافات', 'phenix'); ?></span>
                    </div>
                    <div class="step <?php echo $current_step === 'content' ? 'active' : ($current_step === 'complete' ? 'completed' : ''); ?>">
                        <span class="step-number">3</span>
                        <span class="step-title"><?php _e('المحتوى', 'phenix'); ?></span>
                    </div>
                    <div class="step <?php echo $current_step === 'complete' ? 'active' : ''; ?>">
                        <span class="step-number">4</span>
                        <span class="step-title"><?php _e('مكتمل', 'phenix'); ?></span>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="pds-installer-main">
            <div class="container">
                <?php if ($current_step === 'welcome') : ?>
                <!-- Welcome Step -->
                <div class="installer-step welcome-step">
                    <div class="step-content">
                        <div class="welcome-icon">
                            <svg width="80" height="80" viewBox="0 0 24 24" fill="none">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" fill="currentColor"/>
                            </svg>
                        </div>
                        <h2><?php _e('مرحباً بك في معالج تثبيت قالب PDS Multimedia', 'phenix'); ?></h2>
                        <p><?php _e('سيساعدك هذا المعالج على تثبيت وإعداد قالب PDS Multimedia بسهولة. العملية تستغرق عادة أقل من 5 دقائق.', 'phenix'); ?></p>
                        
                        <div class="system-status">
                            <h3><?php _e('حالة النظام', 'phenix'); ?></h3>
                            <div class="status-list">
                                <div class="status-item">
                                    <span class="status-label"><?php _e('إصدار WordPress', 'phenix'); ?>:</span>
                                    <span class="status-value good"><?php echo get_bloginfo('version'); ?></span>
                                </div>
                                <div class="status-item">
                                    <span class="status-label"><?php _e('إصدار PHP', 'phenix'); ?>:</span>
                                    <span class="status-value <?php echo version_compare(PHP_VERSION, '7.4', '>=') ? 'good' : 'warning'; ?>">
                                        <?php echo PHP_VERSION; ?>
                                    </span>
                                </div>
                                <div class="status-item">
                                    <span class="status-label"><?php _e('كتابة الملفات', 'phenix'); ?>:</span>
                                    <span class="status-value <?php echo wp_is_writable(WP_CONTENT_DIR) ? 'good' : 'error'; ?>">
                                        <?php echo wp_is_writable(WP_CONTENT_DIR) ? __('مُتاح', 'phenix') : __('غير مُتاح', 'phenix'); ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="step-actions">
                            <a href="<?php echo esc_url(admin_url('admin.php?pds_installer=true&step=plugins')); ?>" class="btn btn-primary"><?php _e('البدء في التثبيت', 'phenix'); ?></a>
                            <a href="<?php echo esc_url(admin_url()); ?>" class="btn btn-secondary"><?php _e('تخطي المعالج', 'phenix'); ?></a>
                        </div>
                    </div>
                </div>

                <?php elseif ($current_step === 'plugins') : ?>
                <!-- Plugins Step -->
                <div class="installer-step plugins-step">
                    <div class="step-content">
                        <h2><?php _e('تثبيت الإضافات المطلوبة', 'phenix'); ?></h2>
                        <p><?php _e('يحتاج القالب إلى بعض الإضافات للعمل بشكل مثالي. يمكنك اختيار الإضافات التي تريد تثبيتها.', 'phenix'); ?></p>

                        <form id="plugins-form" class="plugins-form">
                            <?php wp_nonce_field('pds_installer_nonce', 'pds_nonce'); ?>
                            <input type="hidden" name="pds_ajax_url" value="<?php echo admin_url('admin-ajax.php'); ?>" />

                            <div class="plugins-list">
                                <?php foreach (pds_get_required_plugins() as $plugin) : ?>
                                <div class="plugin-item <?php echo ($plugin['source'] === 'github' || $plugin['required']) ? 'special-plugin' : ''; ?>">
                                    <div class="plugin-checkbox">
                                        <input type="checkbox" 
                                            id="plugin_<?php echo esc_attr($plugin['key']); ?>" 
                                            name="plugins[]" 
                                            value="<?php echo esc_attr($plugin['key']); ?>"
                                            <?php echo $plugin['required'] ? 'checked disabled' : 'checked'; ?>>
                                        <label for="plugin_<?php echo esc_attr($plugin['key']); ?>"></label>
                                    </div>
                                    <div class="plugin-info">
                                        <h4>
                                            <?php echo esc_html($plugin['name']); ?>
                                            <?php if ($plugin['required']) : ?>
                                                <span class="required-badge"><?php _e('(مطلوب)', 'phenix'); ?></span>
                                            <?php endif; ?>
                                        </h4>
                                        <p><?php echo esc_html($plugin['description']); ?></p>
                                        <div class="plugin-status">
                                            <?php 
                                            if (is_plugin_active($plugin['file'])) {
                                                echo '<span style="color: #46b450;">' . __('مُفعل', 'phenix') . '</span>';
                                            } elseif (file_exists(WP_PLUGIN_DIR . '/' . dirname($plugin['file']))) {
                                                echo '<span style="color: #ffb900;">' . __('مُثبت غير مُفعل', 'phenix') . '</span>';
                                            } else {
                                                echo '<span style="color: #dc3232;">' . __('غير مُثبت', 'phenix') . '</span>';
                                            }
                                            ?>
                                        </div>
                                        <?php if ($plugin['source'] === 'github') : ?>
                                            <small class="plugin-note"><?php _e('من GitHub - تطوير خاص', 'phenix'); ?></small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="install-progress" style="display: none;">
                                <div class="progress-bar">
                                    <div class="progress-fill"></div>
                                </div>
                                <div class="progress-text"><?php _e('جارٍ تثبيت الإضافات...', 'phenix'); ?></div>
                            </div>

                            <div class="step-actions">
                                <button type="submit" class="btn btn-primary" id="install-plugins-btn">
                                    <?php _e('تثبيت الإضافات المحددة', 'phenix'); ?>
                                </button>
                                <a href="<?php echo esc_url(admin_url('admin.php?pds_installer=true&step=content')); ?>" class="btn btn-secondary">
                                    <?php _e('تخطي تثبيت الإضافات', 'phenix'); ?>
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <?php elseif ($current_step === 'content') : ?>
                <!-- Content Step -->
                <div class="installer-step content-step">
                    <div class="step-content">
                        <h2><?php _e('استيراد المحتوى التجريبي', 'phenix'); ?></h2>
                        <p><?php _e('يمكنك استيراد محتوى تجريبي لتسهيل بداية العمل مع القالب. هذا اختياري ويمكن تخطيه.', 'phenix'); ?></p>

                        <div class="content-options">
                            <div class="content-preview">
                                <img src="<?php echo esc_url(get_template_directory_uri()); ?>/screenshot.png" alt="<?php _e('معاينة المحتوى التجريبي', 'phenix'); ?>">
                            </div>
                            
                            <div class="content-details">
                                <h3><?php _e('ماذا سيتم استيراده؟', 'phenix'); ?></h3>
                                <ul>
                                    <li><?php _e('إعدادات نظام التصميم', 'phenix'); ?></li>
                                    <li><?php _e('إعدادات محرر الموقع', 'phenix'); ?></li>
                                    <li><?php _e('مقالات/محتوي تجريبي مع الصور', 'phenix'); ?></li>
                                    <li><?php _e('صفحات رئيسية (الرئيسية، من نحن، اتصل بنا)', 'phenix'); ?></li>
                                </ul>
                            </div>
                        </div>

                        <form id="content-form" class="content-form">
                            <?php wp_nonce_field('pds_installer_nonce', 'pds_nonce'); ?>
                            <input type="hidden" name="pds_ajax_url" value="<?php echo admin_url('admin-ajax.php'); ?>" />
                            
                            <div class="import-progress" style="display: none;">
                                <div class="progress-bar">
                                    <div class="progress-fill"></div>
                                </div>
                                <div class="progress-text"><?php _e('جارٍ استيراد المحتوى...', 'phenix'); ?></div>
                            </div>

                            <div class="step-actions">
                                <button type="submit" class="btn btn-primary" id="import-content-btn">
                                    <?php _e('استيراد المحتوى التجريبي', 'phenix'); ?>
                                </button>
                                <a href="<?php echo esc_url(admin_url('admin.php?pds_installer=true&step=complete')); ?>" class="btn btn-secondary">
                                    <?php _e('تخطي وإنهاء التثبيت', 'phenix'); ?>
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <?php elseif ($current_step === 'complete') : ?>
                <!-- Complete Step -->
                <div class="installer-step complete-step">
                    <div class="step-content">
                        <div class="success-icon">
                            <svg width="100" height="100" viewBox="0 0 24 24" fill="none">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" fill="currentColor"/>
                            </svg>
                        </div>
                        <h2><?php _e('تهانينا! تم تثبيت القالب بنجاح', 'phenix'); ?></h2>
                        <p><?php _e('تم إعداد قالب PDS Multimedia بنجاح. يمكنك الآن البدء في بناء موقعك الإلكتروني.', 'phenix'); ?></p>

                        <div class="next-steps">
                            <h3><?php _e('الخطوات التالية', 'phenix'); ?></h3>
                            <div class="steps-grid">
                                <!-- Phenix Settings -->
                                <div class="next-step-item">
                                    <div class="step-icon">📝</div>
                                    <h4><?php _e('اعدادات نظام التصميم', 'phenix'); ?></h4>
                                    <p><?php _e('قم بتخصيص ميزات و خطوط الموقع من نظام التصميم', 'phenix'); ?></p>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=pds-admin')); ?>" class="step-link">
                                        <?php _e('فتح الاعدادات', 'phenix'); ?>
                                    </a>
                                </div>
                                <!-- Pages -->
                                <div class="next-step-item">
                                    <div class="step-icon">📄</div>
                                    <h4><?php _e('إنشاء الصفحات', 'phenix'); ?></h4>
                                    <p><?php _e('أنشئ صفحات جديدة أو حرر الموجودة', 'phenix'); ?></p>
                                    <a href="<?php echo esc_url(admin_url('edit.php?post_type=page')); ?>" class="step-link">
                                        <?php _e('إدارة الصفحات', 'phenix'); ?>
                                    </a>
                                </div>
                                <!-- Editor -->
                                <div class="next-step-item">
                                    <div class="step-icon">🎨</div>
                                    <h4><?php _e('محرر الموقع', 'phenix'); ?></h4>
                                    <p><?php _e('لتحرير القوالب وتصميم موقعك والوانة.', 'phenix'); ?></p>
                                    <a href="<?php echo esc_url(admin_url('site-editor.php')); ?>" class="step-link">
                                        <?php _e('فتح محرر Gutenberg', 'phenix'); ?>
                                    </a>
                                </div>
                                <!-- Classic Menus -->
                                <div class="next-step-item">
                                    <div class="step-icon">🗂️</div>
                                    <h4><?php _e('القوائم الدينامك', 'phenix'); ?></h4>
                                    <p><?php _e('قم بإدارة القوائم الكلاسيكية لموقعك', 'phenix'); ?></p>
                                    <a href="<?php echo esc_url(admin_url('nav-menus.php')); ?>" class="step-link">
                                        <?php _e('فتح القوائم', 'phenix'); ?>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="step-actions">
                            <a href="<?php echo esc_url(home_url()); ?>" class="btn btn-primary">
                                <?php _e('زيارة الموقع', 'phenix'); ?>
                            </a>
                            <a href="<?php echo esc_url(admin_url()); ?>" class="btn btn-secondary">
                                <?php _e('لوحة التحكم', 'phenix'); ?>
                            </a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </main>

        <!-- Footer -->
        <footer class="pds-installer-footer">
            <div class="container">
                <p>&copy; <?php echo date('Y'); ?> PDS Multimedia. <?php _e('جميع الحقوق محفوظة.', 'phenix'); ?></p>
                <p><?php _e('مطور بواسطة فريق Phenix', 'phenix'); ?></p>
            </div>
        </footer>
    </div>
    <!-- Installer Scripts -->
    <script src="<?php echo esc_url($installer_url); ?>/installer.js"></script>
</body>
</html>