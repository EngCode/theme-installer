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
    <title><?php _e('PDS Theme Installer - Installation Wizard', 'phenix'); ?></title>
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
                    <h1><?php _e('PDS Theme Installer', 'phenix'); ?></h1>
                    <span><?php _e('Installation Wizard', 'phenix'); ?></span>
                </div>
                <div class="installer-steps">
                    <div class="step <?php echo $current_step === 'welcome' ? 'active' : ($current_step !== 'welcome' ? 'completed' : ''); ?>">
                        <span class="step-number">1</span>
                        <span class="step-title"><?php _e('Welcome', 'phenix'); ?></span>
                    </div>
                    <div class="step <?php echo $current_step === 'plugins' ? 'active' : ($current_step === 'content' || $current_step === 'complete' ? 'completed' : ''); ?>">
                        <span class="step-number">2</span>
                        <span class="step-title"><?php _e('Plugins', 'phenix'); ?></span>
                    </div>
                    <div class="step <?php echo $current_step === 'content' ? 'active' : ($current_step === 'complete' ? 'completed' : ''); ?>">
                        <span class="step-number">3</span>
                        <span class="step-title"><?php _e('Content', 'phenix'); ?></span>
                    </div>
                    <div class="step <?php echo $current_step === 'complete' ? 'active' : ''; ?>">
                        <span class="step-number">4</span>
                        <span class="step-title"><?php _e('Complete', 'phenix'); ?></span>
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
                        <h2><?php _e('Welcome to the PDS Theme Installer Wizard', 'phenix'); ?></h2>
                        <p><?php _e('This wizard will help you install and set up the PDS Theme Installer easily. The process usually takes less than 5 minutes.', 'phenix'); ?></p>
                        
                        <div class="system-status">
                            <h3><?php _e('System Status', 'phenix'); ?></h3>
                            <div class="status-list">
                                <div class="status-item">
                                    <span class="status-label"><?php _e('WordPress Version', 'phenix'); ?>:</span>
                                    <span class="status-value good"><?php echo get_bloginfo('version'); ?></span>
                                </div>
                                <div class="status-item">
                                    <span class="status-label"><?php _e('PHP Version', 'phenix'); ?>:</span>
                                    <span class="status-value <?php echo version_compare(PHP_VERSION, '7.4', '>=') ? 'good' : 'warning'; ?>">
                                        <?php echo PHP_VERSION; ?>
                                    </span>
                                </div>
                                <div class="status-item">
                                    <span class="status-label"><?php _e('File Writing', 'phenix'); ?>:</span>
                                    <span class="status-value <?php echo wp_is_writable(WP_CONTENT_DIR) ? 'good' : 'error'; ?>">
                                        <?php echo wp_is_writable(WP_CONTENT_DIR) ? __('Available', 'phenix') : __('Not Available', 'phenix'); ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="step-actions">
                            <a href="<?php echo esc_url(admin_url('admin.php?pds_installer=true&step=plugins')); ?>" class="btn btn-primary"><?php _e('Start Installation', 'phenix'); ?></a>
                            <a href="<?php echo esc_url(admin_url()); ?>" class="btn btn-secondary"><?php _e('Skip Wizard', 'phenix'); ?></a>
                        </div>
                    </div>
                </div>

                <?php elseif ($current_step === 'plugins') : ?>
                <!-- Plugins Step -->
                <div class="installer-step plugins-step">
                    <div class="step-content">
                        <h2><?php _e('Install Required Plugins', 'phenix'); ?></h2>
                        <p><?php _e('The theme requires some plugins to work perfectly. You can choose which plugins to install.', 'phenix'); ?></p>

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
                                                <span class="required-badge"><?php _e('(Required)', 'phenix'); ?></span>
                                            <?php endif; ?>
                                        </h4>
                                        <p><?php echo esc_html($plugin['description']); ?></p>
                                        <div class="plugin-status">
                                            <?php 
                                            if (is_plugin_active($plugin['file'])) {
                                                echo '<span style="color: #46b450;">' . __('Active', 'phenix') . '</span>';
                                            } elseif (file_exists(WP_PLUGIN_DIR . '/' . dirname($plugin['file']))) {
                                                echo '<span style="color: #ffb900;">' . __('Installed but not active', 'phenix') . '</span>';
                                            } else {
                                                echo '<span style="color: #dc3232;">' . __('Not Installed', 'phenix') . '</span>';
                                            }
                                            ?>
                                        </div>
                                        <?php if ($plugin['source'] === 'github') : ?>
                                            <small class="plugin-note"><?php _e('From GitHub - Custom Development', 'phenix'); ?></small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="install-progress" style="display: none;">
                                <div class="progress-bar">
                                    <div class="progress-fill"></div>
                                </div>
                                <div class="progress-text"><?php _e('Installing plugins...', 'phenix'); ?></div>
                            </div>

                            <div class="step-actions">
                                <button type="submit" class="btn btn-primary" id="install-plugins-btn">
                                    <?php _e('Install Selected Plugins', 'phenix'); ?>
                                </button>
                                <a href="<?php echo esc_url(admin_url('admin.php?pds_installer=true&step=content')); ?>" class="btn btn-secondary">
                                    <?php _e('Skip Plugin Installation', 'phenix'); ?>
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <?php elseif ($current_step === 'content') : ?>
                <!-- Content Step -->
                <div class="installer-step content-step">
                    <div class="step-content">
                        <h2><?php _e('Import Demo Content', 'phenix'); ?></h2>
                        <p><?php _e('You can import demo content to help you get started with the theme. This is optional and can be skipped.', 'phenix'); ?></p>

                        <div class="content-options">
                            <div class="content-preview">
                                <img src="<?php echo esc_url(get_template_directory_uri()); ?>/screenshot.png" alt="<?php _e('Demo Content Preview', 'phenix'); ?>">
                            </div>
                            
                            <div class="content-details">
                                <h3><?php _e('What will be imported?', 'phenix'); ?></h3>
                                <ul>
                                    <li><?php _e('Design System Settings', 'phenix'); ?></li>
                                    <li><?php _e('Site Editor Settings', 'phenix'); ?></li>
                                    <li><?php _e('Demo posts/content with images', 'phenix'); ?></li>
                                    <li><?php _e('Main pages (Home, About Us, Contact Us)', 'phenix'); ?></li>
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
                                <div class="progress-text"><?php _e('Importing content...', 'phenix'); ?></div>
                            </div>

                            <div class="step-actions">
                                <button type="submit" class="btn btn-primary" id="import-content-btn">
                                    <?php _e('Import Demo Content', 'phenix'); ?>
                                </button>
                                <a href="<?php echo esc_url(admin_url('admin.php?pds_installer=true&step=complete')); ?>" class="btn btn-secondary">
                                    <?php _e('Skip and Finish Installation', 'phenix'); ?>
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
                        <h2><?php _e('Congratulations! The theme was installed successfully', 'phenix'); ?></h2>
                        <p><?php _e('The PDS Theme Installer has been set up successfully. You can now start building website.', 'phenix'); ?></p>

                        <div class="next-steps">
                            <h3><?php _e('Next Steps', 'phenix'); ?></h3>
                            <div class="steps-grid">
                                <!-- Phenix Settings -->
                                <div class="next-step-item">
                                    <div class="step-icon">📝</div>
                                    <h4><?php _e('Design System Settings', 'phenix'); ?></h4>
                                    <p><?php _e('Customize your site features and fonts from the design system', 'phenix'); ?></p>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=pds-admin')); ?>" class="step-link">
                                        <?php _e('Open Settings', 'phenix'); ?>
                                    </a>
                                </div>
                                <!-- Pages -->
                                <div class="next-step-item">
                                    <div class="step-icon">📄</div>
                                    <h4><?php _e('Create Pages', 'phenix'); ?></h4>
                                    <p><?php _e('Create new pages or edit existing ones', 'phenix'); ?></p>
                                    <a href="<?php echo esc_url(admin_url('edit.php?post_type=page')); ?>" class="step-link">
                                        <?php _e('Manage Pages', 'phenix'); ?>
                                    </a>
                                </div>
                                <!-- Editor -->
                                <div class="next-step-item">
                                    <div class="step-icon">🎨</div>
                                    <h4><?php _e('Site Editor', 'phenix'); ?></h4>
                                    <p><?php _e('Edit templates and design your site and its colors.', 'phenix'); ?></p>
                                    <a href="<?php echo esc_url(admin_url('site-editor.php')); ?>" class="step-link">
                                        <?php _e('Open Gutenberg Editor', 'phenix'); ?>
                                    </a>
                                </div>
                                <!-- Classic Menus -->
                                <div class="next-step-item">
                                    <div class="step-icon">🗂️</div>
                                    <h4><?php _e('Dynamic Menus', 'phenix'); ?></h4>
                                    <p><?php _e('Manage your site\'s classic menus', 'phenix'); ?></p>
                                    <a href="<?php echo esc_url(admin_url('nav-menus.php')); ?>" class="step-link">
                                        <?php _e('Open Menus', 'phenix'); ?>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="step-actions">
                            <a href="<?php echo esc_url(home_url()); ?>" class="btn btn-primary">
                                <?php _e('Visit Site', 'phenix'); ?>
                            </a>
                            <a href="<?php echo esc_url(admin_url()); ?>" class="btn btn-secondary">
                                <?php _e('Dashboard', 'phenix'); ?>
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
                <p>&copy; <?php echo date('Y'); ?> PDS Theme Installer. <?php _e('All rights reserved.', 'phenix'); ?></p>
                <p><?php _e('Developed by Phenix Team', 'phenix'); ?></p>
            </div>
        </footer>
    </div>
    <!-- Installer Scripts -->
    <script src="<?php echo esc_url($installer_url); ?>/installer.js"></script>
</body>
</html>