<?php
/**
 * Polar Payment Gateway Class
 *
 * @package Polar_WooCommerce
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * WC_Polar_Gateway Class
 */
class WC_Polar_Gateway extends WC_Payment_Gateway {

    /**
     * Constructor
     */
    public function __construct() {
        $this->id                 = 'polar';
        $this->icon               = ''; // URL of icon if available
        $this->has_fields         = false;
        $this->method_title       = __('Polar', 'apwg');
        $this->method_description = __('Accept payments via Polar.sh Checkout', 'apwg');

        // Load settings
        $this->init_form_fields();
        $this->init_settings();

        // Get setting values
        $this->title              = $this->get_option('title');
        $this->description        = $this->get_option('description');
        $this->access_token       = $this->get_option('access_token');
        $this->product_mapping    = $this->get_option('product_mapping', 'dynamic');
        $this->default_product_id = $this->get_option('default_product_id');
        $this->enabled            = $this->get_option('enabled');
        $this->testmode           = 'yes' === $this->get_option('testmode');
        $this->debug              = 'yes' === $this->get_option('debug');

        // Hooks
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        add_action('woocommerce_api_wc_polar_gateway', array($this, 'handle_webhook'));
        add_action('woocommerce_receipt_' . $this->id, array($this, 'receipt_page'));
        
        // Product meta box hooks
        add_action('add_meta_boxes', array($this, 'add_product_meta_box'));
        add_action('woocommerce_process_product_meta', array($this, 'save_product_meta'));
    }

    /**
     * Initialize Gateway Settings Form Fields
     */
    public function init_form_fields() {
        $this->form_fields = array(
            'enabled' => array(
                'title'   => __('Enable/Disable', 'apwg'),
                'type'    => 'checkbox',
                'label'   => __('Enable Polar Payment Gateway', 'apwg'),
                'default' => 'no'
            ),
            'title' => array(
                'title'       => __('Title', 'apwg'),
                'type'        => 'text',
                'description' => __('Payment method title that customers will see during checkout.', 'apwg'),
                'default'     => __('Credit/Debit Card', 'apwg'),
                'desc_tip'    => true,
            ),
            'description' => array(
                'title'       => __('Description', 'apwg'),
                'type'        => 'textarea',
                'description' => __('Payment method description that customers will see during checkout.', 'apwg'),
                'default'     => __('Pay securely with credit or debit card. You will be redirected to our secure payment page.', 'apwg'),
                'desc_tip'    => true,
            ),
            'access_token' => array(
                'title'       => __('Polar Access Token', 'apwg'),
                'type'        => 'text',
                'description' => __('Enter your Access Token from Polar.sh Dashboard.', 'apwg'),
                'default'     => '',
                'desc_tip'    => true,
            ),
            'product_mapping' => array(
                'title'       => __('Product Mapping', 'apwg'),
                'type'        => 'select',
                'description' => __('Choose how to map WooCommerce products to Polar Product IDs. Recommended: "Single Product + Dynamic Pricing"', 'apwg'),
                'default'     => 'single_dynamic',
                'options'     => array(
                    'single_dynamic' => __('Single Product + Dynamic Pricing (Recommended)', 'apwg'),
                    'single'         => __('Single Product (Use catalog price)', 'apwg'),
                    'meta'           => __('From Product Meta Field', 'apwg'),
                ),
                'desc_tip'    => true,
            ),
            'default_product_id' => array(
                'title'       => __('Default Polar Product ID', 'apwg'),
                'type'        => 'text',
                'description' => __('Default Product ID when using "Single Product" modes.', 'apwg'),
                'default'     => '',
                'desc_tip'    => true,
            ),
            'testmode' => array(
                'title'       => __('Test Mode', 'apwg'),
                'type'        => 'checkbox',
                'label'       => __('Enable test mode', 'apwg'),
                'default'     => 'yes',
                'description' => __('Use for testing during development.', 'apwg'),
            ),
            'debug' => array(
                'title'       => __('Debug Log', 'apwg'),
                'type'        => 'checkbox',
                'label'       => __('Enable logging', 'apwg'),
                'default'     => 'no',
                'description' => sprintf(__('Save logs to %s', 'apwg'), '<code>' . WC_Log_Handler_File::get_log_file_path('polar') . '</code>'),
            ),
        );
    }

    /**
     * Process Payment
     *
     * @param int $order_id Order ID
     * @return array
     */
    public function process_payment($order_id) {
        $order = wc_get_order($order_id);

        try {
            // Create checkout session with Polar API
            $checkout_url = $this->create_checkout_session($order);

            if (!$checkout_url) {
                throw new Exception(__('Unable to create checkout session with Polar.', 'apwg'));
            }

            // Save information to order meta
            $order->update_meta_data('_polar_checkout_url', $checkout_url);
            $order->update_meta_data('_polar_checkout_created', time());
            $order->save();

            // Mark order as pending payment
            $order->update_status('pending', __('Awaiting payment via Polar.', 'apwg'));

            // Return redirect URL
            return array(
                'result'   => 'success',
                'redirect' => $checkout_url
            );

        } catch (Exception $e) {
            wc_add_notice(__('Payment error: ', 'apwg') . $e->getMessage(), 'error');
            $this->log('Payment error: ' . $e->getMessage());
            return array(
                'result' => 'fail'
            );
        }
    }

    /**
     * Create Polar Checkout Session
     *
     * @param WC_Order $order
     * @return string|false Checkout URL or false on failure
     */
    private function create_checkout_session($order) {
        if (empty($this->access_token)) {
            throw new Exception(__('Polar Access Token is not configured.', 'apwg'));
        }

        $api_url = 'https://api.polar.sh/v1/checkouts/';
        
        // Prepare checkout data
        $checkout_data = $this->prepare_checkout_data($order);

        $this->log('Creating checkout session for order #' . $order->get_id());
        $this->log('Checkout data: ' . print_r($checkout_data, true));

        // Send request to Polar API
        $response = wp_remote_post($api_url, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->access_token,
                'Content-Type'  => 'application/json',
            ),
            'body'    => json_encode($checkout_data),
            'timeout' => 30,
        ));

        if (is_wp_error($response)) {
            $this->log('API Error: ' . $response->get_error_message());
            throw new Exception($response->get_error_message());
        }

        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        $data = json_decode($response_body, true);

        $this->log('API Response Code: ' . $response_code);
        $this->log('API Response Body: ' . $response_body);

        if ($response_code !== 200 && $response_code !== 201) {
            $error_message = isset($data['detail']) ? $data['detail'] : 'Unknown API error';
            throw new Exception($error_message);
        }

        if (isset($data['url'])) {
            // Save checkout ID for webhook matching
            if (isset($data['id'])) {
                $order->update_meta_data('_polar_checkout_id', $data['id']);
                $order->save();
                $this->log('Saved checkout ID: ' . $data['id']);
            }
            return $data['url'];
        }

        return false;
    }

    /**
     * Prepare checkout data based on mapping strategy
     *
     * @param WC_Order $order
     * @return array
     */
    private function prepare_checkout_data($order) {
        $checkout_data = array(
            'success_url'          => $this->get_return_url($order),
            'customer_email'       => $order->get_billing_email(),
            'customer_name'        => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
            'external_customer_id' => (string) $order->get_customer_id(),
        );

        // Check if order contains subscription products
        $subscription_data = $this->check_subscription_products($order);
        
        if ($subscription_data) {
            // Use subscription product IDs
            $checkout_data = array_merge($checkout_data, $subscription_data);
        } else {
            // Use regular product mapping
            switch ($this->product_mapping) {
                case 'single_dynamic':
                    // Use single product with ad-hoc pricing
                    $checkout_data = array_merge($checkout_data, $this->prepare_single_dynamic($order));
                    break;

                case 'single':
                    // Use a single Product ID with catalog price
                    if (empty($this->default_product_id)) {
                        throw new Exception(__('Default Product ID is not configured.', 'apwg'));
                    }
                    $checkout_data['product_id'] = $this->default_product_id;
                    break;

                case 'meta':
                    // Get Product ID from meta field
                    $checkout_data = array_merge($checkout_data, $this->prepare_from_meta($order));
                    break;
            }
        }

        return $checkout_data;
    }

    /**
     * Check if order contains subscription products
     *
     * @param WC_Order $order
     * @return array|false Array of product data or false if no subscription products
     */
    private function check_subscription_products($order) {
        $subscription_products = array();
        $has_subscription = false;

        foreach ($order->get_items() as $item) {
            $product = $item->get_product();
            if (!$product) {
                continue;
            }

            $is_subscription = get_post_meta($product->get_id(), '_polar_is_subscription', true);
            
            if ($is_subscription === 'yes') {
                $has_subscription = true;
                $polar_product_id = get_post_meta($product->get_id(), '_polar_product_id', true);
                
                if (empty($polar_product_id)) {
                    throw new Exception(
                        sprintf(
                            __('Product "%s" is marked as subscription but has no Polar Product ID. Please configure it in the product settings.', 'apwg'),
                            $product->get_name()
                        )
                    );
                }
                
                $subscription_products[] = $polar_product_id;
            }
        }

        if ($has_subscription) {
            if (count($subscription_products) === 1) {
                // Single subscription product
                return array('product_id' => $subscription_products[0]);
            } else {
                // Multiple subscription products
                return array('products' => $subscription_products);
            }
        }

        return false;
    }

    /**
     * Prepare single product with dynamic amount using ad-hoc prices
     *
     * @param WC_Order $order
     * @return array
     */
    private function prepare_single_dynamic($order) {
        if (empty($this->default_product_id)) {
            throw new Exception(__('Default Product ID is not configured. Please create a Product on Polar and enter its ID in Settings.', 'apwg'));
        }

        $product_id = $this->default_product_id;
        $total_amount = $order->get_total();
        $currency = strtolower($order->get_currency());

        // Convert amount to cents (multiply by 100)
        $amount_cents = intval($total_amount * 100);

        // Use ad-hoc prices with real Product ID
        return array(
            'products' => array($product_id),
            'prices' => array(
                $product_id => array(
                    array(
                        'amount_type' => 'fixed',
                        'price_amount' => $amount_cents,
                        'price_currency' => $currency,
                    )
                )
            )
        );
    }

    /**
     * Prepare checkout data from product meta fields
     *
     * @param WC_Order $order
     * @return array
     */
    private function prepare_from_meta($order) {
        $product_ids = array();

        foreach ($order->get_items() as $item) {
            $product = $item->get_product();
            if ($product) {
                $polar_product_id = $product->get_meta('_polar_product_id');
                if (!empty($polar_product_id)) {
                    $product_ids[] = $polar_product_id;
                }
            }
        }

        if (empty($product_ids)) {
            throw new Exception(__('No Polar Product ID found in products.', 'apwg'));
        }

        return array(
            'products' => $product_ids
        );
    }

    /**
     * Handle webhook from Polar
     */
    public function handle_webhook() {
        $payload = file_get_contents('php://input');
        $data = json_decode($payload, true);

        $this->log('Webhook received: ' . $payload);

        if (!$data) {
            status_header(400);
            exit;
        }

        if (isset($data['type'])) {
            switch ($data['type']) {
                case 'order.created':
                case 'checkout.updated':
                    $this->handle_payment_success($data);
                    break;
                case 'subscription.created':
                    $this->handle_subscription_created($data);
                    break;
            }
        }

        status_header(200);
        exit;
    }

    /**
     * Handle successful payment
     *
     * @param array $data
     */
    private function handle_payment_success($data) {
        $checkout_id = isset($data['data']['id']) ? $data['data']['id'] : null;
        
        if (!$checkout_id) {
            $this->log('No checkout ID in webhook payload');
            return;
        }

        // Find order by checkout ID
        $orders = wc_get_orders(array(
            'meta_key' => '_polar_checkout_id',
            'meta_value' => $checkout_id,
            'limit' => 1,
        ));

        if (empty($orders)) {
            $this->log('No order found for checkout ID: ' . $checkout_id);
            return;
        }

        $order = $orders[0];
        
        // Check if order is already completed
        if ($order->has_status(array('processing', 'completed'))) {
            $this->log('Order #' . $order->get_id() . ' already completed');
            return;
        }

        // Check webhook status
        $status = isset($data['data']['status']) ? $data['data']['status'] : null;
        
        if ($status === 'succeeded' || $data['type'] === 'order.created') {
            $order->payment_complete();
            $order->add_order_note(__('Payment confirmed via Polar.', 'apwg'));
            $this->log('Order #' . $order->get_id() . ' marked as completed via webhook');
        }
    }

    /**
     * Handle subscription created webhook
     *
     * @param array $data
     */
    private function handle_subscription_created($data) {
        $checkout_id = isset($data['data']['checkout_id']) ? $data['data']['checkout_id'] : null;
        
        if (!$checkout_id) {
            $this->log('No checkout ID in subscription webhook');
            return;
        }

        // Find order by checkout ID
        $orders = wc_get_orders(array(
            'meta_key' => '_polar_checkout_id',
            'meta_value' => $checkout_id,
            'limit' => 1,
        ));

        if (!empty($orders)) {
            $order = $orders[0];
            $subscription_id = isset($data['data']['id']) ? $data['data']['id'] : null;
            
            if ($subscription_id) {
                $order->update_meta_data('_polar_subscription_id', $subscription_id);
                $order->save();
                $this->log('Saved subscription ID: ' . $subscription_id . ' for order #' . $order->get_id());
            }
            
            if (!$order->has_status(array('processing', 'completed'))) {
                $order->payment_complete();
                $order->add_order_note(__('Subscription created and payment confirmed via Polar.', 'apwg'));
                $this->log('Order #' . $order->get_id() . ' completed via subscription webhook');
            }
        }
    }

    /**
     * Receipt page
     *
     * @param int $order_id
     */
    public function receipt_page($order_id) {
        $order = wc_get_order($order_id);
        $checkout_url = $order->get_meta('_polar_checkout_url');

        if ($checkout_url) {
            echo '<p>' . __('You will be redirected to Polar payment page...', 'apwg') . '</p>';
            echo '<script>window.location.href = "' . esc_url($checkout_url) . '";</script>';
        }
    }

    /**
     * Log messages
     *
     * @param string $message
     */
    private function log($message) {
        if ($this->debug) {
            if (!isset($this->logger)) {
                $this->logger = wc_get_logger();
            }
            $this->logger->info($message, array('source' => 'polar'));
        }
    }

    /**
     * Add Polar meta box to product edit page
     */
    public function add_product_meta_box() {
        add_meta_box(
            'polar_product_settings',
            __('Polar Payment Settings', 'apwg'),
            array($this, 'render_product_meta_box'),
            'product',
            'side',
            'default'
        );
    }

    /**
     * Render Polar meta box content
     *
     * @param WP_Post $post
     */
    public function render_product_meta_box($post) {
        wp_nonce_field('polar_product_meta', 'polar_product_meta_nonce');
        
        $is_subscription = get_post_meta($post->ID, '_polar_is_subscription', true);
        $polar_product_id = get_post_meta($post->ID, '_polar_product_id', true);
        
        ?>
        <div class="options_group">
            <p class="form-field">
                <label for="polar_is_subscription">
                    <input type="checkbox" 
                           id="polar_is_subscription" 
                           name="_polar_is_subscription" 
                           value="yes" 
                           <?php checked($is_subscription, 'yes'); ?>>
                    <?php _e('Enable Polar Subscription', 'apwg'); ?>
                </label>
                <span class="description">
                    <?php _e('Check this if this product should be sold as a subscription via Polar.', 'apwg'); ?>
                </span>
            </p>
            
            <p class="form-field" id="polar_product_id_field">
                <label for="polar_product_id"><?php _e('Polar Product ID', 'apwg'); ?></label>
                <input type="text" 
                       id="polar_product_id" 
                       name="_polar_product_id" 
                       value="<?php echo esc_attr($polar_product_id); ?>" 
                       class="short"
                       placeholder="prod_xxx-xxx-xxx">
                <span class="description">
                    <?php _e('Enter the Polar Product ID for this product. Required if subscription is enabled or using "From Product Meta Field" mapping.', 'apwg'); ?>
                </span>
            </p>
        </div>
        
        <style>
            #polar_product_settings .options_group {
                padding: 12px;
            }
            #polar_product_settings .form-field {
                margin-bottom: 15px;
            }
            #polar_product_settings .description {
                display: block;
                margin-top: 5px;
                font-style: italic;
                color: #666;
            }
        </style>
        <?php
    }

    /**
     * Save product meta data
     *
     * @param int $post_id
     */
    public function save_product_meta($post_id) {
        if (!isset($_POST['polar_product_meta_nonce']) || 
            !wp_verify_nonce($_POST['polar_product_meta_nonce'], 'polar_product_meta')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Save subscription flag
        $is_subscription = isset($_POST['_polar_is_subscription']) ? 'yes' : 'no';
        update_post_meta($post_id, '_polar_is_subscription', $is_subscription);

        // Save Polar Product ID
        if (isset($_POST['_polar_product_id'])) {
            $polar_product_id = sanitize_text_field($_POST['_polar_product_id']);
            update_post_meta($post_id, '_polar_product_id', $polar_product_id);
        } else {
            delete_post_meta($post_id, '_polar_product_id');
        }
    }
}
