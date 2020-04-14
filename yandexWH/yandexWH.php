<?php
/*
Plugin Name:  Yandex.Money Payment Plugin webhook
Plugin URI:   https://github.com/AlexanderNovikov/WooCommerce-Yandex.Money-Payment-Plugin
Description:  Yandex.Money payment webhook
Version:      1.0
Author:       a@novikovav.com
Author URI:   https://github.com/AlexanderNovikov/
License:      MIT
License URI:  https://github.com/AlexanderNovikov/WooCommerce-Yandex.Money-Payment-Plugin/blob/master/LICENSE
Text Domain:  wporg
Domain Path:  /languages
*/

add_action('plugins_loaded', 'init_yandex_gatewayWH');

function init_yandex_gatewayWH()
{
    add_action('woocommerce_api_callback', 'callback_handler');

    function write_error($error_message)
    {
        $path = 'wp-content/plugins/yandexWH/log.txt';
        error_log("{$error_message}\r\n", 3, $path);
    }

    function callback_handler()
    {
        $yandex_secret = ''; //TODO put your yandex secret here
        $raw_post = file_get_contents('php://input');
        write_error($raw_post);
        parse_str($raw_post, $output);
        $sha1_hash = $output['sha1_hash'];
        $params_str = $output['notification_type'] . '&';
        $params_str .= $output['operation_id'] . '&';
        $params_str .= $output['amount'] . '&';
        $params_str .= $output['currency'] . '&';
        $params_str .= $output['datetime'] . '&';
        $params_str .= $output['sender'] . '&';
        $params_str .= $output['codepro'] . '&';
        $params_str .= $yandex_secret . '&';
        if (strlen($output['label']) > 0) {
            $params_str .= $output['label'];
        }
        $params_str_hash = sha1($params_str);
        if ($sha1_hash == $params_str_hash) {
            $order_id = $output['label'];
            if ($order_id) {
                $order = new WC_Order($order_id);
                $payed_amount = $output['withdraw_amount'];
                $billed_amount = $order->get_total();
                if ($payed_amount == $billed_amount) {
                    $order->update_status('completed');
                }
            }
        }
        die();
    }
}
