<?php
/**
 * $Id: cgp_mistercash.php 30 2011-12-23 17:35:13Z h0ax $
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
 */
require_once(DIR_FS_CATALOG . "includes/modules/payment/cgp/cgp_generic.php");

class cgp_mistercash extends cgp_generic
{
    var $code = 'cgp_mistercash';
	var $module_cgp_text = 'MISTERCASH';
}
?>