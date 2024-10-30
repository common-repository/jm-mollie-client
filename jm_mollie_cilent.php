<?php
/*
 Plugin Name: Mollie.nl Client
 Description: Mollie.nl Client plugin to perform payements through Mollie.nl
 Text Domain:jm_mollie_client
 Domain Path: /languages/  
 Author: Jan Maat
 Version: 2.1
 */

/*  Copyright 2011  Jan Maat  (email : jenj.maat@gmail.com)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
if (!class_exists('WP_List_Table')) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
/*
 * Install 
 */
register_activation_hook(__FILE__, 'jm_mollie_client_install');

function jm_mollie_client_install() {
    if (version_compare(get_bloginfo('version'), '3.8.1', '<')) {
        die("This Plugin requires WordPress version 3.8.1 or higher");
    }
}

/*
 * 
 * Plugin uninstall
 */

function jm_mollie_client_init() {
// Localization    
    load_plugin_textdomain('jm_mollie_client', false, dirname(plugin_basename(__FILE__)) . '/languages');
// Start session for use in Mollie answer
    if (!session_id())
        session_start();
    $url = plugin_dir_url(__FILE__);
    wp_enqueue_script('validator', $url . 'js/validator.min.js', array('jquery'), '0.4.5', true);
}

// Add actions
add_action('init', 'jm_mollie_client_init');

function jm_mollie_EndSession() {
    session_destroy();
}

add_action('wp_logout', 'jm_mollie_EndSession');
add_action('wp_login', 'jm_mollie_EndSession');


add_shortcode('mollie', 'jm_mollie_client_shortcode');

/**
 * 
 * @param array $attr Attributes of the shortcode.
 * @return string HTML content to display gallery.
 */
function jm_mollie_client_shortcode($atts) {
    global $post;
    require_once dirname(__FILE__) . "/Mollie/API/Autoloader.php";
    $options = get_option('jm_mollie_client_option_name');

    try {
        $mollie = new Mollie_API_Client;
        $mollie->setApiKey($options['apikey']);

        if ($_SERVER["REQUEST_METHOD"] != "POST") {
            If ($_GET["return"] != 1) {
                $issuers = $mollie->issuers->all();

                $mollie_form = '<div class="jm_mollie_client">';
                $mollie_form .= '<form id="attributeForm" class="form-horizontal" role="form" method="post"
                            data-toggle="validator">';

                If ($options['idealonly']) {
                    $mollie_form .= '<h2>IDEAL </h2>';
                    $mollie_form .= '<div class="form-group">
                              <label for="selectbank" class="col-sm-4 control-label">' . __('Select bank', 'jm_mollie_client') . '</label>
                              <div class="col-sm-8">
                              <select class="form-control" id="selectbank" name="issuer">';
                    foreach ($issuers as $issuer) {
                        if ($issuer->method == Mollie_API_Object_Method::IDEAL) {
                            $mollie_form .= '<option value=' . htmlspecialchars($issuer->id) . '>' . htmlspecialchars($issuer->name) . '</option>';
                        }
                    }
                    $mollie_form .= '<option value="">' . __('or select later', 'jm_mollie_client') . '</option>';
                    $mollie_form .= ' </select></div>
                         </div>';
                } else {
                    $mollie_form .= '<h4>';
                    $mollie_form .= __('Select payment method in the next step', 'jm_mollie_client');
                    $mollie_form .= '</h4>';
                }
                if ($options['checkin_description']) {
                    $mollie_form .= ' <div class="form-group">
                            <label for="checkin" class="col-sm-4 control-label">' . __('Checkin date', 'jm_mollie_client') . '</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" name="checkin" placeholder="YYYY-MM-DD"  data-error="' . __('use the format yyyy-mm-dd', 'jm_mollie_client') . '" required />
                                <div class="help-block with-errors"></div>
                            </div>
                        </div>';
                } else {
                    $mollie_form .= ' <div class="form-group">
                            <label for="checkin" class="col-sm-4 control-label">' . __('Description', 'jm_mollie_client') . '</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" name="checkin"   data-error="' . __('Mandatory field', 'jm_mollie_client') . '" required />
                                <div class="help-block with-errors"></div>
                            </div>
                        </div>';
                }
                $mollie_form .= '<div class="form-group">
                            <label for="emailadr" class="col-sm-4 control-label">' . __('Email', 'jm_mollie_client') . ' </label>
                            <div class="col-sm-8">
                                <input type="email" class="form-control" id="emailadr" name="emailadr" placeholder="' . __('Email', 'jm_mollie_client') . '" data-error="' . __('this is not a valid email address', 'jm_mollie_client') . '" required>
                                <div class="help-block with-errors"></div>
                               </div>
                          </div>';
                $mollie_form .= '<div class="form-group">
                                <label for="amount" class="col-sm-4 control-label">' . __('Amount', 'jm_mollie_client') . '</label>
                                <div class="col-sm-8">
                                  <input type="text" class="form-control" id="amount" name="amount" placeholder="xxxx.xx" pattern="^\d{2,4}\.\d{2}$" data-error="' . __('use the format xxxx.xx', 'jm_mollie_client') . '" required />
                                  <div class="help-block with-errors"></div>
                                </div>
                          </div>';
                $mollie_form .= '';
                $mollie_form .= '<div class="form-group">
                            <div class="col-sm-offset-4 col-sm-8">
                              <button type="submit" class="btn btn-default">' . __('Pay', 'jm_mollie_client') . '</button>
                            </div>
                          </div>
                        </form>
                      ';
                return $mollie_form;
            } else {
                $payment_id = $_SESSION['payment_id'];
                $payment = $mollie->payments->get($payment_id);
                $meta = (array) $payment->metadata;
                do_action('jm_mollie_result', $payment->status, $meta);
                switch ($payment->status) {
                    case "paid" :
                        $status = __('payed', 'jm_mollie_client');
                        break;
                    case "cancelled" :
                        $status = __('canceld', 'jm_mollie_client');
                        break;
                    default :
                        $status = $payment->status;
                }
                return _e('The status of the payment is: ', 'jm_mollie_client') . $status;
            }
        }
        /*
         * Generate a unique order id for this example. It is important to include this unique attribute
         * in the redirectUrl (below) so a proper return page can be shown to the customer.
         */
        $meta_array = array();
        $meta_array['email'] = $_POST["emailadr"];
        $meta_array ['checkin'] = $_POST["checkin"];
        $emailadr = $_POST["emailadr"];
        $amount = $_POST["amount"];
        $checkin = $_POST["checkin"];
        $meta = apply_filters('jm_mollie_meta', $meta_array);


        global $wp;
        $current_url = home_url(add_query_arg(array(), $wp->request));
        $page_id = $options['return_page'];

        If ($options['idealonly']) {
            /*
             * Payment parameters:
             *   amount        Amount in EUROs. 
             *   method        Payment method "ideal".
             *   description   Description of the payment.
             *   redirectUrl   Redirect location. The customer will be redirected there after the payment.
             *   metadata      Custom metadata that is stored with the payment.
             *   issuer        The customer's bank. If empty the customer can select it later.
             */

            $payment = $mollie->payments->create(array(
                "amount" => $amount,
                "method" => Mollie_API_Object_Method::IDEAL,
                "description" => $checkin,
                "redirectUrl" => "{$current_url}?return=1",
                "metadata" => $meta,
                "issuer" => !empty($_POST["issuer"]) ? $_POST["issuer"] : NULL
            ));
        } else {
            /*
             * Payment parameters:
             *   amount        Amount in EUROs. 
             *   description   Description of the payment.
             *   redirectUrl   Redirect location. The customer will be redirected there after the payment.
             *   metadata      Custom metadata that is stored with the payment.
             */
            $payment = $mollie->payments->create(array(
                "amount" => $amount,
                "description" => $checkin,
                "redirectUrl" => "{$current_url}?return=1",
                "metadata" => array(
                    "email" => $emailadr,
                ),
            ));
        }
    } catch (Mollie_API_Exception $e) {
        echo "API call failed: " . htmlspecialchars($e->getMessage());
    }

    /*
     * We store the payment Id in a session variable.
     */
    $_SESSION['payment_id'] = $payment->id;

    /*
     * Send the customer off to complete the payment.
     */
    ?>
    <script type="text/javascript">
        <!--
        window.location = <?php echo "'" . $payment->getPaymentUrl() . "'"; ?>;
        //-->
    </script>
    <?php
    _e('<h2>Wait for Mollie</h2>', 'jm_mollie_client');
}

if (is_admin()) {
    require_once('jm_mollie_client_admin.php');
}