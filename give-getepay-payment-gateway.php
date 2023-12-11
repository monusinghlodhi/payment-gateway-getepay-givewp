<?php
/*
Plugin Name:  Payment Gateway GetePay GiveWP
Plugin URI:   https://getepay.in/
Description:  GetePay New Payment Gateway Support for Give Donation Platform
Version:      1.0
Author:       GetePay
Author URI:   https://monusingh.com/
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/
if (! defined( 'ABSPATH' )) {
    exit;
}

 /* GetePay Functions */
 if (!function_exists('format_amount')){
    function format_amount($amt)
    {
        $remove_dot = str_replace('.', '', $amt);
        $remove_comma = str_replace(',', '', $remove_dot);
        return $remove_comma;
    }
}
if (!function_exists('getepay_signature')){
    function getepay_signature($source)
    {
        return base64_encode(hex2bin(sha1($source)));
    }
}
if (!function_exists('hex2bin')){
    function hex2bin($hexSource)
    {
        for ($i=0;$i<strlen($hexSource);$i=$i+2)
        {
            $bin .= chr(hexdec(substr($hexSource,$i,2)));
        }
        return $bin;
    }
}
 /* End of GetePay Functions */

/* Plugin Dependencies */
function check_give_plugin_dependency() {
    if ( is_admin() && current_user_can( 'activate_plugins' ) &&  !is_plugin_active( 'give/give.php' ) ) {
        add_action( 'admin_notices', 'give_plugin_notification' );

        deactivate_plugins( plugin_basename( __FILE__ ) ); 

        if ( isset( $_GET['activate'] ) ) {
            unset( $_GET['activate'] );
        }
    }
}
function give_plugin_notification(){
    ?><div class="error"><p>Sorry, but <strong>Give GetePay Payment Gateway</strong> requires the <strong><a href="/wp-admin/plugin-install.php?tab=plugin-information&plugin=give">Give - Donation Plugin</a></strong> to be installed and active.</p></div><?php
}
add_action( 'admin_init', 'check_give_plugin_dependency' );
/* End of Plugin Dependencies */

/* Disabled Plugin Activation Link */
function give_getepay_payment_gateway_activation( $links, $file ) {
    if ( 'givewp-getepay/give-getepay-payment-gateway.php' == $file and isset($links['activate']) )
        $links['activate'] = '<span>Activate</span>';

    return $links;
}
add_filter( 'plugin_action_links', 'give_getepay_payment_gateway_activation', 10, 2 );
/* End of Disabled Plugin Activation Link */

function getepay_gateway_plugin_linkss( $links ) {
	$plugin_links = array(
		'<a href="' . admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=gateways&section=getepay' ) . '">' . __( 'Configure', 'woocommerce-getepay-payment' ) . '</a>'
	);
	return array_merge( $plugin_links, $links );
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'getepay_gateway_plugin_linkss' );

/* Payment Gateway Section */
function add_getepay_payment_gateway($gateways)
{
    $gateways['getepay'] = array(
        'admin_label'    => __( 'GetePay', 'give' ),
        'checkout_label' => __( 'GetePay', 'give' ),
    );
    return $gateways;
}
add_filter( 'give_payment_gateways', 'add_getepay_payment_gateway');
/* End of Payment Gateway Section */

/* Gateway Section */
function add_getepay_gateway_section($sections)
{
    $sections['getepay'] = __( 'GetePay', 'give' );
    return $sections;
}
add_filter( 'give_get_sections_gateways', 'add_getepay_gateway_section');
/* End of Gateway Section */

/* Gateway Settings */
function add_getepay_gateway_settings($settings)
{
    $current_section = give_get_current_setting_section();
    switch ($current_section) {
        case 'getepay':
            $settings = array(
                array(
                    'name' => __('GatePay Settings', 'give'),
                    'type' => 'title',
                    'id'   => 'give_title_gateway_settings_getepay',
                ),
                array(
                    'name' => __( 'Organization Name', 'give' ),
                    'desc' => __( 'Enter your organization name details to be displayed to your donors.', 'give' ),
                    'id'   => 'getepay_merchant_name',
                    'type' => 'text',
                    ),
                array(
                    'name' => __( 'Request Url', 'give' ),
                    'desc' => __( 'Getepay Payment Url', 'give' ),
                    'id'   => 'getepay_request_url',
                    'type' => 'text',                    
                    'default'     => __( 'http://164.52.216.34:8085/getepayPortal/pg/generateInvoice', 'give' ),
                    //'desc_tip'    => true,
                    ),
                array(
                    'name' => __( 'MID', 'give' ),
                    'desc' => __( 'GetePay MID. Your MID can be found in our dashboard.', 'give' ),
                    'id'   => 'getepay_mid',
                    'type' => 'text',
                    ),

                array(
                    'name'       => __( 'Terminal Id', 'give' ),
                    'desc' => __( 'Getepay Terminal Id', 'give' ),
                    'id'   => 'getepay_terminal_id',
                    'type'        => 'text',
                    //'desc_tip'    => true,
                    ),
                array(
                    'name' => __( 'Getepay Key', 'give' ),
                    'desc' => __( 'GetePay key. Your GatePay Key can be found in our dashboard.', 'give' ),
                    'id'   => 'getepay_api_key',
                    'type' => 'text',
                    ),
                array(
                    'name' => __( 'Getepay IV', 'give' ),
                    'desc' => __( 'Getepay IV. Your Getepay IV Key can be found in our dashboard.', 'give' ),
                    'id'   => 'getepay_secret_key',
                    'type' => 'text',
                    ),
                    // array(
                    //     'name' => __('Billing Fields', 'give'),
                    //     'desc' => __('This option will enable the billing details section for GatePay which requires the donor\'s address to complete the donation. These fields are not required by GatePay to process the transaction, but you may have the need to collect the data.', 'give-gatepay'),
                    //     'id' => 'getepay_collect_billing',
                    //     'type' => 'radio_inline',
                    //     'default' => 'disabled',
                    //     'options' => array(
                    //         'enabled' => __('Enabled', 'give'),
                    //         'disabled' => __('Disabled', 'give'),
                    //     ),
                    // ),
                array(
                    'type' => 'sectionend',
                    'id'   => 'give_title_gateway_settings_getepay',
                )
            );
            break;
    }
    return $settings;
}
add_filter( 'give_get_settings_gateways', 'add_getepay_gateway_settings');
/* End of Gateway Settings */

function give_getepay_cc_form($form_id)
{
    $post_getepay_customize_option = give_get_meta($form_id, 'getepay_customize_getepay_donations', true, 'global');

    // Enable Default fields (billing info)
    $post_getepay_cc_fields = give_get_meta($form_id, 'getepay_collect_billing', true);
    $global_getepay_cc_fields = give_get_option('getepay_collect_billing');

    // Output Address fields if global option is on and user hasn't elected to customize this form's offline donation options
    if (
        (give_is_setting_enabled($post_getepay_customize_option, 'global') && give_is_setting_enabled($global_getepay_cc_fields))
        || (give_is_setting_enabled($post_getepay_customize_option, 'enabled') && give_is_setting_enabled($post_getepay_cc_fields))
    ) {
        give_default_cc_address_fields($form_id);
        return true;
    }

    return false;
}

/* getepay Billing Details Form */
function give_getepay_standard_billing_fields( $form_id ) {
    
    if ( give_is_setting_enabled( give_get_option( 'getepay_billing_details' ) ) ) {
        give_default_cc_address_fields( $form_id );

        return true;
    }

    return false;

}
add_action( 'give_getepay_cc_form', 'give_getepay_standard_billing_fields' );
/* End of getepay Billing Details Form */

/* Create Payment Data */
function give_getcreate_getepay_payment_dataepay_cc_form($insert_payment_data)
{
    $insert_payment_data['gateway'] = 'getepay';
    return $insert_payment_data;
}
add_filter( 'give_create_payment', 'give_getcreate_getepay_payment_dataepay_cc_form');
/* End of Create Payment Data */
 
/* Process GetePay Payment */
function give_process_getepay_payment($payment_data)
{
    // Validate nonce.
    give_validate_nonce( $payment_data['gateway_nonce'], 'give-gateway' );
    $payment_id = give_create_payment( $payment_data, 'getepay' );

    // Check payment.
    if (empty($payment_id)) {
        // Record the error.
        give_record_gateway_error(
            esc_html__( 'Payment Error', 'give' ),
            sprintf(
            /* translators: %s: payment data */
                esc_html__( 'Payment creation failed before sending donor to GetePay. Payment data: %s', 'give' ),
                json_encode( $payment_data )
            ),
            $payment_id
        );
        // Problems? Send back.
        give_send_back_to_checkout( '?payment-mode=' . $payment_data['post_data']['give-gateway'] );
    }

    // Redirect to GetePay.
    $result = construct_form_and_post($payment_id, $payment_data);
    exit;
}
add_action( 'give_gateway_getepay', 'give_process_getepay_payment' );
/* End of Process GetePay Payment */

/* Hidden Form Generation */
function construct_form_and_post($payment_id, $payment_data) {
    
    $post_url = give_is_test_mode() ?  'https://pay1.getepay.in:8443/getepayPortal/pg/generateInvoice' : 'https://pay1.getepay.in:8443/getepayPortal/pg/generateInvoice';
    $phone = '-';
    $remark = '';

    // Get the success url.
    $return_url = add_query_arg( array(
        'payment-confirmation' => 'getepay',
        'payment-id'           => $payment_id,
    ), get_permalink( give_get_option( 'success_page' ) ) );

    // Item name.
    $item_name = give_build_getepay_item_title($payment_data);
    
    // Setup GetePay API params.
        //Getepay API
        $url = give_get_option("getepay_request_url");
        $mid = give_get_option("getepay_mid");
        $terminalId= give_get_option("getepay_terminal_id");
        $key = give_get_option("getepay_api_key");
        $iv = give_get_option("getepay_secret_key");    
        //$ru = get_return_url( $return_url );

    $args=array(
        "mid"=>$mid,
        "amount"=>$payment_data['price'],
        "merchantTransactionId"=>$payment_id,
        "transactionDate"=>date("Y-m-d H:i:s"),
        "terminalId"=>$terminalId,
        "udf1"=>$payment_data['user_info']['first_name'] . ' ' . $payment_data['user_info']['last_name'],
        "udf2"=>$phone,
        "udf3"=>$payment_data['user_email'],
        "udf4"=>"",
        "udf5"=>"",
        "udf6"=>"",
        "udf7"=>"",
        "udf8"=>"",
        "udf9"=>"",
        "udf10"=>"",
        "ru"=>$return_url,
        "callbackUrl"=>"",
        "currency"=>"INR",
        "paymentMode"=>"ALL",
        "bankId"=>"",
        "txnType"=>"single",
        "productType"=>"IPG",
        "txnNote"=>"Getepay transaction",
        "vpa"=>$terminalId,
    );
    $json_requset = json_encode($args);

    $format_amt = format_amount($args['amount']);
    $txn_prod_desc = "Payment Order ID: " . $args['merchantTransactionId'];  

    $key = base64_decode($key);
    $iv = base64_decode($iv);

    // Encryption Code //
    $ciphertext_raw = openssl_encrypt($json_requset, "AES-256-CBC", $key, $options = OPENSSL_RAW_DATA, $iv);
    $ciphertext = bin2hex($ciphertext_raw);
    $newCipher = strtoupper($ciphertext);
    //print_r($newCipher);exit;
    $args=array(
        "mid"=>$mid,
        "terminalId"=>$terminalId,
        "req"=>$newCipher
    );

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLINFO_HEADER_OUT, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        'Content-Type:application/json',
    ));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($args));
    $result = curl_exec($curl);
    curl_close ($curl);
    $jsonDecode = json_decode($result);
    $jsonResult = $jsonDecode->response;
    $ciphertext_raw = hex2bin($jsonResult);
    $original_plaintext = openssl_decrypt($ciphertext_raw,  "AES-256-CBC", $key, $options=OPENSSL_RAW_DATA, $iv);
    $json = json_decode($original_plaintext);

    $pgUrl = $json->paymentUrl;
    wp_redirect( $pgUrl );
    //Getepay API End
    return 'success ...';
}
/* End of Hidden Form Generation */

/* Return URL Processing */

function give_getepay_success_page_content( $content ) {

    // $merchantcode = $_REQUEST["MerchantCode"];
    $paymentid = $_REQUEST["payment-id"];
    $ecurrency = 'INR';
    $remark = $_REQUEST["message"];
    //$transid = $_REQUEST["merchantTransactionId"];
    //$authcode = $_REQUEST["signature"];
    $estatus = $_REQUEST["status"];
    $errdesc = $_REQUEST["message"];
    //$signature = $_REQUEST["signature"];

    if (!isset( $_GET['payment-id'] ) && ! give_get_purchase_session() ) {
        return $content;
    }
    $payment_id = isset( $_GET['payment-id'] ) ? absint( $_GET['payment-id'] ) : false;
    if ( ! $payment_id ) {
        $session    = give_get_purchase_session();
        $payment_id = give_get_purchase_id_by_key( $session['purchase_key'] );
    }
    $payment = get_post( $payment_id );

    if ( $payment && 'pending' === $payment->post_status ) {
        // Payment is still pending so show processing indicator to fix the race condition.
        ob_start();
        give_get_template_part( 'payment', 'processing' );
        $content = ob_get_clean();
    }

    $post = $_POST;
    $response = $post["response"];

    $key = base64_decode(give_get_option("getepay_api_key"));
    $iv = base64_decode(give_get_option("getepay_secret_key"));

    $ciphertext_raw = hex2bin($response);
    $original_plaintext = openssl_decrypt($ciphertext_raw,  "AES-256-CBC", $key, $options=OPENSSL_RAW_DATA, $iv);

    $json = json_decode(json_decode($original_plaintext,true),true);

    // echo $json["paymentStatus"];
    // die;
    $order_id = $json["merchantOrderNo"];
    //$order = new WC_Order( $order_id );
    if($json["paymentStatus"] == "SUCCESS"){
    //if ($estatus === "success") {
        //TODO: COMPARE Return Signature with Generated Response Signature   

        // Link `Transaction ID` to the donation.
        give_set_payment_transaction_id( $payment_id, $order_id );
        give_update_payment_status( $payment_id, 'publish' );
        // Send donor to `Donation Confirmation` page.
        //give_send_to_success_page();

    }
    else {
        give_record_gateway_error( __( 'GetePay Error', 'give' ), sprintf(__( $errdesc, 'give' ), json_encode( $_REQUEST ) ), $payment_id );
        give_set_payment_transaction_id( $payment_id, $order_id );
        give_update_payment_status( $payment_id, 'failed' );
        give_insert_payment_note( $payment_id, __( $errdesc, 'give' ) );
        //wp_redirect( give_get_failed_transaction_uri() );
    }
    
    return $content;
}
add_filter('give_payment_confirm_getepay', 'give_getepay_success_page_content');
/* End of Return URL Processing */

/* Build Item Title */
function give_build_getepay_item_title($payment_data)
{
    $form_id   = intval( $payment_data['post_data']['give-form-id'] );
    $item_name = $payment_data['post_data']['give-form-title'];

    // Verify has variable prices.
    if (give_has_variable_prices( $form_id ) && isset( $payment_data['post_data']['give-price-id'] )) {
        $item_price_level_text = give_get_price_option_name( $form_id, $payment_data['post_data']['give-price-id'] );
        $price_level_amount    = give_get_price_option_amount( $form_id, $payment_data['post_data']['give-price-id'] );

        // Donation given doesn't match selected level (must be a custom amount).
        if ($price_level_amount != give_sanitize_amount( $payment_data['price'] )) {
            $custom_amount_text = give_get_meta( $form_id, '_give_custom_amount_text', true );
            // user custom amount text if any, fallback to default if not.
            $item_name .= ' - ' . give_check_variable( $custom_amount_text, 'empty', esc_html__( 'Custom Amount', 'give' ) );
        } //Is there any donation level text?
        elseif (! empty( $item_price_level_text )) {
            $item_name .= ' - ' . $item_price_level_text;
        }
    } //Single donation: Custom Amount.
    elseif (give_get_form_price( $form_id ) !== give_sanitize_amount( $payment_data['price'] )) {
        $custom_amount_text = give_get_meta( $form_id, '_give_custom_amount_text', true );
        // user custom amount text if any, fallback to default if not.
        $item_name .= ' - ' . give_check_variable( $custom_amount_text, 'empty', esc_html__( 'Custom Amount', 'give' ) );
    }

    return $item_name;
}
/* End of Build Item Title */

/* Add Phone Number Field */
function give_phone_number_form_fields( $form_id ) {
	?>
   
	<?php
} 
add_action( 'give_donation_form_after_email', 'give_phone_number_form_fields', 10, 1 );
/* End of Add Phone Number Field */

/* Make Phone Number Field Required */
// function give_required_phone_number($required_fields)
// {
//     $required_fields['give_phone'] =  array(
// 		'give_phone' => array(
// 			'error_id'      => 'invalid_phone',
// 			'error_message' => __( 'Please enter phone number.', 'give' ),
// 		));
//     return $required_fields;
// }
// add_filter( 'give_donation_form_required_fields', 'give_required_phone_number');

/* End of getepay Requery Function */
