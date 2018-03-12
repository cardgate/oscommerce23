<?php

/**
 * $Id: cgp.php 30 2011-12-23 17:35:13Z h0ax $
 *
 * osCommerce, Open Source E-Commerce Solutions
 * http://www.oscommerce.com
 *
 * Copyright (c) 2007 osCommerce
 *
 * Released under the GNU General Public License
 *
 * Modified by Ramon de la Fuente (ramon@future500.nl) for new osCommerce checkout (>Nov 2002) procedure.
 * Tested with CGP eCommerce version Jan 2003 and later. For more
 * infomation about Card Gate Plus: http://www.cardgateplus.com
 *
 * Modified by BZ (support@cardgate.com):
 * - Removed IP check and build in HASH check
 *  version 2.10 2010-06-21
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
 * version 2.36 2013-03-07
 */
chdir( '../../../../' );
require ( 'includes/application_top.php' );
if ( isset( $_REQUEST ['cgp_notify'] ) && $_REQUEST ['cgp_notify'] == TRUE ) {

    if ( $_REQUEST ['cgpstatusid'] > 300 && $_REQUEST ['cgpstatusid'] < 400 ) {
        tep_redirect( tep_href_link( FILENAME_CHECKOUT_PAYMENT, 'error_message=Payment failed or canceled by user.', 'SSL' ) );
    } else {
        global $cart;

        if ( $cart ) {
            $cart->reset( true );
        }
        tep_redirect( tep_href_link( FILENAME_CHECKOUT_SUCCESS, '', 'SSL' ) );
    }
} else {

    // check field type of transaction_id in cardgate table 
    check_cgp_table();
   
    include ( DIR_WS_LANGUAGES . $language . '/' . FILENAME_CHECKOUT_PROCESS );

    $ref = $_POST ['ref'];
    $extra = $_POST ['extra'];
    $ar = explode( '|', $extra );
    $order_id = $ar [0];
    $cgp_id = $ar [1];
    $transaction_id = $_POST ['transactionid'];
    $is_test = $_POST ['is_test'];
    $status = $_POST ['status'];
    $currency = $_POST ['currency'];
    $amount = $_POST ['amount'];

    $cgp_data = tep_db_fetch_array( tep_db_query( "SELECT * FROM CGP_orders_table WHERE ref_id=$cgp_id" ) );
    if ( ( $_POST ['billing_option'] != 'creditcard' ) && ( $_POST ['billing_option'] != 'directebanking' ) ) {
        $module_cgp_text = strtoupper( $_POST ['billing_option'] );
    } else {
        $module_cgp_text = strtoupper( str_replace( "cgp_", "", $cgp_data ['module'] ) );
    }

    $my_order_query = tep_db_query( "select orders_status, currency, currency_value from " . TABLE_ORDERS . " where orders_id = '$order_id'" );
    if ( tep_db_num_rows( $my_order_query ) <= 0 ) {
        exit( 'Missing order in database, order id:' . $order_id );
    }
    $hashKey = @constant( "MODULE_PAYMENT_CGP_" . $module_cgp_text . "_KEYCODE" );

    $hash_uncoded = ( $is_test == 1 ? "TEST" : "" ) . $transaction_id . $currency . $amount . $ref . $status . $hashKey;

    if ( md5( $hash_uncoded ) != $_POST ["hash"] ) {
        exit( 'Hash did not match' );
    }

    require ( DIR_WS_CLASSES . 'order.php' );

    $order = unserialize( base64_decode( $cgp_data ['orderstr'] ) );

    $order_total_query = tep_db_query( "select value from " . TABLE_ORDERS_TOTAL . " where orders_id = '" . ( int ) $order_id . "' and class = 'ot_total'" );
    $order_total = tep_db_fetch_array( $order_total_query );
    $order_amount = format_raw( $order_total ['value'], $order->info ['currency'] ) * 100;

    if ( ( $order_amount != $amount ) && ( round( $order_amount ) != $amount ) && ( abs( $amount - $order_amount ) > 1 ) ) {

        $comment_status = "Order total amount did not match CardGatePlus's gross total amount!";
        $order_status = constant( "MODULE_PAYMENT_CGP_" . $module_cgp_text . "_ORDER_FAILED_STATUS_ID" );

        // Add status history
        $sql_data_array = array(
            'orders_id' => $order_id,
            'orders_status_id' => $order_status,
            'date_added' => 'now()',
            'customer_notified' => '0',
            'comments' => $comment_status
        );
        tep_db_perform( TABLE_ORDERS_STATUS_HISTORY, $sql_data_array );

        $sql_data_array = array(
            'orders_status' => $order_status,
            'last_modified' => 'now()'
        );
        tep_db_perform( TABLE_ORDERS, $sql_data_array, 'update', 'orders_id = ' . $order_id );
        exit( $comment_status );
    }

    // process as long as the order has not been completed
    if ( $cgp_data ['status'] != 200 ) {
        // update cardgate orders table
        $sql_data_array = array(
            'transaction_id' => $transaction_id,
            'status' => $status,
            'order_id' => $order_id
        );
        tep_db_perform( 'CGP_orders_table', $sql_data_array, 'update', 'ref_id =' . $cgp_id );

        // Add initial status to order history
        if ( $cgp_data ['status'] == 0 ) {
            $comment_status = "";
            $order_status = constant( "MODULE_PAYMENT_CGP_" . $module_cgp_text . "_ORDER_INITIAL_STATUS_ID" );

            if ( $order_status == 0 ) {
                $order_status = 1;
            }

            $sql_data_array = array(
                'orders_id' => $order_id,
                'orders_status_id' => $order_status,
                'date_added' => 'now()',
                'customer_notified' => '0',
                'comments' => $comment_status
            );
            tep_db_perform( TABLE_ORDERS_STATUS_HISTORY, $sql_data_array );
        }

        // Update order status
        switch ( $status ) {
            case '200' :
                $comment_status = "Payment complete.";
                $order_status = constant( "MODULE_PAYMENT_CGP_" . $module_cgp_text . "_ORDER_PAID_STATUS_ID" );
                if ( $order_status == 0 ) {
                    $order_status = 1000;
                }
                break;
            case '300' :
            case '301' :
                $comment_status = "Payment failed or canceled by user.";
                $order_status = constant( "MODULE_PAYMENT_CGP_" . $module_cgp_text . "_ORDER_FAILED_STATUS_ID" );
                if ( $order_status == 0 ) {
                    $order_status = 1001;
                }
                break;
            default :
                $comment_status = 'Payment pending';
                $order_status = constant( "MODULE_PAYMENT_CGP_" . $module_cgp_text . "_ORDER_INITIAL_STATUS_ID" );
                if ( $order_status == 0 ) {
                    $order_status = 1;
                }
                break;
        }

        $customer_notification = ( SEND_EMAILS == 'true' ) ? '1' : '0';

        // Add status history
        $sql_data_array = array(
            'orders_id' => $order_id,
            'orders_status_id' => $order_status,
            'date_added' => 'now()',
            'customer_notified' => $customer_notification,
            'comments' => $comment_status
        );
        tep_db_perform( TABLE_ORDERS_STATUS_HISTORY, $sql_data_array );

        $sql_data_array = array(
            'orders_status' => $order_status,
            'last_modified' => 'now()'
        );
        tep_db_perform( TABLE_ORDERS, $sql_data_array, 'update', 'orders_id = ' . $order_id );

        if ( empty( $order_totals ) ) {
            require ( DIR_WS_CLASSES . 'order_total.php' );

            $order_total_modules = new order_total();
            $order_totals = $order_total_modules->process();
        }

        // initialized for the email confirmation
        $products_ordered = '';
        $total_weight = 0;
        $total_tax = 0;
        $total_cost = 0;

        for ( $i = 0, $n = sizeof( $order->products ); $i < $n; $i ++ ) {
            // stock update if order is paid
            if ( $status == '200' ) {
                if ( STOCK_LIMITED == 'true' ) {
                    if ( DOWNLOAD_ENABLED == 'true' ) {
                        $stock_query_raw = "SELECT products_quantity, pad.products_attributes_filename
                                                FROM " . TABLE_PRODUCTS . " p
                                                LEFT JOIN " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                                                ON p.products_id=pa.products_id
                                                    LEFT JOIN " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " pad
                                                ON pa.products_attributes_id=pad.products_attributes_id
                                                WHERE p.products_id = '" . tep_get_prid( $order->products [$i] ['id'] ) . "'";
                        // Will work with only one option for downloadable products
                        // otherwise, we have to build the query dynamically with a loop
                        $products_attributes = $order->products [$i] ['attributes'];
                        if ( is_array( $products_attributes ) ) {
                            $stock_query_raw .= " AND pa.options_id = '" . $products_attributes [0] ['option_id'] . "' AND pa.options_values_id = '" . $products_attributes [0] ['value_id'] . "'";
                        }
                        $stock_query = tep_db_query( $stock_query_raw );
                    } else {
                        $stock_query = tep_db_query( "select products_quantity from " . TABLE_PRODUCTS . " where products_id = '" . tep_get_prid( $order->products [$i] ['id'] ) . "'" );
                    }
                    if ( tep_db_num_rows( $stock_query ) > 0 ) {
                        $stock_values = tep_db_fetch_array( $stock_query );
                        // do not decrement quantities if products_attributes_filename exists
                        if ( ( DOWNLOAD_ENABLED != 'true' ) || (!$stock_values ['products_attributes_filename'] ) ) {
                            $stock_left = $stock_values ['products_quantity'] - $order->products [$i] ['qty'];
                        } else {
                            $stock_left = $stock_values ['products_quantity'];
                        }
                        tep_db_query( "update " . TABLE_PRODUCTS . " set products_quantity = '" . $stock_left . "' where products_id = '" . tep_get_prid( $order->products [$i] ['id'] ) . "'" );
                        if ( ( $stock_left < 1 ) && ( STOCK_ALLOW_CHECKOUT == 'false' ) ) {
                            tep_db_query( "update " . TABLE_PRODUCTS . " set products_status = '0' where products_id = '" . tep_get_prid( $order->products [$i] ['id'] ) . "'" );
                        }
                    }
                } // Decrease stock ended
                // Update products_ordered (for bestsellers list)
                tep_db_query( "update " . TABLE_PRODUCTS . " set products_ordered = products_ordered + " . sprintf( '%d', $order->products [$i] ['qty'] ) . " where products_id = '" . tep_get_prid( $order->products [$i] ['id'] ) . "'" );

                // ------insert customer choosen option to order--------
                $attributes_exist = '0';
                $products_ordered_attributes = '';
                if ( isset( $order->products [$i] ['attributes'] ) ) {
                    $attributes_exist = '1';
                    for ( $j = 0, $n2 = sizeof( $order->products [$i] ['attributes'] ); $j < $n2; $j ++ ) {
                        if ( DOWNLOAD_ENABLED == 'true' ) {
                            $attributes_query = "select popt.products_options_name, poval.products_options_values_name, pa.options_values_price, pa.price_prefix, pad.products_attributes_maxdays, pad.products_attributes_maxcount , pad.products_attributes_filename
                                                    from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_OPTIONS_VALUES . " poval, " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                                                    left join " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " pad
                                                    on pa.products_attributes_id=pad.products_attributes_id
                                                    where pa.products_id = '" . $order->products [$i] ['id'] . "'
                                                    and pa.options_id = '" . $order->products [$i] ['attributes'] [$j] ['option_id'] . "'
                                                    and pa.options_id = popt.products_options_id
                                                    and pa.options_values_id = '" . $order->products [$i] ['attributes'] [$j] ['value_id'] . "'
                                                    and pa.options_values_id = poval.products_options_values_id
                                                    and popt.language_id = '" . $languages_id . "'
                                                    and poval.language_id = '" . $languages_id . "'";
                            $attributes = tep_db_query( $attributes_query );
                        } else {
                            $attributes = tep_db_query( "select popt.products_options_name, poval.products_options_values_name, pa.options_values_price, pa.price_prefix from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_OPTIONS_VALUES . " poval, " . TABLE_PRODUCTS_ATTRIBUTES . " pa where pa.products_id = '" . $order->products [$i] ['id'] . "' and pa.options_id = '" . $order->products [$i] ['attributes'] [$j] ['option_id'] . "' and pa.options_id = popt.products_options_id and pa.options_values_id = '" . $order->products [$i] ['attributes'] [$j] ['value_id'] . "' and pa.options_values_id = poval.products_options_values_id and popt.language_id = '" . $languages_id . "' and poval.language_id = '" . $languages_id . "'" );
                        }
                        $attributes_values = tep_db_fetch_array( $attributes );
                        $products_ordered_attributes .= "\n\t" . $attributes_values ['products_options_name'] . ' ' . $attributes_values ['products_options_values_name'];
                    }
                }
            }
            $products_ordered .= $order->products [$i] ['qty'] . ' x ' . $order->products [$i] ['name'] . ' (' . $order->products [$i] ['model'] . ') = ' . $currencies->display_price( $order->products [$i] ['final_price'], $order->products [$i] ['tax'], $order->products [$i] ['qty'] ) . $products_ordered_attributes . "\n";
        }

        // --- Beginning of addition: Ultimate HTML Emails ---//
        if ( defined( 'ULTIMATE_HTML_EMAIL_LAYOUT' ) && EMAIL_USE_HTML == 'true' ) {

            $order_data = unserialize( base64_decode( $cgp_data ['orderstr'] ) );
            $sendto = $order_data->delivery;
            $sendto ['address_format_id'] = $sendto ['format_id'];
            $billto = $order_data->billing;
            $billto ['address_format_id'] = $billto ['format_id'];
            $customer_id = $cgp_data ['customer_id'];

            require ( DIR_WS_MODULES . 'UHtmlEmails/' . ULTIMATE_HTML_EMAIL_LAYOUT . '/checkout_process.php' );

            $email_order = $html_email;
        } else { // Send text email
            // lets start with the email confirmation
            $email_order = STORE_NAME . "\n" . EMAIL_SEPARATOR . "\n" . EMAIL_TEXT_ORDER_NUMBER . ' ' . $order_id . "\n" . EMAIL_TEXT_INVOICE_URL . ' ' . tep_href_link( FILENAME_ACCOUNT_HISTORY_INFO, 'order_id=' . $order_id, 'SSL', false ) . "\n" . EMAIL_TEXT_DATE_ORDERED . ' ' . strftime( DATE_FORMAT_LONG ) . "\n\n";

            if ( $order->info ['comments'] ) {
                $email_order .= tep_db_output( $order->info ['comments'] ) . "\n\n";
            }

            $email_order .= EMAIL_TEXT_PRODUCTS . "\n" . EMAIL_SEPARATOR . "\n" . $products_ordered . EMAIL_SEPARATOR . "\n";

            $order_data = unserialize( base64_decode( $cgp_data ['orderstr'] ) );
            $sendto = $order_data->delivery;
            $sendto ['address_format_id'] = $sendto ['format_id'];
            $billto = $order_data->billing;
            $billto ['address_format_id'] = $billto ['format_id'];
            $customer_id = $cgp_data ['customer_id'];

            for ( $i = 0, $n = sizeof( $order_totals ); $i < $n; $i ++ ) {
                $email_order .= strip_tags( $order_totals [$i] ['title'] ) . ' ' . strip_tags( $order_totals [$i] ['text'] ) . "\n";
            }

            if ( $order->content_type != 'virtual' ) {
                $email_order .= "\n" . EMAIL_TEXT_DELIVERY_ADDRESS . "\n" . EMAIL_SEPARATOR . "\n" . tep_address_label( $customer_id, $sendto, 0, '', "\n" ) . "\n";
            }

            $email_order .= "\n" . EMAIL_TEXT_BILLING_ADDRESS . "\n" . EMAIL_SEPARATOR . "\n" . tep_address_label( $customer_id, $billto, 0, '', "\n" ) . "\n\n";

            // load selected payment module
            require ( DIR_WS_CLASSES . 'payment.php' );
            $payment = $cgp_data ['module'];
            $payment_class = new payment( $payment );

            if ( is_object( $$payment ) ) {
                $email_order .= EMAIL_TEXT_PAYMENT_METHOD . "\n" . EMAIL_SEPARATOR . "\n";
                $payment_class = $$payment;
                if ( !empty( $order->info ['payment_method'] ) ) {
                    $email_order .= $order->info ['payment_method'] . "\n\n";
                } else {
                    $email_order .= $payment_class->title . "\n\n";
                }
                if ( $payment_class->email_footer ) {
                    $email_order .= $payment_class->email_footer . "\n\n";
                }
            }
        }

        if ( $customer_notification ) {
            tep_mail( $order->customer ['firstname'] . ' ' . $order->customer ['lastname'], $order->customer ['email_address'], EMAIL_TEXT_SUBJECT, $email_order, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS );

            // send emails to other people
            if ( SEND_EXTRA_ORDER_EMAILS_TO != '' ) {
                tep_mail( '', SEND_EXTRA_ORDER_EMAILS_TO, EMAIL_TEXT_SUBJECT, $email_order, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS );
            }
        }

        // load the after_process function from the payment modules
        $payment_class->after_process();

        // clear the cart, but keep the session
        if ( $status == '200' || $status == '701' || $status == '710' ) {
            $whos_online_query = tep_db_query( "select customer_id, full_name, ip_address, time_entry, time_last_click, last_page_url, session_id from " . TABLE_WHOS_ONLINE . " where customer_id = " . ( int ) $customer_id );
            $online_customer = tep_db_fetch_array( $whos_online_query );
            $whos_online_query = tep_db_query( "select customer_id, full_name, ip_address, time_entry, time_last_click, last_page_url, session_id from " . TABLE_WHOS_ONLINE . " where customer_id = " . ( int ) $customer_id );

            tep_session_destroy();
            $HTTP_COOKIE_VARS ['osCsid'] = $online_customer ['session_id'];
            tep_session_start();
            tep_session_unregister( 'cart' );
            tep_session_unregister( 'cartID' );
            tep_session_close();
        }
    }

    echo $transaction_id . "." . $status;
}

// format prices without currency formatting
function format_raw( $number, $currency_code = '', $currency_value = '' ) {

    global $currencies, $currency;

    $number = preg_replace( "/^[^0-9\.]/", "", $number );

    if ( empty( $currency_code ) || !$currencies->is_set( $currency_code ) ) {
        $currency_code = $currency;
    }

    if ( empty( $currency_value ) || !is_numeric( $currency_value ) ) {
        $currency_value = $currencies->currencies [$currency_code] ['value'];
    }

    return number_format( tep_round( $number * $currency_value, $currencies->currencies [$currency_code] ['decimal_places'] ), $currencies->currencies [$currency_code] ['decimal_places'], '.', '' );
}

function check_cgp_table() {
    $sql = "SHOW COLUMNS FROM CGP_orders_table";
    $result = tep_db_query( $sql );
    while ( $row = mysql_fetch_array( $result ) ) {
        if ( $row[0] == 'transaction_id' ) {
            if ($row['Type'] == 'int(11)'){
                $sql = 'ALTER TABLE CGP_orders_table MODIFY transaction_id VARCHAR(32)';
               $res =  tep_db_query( $sql );
            }
        }
    }
}
?>