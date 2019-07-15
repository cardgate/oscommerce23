<?php
/**
 * $Id: cgp_visa.php 30 2011-12-23 17:35:13Z h0ax $
 * 
 * osCommerce, Open Source E-Commerce Solutions
 * http://www.oscommerce.com
 *
 * Copyright (c) 2002 osCommerce
 *
 * Released under the GNU General Public License
 * 
 * Created by Paul Saparov (support@cardgate.com)
 * version 2.33 2011-12-21
 */
require_once(DIR_FS_CATALOG . "includes/modules/payment/cgp/cgp_generic.php");

class cgp_visa extends cgp_generic
{
    var $code = 'cgp_visa';
    var $module_cgp_text = 'VISA';
}
?>