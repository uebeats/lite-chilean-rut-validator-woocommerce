<?php

/**
 * Plugin Name:       Lite Chilean RUT validator
 * Plugin URI:        https://jesuscaballero.cl
 * Description:       Validador de RUT Chileno para Woocommerce
 * Version:           1.0.0
 * Author:            Jesus Caballero P.
 * Author URI:        https://jesuscaballero.cl
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       lite-chilean-rut-validator
 * Domain Path:       /languages
 */

/**
 * If this file is called directly, abort.
 **/
if (!defined('WPINC')) {
  die;
}

/**
 * The code that runs during plugin activation.
 * @since 1.0.0
 * @package lite-chilean-rut-validator
 * @version 1.0.0
 * @return void
 * @link https://developer.wordpress.org/reference/functions/register_activation_hook/
 */
function lcrv_plugin_activate()
{
  add_option('lcrv_show_credits', true); // Opción predeterminada para mostrar los créditos
}
register_activation_hook(__FILE__, 'lcrv_plugin_activate');

/**
 * Enqueue scripts and styles
 * @since 1.0.0
 * @package lite-chilean-rut-validator
 * @version 1.0.0
 * @return void
 * @link https://developer.wordpress.org/reference/functions/wp_enqueue_script/
 * @link https://developer.wordpress.org/reference/functions/wp_enqueue_style/
 * @link https://developer.wordpress.org/reference/functions/is_checkout/
 */
add_action('wp_enqueue_scripts', 'lcrv_enqueue_scripts', 10);
function lcrv_enqueue_scripts()
{

  if (is_checkout()) {
    wp_enqueue_script('lcrv-rut-script', plugins_url('assets/js/jquery.rut.min.js', __FILE__), ['jquery', 'woocommerce'], '1.0.0', true);
    wp_enqueue_script('lcrv-script', plugins_url('assets/js/lcrv-script.js', __FILE__), ['jquery', 'woocommerce', 'lcrv-rut-script'], '1.0.0', true);
  }
  wp_enqueue_style('lcrv-style', plugins_url('assets/css/lcrv-style.css', __FILE__), [], '1.0.0', false);
}

/** 
 * Add the custom field to the checkout page
 * @param array $fields
 * @return array $fields
 * @since 1.0.0
 * @package lite-chilean-rut-validator
 * @version 1.0.0
 **/
add_filter('woocommerce_checkout_fields', 'lcrv_add_custom_checkout_fields');

function lcrv_add_custom_checkout_fields($fields)
{
  $fields['billing']['billing_rut_lcrv'] = array(
    'type' => 'text',
    'label' => __('RUT Cliente', 'mi-plugin'),
    'required' => true,
    'class' => array('form-row-wide'),
    'clear' => true
  );

  return $fields;
}

/**
 * Set custom field as initial field at Checkout
 * @param array $fields
 * @return array $fields
 * @since 1.0.0
 * @package lite-chilean-rut-validator
 * @version 1.0.0
 */
add_filter('woocommerce_checkout_fields', 'lcrv_set_custom_checkout_fields_initial');

function lcrv_set_custom_checkout_fields_initial($fields)
{
  $fields_order = array('billing_rut_lcrv', 'billing_first_name', 'billing_last_name', 'billing_company', 'billing_country', 'billing_address_1', 'billing_address_2', 'billing_city', 'billing_state', 'billing_postcode', 'billing_phone', 'billing_email');

  foreach ($fields_order as $field) {
    if (isset($fields['billing'][$field])) {
      $new_fields[$field] = $fields['billing'][$field];
    }
  }

  $fields['billing'] = $new_fields;

  return $fields;
}

/**
 * Show custom field in the customer data in the order form and
 * save the value in the order meta
 * @param array $order_id
 * @return void
 * @since 1.0.0
 * @package lite-chilean-rut-validator
 * @version 1.0.0
 */
add_action('woocommerce_checkout_update_order_meta', 'lcrv_checkout_field_update_order_meta');

function lcrv_checkout_field_update_order_meta($order_id)
{
  if (!empty($_POST['billing_rut_lcrv'])) {
    $billing_rut_lcrv = sanitize_text_field($_POST['billing_rut_lcrv']);
    update_post_meta($order_id, 'billing_rut_lcrv', $billing_rut_lcrv);
  }
}

add_action('woocommerce_order_details_after_customer_details', 'lcrv_checkout_field_display_admin_order_meta', 10, 1);

function lcrv_checkout_field_display_admin_order_meta($order)
{
  $billing_rut_lcrv = get_post_meta($order->get_id(), 'billing_rut_lcrv', true);

  if (!empty($billing_rut_lcrv)) {
    echo '<p><strong>RUT Cliente:</strong> ' . $billing_rut_lcrv . '</p>';
  }
}

/**
 * Show custom field in the order details in the backoffice
 * @param array $order
 * @return void
 * @since 1.0.0
 * @package lite-chilean-rut-validator
 * @version 1.0.0
 * @see https://docs.woocommerce.com/document/tutorial-customising-checkout-fields-using-actions-and-filters/
 */
add_filter('woocommerce_admin_order_data_after_billing_address', 'lcrv_show_custom_field_backoffice', 10, 1);

function lcrv_show_custom_field_backoffice($order)
{
  $billing_rut_lcrv = get_post_meta($order->get_id(), 'billing_rut_lcrv', true);

  if (!empty($billing_rut_lcrv)) {
    echo '<p><strong>RUT Cliente:</strong> ' . $billing_rut_lcrv . '</p>';
  }
}

/**
 * Show custom field at the beginning of the order notification email
 * @param array $order, $sent_to_admin, $plain_text, $email
 * @return void
 * @since 1.0.0
 * @package lite-chilean-rut-validator
 * @version 1.0.0
 */
add_action('woocommerce_email_order_details', 'lcrv_show_custom_field_notification_email', 10, 4);

function lcrv_show_custom_field_notification_email($order, $sent_to_admin, $plain_text, $email)
{
  $billing_rut_lcrv = get_post_meta($order->get_id(), 'billing_rut_lcrv', true);

  if (!empty($billing_rut_lcrv)) {
    echo '<p><strong>RUT Cliente:</strong> ' . $billing_rut_lcrv . '</p>';
  }
}

/**
 * Validate the custom field
 * @return void
 * @since 1.0.0
 * @package lite-chilean-rut-validator
 * @version 1.0.0
 * 
 */
add_action('woocommerce_checkout_process', 'lcrv_validar_rut_chileno');

function lcrv_validar_rut_chileno()
{
  $rut = isset($_POST['billing_rut_lcrv']) ? sanitize_text_field($_POST['billing_rut_lcrv']) : '';

  if (!lcrvValidarRut($rut)) {
    wc_add_notice('Por favor, ingrese un RUT chileno válido.', 'error');
  }
}

function lcrvValidarRut($rut)
{
  $rut = preg_replace('/[^0-9kK]/', '', $rut);

  if (strlen($rut) < 3) {
    return false;
  }

  $cuerpo = substr($rut, 0, -1);
  $dv = strtoupper(substr($rut, -1));

  $suma = 0;
  $multiplo = 2;

  for ($i = strlen($cuerpo) - 1; $i >= 0; $i--) {
    $suma += intval($cuerpo[$i]) * $multiplo;
    $multiplo = $multiplo === 7 ? 2 : $multiplo + 1;
  }

  $resultado = (11 - ($suma % 11));
  $resultado = $resultado === 10 ? 'K' : strval($resultado);

  return $resultado === $dv;
}

/**
 * Show credits on plugin activation
 * @return void
 * @since 1.0.0
 * @package lite-chilean-rut-validator
 * @version 1.0.0
 */
function lcrv_plugin_menu()
{
  add_menu_page(
    'Admin LCRV', // Título de la página
    'Admin LCRV', // Título del menú
    'manage_options', // Capacidad requerida para acceder a la página
    'lcrv-plugin-settings', // Slug de la página
    'lcrv_plugin_settings_page', // Función de devolución de llamada para mostrar la página
    'dashicons-admin-generic' // Ícono del menú (opcional)
  );
}
add_action('admin_menu', 'lcrv_plugin_menu');

function lcrv_plugin_settings_page()
{
  if (!current_user_can('manage_options')) {
    return;
  }

  if (isset($_POST['update_credits'])) {
    $show_credits = isset($_POST['show_credits']) ? true : false;

    update_option('show_credits', $show_credits);
    echo '<div class="notice notice-success"><p>Opción de créditos actualizada correctamente.</p></div>';
  }

  $show_credits = get_option('show_credits');
?>
  <div class="wrap">
    <h1>Configuración de Créditos de Footer</h1>
    <form method="POST" action="">
      <label for="show_credits">
        <input type="checkbox" name="show_credits" id="show_credits" <?php checked($show_credits, true); ?>>
        Mostrar créditos en el footer
      </label>
      <p class="submit">
        <input type="submit" name="update_credits" id="submit" class="button button-primary" value="Guardar cambios">
      </p>
    </form>
  </div>
<?php
}

function lcrv_plugin_display_credits()
{
  $show_credits = get_option('show_credits') ? get_option('show_credits') : false;
  if ($show_credits) {
    echo '
      <div class="lcrv-copy-footer">
        <p>Potenciado por staffdigitalchile.net</p>
      </div>';
  }
}
add_action('wp_footer', 'lcrv_plugin_display_credits');