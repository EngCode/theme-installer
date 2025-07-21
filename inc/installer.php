<?php
/**
 * PDS Theme Installer
 * Entry point - Logic only
 * @since Phenix Blocks 1.3
*/

if (!defined('ABSPATH')) : die('You are not allowed to call this page directly.'); endif;

//=====> Installer Admin Page Only <=====//
add_action('admin_init', function() {
    //=====> Start output buffering to prevent header issues <=====//
    ob_start();
    //=====> Check if installer is requested <=====//
    if (isset($_GET['pds_installer']) && $_GET['pds_installer'] === 'true') {
        //====> Clean any output before including template <=====//
        ob_clean();
        include dirname(__FILE__) . '/installer/template.php';
    }
});

//=====> Add Custom Link for the Installer pointing at admin.php?pds_installer=true <=====//
add_action('admin_menu', function() {
    //====> Add a submenu page under Appearance <=====//
    add_theme_page(__('Theme Installer', 'phenix'),__('Theme Installer', 'phenix'),'manage_options','admin.php?pds_installer=true');
});

//=====> Plugin Functions <=====//
if (!function_exists('pds_get_required_plugins')) :
    function pds_get_required_plugins() {
        //====> Get theme installer configuration <=====//
        $config = pds_theme_installer_config();
        $plugins = array();
        
        foreach ($config as $key => $plugin) {
            $plugins[] = array(
                'key' => $key,
                'name' => $plugin['name'],
                'slug' => $plugin['slug'],
                'file' => isset($plugin['file']) ? $plugin['file'] : '',
                'source' => isset($plugin['source']) ? $plugin['source'] : 'wordpress',
                'required' => isset($plugin['required']) ? $plugin['required'] : false,
                'description' => isset($plugin['description']) ? $plugin['description'] : '',
            );
        }
        
        return $plugins;
    }
endif;

//=====> Plugin Installer Ajax Handler <=====//
if (!function_exists('pds_ajax_install_plugins')) :
    function pds_ajax_install_plugins() {
        //====> Check security <=====//
        if (!wp_verify_nonce($_POST['nonce'], 'pds_installer_nonce')) { wp_die('Invalid nonce'); }

        //====> Get plugin list <=====//
        $plugins = isset($_POST['plugins']) ? $_POST['plugins'] : array();
        $results = array();

        //====> Load Files System from WP <=====//
        if (!function_exists('request_filesystem_credentials')) { require_once(ABSPATH . 'wp-admin/includes/file.php'); }
        //====> Load Plugin Upgrader <=====//
        if (!class_exists('Plugin_Upgrader')) { require_once(ABSPATH . 'wp-admin/includes/class-wp-upgrader.php'); }

        //====> Install each plugin <=====//
        foreach ($plugins as $plugin_slug) {
            //====> Get theme installer configuration <=====//
            $config = pds_theme_installer_config();
            $plugin_info = isset($config[$plugin_slug]) ? $config[$plugin_slug] : false;

            //====> Check if plugin info exists <=====//
            if (!$plugin_info) {
                $results[$plugin_slug] = array('status' => 'error', 'message' => 'Plugin not found');
                continue;
            }

            //====> Check if already installed <=====//
            if (is_plugin_active($plugin_info['file'])) {
                $results[$plugin_slug] = array('status' => 'success', 'message' => 'Already active');
                continue;
            }

            //====> Install plugin <=====//
            $upgrader = new Plugin_Upgrader(new WP_Ajax_Upgrader_Skin());
            $install_result = $upgrader->install('https://downloads.wordpress.org/plugin/'.$plugin_info['slug'].'.latest-stable.zip');

            //====> Check installation result <=====//
            if (!is_wp_error($install_result) && $install_result !== false) {
                //====> Activate plugin <=====//
                $activate = activate_plugin($plugin_info['file']);
                //====> Activation Success <=====//
                if (!is_wp_error($activate)) {
                    $results[$plugin_slug] = array('status' => 'success', 'message' => 'Installed and activated');
                } 
                //====> Activation Error <=====//
                else {
                    $results[$plugin_slug] = array('status' => 'error', 'message' => $activate->get_error_message());
                }
            }

            //====> Check installation Errors <=====//
            else {
                $error_message = is_wp_error($install_result) ? $install_result->get_error_message() : 'Installation failed';
                $results[$plugin_slug] = array('status' => 'error', 'message' => $error_message);
            }
        }

        //====> Sending JSON <=====//
        wp_send_json_success($results);
    }

    add_action('wp_ajax_pds_install_plugins', 'pds_ajax_install_plugins');
endif;

//===> Phenix Settings Import & Theme Activation <===//
if (!function_exists('pds_theme_activation_redirect')) :
    function pds_theme_activation_redirect() {
        //===> Install Phenix Blocks Plugin First <===//
        pds_install_phenix_blocks_plugin();
        
        //===> Get Settings <===//
        $default_options = wp_remote_get(get_template_directory_uri() . '/data/pds-options.json');
        //===> Check for Errors <===//
        if (is_wp_error($default_options)) { return; }

        //===> Decode JSON Response <===//
        $default_options = json_decode(wp_remote_retrieve_body($default_options));
        $default_options = (array) $default_options;

        //===> Update Options <===//
        if ($default_options && is_array($default_options)) {
            //===> Update Options <===//
            foreach ($default_options as $key => $value) { update_option($key, $value); }
        }

        //===> Only redirect if installer hasn't been completed <===//
        if (!get_option('pds_installer_completed', false) && !isset($_GET['pds_installer'])) {
            //===> Set permalink structure to post name <===//
            update_option('permalink_structure', '/%postname%/');
            //===> Flush rewrite rules <===//
            flush_rewrite_rules();
            //===> Redirect to Installer Page <===//
            wp_safe_redirect(admin_url('admin.php?pds_installer=true'));
        }
    }
    //===> Hook to Theme Activation <===//
    add_action('after_switch_theme', 'pds_theme_activation_redirect');
endif;

//===> Install Phenix Blocks Plugin from GitHub <===//
if (!function_exists('pds_install_phenix_blocks_plugin')) :
    function pds_install_phenix_blocks_plugin() {
        //===> Check if Plugin is Already Installed <===//
        if (is_plugin_active('pds-blocks/pds-blocks.php') || is_plugin_active('phenix-blocks-main/pds-blocks.php')) {
            return true;
        }
        
        //===> Include Files Manager <===//
        if (!function_exists('request_filesystem_credentials')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }
        //===> Include Plugin Upgrader <===//
        if (!class_exists('Plugin_Upgrader')) {
            require_once(ABSPATH . 'wp-admin/includes/class-wp-upgrader.php');
        }
        
        //===> Plugin Details <===//
        $plugin_zip = 'https://github.com/EngCode/phenix-blocks/archive/refs/heads/main.zip';
        $plugin_slug = 'pds-blocks';
        
        //===> Create a Silent Upgrader <===//
        $upgrader = new Plugin_Upgrader(new WP_Ajax_Upgrader_Skin());
        
        //===> Install Plugin <===//
        $result = $upgrader->install($plugin_zip);
        
        //===> Activate Plugin if Installation was Successful <===//
        if (!is_wp_error($result) && $result !== false) {
            //===> Try to Activate <===//
            $activate = activate_plugin('pds-blocks/pds-blocks.php');
            $activate_gb = activate_plugin('phenix-blocks-main/pds-blocks.php');

            //===> Check Activation Result <===//
            if (!is_wp_error($activate) || !is_wp_error($activate_gb)) {
                return true;
            }
        }
        
        //===> Log Error if Installation Failed <===//
        if (is_wp_error($result)) {
            error_log('PDS Theme Installer: Failed to install Phenix Blocks plugin - ' . $result->get_error_message());
        }
        
        return false;
    }
endif;

//=====> Import Demo Content Ajax Handler <=====//
if (!function_exists('pds_ajax_import_content')) :
    function pds_ajax_import_content() {
        //====> Check security <=====//
        if (!wp_verify_nonce($_POST['nonce'], 'pds_installer_nonce')) { wp_die('Invalid nonce'); }

        //====> Include Required Files for Attachment Uploading <=====//
        require_once ABSPATH . 'wp-admin/includes/image.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';

        //====> Define errors and success messages <=====//
        $errors = array(); $success = array();

        //====> Get the Content XML file path <=====//
        $xml_file = dirname(__DIR__) . '/data/content-data.xml';
        if (!file_exists($xml_file)) {
            $errors[] = __('Content XML file not found.', 'phenix');
            wp_send_json_error(array('message' => implode(' ', $errors)));
            return;
        }

        //====> Load and Parse the XML file <=====//
        libxml_use_internal_errors(true);
        $xml = simplexml_load_file($xml_file);
        if ($xml === false) {
            $errors[] = __('Failed to parse XML file.', 'phenix');
            wp_send_json_error(array('message' => implode(' ', $errors)));
            return;
        }

        //====> Detect Old and New Site URLs <=====//
        $old_url = '';
        $new_url = get_site_url();
        if (isset($xml->channel->link)) $old_url = (string) $xml->channel->link;

        //====> Import Standard Posts and Terms <=====//
        foreach ($xml->channel->item as $item) {
            $wp = $item->children('wp', true);
            $post_type = isset($wp->post_type) ? (string) $wp->post_type : 'post';
            //====> Skip Menu Items to Array <=====//
            if ($post_type === 'nav_menu_item') { continue; }
            //====> Attachment Import & Sideload Handler <=====//
            if ($post_type === 'attachment' && isset($wp->attachment_url)) {
                //====> Get File URL <=====//
                $file_url = (string)$wp->attachment_url;
                //====> Check if the URL is valid <=====//
                if ($file_url) {
                    //====> Download the file <=====//
                    $tmp = download_url($file_url);
                    $file_name = basename($file_url);
                    $file_array = array('name' => $file_name, 'tmp_name' => $tmp);
                    //====> Check for download errors <=====//
                    if (is_wp_error($tmp)) {
                        $errors[] = 'Download error for ' . $file_url . ': ' . $tmp->get_error_message();
                        continue;
                    }
                    //====> SVG Support: Allow SVG mime type <=====//
                    $is_svg = strtolower(pathinfo($file_name, PATHINFO_EXTENSION)) === 'svg';
                    if ($is_svg) {
                        add_filter('upload_mimes', function($mimes) { $mimes['svg'] = 'image/svg+xml'; return $mimes; });
                    }
                    //====> Set upload directory to original year/month <=====//
                    $original_date = isset($wp->post_date) ? (string)$wp->post_date : '';
                    $custom_upload_dir = null;
                    if ($original_date) {
                        $time = strtotime($original_date);
                        if ($time) {
                            $year = date('Y', $time);
                            $month = date('m', $time);
                            $custom_upload_dir = function($dirs) use ($year, $month) {
                                $dirs['subdir'] = "/$year/$month";
                                $dirs['path'] = $dirs['basedir'] . $dirs['subdir'];
                                $dirs['url'] = $dirs['baseurl'] . $dirs['subdir'];
                                return $dirs;
                            };
                            add_filter('upload_dir', $custom_upload_dir);
                        }
                    }
                    //====> Sideload the file and create attachment post <=====//
                    $attach_id = media_handle_sideload($file_array, 0); // unattached
                    //===> Remove SVG mime filter after upload <===//
                    if ($is_svg) { remove_filter('upload_mimes', '__return_false'); }
                    //====> Remove custom upload_dir filter <=====//
                    if ($custom_upload_dir) { remove_filter('upload_dir', $custom_upload_dir); }
                    //====> Check for errors during sideload <=====//
                    if (is_wp_error($attach_id)) {
                        $errors[] = 'Sideload error for ' . $file_url . ': ' . $attach_id->get_error_message();
                        continue;
                    }
                }
                //==> Exit <===//
                continue;
            }
            //====> Import Post Item <=====//
            pds_import_post_item($item, $wp, $old_url, $new_url);
        }

        //====> Update installer completion status <=====//
        update_option('pds_installer_completed', true);
        //====> Import menus from JSON <====//
        pds_import_menus_data();

        //====> Send simple success or error <=====//
        if ($errors) {
            wp_send_json_error(array('message' => implode(' | ', $errors)));
        } else {
            //====> Always flush rewrite rules after import <=====//
            flush_rewrite_rules();
            wp_send_json_success(array('message' => __('Content import completed.', 'phenix')));
        }
    }

    add_action('wp_ajax_pds_import_content', 'pds_ajax_import_content');
endif;

//====> Import meta fields <=====//
if (!function_exists('pds_import_meta_fields')) :
    function pds_import_meta_fields($post_id, $meta_list, $old_url, $new_url) {
        //====> Import meta fields <=====//
        if ($meta_list) {
            foreach ($meta_list as $meta) {
                $meta_key = (string)$meta->meta_key;
                $meta_value = pds_replace_url((string)$meta->meta_value, $old_url, $new_url);
                update_post_meta($post_id, $meta_key, $meta_value);
            }
        }
    }
endif;

//====> Import taxonomies <=====//
if (!function_exists('pds_import_taxonomies')) :
    function pds_import_taxonomies($post_id, $categories, $old_url, $new_url) {
        if ($categories) {
            $taxonomies = array();
            //====> Loop through categories <=====//
            foreach ($categories as $cat) {
                //===> Get domain and category name <===//
                $domain = (string) $cat['domain'];
                $cat_name = pds_replace_url((string) $cat, $old_url, $new_url);
                if (!isset($taxonomies[$domain])) $taxonomies[$domain] = array();
                $taxonomies[$domain][] = $cat_name;
                $term_obj = get_term_by('name', $cat_name, $domain);
                //====> Check if term exists <=====//
                if ($term_obj && isset($cat->termmeta)) {
                    //====> Update term meta <=====//
                    foreach ($cat->termmeta as $meta) {
                        $meta_key = (string)$meta->meta_key;
                        $meta_value = pds_replace_url((string)$meta->meta_value, $old_url, $new_url);
                        update_term_meta($term_obj->term_id, $meta_key, $meta_value);
                    }
                }
            }
            foreach ($taxonomies as $tax => $terms) {
                wp_set_object_terms($post_id, $terms, $tax);
            }
        }
    }
endif;

//====> Import any post item <=====//
if (!function_exists('pds_import_post_item')) :
    function pds_import_post_item($item, $wp, $old_url, $new_url) {
        //=====> Define Item Data <=====//
        $post_type = isset($wp->post_type) ? (string) $wp->post_type : 'post';
        $post_title = (string) $item->title;
        $post_data = array(
            'post_type'    => $post_type,
            'post_title'   => $post_title,
            'post_author'  => get_current_user_id(),
            'post_status'  => isset($wp->status) ? (string)$wp->status : 'publish',
            'post_excerpt' => pds_replace_url((string)$item->children('excerpt', true)->encoded, $old_url, $new_url),
            'post_content' => pds_replace_url((string)$item->children('content', true)->encoded, $old_url, $new_url),
        );
        //====> Handle Post Date <=====//
        foreach ($wp as $key => $value) {
            $post_data[$key] = pds_replace_url((string)$value, $old_url, $new_url);
        }
        //====> Check for existing post <=====//
        $existing = get_posts(array('post_type' => $post_type, 'title' => $post_title, 'post_status' => 'any', 'numberposts' => 1));
        if ($existing && isset($existing[0]->ID)) {
            $post_id = $existing[0]->ID;
            $post_data['ID'] = $post_id;
            wp_update_post($post_data);
        }
        //====> If no existing post, insert new <=====//
        else {
            $post_id = wp_insert_post($post_data);
        }
        //====> Check if post was inserted successfully <=====//
        if (!$post_id || is_wp_error($post_id)) return false;
        //====> Import meta fields <=====//
        pds_import_meta_fields($post_id, $wp->postmeta, $old_url, $new_url);
        //====> Store old ID in post meta <=====//
        if (isset($wp->post_id)) {
            update_post_meta($post_id, 'old_id', (string)$wp->post_id);
        } elseif (isset($wp->ID)) {
            update_post_meta($post_id, 'old_id', (string)$wp->ID);
        }
        //====> Import taxonomies <=====//
        pds_import_taxonomies($post_id, $item->category, $old_url, $new_url);
        return $post_id;
    }
endif;

//====> Import Menus from JSON after posts/terms <=====//
if (!function_exists('pds_import_menus_data')) :
    function pds_import_menus_data() {
        //====> Get Menu Data File <=====//
        $json_file = dirname(__DIR__) . '/data/menu-data.json';
        if (!file_exists($json_file)) return false;
        $json = file_get_contents($json_file);
        $menus = json_decode($json, true);
        if (!$menus || !is_array($menus)) return false;

        //====> Check if menus are empty <=====//
        foreach ($menus as $menu) {
            $menu_obj = wp_get_nav_menu_object($menu['slug']);
            $menu_id = $menu_obj ? $menu_obj->term_id : wp_create_nav_menu($menu['name']);
            $item_id_map = array();
            //====> Create Menu Items <=====//
            foreach ($menu['terms'] as $item) {
                $args = array(
                    'menu-item-title' => $item['title'],
                    'menu-item-url' => pds_replace_url($item['url'], $menu['plugin_name'] ?? '', get_site_url()),
                    'menu-item-type' => $item['type'],
                    'menu-item-object' => $item['object'],
                    'menu-item-status' => 'publish',
                    'menu-item-classes' => implode(' ', $item['classes']),
                    'menu-item-xfn' => $item['xfn'],
                    'menu-item-description' => $item['description'],
                    'menu-item-attr_title' => $item['attr_title'],
                    'menu-item-target' => $item['target'],
                );
                //====> Robust Mapping for Object ID <=====//
                if ($item['type'] === 'post_type') {
                    $post_id = 0;
                    $post_query = get_posts([
                        'post_type' => $item['object'],
                        'meta_query' => [['key' => 'old_id','value' => $item['object_id'],'compare' => '=']],
                        'post_status' => 'any',
                        'numberposts' => 1,
                        'fields' => 'ids'
                    ]);
                    //====> Check if post exists <=====//
                    if (!empty($post_query) && is_array($post_query)) {
                        $post_id = $post_query[0];
                    }
                    //====> Set Menu Item Object ID <=====//
                    if ($post_id) {
                        $args['menu-item-object-id'] = $post_id;
                    }
                }
                //====> Taxonomy Mapping with Direct Meta Query <=====//
                elseif ($item['type'] === 'taxonomy') {
                    //====> Initialize Term ID <=====//
                    $term_id = 0;
                    $taxonomy = $item['object'];
                    $args['menu-item-object-id'] = 0;
                    //====> Extract slug from the end of the URL <=====//
                    $slug = '';
                    $url = isset($item['url']) ? $item['url'] : '';

                    //====> Extract slug from the end of the URL <=====//
                    if ($url) {
                        $parts = explode('/', trim($url, '/'));
                        $slug = urldecode(end($parts));
                    }

                    //====> Check if slug is valid and taxonomy exists <=====//
                    if ($slug && taxonomy_exists($taxonomy)) {
                        //====> Get Term by Slug <=====//
                        $term = get_term_by('slug', $slug, $taxonomy);
                        //====> Check if term exists <=====//
                        if ($term && !is_wp_error($term)) {
                            $term_id = $term->term_id;
                        }
                        //====> Set Menu Item Object ID <=====//
                        $args['menu-item-object-id'] = $term_id;
                        $args['menu-item-object'] = $taxonomy;
                        $args['menu-item-type'] = 'taxonomy';
                    }
                } 
                //====> Post Type Archive Mapping <=====//
                elseif ($item['type'] === 'post_type_archive' && post_type_exists($item['object'])) {
                    $args['menu-item-object-id'] = 0;
                    $args['menu-item-type'] = 'post_type_archive';
                }
                //====> Default Object ID for Other Types <=====//
                else {
                    $args['menu-item-object-id'] = 0;
                }
                //====> Parent Mapping <=====//
                $parent = 0;
                if (!empty($item['menu_item_parent']) && isset($item_id_map[$item['menu_item_parent']])) {
                    $parent = $item_id_map[$item['menu_item_parent']];
                }
                $args['menu-item-parent-id'] = $parent;
                //====> Insert Menu Item <=====//
                $menu_item_id = wp_update_nav_menu_item($menu_id, 0, $args);
                if ($menu_item_id && !is_wp_error($menu_item_id)) {
                    $item_id_map[$item['ID']] = $menu_item_id;
                }
            }
            //====> Attach Menu to Theme Location <=====//
            $locations = get_theme_mod('nav_menu_locations');
            if (!is_array($locations)) $locations = array();
            //====> Assign menu to locations from JSON if available <====//
            if (isset($menu['locations']) && is_array($menu['locations'])) {
                foreach ($menu['locations'] as $loc) {
                    $locations[$loc] = $menu_id;
                }
            } else {
                //====> Always assign by slug, no fallback or check <====//
                $locations[$menu['slug']] = $menu_id;
            }
            //====> Update Theme Locations <=====//
            set_theme_mod('nav_menu_locations', $locations);
        }
        return true;
    }
endif;

//====> URL Replace Helper <=====//
if (!function_exists('pds_replace_url')) :
    function pds_replace_url($value, $old_url, $new_url) {
        //====> Get uploads directory <=====//
        $uploads = wp_upload_dir();
        $current_upload_baseurl = $uploads['baseurl'];

        //====> Regex: match old uploads base URL, with or without /sites/{number} <=====//
        $old_uploads_pattern = '#'.preg_quote(rtrim($old_url, '/'), '#').'/wp-content/uploads(?:/sites/\\d+)?#i';
        $value = preg_replace($old_uploads_pattern, $current_upload_baseurl, $value);

        //====> Replace old site URL with new site URL <=====//
        $value = str_replace($old_url, $new_url, $value);

        return $value;
    }
endif;