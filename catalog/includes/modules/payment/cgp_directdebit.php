<?php
/**
 * $Id: cgp_directdebit.php 30 2011-12-23 17:35:13Z h0ax $
 *
 * osCommerce, Open Source E-Commerce Solutions
 * http://www.oscommerce.com
 *
 * Copyright (c) 2002 osCommerce
 *
 * Released under the GNU General Public License
 *
 * Created by BZ (support@cardgate.com)
 * version 2.20 2011-02-14
 * 
 * Modified by Paul Saparov (support@cardgate.com)
 * version 2.31 2011-11-02
 */
require_once(DIR_FS_CATALOG . "includes/modules/payment/cgp/cgp_generic.php");

class cgp_directdebit extends cgp_generic
{
    var $code = 'cgp_directdebit';
	var $module_cgp_text = 'DIRECTDEBIT';
}
?>