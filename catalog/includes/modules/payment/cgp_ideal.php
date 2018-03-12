<?php

/**
 * $Id: cgp_ideal.php 30 2011-12-23 17:35:13Z h0ax $
 *
 * osCommerce, Open Source E-Commerce Solutions
 * http://www.oscommerce.com
 *
 * Copyright (c) 2002 osCommerce
 *
 * Released under the GNU General Public License
 *
 * Modified by Ramon de la Fuente (ramon@future500.nl) for new osCommerce checkout (>Nov 2002) procedure.
 * Tested with CGP eCommerce version Jan 2003 and later. For more
 * infomation about Card Gate Plus: http://www.cardgateplus.com
 *
 * Modified by Mark Stunnenberg (mark@databoss.nl) for new osCommerce checkout procedure for CGP.
 * version 2.04 2009-07-15
 *
 * Modified by BZ (support@cardgate.com) for new test method and direct link to iDEAL
 * version 2.10 2010-06-21
 *
 * Modified by BZ (support@cardgate.com) to fix iDEAL bank selection popup that showed at all payment methods
 * version 2.10 2010-08-23
 * 
 * Modified by Paul Saparov (support@cardgate.com)
 * version 2.31 2011-11-02
 */
require_once(DIR_FS_CATALOG . "includes/modules/payment/cgp/cgp_generic.php");

class cgp_ideal extends cgp_generic {

    var $code = 'cgp_ideal';
    var $module_cgp_text = 'IDEAL';

    function javascript_validation() {
        $errorValidation.= "var ideal_issuer_id_value = document.checkout_payment.ideal_issuer_id.value;\n";
        $errorValidation.= "for (var i=0; i < document.checkout_payment.payment.length; i++) {\n";
        $errorValidation.= "    if (document.checkout_payment.payment[i].checked) {\n";
        $errorValidation.= "        var option_value = document.checkout_payment.payment[i].value;\n";
        $errorValidation.= "	}\n";
        $errorValidation.= "}\n";
        $errorValidation.= "if (ideal_issuer_id_value == 0 && option_value == \"cgp_ideal\" ) {\n";
        $errorValidation.= "    error_message = error_message + \"* " . MODULE_PAYMENT_CGP_IDEAL_WARNING_BANK . "\";\n";
        $errorValidation.= "    error = 1;\n";
        $errorValidation.= "}\n";

        return $errorValidation;
    }

    function selection() {
        $js = "for (var i=0; i < document.checkout_payment.payment.length; i++) {\n";
        $js .= "    if (document.checkout_payment.payment[i].value == 'cgp_ideal') {\n";
        $js .= "        var option_value = i;\n";
        $js .= "	}\n";
        $js .= "}\n";
        $js .= "var ideal_issuer_id_value = document.checkout_payment.ideal_issuer_id.value;";
        $js .= "if (ideal_issuer_id_value != 0 ) {";
        $js .= "selectRowEffect(this,option_value);";
        $js .= "}";
        $onChange = ' onChange="' . $js . '"';

        $selection = array();
        $selection['id'] = $this->code;
        $selection['module'] = $this->title;
        $selection['fields'] = array( array(
                'field' => tep_draw_pull_down_menu( 'ideal_issuer_id', $this->get_banks(), '', $onChange )
        ) );
        return $selection;
    }

}

?>