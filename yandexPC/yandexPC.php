<?php
/*
Plugin Name:  Yandex.Money Payment Plugin PC
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

add_action('plugins_loaded', 'init_yandex_gatewayPC');

function init_yandex_gatewayPC()
{

    class WC_Gateway_YandexPC extends WC_Payment_Gateway
    {
        private $ya_money_form_url = 'https://money.yandex.ru/quickpay/confirm.xml?';
        private $ya_money_id = ''; //TODO put your yandex money id here

        public function __construct()
        {
            $this->id = 'yandexPC';
            $this->has_fields = false;

            $this->title = 'Yandex Money';
            $this->icon = plugins_url('/', 'yandexPC') . '/yandexPC/yandexPC.png';

            $this->init_form_fields();
            $this->init_settings();

            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        }

        function init_form_fields()
        {
            $this->form_fields = array(
                'enabled' => array(
                    'title' => __('Включить/Выключить', 'woocommerce'),
                    'type' => 'checkbox',
                    'label' => __('Включен', 'woocommerce'),
                    'default' => 'yes'
                )
            );
        }

        function process_payment($order_id)
        {
            global $woocommerce;
            $order = new WC_Order($order_id);

            // Mark as on-hold (we're awaiting the cheque)
            $order->update_status('on-hold', __('Awaiting cheque payment', 'woocommerce'));

//            Reduce stock levels
//            $order->wc_reduce_stock_levels();

            // Remove cart
            $woocommerce->cart->empty_cart();

            $redirect_params = [
                'paymentType=PC',
                "receiver=$this->ya_money_id",
                'quickpay-form=small',
                "targets={$order->get_id()}",
                "sum={$order->get_total()}",
                "label={$order->get_id()}",
                "successURL={$this->get_return_url($order)}",
            ];
            return array(
                'result' => 'success',
                'redirect' => $this->ya_money_form_url . implode('&', $redirect_params)
            );
        }
    }

    function add_your_gateway_classPC($methods)
    {
        $methods[] = 'WC_Gateway_YandexPC';
        return $methods;
    }

    add_filter('woocommerce_payment_gateways', 'add_your_gateway_classPC');
}

?>
