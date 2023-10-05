<?php
/*
Plugin Name: Disable WP
Description: Wordpress Güncellemeleri İptal Et
Version: 1.0
Author: Saitama
Author URI: https://www.saitama.net.tr
*/
defined('ABSPATH') or die('Unauthorized Access'); 

if (!class_exists('Da_updates')) {

    class Da_updates
    {
        
        public $dau_dir = WP_CONTENT_DIR . '/uploads/Disable-updates';

        public function register()
        {
            
            add_action('admin_menu', array($this, 'add_admin_pages'));
            add_filter('clean_url', [$this, 'add_async_attribute'], 11, 1);
            add_filter("plugin_row_meta", [$this, "meta"], 10, 2);
            add_filter('plugin_action_links', [$this, 'ads_action_links'], 10, 5);
        }

        public function add_admin_pages()
        {
            
            add_submenu_page('tools.php', 'Güncelleme ayarlarını devre dışı bırak', 'Güncelleme ayarlarını devre dışı bırak', 'manage_options', 'Disable-updates', [$this, 'view']);
            if (file_exists("$this->dau_dir/disable-all.php") || file_exists("$this->dau_dir/hide-notification.php") || file_exists("$this->dau_dir/disable-core.php") && file_exists("$this->dau_dir/disable-theme.php") && file_exists("$this->dau_dir/disable-plugin.php")) {
                remove_submenu_page('index.php', 'update-core.php');
            }
        }

        public function view()
        {
            
            require_once plugin_dir_path(__FILE__) . 'Set-up/Set-up.php';
        }

        public function activate()
        {
            
            flush_rewrite_rules();
        }

        public function deactivate()
        {
            
            flush_rewrite_rules();
        }

        public function add_async_attribute($url)
        {
            
            return str_replace("'", "' async='async", $url);
        }

        public function meta($links = [], $file = "")
        {
            if (strpos($file, "Disable-updates/Disable-updates.php") !== false) {
                $new_link = [
                    "donation" => '<a href="https://github.com/saitamar10" target="_blank">GITHUB</span></a>'
                ];

                
                $links = array_merge($links, $new_link);
            }

            return $links;
        }

        public function ads_action_links($links, $plugin_file)
        {
            $plugin = plugin_basename(__FILE__);

            if ($plugin === $plugin_file) {
                $ads_links = [
                    '<a href="' . admin_url('tools.php?page=Disable-updates') . '">Yükle</a>',
                ];

                
                $links = array_merge($ads_links, $links);
            }
            return $links;
        }

    }

    if (class_exists('Da_updates')) {
        
        $disable_auto_updates = new Da_updates();
        $disable_auto_updates->register();
        register_activation_hook(__FILE__, [$disable_auto_updates, 'activate']);
        register_deactivation_hook(__FILE__, [$disable_auto_updates, 'deactivate']);
    } else {
        die('Plugin internal code conflict'); 
    }

    $dau_services = ['disable-all', 'disable-plugin', 'disable-theme', 'disable-core', 'disable-admin-notice', 'hide-notification'];

    foreach ($dau_services as $service) {
        if (file_exists("$disable_auto_updates->dau_dir/$service.php")) {
            include_once "$disable_auto_updates->dau_dir/$service.php";
        }
    }

}
