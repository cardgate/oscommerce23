<?php
/*
 * $Id: cgp_orders.php 30 2011-12-23 17:35:13Z h0ax $
 *
 * osCommerce, Open Source E-Commerce Solutions
 * http://www.oscommerce.com
 *
 * Copyright (c) 2003 osCommerce
 *
 * Released under the GNU General Public License
 *
 * Modified by Ramon de la Fuente (ramon@future500.nl) for new osCommerce checkout (>Nov 2002) procedure.
 * Tested with CGP eCommerce version Jan 2003 and later. For more
 * infomation about Card Gate Plus: http://www.cardgateplus.com
 *
 * version 2.01 2009-05-20
 * 
 * Modified by Paul Saparov (support@cardgate.com)
 * version 2.31 2011-11-02
 */

require('includes/application_top.php');

require(DIR_WS_CLASSES . 'currencies.php');
$currencies = new currencies();

$orders_statuses = array();
$orders_status_array = array();
$orders_status_query = tep_db_query("select distinct status as orders_status_id, status as orders_status_name from CGP_orders_table");
while ($orders_status = tep_db_fetch_array($orders_status_query)) {
$orders_statuses[] = array('id' => $orders_status['orders_status_id'],
                           'text' => $orders_status['orders_status_name']);
$orders_status_array[$orders_status['orders_status_id']] = $orders_status['orders_status_name'];
}

$action = (isset($HTTP_GET_VARS['action']) ? $HTTP_GET_VARS['action'] : '');

if (tep_not_null($action)) {
    switch ($action) {
      case 'insertconfirm':
        $oID = tep_db_prepare_input($HTTP_GET_VARS['oID']);

        $query = "SELECT * FROM CGP_orders_table WHERE ref_id=" . (int)$oID;
        $order_db = tep_db_query($query);
        if ($order_arr = tep_db_fetch_array($order_db)) {

          require_once(DIR_WS_CLASSES . 'order.php');
          if ( $order = unserialize(stripslashes($order_arr['orderstr'])) ) {

              chdir('../');
              require(DIR_WS_CLASSES . 'order_total.php');

              $order_total_modules = new order_total;
              $order_totals = $order_total_modules->process();
              chdir('admin/');

              $sql_data_array = array('customers_id' => $order_arr['customer_id'],
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
                                  'date_purchased' => $order_arr['date_ordered'],
                                  'orders_status' => ('200' != $order_arr['status']?MODULE_PAYMENT_CGP_IDEAL_ORDER_INITIAL_STATUS_ID:MODULE_PAYMENT_CGP_IDEAL_ORDER_PAID_STATUS_ID),
                                  'currency' => $order->info['currency'],
                                  'currency_value' => $order->info['currency_value']);
              tep_db_perform(TABLE_ORDERS, $sql_data_array);
              $insert_id = tep_db_insert_id();

              $query = "UPDATE CGP_orders_table SET order_id=" . $insert_id . " WHERE ref_id=" . (int)$oID;
              tep_db_query($query);

              for ($i=0, $n=sizeof($order_totals); $i<$n; $i++) {
                $sql_data_array = array('orders_id' => $insert_id,
                                    'title' => $order_totals[$i]['title'],
                                    'text' => $order_totals[$i]['text'],
                                    'value' => $order_totals[$i]['value'],
                                    'class' => $order_totals[$i]['code'],
                                    'sort_order' => $order_totals[$i]['sort_order']);
                 tep_db_perform(TABLE_ORDERS_TOTAL, $sql_data_array);
              }

              $sql_data_array = array('orders_id' => $insert_id,
                                      'orders_status_id' => $order->info['order_status'],
                                      'date_added' => 'now()',
                                      'customer_notified' => '0',
                                      'comments' => $order->info['comments']);
              tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);

            // initialized for the email confirmation
              $products_ordered = '';
              $subtotal = 0;
              $total_tax = 0;

              for ($i=0, $n=sizeof($order->products); $i<$n; $i++) {
            // Stock Update - Joao Correia
                if (STOCK_LIMITED == 'true') {
                  if (DOWNLOAD_ENABLED == 'true') {
                    $stock_query_raw = "SELECT products_quantity, pad.products_attributes_filename
                                        FROM " . TABLE_PRODUCTS . " p
                                        LEFT JOIN " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                                         ON p.products_id=pa.products_id
                                        LEFT JOIN " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " pad
                                         ON pa.products_attributes_id=pad.products_attributes_id
                                        WHERE p.products_id = '" . tep_get_prid($order->products[$i]['id']) . "'";
            // Will work with only one option for downloadable products
            // otherwise, we have to build the query dynamically with a loop
                    $products_attributes = $order->products[$i]['attributes'];
                    if (is_array($products_attributes)) {
                      $stock_query_raw .= " AND pa.options_id = '" . $products_attributes[0]['option_id'] . "' AND pa.options_values_id = '" . $products_attributes[0]['value_id'] . "'";
                    }
                    $stock_query = tep_db_query($stock_query_raw);
                  } else {
                    $stock_query = tep_db_query("select products_quantity from " . TABLE_PRODUCTS . " where products_id = '" . tep_get_prid($order->products[$i]['id']) . "'");
                  }
                  if (tep_db_num_rows($stock_query) > 0) {
                    $stock_values = tep_db_fetch_array($stock_query);
            // do not decrement quantities if products_attributes_filename exists
                    if ((DOWNLOAD_ENABLED != 'true') || (!$stock_values['products_attributes_filename'])) {
                      $stock_left = $stock_values['products_quantity'] - $order->products[$i]['qty'];
                    } else {
                      $stock_left = $stock_values['products_quantity'];
                    }
                    tep_db_query("update " . TABLE_PRODUCTS . " set products_quantity = '" . $stock_left . "' where products_id = '" . tep_get_prid($order->products[$i]['id']) . "'");
                    if ( ($stock_left < 1) && (STOCK_ALLOW_CHECKOUT == 'false') ) {
                      tep_db_query("update " . TABLE_PRODUCTS . " set products_status = '0' where products_id = '" . tep_get_prid($order->products[$i]['id']) . "'");
                    }
                  }
                }

            // Update products_ordered (for bestsellers list)
                tep_db_query("update " . TABLE_PRODUCTS . " set products_ordered = products_ordered + " . sprintf('%d', $order->products[$i]['qty']) . " where products_id = '" . tep_get_prid($order->products[$i]['id']) . "'");

                $sql_data_array = array('orders_id' => $insert_id,
                                        'products_id' => tep_get_prid($order->products[$i]['id']),
                                        'products_model' => $order->products[$i]['model'],
                                        'products_name' => $order->products[$i]['name'],
                                        'products_price' => $order->products[$i]['price'],
                                        'final_price' => $order->products[$i]['final_price'],
                                        'products_tax' => $order->products[$i]['tax'],
                                        'products_quantity' => $order->products[$i]['qty']);
                tep_db_perform(TABLE_ORDERS_PRODUCTS, $sql_data_array);
                $order_products_id = tep_db_insert_id();

            //------insert customer choosen option to order--------
                $attributes_exist = '0';
                $products_ordered_attributes = '';
                if (isset($order->products[$i]['attributes'])) {
                  $attributes_exist = '1';
                  for ($j=0, $n2=sizeof($order->products[$i]['attributes']); $j<$n2; $j++) {
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

                    $sql_data_array = array('orders_id' => $insert_id,
                                            'orders_products_id' => $order_products_id,
                                            'products_options' => $attributes_values['products_options_name'],
                                            'products_options_values' => $attributes_values['products_options_values_name'],
                                            'options_values_price' => $attributes_values['options_values_price'],
                                            'price_prefix' => $attributes_values['price_prefix']);
                    tep_db_perform(TABLE_ORDERS_PRODUCTS_ATTRIBUTES, $sql_data_array);

                    if ((DOWNLOAD_ENABLED == 'true') && isset($attributes_values['products_attributes_filename']) && tep_not_null($attributes_values['products_attributes_filename'])) {
                      $sql_data_array = array('orders_id' => $insert_id,
                                              'orders_products_id' => $order_products_id,
                                              'orders_products_filename' => $attributes_values['products_attributes_filename'],
                                              'download_maxdays' => $attributes_values['products_attributes_maxdays'],
                                              'download_count' => $attributes_values['products_attributes_maxcount']);
                      tep_db_perform(TABLE_ORDERS_PRODUCTS_DOWNLOAD, $sql_data_array);
                    }
                    $products_ordered_attributes .= "\n\t" . $attributes_values['products_options_name'] . ' ' . $attributes_values['products_options_values_name'];
                  }
                }
            //------insert customer choosen option eof ----
                $total_weight += ($order->products[$i]['qty'] * $order->products[$i]['weight']);
                $total_tax += tep_calculate_tax($total_products_price, $products_tax) * $order->products[$i]['qty'];
                $total_cost += $total_products_price;

                $products_ordered .= $order->products[$i]['qty'] . ' x ' . $order->products[$i]['name'] . ' (' . $order->products[$i]['model'] . ') = ' . $currencies->display_price($order->products[$i]['final_price'], $order->products[$i]['tax'], $order->products[$i]['qty']) . $products_ordered_attributes . "\n";
              }

              tep_redirect(tep_href_link('cgp_orders.php', tep_get_all_get_params(array('oID', 'action'))));
          }
        }

        break;
      case 'deleteconfirm':
        $oID = tep_db_prepare_input($HTTP_GET_VARS['oID']);

        $query = "DELETE FROM CGP_orders_table WHERE ref_id=" . $oID;
        tep_db_query($query);
        tep_redirect(tep_href_link('cgp_orders.php', tep_get_all_get_params(array('oID', 'action'))));
        break;
    }
  }

  if (($action == 'edit') && isset($HTTP_GET_VARS['oID'])) {
    $oID = tep_db_prepare_input($HTTP_GET_VARS['oID']);

    $orders_query = tep_db_query("select ref_id from CGP_orders_table where ref_id = '" . (int)$oID . "'");
    $order_exists = true;
    if (!tep_db_num_rows($orders_query)) {
      $order_exists = false;
      $messageStack->add(sprintf(ERROR_ORDER_DOES_NOT_EXIST, $oID), 'error');
    }
}

  require_once(DIR_WS_CLASSES . 'order.php');
  require(DIR_WS_INCLUDES . 'template_top.php');
?>

    <table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td width="100%"><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
            <td class="pageHeading" align="right"><?php echo tep_draw_separator('pixel_trans.gif', 1, HEADING_IMAGE_HEIGHT); ?></td>
            <td align="right"><table border="0" width="100%" cellspacing="0" cellpadding="0">
              <tr><?php echo tep_draw_form('orders', 'cgp_orders.php', '', 'get'); ?>
                <td class="smallText" align="right"><?php echo HEADING_TITLE_SEARCH . ' ' . tep_draw_input_field('oID', '', 'size="12"') . tep_draw_hidden_field('action', 'edit'); ?></td>
              </form></tr>
              <tr><?php echo tep_draw_form('status', 'cgp_orders.php', '', 'get'); ?>
                <td class="smallText" align="right"><?php echo HEADING_TITLE_STATUS . ' ' . tep_draw_pull_down_menu('status', array_merge(array(array('id' => '', 'text' => TEXT_ALL_ORDERS)), $orders_statuses), '', 'onChange="this.form.submit();"'); ?></td>
              </form></tr>
            </table></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_CUSTOMERS; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ORDER_TOTAL; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_DATE_PURCHASED; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_STATUS; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ORDERS_ID; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_MERCHANT_REF; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php

      if ( isset($HTTP_GET_VARS['status']) && is_numeric($HTTP_GET_VARS['status'])  ) {
          $where = ' AND status=' . (int)$HTTP_GET_VARS['status'];
      } else {
          $where = '';
      }

      $orders_query_raw = "
        select
            o.ref_id as orders_id,
            o.module,
            o.customer_id,
            o.orderstr,
            o.amount,
            o.amount as order_total,
            o.date_ordered as date_purchased,
            o.status as orders_status_name,
            o.transaction_id,
            o.status,
            o.is_test,
            o.order_id
        from
            CGP_orders_table o join " . TABLE_CUSTOMERS . " c
                on (o.customer_id = c.customers_id)
            ". $where . "
        order by
            o.ref_id DESC";
      
       
      
    $orders_split = new splitPageResults($HTTP_GET_VARS['page'], MAX_DISPLAY_SEARCH_RESULTS, $orders_query_raw, $orders_query_numrows);
    $orders_query = tep_db_query($orders_query_raw);
    while ($orders = tep_db_fetch_array($orders_query)) {
        $order_decoded = base64_decode($orders['orderstr']);
        if ( $obj_orders = unserialize($order_decoded) ) {
            $orders['customers_name'] = $obj_orders->customer['firstname'] . ' ' . $obj_orders->customer['lastname'];

            if ((!isset($HTTP_GET_VARS['oID']) || (isset($HTTP_GET_VARS['oID']) && ($HTTP_GET_VARS['oID'] == $orders['orders_id']))) && !isset($oInfo)) {
                $oInfo = new objectInfo($orders);
              }

              if (isset($oInfo) && is_object($oInfo) && ($orders['orders_id'] == $oInfo->orders_id)) {
                echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)">' . "\n";
              } else {
                echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link('cgp_orders.php', tep_get_all_get_params(array('oID')) . 'oID=' . $orders['orders_id']) . '\'">' . "\n";
              }
?>
                <td class="dataTableContent"><?php echo $orders['customers_name'] . (1==$orders['is_test']?' (TEST)':'') ?></td>
                <td class="dataTableContent" align="right"><?php echo number_format(($orders['order_total']/100), 2); ?></td>
                <td class="dataTableContent" align="center"><?php echo tep_datetime_short($orders['date_purchased']); ?></td>
                <td class="dataTableContent" align="right"><?php echo $orders['orders_status_name']; ?></td>
                <td class="dataTableContent" align="right"><?php if ($orders['order_id']>0) { echo '<a href="' . tep_href_link(FILENAME_ORDERS, tep_get_all_get_params(array('oID', 'action')) . 'oID=' . $orders['order_id'] . '&action=edit') . '">' . tep_image(DIR_WS_ICONS . 'preview.gif', ICON_PREVIEW) . '</a>&nbsp;' . $orders['order_id']; } else { echo '0'; } ?></td>
                <td class="dataTableContent" align="right"><?php echo $orders['orders_id'] . '|' . $orders['order_id']; ?></td>
                <td class="dataTableContent" align="right"><?php if (isset($oInfo) && is_object($oInfo) && ($orders['orders_id'] == $oInfo->orders_id)) { echo tep_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); } else { echo '<a href="' . tep_href_link('cgp_orders.php', tep_get_all_get_params(array('oID')) . 'oID=' . $orders['orders_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
        }
    }
?>
              <tr>
                <td colspan="5"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText" valign="top"><?php echo $orders_split->display_count($orders_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $HTTP_GET_VARS['page'], TEXT_DISPLAY_NUMBER_OF_ORDERS); ?></td>
                    <td class="smallText" align="right"><?php echo $orders_split->display_links($orders_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $HTTP_GET_VARS['page'], tep_get_all_get_params(array('page', 'oID', 'action'))); ?></td>
                  </tr>
                </table></td>
              </tr>
            </table></td>
<?php
  $heading = array();
  $contents = array();

  switch ($action) {
    case 'edit':
      $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_INSERT_ORDER . '</b>');

      $contents = array('form' => tep_draw_form('orders', 'cgp_orders.php', tep_get_all_get_params(array('oID', 'action')) . 'oID=' . $oInfo->orders_id . '&action=insertconfirm'));
      $contents[] = array('text' => TEXT_INFO_INSERT_INTRO . '<br><br>');
      $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_edit.gif', IMAGE_EDIT) . ' <a href="' . tep_href_link('cgp_orders.php', tep_get_all_get_params(array('oID', 'action')) . 'oID=' . $oInfo->orders_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    case 'delete':
      $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_DELETE_ORDER . '</b>');

      $contents = array('form' => tep_draw_form('orders', 'cgp_orders.php', tep_get_all_get_params(array('oID', 'action')) . 'oID=' . $oInfo->orders_id . '&action=deleteconfirm'));
      $contents[] = array('text' => TEXT_INFO_DELETE_INTRO . '<br><br>');
      $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_delete.gif', IMAGE_DELETE) . ' <a href="' . tep_href_link('cgp_orders.php', tep_get_all_get_params(array('oID', 'action')) . 'oID=' . $oInfo->orders_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    default:
      if (isset($oInfo) && is_object($oInfo)) {
        $heading[] = array('text' => '<b>[' . $oInfo->orders_id . ']&nbsp;&nbsp;' . tep_datetime_short($oInfo->date_purchased) . '</b>');

        if ( 0 == $oInfo->order_id ) {
            $edit_button = '<a href="' . tep_href_link('cgp_orders.php', tep_get_all_get_params(array('oID', 'action')) . 'oID=' . $oInfo->orders_id . '&action=edit') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a>';
        } else {
            $edit_button = '';
        }
        $contents[] = array('align' => 'center', 'text' => $edit_button . ' <a href="' . tep_href_link('cgp_orders.php', tep_get_all_get_params(array('oID', 'action')) . 'oID=' . $oInfo->orders_id . '&action=delete') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a>');
        $contents[] = array('text' => '<br>' . TEXT_DATE_ORDER_CREATED . ' ' . tep_date_short($oInfo->date_purchased));
        $contents[] = array('text' => '<br>' . TEXT_INFO_ORDER_ID . ' ' . $oInfo->order_id);
        $contents[] = array('text' => '<br>' . TEXT_INFO_MERCHANT_REF . ' ' . $oInfo->orders_id . '|' . $oInfo->order_id);
        $contents[] = array('text' => '<br>' . TEXT_INFO_TRANSACTION_ID . ' ' . $oInfo->transaction_id);
        $contents[] = array('text' => '<br>' . TEXT_INFO_STATUS . ' ' . $oInfo->status);
        $contents[] = array('text' => '<br>' . TEXT_INFO_TEST . ' ' . $oInfo->is_test);
        $contents[] = array('text' => '<br>' . TEXT_INFO_PAYMENT_METHOD . ' '  . $oInfo->module);
      }
      break;
  }

  if ( (tep_not_null($heading)) && (tep_not_null($contents)) ) {
    echo '            <td width="25%" valign="top">' . "\n";

    $box = new box;
    echo $box->infoBox($heading, $contents);

    echo '            </td>' . "\n";
  }
?>
          </tr>
        </table></td>
      </tr>
    </table>
    <table>
    <tr><td colspan=2 class="pageHeading">Status codes:</td></tr>
    <tr><td class="smallText">0 =</td><td class="smallText">INITIATED</td></tr>
    <tr><td class="smallText">200 =</td><td class="smallText">PAID</td></tr>
    <tr><td class="smallText">300 =</td><td class="smallText">FAILED</td></tr>
    </table>
<?php
  require(DIR_WS_INCLUDES . 'template_bottom.php');
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>
