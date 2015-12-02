<?php
/**
 * WooCommerce Order Simulator Settings
 *
 * @author      75nineteen
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists( 'WC_Settings_Order_Simulator' ) ) :

    /**
     * WC_Admin_Settings_Order_Simulator
     */
    class WC_Settings_Order_Simulator extends WC_Settings_Page {

        /**
         * Constructor.
         */
        public function __construct() {

            $this->id    = 'order_simulator';
            $this->label = __( 'Order Simulator', 'woocommerce' );

            add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
            add_action( 'woocommerce_settings_' . $this->id, array( $this, 'output' ) );
            add_action( 'woocommerce_settings_save_order_simulator', array( $this, 'save' ) );
        }

        /**
         * Get settings array
         *
         * @return array
         */
        public function get_settings() {
            $values = WC_Order_Simulator::get_settings();

            // selected products
            $product_ids = array_filter( array_map( 'absint', $values['products'] ) );
            $json_ids    = array();

            foreach ( $product_ids as $product_id ) {
                $product = wc_get_product( $product_id );
                if ( is_object( $product ) ) {
                    $json_ids[ $product_id ] = wp_kses_post( html_entity_decode( $product->get_formatted_name(), ENT_QUOTES, get_bloginfo( 'charset' ) ) );
                }
            }

            $data_selected = esc_attr( json_encode( $json_ids ) );
            $data_value    = implode( ',', array_keys( $json_ids ) );

            $settings = apply_filters( 'woocommerce_order_simulator_settings', array(

                array( 'title' => __( 'Settings', 'woocommerce' ), 'type' => 'title', 'desc' => '', 'id' => 'wcos_settings' ),

                array(
                    'title'    => __( 'Orders per Hour', 'woocommerce' ),
                    'desc'     => __( 'The maximum number of orders to generate per hour.', 'woocommerce' ),
                    'id'       => 'wcos_orders_per_hour',
                    'css'      => 'width:100px;',
                    'default'  => $values['orders_per_hour'],
                    'type'     => 'number',
                    'desc_tip' =>  true,
                ),

                array(
                    'title'    => __( 'Products', 'woocommerce' ),
                    'desc'     => __( 'The products that will be added to the generated orders. Leave empty to randomly select from all products.', 'woocommerce' ),
                    'id'       => 'wcos_products',
                    'default'  => $data_value,
                    'type'     => 'text',
                    'class'    => 'wc-product-search',
                    'css'      => 'min-width: 350px;',
                    'custom_attributes' => array(
                        'data-multiple' => "true",
                        'data-selected' => $data_selected
                    ),
                    'desc_tip' =>  true,
                ),

                array(
                    'title'     => __( 'Min Order Products', 'woocommerce' ),
                    'id'        => 'wcos_min_order_products',
                    'desc'      => __( 'The minimum number of products to add to the generated orders', 'woocommerce' ),
                    'type'      => 'number',
                    'custom_attributes' => array(
                        'min'   => 1
                    ),
                    'default'   => $values['min_order_products'],
                    'css'       => 'width:50px;',
                    'autoload'  => false
                ),

                array(
                    'title'     => __( 'Max Order Products', 'woocommerce' ),
                    'desc'      => '',
                    'id'        => 'wcos_max_order_products',
                    'desc'      => __( 'The maximum number of products to add to the generated orders', 'woocommerce' ),
                    'type'      => 'number',
                    'custom_attributes' => array(
                        'min'   => 1
                    ),
                    'default'   => $values['max_order_products'],
                    'css'       => 'width:50px;',
                    'autoload'  => false
                ),

                array(
                    'title'     => __( 'Create User Accounts', 'woocommerce' ),
                    'desc_tip'  => true,
                    'id'        => 'wcos_create_users',
                    'desc'      => __( 'If enabled, accounts will be created and will randomly assigned to new orders.', 'woocommerce' ),
                    'type'      => 'select',
                    'options'   => array(
                        0   => __('No - assign existing accounts to new orders', 'woocommerce'),
                        1   => __('Yes - create a new account or randomly select an existing account to assign to new orders', 'woocommerce')
                    ),
                    'default'   => $values['create_users'],
                    'autoload'  => false,
                    'class'     => 'wc-enhanced-select'
                ),

                array( 'type' => 'sectionend', 'id' => 'wcos_settings'),

            ) );

            return apply_filters( 'woocommerce_get_settings_' . $this->id, $settings );
        }

        /**
         * Output a colour picker input box.
         *
         * @param mixed $name
         * @param string $id
         * @param mixed $value
         * @param string $desc (default: '')
         */
        public function color_picker( $name, $id, $value, $desc = '' ) {
            echo '<div class="color_box"><strong><img class="help_tip" data-tip="' . esc_attr( $desc ) . '" src="' . WC()->plugin_url() . '/assets/images/help.png" height="16" width="16" /> ' . esc_html( $name ) . '</strong>
			<input name="' . esc_attr( $id ). '" id="' . esc_attr( $id ) . '" type="text" value="' . esc_attr( $value ) . '" class="colorpick" /> <div id="colorPickerDiv_' . esc_attr( $id ) . '" class="colorpickdiv"></div>
		</div>';
        }

        /**
         * Save settings
         */
        public function save() {
            $settings = array(
                'orders_per_hour'       => absint( $_POST['wcos_orders_per_hour'] ),
                'products'              => array_map( 'trim', explode( ',', $_POST['wcos_products'] ) ),
                'min_order_products'    => absint( $_POST['wcos_min_order_products'] ),
                'max_order_products'    => absint( $_POST['wcos_max_order_products'] ),
                'create_users'          => (bool) $_POST['wcos_create_users']
            );

            if ( !empty( $settings['products'] ) ) {
                $settings['products'] = array_filter( $settings['products'] );
            }

            if ( empty( $settings['min_order_products'] ) || $settings['min_order_products'] < 1 ) {
                $settings['min_order_products'] = 1;
            }

            if ( empty( $settings['max_order_products'] ) || $settings['max_order_products'] < $settings['min_order_products'] ) {
                $settings['max_order_products'] = $settings['min_order_products'];
            }

            $stored_settings = WC_Order_Simulator::get_settings();
            $settings = wp_parse_args( $settings, $stored_settings );

            update_option( 'wc_order_simulator_settings', $settings );

        }

    }

endif;

return new WC_Settings_Order_Simulator();
