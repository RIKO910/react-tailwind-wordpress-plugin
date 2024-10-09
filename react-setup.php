<?php
/**
 * Plugin Name:       React Setup
 * Description:       A simple WordPress plugin using React and Tailwind CSS.
 * Requires at least: 5.8
 * Requires PHP:      7.0
 * Version:           1.0.0
 * Author:            Your Name
 * License:           GPL-2.0-or-later
 */

// Hook into the admin menu
add_action( 'admin_menu', 'react_setup_init_menu' );
add_action('wp', 'remove_loop_button');
add_action('woocommerce_product_meta_start', 'send_message');

/**
 * Add Admin Menu.
 */
function react_setup_init_menu() {
    add_menu_page(
        __( 'React Setup', 'react-setup' ),
        __( 'React Setup', 'react-setup' ),
        'manage_options',
        'react-setup',
        'react_setup_admin_page',
        'dashicons-admin-site',
        20
    );
}

/**
 * Admin Page HTML Output.
 */
function react_setup_admin_page() {
    echo '<div id="react-setup-app"><h2>Loading...</h2></div>';
}

/**
 * Remove add to cart button.
 *
 * @since 1.0.0
 * @return void
 */
function remove_loop_button() {
    $store_enabled = get_option('pcm_status_catalog_mode');

    if ( 'yes' === $store_enabled ) {
        remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
        remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
    }
}

/**
 * Add inquiry button and modal to the product page.
 */
function send_message() {
    global $product;
    $product_sku = $product->get_sku(); // Get product SKU
    ?>
    <button id='open-modal' class='button modal-button button-primary'>Send Inquiry</button>

    <!-- Modal Structure -->
    <div id='inquiry-modal' style='display:none;'>
        <div class='modal-content'>
            <span id="close-modal" style="float: right; cursor: pointer;">&times;</span> <!-- Close Button -->
            <h3>Inquiry Form</h3>
            <form id='inquiry-form'>
                <label for='inquiry-name'>Name:</label>
                <input type='text' id='inquiry-name' name='inquiry_name' required><br>

                <label for='inquiry-email'>Email:</label>
                <input type='email' id='inquiry-email' name='inquiry_email' required><br>

                <label for='inquiry-comment'>Comment:</label>
                <textarea id='inquiry-comment' name='inquiry_comment' required></textarea><br>

                <input type='hidden' id='product-sku' name='inquiry_sku' value='<?php echo esc_attr( $product_sku ); ?>'>

                <button type='submit' class='button button-submit button-primary'>Submit</button>
            </form>
        </div>
    </div>

    <?php
}

/**
 * Handle inquiry form submission via AJAX.
 *
 * @since 1.0.0
 * @return void
 */
function send_inquiry() {
    // Check if the fields are set and not empty
    if (isset($_POST['inquiry_name']) && isset($_POST['inquiry_email']) && isset($_POST['inquiry_comment']) && isset($_POST['inquiry_sku'])) {
        $name    = sanitize_text_field($_POST['inquiry_name']);
        $email   = sanitize_email($_POST['inquiry_email']);
        $comment = sanitize_textarea_field($_POST['inquiry_comment']);
        $sku     = sanitize_text_field($_POST['inquiry_sku']);

        // Double-checking if variables are not empty
        if (!empty($name) && !empty($email) && !empty($comment) && !empty($sku)) {
            // Construct the email
            $to      = 'tarikulislamriko910@gmail.com';
            $subject = 'Product Inquiry for SKU: ' . $sku;
            $message = "Name: $name\nEmail: $email\nComment: $comment\nProduct SKU: $sku";
            $headers = array('Content-Type: text/plain; charset=UTF-8', 'From: ' . $email);

            // Send the email and respond with success/failure
            if (wp_mail($to, $subject, $message, $headers)) {
                wp_send_json_success('Inquiry sent successfully!');
            } else {
                wp_send_json_error('Failed to send inquiry.');
            }
        } else {
            wp_send_json_error('One or more fields are empty.');
        }
    } else {
        wp_send_json_error('Required fields are missing.');
    }

    wp_die(); // Required to end AJAX request properly
}

/**
 * Enqueue Scripts and Styles.
 */
add_action( 'admin_enqueue_scripts', 'react_setup_enqueue_assets' );
function react_setup_enqueue_assets() {
    $plugin_url = plugin_dir_url( __FILE__ );

    wp_enqueue_style(
        'react-setup-style',
        $plugin_url . 'build/index.css',
        array(),
        '1.0.0'
    );

    wp_enqueue_script(
        'react-setup-script',
        $plugin_url . 'build/index.js',
        array( 'wp-element' ), // Ensure 'wp-element' is loaded to support React
        '1.0.0',
        true // Load script in the footer
    );
}

/**
 * Register REST API routes to get and update options.
 */
add_action('rest_api_init', function () {
    register_rest_route('jobplace/v1', '/get_option', array(
        'methods' => 'GET',
        'callback' => 'get_pcm_status_catalog_mode',
    ));

    register_rest_route('jobplace/v1', '/update_option', array(
        'methods' => 'POST',
        'callback' => 'update_pcm_status_catalog_mode',
    ));
});

/**
 * Get the option from the wp_options table.
 */
function get_pcm_status_catalog_mode() {
    $isEnabled = get_option('pcm_status_catalog_mode', 'no'); // Default to 'no' if not set
    return rest_ensure_response(array('isEnabled' => $isEnabled));
}

/**
 * Update the option in the wp_options table.
 */
function update_pcm_status_catalog_mode(WP_REST_Request $request) {
    $isEnabled = $request->get_param('isEnabled');

    if ($isEnabled !== null) {
        update_option('pcm_status_catalog_mode', $isEnabled);
        return rest_ensure_response(array('success' => true, 'isEnabled' => $isEnabled));
    }

    return rest_ensure_response(array('success' => false, 'message' => 'Invalid input'));
}

// Hook for AJAX handling
add_action('wp_ajax_send_inquiry', 'send_inquiry');
add_action('wp_ajax_nopriv_send_inquiry', 'send_inquiry');
add_action('wp_enqueue_scripts', 'enqueue_frontend_scripts' );

function enqueue_frontend_scripts() {
    $plugin_url = plugin_dir_url( __FILE__ );

    wp_enqueue_style(
        'react-frontend-style',
        $plugin_url . 'assets/css/frontend.css',
        array(),
        '1.0.0'
    );

    wp_enqueue_script(
        'react-frontend-script',
        $plugin_url . 'assets/js/frontend.js',
        array( 'jquery' ), // Ensure jQuery is loaded
        '1.0.0',
        true // Load script in the footer
    );

    // Localize the script with the AJAX URL and nonce
    wp_localize_script(
        'react-frontend-script',
        'pcm_ajax_obj',
        array(
            'ajax_url' => admin_url('admin-ajax.php')
        )
    );
}
