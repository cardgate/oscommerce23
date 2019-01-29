<?php

error_reporting( E_ALL );
ini_set( "display_errors", 1 );

function zipfiles(){
    // Get real path for our folder
$rootPath = '/var/www/oscommerce23/htdocs/_plugin/catalog';

// Initialize archive object
$zip = new ZipArchive();
$zip->open('catalog.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE);

// Create recursive directory iterator
/** @var SplFileInfo[] $files */
$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($rootPath),
    RecursiveIteratorIterator::SELF_FIRST
);

foreach ($files as $name => $file)
{
    // Skip directories (they would be added automatically)
    if (!$file->isDir())
    {
        // Get real and relative path for current file
        $filePath = $file->getRealPath();
        $relativePath = substr($filePath, strlen($rootPath) + 1);

        // Add current file to archive
        $zip->addFile($filePath, $relativePath);
    }
}

// Zip archive will be created only after closing object
$zip->close();
}

function recurse_copy( $src, $dst, $is_dir ) {
    if ( $is_dir ) {
        // copy directory
        if ( is_dir( $src ) ) {
            if ( $src != '.svn' ) {
                $dir = opendir( $src );
                @mkdir( $dst );
                while ( false !== ( $file = readdir( $dir )) ) {
                    if ( ( $file != '.' ) && ( $file != '..' ) ) {
                        if ( is_dir( $src . '/' . $file ) ) {
                            recurse_copy( $src . '/' . $file, $dst . '/' . $file, true );
                        } else {
                            if ( strpos( $file, '.DS_Store' ) === false ) {
                                copy( $src . '/' . $file, $dst . '/' . $file );
                            }
                        }
                    }
                }
                closedir( $dir );
            }
        } else {
            echo 'dir ' . $src . ' is not found!';
        }
    } else {
        if ( strpos( $src, '.DS_Store' ) === false ) {
            // copy file
            copy( $src, $dst );
        }
    }
}
  
// make file and directory array
function data_element( $src, $dst, $is_dir = false ) {
    $data = array();
    $data['src'] = $src;
    $data['dst'] = $dst;
    $data['isdir'] = $is_dir;
    return $data;
}

// make data

$data = array();


$src = '../admin/cgp_orders.php';
$dst = 'catalog/admin/cgp_orders.php';
$is_dir = false;
array_push( $data, data_element( $src, $dst, $is_dir ) );

$src = '../ext/modules/payment/cgp/';
$dst = 'catalog/ext/modules/payment/cgp';
$is_dir = true;
array_push( $data, data_element( $src, $dst, $is_dir ) );

$src = '../includes/languages/dutch/modules/payment/cgp_afterpay.php';
$dst = 'catalog/includes/languages/dutch/modules/payment/cgp_afterpay.php';
$is_dir = false;
array_push( $data, data_element( $src, $dst, $is_dir ) );

$src = '../includes/languages/dutch/modules/payment/cgp_americanexpress.php';
$dst = 'catalog/includes/languages/dutch/modules/payment/cgp_americanexpress.php';
$is_dir = false;
array_push( $data, data_element( $src, $dst, $is_dir ) );

$src = '../includes/languages/dutch/modules/payment/cgp_banktransfer.php';
$dst = 'catalog/includes/languages/dutch/modules/payment/cgp_banktransfer.php';
$is_dir = false;
array_push( $data, data_element( $src, $dst, $is_dir ) );

$src = '../includes/languages/dutch/modules/payment/cgp_billink.php';
$dst = 'catalog/includes/languages/dutch/modules/payment/cgp_billink.php';
$is_dir = false;
array_push( $data, data_element( $src, $dst, $is_dir ) );

$src = '../includes/languages/dutch/modules/payment/cgp_bitcoin.php';
$dst = 'catalog/includes/languages/dutch/modules/payment/cgp_bitcoin.php';
$is_dir = false;
array_push( $data, data_element( $src, $dst, $is_dir ) );

$src = '../includes/languages/dutch/modules/payment/cgp_directdebit.php';
$dst = 'catalog/includes/languages/dutch/modules/payment/cgp_directdebit.php';
$is_dir = false;
array_push( $data, data_element( $src, $dst, $is_dir ) );

$src = '../includes/languages/dutch/modules/payment/cgp_directebanking.php';
$dst = 'catalog/includes/languages/dutch/modules/payment/cgp_directebanking.php';
$is_dir = false;
array_push( $data, data_element( $src, $dst, $is_dir ) );

$src = '../includes/languages/dutch/modules/payment/cgp_giftcard.php';
$dst = 'catalog/includes/languages/dutch/modules/payment/cgp_giftcard.php';
$is_dir = false;
array_push( $data, data_element( $src, $dst, $is_dir ) );

$src = '../includes/languages/dutch/modules/payment/cgp_giropay.php';
$dst = 'catalog/includes/languages/dutch/modules/payment/cgp_giropay.php';
$is_dir = false;
array_push( $data, data_element( $src, $dst, $is_dir ) );

$src = '../includes/languages/dutch/modules/payment/cgp_idealqr.php';
$dst = 'catalog/includes/languages/dutch/modules/payment/cgp_idealqr.php';
$is_dir = false;
array_push( $data, data_element( $src, $dst, $is_dir ) );

$src = '../includes/languages/dutch/modules/payment/cgp_ideal.php';
$dst = 'catalog/includes/languages/dutch/modules/payment/cgp_ideal.php';
$is_dir = false;
array_push( $data, data_element( $src, $dst, $is_dir ) );

$src = '../includes/languages/dutch/modules/payment/cgp_klarna.php';
$dst = 'catalog/includes/languages/dutch/modules/payment/cgp_klarna.php';
$is_dir = false;
array_push( $data, data_element( $src, $dst, $is_dir ) );

$src = '../includes/languages/dutch/modules/payment/cgp_maestro.php';
$dst = 'catalog/includes/languages/dutch/modules/payment/cgp_maestro.php';
$is_dir = false;
array_push( $data, data_element( $src, $dst, $is_dir ) );

$src = '../includes/languages/dutch/modules/payment/cgp_mastercard.php';
$dst = 'catalog/includes/languages/dutch/modules/payment/cgp_mastercard.php';
$is_dir = false;
array_push( $data, data_element( $src, $dst, $is_dir ) );

$src = '../includes/languages/dutch/modules/payment/cgp_mistercash.php';
$dst = 'catalog/includes/languages/dutch/modules/payment/cgp_mistercash.php';
$is_dir = false;
array_push( $data, data_element( $src, $dst, $is_dir ) );

$src = '../includes/languages/dutch/modules/payment/cgp_paypal.php';
$dst = 'catalog/includes/languages/dutch/modules/payment/cgp_paypal.php';
$is_dir = false;
array_push( $data, data_element( $src, $dst, $is_dir ) );

$src = '../includes/languages/dutch/modules/payment/cgp_paysafecard.php';
$dst = 'catalog/includes/languages/dutch/modules/payment/cgp_paysafecard.php';
$is_dir = false;
array_push( $data, data_element( $src, $dst, $is_dir ) );

$src = '../includes/languages/dutch/modules/payment/cgp_paysafecash.php';
$dst = 'catalog/includes/languages/dutch/modules/payment/cgp_paysafecash.php';
$is_dir = false;
array_push( $data, data_element( $src, $dst, $is_dir ) );

$src = '../includes/languages/dutch/modules/payment/cgp_przelewy24.php';
$dst = 'catalog/includes/languages/dutch/modules/payment/cgp_przelewy24.php';
$is_dir = false;
array_push( $data, data_element( $src, $dst, $is_dir ) );

$src = '../includes/languages/dutch/modules/payment/cgp_sofortueberweisung.php';
$dst = 'catalog/includes/languages/dutch/modules/payment/cgp_sofortueberweisung.php';
$is_dir = false;
array_push( $data, data_element( $src, $dst, $is_dir ) );

$src = '../includes/languages/dutch/modules/payment/cgp_visa.php';
$dst = 'catalog/includes/languages/dutch/modules/payment/cgp_visa.php';
$is_dir = false;
array_push( $data, data_element( $src, $dst, $is_dir ) );

$src = '../includes/languages/dutch/modules/payment/cgp_vpay.php';
$dst = 'catalog/includes/languages/dutch/modules/payment/cgp_vpay.php';
$is_dir = false;
array_push( $data, data_element( $src, $dst, $is_dir ) );

$src = '../includes/languages/dutch/modules/payment/cgp_generic.php';
$dst = 'catalog/includes/languages/dutch/modules/payment/cgp_generic.php';
$is_dir = false;
array_push( $data, data_element( $src, $dst, $is_dir ) );


$src = '../includes/languages/english/modules/payment/cgp_afterpay.php';
$dst = 'catalog/includes/languages/english/modules/payment/cgp_afterpay.php';
$is_dir = false;
array_push( $data, data_element( $src, $dst, $is_dir ) );

$src = '../includes/languages/english/modules/payment/cgp_americanexpress.php';
$dst = 'catalog/includes/languages/english/modules/payment/cgp_americanexpress.php';
$is_dir = false;
array_push( $data, data_element( $src, $dst, $is_dir ) );

$src = '../includes/languages/english/modules/payment/cgp_banktransfer.php';
$dst = 'catalog/includes/languages/english/modules/payment/cgp_banktransfer.php';
$is_dir = false;
array_push( $data, data_element( $src, $dst, $is_dir ) );

$src = '../includes/languages/english/modules/payment/cgp_billink.php';
$dst = 'catalog/includes/languages/english/modules/payment/cgp_billink.php';
$is_dir = false;
array_push( $data, data_element( $src, $dst, $is_dir ) );

$src = '../includes/languages/english/modules/payment/cgp_bitcoin.php';
$dst = 'catalog/includes/languages/english/modules/payment/cgp_bitcoin.php';
$is_dir = false;
array_push( $data, data_element( $src, $dst, $is_dir ) );

$src = '../includes/languages/english/modules/payment/cgp_directdebit.php';
$dst = 'catalog/includes/languages/english/modules/payment/cgp_directdebit.php';
$is_dir = false;
array_push( $data, data_element( $src, $dst, $is_dir ) );

$src = '../includes/languages/english/modules/payment/cgp_directebanking.php';
$dst = 'catalog/includes/languages/english/modules/payment/cgp_directebanking.php';
$is_dir = false;
array_push( $data, data_element( $src, $dst, $is_dir ) );

$src = '../includes/languages/english/modules/payment/cgp_giftcard.php';
$dst = 'catalog/includes/languages/english/modules/payment/cgp_giftcard.php';
$is_dir = false;
array_push( $data, data_element( $src, $dst, $is_dir ) );

$src = '../includes/languages/english/modules/payment/cgp_giropay.php';
$dst = 'catalog/includes/languages/english/modules/payment/cgp_giropay.php';
$is_dir = false;
array_push( $data, data_element( $src, $dst, $is_dir ) );

$src = '../includes/languages/english/modules/payment/cgp_idealqr.php';
$dst = 'catalog/includes/languages/english/modules/payment/cgp_idealqr.php';
$is_dir = false;
array_push( $data, data_element( $src, $dst, $is_dir ) );

$src = '../includes/languages/english/modules/payment/cgp_ideal.php';
$dst = 'catalog/includes/languages/english/modules/payment/cgp_ideal.php';
$is_dir = false;
array_push( $data, data_element( $src, $dst, $is_dir ) );

$src = '../includes/languages/english/modules/payment/cgp_klarna.php';
$dst = 'catalog/includes/languages/english/modules/payment/cgp_klarna.php';
$is_dir = false;
array_push( $data, data_element( $src, $dst, $is_dir ) );

$src = '../includes/languages/english/modules/payment/cgp_maestro.php';
$dst = 'catalog/includes/languages/english/modules/payment/cgp_maestro.php';
$is_dir = false;
array_push( $data, data_element( $src, $dst, $is_dir ) );

$src = '../includes/languages/english/modules/payment/cgp_mastercard.php';
$dst = 'catalog/includes/languages/english/modules/payment/cgp_mastercard.php';
$is_dir = false;
array_push( $data, data_element( $src, $dst, $is_dir ) );

$src = '../includes/languages/english/modules/payment/cgp_mistercash.php';
$dst = 'catalog/includes/languages/english/modules/payment/cgp_mistercash.php';
$is_dir = false;
array_push( $data, data_element( $src, $dst, $is_dir ) );

$src = '../includes/languages/english/modules/payment/cgp_paypal.php';
$dst = 'catalog/includes/languages/english/modules/payment/cgp_paypal.php';
$is_dir = false;
array_push( $data, data_element( $src, $dst, $is_dir ) );

$src = '../includes/languages/english/modules/payment/cgp_paysafecard.php';
$dst = 'catalog/includes/languages/english/modules/payment/cgp_paysafecard.php';
$is_dir = false;
array_push( $data, data_element( $src, $dst, $is_dir ) );

$src = '../includes/languages/english/modules/payment/cgp_paysafecash.php';
$dst = 'catalog/includes/languages/english/modules/payment/cgp_paysafecash.php';
$is_dir = false;
array_push( $data, data_element( $src, $dst, $is_dir ) );

$src = '../includes/languages/english/modules/payment/cgp_przelewy24.php';
$dst = 'catalog/includes/languages/english/modules/payment/cgp_przelewy24.php';
$is_dir = false;
array_push( $data, data_element( $src, $dst, $is_dir ) );

$src = '../includes/languages/english/modules/payment/cgp_sofortueberweisung.php';
$dst = 'catalog/includes/languages/english/modules/payment/cgp_sofortueberweisung.php';
$is_dir = false;
array_push( $data, data_element( $src, $dst, $is_dir ) );

$src = '../includes/languages/english/modules/payment/cgp_visa.php';
$dst = 'catalog/includes/languages/english/modules/payment/cgp_visa.php';
$is_dir = false;
array_push( $data, data_element( $src, $dst, $is_dir ) );

$src = '../includes/languages/english/modules/payment/cgp_vpay.php';
$dst = 'catalog/includes/languages/english/modules/payment/cgp_vpay.php';
$is_dir = false;
array_push( $data, data_element( $src, $dst, $is_dir ) );

$src = '../includes/languages/english/modules/payment/cgp_generic.php';
$dst = 'catalog/includes/languages/english/modules/payment/cgp_generic.php';
$is_dir = false;
array_push( $data, data_element( $src, $dst, $is_dir ) );



$src = '../includes/modules/payment/cgp_afterpay.php';
$dst = 'catalog/includes/modules/payment/cgp_afterpay.php';
$is_dir = false;
array_push( $data, data_element( $src, $dst, $is_dir ) );

$src = '../includes/modules/payment/cgp_americanexpress.php';
$dst = 'catalog/includes/modules/payment/cgp_americanexpress.php';
$is_dir = false;
array_push( $data, data_element( $src, $dst, $is_dir ) );

$src = '../includes/modules/payment/cgp_banktransfer.php';
$dst = 'catalog/includes/modules/payment/cgp_banktransfer.php';
$is_dir = false;
array_push( $data, data_element( $src, $dst, $is_dir ) );

$src = '../includes/modules/payment/cgp_billink.php';
$dst = 'catalog/includes/modules/payment/cgp_billink.php';
$is_dir = false;
array_push( $data, data_element( $src, $dst, $is_dir ) );

$src = '../includes/modules/payment/cgp_bitcoin.php';
$dst = 'catalog/includes/modules/payment/cgp_bitcoin.php';
$is_dir = false;
array_push( $data, data_element( $src, $dst, $is_dir ) );

$src = '../includes/modules/payment/cgp_directdebit.php';
$dst = 'catalog/includes/modules/payment/cgp_directdebit.php';
$is_dir = false;
array_push( $data, data_element( $src, $dst, $is_dir ) );

$src = '../includes/modules/payment/cgp_directebanking.php';
$dst = 'catalog/includes/modules/payment/cgp_directebanking.php';
$is_dir = false;
array_push( $data, data_element( $src, $dst, $is_dir ) );

$src = '../includes/modules/payment/cgp_giftcard.php';
$dst = 'catalog/includes/modules/payment/cgp_giftcard.php';
$is_dir = false;
array_push( $data, data_element( $src, $dst, $is_dir ) );

$src = '../includes/modules/payment/cgp_giropay.php';
$dst = 'catalog/includes/modules/payment/cgp_giropay.php';
$is_dir = false;
array_push( $data, data_element( $src, $dst, $is_dir ) );

$src = '../includes/modules/payment/cgp_idealqr.php';
$dst = 'catalog/includes/modules/payment/cgp_idealqr.php';
$is_dir = false;
array_push( $data, data_element( $src, $dst, $is_dir ) );

$src = '../includes/modules/payment/cgp_ideal.php';
$dst = 'catalog/includes/modules/payment/cgp_ideal.php';
$is_dir = false;
array_push( $data, data_element( $src, $dst, $is_dir ) );

$src = '../includes/modules/payment/cgp_klarna.php';
$dst = 'catalog/includes/modules/payment/cgp_klarna.php';
$is_dir = false;
array_push( $data, data_element( $src, $dst, $is_dir ) );

$src = '../includes/modules/payment/cgp_maestro.php';
$dst = 'catalog/includes/modules/payment/cgp_maestro.php';
$is_dir = false;
array_push( $data, data_element( $src, $dst, $is_dir ) );

$src = '../includes/modules/payment/cgp_mastercard.php';
$dst = 'catalog/includes/modules/payment/cgp_mastercard.php';
$is_dir = false;
array_push( $data, data_element( $src, $dst, $is_dir ) );

$src = '../includes/modules/payment/cgp_mistercash.php';
$dst = 'catalog/includes/modules/payment/cgp_mistercash.php';
$is_dir = false;
array_push( $data, data_element( $src, $dst, $is_dir ) );

$src = '../includes/modules/payment/cgp_paypal.php';
$dst = 'catalog/includes/modules/payment/cgp_paypal.php';
$is_dir = false;
array_push( $data, data_element( $src, $dst, $is_dir ) );

$src = '../includes/modules/payment/cgp_paysafecard.php';
$dst = 'catalog/includes/modules/payment/cgp_paysafecard.php';
$is_dir = false;
array_push( $data, data_element( $src, $dst, $is_dir ) );

$src = '../includes/modules/payment/cgp_paysafecash.php';
$dst = 'catalog/includes/modules/payment/cgp_paysafecash.php';
$is_dir = false;
array_push( $data, data_element( $src, $dst, $is_dir ) );

$src = '../includes/modules/payment/cgp_przelewy24.php';
$dst = 'catalog/includes/modules/payment/cgp_przelewy24.php';
$is_dir = false;
array_push( $data, data_element( $src, $dst, $is_dir ) );

$src = '../includes/modules/payment/cgp_sofortueberweisung.php';
$dst = 'catalog/includes/modules/payment/cgp_sofortueberweisung.php';
$is_dir = false;
array_push( $data, data_element( $src, $dst, $is_dir ) );

$src = '../includes/modules/payment/cgp_visa.php';
$dst = 'catalog/includes/modules/payment/cgp_visa.php';
$is_dir = false;
array_push( $data, data_element( $src, $dst, $is_dir ) );

$src = '../includes/modules/payment/cgp_vpay.php';
$dst = 'catalog/includes/modules/payment/cgp_vpay.php';
$is_dir = false;
array_push( $data, data_element( $src, $dst, $is_dir ) );

$src = '../includes/modules/payment/cgp_afterpay.php';
$dst = 'catalog/includes/modules/payment/cgp_afterpay.php';
$is_dir = false;
array_push( $data, data_element( $src, $dst, $is_dir ) );

$src = '../includes/modules/payment/cgp_americanexpress.php';
$dst = 'catalog/includes/modules/payment/cgp_americanexpress.php';
$is_dir = false;
array_push( $data, data_element( $src, $dst, $is_dir ) );

$src = '../includes/modules/payment/cgp_banktransfer.php';
$dst = 'catalog/includes/modules/payment/cgp_banktransfer.php';
$is_dir = false;
array_push( $data, data_element( $src, $dst, $is_dir ) );

$src = '../includes/modules/payment/cgp_billink.php';
$dst = 'catalog/includes/modules/payment/cgp_billink.php';
$is_dir = false;
array_push( $data, data_element( $src, $dst, $is_dir ) );

$src = '../includes/modules/payment/cgp_bitcoin.php';
$dst = 'catalog/includes/modules/payment/cgp_bitcoin.php';
$is_dir = false;
array_push( $data, data_element( $src, $dst, $is_dir ) );

$src = '../includes/modules/payment/cgp_directdebit.php';
$dst = 'catalog/includes/modules/payment/cgp_directdebit.php';
$is_dir = false;
array_push( $data, data_element( $src, $dst, $is_dir ) );

$src = '../includes/modules/payment/cgp_directebanking.php';
$dst = 'catalog/includes/modules/payment/cgp_directebanking.php';
$is_dir = false;
array_push( $data, data_element( $src, $dst, $is_dir ) );

$src = '../includes/modules/payment/cgp_giftcard.php';
$dst = 'catalog/includes/modules/payment/cgp_giftcard.php';
$is_dir = false;
array_push( $data, data_element( $src, $dst, $is_dir ) );

$src = '../includes/modules/payment/cgp_giropay.php';
$dst = 'catalog/includes/modules/payment/cgp_giropay.php';
$is_dir = false;
array_push( $data, data_element( $src, $dst, $is_dir ) );

$src = '../includes/modules/payment/cgp_idealqr.php';
$dst = 'catalog/includes/modules/payment/cgp_idealqr.php';
$is_dir = false;
array_push( $data, data_element( $src, $dst, $is_dir ) );

$src = '../includes/modules/payment/cgp_ideal.php';
$dst = 'catalog/includes/modules/payment/cgp_ideal.php';
$is_dir = false;
array_push( $data, data_element( $src, $dst, $is_dir ) );

$src = '../includes/modules/payment/cgp_klarna.php';
$dst = 'catalog/includes/modules/payment/cgp_klarna.php';
$is_dir = false;
array_push( $data, data_element( $src, $dst, $is_dir ) );

$src = '../includes/modules/payment/cgp_maestro.php';
$dst = 'catalog/includes/modules/payment/cgp_maestro.php';
$is_dir = false;
array_push( $data, data_element( $src, $dst, $is_dir ) );

$src = '../includes/modules/payment/cgp_mastercard.php';
$dst = 'catalog/includes/modules/payment/cgp_mastercard.php';
$is_dir = false;
array_push( $data, data_element( $src, $dst, $is_dir ) );

$src = '../includes/modules/payment/cgp_mistercash.php';
$dst = 'catalog/includes/modules/payment/cgp_mistercash.php';
$is_dir = false;
array_push( $data, data_element( $src, $dst, $is_dir ) );

$src = '../includes/modules/payment/cgp_paypal.php';
$dst = 'catalog/includes/modules/payment/cgp_paypal.php';
$is_dir = false;
array_push( $data, data_element( $src, $dst, $is_dir ) );

$src = '../includes/modules/payment/cgp_paysafecard.php';
$dst = 'catalog/includes/modules/payment/cgp_paysafecard.php';
$is_dir = false;
array_push( $data, data_element( $src, $dst, $is_dir ) );

$src = '../includes/modules/payment/cgp_paysafecash.php';
$dst = 'catalog/includes/modules/payment/cgp_paysafecash.php';
$is_dir = false;
array_push( $data, data_element( $src, $dst, $is_dir ) );

$src = '../includes/modules/payment/cgp_przelewy24.php';
$dst = 'catalog/includes/modules/payment/cgp_przelewy24.php';
$is_dir = false;
array_push( $data, data_element( $src, $dst, $is_dir ) );

$src = '../includes/modules/payment/cgp_sofortueberweisung.php';
$dst = 'catalog/includes/modules/payment/cgp_sofortueberweisung.php';
$is_dir = false;
array_push( $data, data_element( $src, $dst, $is_dir ) );

$src = '../includes/modules/payment/cgp_visa.php';
$dst = 'catalog/includes/modules/payment/cgp_visa.php';
$is_dir = false;
array_push( $data, data_element( $src, $dst, $is_dir ) );

$src = '../includes/modules/payment/cgp_vpay.php';
$dst = 'catalog/includes/modules/payment/cgp_vpay.php';
$is_dir = false;
array_push( $data, data_element( $src, $dst, $is_dir ) );

$src = '../includes/modules/payment/cgp/cgp_generic.php';
$dst = 'catalog/includes/modules/payment/cgp/cgp_generic.php';
$is_dir = false;
array_push( $data, data_element( $src, $dst, $is_dir ) );
 
// copy files

foreach ( $data as $k => $v ) {
        recurse_copy( $v['src'], $v['dst'], $v['isdir'] );
}

// make the zip
echo 'files copied<br>';
zipfiles();
echo 'zipfile made<br>';
echo 'done!';
?>