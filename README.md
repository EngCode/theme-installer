# PDS Theme Installer

A robust, standalone WordPress theme installer for importing all required plugins, theme settings, demo content (posts, pages, custom post types, attachments, menus, taxonomies) from XML and JSON backups. Designed for both single-site and multisite WordPress environments.

---

## Quick Start

1. **Copy Files**
   - Copy `inc/installer.php` and the `data/` folder into your theme or plugin directory.
   - Copy `inc/installer/` — Contains the installer's UI templates, CSS, JS, and supporting files for the admin interface.

2. **Add Configuration**
   - In your theme's `functions.php`, add the required configuration function:

```php
//===> Make sure Plugins are loaded <===//
include_once(ABSPATH . 'wp-admin/includes/plugin.php');

//=====> Theme Installer Configuration <=====//
function pds_theme_installer_config() {
    return array(
        // Example plugin:
        'contact-form-7' => array(
            'required' => false,
            'name' => 'Contact Form 7',
            'slug' => 'contact-form-7',
            'file' => 'contact-form-7/wp-contact-form-7.php',
            'description' => __('Contact form plugin', 'phenix'),
        ),
        // Add more plugins as needed...
    );
}

//=====> Theme Installer <=====//
include_once(dirname(__FILE__) . '/inc/installer.php');
```

3. **Initialize the Installer**
   - The installer will automatically register its admin page and AJAX handlers.
   - Access the installer via the admin page: `your-site.com/wp-admin/admin.php?pds_installer=true`

---

## Exporting Demo Content & Settings

To prepare your own demo data for use with the installer, export the following files:

- **Content Data (XML):**
  - Use the native WordPress Exporter: go to **Tools → Export** in your WordPress admin and export "All Content". Save the resulting XML file as `content-data.xml` in the `data/` folder.

- **Menu Data (JSON):**
  - Use the [Import Export Menu plugin](https://wordpress.org/plugins/import-export-menu/):
    1. Install and activate the plugin.
    2. Go to **Appearance → Menus** and use the plugin's export feature to export your menus as JSON.
    3. Save the exported file as `menu-data.json` in the `data/` folder.

- **Phenix Blocks Options (JSON):**
  - If using Phenix Blocks, export your options from the plugin:
    1. Go to **Design System → Data Manager → Import Export** in the WordPress admin.
    2. Click **Export Phenix Options** to download your settings as JSON.
    3. Save the exported file as `pds-options.json` in the `data/` folder.

You can now use these files with the installer to import your demo content, menus, and theme options.

---


## Compatibility
- **Theme Types:** Works with both Classic and Block (Full Site Editing) WordPress themes.
- **PHP Version:** Fully compatible with PHP 8 and above.
- **WordPress:** Supports both single-site and multisite installations.

---

## Features
- Imports all post types, meta, taxonomies, attachments, and menus from backup files.
- Handles menu mapping for post types and taxonomies using slugs (nicename) for reliability.
- Rewrites all URLs (including media and custom links) to match the current site, supporting both single and multisite setups.
- Supports SVG uploads and robust attachment sideloading.
- Ensures no duplicate posts or terms are created.
- AJAX-based import and plugin installation for smooth admin experience.

---

## Usage

### 1. Standalone (Recommended)
- Follow the Quick Start steps above.
- Use the admin UI to install required plugins and import demo content.

### 2. Programmatic
- You can call the main import functions directly:

```php
// Import all demo content
pds_ajax_import_content();

// Install required plugins
pds_ajax_install_plugins();
```

### 3. Customization
- To add or change required plugins, edit the `pds_theme_installer_config()` function.
- To change or add demo content, update the files in the `data/` directory.
- To customize URL rewriting, edit the `pds_replace_url()` helper in `installer.php`.

---

## Required Functions & Configuration

- `pds_theme_installer_config()` — **Required.** Defines the plugins to be installed. Must be present in your theme's `functions.php`.
- `pds_get_required_plugins()` — Used internally by the installer to fetch plugin info from your config.

---

## Disabling Phenix Blocks Integration
If you are not using the Phenix Design System for WordPress, you can safely remove or comment out any code related to Phenix Blocks plugin installation and activation. This includes:
- The `pds_install_phenix_blocks_plugin()` function
- Any calls to `pds_install_phenix_blocks_plugin()` or references to Phenix Blocks in the installer logic
- Any Phenix-specific plugin configuration in `pds_theme_installer_config()`

The installer will work independently for any theme or plugin demo import needs.

---

## File Structure
- `inc/installer.php` — Main installer logic (import, mapping, helpers)
- `data/content-data.xml` — Demo content (posts, pages, custom post types, attachments)
- `data/menu-data.json` — Menu structure and items
- `data/pds-options.json` — Theme options and settings

---

## Key Functions
- `pds_ajax_import_content()` — Main AJAX handler for importing all content.
- `pds_import_post_item()` — Imports a single post, page, or custom post type.
- `pds_import_taxonomies()` — Imports and maps taxonomies and term meta.
- `pds_import_menus_from_json()` — Imports menus and menu items from JSON.
- `pds_replace_url()` — Helper for robust URL rewriting (media, custom links, etc).

---

## Notes
- The installer is designed to be idempotent: running it multiple times will not create duplicates.
- For multisite, media URLs are automatically mapped to the correct uploads directory.
- All AJAX handlers are secured with nonces.

---

## License
MIT License. See main project for details.