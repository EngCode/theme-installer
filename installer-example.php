<?php
//===> make sure Plugins are loaded <===//
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

//=====> Theme Installer Configuration <=====//
function pds_theme_installer_config() {
    //====> Required Plugins <====//
    return array(
        //====> Contact Form 7 <====//
        'contact-form-7' => array(
            'required' => false,
            'name' => 'Contact Form 7',
            'slug' => 'contact-form-7',
            'file' => 'contact-form-7/wp-contact-form-7.php',
            'description' => __('إضافة نماذج الاتصال المطلوبة للموقع', 'phenix'),
        ),
        //====> YOAST SEO <====//
        'yoast-seo' => array(
            'required' => false,
            'name' => 'Yoast SEO',
            'slug' => 'wordpress-seo',
            'file' => 'wordpress-seo/wp-seo.php',
            'description' => __('إضافة تحسين محركات البحث', 'phenix'),
        )
    );
}

//=====> Theme Installer <=====//
include_once(dirname(__FILE__) . '/inc/installer.php');