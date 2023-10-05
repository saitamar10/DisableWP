<?php

defined('ABSPATH') or die('Unauthorized Access');

$upload_dir = wp_upload_dir();
global $dau_upload;
global $dau_dir;

$dau_upload = $upload_dir['basedir'];

if (!empty($dau_upload)) {
    $dau_dir = $dau_upload . '/Disable-updates';
    wp_mkdir_p($dau_dir);
}

global $current_user;
wp_get_current_user();
$user = $current_user->user_login;

global $dau_services;
$dau_services = ['disable-all', 'disable-plugin', 'disable-theme', 'disable-core', 'disable-admin-notice', 'hide-notification'];
$submitted = [];

function dau_check_files($file_name)
{
    if (in_array($file_name, $GLOBALS['dau_services']) && file_exists($GLOBALS['dau_dir'] . "/$file_name.php")) {
        echo 'checked';
    }
}

if (isset($_POST['submit'])) {

    foreach ($_POST as $key => $value) {
        if (isset($key) && $key == true && $key != 'submit') {
            $submitted[] = esc_html(filter_var($key, FILTER_SANITIZE_STRING));
        }
    }

    foreach ($dau_services as $service) {

        if (in_array($service, $submitted)) {

            $content = '';

            if ($service === "disable-all") {
                $content = '<?php

defined("ABSPATH") or die("Unauthorized Access");

define( "WP_AUTO_UPDATE_CORE", false );
add_filter("auto_update_plugin", "__return_false");
add_filter("auto_update_theme", "__return_false");';
            } elseif ($service === "disable-plugin") {
                $content = '<?php

defined("ABSPATH") or die("Unauthorized Access");

add_filter("auto_update_plugin", "__return_false");';
            } elseif ($service === "disable-theme") {
                $content = '<?php

defined("ABSPATH") or die("Unauthorized Access");

add_filter("auto_update_theme", "__return_false");';
            } elseif ($service === "disable-core") {
                $content = '<?php

defined("ABSPATH") or die("Unauthorized Access");

define( "WP_AUTO_UPDATE_CORE", false );';
            } elseif ($service === 'disable-admin-notice') {
                $content = '<?php

defined("ABSPATH") or die("Unauthorized Access");

add_action("admin_enqueue_scripts", "hide_notices");
add_action("login_enqueue_scripts", "hide_notices");

function hide_notices() {
    if (current_user_can("manage_options")) {
        echo "<style>.update-nag, .updated, .error, .is-dismissible, .notice { display: none; }</style>";
    }
}';
            } else {
                $content = '<?php

defined("ABSPATH") or die("Unauthorized Access");

function dau_remove_notifications() {
    global $wp_version;
    return (object) array("last_checked" => time(), "version_checked" => $wp_version,);
}

add_filter("pre_site_transient_update_core", "dau_remove_notifications");
add_filter("pre_site_transient_update_plugins", "dau_remove_notifications");
add_filter("pre_site_transient_update_themes", "dau_remove_notifications");';
            }

            $file = fopen("$dau_dir/$service.php", "wb");
            fwrite($file, $content);
            fclose($file);
        } else {
            if (file_exists("$dau_dir/$service.php")) {
                unlink("$dau_dir/$service.php");
            }
        }
    }
}

?>

<div id="dau">
    <h1 id="dau-title">Arka plan güncellemelerini devre dışı bırakma <sub style="font-size: 12px">V 1.0</sub></h1>
    <p>Geliştirdiğim ve sürdürdüğüm projeler için kullanımınız varsa, lütfen çalışmalarımı desteklemeyi düşünün, böylece onları sürekli olarak sürdürebilirim, buraya tıklayabilirsiniz 👉 tıkla <a href="https://www.ismetceber.com.tr" target="_blank">Blogum</a>destek için teşekkür！</p>
    <hr/>

    <form action="" method="POST" id="dau-form">
        <label for="dau-disable">
        </label><br>
        <label for="dau-disable-plugin">
            Eklenti güncellemelerini devre dışı bırak
            <input type="checkbox" name="disable-plugin" id="dau-disable-plugin" <?php dau_check_files('disable-plugin'); ?>><br>
        </label><br>
        <label for="dau-disable-theme">
            Tema güncellemelerini devre dışı bırak
            <input type="checkbox" name="disable-theme" id="dau-disable-theme" <?php dau_check_files('disable-theme'); ?>><br>
        </label><br>
        <label for="dau-disable-core">
            Çekirdek güncellemelerini devre dışı bırak
            <input type="checkbox" name="disable-core" id="dau-disable-core" <?php dau_check_files('disable-core'); ?>><br>
        </label><br>
        <label for="dau-hide-notification">
            Güncelleme bildirimlerini gizle
            <input type="checkbox" name="hide-notification" id="dau-hide-notification" <?php dau_check_files('hide-notification'); ?>><br>
        </label><br />
        <label for="dau-disable-admin-notice">
            Yönetici bildirimini devre dışı bırakma
            <input type="checkbox" name="disable-admin-notice" id="dau-disable-admin-notice" <?php dau_check_files('disable-admin-notice'); ?>><br>
            <p>
                        <td><input type="submit" name="submit" class="button button-primary" value="Aktif et"/></td>
            </tr>
        </table>
        </p>
    </form>
</div>