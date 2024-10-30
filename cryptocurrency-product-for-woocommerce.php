<?php

/*
Plugin Name: Cryptocurrency Product for WooCommerce
Plugin URI: https://wordpress.org/plugins/cryptocurrency-product-for-woocommerce
Description: Cryptocurrency Product for WooCommerce enables customers to buy Ether or any ERC20 or ERC223 token on your WooCommerce store for fiat, bitcoin or any other currency supported by WooCommerce.
Version: 3.16.14
WC requires at least: 5.5.0
WC tested up to: 8.8.3
Author: ethereumicoio
Author URI: https://ethereumico.io
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: cryptocurrency-product-for-woocommerce
Domain Path: /languages
*/
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
// Explicitly globalize to support bootstrapped WordPress
global 
    $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_plugin_basename,
    $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_options,
    $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_plugin_dir,
    $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_plugin_url_path,
    $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_product
;
if ( !function_exists( 'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_deactivate' ) ) {
    function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_deactivate() {
        if ( !current_user_can( 'activate_plugins' ) ) {
            return;
        }
        deactivate_plugins( plugin_basename( __FILE__ ) );
    }

}
if ( PHP_INT_SIZE != 8 ) {
    add_action( 'admin_init', 'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_deactivate' );
    add_action( 'admin_notices', 'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_admin_notice' );
    function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_admin_notice() {
        if ( !current_user_can( 'activate_plugins' ) ) {
            return;
        }
        echo '<div class="error"><p><strong>Cryptocurrency Product for WooCommerce</strong> requires 64 bit architecture server.</p></div>';
        if ( isset( $_GET['activate'] ) ) {
            unset($_GET['activate']);
        }
    }

} else {
    if ( version_compare( phpversion(), '7.2.5', '<' ) ) {
        add_action( 'admin_init', 'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_deactivate' );
        add_action( 'admin_notices', 'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_admin_notice' );
        function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_admin_notice() {
            if ( !current_user_can( 'activate_plugins' ) ) {
                return;
            }
            echo '<div class="error"><p><strong>Cryptocurrency Product for WooCommerce</strong> requires PHP version 7.0 or above.</p></div>';
            if ( isset( $_GET['activate'] ) ) {
                unset($_GET['activate']);
            }
        }

    } else {
        if ( !function_exists( 'gmp_init' ) ) {
            add_action( 'admin_init', 'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_deactivate' );
            add_action( 'admin_notices', 'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_admin_notice_gmp' );
            function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_admin_notice_gmp() {
                if ( !current_user_can( 'activate_plugins' ) ) {
                    return;
                }
                echo '<div class="error"><p><strong>Cryptocurrency Product for WooCommerce</strong> requires  <a target="_blank" href="http://php.net/manual/en/book.gmp.php">GMP</a> module to be installed.</p></div>';
                if ( isset( $_GET['activate'] ) ) {
                    unset($_GET['activate']);
                }
            }

        } else {
            if ( !function_exists( 'mb_strtolower' ) ) {
                add_action( 'admin_init', 'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_deactivate' );
                add_action( 'admin_notices', 'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_admin_notice_mbstring' );
                function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_admin_notice_mbstring() {
                    if ( !current_user_can( 'activate_plugins' ) ) {
                        return;
                    }
                    echo '<div class="error"><p><strong>Cryptocurrency Product for WooCommerce</strong> requires  <a target="_blank" href="http://php.net/manual/en/book.mbstring.php">Multibyte String (mbstring)</a> module to be installed.</p></div>';
                    if ( isset( $_GET['activate'] ) ) {
                        unset($_GET['activate']);
                    }
                }

            } else {
                /**
                 * Check if WooCommerce is active
                 * https://wordpress.stackexchange.com/a/193908/137915
                 **/
                if ( !in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
                    add_action( 'admin_init', 'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_deactivate' );
                    add_action( 'admin_notices', 'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_admin_notice_woocommerce' );
                    function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_admin_notice_woocommerce() {
                        if ( !current_user_can( 'activate_plugins' ) ) {
                            return;
                        }
                        echo '<div class="error"><p><strong>Cryptocurrency Product for WooCommerce</strong> requires WooCommerce plugin to be installed and activated.</p></div>';
                        if ( isset( $_GET['activate'] ) ) {
                            unset($_GET['activate']);
                        }
                    }

                } else {
                    if ( function_exists( 'cryptocurrency_product_for_woocommerce_freemius_init' ) ) {
                        cryptocurrency_product_for_woocommerce_freemius_init()->set_basename( false, __FILE__ );
                    } else {
                        // Create a helper function for easy SDK access.
                        function cryptocurrency_product_for_woocommerce_freemius_init() {
                            global $cryptocurrency_product_for_woocommerce_freemius_init;
                            if ( !isset( $cryptocurrency_product_for_woocommerce_freemius_init ) ) {
                                // Include Freemius SDK.
                                require_once dirname( __FILE__ ) . '/vendor/freemius/wordpress-sdk/start.php';
                                $cryptocurrency_product_for_woocommerce_freemius_init = fs_dynamic_init( array(
                                    'id'              => '4418',
                                    'slug'            => 'cryptocurrency-product-for-woocommerce',
                                    'type'            => 'plugin',
                                    'public_key'      => 'pk_ad7ad2f13633e6e97e62528e0259b',
                                    'is_premium'      => false,
                                    'premium_suffix'  => 'Professional',
                                    'has_addons'      => true,
                                    'has_paid_plans'  => true,
                                    'trial'           => array(
                                        'days'               => 7,
                                        'is_require_payment' => true,
                                    ),
                                    'has_affiliation' => 'all',
                                    'menu'            => array(
                                        'slug'   => 'cryptocurrency-product-for-woocommerce',
                                        'parent' => array(
                                            'slug' => 'options-general.php',
                                        ),
                                    ),
                                    'is_live'         => true,
                                ) );
                            }
                            return $cryptocurrency_product_for_woocommerce_freemius_init;
                        }

                        // Init Freemius.
                        cryptocurrency_product_for_woocommerce_freemius_init();
                        // Signal that SDK was initiated.
                        do_action( 'cryptocurrency_product_for_woocommerce_freemius_init_loaded' );
                        // ... Your plugin's main file logic ...
                        function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_custom_addons_pricing() {
                            global $cryptocurrency_product_for_woocommerce_freemius_init;
                            $is_whitelabeled = $cryptocurrency_product_for_woocommerce_freemius_init->is_whitelabeled();
                            $slug = $cryptocurrency_product_for_woocommerce_freemius_init->get_slug();
                            /**
                             * @var \FS_Plugin[]
                             */
                            $addons = $cryptocurrency_product_for_woocommerce_freemius_init->get_addons();
                            if ( $addons ) {
                                foreach ( $addons as $addon ) {
                                    // $basename = $cryptocurrency_product_for_woocommerce_freemius_init->get_addon_basename($addon->id);
                                    $addon_id = $addon->id;
                                    $addon_slug = $addon->slug;
                                    $plans_and_pricing_by_addon_id = $cryptocurrency_product_for_woocommerce_freemius_init->_get_addons_plans_and_pricing_map_by_id();
                                    $price = 0;
                                    $has_trial = false;
                                    $has_free_plan = false;
                                    $has_paid_plan = false;
                                    if ( isset( $plans_and_pricing_by_addon_id[$addon_id] ) ) {
                                        $plans = $plans_and_pricing_by_addon_id[$addon_id];
                                        if ( is_array( $plans ) && 0 < count( $plans ) ) {
                                            $min_price = 999999;
                                            foreach ( $plans as $plan ) {
                                                if ( !isset( $plan->pricing ) || !is_array( $plan->pricing ) || 0 == count( $plan->pricing ) ) {
                                                    // No pricing means a free plan.
                                                    $has_free_plan = true;
                                                    continue;
                                                }
                                                $has_paid_plan = true;
                                                $has_trial = $has_trial || is_numeric( $plan->trial_period ) && $plan->trial_period > 0;
                                                foreach ( $plan->pricing as $pricing ) {
                                                    $pricing = new \FS_Pricing($pricing);
                                                    if ( !$pricing->is_usd() ) {
                                                        /**
                                                         * Skip non-USD pricing.
                                                         *
                                                         * @author Leo Fajardo (@leorw)
                                                         * @since 2.3.1
                                                         */
                                                        continue;
                                                    }
                                                    if ( $pricing->has_annual() ) {
                                                        $min_price = min( $min_price, $pricing->annual_price );
                                                    }
                                                    if ( $pricing->has_monthly() ) {
                                                        $min_price = min( $min_price, $pricing->monthly_price );
                                                    }
                                                }
                                                if ( $min_price < 999999 ) {
                                                    $price = $min_price;
                                                }
                                            }
                                        }
                                    }
                                    if ( !$has_paid_plan && !$has_free_plan ) {
                                        continue;
                                    }
                                    $price_str = '';
                                    if ( $is_whitelabeled ) {
                                        $price_str = '&nbsp;';
                                    } else {
                                        $descriptors = array();
                                        if ( $has_free_plan ) {
                                            $descriptors[] = \fs_text_inline( 'Free', 'free', $slug );
                                        }
                                        if ( $has_paid_plan && $price > 0 ) {
                                            $descriptors[] = '$' . \number_format( $price, 2 );
                                        }
                                        if ( $has_trial ) {
                                            $descriptors[] = \fs_text_x_inline(
                                                'Trial',
                                                'trial period',
                                                'trial',
                                                $slug
                                            );
                                        }
                                        $price_str = \implode( ' - ', $descriptors );
                                    }
                                    ?>
                    <script type="text/javascript">
                        jQuery('.fs-cards-list .fs-card.fs-addon[data-slug="<?php 
                                    echo $addon_slug;
                                    ?>"] .fs-offer .fs-price').text('<?php 
                                    echo $price_str;
                                    ?>');
                    </script>
                <?php 
                                }
                            }
                        }

                        cryptocurrency_product_for_woocommerce_freemius_init()->add_action( 'addons/after_addons', 'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_custom_addons_pricing' );
                        $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_plugin_basename = plugin_basename( dirname( __FILE__ ) );
                        $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_plugin_dir = untrailingslashit( plugin_dir_path( __FILE__ ) );
                        $plugin_url_path = untrailingslashit( plugin_dir_url( __FILE__ ) );
                        // HTTPS?
                        $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_plugin_url_path = ( is_ssl() ? str_replace( 'http:', 'https:', $plugin_url_path ) : $plugin_url_path );
                        // Set plugin options
                        $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_options = get_option( 'cryptocurrency-product-for-woocommerce_options', array() );
                        require $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_plugin_dir . '/vendor/autoload.php';
                        // require_once $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_plugin_dir .
                        //     '/vendor/woocommerce/action-scheduler/action-scheduler.php';
                        require_once $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_plugin_dir . '/cryptocurrency-product-for-woocommerce.eth.php';
                        function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_product_type_options(  $product_type_options  ) {
                            $cryptocurrency = array(
                                'cryptocurrency_product_for_woocommerce_cryptocurrency_product_type' => array(
                                    'id'            => '_cryptocurrency_product_for_woocommerce_cryptocurrency_product_type',
                                    'wrapper_class' => 'show_if_simple show_if_variable show_if_auction',
                                    'label'         => __( 'Cryptocurrency', 'cryptocurrency-product-for-woocommerce' ),
                                    'description'   => __( 'Make product a cryptocurrency.', 'cryptocurrency-product-for-woocommerce' ),
                                    'default'       => 'no',
                                ),
                            );
                            // combine the two arrays
                            $product_type_options = array_merge( $cryptocurrency, $product_type_options );
                            //    CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log('product_type_options=' . print_r($product_type_options, true));
                            return apply_filters( 'cryptocurrency_product_for_woocommerce_product_type_options', $product_type_options );
                        }

                        add_filter( 'product_type_options', 'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_product_type_options' );
                        // Function to check if a product is a cryptocurrency
                        function _CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_is_cryptocurrency(  $product_id  ) {
                            $cryptocurrency = _CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_post_meta( $product_id, '_cryptocurrency_product_for_woocommerce_cryptocurrency_product_type', true );
                            $is_cryptocurrency = ( !empty( $cryptocurrency ) ? 'yes' : 'no' );
                            return $is_cryptocurrency === 'yes';
                        }

                        function _CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE__before_POST_processing_action_impl() {
                            do_action( 'cryptocurrency_product_for_woocommerce_before_POST_processing_action' );
                        }

                        function _CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE__get_hide_element_classes(  $classes  ) {
                            return trim( $classes . ' ' . 'hidden hide-all' );
                        }

                        add_filter( 'cryptocurrency_product_for_woocommerce__get_hide_element_classes', '_CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE__get_hide_element_classes' );
                        function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_process_meta(  $post_id, $post  ) {
                            global 
                                $wpdb,
                                $woocommerce,
                                $woocommerce_errors,
                                $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_options
                            ;
                            $product = wc_get_product( $post_id );
                            if ( !$product ) {
                                return $post_id;
                            }
                            if ( get_post_type( $post_id ) != 'product' ) {
                                return $post_id;
                            }
                            _CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE__before_POST_processing_action_impl();
                            // check if we are called from the product settings page
                            // fix from: https://stackoverflow.com/questions/5434219/problem-with-wordpress-save-post-action#comment6729746_5849143
                            if ( !isset( $_POST['_text_input_cryptocurrency_flag'] ) ) {
                                return $post_id;
                            }
                            if ( !current_user_can( 'edit_product', $post_id ) ) {
                                return $post_id;
                            }
                            $is_cryptocurrency = ( isset( $_POST['_cryptocurrency_product_for_woocommerce_cryptocurrency_product_type'] ) ? 'yes' : 'no' );
                            if ( $is_cryptocurrency != 'yes' ) {
                                delete_post_meta( $post_id, '_cryptocurrency_product_for_woocommerce_cryptocurrency_product_type' );
                                return $post_id;
                            }
                            update_post_meta( $post_id, '_cryptocurrency_product_for_woocommerce_cryptocurrency_product_type', $is_cryptocurrency );
                            //    if ( get_option( "woocommerce_enable_multiples") != "yes" ) {
                            //        update_post_meta( $post_id, '_sold_individually', $is_cryptocurrency );
                            //    }
                            $want_physical = get_option( 'woocommerce_enable_physical' );
                            if ( $want_physical == "no" ) {
                                update_post_meta( $post_id, '_virtual', $is_cryptocurrency );
                            }
                            //
                            // Handle first save
                            //
                            // Select
                            $cryptocurrency_option = $_POST['_select_cryptocurrency_option'];
                            if ( !empty( $cryptocurrency_option ) ) {
                                update_post_meta( $post_id, '_select_cryptocurrency_option', esc_attr( $cryptocurrency_option ) );
                            } else {
                                CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( 'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_process_meta: _select_cryptocurrency_option set to empty' );
                                update_post_meta( $post_id, '_select_cryptocurrency_option', '' );
                            }
                            //    if ( isset( $_POST['_text_input_cryptocurrency_data'] ) ) {
                            //        update_post_meta( $post_id, '_text_input_cryptocurrency_data', sanitize_text_field( $_POST['_text_input_cryptocurrency_data'] ) );
                            //    }
                            if ( isset( $_POST['_text_input_cryptocurrency_minimum_value'] ) ) {
                                update_post_meta( $post_id, '_text_input_cryptocurrency_minimum_value', sanitize_text_field( $_POST['_text_input_cryptocurrency_minimum_value'] ) );
                            }
                            if ( isset( $_POST['_text_input_cryptocurrency_step'] ) ) {
                                update_post_meta( $post_id, '_text_input_cryptocurrency_step', sanitize_text_field( $_POST['_text_input_cryptocurrency_step'] ) );
                            }
                            do_action(
                                'cryptocurrency_product_for_woocommerce_save_option_field',
                                $cryptocurrency_option,
                                $post_id,
                                $product
                            );
                        }

                        add_action(
                            'save_post',
                            'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_process_meta',
                            10,
                            2
                        );
                        // @see https://wordpress.stackexchange.com/a/42178/137915
                        function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_redirect_location(  $location, $post_id  ) {
                            //If post was published...
                            if ( isset( $_POST['publish'] ) ) {
                                //obtain current post status
                                $status = get_post_status( $post_id );
                                //The post was 'published', but if it is still a draft, display draft message (10).
                                if ( $status == 'draft' ) {
                                    $location = add_query_arg( 'message', 10, $location );
                                }
                            }
                            return $location;
                        }

                        add_filter(
                            'redirect_post_location',
                            'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_redirect_location',
                            10,
                            2
                        );
                        /**
                         * Show pricing fields for cryptocurrency product.
                         */
                        function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_custom_js() {
                            // global $post;
                            do_action( 'cryptocurrency_product_for_woocommerce_custom_js' );
                        }

                        add_action( 'cryptocurrency_product_for_woocommerce_custom_js', function () {
                            if ( 'product' != get_post_type() ) {
                                return;
                            }
                            CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_custom_js_aux();
                        } );
                        /**
                         * Show pricing fields for cryptocurrency product.
                         */
                        function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_custom_js_aux() {
                            global $post;
                            //    $post_id = $post->ID;
                            //    $product = wc_get_product( $post_id );
                            //    if (!$product) {
                            //        return;
                            //    }
                            //    if (!_CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_is_cryptocurrency( $product->get_id() )) {
                            //        return;
                            //    }
                            ?>
                <script type='text/javascript'>
                    <?php 
                            ?>

                    jQuery(document).ready(function() {
                        window.CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_regular_price_str = "<?php 
                            _e( 'Regular price', 'woocommerce' );
                            ?>";
                        window.CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_sale_price_str = "<?php 
                            _e( 'Sale price', 'woocommerce' );
                            ?>";
                        window.CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_default_currency_symbol_str = "<?php 
                            echo esc_attr( get_woocommerce_currency_symbol() );
                            ?>";
                        window.CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_hide_element_classes = "<?php 
                            echo esc_attr( apply_filters( 'cryptocurrency_product_for_woocommerce__get_hide_element_classes', '' ) );
                            ?>";

                        CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_init();
                        //			jQuery( '.options_group.pricing' ).addClass( 'show_if_cryptocurrency_product_for_woocommerce_cryptocurrency_product_type' ).show();
                        jQuery('#_select_cryptocurrency_option').on('change', CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_select_cryptocurrency_option_change);
                        CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_select_cryptocurrency_option_change();
                        jQuery('#_cryptocurrency_product_for_woocommerce_cryptocurrency_product_type').on('change', CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_select_cryptocurrency_product_type);
                        CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_select_cryptocurrency_product_type();
                        <?php 
                            ?>

                    });
                </script>
            <?php 
                        }

                        add_action( 'admin_footer', 'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_custom_js' );
                        /**
                         * Amount in base crypto for one $
                         *
                         * @param int $product_id
                         * @return double
                         */
                        function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_product_rate_(  $product_id  ) {
                            $_product = wc_get_product( $product_id );
                            if ( !$_product ) {
                                CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_product_rate_({$product_id}) not a product" );
                                return 1;
                            }
                            $price = doubleval( $_product->get_price() );
                            if ( 0 == $price ) {
                                CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_product_rate_({$product_id}) zero product price" );
                                return 1;
                            }
                            return 1 / $price;
                        }

                        /**
                         * Amount in crypto for the item specified
                         *
                         * @param int $product_id
                         * @param object $item
                         * @return double
                         */
                        function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_product_quantity_for_item(  $product_id, $item  ) {
                            return $item['qty'];
                        }

                        /**
                         * Product price in $
                         *
                         * @param double $orig_price
                         * @param type $product
                         * @param bool $sale
                         * @return double
                         */
                        function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_product_price(  $orig_price, $product, $sale = false  ) {
                            $product_id = ( !is_null( $product ) ? $product->get_id() : null );
                            if ( $sale && empty( $orig_price ) ) {
                                return $orig_price;
                            }
                            return $orig_price;
                        }

                        function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_base_cryptocurrency_symbol(  $product_id  ) {
                            global $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_plugin_dir;
                            $baseCurrency = '';
                            $_select_dynamic_price_source_option = _CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_post_meta( $product_id, '_select_dynamic_price_source_option', true );
                            if ( empty( $_select_dynamic_price_source_option ) ) {
                                return $baseCurrency;
                            }
                            return apply_filters(
                                'cryptocurrency_product_for_woocommerce_get_base_cryptocurrency_symbol',
                                $baseCurrency,
                                $_select_dynamic_price_source_option,
                                $product_id
                            );
                        }

                        // @see https://www.php.net/manual/en/function.debug-backtrace.php#112238
                        function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_generateCallTrace() {
                            $e = new Exception();
                            $trace = explode( "\n", $e->getTraceAsString() );
                            // reverse array to make steps line up chronologically
                            $trace = array_reverse( $trace );
                            array_shift( $trace );
                            // remove {main}
                            array_pop( $trace );
                            // remove call to this method
                            $length = count( $trace );
                            $result = array();
                            for ($i = 0; $i < $length; $i++) {
                                $result[] = $i + 1 . ')' . substr( $trace[$i], strpos( $trace[$i], ' ' ) );
                                // replace '#someNum' with '$i)', set the right ordering
                            }
                            return "\t" . implode( "\n\t", $result );
                        }

                        // @see https://stackoverflow.com/a/47788626/4256005
                        add_filter(
                            'woocommerce_product_get_price',
                            'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_product_get_price',
                            10,
                            2
                        );
                        add_filter(
                            'woocommerce_product_get_sale_price',
                            'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_product_get_sale_price',
                            10,
                            2
                        );
                        add_filter(
                            'woocommerce_product_get_regular_price',
                            'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_product_get_regular_price',
                            10,
                            2
                        );
                        function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_product_get_price(  $price, $product  ) {
                            global $post;
                            //    CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log('CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_product_get_price $price: ' . $price);
                            if ( !_CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_is_cryptocurrency( $product->get_id() ) ) {
                                return $price;
                            }
                            //    if( is_shop() || is_product_category() || is_product_tag() || is_product() || is_cart() || is_checkout() || (wp_doing_ajax() && !is_checkout()) ||
                            //        ( function_exists('get_current_screen') &&
                            //        get_current_screen() && get_current_screen()->parent_base == 'woocommerce' &&
                            //        'shop_order' == get_post_type($post) )
                            //    ) {
                            if ( $product->is_on_sale() ) {
                                if ( empty( $price ) ) {
                                    return $price;
                                }
                                $sale_price = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_product_price( $price, $product, true );
                                // / CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_product_rate_($product->get_id());
                                //           CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log('CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_product_get_price $sale_price: ' . $sale_price);
                                return $sale_price;
                            } else {
                                $regular_price = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_product_price( $price, $product );
                                // / CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_product_rate_($product->get_id());
                                //           CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log('CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_product_get_price $regular_price: ' . $regular_price);
                                return $regular_price;
                            }
                            //    }
                            return $price;
                        }

                        function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_product_get_sale_price(  $price, $product  ) {
                            global $post;
                            if ( !_CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_is_cryptocurrency( $product->get_id() ) ) {
                                return $price;
                            }
                            if ( is_shop() || is_product_category() || is_product_tag() || is_product() || wp_doing_ajax() && !is_checkout() || function_exists( 'get_current_screen' ) && get_current_screen() && get_current_screen()->parent_base == 'woocommerce' && 'shop_order' == get_post_type( $post ) ) {
                                if ( empty( $price ) ) {
                                    return $price;
                                }
                                $sale_price = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_product_price( $price, $product, true );
                                // / CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_product_rate_($product->get_id());
                                //       CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log('CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_product_get_sale_price $sale_price: ' . $sale_price);
                                return $sale_price;
                            }
                            return $price;
                        }

                        function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_product_get_regular_price(  $price, $product  ) {
                            global $post;
                            if ( !_CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_is_cryptocurrency( $product->get_id() ) ) {
                                return $price;
                            }
                            if ( is_shop() || is_product_category() || is_product_tag() || is_product() || wp_doing_ajax() && !is_checkout() || function_exists( 'get_current_screen' ) && get_current_screen() && get_current_screen()->parent_base == 'woocommerce' && 'shop_order' == get_post_type( $post ) ) {
                                $regular_price = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_product_price( $price, $product );
                                // / CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_product_rate_($product->get_id());
                                //       CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log('CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_product_get_regular_price $regular_price: ' . $regular_price);
                                return $regular_price;
                            }
                            return $price;
                        }

                        //add_filter( 'woocommerce_add_cart_item', 'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_add_cart_item', 20, 2 );
                        //function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_add_cart_item( $cart_data, $cart_item_key ) {
                        //    $product_id = $cart_data['product_id'];
                        //    if (!_CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_is_cryptocurrency( $product_id )) {
                        //        return $cart_data;
                        //    }
                        //    $product = wc_get_product($product_id);
                        //    $new_price = $cart_data['data']->get_price();
                        //    CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log('CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_add_cart_item $cart_data[data]->get_price(): ' . $cart_data['data']->get_price());
                        //    // Price calculation
                        //    if ( $product->is_on_sale() ) {
                        //        if (empty($new_price)) {
                        //            return $new_price;
                        //        }
                        //        $product_price = $product->get_sale_price();
                        //        CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log('CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_add_cart_item $product_price: ' . $product_price);
                        //        if ($new_price < $product_price) {
                        //            $new_price = $product_price;
                        //        }
                        ////        $new_price = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_product_price( $new_price, $product, true ) / CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_product_rate_($product_id);
                        //        CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log('CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_add_cart_item CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_product_rate_($product_id): ' . CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_product_rate_($product_id));
                        //        $new_price = $new_price / CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_product_rate_($product_id);
                        //    } else {
                        //        CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log('CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_add_cart_item CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_product_price( $new_price, $product ): ' . CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_product_price( $new_price, $product ));
                        //        CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log('CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_add_cart_item CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_product_rate_($product_id): ' . CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_product_rate_($product_id));
                        //        $product_price = $product->get_regular_price();
                        //        CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log('CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_add_cart_item $product_price: ' . $product_price);
                        //        if ($new_price < $product_price) {
                        //            $new_price = $product_price;
                        //        }
                        ////        $new_price = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_product_price( $new_price, $product ) / CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_product_rate_($product_id);
                        //        $new_price = $new_price / CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_product_rate_($product_id);
                        //    }
                        //    CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log('CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_add_cart_item $new_price: ' . $new_price);
                        //
                        //    // Set and register the new calculated price
                        //    $cart_data['data']->set_price( $new_price );
                        //    $cart_data['new_price'] = $new_price;
                        //
                        //    return $cart_data;
                        //}
                        add_filter(
                            'woocommerce_get_cart_item_from_session',
                            'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_get_cart_item_from_session',
                            20,
                            3
                        );
                        function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_get_cart_item_from_session(  $session_data, $values, $key  ) {
                            if ( !isset( $session_data['new_price'] ) || empty( $session_data['new_price'] ) ) {
                                return $session_data;
                            }
                            //    CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log('CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_get_cart_item_from_session $session_data[new_price]: ' . $session_data['new_price']);
                            // Get the new calculated price and update cart session item price
                            $session_data['data']->set_price( $session_data['new_price'] );
                            return $session_data;
                        }

                        // // @see https://github.com/woocommerce/woocommerce/blob/e2b59d44ee88c131e044c49e508767620415e1e6/includes/wc-formatting-functions.php#L557
                        // /**
                        //  * Format the price with a currency symbol.
                        //  *
                        //  * @param  float $price Formatted price.
                        //  * @param  array $args  Arguments to format a price {
                        //  *     Array of arguments.
                        //  *     Defaults to empty array.
                        //  *
                        //  *     @type bool   $ex_tax_label       Adds exclude tax label.
                        //  *                                      Defaults to false.
                        //  *     @type string $currency           Currency code.
                        //  *                                      Defaults to empty string (Use the result from get_woocommerce_currency()).
                        //  *     @type string $decimal_separator  Decimal separator.
                        //  *                                      Defaults the result of wc_get_price_decimal_separator().
                        //  *     @type string $thousand_separator Thousand separator.
                        //  *                                      Defaults the result of wc_get_price_thousand_separator().
                        //  *     @type string $decimals           Number of decimals.
                        //  *                                      Defaults the result of wc_get_price_decimals().
                        //  *     @type string $price_format       Price format depending on the currency position.
                        //  *                                      Defaults the result of get_woocommerce_price_format().
                        //  * }
                        //  * @param  float $unformatted_price Raw price.
                        //  * @return string
                        //  */
                        // function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_wc_price( $return, $price, $args, $unformatted_price ) {
                        //     global $wp, $woocommerce, $post, $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_product;
                        // //    CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log('CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_wc_price: return=' . $return . '; price=' . $price . '; unformatted_price=' . $unformatted_price . '; is_shop=' . is_shop() . '; is_cart=' . is_cart() . '; is_checkout=' . is_checkout() . '; wp_doing_ajax=' . wp_doing_ajax());
                        //         if (cryptocurrency_product_for_woocommerce_freemius_init()->can_use_premium_code__premium_only()) {
                        //
                        //     $product = null;
                        //     if (wp_doing_ajax() && !is_checkout() && !is_null(WC()->cart)) {
                        // //        CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log("CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_wc_price($post_id) is_cart() || is_checkout()");
                        //         foreach( WC()->cart->get_cart() as $cart_item_key => $p ) {
                        //             if (_CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_is_cryptocurrency($p['product_id'])) {
                        //                 $product = wc_get_product( $p['product_id'] );
                        //                 break;
                        //             }
                        //         }
                        //     }
                        //     if (!$post) {
                        //         if (is_null($product)) {
                        //             return $return;
                        //         }
                        //     }
                        //     $post_id = $post->ID;
                        //     if (!$product) {
                        //         $product = wc_get_product( $post_id );
                        //     }
                        //     if (!$product && isset($CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_product) &&
                        //         !is_null($CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_product)
                        //     ) {
                        //         $product = $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_product;
                        //     }
                        //     if (!$product && !is_null(WC()->cart)/* && (is_cart() || is_checkout() || is_shop())*/) {
                        // //        CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log("CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_wc_price($post_id) is_cart() || is_checkout()");
                        //         foreach( WC()->cart->get_cart() as $cart_item_key => $p ) {
                        //             if (_CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_is_cryptocurrency($p['product_id'])) {
                        //                 $product = wc_get_product( $p['product_id'] );
                        //                 break;
                        //             }
                        //         }
                        //     }
                        //     if (!$product && is_checkout_pay_page() && isset($wp->query_vars['order-pay'])) {
                        //         $order = wc_get_order($wp->query_vars['order-pay']);
                        //         if ($order) {
                        //             $order_items = $order->get_items();
                        //             foreach( $order_items as $p ) {
                        //                 $product_id = $p['product_id'];
                        //                 if (_CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_is_cryptocurrency($product_id)) {
                        //                     $product = wc_get_product( $product_id );
                        //                     break;
                        //                 }
                        //             }
                        //         }
                        //     }
                        //     if (!$product && is_order_received_page() && isset($wp->query_vars['order-received'])) {
                        //         $order = wc_get_order($wp->query_vars['order-received']);
                        //         if ($order) {
                        //             $order_items = $order->get_items();
                        //             foreach( $order_items as $p ) {
                        //                 $product_id = $p['product_id'];
                        //                 if (_CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_is_cryptocurrency($product_id)) {
                        //                     $product = wc_get_product( $product_id );
                        //                     break;
                        //                 }
                        //             }
                        //         }
                        //     }
                        //     if (!$product) {
                        //         $order_id = $post_id;
                        //         $order = wc_get_order($order_id);
                        //         if ($order) {
                        //             $order_items = $order->get_items();
                        //             foreach( $order_items as $p ) {
                        //                 $product_id = $p['product_id'];
                        //                 if (_CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_is_cryptocurrency($product_id)) {
                        //                     $product = wc_get_product( $product_id );
                        //                     break;
                        //                 }
                        //             }
                        //         }
                        //     }
                        //
                        //     if (!$product) {
                        // //        CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log("CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_wc_price($post_id) not a product");
                        //         return $return;
                        //     }
                        //     $return = _CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_wc_price_html_ex( $return, $price, $args, $unformatted_price, $product );
                        //
                        //     }
                        //     return $return;
                        // }
                        // add_filter( 'wc_price', 'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_wc_price', 100, 5 );
                        add_filter(
                            'woocommerce_cart_product_price',
                            'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_cart_product_price',
                            PHP_INT_MAX,
                            2
                        );
                        add_filter(
                            'woocommerce_get_price_html',
                            'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_get_price_html',
                            PHP_INT_MAX,
                            2
                        );
                        // add_filter( 'woocommerce_get_variation_price_html',  'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_get_variation_price_html', PHP_INT_MAX, 2 ); // used only in below WooCommerce v3.0.0
                        function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_cart_product_price(  $price, $product  ) {
                            if ( is_null( WC()->cart ) ) {
                                //        CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log("CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_cart_product_price: is_null(WC()->cart)");
                                return $price;
                            }
                            if ( !_CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_is_cryptocurrency( $product->get_id() ) ) {
                                //        CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log("CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_cart_product_price: not a cryptocurrency: " . $product->get_id());
                                return $price;
                            }
                            if ( WC()->cart->display_prices_including_tax() ) {
                                $product_price = wc_get_price_including_tax( $product );
                            } else {
                                $product_price = wc_get_price_excluding_tax( $product );
                            }
                            $price = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_wc_price_html( $product_price, $product );
                            remove_filter( 'woocommerce_cart_product_price', 'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_cart_product_price', PHP_INT_MAX );
                            $price = apply_filters( 'woocommerce_cart_product_price', $price, $product );
                            add_filter(
                                'woocommerce_cart_product_price',
                                'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_cart_product_price',
                                PHP_INT_MAX,
                                2
                            );
                            return $price;
                        }

                        function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_get_price_html(  $price, $product  ) {
                            if ( !_CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_is_cryptocurrency( $product->get_id() ) ) {
                                return $price;
                            }
                            if ( class_exists( 'WC_Product_Variable' ) && $product instanceof WC_Product_Variable ) {
                                return CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_get_price_html_WC_Product_Variable( $price, $product );
                            } else {
                                if ( class_exists( 'WC_Product_Grouped' ) && $product instanceof WC_Product_Grouped ) {
                                    return CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_get_price_html_WC_Product_Grouped( $price, $product );
                                }
                            }
                            return CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_get_price_html_WC_Product( $price, $product );
                        }

                        function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_get_price_html_WC_Product(  $price, $product  ) {
                            if ( '' === $product->get_price() ) {
                                $price = apply_filters( 'woocommerce_empty_price_html', '', $product );
                            } else {
                                $simple_price = wc_get_price_to_display( $product );
                                $simple_price = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_wc_price_html( $simple_price, $product );
                                if ( $product->is_on_sale() ) {
                                    $regular_price = wc_get_price_to_display( $product, array(
                                        'price' => $product->get_regular_price(),
                                    ) );
                                    $regular_price = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_wc_price_html( $regular_price, $product );
                                    $price = wc_format_sale_price( $regular_price, $simple_price ) . $product->get_price_suffix();
                                } else {
                                    $price = $simple_price . $product->get_price_suffix();
                                }
                            }
                            remove_filter( 'woocommerce_get_price_html', 'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_get_price_html', PHP_INT_MAX );
                            $price = apply_filters( 'woocommerce_get_price_html', $price, $product );
                            add_filter(
                                'woocommerce_get_price_html',
                                'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_get_price_html',
                                PHP_INT_MAX,
                                2
                            );
                            return $price;
                        }

                        function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_get_price_html_WC_Product_Variable(  $price, $product  ) {
                            $prices = $product->get_variation_prices( true );
                            if ( empty( $prices['price'] ) ) {
                                $price = apply_filters( 'woocommerce_variable_empty_price_html', '', $product );
                            } else {
                                $min_price = current( $prices['price'] );
                                $max_price = end( $prices['price'] );
                                $min_reg_price = current( $prices['regular_price'] );
                                $max_reg_price = end( $prices['regular_price'] );
                                $min_price_display = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_wc_price_html( $min_price, $product );
                                if ( $min_price !== $max_price ) {
                                    $max_price_display = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_wc_price_html( $max_price, $product );
                                    $price = wc_format_price_range( $min_price_display, $max_price_display );
                                } elseif ( $product->is_on_sale() && $min_reg_price === $max_reg_price ) {
                                    $max_reg_price_display = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_wc_price_html( $max_reg_price, $product );
                                    $price = wc_format_sale_price( $max_reg_price_display, $min_price_display );
                                } else {
                                    $price = $min_price_display;
                                }
                                $price = apply_filters( 'woocommerce_variable_price_html', $price . $product->get_price_suffix(), $product );
                            }
                            remove_filter( 'woocommerce_get_price_html', 'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_get_price_html', PHP_INT_MAX );
                            $price = apply_filters( 'woocommerce_get_price_html', $price, $product );
                            add_filter(
                                'woocommerce_get_price_html',
                                'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_get_price_html',
                                PHP_INT_MAX,
                                2
                            );
                            return $price;
                        }

                        function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_get_price_html_WC_Product_Grouped(  $price, $product  ) {
                            $tax_display_mode = get_option( 'woocommerce_tax_display_shop' );
                            $child_prices = array();
                            $children = array_filter( array_map( 'wc_get_product', $product->get_children() ), 'wc_products_array_filter_visible_grouped' );
                            foreach ( $children as $child ) {
                                if ( '' !== $child->get_price() ) {
                                    $child_prices[] = ( 'incl' === $tax_display_mode ? wc_get_price_including_tax( $child ) : wc_get_price_excluding_tax( $child ) );
                                }
                            }
                            if ( !empty( $child_prices ) ) {
                                $min_price = min( $child_prices );
                                $max_price = max( $child_prices );
                            } else {
                                $min_price = '';
                                $max_price = '';
                            }
                            if ( '' !== $min_price ) {
                                $min_price_display = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_wc_price_html( $min_price, $product );
                                if ( $min_price !== $max_price ) {
                                    $max_price_display = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_wc_price_html( $max_price, $product );
                                    $price = wc_format_price_range( $min_price_display, $max_price_display );
                                } else {
                                    $price = $min_price_display;
                                }
                                $is_free = 0 === $min_price && 0 === $max_price;
                                if ( $is_free ) {
                                    $price = apply_filters( 'woocommerce_grouped_free_price_html', __( 'Free!', 'woocommerce' ), $product );
                                } else {
                                    $price = apply_filters(
                                        'woocommerce_grouped_price_html',
                                        $price . $product->get_price_suffix(),
                                        $product,
                                        $child_prices
                                    );
                                }
                            } else {
                                $price = apply_filters( 'woocommerce_grouped_empty_price_html', '', $product );
                            }
                            remove_filter( 'woocommerce_get_price_html', 'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_get_price_html', PHP_INT_MAX );
                            $price = apply_filters( 'woocommerce_get_price_html', $price, $product );
                            add_filter(
                                'woocommerce_get_price_html',
                                'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_get_price_html',
                                PHP_INT_MAX,
                                2
                            );
                            return $price;
                        }

                        add_filter(
                            'woocommerce_cart_product_subtotal',
                            'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_cart_product_subtotal',
                            PHP_INT_MAX,
                            4
                        );
                        function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_cart_product_subtotal(
                            $product_subtotal,
                            $product,
                            $quantity,
                            $cart
                        ) {
                            if ( !_CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_is_cryptocurrency( $product->get_id() ) ) {
                                return $product_subtotal;
                            }
                            $price = $product->get_price();
                            if ( $product->is_taxable() ) {
                                if ( $cart->display_prices_including_tax() ) {
                                    $row_price = wc_get_price_including_tax( $product, array(
                                        'qty' => $quantity,
                                    ) );
                                    $product_subtotal = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_wc_price_html( $row_price, $product );
                                    if ( !wc_prices_include_tax() && $cart->get_subtotal_tax() > 0 ) {
                                        $product_subtotal .= ' <small class="tax_label">' . WC()->countries->inc_tax_or_vat() . '</small>';
                                    }
                                } else {
                                    $row_price = wc_get_price_excluding_tax( $product, array(
                                        'qty' => $quantity,
                                    ) );
                                    $product_subtotal = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_wc_price_html( $row_price, $product );
                                    if ( wc_prices_include_tax() && $cart->get_subtotal_tax() > 0 ) {
                                        $product_subtotal .= ' <small class="tax_label">' . WC()->countries->ex_tax_or_vat() . '</small>';
                                    }
                                }
                            } else {
                                $row_price = $price * $quantity;
                                $product_subtotal = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_wc_price_html( $row_price, $product );
                            }
                            remove_filter( 'woocommerce_cart_product_subtotal', 'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_cart_product_subtotal', PHP_INT_MAX );
                            $product_subtotal = apply_filters(
                                'woocommerce_cart_product_subtotal',
                                $product_subtotal,
                                $product,
                                $quantity,
                                $cart
                            );
                            add_filter(
                                'woocommerce_cart_product_subtotal',
                                'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_cart_product_subtotal',
                                PHP_INT_MAX,
                                4
                            );
                            return $product_subtotal;
                        }

                        add_filter(
                            'woocommerce_cart_subtotal',
                            'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_cart_subtotal',
                            PHP_INT_MAX,
                            3
                        );
                        // bool $compound whether to include compound taxes.
                        function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_cart_subtotal(  $cart_subtotal, $compound, $cart  ) {
                            $product = null;
                            foreach ( $cart->get_cart() as $cart_item_key => $p ) {
                                if ( _CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_is_cryptocurrency( $p['product_id'] ) ) {
                                    $product = wc_get_product( $p['product_id'] );
                                    break;
                                }
                            }
                            if ( is_null( $product ) ) {
                                return $cart_subtotal;
                            }
                            /**
                             * If the cart has compound tax, we want to show the subtotal as cart + shipping + non-compound taxes (after discount).
                             */
                            if ( $compound ) {
                                $cart_subtotal = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_wc_price_html( $cart->get_cart_contents_total() + $cart->get_shipping_total() + $cart->get_taxes_total( false, false ), $product );
                            } elseif ( $cart->display_prices_including_tax() ) {
                                $cart_subtotal = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_wc_price_html( $cart->get_subtotal() + $cart->get_subtotal_tax(), $product );
                                if ( $cart->get_subtotal_tax() > 0 && !wc_prices_include_tax() ) {
                                    $cart_subtotal .= ' <small class="tax_label">' . WC()->countries->inc_tax_or_vat() . '</small>';
                                }
                            } else {
                                $cart_subtotal = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_wc_price_html( $cart->get_subtotal(), $product );
                                if ( $cart->get_subtotal_tax() > 0 && wc_prices_include_tax() ) {
                                    $cart_subtotal .= ' <small class="tax_label">' . WC()->countries->ex_tax_or_vat() . '</small>';
                                }
                            }
                            remove_filter( 'woocommerce_cart_subtotal', 'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_cart_subtotal', PHP_INT_MAX );
                            $cart_subtotal = apply_filters(
                                'woocommerce_cart_subtotal',
                                $cart_subtotal,
                                $compound,
                                $cart
                            );
                            add_filter(
                                'woocommerce_cart_subtotal',
                                'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_cart_subtotal',
                                PHP_INT_MAX,
                                3
                            );
                            return $cart_subtotal;
                        }

                        add_filter(
                            'woocommerce_cart_contents_total',
                            'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_cart_contents_total',
                            PHP_INT_MAX,
                            1
                        );
                        // bool $compound whether to include compound taxes.
                        function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_cart_contents_total(  $cart_total  ) {
                            if ( is_null( WC()->cart ) ) {
                                return $cart_total;
                            }
                            $cart = WC()->cart;
                            $product = null;
                            foreach ( WC()->cart->get_cart() as $cart_item_key => $p ) {
                                if ( _CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_is_cryptocurrency( $p['product_id'] ) ) {
                                    $product = wc_get_product( $p['product_id'] );
                                    break;
                                }
                            }
                            if ( is_null( $product ) ) {
                                return $cart_total;
                            }
                            $total_raw = ( wc_prices_include_tax() ? $cart->get_cart_contents_total() + $cart->get_cart_contents_tax() : $cart->get_cart_contents_total() );
                            $cart_total = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_wc_price_html( $total_raw, $product );
                            remove_filter( 'woocommerce_cart_contents_total', 'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_cart_contents_total', PHP_INT_MAX );
                            $cart_total = apply_filters( 'woocommerce_cart_contents_total', $cart_total );
                            add_filter(
                                'woocommerce_cart_contents_total',
                                'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_cart_contents_total',
                                PHP_INT_MAX,
                                1
                            );
                            return $cart_total;
                        }

                        add_filter(
                            'woocommerce_cart_total_ex_tax',
                            'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_cart_total_ex_tax',
                            PHP_INT_MAX,
                            1
                        );
                        // bool $compound whether to include compound taxes.
                        function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_cart_total_ex_tax(  $cart_total  ) {
                            if ( is_null( WC()->cart ) ) {
                                return $cart_total;
                            }
                            $cart = WC()->cart;
                            $product = null;
                            foreach ( WC()->cart->get_cart() as $cart_item_key => $p ) {
                                if ( _CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_is_cryptocurrency( $p['product_id'] ) ) {
                                    $product = wc_get_product( $p['product_id'] );
                                    break;
                                }
                            }
                            if ( is_null( $product ) ) {
                                return $cart_total;
                            }
                            $total_raw = max( 0, $cart->get_total( 'edit' ) - $cart->get_total_tax() );
                            $cart_total = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_wc_price_html( $total_raw, $product );
                            remove_filter( 'woocommerce_cart_total_ex_tax', 'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_cart_total_ex_tax', PHP_INT_MAX );
                            $cart_total = apply_filters( 'woocommerce_cart_total_ex_tax', $cart_total );
                            add_filter(
                                'woocommerce_cart_total_ex_tax',
                                'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_cart_total_ex_tax',
                                PHP_INT_MAX,
                                1
                            );
                            return $cart_total;
                        }

                        add_filter(
                            'woocommerce_cart_shipping_total',
                            'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_cart_shipping_total',
                            PHP_INT_MAX,
                            2
                        );
                        // bool $compound whether to include compound taxes.
                        function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_cart_shipping_total(  $total, $cart  ) {
                            $product = null;
                            foreach ( $cart->get_cart() as $cart_item_key => $p ) {
                                if ( _CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_is_cryptocurrency( $p['product_id'] ) ) {
                                    $product = wc_get_product( $p['product_id'] );
                                    break;
                                }
                            }
                            if ( is_null( $product ) ) {
                                return $total;
                            }
                            // Default total assumes Free shipping.
                            $total = __( 'Free!', 'woocommerce' );
                            if ( 0 < $cart->get_shipping_total() ) {
                                if ( $cart->display_prices_including_tax() ) {
                                    $total_raw = $cart->shipping_total + $cart->shipping_tax_total;
                                    $total = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_wc_price_html( $total_raw, $product );
                                    if ( $cart->shipping_tax_total > 0 && !wc_prices_include_tax() ) {
                                        $total .= ' <small class="tax_label">' . WC()->countries->inc_tax_or_vat() . '</small>';
                                    }
                                } else {
                                    $total_raw = $cart->shipping_total;
                                    $total = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_wc_price_html( $total_raw, $product );
                                    if ( $cart->shipping_tax_total > 0 && wc_prices_include_tax() ) {
                                        $total .= ' <small class="tax_label">' . WC()->countries->ex_tax_or_vat() . '</small>';
                                    }
                                }
                            }
                            remove_filter( 'woocommerce_cart_shipping_total', 'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_cart_shipping_total', PHP_INT_MAX );
                            $total = apply_filters( 'woocommerce_cart_shipping_total', $total, $cart );
                            add_filter(
                                'woocommerce_cart_shipping_total',
                                'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_cart_shipping_total',
                                PHP_INT_MAX,
                                2
                            );
                            return $total;
                        }

                        add_filter(
                            'woocommerce_cart_tax_totals',
                            'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_cart_tax_totals',
                            PHP_INT_MAX,
                            2
                        );
                        // bool $compound whether to include compound taxes.
                        function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_cart_tax_totals(  $tax_totals, $cart  ) {
                            $product = null;
                            foreach ( $cart->get_cart() as $cart_item_key => $p ) {
                                if ( _CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_is_cryptocurrency( $p['product_id'] ) ) {
                                    $product = wc_get_product( $p['product_id'] );
                                    break;
                                }
                            }
                            if ( is_null( $product ) ) {
                                return $tax_totals;
                            }
                            $shipping_taxes = $cart->get_shipping_taxes();
                            // Shipping taxes are rounded differently, so we will subtract from all taxes, then round and then add them back.
                            $taxes = $cart->get_taxes();
                            $tax_totals = array();
                            foreach ( $taxes as $key => $tax ) {
                                $code = WC_Tax::get_rate_code( $key );
                                if ( $code || apply_filters( 'woocommerce_cart_remove_taxes_zero_rate_id', 'zero-rated' ) === $key ) {
                                    if ( !isset( $tax_totals[$code] ) ) {
                                        $tax_totals[$code] = new stdClass();
                                        $tax_totals[$code]->amount = 0;
                                    }
                                    $tax_totals[$code]->tax_rate_id = $key;
                                    $tax_totals[$code]->is_compound = WC_Tax::is_compound( $key );
                                    $tax_totals[$code]->label = WC_Tax::get_rate_label( $key );
                                    if ( isset( $shipping_taxes[$key] ) ) {
                                        $tax -= $shipping_taxes[$key];
                                        $tax = wc_round_tax_total( $tax );
                                        $tax += round( ( is_numeric( $shipping_taxes[$key] ) ? $shipping_taxes[$key] : floatval( $shipping_taxes[$key] ) ), wc_get_price_decimals() );
                                        unset($shipping_taxes[$key]);
                                    }
                                    $tax_totals[$code]->amount += wc_round_tax_total( $tax );
                                    $tax_totals[$code]->formatted_amount = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_wc_price_html( $tax_totals[$code]->amount, $product );
                                }
                            }
                            if ( apply_filters( 'woocommerce_cart_hide_zero_taxes', true ) ) {
                                $amounts = array_filter( wp_list_pluck( $tax_totals, 'amount' ) );
                                $tax_totals = array_intersect_key( $tax_totals, $amounts );
                            }
                            remove_filter( 'woocommerce_cart_tax_totals', 'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_cart_tax_totals', PHP_INT_MAX );
                            $tax_totals = apply_filters( 'woocommerce_cart_tax_totals', $tax_totals, $cart );
                            add_filter(
                                'woocommerce_cart_tax_totals',
                                'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_cart_tax_totals',
                                PHP_INT_MAX,
                                2
                            );
                            return $tax_totals;
                        }

                        add_filter(
                            'woocommerce_cart_total',
                            'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_cart_total',
                            PHP_INT_MAX,
                            1
                        );
                        function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_cart_total(  $cart_total  ) {
                            if ( is_null( WC()->cart ) ) {
                                return $cart_total;
                            }
                            $cart = WC()->cart;
                            $product = null;
                            foreach ( WC()->cart->get_cart() as $cart_item_key => $p ) {
                                if ( _CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_is_cryptocurrency( $p['product_id'] ) ) {
                                    $product = wc_get_product( $p['product_id'] );
                                    break;
                                }
                            }
                            if ( is_null( $product ) ) {
                                return $cart_total;
                            }
                            $totals = WC()->cart->get_totals();
                            $total = ( isset( $totals['total'] ) ? $totals['total'] : 0 );
                            $total = apply_filters( 'woocommerce_cart_get_total', $total );
                            $total_display = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_wc_price_html( $total, $product );
                            remove_filter( 'woocommerce_cart_total', 'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_cart_total', PHP_INT_MAX );
                            $total_return = apply_filters( 'woocommerce_cart_total', $total_display );
                            add_filter(
                                'woocommerce_cart_total',
                                'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_cart_total',
                                PHP_INT_MAX,
                                1
                            );
                            return $total_return;
                        }

                        add_filter(
                            'woocommerce_cart_total_discount',
                            'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_cart_total_discount',
                            PHP_INT_MAX,
                            2
                        );
                        function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_cart_total_discount(  $cart_total, $cart  ) {
                            $product = null;
                            foreach ( $cart->get_cart() as $cart_item_key => $p ) {
                                if ( _CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_is_cryptocurrency( $p['product_id'] ) ) {
                                    $product = wc_get_product( $p['product_id'] );
                                    break;
                                }
                            }
                            if ( is_null( $product ) ) {
                                return $cart_total;
                            }
                            $total_display = ( $cart->get_discount_total() ? CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_wc_price_html( $cart->get_discount_total(), $product ) : false );
                            remove_filter( 'woocommerce_cart_total_discount', 'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_cart_total_discount', PHP_INT_MAX );
                            $total_return = apply_filters( 'woocommerce_cart_total_discount', $total_display );
                            add_filter(
                                'woocommerce_cart_total_discount',
                                'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_cart_total_discount',
                                PHP_INT_MAX,
                                2
                            );
                            return $total_return;
                        }

                        add_filter(
                            'woocommerce_cart_shipping_method_full_label',
                            'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_cart_shipping_method_full_label',
                            PHP_INT_MAX,
                            2
                        );
                        // bool $compound whether to include compound taxes.
                        function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_cart_shipping_method_full_label(  $label, $method  ) {
                            if ( is_null( WC()->cart ) ) {
                                return $label;
                            }
                            $cart = WC()->cart;
                            $product = null;
                            foreach ( WC()->cart->get_cart() as $cart_item_key => $p ) {
                                if ( _CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_is_cryptocurrency( $p['product_id'] ) ) {
                                    $product = wc_get_product( $p['product_id'] );
                                    break;
                                }
                            }
                            if ( is_null( $product ) ) {
                                return $label;
                            }
                            $label = $method->get_label();
                            $has_cost = 0 < $method->cost;
                            $hide_cost = !$has_cost && in_array( $method->get_method_id(), array('free_shipping', 'local_pickup'), true );
                            if ( $has_cost && !$hide_cost ) {
                                if ( WC()->cart->display_prices_including_tax() ) {
                                    $cost_display = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_wc_price_html( $method->cost + $method->get_shipping_tax(), $product );
                                    $label .= ': ' . $cost_display;
                                    if ( $method->get_shipping_tax() > 0 && !wc_prices_include_tax() ) {
                                        $label .= ' <small class="tax_label">' . WC()->countries->inc_tax_or_vat() . '</small>';
                                    }
                                } else {
                                    $cost_display = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_wc_price_html( $method->cost, $product );
                                    $label .= ': ' . $cost_display;
                                    if ( $method->get_shipping_tax() > 0 && wc_prices_include_tax() ) {
                                        $label .= ' <small class="tax_label">' . WC()->countries->ex_tax_or_vat() . '</small>';
                                    }
                                }
                            }
                            remove_filter( 'woocommerce_cart_shipping_method_full_label', 'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_cart_shipping_method_full_label', PHP_INT_MAX );
                            $label = apply_filters( 'woocommerce_cart_shipping_method_full_label', $label, $method );
                            add_filter(
                                'woocommerce_cart_shipping_method_full_label',
                                'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_cart_shipping_method_full_label',
                                PHP_INT_MAX,
                                2
                            );
                            return $label;
                        }

                        add_filter(
                            'woocommerce_get_formatted_order_total',
                            'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_get_formatted_order_total',
                            PHP_INT_MAX,
                            4
                        );
                        function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_get_formatted_order_total(
                            $formatted_total,
                            $order,
                            $tax_display,
                            $display_refunded
                        ) {
                            $product = null;
                            foreach ( $order->get_items() as $item ) {
                                if ( _CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_is_cryptocurrency( $item->get_product_id() ) ) {
                                    $product = wc_get_product( $item->get_product_id() );
                                    break;
                                }
                            }
                            if ( is_null( $product ) ) {
                                return $formatted_total;
                            }
                            $formatted_total = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_wc_price_html( $order->get_total(), $product, array(
                                'currency' => $order->get_currency(),
                            ) );
                            $order_total = $order->get_total();
                            $total_refunded = $order->get_total_refunded();
                            $tax_string = '';
                            // Tax for inclusive prices.
                            if ( wc_tax_enabled() && 'incl' === $tax_display ) {
                                $tax_string_array = array();
                                $tax_totals = $order->get_tax_totals();
                                if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) {
                                    foreach ( $tax_totals as $code => $tax ) {
                                        $tax_amount = ( $total_refunded && $display_refunded ? CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_wc_price_html( WC_Tax::round( $tax->amount - $order->get_total_tax_refunded_by_rate_id( $tax->rate_id ) ), $product, array(
                                            'currency' => $order->get_currency(),
                                        ) ) : CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_wc_price_html( $tax->amount, $product, array(
                                            'currency' => $order->get_currency(),
                                        ) ) );
                                        $tax_string_array[] = sprintf( '%s %s', $tax_amount, $tax->label );
                                    }
                                } elseif ( !empty( $tax_totals ) ) {
                                    $tax_amount = ( $total_refunded && $display_refunded ? $order->get_total_tax() - $order->get_total_tax_refunded() : $order->get_total_tax() );
                                    $tax_string_array[] = sprintf( '%s %s', CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_wc_price_html( $tax_amount, $product, array(
                                        'currency' => $order->get_currency(),
                                    ) ), WC()->countries->tax_or_vat() );
                                }
                                if ( !empty( $tax_string_array ) ) {
                                    /* translators: %s: taxes */
                                    $tax_string = ' <small class="includes_tax">' . sprintf( __( '(includes %s)', 'woocommerce' ), implode( ', ', $tax_string_array ) ) . '</small>';
                                }
                            }
                            if ( $total_refunded && $display_refunded ) {
                                $formatted_total = '<del aria-hidden="true">' . wp_strip_all_tags( $formatted_total ) . '</del> <ins>' . CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_wc_price_html( $order_total - $total_refunded, $product, array(
                                    'currency' => $order->get_currency(),
                                ) ) . $tax_string . '</ins>';
                            } else {
                                $formatted_total .= $tax_string;
                            }
                            remove_filter( 'woocommerce_get_formatted_order_total', 'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_get_formatted_order_total', PHP_INT_MAX );
                            $formatted_total = apply_filters(
                                'woocommerce_get_formatted_order_total',
                                $formatted_total,
                                $order,
                                $tax_display,
                                $display_refunded
                            );
                            add_filter(
                                'woocommerce_get_formatted_order_total',
                                'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_get_formatted_order_total',
                                PHP_INT_MAX,
                                4
                            );
                            return $formatted_total;
                        }

                        add_filter(
                            'woocommerce_order_formatted_line_subtotal',
                            'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_order_formatted_line_subtotal',
                            PHP_INT_MAX,
                            3
                        );
                        function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_order_formatted_line_subtotal(  $subtotal, $item, $order  ) {
                            $product = null;
                            if ( _CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_is_cryptocurrency( $item->get_product_id() ) ) {
                                $product = wc_get_product( $item->get_product_id() );
                            }
                            if ( is_null( $product ) ) {
                                return $subtotal;
                            }
                            $tax_display = get_option( 'woocommerce_tax_display_cart' );
                            if ( 'excl' === $tax_display ) {
                                $ex_tax_label = ( $order->get_prices_include_tax() ? 1 : 0 );
                                $subtotal = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_wc_price_html( $order->get_line_subtotal( $item ), $product, array(
                                    'ex_tax_label' => $ex_tax_label,
                                    'currency'     => $order->get_currency(),
                                ) );
                            } else {
                                $subtotal = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_wc_price_html( $order->get_line_subtotal( $item, true ), $product, array(
                                    'currency' => $order->get_currency(),
                                ) );
                            }
                            remove_filter( 'woocommerce_order_formatted_line_subtotal', 'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_order_formatted_line_subtotal', PHP_INT_MAX );
                            $subtotal = apply_filters(
                                'woocommerce_order_formatted_line_subtotal',
                                $subtotal,
                                $item,
                                $order
                            );
                            add_filter(
                                'woocommerce_order_formatted_line_subtotal',
                                'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_order_formatted_line_subtotal',
                                PHP_INT_MAX,
                                3
                            );
                            return $subtotal;
                        }

                        add_filter(
                            'woocommerce_order_subtotal_to_display',
                            'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_order_subtotal_to_display',
                            PHP_INT_MAX,
                            3
                        );
                        function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_order_subtotal_to_display(  $subtotal, $compound, $order  ) {
                            $product = null;
                            foreach ( $order->get_items() as $item ) {
                                if ( _CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_is_cryptocurrency( $item->get_product_id() ) ) {
                                    $product = wc_get_product( $item->get_product_id() );
                                    break;
                                }
                            }
                            if ( is_null( $product ) ) {
                                return $subtotal;
                            }
                            $tax_display = get_option( 'woocommerce_tax_display_cart' );
                            // $subtotal    = $order->get_cart_subtotal_for_order();
                            $subtotal = wc_remove_number_precision( $order->get_rounded_items_total( 
                                // $order->get_values_for_total( 'subtotal' )
                                array_map( function ( $item ) {
                                    return wc_add_number_precision( $item['subtotal'], false );
                                }, array_values( $order->get_items() ) )
                             ) );
                            if ( !$compound ) {
                                if ( 'incl' === $tax_display ) {
                                    $subtotal_taxes = 0;
                                    $round_at_subtotal = get_option( 'woocommerce_tax_round_at_subtotal' );
                                    $in_cents = false;
                                    foreach ( $order->get_items() as $item ) {
                                        // $subtotal_taxes += self::round_line_tax( $item->get_subtotal_tax(), false );
                                        if ( 'yes' !== $round_at_subtotal ) {
                                            $subtotal_taxes += wc_round_tax_total( $item->get_subtotal_tax(), ( $in_cents ? 0 : null ) );
                                        } else {
                                            $subtotal_taxes += $item->get_subtotal_tax();
                                        }
                                    }
                                    $subtotal += wc_round_tax_total( $subtotal_taxes );
                                }
                                $subtotal = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_wc_price_html( $subtotal, $product, array(
                                    'currency' => $order->get_currency(),
                                ) );
                                if ( 'excl' === $tax_display && $order->get_prices_include_tax() && wc_tax_enabled() ) {
                                    $subtotal .= ' <small class="tax_label">' . WC()->countries->ex_tax_or_vat() . '</small>';
                                }
                            } else {
                                if ( 'incl' === $tax_display ) {
                                    return '';
                                }
                                // Add Shipping Costs.
                                $subtotal += $order->get_shipping_total();
                                // Remove non-compound taxes.
                                foreach ( $order->get_taxes() as $tax ) {
                                    if ( $tax->is_compound() ) {
                                        continue;
                                    }
                                    $subtotal = $subtotal + $tax->get_tax_total() + $tax->get_shipping_tax_total();
                                }
                                // Remove discounts.
                                $subtotal = $subtotal - $order->get_total_discount();
                                $subtotal = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_wc_price_html( $subtotal, $product, array(
                                    'currency' => $order->get_currency(),
                                ) );
                            }
                            remove_filter( 'woocommerce_order_subtotal_to_display', 'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_order_subtotal_to_display', PHP_INT_MAX );
                            $subtotal = apply_filters(
                                'woocommerce_order_subtotal_to_display',
                                $subtotal,
                                $item,
                                $order
                            );
                            add_filter(
                                'woocommerce_order_subtotal_to_display',
                                'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_order_subtotal_to_display',
                                PHP_INT_MAX,
                                3
                            );
                            return $subtotal;
                        }

                        add_filter(
                            'woocommerce_order_discount_to_display',
                            'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_order_discount_to_display',
                            PHP_INT_MAX,
                            2
                        );
                        function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_order_discount_to_display(  $discount, $order  ) {
                            $product = null;
                            foreach ( $order->get_items() as $item ) {
                                if ( _CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_is_cryptocurrency( $item->get_product_id() ) ) {
                                    $product = wc_get_product( $item->get_product_id() );
                                    break;
                                }
                            }
                            if ( is_null( $product ) ) {
                                return $discount;
                            }
                            $tax_display = get_option( 'woocommerce_tax_display_cart' );
                            $discount = $order->get_total_discount( 'excl' === $tax_display && 'excl' === get_option( 'woocommerce_tax_display_cart' ) );
                            $discount = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_wc_price_html( $discount, $product, array(
                                'currency' => $order->get_currency(),
                            ) );
                            remove_filter( 'woocommerce_order_discount_to_display', 'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_order_discount_to_display', PHP_INT_MAX );
                            $discount = apply_filters( 'woocommerce_order_discount_to_display', $discount, $order );
                            add_filter(
                                'woocommerce_order_discount_to_display',
                                'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_order_discount_to_display',
                                PHP_INT_MAX,
                                2
                            );
                            return $discount;
                        }

                        add_filter(
                            'woocommerce_order_shipping_to_display',
                            'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_order_shipping_to_display',
                            PHP_INT_MAX,
                            3
                        );
                        function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_order_shipping_to_display(  $shipping, $order, $tax_display  ) {
                            $product = null;
                            foreach ( $order->get_items() as $item ) {
                                if ( _CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_is_cryptocurrency( $item->get_product_id() ) ) {
                                    $product = wc_get_product( $item->get_product_id() );
                                    break;
                                }
                            }
                            if ( is_null( $product ) ) {
                                return $shipping;
                            }
                            $tax_display = ( $tax_display ? $tax_display : get_option( 'woocommerce_tax_display_cart' ) );
                            if ( 0 < abs( (float) $order->get_shipping_total() ) ) {
                                if ( 'excl' === $tax_display ) {
                                    // Show shipping excluding tax.
                                    $shipping = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_wc_price_html( $order->get_shipping_total(), $product, array(
                                        'currency' => $order->get_currency(),
                                    ) );
                                    if ( (float) $order->get_shipping_tax() > 0 && $order->get_prices_include_tax() ) {
                                        $shipping .= apply_filters(
                                            'woocommerce_order_shipping_to_display_tax_label',
                                            '&nbsp;<small class="tax_label">' . WC()->countries->ex_tax_or_vat() . '</small>',
                                            $order,
                                            $tax_display
                                        );
                                    }
                                } else {
                                    // Show shipping including tax.
                                    $shipping = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_wc_price_html( $order->get_shipping_total() + $order->get_shipping_tax(), $product, array(
                                        'currency' => $order->get_currency(),
                                    ) );
                                    if ( (float) $order->get_shipping_tax() > 0 && !$order->get_prices_include_tax() ) {
                                        $shipping .= apply_filters(
                                            'woocommerce_order_shipping_to_display_tax_label',
                                            '&nbsp;<small class="tax_label">' . WC()->countries->inc_tax_or_vat() . '</small>',
                                            $order,
                                            $tax_display
                                        );
                                    }
                                }
                                /* translators: %s: method */
                                $shipping .= apply_filters( 'woocommerce_order_shipping_to_display_shipped_via', '&nbsp;<small class="shipped_via">' . sprintf( __( 'via %s', 'woocommerce' ), $order->get_shipping_method() ) . '</small>', $order );
                            } elseif ( $order->get_shipping_method() ) {
                                $shipping = $order->get_shipping_method();
                            } else {
                                $shipping = __( 'Free!', 'woocommerce' );
                            }
                            remove_filter( 'woocommerce_order_shipping_to_display', 'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_order_shipping_to_display', PHP_INT_MAX );
                            $shipping = apply_filters(
                                'woocommerce_order_shipping_to_display',
                                $shipping,
                                $order,
                                $tax_display
                            );
                            add_filter(
                                'woocommerce_order_shipping_to_display',
                                'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_order_shipping_to_display',
                                PHP_INT_MAX,
                                3
                            );
                            return $shipping;
                        }

                        add_filter(
                            'woocommerce_get_order_item_totals',
                            'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_get_order_item_totals',
                            PHP_INT_MAX,
                            3
                        );
                        function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_get_order_item_totals(  $total_rows, $order, $tax_display  ) {
                            $product = null;
                            foreach ( $order->get_items() as $item ) {
                                if ( _CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_is_cryptocurrency( $item->get_product_id() ) ) {
                                    $product = wc_get_product( $item->get_product_id() );
                                    break;
                                }
                            }
                            if ( is_null( $product ) ) {
                                return $total_rows;
                            }
                            // $order->add_order_item_totals_fee_rows( $total_rows, $tax_display );
                            $fees = $order->get_fees();
                            if ( $fees ) {
                                foreach ( $fees as $id => $fee ) {
                                    if ( apply_filters( 'woocommerce_get_order_item_totals_excl_free_fees', empty( $fee['line_total'] ) && empty( $fee['line_tax'] ), $id ) ) {
                                        continue;
                                    }
                                    $total_rows['fee_' . $fee->get_id()] = array(
                                        'label' => $fee->get_name() . ':',
                                        'value' => CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_wc_price_html( ( 'excl' === $tax_display ? $fee->get_total() : $fee->get_total() + $fee->get_total_tax() ), $product, array(
                                            'currency' => $order->get_currency(),
                                        ) ),
                                    );
                                }
                            }
                            // $order->add_order_item_totals_tax_rows( $total_rows, $tax_display );
                            if ( 'excl' === $tax_display && wc_tax_enabled() ) {
                                if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) {
                                    foreach ( $order->get_tax_totals() as $code => $tax ) {
                                        $total_rows[sanitize_title( $code )] = array(
                                            'label' => $tax->label . ':',
                                            'value' => CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_wc_price_html( $tax->amount, $product, array(
                                                'currency' => $order->get_currency(),
                                            ) ),
                                        );
                                    }
                                } else {
                                    $total_rows['tax'] = array(
                                        'label' => WC()->countries->tax_or_vat() . ':',
                                        'value' => CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_wc_price_html( $order->get_total_tax(), $product, array(
                                            'currency' => $order->get_currency(),
                                        ) ),
                                    );
                                }
                            }
                            // $order->add_order_item_totals_refund_rows( $total_rows, $tax_display );
                            $refunds = $order->get_refunds();
                            if ( $refunds ) {
                                foreach ( $refunds as $id => $refund ) {
                                    $total_rows['refund_' . $id] = array(
                                        'label' => ( $refund->get_reason() ? $refund->get_reason() : __( 'Refund', 'woocommerce' ) . ':' ),
                                        'value' => CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_wc_price_html( '-' . $refund->get_amount(), $product, array(
                                            'currency' => $order->get_currency(),
                                        ) ),
                                    );
                                }
                            }
                            return $total_rows;
                        }

                        function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_wc_price_html(  $price, $product, $args = []  ) {
                            global 
                                $wp,
                                $woocommerce,
                                $post,
                                $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_product
                            ;
                            return wc_price( $price );
                        }

                        /**
                         * If option not found in a product, look it in a _POST
                         */
                        function _CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_post_meta(  $product_id, $option_name, $single = true  ) {
                            $option = '';
                            if ( isset( $_POST[$option_name] ) ) {
                                $option = $_POST[$option_name];
                            }
                            if ( empty( $option ) ) {
                                $option = get_post_meta( $product_id, $option_name, $single );
                            }
                            return $option;
                        }

                        /**
                         * Save option in a _POST also
                         */
                        function _CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_update_post_meta(  $product_id, $option_name, $option_value  ) {
                            update_post_meta( $product_id, $option_name, $option_value );
                            if ( isset( $_POST ) ) {
                                $_POST[$option_name] = $option_value;
                            }
                        }

                        function _CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_wc_price_html_ex(
                            $return,
                            $price,
                            $args,
                            $unformatted_price,
                            $product
                        ) {
                            global 
                                $wp,
                                $woocommerce,
                                $post,
                                $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_product
                            ;
                            return $return;
                        }

                        /**
                         * Add a custom product tab.
                         */
                        function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_custom_product_tabs(  $tabs  ) {
                            $tabs['cryptocurrency'] = array(
                                'label'  => __( 'Cryptocurrency', 'cryptocurrency-product-for-woocommerce' ),
                                'target' => 'cryptocurrency_product_data',
                                'class'  => array('show_if_cryptocurrency_product_for_woocommerce_cryptocurrency_product_type', 'show_if_variable_cryptocurrency_product_for_woocommerce_cryptocurrency_product_type', 'show_if_auction_cryptocurrency_product_for_woocommerce_cryptocurrency_product_type'),
                            );
                            //    CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log('tabs=' . print_r($tabs, true));
                            return $tabs;
                        }

                        add_filter( 'woocommerce_product_data_tabs', 'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_custom_product_tabs' );
                        // define the woocommerce_product_options_general_product_data callback
                        function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_product_options_general_product_data_aux(  $post_id  ) {
                            global $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_plugin_dir;
                            $settings = [];
                            return $settings;
                        }

                        // define the woocommerce_product_options_general_product_data callback
                        function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_product_options_general_product_data() {
                            global $post;
                            $post_id = $post->ID;
                            $product = wc_get_product( $post_id );
                            if ( !$product ) {
                                CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "woocommerce_product_options_general_product_data({$post_id}) not a product" );
                                return;
                            }
                            //    $display = 'block';
                            //    if (!_CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_is_cryptocurrency( $product->get_id() )) {
                            //        $display = 'none';
                            //    }
                            $upgrade_message = '<p class="description">' . sprintf(
                                __( '%1$sUpgrade Now!%2$s to enable "%3$s" feature.', 'cryptocurrency-product-for-woocommerce' ),
                                '<a href="' . cryptocurrency_product_for_woocommerce_freemius_init()->get_upgrade_url() . '" target="_blank">',
                                '</a>',
                                __( 'The dynamic price source', 'cryptocurrency-product-for-woocommerce' )
                            ) . '</p>';
                            // if (cryptocurrency_product_for_woocommerce_freemius_init()->can_use_premium_code__premium_only()) {
                            ?>
                <div class="options_group cryptocurrency-product-for-woocommerce-settings-wrapper show_if_cryptocurrency_product_for_woocommerce_cryptocurrency_product_type show_if_variable_cryptocurrency_product_for_woocommerce_cryptocurrency_product_type show_if_auction_cryptocurrency_product_for_woocommerce_cryptocurrency_product_type">
                    <h3 class="cryptocurrency-product-for-woocommerce-settings-header" style="display: block;"><?php 
                            _e( 'Cryptocurrency Product Settings', 'cryptocurrency-product-for-woocommerce' );
                            ?></h3>
                    <div class="options_group">
                        <?php 
                            echo $upgrade_message;
                            ?>
                    </div>
                </div>
            <?php 
                            // }
                            CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_enqueue_scripts_();
                        }

                        // add the action
                        add_action( 'woocommerce_product_options_general_product_data', 'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_product_options_general_product_data' );
                        function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_display_options(  $settings  ) {
                            foreach ( $settings as $s ) {
                                switch ( $s['_cryptocurrency_product_setting_type'] ) {
                                    case 'text_input':
                                        woocommerce_wp_text_input( $s );
                                        break;
                                    case 'textarea_input':
                                        woocommerce_wp_textarea_input( $s );
                                        break;
                                    case 'checkbox':
                                        woocommerce_wp_checkbox( $s );
                                        break;
                                    case 'select':
                                        woocommerce_wp_select( $s );
                                        break;
                                    case 'hidden':
                                        woocommerce_wp_hidden_input( $s );
                                        break;
                                    default:
                                        throw new \Exception("Unknown _cryptocurrency_product_setting_type: " . $s['_cryptocurrency_product_setting_type']);
                                }
                            }
                        }

                        /**
                         * Contents of the cryptocurrency options product tab.
                         */
                        function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_options_product_tab_content(  $object_id = null  ) {
                            global $post;
                            $post_id = ( is_null( $object_id ) ? $post->ID : $object_id );
                            // CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log("CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_options_product_tab_content($object_id): $post_id");
                            // Get the selected value
                            $_select_cryptocurrency_option = _CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_post_meta( $post_id, '_select_cryptocurrency_option', true );
                            if ( empty( $_select_cryptocurrency_option ) ) {
                                $_select_cryptocurrency_option = '';
                            }
                            $_text_input_cryptocurrency_minimum_value = _CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_post_meta( $post_id, '_text_input_cryptocurrency_minimum_value', true );
                            if ( empty( $_text_input_cryptocurrency_minimum_value ) ) {
                                $_text_input_cryptocurrency_minimum_value = '';
                            }
                            $_text_input_cryptocurrency_step = _CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_post_meta( $post_id, '_text_input_cryptocurrency_step', true );
                            if ( empty( $_text_input_cryptocurrency_step ) ) {
                                $_text_input_cryptocurrency_step = '';
                            }
                            $options = [];
                            $options[''] = __( 'Select a value', 'woocommerce' );
                            // default value
                            if ( !CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_is_ether_product_type_disabled() ) {
                                $options['Ether'] = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getBlockchainCurrencyTickerName();
                            }
                            $options = apply_filters( 'cryptocurrency_product_for_woocommerce_get_product_symbols', $options );
                            $s = pow( 10, -18 );
                            $settings = [
                                //        [
                                //            'id'			=> '_text_input_cryptocurrency_data',
                                //            'label'			=> __( 'Token address', 'cryptocurrency-product-for-woocommerce' ),
                                //            'desc_tip'		=> 'true',
                                //            'description'	=> __( 'The ethereum address of the Token to sell', 'cryptocurrency-product-for-woocommerce' ),
                                //            'type' 			=> 'text',
                                //            'wrapper_class' => 'hidden',
                                //            '_cryptocurrency_product_setting_type' => 'text_input',
                                //        ],
                                [
                                    'id'                                   => '_select_cryptocurrency_option',
                                    'label'                                => __( 'The cryptocurrency', 'cryptocurrency-product-for-woocommerce' ),
                                    'options'                              => $options,
                                    'value'                                => $_select_cryptocurrency_option,
                                    '_cryptocurrency_product_setting_type' => 'select',
                                ],
                                [
                                    'id'                                   => '_text_input_cryptocurrency_minimum_value',
                                    'label'                                => __( 'Minimum amount', 'cryptocurrency-product-for-woocommerce' ),
                                    'desc_tip'                             => 'true',
                                    'description'                          => __( 'The minimum amount of cryptocurrency user can buy', 'cryptocurrency-product-for-woocommerce' ),
                                    'wrapper_class'                        => '_text_input_cryptocurrency_minimum_value_field hidden',
                                    'custom_attributes'                    => [
                                        'min'        => 0,
                                        'step'       => $s,
                                        'novalidate' => 'novalidate',
                                        'type'       => 'number',
                                    ],
                                    'value'                                => $_text_input_cryptocurrency_minimum_value,
                                    '_cryptocurrency_product_setting_type' => 'text_input',
                                    '_cryptocurrency_type'                 => 'ether',
                                ],
                                [
                                    'id'                                   => '_text_input_cryptocurrency_step',
                                    'label'                                => __( 'Step', 'cryptocurrency-product-for-woocommerce' ),
                                    'desc_tip'                             => 'true',
                                    'description'                          => __( 'The increment/decrement step', 'cryptocurrency-product-for-woocommerce' ),
                                    'wrapper_class'                        => '_text_input_cryptocurrency_step_field hidden',
                                    'custom_attributes'                    => [
                                        'min'        => $s,
                                        'step'       => $s,
                                        'novalidate' => 'novalidate',
                                        'type'       => 'number',
                                    ],
                                    'value'                                => $_text_input_cryptocurrency_step,
                                    '_cryptocurrency_product_setting_type' => 'text_input',
                                    '_cryptocurrency_type'                 => 'ether',
                                ],
                                [
                                    'id'                                   => '_text_input_cryptocurrency_balance',
                                    'label'                                => __( 'Balance', 'cryptocurrency-product-for-woocommerce' ),
                                    'desc_tip'                             => 'true',
                                    'description'                          => __( 'The wallet balance', 'cryptocurrency-product-for-woocommerce' ),
                                    'wrapper_class'                        => '_text_input_cryptocurrency_balance_field hidden',
                                    'custom_attributes'                    => array(
                                        'disabled' => 'disabled',
                                    ),
                                    '_cryptocurrency_product_setting_type' => 'text_input',
                                    '_cryptocurrency_type'                 => 'ether',
                                ],
                                // fix for save_post: https://stackoverflow.com/questions/5434219/problem-with-wordpress-save-post-action#comment6729746_5849143
                                [
                                    'id'                                   => '_text_input_cryptocurrency_flag',
                                    'value'                                => 'yes',
                                    '_cryptocurrency_product_setting_type' => 'hidden',
                                ],
                            ];
                            $settings = apply_filters(
                                'cryptocurrency_product_for_woocommerce_product_type_settings',
                                $settings,
                                $_select_cryptocurrency_option,
                                $post_id
                            );
                            return $settings;
                        }

                        /**
                         * Contents of the cryptocurrency options product tab.
                         */
                        function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_options_product_tab_content_wc() {
                            global $post;
                            global $product;
                            $post_id = ( $product ? $product->get_id() : $post->ID );
                            ?>
                <div id="cryptocurrency_product_data" class="panel woocommerce_options_panel hidden">
                    <div class="options_group">
                        <?php 
                            $settings = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_options_product_tab_content( $post_id );
                            //            CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log("options_product_tab_content_wc($post_id): " . print_r($settings, true));
                            CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_display_options( $settings );
                            ?>
                    </div>

                </div>
<?php 
                            CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_enqueue_scripts_();
                        }

                        add_action( 'woocommerce_product_data_panels', 'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_options_product_tab_content_wc' );
                        function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_is_vendor_user(  $user_id = null  ) {
                            if ( is_null( $user_id ) ) {
                                $user_id = get_current_user_id();
                                if ( $user_id <= 0 ) {
                                    return false;
                                }
                            }
                            $roles = apply_filters( 'cryptocurrency_product_for_woocommerce__get_vendor_roles', ['vendor'] );
                            // CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log("CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_is_vendor_user($user_id): " . print_r($roles, true));
                            foreach ( $roles as $role ) {
                                if ( user_can( $user_id, $role ) ) {
                                    return true;
                                }
                            }
                            return false;
                        }

                        /**
                         * Save the custom fields.
                         */
                        function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_save_option_field(  $post_id, $product = null  ) {
                            // CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log("CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_save_option_field($post_id): " . print_r($_POST, true));
                            $product = ( is_null( $product ) ? wc_get_product( $post_id ) : $product );
                            if ( !$product ) {
                                CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "save_option_field({$post_id}) not a product" );
                                return;
                            }
                            // Fix for: Call to undefined method WP_Post::get_id()
                            if ( 'WP_Post' === get_class( $product ) ) {
                                $product = wc_get_product( $product->ID );
                            }
                            if ( !$product ) {
                                CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "save_option_field({$post_id}) not a product" );
                                return;
                            }
                            _CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE__before_POST_processing_action_impl();
                            if ( !_CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_is_cryptocurrency( $product->get_id() ) ) {
                                $is_cryptocurrency = ( isset( $_POST['_cryptocurrency_product_for_woocommerce_cryptocurrency_product_type'] ) ? 'yes' : 'no' );
                                if ( $is_cryptocurrency == 'no' ) {
                                    return;
                                }
                            }
                            // Select
                            $cryptocurrency_option = $_POST['_select_cryptocurrency_option'];
                            if ( !empty( $cryptocurrency_option ) ) {
                                update_post_meta( $post_id, '_select_cryptocurrency_option', esc_attr( $cryptocurrency_option ) );
                            } else {
                                CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( 'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_save_option_field: _select_cryptocurrency_option set to empty' );
                                update_post_meta( $post_id, '_select_cryptocurrency_option', '' );
                            }
                            //	if ( isset( $_POST['_text_input_cryptocurrency_data'] ) ) {
                            //		update_post_meta( $post_id, '_text_input_cryptocurrency_data', sanitize_text_field( $_POST['_text_input_cryptocurrency_data'] ) );
                            //    }
                            if ( isset( $_POST['_text_input_cryptocurrency_minimum_value'] ) ) {
                                update_post_meta( $post_id, '_text_input_cryptocurrency_minimum_value', sanitize_text_field( $_POST['_text_input_cryptocurrency_minimum_value'] ) );
                            }
                            if ( isset( $_POST['_text_input_cryptocurrency_step'] ) ) {
                                update_post_meta( $post_id, '_text_input_cryptocurrency_step', sanitize_text_field( $_POST['_text_input_cryptocurrency_step'] ) );
                            }
                            do_action(
                                'cryptocurrency_product_for_woocommerce_save_option_field',
                                $cryptocurrency_option,
                                $post_id,
                                $product
                            );
                        }

                        add_action(
                            'woocommerce_process_product_meta',
                            'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_save_option_field',
                            10,
                            2
                        );
                        add_action(
                            'woocommerce_process_product_meta_variable',
                            'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_save_option_field',
                            10,
                            2
                        );
                        add_action(
                            'woocommerce_process_product_meta_auction',
                            'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_save_option_field',
                            10,
                            2
                        );
                        function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_after_product_object_save(  $product, $data_store  ) {
                            if ( !_CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_is_cryptocurrency( $product->get_id() ) ) {
                                return;
                            }
                            $cryptocurrency_option = _CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_post_meta( $product->get_id(), '_select_cryptocurrency_option', true );
                            do_action(
                                'cryptocurrency_product_for_woocommerce_woocommerce_after_product_object_save',
                                $cryptocurrency_option,
                                $product,
                                $data_store
                            );
                        }

                        add_action(
                            'woocommerce_after_product_object_save',
                            'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_after_product_object_save',
                            10,
                            2
                        );
                        // define the woocommerce_save_product_variation callback
                        function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_save_product_variation(  $variation_id, $i  ) {
                            CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_save_product_variation( {$variation_id}, {$i} )" );
                            $variation = wc_get_product_object( 'variation', $variation_id );
                            if ( !$variation ) {
                                CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "Failed to get product object for variation_id: ", $variation_id );
                                return;
                            }
                            //    $variation = wc_get_product($variation_id);
                            $product = wc_get_product( $variation->get_parent_id() );
                            if ( !$product ) {
                                CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "Failed to get parent product object for variation_id: ", $variation_id );
                                return;
                            }
                            _CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE__before_POST_processing_action_impl();
                            $cryptocurrency_option = _CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_post_meta( $product->get_id(), '_select_cryptocurrency_option', true );
                            do_action(
                                'cryptocurrency_product_for_woocommerce_save_option_field',
                                $cryptocurrency_option,
                                $variation->get_id(),
                                $variation
                            );
                        }

                        // add the action
                        add_action(
                            'woocommerce_save_product_variation',
                            'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_save_product_variation',
                            10,
                            2
                        );
                        /**
                         * Hide Attributes data panel.
                         */
                        function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_hide_attributes_data_panel(  $tabs  ) {
                            $tabs['shipping']['class'][] = 'hide_if_cryptocurrency_product_for_woocommerce_cryptocurrency_product_type';
                            $tabs['shipping']['class'][] = 'hide_if_variable_cryptocurrency_product_for_woocommerce_cryptocurrency_product_type';
                            $tabs['shipping']['class'][] = 'hide_if_auction_cryptocurrency_product_for_woocommerce_cryptocurrency_product_type';
                            return $tabs;
                        }

                        add_filter( 'woocommerce_product_data_tabs', 'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_hide_attributes_data_panel' );
                        //----------------------------------------------------------------------------//
                        //                     Shipping field for crypto-address                      //
                        //----------------------------------------------------------------------------//
                        function _CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_is_cryptocurrency_product_in_cart() {
                            if ( !is_null( WC()->cart ) ) {
                                // Find each product in the cart and add it to the $cart_ids array
                                foreach ( WC()->cart->get_cart() as $cart_item_key => $product ) {
                                    if ( _CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_is_cryptocurrency( $product['product_id'] ) ) {
                                        return true;
                                    }
                                }
                            }
                            return false;
                        }

                        // The type of crypto product in a cart
                        function _CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_cryptocurrency_product_type_in_cart() {
                            if ( !is_null( WC()->cart ) ) {
                                // Find each product in the cart and add it to the $cart_ids array
                                foreach ( WC()->cart->get_cart() as $cart_item_key => $product ) {
                                    if ( _CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_is_cryptocurrency( $product['product_id'] ) ) {
                                        $cryptocurrency_option = _CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_post_meta( $product['product_id'], '_select_cryptocurrency_option', true );
                                        if ( empty( $cryptocurrency_option ) ) {
                                            $cryptocurrency_option = '';
                                        }
                                        return $cryptocurrency_option;
                                    }
                                }
                            }
                            return '';
                        }

                        function _CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_cryptocurrency_product_id_in_cart() {
                            if ( !is_null( WC()->cart ) ) {
                                // Find each product in the cart and add it to the $cart_ids array
                                foreach ( WC()->cart->get_cart() as $cart_item_key => $product ) {
                                    if ( _CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_is_cryptocurrency( $product['product_id'] ) ) {
                                        return $product['product_id'];
                                    }
                                }
                            }
                            return '';
                        }

                        function _CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_cryptocurrency_product_type_in_order(  $order_id  ) {
                            // do the payment
                            $order = wc_get_order( $order_id );
                            if ( !$order ) {
                                return '';
                            }
                            $order_items = $order->get_items();
                            foreach ( $order_items as $product ) {
                                $product_id = $product['product_id'];
                                if ( !_CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_is_cryptocurrency( $product_id ) ) {
                                    // skip non-cryptocurrency products
                                    continue;
                                }
                                $_product = wc_get_product( $product_id );
                                if ( !$_product ) {
                                    CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "_CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_cryptocurrency_product_type_in_order({$product_id}) not a product" );
                                    continue;
                                }
                                $cryptocurrency_option = _CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_post_meta( $product_id, '_select_cryptocurrency_option', true );
                                return $cryptocurrency_option;
                            }
                            return '';
                        }

                        // Hook in
                        add_filter( 'woocommerce_checkout_fields', 'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_override_checkout_fields' );
                        // Our hooked in function - $fields is passed via the filter!
                        function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_override_checkout_fields(  $fields  ) {
                            global $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_options;
                            if ( !_CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_is_cryptocurrency_product_in_cart() ) {
                                return $fields;
                            }
                            $cryptocurrency_option = _CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_cryptocurrency_product_type_in_cart();
                            return apply_filters( 'cryptocurrency_product_for_woocommerce_override_checkout_fields', $fields, $cryptocurrency_option );
                        }

                        /**
                         * Display field value on the order edit page
                         */
                        //add_action( 'woocommerce_admin_order_data_after_billing_address', 'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_checkout_field_display_admin_order_meta', 10, 1 );
                        //
                        //function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_checkout_field_display_admin_order_meta($order){
                        //    echo '<p><strong>'.__('Ethereum Address From Checkout Form', 'cryptocurrency-product-for-woocommerce').':</strong> ' . _CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_post_meta( $order->get_id(), '_billing_cryptocurrency_ethereum_address', true ) . '</p>';
                        //}
                        // @see https://stackoverflow.com/a/41987077/4256005
                        add_filter(
                            'woocommerce_email_customer_details_fields',
                            'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_email_customer_details_fields',
                            20,
                            3
                        );
                        function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_email_customer_details_fields(  $fields, $sent_to_admin = false, $order = null  ) {
                            if ( is_null( $order ) ) {
                                CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( 'woocommerce_email_customer_details_fields order is null' );
                                return $fields;
                            }
                            $order_id = $order->get_id();
                            $cryptocurrency_option = _CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_cryptocurrency_product_type_in_order( $order_id );
                            if ( empty( $cryptocurrency_option ) ) {
                                // CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log("woocommerce_email_customer_details_fields empty($cryptocurrency_option) for order id " . $order_id);
                                return $fields;
                            }
                            $_billing_cryptocurrency_address = apply_filters(
                                'cryptocurrency_product_for_woocommerce_get_cryptocurrency_address',
                                '',
                                $cryptocurrency_option,
                                $order_id
                            );
                            $fields['billing_cryptocurrency_address'] = array(
                                'label' => __( 'Crypto Wallet Address', 'cryptocurrency-product-for-woocommerce' ),
                                'value' => $_billing_cryptocurrency_address,
                            );
                            $order_items = $order->get_items();
                            foreach ( $order_items as $item ) {
                                $product_id = $item['product_id'];
                                if ( !_CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_is_cryptocurrency( $product_id ) ) {
                                    // skip non-cryptocurrency products
                                    continue;
                                }
                                $fields = apply_filters(
                                    'cryptocurrency_product_for_woocommerce_get_order_txhash',
                                    $fields,
                                    $cryptocurrency_option,
                                    $order_id,
                                    $product_id
                                );
                            }
                            return $fields;
                        }

                        /* Add CSS for ADMIN area so that the additional billing fields (email, phone) display on left and right side of edit billing details */
                        //add_action('admin_head', 'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_custom_admin_css');
                        //function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_custom_admin_css() {
                        //  echo '<style>
                        //    #order_data .order_data_column ._billing_email2_field {
                        //        clear: left;
                        //        float: left;
                        //    }
                        //    #order_data .order_data_column ._billing_phone_field {
                        //        float: right;
                        //    }
                        //  </style>';
                        //}
                        // @see https://stackoverflow.com/a/37780501/4256005
                        // Adding Meta container admin shop_order pages
                        add_action( 'add_meta_boxes', 'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_add_meta_boxes' );
                        function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_add_meta_boxes() {
                            global $post;
                            if ( 'shop_order' !== get_post_type( $post ) ) {
                                return;
                            }
                            $order_id = $post->ID;
                            $cryptocurrency_option = _CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_cryptocurrency_product_type_in_order( $order_id );
                            if ( empty( $cryptocurrency_option ) ) {
                                return;
                            }
                            do_action( 'cryptocurrency_product_for_woocommerce_add_meta_boxes', $cryptocurrency_option );
                        }

                        // Save the data of the Meta field
                        add_action(
                            'save_post',
                            'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_save_wc_order_other_fields',
                            1000,
                            1
                        );
                        function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_save_wc_order_other_fields(  $post_id  ) {
                            // We need to verify this with the proper authorization (security stuff).
                            if ( 'shop_order' !== get_post_type( $post_id ) ) {
                                return;
                            }
                            // Check the user's permissions.
                            if ( !current_user_can( 'edit_shop_order', $post_id ) ) {
                                return $post_id;
                            }
                            $order_id = $post_id;
                            $order = wc_get_order( $order_id );
                            if ( !$order ) {
                                return $post_id;
                            }
                            _CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE__before_POST_processing_action_impl();
                            $cryptocurrency_option = _CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_cryptocurrency_product_type_in_order( $order_id );
                            // Check if our nonce is set.
                            if ( !isset( $_POST['CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_' . $cryptocurrency_option . '_other_meta_field_nonce'] ) ) {
                                return $post_id;
                            }
                            $nonce = $_REQUEST['CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_' . $cryptocurrency_option . '_other_meta_field_nonce'];
                            //Verify that the nonce is valid.
                            if ( !wp_verify_nonce( $nonce ) ) {
                                CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "save_wc_order_other_fields: bad nonce" );
                                return $post_id;
                            }
                            // If this is an autosave, our form has not been submitted, so we don't want to do anything.
                            if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
                                return $post_id;
                            }
                            // --- Its safe for us to save the data ! --- //
                            do_action( 'cryptocurrency_product_for_woocommerce_save_wc_order_other_fields', $cryptocurrency_option, $post_id );
                        }

                        function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_checkout_update_order_meta(  $order_id, $data  ) {
                            if ( !_CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_is_cryptocurrency_product_in_cart() ) {
                                return;
                            }
                            $cryptocurrency_option = _CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_cryptocurrency_product_type_in_cart();
                            do_action(
                                'cryptocurrency_product_for_woocommerce_checkout_update_order_meta',
                                $cryptocurrency_option,
                                $order_id,
                                $data
                            );
                        }

                        add_action(
                            'woocommerce_checkout_update_order_meta',
                            'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_checkout_update_order_meta',
                            20,
                            2
                        );
                        //----------------------------------------------------------------------------//
                        //                     Process order status changes                           //
                        //----------------------------------------------------------------------------//
                        function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_order_processing(  $order_id  ) {
                            CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_process_order( $order_id );
                        }

                        add_action( 'woocommerce_order_status_processing', 'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_order_processing' );
                        function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_order_completed(  $order_id  ) {
                            CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_process_order( $order_id );
                        }

                        add_action( 'woocommerce_order_status_completed', 'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_order_completed' );
                        /**
                         * Safely multiply double and int values and return a \phpseclib3\Math\BigInteger value
                         *
                         * @param  double $dval
                         * @param  int $ival
                         * @return \phpseclib3\Math\BigInteger
                         */
                        function _CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_double_int_multiply(  $dval, $ival  ) {
                            $dval = doubleval( $dval );
                            $ival = intval( $ival );
                            $dv1 = floor( $dval );
                            $ret = new \phpseclib3\Math\BigInteger(intval( $dv1 ));
                            $ret = $ret->multiply( new \phpseclib3\Math\BigInteger($ival) );
                            if ( $dv1 === $dval ) {
                                return $ret;
                            }
                            $dv2 = $dval - $dv1;
                            $iv1 = intval( $dv2 * $ival );
                            $ret = $ret->add( new \phpseclib3\Math\BigInteger($iv1) );
                            return $ret;
                        }

                        function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_clean_order_product_txhashes(  $lasttxhash, $order_id, $product_id  ) {
                            $errors_count = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_tx_errors_counter( $lasttxhash );
                            if ( $errors_count >= 10 ) {
                                $cryptocurrency_option = _CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_post_meta( $product_id, '_select_cryptocurrency_option', true );
                                do_action(
                                    'cryptocurrency_product_for_woocommerce_clean_order_product_txhashes',
                                    $cryptocurrency_option,
                                    $order_id,
                                    $product_id,
                                    $lasttxhash
                                );
                            }
                        }

                        function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_txhash_path(  $txHash, $blockchainNetwork  ) {
                            return CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_ETHER_get_txhash_path( $txHash, $blockchainNetwork );
                        }

                        function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_address_path(  $address, $blockchainNetwork  ) {
                            return CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_ETHER_get_address_path( $address, $blockchainNetwork );
                        }

                        add_action(
                            "CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_complete_order",
                            "CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_complete_order",
                            0,
                            2
                        );
                        function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_complete_order(  $order_id, $_product_id  ) {
                            try {
                                // do the payment
                                $order = wc_get_order( $order_id );
                                if ( !_CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_is_cryptocurrency( $_product_id ) ) {
                                    // skip non-cryptocurrency products
                                    return;
                                }
                                $order_items = $order->get_items();
                                if ( !$order_items ) {
                                    return;
                                }
                                $is_order_complete = true;
                                foreach ( $order_items as $item_id => $item ) {
                                    $product_id = $item['product_id'];
                                    if ( !_CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_is_cryptocurrency( $product_id ) ) {
                                        // skip non-cryptocurrency products
                                        continue;
                                    }
                                    $_product = wc_get_product( $product_id );
                                    if ( !$_product ) {
                                        CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_complete_order: complete_order({$product_id}) not a product" );
                                        continue;
                                    }
                                    $cryptocurrency_option = _CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_post_meta( $product_id, '_select_cryptocurrency_option', true );
                                    $is_order_complete = apply_filters(
                                        'cryptocurrency_product_for_woocommerce_is_order_complete',
                                        $is_order_complete,
                                        $cryptocurrency_option,
                                        $order_id,
                                        $product_id
                                    );
                                }
                                // $tx_succeeded = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_is_tx_succeeded($txhash, $providerUrl);
                                if ( $is_order_complete ) {
                                    // Load the order.
                                    $order = wc_get_order( $order_id );
                                    // Place the order completed.
                                    $res = $order->update_status( 'completed', __( 'Transaction confirmed.', 'cryptocurrency-product-for-woocommerce' ) );
                                    if ( !$res ) {
                                        // failed to complete order
                                        CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_complete_order: Failed to complete order: " . $order_id );
                                        // Неуспех - поставить себя снова в очередь
                                        // wait 5 blocks
                                        $blockchainInterblockPeriodSeconds = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getBlockchainInterblockPeriodSeconds();
                                        $offset = 5 * $blockchainInterblockPeriodSeconds;
                                        CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_enqueue_complete_order_task( $order_id, $product_id, $offset );
                                    }
                                    CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_complete_order: Order " . $order_id . " completed." );
                                } else {
                                    if ( is_null( $is_order_complete ) ) {
                                        // transaction failed
                                        // CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log("transaction $txhash for $order_id has failed");
                                        update_post_meta( $order_id, 'status', __( 'Transaction failed', 'cryptocurrency-product-for-woocommerce' ) );
                                    } else {
                                        // tx is not confirmed yet
                                        CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_complete_order: Failed to complete order: " . $order_id . ". one or more txns not confirmed yet. Restart processing." );
                                        // wait 2 blocks
                                        $blockchainInterblockPeriodSeconds = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getBlockchainInterblockPeriodSeconds();
                                        $offset = 2 * $blockchainInterblockPeriodSeconds;
                                        CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_enqueue_complete_order_task( $order_id, $product_id, $offset );
                                    }
                                }
                                // backward compatibility
                                do_action(
                                    'cryptocurrency_product_for_woocommerce_complete_order',
                                    $cryptocurrency_option,
                                    $order_id,
                                    $product_id
                                );
                            } catch ( Exception $ex ) {
                                CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_complete_order: " . $ex->getMessage() );
                                // Неуспех - поставить себя снова в очередь +60сек
                                $offset = 60;
                                CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_enqueue_complete_order_task( $order_id, $_product_id, $offset );
                            }
                        }

                        function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_enqueue_complete_order_task(  $order_id, $product_id, $offset = 0  ) {
                            global $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_plugin_dir;
                            $order = wc_get_order( $order_id );
                            if ( !$order ) {
                                CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "Failed to create complete_order task for order: {$order_id}" );
                                return;
                            }
                            $date = $order->get_date_created();
                            // fail order after one week of inactivity
                            $timeout = 3600 * 24 * CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getExpirationPeriod();
                            if ( time() - $date->getTimestamp() > $timeout ) {
                                CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "Failed to create complete_order task for order {$order_id}: order timed out." );
                                update_post_meta( $order_id, 'status', __( 'Timed out', 'cryptocurrency-product-for-woocommerce' ) );
                                // Place the order to failed.
                                $res = $order->update_status( 'failed', __( 'Timed out', 'cryptocurrency-product-for-woocommerce' ) );
                                if ( !$res ) {
                                    // failed to complete order
                                    CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "Failed to fail order: " . $order_id );
                                }
                                return;
                            }
                            $timestamp = time() + $offset;
                            $hook = "CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_complete_order";
                            $args = array($order_id, $product_id);
                            // @see https://github.com/woocommerce/action-scheduler/issues/730
                            // if (!class_exists('ActionScheduler', false) || !ActionScheduler::is_initialized()) {
                            //     require_once($CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_plugin_dir . '/vendor/woocommerce/action-scheduler/classes/abstracts/ActionScheduler.php');
                            //     ActionScheduler::init($CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_plugin_dir . '/vendor/woocommerce/action-scheduler/action-scheduler.php');
                            // }
                            $task_id = as_schedule_single_action( $timestamp, $hook, $args );
                            CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "Task complete_order with id {$task_id} scheduled for order: {$order_id}" );
                        }

                        // function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_cancel_complete_order_task($order_id, $product_id, $txhash, $nonce) {
                        // //    global $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_options;
                        //
                        //     $hook = "CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_complete_order";
                        //     $args = array($order_id, $product_id, $txhash, $nonce);
                        //     as_unschedule_action( $hook, $args );
                        //     CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log("Task complete_order with txhash $txhash unscheduled for order: $order_id");
                        // }
                        /**
                         * Log information using the WC_Logger class.
                         *
                         * Will do nothing unless debug is enabled.
                         *
                         * @param string $msg   The message to be logged.
                         */
                        function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log(  $msg  ) {
                            static $logger = false;
                            // Create a logger instance if we don't already have one.
                            if ( false === $logger ) {
                                $logger = new WC_Logger();
                            }
                            $logger->add( 'cryptocurrency-product-for-woocommerce', $msg );
                        }

                        add_action(
                            "CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_process_order",
                            "CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_process_order",
                            0,
                            1
                        );
                        /**
                         * Check if order is not processed yet and process it in this case:
                         * sends Ether or ERC20 tokens to the customer Ethereum address
                         *
                         * @param int $order_id The order id
                         */
                        function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_process_order(  $order_id  ) {
                            _CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_process_order_impl( $order_id );
                        }

                        ////see woocommerce simple auctions wordpress auctions
                        //function _CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_is_auction_order_item($order, $item_id) {
                        //    if ( function_exists( 'wc_get_order_item_meta' ) ) {
                        //        $item_meta = wc_get_order_item_meta( $item_id, '' );
                        //    } else {
                        //        $item_meta = method_exists( $order, 'wc_get_order_item_meta' ) ? $order->wc_get_order_item_meta( $item_id ) : $order->get_item_meta( $item_id );
                        //    }
                        //    $product_id   = $item_meta['_product_id'][0];
                        //    $product_data = wc_get_product( $product_id );
                        //    return ( method_exists( $product_data, 'get_type' ) && $product_data->get_type() == 'auction' );
                        //}
                        //// $id = $this->get_main_wpml_product_id();
                        //add_action('woocommerce_simple_auction_won', 'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_simple_auction_won', 10, 1);
                        //
                        //function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_simple_auction_won($product_id) {
                        //    CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log("CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_simple_auction_won($product_id) call");
                        //    $order_id = _CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_post_meta( $product_id, '_order_id', true );
                        //    if (empty($order_id)) {
                        //        CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log("CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_simple_auction_won($product_id) empty order_id");
                        //        return;
                        //    }
                        //    _CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_process_order_impl($order_id, false);
                        //}
                        //
                        //// do_action( 'woocommerce_simple_auction_close_buynow', $product_id , $original_product_id);
                        /**
                         * Check if order is not processed yet and process it in this case:
                         * sends Ether or ERC20 tokens to the customer Ethereum address
                         *
                         * @param int $order_id The order id
                         */
                        function _CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_process_order_impl(  $order_id, $skip_auction = true  ) {
                            global $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_options;
                            try {
                                // do the payment
                                $order = wc_get_order( $order_id );
                                $order_items = $order->get_items();
                                if ( !$order_items ) {
                                    return;
                                }
                                foreach ( $order_items as $item_id => $item ) {
                                    $product_id = $item['product_id'];
                                    if ( !_CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_is_cryptocurrency( $product_id ) ) {
                                        // skip non-cryptocurrency products
                                        continue;
                                    }
                                    //            if ($skip_auction && _CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_is_auction_order_item($order, $item_id)) {
                                    //                // skip auction products
                                    //                CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log("process_order($product_id) skip auction");
                                    //                continue;
                                    //            }
                                    $_product = wc_get_product( $product_id );
                                    if ( !$_product ) {
                                        CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "process_order({$product_id}) not a product" );
                                        continue;
                                    }
                                    $cryptocurrency_option = _CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_post_meta( $product_id, '_select_cryptocurrency_option', true );
                                    $_billing_cryptocurrency_address = apply_filters(
                                        'cryptocurrency_product_for_woocommerce_get_cryptocurrency_address',
                                        '',
                                        $cryptocurrency_option,
                                        $order_id
                                    );
                                    if ( empty( $_billing_cryptocurrency_address ) ) {
                                        CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "Ethereum address is empty for order {$order_id}. Skip processing." );
                                        continue;
                                    }
                                    $marketAddress = $_billing_cryptocurrency_address;
                                    $minimumValue = apply_filters( 'woocommerce_quantity_input_min', 0, $_product );
                                    if ( empty( $minimumValue ) || 0 == $minimumValue ) {
                                        $minimumValue = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_quantity_input_min( 0.01, $_product );
                                    }
                                    $inputStep = apply_filters( 'woocommerce_quantity_input_step', 0.01, $_product );
                                    if ( empty( $inputStep ) || 0 == floatval( $inputStep ) ) {
                                        $inputStep = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_quantity_input_step( 0.01, $_product );
                                    }
                                    $product_quantity = null;
                                    if ( is_null( $product_quantity ) ) {
                                        $product_quantity = $item['qty'];
                                        CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_process_order({$product_id}): total: " . $item['total'] . ', total_tax: ' . $item['total_tax'] . ', product_quantity=' . $product_quantity );
                                    }
                                    // add 10% of the $inputStep to workaround price fluctuations
                                    if ( floatval( $product_quantity ) < floatval( $minimumValue ) ) {
                                        // Place the order to failed.
                                        $res = $order->update_status( 'failed', sprintf( __( 'Product quantity %1$s less then the minimum allowed: %2$s.', 'cryptocurrency-product-for-woocommerce' ), $product_quantity, $minimumValue ) );
                                        if ( !$res ) {
                                            // failed to complete order
                                            CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "Failed to fail order: " . $order_id );
                                        }
                                        continue;
                                    }
                                    $maximumValue = apply_filters( 'woocommerce_quantity_input_max', -1, $_product );
                                    if ( empty( $maximumValue ) || $maximumValue ) {
                                        $maximumValue = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_quantity_input_max( -1, $_product );
                                    }
                                    if ( $maximumValue > 0 ) {
                                        //                $product_quantity = $product['qty'];
                                        if ( floatval( $product_quantity ) > floatval( $maximumValue ) ) {
                                            // Place the order to failed.
                                            $res = $order->update_status( 'failed', sprintf( __( 'Product quantity %1$s greater then the maximum allowed: %2$s.', 'cryptocurrency-product-for-woocommerce' ), $product_quantity, $maximumValue ) );
                                            if ( !$res ) {
                                                // failed to complete order
                                                CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "Failed to fail order: " . $order_id );
                                            }
                                            continue;
                                        }
                                    }
                                    // @see https://stackoverflow.com/a/25159437/4256005
                                    if ( $_product->is_type( 'variable' ) ) {
                                        // item is variable and we can check its variation
                                        $variation_id = $_product->get_id();
                                        // It will always be the variation ID if this is a variation
                                        if ( !empty( $variation_id ) ) {
                                            $variation = wc_get_product_object( 'variation', $variation_id );
                                            if ( !$variation ) {
                                                CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "Failed to get product object for variation_id: ", $variation_id );
                                                continue;
                                            }
                                            CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_enqueue_send_task(
                                                $cryptocurrency_option,
                                                $order_id,
                                                $variation->get_id(),
                                                $marketAddress,
                                                $product_quantity
                                            );
                                        }
                                    } else {
                                        // $id = $this->get_main_wpml_product_id();
                                        // do_action('woocommerce_simple_auction_won', $id);
                                        CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_enqueue_send_task(
                                            $cryptocurrency_option,
                                            $order_id,
                                            $product_id,
                                            $marketAddress,
                                            $product_quantity
                                        );
                                    }
                                }
                            } catch ( Exception $ex ) {
                                CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_process_order: " . $ex->getMessage() );
                                // Неуспех - поставить себя снова в очередь +60сек
                                $offset = 60;
                                CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_enqueue_process_order_task( $order_id, $offset );
                            }
                        }

                        function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_enqueue_send_task(
                            $cryptocurrency_option,
                            $order_id,
                            $product_id,
                            $marketAddress,
                            $product_quantity
                        ) {
                            $order = wc_get_order( $order_id );
                            if ( !$order ) {
                                CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_enqueue_send_task: Order {$order_id} was not found. Skip payment." );
                                return;
                            }
                            if ( !$order->has_status( "processing" ) ) {
                                CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_enqueue_send_task: Order {$order_id} status is not eligible for processing. Skip payment." );
                                return;
                            }
                            // if (0 == $order->get_total()) {
                            //     CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log("Order $order_id has zero total value paid and is not eligible for processing. Skip payment.");
                            //     return;
                            // }
                            do_action(
                                'cryptocurrency_product_for_woocommerce_enqueue_send_task',
                                $cryptocurrency_option,
                                $order_id,
                                $product_id,
                                $marketAddress,
                                $product_quantity
                            );
                        }

                        function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_enqueue_process_order_task(  $order_id, $offset = 0  ) {
                            global $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_plugin_dir;
                            $timestamp = time() + $offset;
                            $hook = "CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_process_order";
                            $args = array($order_id);
                            // @see https://github.com/woocommerce/action-scheduler/issues/730
                            // if (!class_exists('ActionScheduler', false) || !ActionScheduler::is_initialized()) {
                            //     require_once($CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_plugin_dir . '/vendor/woocommerce/action-scheduler/classes/abstracts/ActionScheduler.php');
                            //     ActionScheduler::init($CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_plugin_dir . '/vendor/woocommerce/action-scheduler/action-scheduler.php');
                            // }
                            $task_id = as_schedule_single_action( $timestamp, $hook, $args );
                            CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "Task process_order with id {$task_id} scheduled for order: {$order_id}" );
                        }

                        function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getExpirationPeriod() {
                            global $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_options;
                            return 7;
                        }

                        function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_billing_cryptocurrency_address(  $cryptocurrency_option  ) {
                            $value = '';
                            $user_id = get_current_user_id();
                            if ( $user_id <= 0 ) {
                                // user not logged in
                                return $value;
                            }
                            $billing_address_meta_key = apply_filters( 'cryptocurrency_product_for_woocommerce_get_billing_address_meta_key', '', $cryptocurrency_option );
                            $value = get_user_meta( $user_id, $billing_address_meta_key, true );
                            if ( !empty( $value ) ) {
                                // previous entered value found
                                return $value;
                            }
                            $userWalletMetaKeys = apply_filters( 'cryptocurrency_product_for_woocommerce_get_user_wallet_meta_keys', '', $cryptocurrency_option );
                            if ( empty( $userWalletMetaKeys ) ) {
                                return $value;
                            }
                            $userWalletMetaKeys = array_map( 'trim', explode( ',', $userWalletMetaKeys ) );
                            if ( $userWalletMetaKeys ) {
                                foreach ( $userWalletMetaKeys as $userWalletMetaKey ) {
                                    $value = get_user_meta( $user_id, $userWalletMetaKey, true );
                                    if ( !empty( $value ) ) {
                                        // previous entered value found
                                        break;
                                    }
                                    $value = apply_filters( 'cryptocurrency_product_for_woocommerce_get_valid_blockchain_address', $value, $cryptocurrency_option );
                                    if ( !is_null( $value ) ) {
                                        // valid address found
                                        break;
                                    }
                                }
                            }
                            return $value;
                        }

                        //----------------------------------------------------------------------------//
                        //                            Enqueue Scripts                                 //
                        //----------------------------------------------------------------------------//
                        function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_enqueue_scripts_() {
                            wp_enqueue_script( 'cryptocurrency-product-for-woocommerce' );
                        }

                        function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_enqueue_script() {
                            do_action( 'cryptocurrency_product_for_woocommerce_enqueue_script' );
                        }

                        add_action( 'admin_enqueue_scripts', 'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_enqueue_script' );
                        add_action( 'wp_enqueue_scripts', 'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_enqueue_script' );
                        //----------------------------------------------------------------------------//
                        //                               Admin Options                                //
                        //----------------------------------------------------------------------------//
                        if ( is_admin() ) {
                            include_once $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_plugin_dir . '/cryptocurrency-product-for-woocommerce.admin.php';
                        }
                        function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_add_menu_link() {
                            $page = add_options_page(
                                __( 'Cryptocurrency Product Settings', 'cryptocurrency-product-for-woocommerce' ),
                                __( 'Cryptocurrency Product', 'cryptocurrency-product-for-woocommerce' ),
                                'manage_options',
                                'cryptocurrency-product-for-woocommerce',
                                'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_options_page'
                            );
                        }

                        add_filter( 'admin_menu', 'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_add_menu_link' );
                        // Place in Option List on Settings > Plugins page
                        function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_actlinks(  $links, $file  ) {
                            // Static so we don't call plugin_basename on every plugin row.
                            static $this_plugin;
                            if ( !$this_plugin ) {
                                $this_plugin = plugin_basename( __FILE__ );
                            }
                            if ( $file == $this_plugin ) {
                                $settings_link = '<a href="options-general.php?page=cryptocurrency-product-for-woocommerce">' . __( 'Settings' ) . '</a>';
                                array_unshift( $links, $settings_link );
                                // before other links
                            }
                            return $links;
                        }

                        add_filter(
                            'plugin_action_links',
                            'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_actlinks',
                            10,
                            2
                        );
                        //----------------------------------------------------------------------------//
                        //                Use decimal in quantity fields in WooCommerce               //
                        //----------------------------------------------------------------------------//
                        // @see: http://codeontrack.com/use-decimal-in-quantity-fields-in-woocommerce-wordpress/
                        add_action( 'plugins_loaded', 'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_plugins_loaded' );
                        function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_plugins_loaded() {
                            // Removes the WooCommerce filter, that is validating the quantity to be an int
                            remove_filter( 'woocommerce_stock_amount', 'intval' );
                            // Add a filter, that validates the quantity to be a float
                            add_filter( 'woocommerce_stock_amount', 'floatval' );
                        }

                        //// Add unit price fix when showing the unit price on processed orders
                        //add_filter('woocommerce_order_amount_item_total', 'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_order_amount_item_total', 10, 5);
                        //function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_order_amount_item_total($total, $order, $item, $inc_tax = false, $round = true) {
                        //    CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log("woocommerce_order_amount_item_total: price=$total, item=" . print_r($item, true));
                        //    return $total;
                        ////    $product = $item->get_product();
                        ////    $new_total = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_product_get_price( $total, $product );
                        ////    CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log("woocommerce_order_amount_item_total: new_total=$new_total");
                        ////    return $new_total;
                        //}
                        //add_filter('woocommerce_order_amount_item_subtotal', 'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_order_amount_item_subtotal', 10, 5);
                        //function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_order_amount_item_subtotal($subtotal, $order, $item, $inc_tax = false, $round = true) {
                        //    CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log("woocommerce_order_amount_item_subtotal: subtotal=$subtotal, item=" . print_r($item, true));
                        //    $product = $item->get_product();
                        //    $new_subtotal = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_product_get_price( $subtotal, $product );
                        //    CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log("woocommerce_order_amount_item_subtotal: new_subtotal=$new_subtotal");
                        //    return $new_subtotal;
                        //}
                        //add_filter('woocommerce_order_get_total', 'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_order_get_total', 10, 2);
                        //function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_order_get_total($total, $order) {
                        ////    CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log("woocommerce_order_get_total: total=$total");
                        ////    return $total;
                        //    $new_total = 0;
                        //    foreach ( $order->get_items() as $item ) {
                        //        $new_total += $item->get_total();
                        //    }
                        ////    CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log("woocommerce_order_get_total: new_total=$new_total");
                        //    return $new_total;
                        //}
                        //// Add unit price fix when showing the unit price on processed orders
                        //add_filter('woocommerce_order_item_get_total', 'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_order_item_get_total', 10, 2);
                        //function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_order_item_get_total($total, $item) {
                        //    CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log("woocommerce_order_item_get_total: total=$total");
                        //    return $total;
                        ////    $product = $item->get_product();
                        ////    $new_total = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_product_get_price( $total, $product );
                        ////    CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log("woocommerce_order_item_get_total: new_total=$new_total");
                        ////    return $new_total;
                        //}
                        //add_filter('woocommerce_order_item_get_subtotal', 'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_order_item_get_subtotal', 10, 2);
                        //function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_order_item_get_subtotal($subtotal, $item) {
                        //    CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log("woocommerce_order_item_get_subtotal: subtotal=$subtotal");
                        //    return $subtotal;
                        ////    $product = $item->get_product();
                        ////    $new_subtotal = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_product_get_price( $subtotal, $product );
                        ////    CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log("woocommerce_order_item_get_subtotal: new_subtotal=$new_subtotal");
                        ////    return $new_subtotal;
                        //}
                        //add_filter('woocommerce_get_formatted_order_total', 'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_formatted_order_total', 10, 2);
                        //function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_formatted_order_total($formatted_total, $order/*, $tax_display, $display_refunded*/) {
                        //    $order_total     = $order->get_total();
                        ////    CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log("woocommerce_get_formatted_order_total:  formatted_total: $formatted_total, tax_display: $tax_display, display_refunded: $display_refunded");
                        //    CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log("woocommerce_get_formatted_order_total: order_total: $order_total, formatted_total: $formatted_total");
                        //    return $formatted_total;
                        //}
                        // define the woocommerce_quantity_input_min callback
                        function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_quantity_input_min(  $min, $product  ) {
                            if ( !$product ) {
                                return $min;
                            }
                            $product_id = $product->get_id();
                            if ( !_CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_is_cryptocurrency( $product_id ) ) {
                                return $min;
                            }
                            $minimumValue = _CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_post_meta( $product_id, '_text_input_cryptocurrency_minimum_value', true );
                            if ( !empty( $minimumValue ) ) {
                                return floatval( $minimumValue );
                            }
                            $cryptocurrency_option = _CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_post_meta( $product_id, '_select_cryptocurrency_option', true );
                            return apply_filters(
                                'cryptocurrency_product_for_woocommerce_woocommerce_quantity_input_min',
                                $min,
                                $cryptocurrency_option,
                                $product_id
                            );
                        }

                        add_filter(
                            'woocommerce_quantity_input_min',
                            'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_quantity_input_min',
                            10,
                            2
                        );
                        // define the woocommerce_quantity_input_max callback
                        function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_quantity_input_max(  $max, $product  ) {
                            if ( !$product ) {
                                return $max;
                            }
                            $product_id = $product->get_id();
                            if ( !_CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_is_cryptocurrency( $product_id ) ) {
                                return $max;
                            }
                            $cryptocurrency_option = _CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_post_meta( $product_id, '_select_cryptocurrency_option', true );
                            return apply_filters(
                                'cryptocurrency_product_for_woocommerce_woocommerce_quantity_input_max',
                                $max,
                                $cryptocurrency_option,
                                $product_id
                            );
                        }

                        add_filter(
                            'woocommerce_quantity_input_max',
                            'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_quantity_input_max',
                            10,
                            2
                        );
                        // define the woocommerce_quantity_input_step callback
                        function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_quantity_input_step(  $step, $product  ) {
                            if ( !$product ) {
                                return $step;
                            }
                            $product_id = $product->get_id();
                            if ( !_CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_is_cryptocurrency( $product_id ) ) {
                                return $step;
                            }
                            $step = _CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_post_meta( $product_id, '_text_input_cryptocurrency_step', true );
                            if ( !empty( $step ) ) {
                                return floatval( $step );
                            }
                            $cryptocurrency_option = _CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_post_meta( $product_id, '_select_cryptocurrency_option', true );
                            return apply_filters(
                                'cryptocurrency_product_for_woocommerce_woocommerce_quantity_input_step',
                                $step,
                                $cryptocurrency_option,
                                $product_id
                            );
                        }

                        add_filter(
                            'woocommerce_quantity_input_step',
                            'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_quantity_input_step',
                            10,
                            2
                        );
                        //----------------------------------------------------------------------------//
                        //                                   L10n                                     //
                        //----------------------------------------------------------------------------//
                        function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_load_textdomain() {
                            /**
                             * Localise.
                             */
                            load_plugin_textdomain( 'cryptocurrency-product-for-woocommerce', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
                        }

                        add_action( 'plugins_loaded', 'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_load_textdomain' );
                        //----------------------------------------------------------------------------//
                        //                      Ethereum address verification                         //
                        //----------------------------------------------------------------------------//
                        add_action(
                            'woocommerce_after_checkout_validation',
                            'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_after_checkout_validation',
                            10,
                            2
                        );
                        function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_after_checkout_validation(  $data, $errors  ) {
                            global $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_options;
                            if ( !_CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_is_cryptocurrency_product_in_cart() ) {
                                return;
                            }
                            $cryptocurrency_option = _CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_cryptocurrency_product_type_in_cart();
                            $product_id = _CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_cryptocurrency_product_id_in_cart();
                            do_action(
                                'cryptocurrency_product_for_woocommerce_after_checkout_validation',
                                $cryptocurrency_option,
                                $product_id,
                                $data,
                                $errors
                            );
                        }

                        add_filter(
                            'woocommerce_checkout_order_processed',
                            'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_checkout_order_processed',
                            20,
                            3
                        );
                        function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_checkout_order_processed(  $order_id, $posted_data, $order  ) {
                            // user is already created there, if createaccount is checked
                            $createAccount = isset( $posted_data['createaccount'] ) && !empty( $posted_data['createaccount'] );
                            if ( !$createAccount ) {
                                return;
                            }
                            $order_items = $order->get_items();
                            foreach ( $order_items as $product ) {
                                $product_id = $product['product_id'];
                                if ( !_CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_is_cryptocurrency( $product_id ) ) {
                                    // skip non-cryptocurrency products
                                    continue;
                                }
                                $_product = wc_get_product( $product_id );
                                if ( !$_product ) {
                                    CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "_CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_cryptocurrency_product_type_in_order({$product_id}) not a product" );
                                    continue;
                                }
                                $cryptocurrency_option = _CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_post_meta( $product_id, '_select_cryptocurrency_option', true );
                                $billing_address_meta_key = apply_filters( 'cryptocurrency_product_for_woocommerce_get_billing_address_meta_key', '', $cryptocurrency_option );
                                if ( !empty( $billing_address_meta_key ) && (!isset( $posted_data[$billing_address_meta_key] ) || empty( $posted_data[$billing_address_meta_key] )) ) {
                                    $billing_address = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_billing_cryptocurrency_address( $cryptocurrency_option );
                                    _CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_update_post_meta( $order_id, '_' . $billing_address_meta_key, $billing_address );
                                }
                            }
                        }

                        add_action(
                            'woocommerce_order_item_needs_processing',
                            'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_order_item_needs_processing',
                            10,
                            3
                        );
                        function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_order_item_needs_processing(  $needs_procesing, $product, $order_id  ) {
                            return $needs_procesing || _CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_is_cryptocurrency( $product->get_id() );
                        }

                    }
                    //if ( ! function_exists( 'cryptocurrency_product_for_woocommerce_freemius_init' ) ) {
                }
                // WooCommerce activated
            }
        }
    }
}
// PHP version