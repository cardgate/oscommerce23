<?php

/**
 * $Id: cgp_generic.php 30 2011-12-23 17:35:13Z h0ax $
 *
 * osCommerce, Open Source E-Commerce Solutions
 * http://www.oscommerce.com
 *
 * Copyright (c) 2002 osCommerce
 *
 * Released under the GNU General Public License
 *
 * Created by BZ (support@cardgate.com)
 * version 2.10 2010-06-21
 *
 * Modified by Paul Saparov (support@cardgate.com)
 * version 2.31 2011-11-02
 *
 * Modified by Paul Saparov (support@cardgate.com)
 * version 2.32 2011-11-11
 *
 * Modified by Paul Saparov (support@cardgate.com)
 * version 2.33 2011-12-21
 *
 * Modified by Richard Schoots (support@cardgate.com)
 * version 2.34 2013-03-11
 *
 * Modified by Richard Schoots (support@cardgate.com)
 * version 2.35
 *
 * Modified by Richard Schoots (support@cardgate.com)
 * version 2.36 2013-06-07
 */
class cgp_generic {

    var $module_cgp_text;

    var $title;

    var $sort_order;

    var $description;

    var $enabled;

    var $form_action_url;

    var $is_test;

    var $version;

    /**
     * Constructor
     */
    function __construct() {
        global $order;
        
        $payment_method = strtolower($this->module_cgp_text);
        switch ($payment_method) {
            case 'directebanking':
                $payment_method = 'bancontact';
                break;
            case 'sofortueberweisung':
                $payment_method = 'sofortbanking';
                break;
            case 'americanexpress':
                $payment_method = 'creditcard';
                break;
            case 'mastercard':
                $payment_method = 'creditcard';
                break;
            case 'maestro':
                $payment_method = 'creditcard';
                break;
            case 'visa':
                $payment_method = 'creditcard';
                break;
            case 'vpay':
                $payment_method = 'creditcard';
                break;
        }
        
        $title = @constant("MODULE_PAYMENT_CGP_" . $this->module_cgp_text . "_TEXT_TITLE");
        $payment_image = '<img style="max-height:30px; max-width:70px;" src="https://cdn.curopayments.net/images/paymentmethods/' . $payment_method . '.svg">';
        $display = defined("MODULE_PAYMENT_CGP_" . $this->module_cgp_text . "_CHECKOUT_DISPLAY") ? constant("MODULE_PAYMENT_CGP_" . $this->module_cgp_text . "_CHECKOUT_DISPLAY") : 'Text';
        switch($display){
            case 'Text':
                $this->title = $title;
                break;
            case 'Logo':
                $this->title = $payment_image;
                break;
            case 'Text and Logo':
                $this->title = $payment_image . ' ' . $title;
                break;
            default:
                $this->title = $title;
        }
        
        $this->description = @constant("MODULE_PAYMENT_CGP_" . $this->module_cgp_text . "_TEXT_DESCRIPTION");
        // the cardgate version number is the first no. in the signature
        $this->signature = "cardgateplus|cardgateplus|3.1.2|2.3";
        $aVersion = explode("|", $this->signature);
        $this->version = $aVersion[2];
        $this->sort_order = defined("MODULE_PAYMENT_CGP_" . $this->module_cgp_text . "_SORT_ORDER") ? constant("MODULE_PAYMENT_CGP_" . $this->module_cgp_text . "_SORT_ORDER") : 0;
        $this->enabled = (defined("MODULE_PAYMENT_CGP_" . $this->module_cgp_text . "_STATUS") && (constant("MODULE_PAYMENT_CGP_" . $this->module_cgp_text . "_STATUS") == 'True') ? true : false);
        $this->is_test = (defined("MODULE_PAYMENT_CGP_" . $this->module_cgp_text . "_MODE") && (constant("MODULE_PAYMENT_CGP_" . $this->module_cgp_text . "_MODE") == 'Test') ? true : false);
        
        if (is_object($order)) {
            $this->update_status();
        }
        
        if ($this->is_test) {
            $this->form_action_url = 'https://secure-staging.curopayments.net/gateway/cardgate/';
        } else {
            $this->form_action_url = 'https://secure.curopayments.net/gateway/cardgate/';
        }
    }

    /**
     * Check whether this payment module is available
     *
     * @return void
     */
    function update_status() {
        global $order;
        if ($this->enabled && ((int) constant("MODULE_PAYMENT_CGP_" . $this->module_cgp_text . "_ZONE") > 0)) {
            $check_flag = false;
            $check_query = tep_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . constant("MODULE_PAYMENT_CGP_" . $this->module_cgp_text . "_ZONE") . "' and zone_country_id = '" . $order->delivery['country']['id'] . "' order by zone_id");
            while ($check = tep_db_fetch_array($check_query)) {
                if ($check['zone_id'] < 1) {
                    $check_flag = true;
                    break;
                } else if ($check['zone_id'] == $order->delivery['zone_id']) {
                    $check_flag = true;
                    break;
                }
            }
            
            if (! $check_flag) {
                $this->enabled = false;
            }
        }
    }

    /**
     * Client side javascript that will verify any input
     * fields you use in the payment method selection page
     *
     * @return boolean False
     */
    function javascript_validation() {
        return false;
    }

    /**
     * Outputs the payment method title/text and if required,
     * the input fields
     *
     * @global type $cart_cgp_ID
     * @return array
     */
    function selection() {
        global $cart_cgp_ID;
        
        if (tep_session_is_registered('cart_cgp_ID')) {
            $order_id = substr($cart_cgp_ID, strpos($cart_cgp_ID, '-') + 1);
            
            $check_query = tep_db_query('select orders_id from ' . TABLE_ORDERS_STATUS_HISTORY . ' where orders_id = "' . (int) $order_id . '" limit 1');
            
            if (tep_db_num_rows($check_query) < 1) {
                tep_session_unregister('cart_cgp_ID');
            }
        }
        
        return array(
            'id' => $this->code,
            'module' => $this->title
        );
    }

    /**
     * Any checks of any conditions after payment method has been selected
     *
     * @global type $cartID
     * @global type $cart
     */
    function pre_confirmation_check() {
        global $cartID;
        global $cart;
        
        if (empty($cart->cartID)) {
            $cartID = $cart->cartID = $cart->generate_cart_id();
        }
        
        if (! tep_session_is_registered('cartID')) {
            tep_session_register('cartID');
        }
    }

    /**
     * Any checks or processing on the order information before
     * proceeding to payment confirmation
     *
     * @global type $cartID
     * @global type $cart_cgp_ID
     * @global type $customer_id
     * @global type $languages_id
     * @global type $order
     * @global type $order_total_modules
     * @return array
     */
    function confirmation() {
        global $cartID;
        global $cart_cgp_ID;
        global $customer_id;
        global $languages_id;
        global $order;
        global $order_total_modules;
        
        if (tep_session_is_registered('cartID')) {
            $insert_order = false;
            
            if (tep_session_is_registered('cart_cgp_ID')) {
                $order_id = substr($cart_cgp_ID, strpos($cart_cgp_ID, '-') + 1);
                
                $curr_check = tep_db_query("select currency from " . TABLE_ORDERS . " where orders_id = '" . (int) $order_id . "'");
                $curr = tep_db_fetch_array($curr_check);
                
                if (($curr['currency'] != $order->info['currency']) || ($cartID != substr($cart_cgp_ID, 0, strlen($cartID)))) {
                    $check_query = tep_db_query('select orders_id from ' . TABLE_ORDERS_STATUS_HISTORY . ' where orders_id = "' . (int) $order_id . '" limit 1');
                    
                    if (tep_db_num_rows($check_query) < 1) {
                        tep_db_query('delete from ' . TABLE_ORDERS . ' where orders_id = "' . (int) $order_id . '"');
                        tep_db_query('delete from ' . TABLE_ORDERS_TOTAL . ' where orders_id = "' . (int) $order_id . '"');
                        tep_db_query('delete from ' . TABLE_ORDERS_STATUS_HISTORY . ' where orders_id = "' . (int) $order_id . '"');
                        tep_db_query('delete from ' . TABLE_ORDERS_PRODUCTS . ' where orders_id = "' . (int) $order_id . '"');
                        tep_db_query('delete from ' . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . ' where orders_id = "' . (int) $order_id . '"');
                        tep_db_query('delete from ' . TABLE_ORDERS_PRODUCTS_DOWNLOAD . ' where orders_id = "' . (int) $order_id . '"');
                    }
                    
                    $insert_order = true;
                }
            } else {
                $insert_order = true;
            }
            
            if ($insert_order == true) {
                $order_totals = array();
                if (is_array($order_total_modules->modules)) {
                   reset($order_total_modules->modules);
                        foreach ($order_total_modules->modules as $value) {
                        $class = substr($value, 0, strpos($value, '.'));
                        if ($GLOBALS[$class]->enabled) {
                            for ($i = 0, $n = sizeof($GLOBALS[$class]->output); $i < $n; $i ++) {
                                if (tep_not_null($GLOBALS[$class]->output[$i]['title']) && tep_not_null($GLOBALS[$class]->output[$i]['text'])) {
                                    $order_totals[] = array(
                                        'code' => $GLOBALS[$class]->code,
                                        'title' => $GLOBALS[$class]->output[$i]['title'],
                                        'text' => $GLOBALS[$class]->output[$i]['text'],
                                        'value' => $GLOBALS[$class]->output[$i]['value'],
                                        'sort_order' => $GLOBALS[$class]->sort_order
                                    );
                                }
                            }
                        }
                    }
                }
                
                $sql_data_array = array(
                    'customers_id' => $customer_id,
                    'customers_name' => $order->customer['firstname'] . ' ' . $order->customer['lastname'],
                    'customers_company' => $order->customer['company'],
                    'customers_street_address' => $order->customer['street_address'],
                    'customers_suburb' => $order->customer['suburb'],
                    'customers_city' => $order->customer['city'],
                    'customers_postcode' => $order->customer['postcode'],
                    'customers_state' => $order->customer['state'],
                    'customers_country' => $order->customer['country']['title'],
                    'customers_telephone' => $order->customer['telephone'],
                    'customers_email_address' => $order->customer['email_address'],
                    'customers_address_format_id' => $order->customer['format_id'],
                    'delivery_name' => $order->delivery['firstname'] . ' ' . $order->delivery['lastname'],
                    'delivery_company' => $order->delivery['company'],
                    'delivery_street_address' => $order->delivery['street_address'],
                    'delivery_suburb' => $order->delivery['suburb'],
                    'delivery_city' => $order->delivery['city'],
                    'delivery_postcode' => $order->delivery['postcode'],
                    'delivery_state' => $order->delivery['state'],
                    'delivery_country' => $order->delivery['country']['title'],
                    'delivery_address_format_id' => $order->delivery['format_id'],
                    'billing_name' => $order->billing['firstname'] . ' ' . $order->billing['lastname'],
                    'billing_company' => $order->billing['company'],
                    'billing_street_address' => $order->billing['street_address'],
                    'billing_suburb' => $order->billing['suburb'],
                    'billing_city' => $order->billing['city'],
                    'billing_postcode' => $order->billing['postcode'],
                    'billing_state' => $order->billing['state'],
                    'billing_country' => $order->billing['country']['title'],
                    'billing_address_format_id' => $order->billing['format_id'],
                    'payment_method' => $order->info['payment_method'],
                    'cc_type' => $order->info['cc_type'],
                    'cc_owner' => $order->info['cc_owner'],
                    'cc_number' => $order->info['cc_number'],
                    'cc_expires' => $order->info['cc_expires'],
                    'date_purchased' => 'now()',
                    'orders_status' => constant('MODULE_PAYMENT_CGP_' . $this->module_cgp_text . '_ORDER_INITIAL_STATUS_ID'),
                    'currency' => $order->info['currency'],
                    'currency_value' => $order->info['currency_value']
                );
                
                tep_db_perform(TABLE_ORDERS, $sql_data_array);
                
                $insert_id = tep_db_insert_id();
                
                for ($i = 0, $n = sizeof($order_totals); $i < $n; $i ++) {
                    $sql_data_array = array(
                        'orders_id' => $insert_id,
                        'title' => $order_totals[$i]['title'],
                        'text' => $order_totals[$i]['text'],
                        'value' => $order_totals[$i]['value'],
                        'class' => $order_totals[$i]['code'],
                        'sort_order' => $order_totals[$i]['sort_order']
                    );
                    
                    tep_db_perform(TABLE_ORDERS_TOTAL, $sql_data_array);
                }
                
                for ($i = 0, $n = sizeof($order->products); $i < $n; $i ++) {
                    $sql_data_array = array(
                        'orders_id' => $insert_id,
                        'products_id' => tep_get_prid($order->products[$i]['id']),
                        'products_model' => $order->products[$i]['model'],
                        'products_name' => $order->products[$i]['name'],
                        'products_price' => $order->products[$i]['price'],
                        'final_price' => $order->products[$i]['final_price'],
                        'products_tax' => $order->products[$i]['tax'],
                        'products_quantity' => $order->products[$i]['qty']
                    );
                    
                    tep_db_perform(TABLE_ORDERS_PRODUCTS, $sql_data_array);
                    
                    $order_products_id = tep_db_insert_id();
                    
                    $attributes_exist = '0';
                    if (isset($order->products[$i]['attributes'])) {
                        $attributes_exist = '1';
                        for ($j = 0, $n2 = sizeof($order->products[$i]['attributes']); $j < $n2; $j ++) {
                            if (DOWNLOAD_ENABLED == 'true') {
                                $attributes_query = "select popt.products_options_name, poval.products_options_values_name, pa.options_values_price, pa.price_prefix, pad.products_attributes_maxdays, pad.products_attributes_maxcount , pad.products_attributes_filename
                                       from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_OPTIONS_VALUES . " poval, " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                                       left join " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " pad
                                       on pa.products_attributes_id=pad.products_attributes_id
                                       where pa.products_id = '" . $order->products[$i]['id'] . "'
                                       and pa.options_id = '" . $order->products[$i]['attributes'][$j]['option_id'] . "'
                                       and pa.options_id = popt.products_options_id
                                       and pa.options_values_id = '" . $order->products[$i]['attributes'][$j]['value_id'] . "'
                                       and pa.options_values_id = poval.products_options_values_id
                                       and popt.language_id = '" . $languages_id . "'
                                       and poval.language_id = '" . $languages_id . "'";
                                $attributes = tep_db_query($attributes_query);
                            } else {
                                $attributes = tep_db_query("select popt.products_options_name, poval.products_options_values_name, pa.options_values_price, pa.price_prefix from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_OPTIONS_VALUES . " poval, " . TABLE_PRODUCTS_ATTRIBUTES . " pa where pa.products_id = '" . $order->products[$i]['id'] . "' and pa.options_id = '" . $order->products[$i]['attributes'][$j]['option_id'] . "' and pa.options_id = popt.products_options_id and pa.options_values_id = '" . $order->products[$i]['attributes'][$j]['value_id'] . "' and pa.options_values_id = poval.products_options_values_id and popt.language_id = '" . $languages_id . "' and poval.language_id = '" . $languages_id . "'");
                            }
                            $attributes_values = tep_db_fetch_array($attributes);
                            
                            $sql_data_array = array(
                                'orders_id' => $insert_id,
                                'orders_products_id' => $order_products_id,
                                'products_options' => $attributes_values['products_options_name'],
                                'products_options_values' => $attributes_values['products_options_values_name'],
                                'options_values_price' => $attributes_values['options_values_price'],
                                'price_prefix' => $attributes_values['price_prefix']
                            );
                            
                            tep_db_perform(TABLE_ORDERS_PRODUCTS_ATTRIBUTES, $sql_data_array);
                            
                            if ((DOWNLOAD_ENABLED == 'true') && isset($attributes_values['products_attributes_filename']) && tep_not_null($attributes_values['products_attributes_filename'])) {
                                $sql_data_array = array(
                                    'orders_id' => $insert_id,
                                    'orders_products_id' => $order_products_id,
                                    'orders_products_filename' => $attributes_values['products_attributes_filename'],
                                    'download_maxdays' => $attributes_values['products_attributes_maxdays'],
                                    'download_count' => $attributes_values['products_attributes_maxcount']
                                );
                                
                                tep_db_perform(TABLE_ORDERS_PRODUCTS_DOWNLOAD, $sql_data_array);
                            }
                        }
                    }
                }
                
                $cart_cgp_ID = $cartID . '-' . $insert_id;
                tep_session_register('cart_cgp_ID');
            }
        }
    }

    /**
     * Outputs the html form hidden elements sent as POST data
     * to the payment gateway
     *
     * @global type $cart_cgp_ID
     * @global type $HTTP_POST_VARS
     * @global type $customer_id
     * @global type $order
     * @global type $currencies
     */
    function process_button() {
        global $cart_cgp_ID, $HTTP_POST_VARS, $customer_id, $order, $currencies, $shipping;
        
        $aItems = array();
        $iNr = - 1;
        foreach ($order->products as $sKey => $aProduct) {
            $iNr ++;
            $aItems[$iNr]['quantity'] = $aProduct['qty'];
            $aItems[$iNr]['sku'] = $aProduct['id'];
            $aItems[$iNr]['name'] = $aProduct['name'];
            $aItems[$iNr]['price'] = round($aProduct['final_price'] * (100 + $aProduct['tax']), 0);
            $aItems[$iNr]['vat'] = round($aProduct['tax'], 0);
            $aItems[$iNr]['vat_inc'] = true;
            $aItems[$iNr]['type'] = 1;
            $iTotal += round($aItems[$iNr]['quantity'] * $aItems[$iNr]['price']);
        }
        if (! empty($order->info['shipping_cost']) && $order->info['shipping_cost'] > 0) {
            $shipping_id = $shipping['id'];
            $aShipping = explode('_', $shipping_id);
            $shipping_type = strtoupper($aShipping[0]);
            $configuration_key = 'MODULE_SHIPPING_' . $shipping_type . '_TAX_CLASS';
            $configuration_query = tep_db_query("select c.configuration_value from " . TABLE_CONFIGURATION . " c where c.configuration_key='" . $configuration_key . "'");
            
            if ($configuration = tep_db_fetch_array($configuration_query)) {
                $tax_class = (int) $configuration['configuration_value'];
            }
            
            if ($tax_class > 0) {
                $aShipping_Tax_Rate = tep_get_tax_rate(MODULE_SHIPPING_FLAT_TAX_CLASS, $order->delivery['country']['id'], $order->delivery['zone_id']);
            }
            $aItems[] = array(
                'quantity' => 1,
                'sku' => 'SHIPPING',
                'name' => 'Bezorgkosten',
                'price' => round(($shipping['cost'] * (100 + $aShipping_Tax_Rate))),
                'vat' => round($aShipping_Tax_Rate, 0),
                'vat_inc' => true,
                'type' => 2
            );
            $iTotal += round(($shipping['cost'] * (100 + $aShipping_Tax_Rate)));
        }
        
        $is_test = (constant("MODULE_PAYMENT_CGP_" . $this->module_cgp_text . "_MODE") === 'Test' ? 1 : 0);
        $amount = $this->format_raw($order->info['total'], $order->info['currency']) * 100;
        
        $total_diff = round($amount - $iTotal);
        
        if ($total_diff != 0) {
            $aItems[] = array(
                'quantity' => 1,
                'sku' => 'item_correction',
                'name' => 'Correction',
                'price' => $total_diff,
                'vat' => 0,
                'vat_inc' => false,
                'type' => 6
            );
        }
        
        $order_id = substr($cart_cgp_ID, strpos($cart_cgp_ID, '-') + 1);
        $sql_data_array = array(
            'module' => $this->code,
            'date_ordered' => 'now()',
            'orderstr' => base64_encode(serialize($order)),
            'customer_id' => $customer_id,
            'is_test' => $is_test,
            'amount' => $amount,
            'order_id' => $order_id
        );
        tep_db_perform('CGP_orders_table', $sql_data_array);
        $last_id = tep_db_insert_id();
        $ref = time() . $order_id . "|" . $last_id;
        $extra = $order_id . "|" . $last_id;
        
        $sHashkey = "";
        $siteID = constant("MODULE_PAYMENT_CGP_" . $this->module_cgp_text . "_SITEID");
        $hashKey = constant("MODULE_PAYMENT_CGP_" . $this->module_cgp_text . "_KEYCODE");
        $language = constant("MODULE_PAYMENT_CGP_" . $this->module_cgp_text . "_LANGUAGE");
        
        // create hash
        if ($hashKey != "") {
            $sHashKey = md5(($is_test == 1 ? "TEST" : "") . $siteID . $amount . $ref . $hashKey);
        }
        
        // payment option
        switch ($this->module_cgp_text) {
            case "VISA":
            case "MASTERCARD":
            case "VPAY":
            case "MAESTRO":
            case "AMERICANEXPRESS":
                $payment_option = tep_draw_hidden_field('option', 'creditcard');
                break;
            
            case "IDEAL":
                $payment_option = tep_draw_hidden_field('option', 'ideal') . tep_draw_hidden_field('suboption', $HTTP_POST_VARS['ideal_issuer_id']);
                break;
            
            case "DIRECTEBANKING":
            case "SOFORTUEBERWEISUNG":
                $payment_option = tep_draw_hidden_field('option', 'directebanking');
                break;
            
            case "MISTERCASH":
                $payment_option = tep_draw_hidden_field('option', 'mistercash');
                break;
            
            case "AFTERPAY":
                $payment_option = tep_draw_hidden_field('option', 'afterpay');
                break;
            
            case "KLARNA":
                $payment_option = tep_draw_hidden_field('option', 'klarna');
                break;
            
            case "BITCOIN":
                $payment_option = tep_draw_hidden_field('option', 'bitcoin');
                break;
            
            case "PAYPAL":
                $payment_option = tep_draw_hidden_field('option', 'paypal');
                break;
            
            case "PAYSAFECARD":
                $payment_option = tep_draw_hidden_field('option', 'paysafecard');
                break;
            
            case "GIROPAY":
                $payment_option = tep_draw_hidden_field('option', 'giropay');
                break;
            
            case "BANKTRANSFER":
                $payment_option = tep_draw_hidden_field('option', 'banktransfer');
                break;
            
            case "DIRECTDEBIT":
                $payment_option = tep_draw_hidden_field('option', 'directdebit');
                break;
            
            case "PRZELEWY24":
                $payment_option = tep_draw_hidden_field('option', 'przelewy24');
                break;
            
            case "BILLINK":
                $payment_option = tep_draw_hidden_field('option', 'billink');
                break;
            
            case "IDEALQR":
                $payment_option = tep_draw_hidden_field('option', 'idealqr');
                break;
            
            case "PAYSAFECASH":
                $payment_option = tep_draw_hidden_field('option', 'paysafecash');
                break;
            
            case "GIFTCARD":
                $payment_option = tep_draw_hidden_field('option', 'giftcard');
                break;
        }
        
        // generate items list
        $description = 'Order ' . $order_id;
        
        $process_button_string = tep_draw_hidden_field('siteid', $siteID) . tep_draw_hidden_field('currency', $order->info['currency']) . tep_draw_hidden_field('description', $description) . tep_draw_hidden_field('language', $language) . $payment_option . tep_draw_hidden_field('test', $is_test) . tep_draw_hidden_field('amount', $amount) . tep_draw_hidden_field('ref', $ref) . tep_draw_hidden_field('email', $order->customer['email_address']) . tep_draw_hidden_field('firstname', $order->customer['firstname']) . tep_draw_hidden_field('lastname', $order->customer['lastname']) . tep_draw_hidden_field('address', $order->customer['street_address']) . tep_draw_hidden_field('city', $order->customer['city']) . tep_draw_hidden_field('state', $order->customer['state']) . tep_draw_hidden_field('zipcode', $order->customer['postcode']) . tep_draw_hidden_field('phone', $order->customer['telephone']) . tep_draw_hidden_field('country', $order->customer['country']['iso_code_2']) . tep_draw_hidden_field('shipto_firstname', $order->delivery['firstname']) . tep_draw_hidden_field('shipto_lastname', $order->delivery['lastname']) . tep_draw_hidden_field('shipto_address', $order->delivery['street_address']) . tep_draw_hidden_field('shipto_city', $order->delivery['city']) . tep_draw_hidden_field('shipto_state', $order->delivery['state']) . tep_draw_hidden_field('shipto_zipcode', $order->delivery['postcode']) . tep_draw_hidden_field('url_success', HTTP_SERVER . DIR_WS_CATALOG . 'ext/modules/payment/cgp/cgp.php?cgp_notify=true') . tep_draw_hidden_field('url_pending', HTTP_SERVER . DIR_WS_CATALOG . 'ext/modules/payment/cgp/cgp.php?cgp_notify=true') . tep_draw_hidden_field('url_failure', HTTP_SERVER . DIR_WS_CATALOG . 'ext/modules/payment/cgp/cgp.php?cgp_notify=true') . tep_draw_hidden_field('shop_version', PROJECT_VERSION) . tep_draw_hidden_field('plugin_name', 'OsCommerce CGP') . tep_draw_hidden_field('plugin_version', $this->version) . tep_draw_hidden_field('hash', $sHashKey) . tep_draw_hidden_field('extra', $extra) . tep_draw_hidden_field('cartitems', json_encode($aItems, JSON_HEX_APOS | JSON_HEX_QUOT));
        
        return $process_button_string;
    }

    /**
     * Payment verification
     *
     * @global type $customer_id
     * @global type $order
     * @global type $order_totals
     * @global type $sendto
     * @global type $billto
     * @global type $languages_id
     * @global type $payment
     * @global type $currencies
     * @global type $cart
     * @global type $cart_cgp_ID
     * @global type $payment
     */
    function before_process() {
        // redirect to success url without processing
        // processing is done via the callback url
        $cart->reset(true);
        tep_redirect(tep_href_link(FILENAME_CHECKOUT_SUCCESS, '', 'SSL'));
    }

    /**
     * Post-processing of the payment/order after the order has been finalised
     */
    function after_process() {
        return false;
    }

    /**
     * Advanced error handling
     */
    function output_error() {
        return false;
    }

    /**
     * Get error.
     *
     * @global type $_GET
     * @return array
     */
    function get_error() {
        global $HTTP_GET_VARS;
        
        if (isset($HTTP_GET_VARS['ErrMsg']) && tep_not_null($HTTP_GET_VARS['ErrMsg'])) {
            $error = stripslashes(urldecode($_GET['ErrMsg']));
        } elseif (isset($HTTP_GET_VARS['Err']) && tep_not_null($HTTP_GET_VARS['Err'])) {
            $error = stripslashes(urldecode($HTTP_GET_VARS['Err']));
        } elseif (isset($HTTP_GET_VARS['error']) && tep_not_null($HTTP_GET_VARS['error'])) {
            $error = stripslashes(urldecode($HTTP_GET_VARS['error']));
        } else {
            $error = MODULE_PAYMENT_CGP_TEXT_ERROR_MESSAGE;
        }
        
        return array(
            'title' => MODULE_PAYMENT_CGP_TEXT_ERROR,
            'error' => $error
        );
    }

    /**
     * Format prices without currency formatting
     *
     * @global type $currencies
     * @global type $currency
     * @param float $number            
     * @param string $currency_code            
     * @param string $currency_value            
     * @return float
     */
    function format_raw($number, $currency_code = '', $currency_value = '') {
        global $currencies, $currency;
        
        if (empty($currency_code) || ! $currencies->is_set($currency_code)) {
            $currency_code = $currency;
        }
        
        if (empty($currency_value) || ! is_numeric($currency_value)) {
            $currency_value = $currencies->currencies[$currency_code]['value'];
        }
        return number_format(tep_round($number * $currency_value, $currencies->currencies[$currency_code]['decimal_places']), $currencies->currencies[$currency_code]['decimal_places'], '.', '');
    }

    /**
     * Check if configuration_value is stored in DB.
     *
     * @return boolean
     */
    function check() {
        if (! isset($this->_check)) {
            $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_CGP_" . $this->module_cgp_text . "_STATUS'");
            $this->_check = tep_db_num_rows($check_query);
        }
        return $this->_check;
    }

    /**
     * Fetch array with bank options
     *
     * @return array
     */
    function get_banks() {
        $aBankOptions = $this->getBankOptions($this->is_test);
        $aBanks = array();
        foreach ($aBankOptions as $id => $text) {
            $aBanks[] = array(
                "id" => $id,
                "text" => $text
            );
        }
        return $aBanks;
    }

    /**
     * Download bank options from Card Gate Plus
     *
     * @return array
     */
    private function getBankOptions($is_test) {
        if (! empty($_SERVER['CGP_GATEWAY_URL'])) {
            $url = 'https://ralph.api.curopayments.dev/cache/idealDirectoryCUROPayments.dat';
        } else {
            if ($is_test) {
                $url = 'https://secure-staging.curopayments.net/cache/idealDirectoryCUROPayments.dat';
            } else {
                $url = 'https://secure.curopayments.net/cache/idealDirectoryCUROPayments.dat';
            }
        }
        
        if (! ini_get('allow_url_fopen') || ! function_exists('file_get_contents')) {
            $result = false;
        } else {
            $result = file_get_contents($url);
        }
        
        $aBanks = array();
        
        if ($result) {
            $aBanks = unserialize($result);
            $aBanks[0] = MODULE_PAYMENT_CGP_IDEAL_CHOOSE_BANK;
        }
        if (count($aBanks) < 1) {
            $aBanks = array(
                'ABNANL2A' => 'ABN Amro',
                'ASNBNL21' => 'ASN Bank',
                'INGBNL2A' => 'ING Bank',
                'KNABNL2H' => 'Knab',
                'RABONL2U' => 'Rabobank',
                'RBRBNL21' => 'RegioBank',
                'SNSBNL2A' => 'SNS Bank',
                'TRIONL2U' => 'Triodos Bank',
                'FVLBNL22' => 'Van Landschot Bank'
            );
        }
        return $aBanks;
    }

    /**
     * Install method creates `CGP_orders_table` table
     */
    function install() {
        global $db;
        $payments = $this->checkpayments();
        foreach ($payments as $payment){
            $id_result = tep_db_query("SELECT configuration_id FROM ". TABLE_CONFIGURATION ." WHERE configuration_key='MODULE_PAYMENT_CGP_".$payment."_MODE'");
            $id = tep_db_fetch_array($id_result);
            if (is_array($id)){
                $display_result = tep_db_query("SELECT configuration_id FROM ". TABLE_CONFIGURATION ." WHERE configuration_key='MODULE_PAYMENT_CGP_".$payment."_CHECKOUT_DISPLAY'");
                $display = tep_db_fetch_array($display_result);
                if ($display == false){
                    @tep_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Checkout display mode', 'MODULE_PAYMENT_CGP_" . strtoupper($payment) . "_CHECKOUT_DISPLAY', 'Text', 'Set checkout display mode', '6', '21','tep_cfg_select_option(array(\'Text\', \'Logo\', \'Text and Logo\'), ', now())");
                }
            }
        }
        $domain_name = constant("HTTP_SERVER");
        if (! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) {
            $domain_name .= constant("DIR_WS_CATALOG");
        } else {
            $domain_name .= constant("DIR_WS_CATALOG");
        }
        
        $cgp_control = $domain_name . 'ext/modules/payment/cgp/cgp.php';
        
        @tep_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable CGP Module', 'MODULE_PAYMENT_CGP_" . $this->module_cgp_text . "_STATUS', 'True', 'Do you want to accept " . constant("MODULE_PAYMENT_CGP_" . $this->module_cgp_text . "_TEXT_TITLE") . " payments?', '6', '21', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
        @tep_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Test/Live Mode', 'MODULE_PAYMENT_CGP_" . $this->module_cgp_text . "_MODE', 'Test', 'Set test or live mode', '6', '21','tep_cfg_select_option(array(\'Test\', \'Live\'), ', now())");
        @tep_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Site ID', 'MODULE_PAYMENT_CGP_" . $this->module_cgp_text . "_SITEID', '', 'Assigned Site ID by CardGate', '6', '22', now())");
        @tep_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Hash key', 'MODULE_PAYMENT_CGP_" . $this->module_cgp_text . "_KEYCODE', '', 'Security hash code from CardGate back-office', '6', '23', now())");
        @tep_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Gateway language', 'MODULE_PAYMENT_CGP_" . $this->module_cgp_text . "_LANGUAGE', 'en', 'Default gateway language', '6', '24', now())");
        @tep_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Payment in progress status', 'MODULE_PAYMENT_CGP_" . $this->module_cgp_text . "_ORDER_INITIAL_STATUS_ID', '0', 'Set the status of orders when customers are being redirected to the gateway', '6', '0', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");
        @tep_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Payment complete status', 'MODULE_PAYMENT_CGP_" . $this->module_cgp_text . "_ORDER_PAID_STATUS_ID', '0', 'Set the status of orders when a payment was successful', '6', '0', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");
        @tep_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Payment failed/canceled status', 'MODULE_PAYMENT_CGP_" . $this->module_cgp_text . "_ORDER_FAILED_STATUS_ID', '0', 'Set the status of orders when a payment has failed or been canceled', '6', '0', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");
        @tep_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Payment Zone', 'MODULE_PAYMENT_CGP_" . $this->module_cgp_text . "_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '9', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now())");
       
        @tep_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Checkout display mode', 'MODULE_PAYMENT_CGP_" . $this->module_cgp_text . "_CHECKOUT_DISPLAY', 'Text', 'Set checkout display mode', '6', '21','tep_cfg_select_option(array(\'Text\', \'Logo\', \'Text and Logo\'), ', now())");
        @tep_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort order of display.', 'MODULE_PAYMENT_CGP_" . $this->module_cgp_text . "_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0' , now())");
        @tep_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Drop table on deinstall', 'MODULE_PAYMENT_CGP_" . $this->module_cgp_text . "_DROP_TABLE', 'False', 'Drop the `CGP_orders_table` on deinstall of this module. ONLY DO THIS IF ALL THE ORDERS HAVE BEEN PROCESSED AND YOU ARE INSTALLING A NEWER VERSION OF THIS MODULE. AFFECTS ALL CGP MODULES!', '6', '0', 'tep_cfg_select_option(array(\'False\', \'True\'), ', now())");
        @tep_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Control URL', 'MODULE_PAYMENT_CGP_" . $this->module_cgp_text . "_REMEMBER', '', 'Set the Control URL in your Card Gate Merchant back-office to: <br><b>" . $cgp_control . "</b>', '6', '0',true, now())");
        
        $query = 'CREATE TABLE IF NOT EXISTS `CGP_orders_table` (' . 'ref_id INT(11)  NOT NULL AUTO_INCREMENT PRIMARY KEY,' . 'date_ordered datetime NOT NULL,' . 'module VARCHAR(32)  NOT NULL,' . 'customer_id INT(11) NOT NULL DEFAULT 0,' . 'orderstr TEXT NOT NULL,' . 'amount INT(11) NOT NULL DEFAULT 0,' . 'status INT(11) NOT NULL DEFAULT 0,' . 'transaction_id VARCHAR(32) NOT NULL DEFAULT 0,' . 'is_test INT(11) NOT NULL DEFAULT 0,' . 'order_id INT(11) NOT NULL' . ')';
        
        tep_db_query($query);
        
        // make sure order status exist
        $pm_status = array();
        $pm_status[0] = array(
            '1000',
            'Payment complete'
        );
        $pm_status[1] = array(
            '1001',
            'Payment cancelled'
        );
        
        for ($i = 0; $i < count($pm_status); $i ++) {
            $language_result = tep_db_query('SELECT languages_id FROM ' . TABLE_LANGUAGES);
            while ($languages = tep_db_fetch_array($language_result)) {
                $language_id = $languages['languages_id'];
                $sql = 'SELECT orders_status_id FROM ' . TABLE_ORDERS_STATUS . ' WHERE orders_status_id = ' . $pm_status[$i][0] . ' and language_id = ' . $language_id;
                $status_result = tep_db_query($sql);
                $id = tep_db_fetch_array($status_result);
                if ($id['orders_status_id'] != $pm_status[$i][0]) {
                    tep_db_query('INSERT INTO ' . TABLE_ORDERS_STATUS . ' (orders_status_id, language_id, orders_status_name) VALUES (' . $pm_status[$i][0] . ',' . $language_id . ',"' . $pm_status[$i][1] . '")');
                }
            }
        }
    }

    /**
     * Remove method drops `CGP_orders_table` table
     */
    function remove() {
        $keys = "";
        $keys_array = $this->keys();
        for ($i = 0; $i < sizeof($keys_array); $i ++) {
            $keys .= "'" . $keys_array[$i] . "',";
        }
        $keys = substr($keys, 0, - 1);
        
        if (MODULE_PAYMENT_CGP_DROP_TABLE === 'True') {
            tep_db_query("DROP TABLE IF EXISTS `CGP_orders_table`");
        }
        
        tep_db_query("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key IN (" . $keys . ")");
    }

    /**
     * Module installation keys
     *
     * @return array
     */
    function keys() {
        return array(
            'MODULE_PAYMENT_CGP_' . $this->module_cgp_text . '_STATUS',
            'MODULE_PAYMENT_CGP_' . $this->module_cgp_text . '_MODE',
            'MODULE_PAYMENT_CGP_' . $this->module_cgp_text . '_SITEID',
            'MODULE_PAYMENT_CGP_' . $this->module_cgp_text . '_KEYCODE',
            'MODULE_PAYMENT_CGP_' . $this->module_cgp_text . '_CHECKOUT_DISPLAY',
            'MODULE_PAYMENT_CGP_' . $this->module_cgp_text . '_LANGUAGE',
            'MODULE_PAYMENT_CGP_' . $this->module_cgp_text . '_ZONE',
            'MODULE_PAYMENT_CGP_' . $this->module_cgp_text . '_SORT_ORDER',
            'MODULE_PAYMENT_CGP_' . $this->module_cgp_text . '_ORDER_INITIAL_STATUS_ID',
            'MODULE_PAYMENT_CGP_' . $this->module_cgp_text . '_ORDER_PAID_STATUS_ID',
            'MODULE_PAYMENT_CGP_' . $this->module_cgp_text . '_ORDER_FAILED_STATUS_ID',
            'MODULE_PAYMENT_CGP_' . $this->module_cgp_text . '_DROP_TABLE',
            'MODULE_PAYMENT_CGP_' . $this->module_cgp_text . '_REMEMBER'
        );
    }
    private function checkpayments(){
       $payments = array(   'mastercard',
                            'vpay',
                            'visa',
                            'maestro',
                            'americanexpress',
                            'ideal',
                            'directebanking', 
                            'sofortueberweising',
                            'mistercash',
                            'afterpay',
                            'klarna',
                            'bitcoin',
                            'paypal',
                            'paysafecard',
                            'giropay',
                            'banktransfer',
                            'directdebit',
                            'przelewy24',
                            'billink',
                            'idealqr',
                            'paysafecash',
                            'giftcard'
       );
       return $payments;
    }
}

?>