<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
add_filter(
    'ethereumico.io/blockchain-explorer-api-key',
    function ( $etherscanApiKey, $blockchainId ) {
        global $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_options;
        $chainId = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getChainId();
        if ( $chainId !== intval( $blockchainId ) ) {
            return $etherscanApiKey;
        }
        $options = stripslashes_deep( $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_options );
        $etherscanApiKey = ( !empty( $options['etherscanApiKey'] ) ? $options['etherscanApiKey'] : '' );
        return $etherscanApiKey;
    },
    20,
    2
);
/**
 * ETH price in $
 *
 * @param double $orig_price
 * @param type $product
 * @param bool $sale
 * @return double
 */
function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_ETH_rate(  $orig_price  ) {
    $product = null;
    $product_id = null;
    $sale = false;
    return $orig_price;
}

add_action(
    'cryptocurrency_product_for_woocommerce_save_option_field',
    'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_ETHER_save_option_field_hook',
    10,
    3
);
function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_ETHER_save_option_field_hook(  $cryptocurrency_option, $post_id, $product  ) {
    global $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_options;
    // if ('Ether' !== $cryptocurrency_option) {
    //     return;
    // }
    $product_id = $post_id;
    $vendor_id = get_post_field( 'post_author_override', $product_id );
    if ( empty( $vendor_id ) ) {
        $vendor_id = get_post_field( 'post_author', $product_id );
    }
    if ( !CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_is_vendor_user( $vendor_id ) ) {
        // process only vendor's products
        return $post_id;
    }
    $vendor_fee = 0;
    if ( $vendor_fee <= 0 ) {
        return $post_id;
    }
    // Ether rate
    $rate = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_ETH_rate( 1 );
    if ( is_null( $rate ) ) {
        CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( 'Failed to get Ether rate' );
        return $post_id;
    }
    $eth_value = $vendor_fee / $rate;
    $eth_value_wei = _CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_double_int_multiply( $eth_value, pow( 10, 18 ) );
    // 1. check balance
    $thisWalletAddress = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getHotWalletAddress( $product_id );
    $providerUrl = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getWeb3Endpoint();
    try {
        $eth_balance = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getBalanceEth( $thisWalletAddress, $providerUrl );
        if ( null === $eth_balance || $eth_balance->compare( $eth_value_wei ) < 0 ) {
            // @see https://wordpress.stackexchange.com/a/42178/137915
            // unhook this function to prevent indefinite loop
            remove_action( 'save_post', 'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_process_meta' );
            // update the post to change post status
            wp_update_post( array(
                'ID'          => $post_id,
                'post_status' => 'draft',
            ) );
            // re-hook this function again
            add_action( 'save_post', 'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_process_meta' );
        }
    } catch ( Exception $ex ) {
        CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_ETHER_save_option_field_hook: " . $ex->getMessage() );
    }
}

// @see https://wordpress.stackexchange.com/a/110052/137915
function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_transition_post_status(  $new_status, $old_status, $post  ) {
    global $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_options;
    if ( !current_user_can( 'administrator' ) ) {
        return;
    }
    if ( !($old_status != 'publish' && $new_status == 'publish' && !empty( $post->ID ) && in_array( $post->post_type, ['product'] )) ) {
        return;
    }
    $product_id = $post->ID;
    if ( !_CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_is_cryptocurrency( $product_id ) ) {
        return;
    }
    $vendor_id = get_post_field( 'post_author_override', $product_id );
    if ( empty( $vendor_id ) ) {
        $vendor_id = get_post_field( 'post_author', $product_id );
    }
    if ( !CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_is_vendor_user( $vendor_id ) ) {
        // process only vendor's products
        return;
    }
    $vendor_fee = 0;
    if ( $vendor_fee <= 0 ) {
        return;
    }
    // Ether rate
    $rate = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_ETH_rate( 1 );
    if ( is_null( $rate ) ) {
        CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( 'Failed to get Ether rate' );
        return;
    }
    CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( 'Ether rate: ' . $rate );
    $eth_value = $vendor_fee / $rate;
    $eth_value_wei = _CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_double_int_multiply( $eth_value, pow( 10, 18 ) );
    // 1. check balance
    $thisWalletAddress = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getHotWalletAddress( $product_id );
    $providerUrl = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getWeb3Endpoint();
    try {
        $eth_balance = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getBalanceEth( $thisWalletAddress, $providerUrl );
        if ( null === $eth_balance || $eth_balance->compare( $eth_value_wei ) < 0 ) {
            $eth_balance_str = $eth_balance->toString();
            $eth_value_wei_str = $eth_value_wei->toString();
            CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "Failed to take vendor fee: insufficient Ether balance: eth_balance_wei({$eth_balance_str}) < eth_value_wei({$eth_value_wei_str})" );
            return;
        }
        CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_ETHER_enqueue_send_ether_task(
            null,
            null,
            CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getHotWalletAddress(),
            $eth_value,
            $providerUrl,
            0,
            $vendor_id
        );
    } catch ( Exception $ex ) {
        CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_transition_post_status: " . $ex->getMessage() );
    }
}

add_action(
    'transition_post_status',
    'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_transition_post_status',
    10,
    3
);
function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_default_gas_price_wei() {
    global $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_options;
    $gasPriceMaxGwei = doubleval( ( isset( $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_options['gas_price'] ) ? $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_options['gas_price'] : '21' ) );
    return array(
        'tm'            => time(),
        'gas_price'     => intval( floatval( $gasPriceMaxGwei ) * 1000000000 ),
        'gas_price_tip' => null,
    );
}

function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_query_web3_gas_price_wei() {
    try {
        $httpProvider = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getHttpProvider();
        $web3 = new \Web3\Web3($httpProvider);
        $eth = $web3->eth;
        $ret = null;
        $eth->gasPrice( function ( $err, $gasPrice ) use(&$ret) {
            if ( $err !== null ) {
                CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "Failed to get gasPrice: ", $err );
                return;
            }
            $ret = $gasPrice;
        } );
        if ( is_null( $ret ) ) {
            return null;
        }
        return $ret->toString();
    } catch ( Exception $ex ) {
        CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_query_web3_gas_price_wei: " . $ex->getMessage() );
    }
    return 0;
}

function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_query_gas_price_wei() {
    $gasPriceWei = null;
    $gasPriceTipWei = null;
    $default_gas_price_wei = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_default_gas_price_wei();
    try {
        $httpProvider = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getHttpProvider();
        $web3 = new \Web3\Web3($httpProvider);
        $eth = $web3->eth;
        $isEIP1559 = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_isEIP1559( $eth );
        if ( !$isEIP1559 ) {
            CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_query_gas_price_wei: !isEIP1559" );
            $gasPriceWei = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_query_web3_gas_price_wei();
        } else {
            CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_query_gas_price_wei: isEIP1559" );
            list( $error, $block ) = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getLatestBlock( $eth );
            if ( !is_null( $error ) ) {
                CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_query_gas_price_wei: Failed to get block: " . $error );
                return $default_gas_price_wei;
            }
            $gasPriceAndTipWei = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_query_web3_gas_price_wei();
            CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_query_gas_price_wei: gasPriceAndTipWei: " . $gasPriceAndTipWei );
            $gasPriceTipWeiBI = ( new \phpseclib3\Math\BigInteger($gasPriceAndTipWei) )->subtract( new \phpseclib3\Math\BigInteger($block->baseFeePerGas, 16) );
            CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_query_gas_price_wei: gasPriceTipWeiBI: " . $gasPriceTipWeiBI->toString() . '; baseFeePerGas = ' . ( new \phpseclib3\Math\BigInteger($block->baseFeePerGas, 16) )->toString() );
            if ( $gasPriceTipWeiBI->compare( new \phpseclib3\Math\BigInteger(0) ) < 0 ) {
                $gasPriceTipWeiBI = new \phpseclib3\Math\BigInteger(1000000000);
                // 1 Gwei
                CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_query_gas_price_wei1: gasPriceTipWeiBI: " . $gasPriceTipWeiBI->toString() );
            }
            $default_gas_price_wei_BI = new \phpseclib3\Math\BigInteger($default_gas_price_wei['gas_price']);
            if ( $default_gas_price_wei_BI->compare( $gasPriceTipWeiBI ) < 0 ) {
                $gasPriceTipWeiBI = $default_gas_price_wei_BI;
                CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_query_gas_price_wei2: gasPriceTipWeiBI: " . $gasPriceTipWeiBI->toString() );
            }
            $gasPriceTipWei = $gasPriceTipWeiBI->toString();
            $gasPriceWei = ( new \phpseclib3\Math\BigInteger($block->baseFeePerGas, 16) )->multiply( new \phpseclib3\Math\BigInteger(2) )->add( $gasPriceTipWeiBI );
            $gasPriceWei = $gasPriceWei->toString();
            if ( '0' === $gasPriceWei ) {
                CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_query_gas_price_wei: 0 === gasPriceWei: " . $block->baseFeePerGas . ', bn=' . ( new \phpseclib3\Math\BigInteger($block->baseFeePerGas) )->toString() . '; block=' . print_r( $block, true ) );
                return $default_gas_price_wei;
            }
        }
    } catch ( Exception $ex ) {
        CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_query_gas_price_wei: " . $ex->getMessage() );
        return $default_gas_price_wei;
    }
    if ( is_null( $gasPriceWei ) ) {
        CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_query_gas_price_wei: is_null(gasPriceWei)" );
        return $default_gas_price_wei;
    }
    $cache_gas_price = array(
        'tm'            => time(),
        'gas_price'     => $gasPriceWei,
        'gas_price_tip' => $gasPriceTipWei,
    );
    $chainId = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getChainId();
    if ( null === $chainId ) {
        CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( sprintf( __( 'Configuration error! The "%s" setting is not set.', 'cryptocurrency-product-for-woocommerce' ), __( "Blockchain", 'cryptocurrency-product-for-woocommerce' ) ) );
        return $default_gas_price_wei;
    }
    $option_name = 'ethereumicoio_cache_gas_price-wei-' . $chainId;
    if ( get_option( $option_name ) ) {
        update_option( $option_name, $cache_gas_price );
    } else {
        $deprecated = '';
        $autoload = 'no';
        add_option(
            $option_name,
            $cache_gas_price,
            $deprecated,
            $autoload
        );
    }
    return $cache_gas_price;
}

function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_gas_price_wei(  $timeout = null  ) {
    $chainId = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getChainId();
    if ( null === $chainId ) {
        CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( sprintf( __( 'Configuration error! The "%s" setting is not set.', 'cryptocurrency-product-for-woocommerce' ), __( "Blockchain", 'cryptocurrency-product-for-woocommerce' ) ) );
        return null;
    }
    $option_name = 'ethereumicoio_cache_gas_price-wei-' . $chainId;
    $cache_gas_price_wei = get_option( $option_name, array() );
    if ( !$cache_gas_price_wei ) {
        $cache_gas_price_wei = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_query_gas_price_wei();
    }
    // CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log('CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_gas_price_wei: $cache_gas_price_wei: ' . print_r($cache_gas_price_wei, true) . '; time = ' . time());
    $tm_diff = time() - intval( $cache_gas_price_wei['tm'] );
    if ( is_null( $timeout ) ) {
        // seconds
        $blockchainInterblockPeriodSeconds = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getBlockchainInterblockPeriodSeconds();
        $timeout = 5 * $blockchainInterblockPeriodSeconds;
    }
    if ( $tm_diff > $timeout ) {
        $cache_gas_price_wei = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_query_gas_price_wei();
    }
    $gasPriceWei = doubleval( $cache_gas_price_wei['gas_price'] );
    if ( is_null( $cache_gas_price_wei['gas_price_tip'] ) ) {
        // only if pre-EIP1559
        $gasPriceMaxWei = doubleval( CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_default_gas_price_wei()['gas_price'] );
        if ( $gasPriceMaxWei < $gasPriceWei ) {
            $gasPriceWei = $gasPriceMaxWei;
        }
    }
    return intval( $gasPriceWei );
}

function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_gas_price_tip_wei(  $timeout = null  ) {
    $chainId = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getChainId();
    if ( null === $chainId ) {
        CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( sprintf( __( 'Configuration error! The "%s" setting is not set.', 'cryptocurrency-product-for-woocommerce' ), __( "Blockchain", 'cryptocurrency-product-for-woocommerce' ) ) );
        return null;
    }
    $option_name = 'ethereumicoio_cache_gas_price-wei-' . $chainId;
    $cache_gas_price_wei = get_option( $option_name, array() );
    if ( !$cache_gas_price_wei ) {
        $cache_gas_price_wei = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_query_gas_price_wei();
        CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( '!$cache_gas_price_wei: ' . print_r( $cache_gas_price_wei, true ) );
    }
    // CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log('CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_gas_price_tip_wei: $cache_gas_price_wei: ' . print_r($cache_gas_price_wei, true) . '; time = ' . time());
    $tm_diff = time() - intval( $cache_gas_price_wei['tm'] );
    if ( is_null( $timeout ) ) {
        // seconds
        $blockchainInterblockPeriodSeconds = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getBlockchainInterblockPeriodSeconds();
        $timeout = 5 * $blockchainInterblockPeriodSeconds;
    }
    if ( $tm_diff > $timeout ) {
        $cache_gas_price_wei = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_query_gas_price_wei();
        CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( '$tm_diff > $timeout: ' . print_r( $cache_gas_price_wei, true ) );
    }
    if ( is_null( $cache_gas_price_wei['gas_price_tip'] ) ) {
        CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( 'is_null($cache_gas_price_wei[\'gas_price_tip\']): ' . print_r( $cache_gas_price_wei, true ) );
        return null;
    }
    $gasPriceTipWei = doubleval( $cache_gas_price_wei['gas_price_tip'] );
    if ( !is_null( CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_default_gas_price_wei()['gas_price'] ) ) {
        $gasPriceTipMaxWei = doubleval( CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_default_gas_price_wei()['gas_price'] );
        if ( $gasPriceTipMaxWei < $gasPriceTipWei ) {
            $gasPriceTipWei = $gasPriceTipMaxWei;
        }
    }
    return intval( $gasPriceTipWei );
}

function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_isEIP1559(  $eth = null  ) {
    $chainId = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getChainId();
    if ( null === $chainId ) {
        CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( sprintf( __( 'Configuration error! The "%s" setting is not set.', 'cryptocurrency-product-for-woocommerce' ), __( "Blockchain", 'cryptocurrency-product-for-woocommerce' ) ) );
        return null;
    }
    $option_name = 'ethereumicoio_cache_is-eip-1559-network-' . $chainId;
    // delete_option($option_name);
    $isEIP1559Option = get_option( $option_name, null );
    $tm_diff = time() - intval( ( !is_null( $isEIP1559Option ) ? $isEIP1559Option['tm'] : time() ) );
    // TODO: admin setting
    $timeout = 10 * 60;
    // seconds
    // list($error, $block) = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getLatestBlock($eth);
    $isEIP1559 = ( !is_null( $isEIP1559Option ) ? $isEIP1559Option['isEIP1559'] : null );
    // CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log($option_name . ' : isEIP1559=' . (is_null($isEIP1559) ? 'null' : ($isEIP1559 ? 'true' : 'false')));
    if ( is_null( $isEIP1559 ) || $tm_diff > $timeout ) {
        list( $error, $block ) = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getLatestBlock( $eth );
        if ( !is_null( $error ) ) {
            CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "Failed to get block: " . $error );
            return null;
        }
        if ( is_null( $block ) ) {
            CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "Failed to get block: block is null" );
            return null;
        }
        $isEIP1559 = property_exists( $block, 'baseFeePerGas' );
        // CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log('CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_isEIP1559: isEIP1559=' . (is_null($isEIP1559) ? 'null' : ($isEIP1559 ? 'true' : 'false')));
        // isset($block['baseFeePerGas']);
        if ( get_option( $option_name ) ) {
            update_option( $option_name, [
                'isEIP1559' => $isEIP1559,
                'tm'        => time(),
            ] );
        } else {
            $deprecated = '';
            $autoload = 'no';
            add_option(
                $option_name,
                [
                    'isEIP1559' => $isEIP1559,
                    'tm'        => time(),
                ],
                $deprecated,
                $autoload
            );
        }
        return $isEIP1559;
    }
    return $isEIP1559;
}

//----------------------------------------------------------------------------//
//                     Shipping field for crypto-address                      //
//----------------------------------------------------------------------------//
add_filter(
    'cryptocurrency_product_for_woocommerce_override_checkout_fields',
    'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_ETHER_ALL_override_checkout_fields',
    20,
    2
);
function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_ETHER_ALL_override_checkout_fields(  $fields, $cryptocurrency_option  ) {
    global $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_options;
    if ( "ETHEREUM" !== apply_filters( 'cryptocurrency_product_for_woocommerce_get_base_blockchain', '', $cryptocurrency_option ) ) {
        return $fields;
    }
    $user_id = get_current_user_id();
    $isUserLoggedIn = $user_id > 0;
    $walletDisabled = ( isset( $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_options['wallet_field_disable'] ) ? esc_attr( $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_options['wallet_field_disable'] ) : '' );
    $isWalletFieldDisabled = !empty( $walletDisabled );
    $isWalletAddressGeneratorExists = apply_filters( 'cryptocurrency_product_for_woocommerce_is_wallet_address_generator_exists', false, $cryptocurrency_option );
    $isWCGuestBuyAllowed = !WC()->checkout()->is_registration_required();
    $isWCCheckoutRegistrationEnabled = WC()->checkout()->is_registration_enabled();
    $isWalletAddressFieldRequired = $isUserLoggedIn || !$isWalletAddressGeneratorExists || $isWCGuestBuyAllowed && !$isWCCheckoutRegistrationEnabled;
    if ( !wp_doing_ajax() && !($isUserLoggedIn || !$isWalletAddressGeneratorExists || $isWCGuestBuyAllowed) ) {
        ?>
        <script type='text/javascript'>
            jQuery(document).ready(function() {
                setTimeout(function() {
                    jQuery('#billing_cryptocurrency_ethereum_address_field').hide();
                }, 100);
            });
        </script>
    <?php 
    }
    if ( !$isWalletAddressFieldRequired ) {
        $label = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_geEthereumAddressFieldLabel();
        $required = $label . '&nbsp;<abbr class="required" title="' . esc_attr__( 'required', 'woocommerce' ) . '">*</abbr>';
        $optional = $label . '&nbsp;<span class="optional">(' . esc_html__( 'optional', 'woocommerce' ) . ')</span>';
        ?>
        <script type='text/javascript'>
            jQuery(document).ready(function() {
                jQuery('#createaccount').on('change', function(e) {
                    if (jQuery(this).is(':checked')) {
                        jQuery('#billing_cryptocurrency_ethereum_address_field > label').html('<?php 
        echo $optional;
        ?>');
                        jQuery('#billing_cryptocurrency_ethereum_address_field').removeClass("validate-required");
                        jQuery('#billing_cryptocurrency_ethereum_address_field').removeClass("validate-ethereum-address");
                    } else {
                        jQuery('#billing_cryptocurrency_ethereum_address_field > label').html('<?php 
        echo $required;
        ?>');
                        jQuery('#billing_cryptocurrency_ethereum_address_field').addClass("validate-required");
                        jQuery('#billing_cryptocurrency_ethereum_address_field').addClass("validate-ethereum-address");
                    }
                }).change();
            });
        </script>
    <?php 
    }
    $value = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_billing_cryptocurrency_address( $cryptocurrency_option );
    if ( !empty( $value ) && !wp_doing_ajax() ) {
        ?>
        <script type='text/javascript'>
            if (typeof jQuery === 'undefined') {
                document.addEventListener("DOMContentLoaded", function() {
                    setTimeout(function() {
                        document.getElementById('billing_cryptocurrency_ethereum_address').value = "<?php 
        echo $value;
        ?>";
                        document.getElementsByName('billing_cryptocurrency_ethereum_address').forEach(function(el) {
                            el.value = "<?php 
        echo $value;
        ?>";
                        });
                    }, 100);
                });
            } else {
                jQuery(document).ready(function() {
                    setTimeout(function() {
                        jQuery('#billing_cryptocurrency_ethereum_address').val("<?php 
        echo $value;
        ?>");
                        jQuery('input[name=billing_cryptocurrency_ethereum_address]').val("<?php 
        echo $value;
        ?>");
                    }, 100);
                });
            }
        </script>
<?php 
    }
    $custom_attributes = array();
    if ( !empty( $value ) && $isWalletFieldDisabled ) {
        $custom_attributes['readonly'] = 'readonly';
    }
    $fields['billing']['billing_cryptocurrency_ethereum_address'] = array(
        'label'             => CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_geEthereumAddressFieldLabel(),
        'placeholder'       => _x( '0x', 'placeholder', 'cryptocurrency-product-for-woocommerce' ),
        'required'          => $isWalletAddressFieldRequired,
        'class'             => array('form-row-wide'),
        'clear'             => true,
        'value'             => $value,
        'custom_attributes' => $custom_attributes,
    );
    return $fields;
}

/**
 * Display field value on the order edit page
 */
/* Display additional billing fields (email, phone) in ADMIN area (i.e. Order display ) */
/* Note:  $fields keys (i.e. field names) must be in format:  WITHOUT the "billing_" prefix (it's added by the code) */
add_filter( 'woocommerce_admin_billing_fields', 'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_additional_admin_billing_fields' );
function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_additional_admin_billing_fields(  $fields  ) {
    $fields['cryptocurrency_ethereum_address'] = array(
        'label' => CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_geEthereumAddressFieldLabel(),
    );
    return $fields;
}

/* Display additional billing fields (email, phone) in USER area (i.e. Admin User/Customer display ) */
/* Note:  $fields keys (i.e. field names) must be in format: billing_ */
add_filter( 'woocommerce_customer_meta_fields', 'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_additional_customer_meta_fields' );
function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_additional_customer_meta_fields(  $fields  ) {
    $fields['billing']['fields']['billing_cryptocurrency_ethereum_address'] = array(
        'label'       => CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_geEthereumAddressFieldLabel(),
        'description' => '',
    );
    return $fields;
}

add_filter( 'woocommerce_default_address_fields', 'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_default_address_fields' );
function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_default_address_fields(  $fields  ) {
    if ( is_checkout() ) {
        return $fields;
    }
    if ( !_CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_is_cryptocurrency_product_in_cart() ) {
        return $fields;
    }
    $user_id = get_current_user_id();
    if ( 0 !== $user_id ) {
        $fields = array_merge( $fields, [
            'billing_cryptocurrency_ethereum_address' => array(
                'label'        => CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_geEthereumAddressFieldLabel(),
                'label_class'  => array('screen-reader-text'),
                'placeholder'  => esc_attr( _x( '0x', 'placeholder', 'cryptocurrency-product-for-woocommerce' ) ),
                'class'        => array('form-row-wide', 'address-field'),
                'autocomplete' => 'ethereum-wallet-address',
                'priority'     => 1000,
                'required'     => 'required' === get_option( 'woocommerce_checkout_address_2_field', 'optional' ),
            ),
        ] );
        $fields['billing']['fields']['billing_cryptocurrency_ethereum_address'] = array(
            'label'       => CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_geEthereumAddressFieldLabel(),
            'description' => '',
        );
    }
    return $fields;
}

add_filter(
    'cryptocurrency_product_for_woocommerce_get_order_txhash',
    'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_ETHER_get_order_txhash',
    10,
    4
);
function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_ETHER_get_order_txhash(
    $fields,
    $cryptocurrency_option,
    $order_id,
    $product_id
) {
    $txhash = _CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_post_meta( $order_id, 'ether_txhash_' . $product_id, true );
    if ( !empty( $txhash ) ) {
        $fields['cryptocurrency_ether_txhash_' . $product_id] = array(
            'label' => __( 'Crypto Tx Hash', 'cryptocurrency-product-for-woocommerce' ),
            'value' => $txhash,
        );
    }
    return $fields;
}

// Adding Meta container admin shop_order pages
add_action(
    'cryptocurrency_product_for_woocommerce_add_meta_boxes',
    'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_ETHER_ALL_add_meta_boxes',
    20,
    1
);
function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_ETHER_ALL_add_meta_boxes(  $cryptocurrency_option  ) {
    if ( "ETHEREUM" !== apply_filters( 'cryptocurrency_product_for_woocommerce_get_base_blockchain', '', $cryptocurrency_option ) ) {
        return;
    }
    add_meta_box(
        'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_other_fields',
        CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_geEthereumAddressFieldLabel(),
        'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_ETHER_add_other_fields_for_packaging',
        'shop_order',
        'side',
        'core'
    );
}

// Adding Meta field in the meta container admin shop_order pages
function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_ETHER_add_other_fields_for_packaging() {
    global $post;
    if ( 'shop_order' !== get_post_type( $post ) ) {
        return;
    }
    $order_id = $post->ID;
    $cryptocurrency_option = _CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_cryptocurrency_product_type_in_order( $order_id );
    $_billing_cryptocurrency_address = apply_filters(
        'cryptocurrency_product_for_woocommerce_get_cryptocurrency_address',
        '',
        $cryptocurrency_option,
        $order_id
    );
    $meta_field_data = $_billing_cryptocurrency_address;
    echo '<input type="hidden" name="CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_' . $cryptocurrency_option . '_other_meta_field_nonce" value="' . wp_create_nonce() . '">
    <p style="border-bottom:solid 1px #eee;padding-bottom:13px;">
        <input type="text" style="width:250px;";" name="_billing_cryptocurrency_ethereum_address_input" placeholder="' . $meta_field_data . '" value="' . $meta_field_data . '"></p>';
}

add_action(
    'cryptocurrency_product_for_woocommerce_save_wc_order_other_fields',
    'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_ETHER_ALL_save_wc_order_other_fields_hook',
    10,
    2
);
function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_ETHER_ALL_save_wc_order_other_fields_hook(  $cryptocurrency_option, $post_id  ) {
    if ( "ETHEREUM" !== apply_filters( 'cryptocurrency_product_for_woocommerce_get_base_blockchain', '', $cryptocurrency_option ) ) {
        return;
    }
    // Sanitize user input  and update the meta field in the database.
    $_billing_cryptocurrency_ethereum_address_input = sanitize_text_field( $_POST['_billing_cryptocurrency_ethereum_address_input'] );
    $order_id = $post_id;
    $order = wc_get_order( $order_id );
    if ( !$order ) {
        return;
    }
    if ( empty( $_billing_cryptocurrency_ethereum_address_input ) ) {
        // @see https://stackoverflow.com/a/43815280/4256005
        // Get an instance of the WC_Order object
        // Get the user ID from WC_Order methods
        $user_id = $order->get_user_id();
        // or $order->get_customer_id();
        if ( $user_id >= 0 ) {
            $_billing_cryptocurrency_ethereum_address_input = get_user_meta( $user_id, 'user_ethereum_wallet_address', true );
        }
    }
    $_billing_cryptocurrency_ethereum_address_input_old = _CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_post_meta( $post_id, '_billing_cryptocurrency_ethereum_address', true );
    if ( $_billing_cryptocurrency_ethereum_address_input_old != $_billing_cryptocurrency_ethereum_address_input ) {
        update_post_meta( $post_id, '_billing_cryptocurrency_ethereum_address', $_billing_cryptocurrency_ethereum_address_input );
        // Is this a note for the customer?
        $is_customer_note = 1;
        $order->add_order_note( sprintf( __( '%1$s set to %2$s', 'cryptocurrency-product-for-woocommerce' ), CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_geEthereumAddressFieldLabel(), $_billing_cryptocurrency_ethereum_address_input ), $is_customer_note );
    }
}

add_action(
    'cryptocurrency_product_for_woocommerce_checkout_update_order_meta',
    'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_ETHER_ALL_checkout_update_order_meta_hook',
    10,
    3
);
function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_ETHER_ALL_checkout_update_order_meta_hook(  $cryptocurrency_option, $order_id, $data  ) {
    if ( "ETHEREUM" !== apply_filters( 'cryptocurrency_product_for_woocommerce_get_base_blockchain', '', $cryptocurrency_option ) ) {
        return;
    }
    if ( isset( $data['billing_cryptocurrency_ethereum_address'] ) ) {
        update_post_meta( $order_id, '_billing_cryptocurrency_ethereum_address', $data['billing_cryptocurrency_ethereum_address'] );
    }
}

add_filter(
    'cryptocurrency_product_for_woocommerce_get_base_blockchain',
    'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_ETHER_get_base_blockchain',
    20,
    2
);
function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_ETHER_get_base_blockchain(  $blockchain, $cryptocurrency_option  ) {
    if ( !in_array( $cryptocurrency_option, ['Ether'] ) ) {
        return $blockchain;
    }
    return "ETHEREUM";
}

add_action(
    "CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_ETHER_send_ether",
    "CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_ETHER_send_ether",
    0,
    6
);
function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_ETHER_send_ether(
    $order_id,
    $product_id,
    $marketAddress,
    $eth_value,
    $providerUrl,
    $from_user_id
) {
    global $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_options;
    $order = wc_get_order( $order_id );
    if ( !$order ) {
        CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_ETHER_send_ether: Order {$order_id} was not found. Skip payment." );
        return;
    }
    if ( !$order->has_status( "processing" ) ) {
        CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_ETHER_send_ether: Order {$order_id} status is not eligible for processing. Skip payment." );
        return;
    }
    if ( !is_null( $order_id ) ) {
        if ( CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_ETHER_is_order_payed( $order_id, $product_id ) ) {
            // already payed
            $txhash = _CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_post_meta( $order_id, 'ether_txhash_' . $product_id, true );
            CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "Ether payment found for order {$order_id}, product {$product_id}: {$txhash}. Skip payment." );
            return;
        }
    }
    // проверить, есть ли прошлая транзакция
    $thisWalletAddress = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getHotWalletAddress( $product_id, $from_user_id );
    if ( is_null( $thisWalletAddress ) ) {
        CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( sprintf( __( 'Configuration error! The "%s" setting is not set.', 'cryptocurrency-product-for-woocommerce' ), __( "Ethereum wallet address", 'cryptocurrency-product-for-woocommerce' ) ) );
        return false;
    }
    $lasttxhash = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_last_txhash( $thisWalletAddress );
    $txhash = null;
    $nonce = null;
    $canceled = false;
    try {
        if ( $lasttxhash ) {
            // если есть, проверить, завершена ли она
            $lastnonce = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_last_nonce( $thisWalletAddress );
            $tx_confirmed = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_is_tx_confirmed(
                $lasttxhash,
                $lastnonce,
                null,
                $product_id,
                $from_user_id
            );
            if ( $tx_confirmed ) {
                //   - да, послать новую транзакцию
                list( $txhash, $nonce, $canceled ) = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_ETHER_send_ether_impl(
                    $order_id,
                    $product_id,
                    $marketAddress,
                    $eth_value,
                    $providerUrl,
                    $from_user_id
                );
            } else {
                if ( is_null( $tx_confirmed ) ) {
                    // nonce in last tx is outdated. remove it
                    CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_delete_last_txhash( $thisWalletAddress );
                    // CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_cancel_complete_order_task($order_id, $txhash, $nonce);
                    CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_clean_order_product_txhashes( $lasttxhash, $order_id, $product_id );
                }
            }
        } else {
            // нет последней транзакции. послать новую транзакцию
            list( $txhash, $nonce, $canceled ) = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_ETHER_send_ether_impl(
                $order_id,
                $product_id,
                $marketAddress,
                $eth_value,
                $providerUrl,
                $from_user_id
            );
        }
        if ( $txhash ) {
            // успех - запомнить новую последнюю транзакцию
            CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_set_last_txhash( $thisWalletAddress, $txhash, $nonce );
            // поставить задачу отслеживания состояния транзакции
            if ( !is_null( $order_id ) ) {
                CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_enqueue_complete_order_task( $order_id, $product_id );
            }
        } else {
            if ( !$canceled ) {
                // Неуспех - поставить себя снова в очередь
                // wait 5 blocks
                $blockchainInterblockPeriodSeconds = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getBlockchainInterblockPeriodSeconds();
                $offset = 5 * $blockchainInterblockPeriodSeconds;
                CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_ETHER_enqueue_send_ether_task(
                    $order_id,
                    $product_id,
                    $marketAddress,
                    $eth_value,
                    $providerUrl,
                    $offset,
                    $from_user_id
                );
            }
        }
    } catch ( Exception $ex ) {
        CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_ETHER_send_ether: " . $ex->getMessage() );
        $offset = 60;
        CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_ETHER_enqueue_send_ether_task(
            $order_id,
            $product_id,
            $marketAddress,
            $eth_value,
            $providerUrl,
            $offset,
            $from_user_id
        );
    }
    return true;
}

// Takes a hex (string) address as input
function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_checksum_encode(  $addr_str  ) {
    $out = array();
    $addr = str_replace( '0x', '', strtolower( $addr_str ) );
    $addr_array = str_split( $addr );
    $hash_addr = \kornrunner\Keccak::hash( $addr, 256 );
    $hash_addr_array = str_split( $hash_addr );
    for ($idx = 0; $idx < count( $addr_array ); $idx++) {
        $ch = $addr_array[$idx];
        if ( (int) hexdec( $hash_addr_array[$idx] ) >= 8 ) {
            $out[] = strtoupper( $ch ) . '';
        } else {
            $out[] = $ch . '';
        }
    }
    return '0x' . implode( '', $out );
}

// create Ethereum wallet on user register
// see https://wp-kama.ru/hook/user_register
function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_address_from_key(  $privateKeyHex  ) {
    $privateKeyFactory = new \BitWasp\Bitcoin\Key\Factory\PrivateKeyFactory();
    $privateKey = $privateKeyFactory->fromHexUncompressed( $privateKeyHex );
    $pubKeyHex = $privateKey->getPublicKey()->getHex();
    $hash = \kornrunner\Keccak::hash( substr( hex2bin( $pubKeyHex ), 1 ), 256 );
    $ethAddress = '0x' . substr( $hash, 24 );
    $ethAddressChkSum = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_checksum_encode( $ethAddress );
    return $ethAddressChkSum;
}

function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_create_account() {
    $random = new \BitWasp\Bitcoin\Crypto\Random\Random();
    $privateKeyBuffer = $random->bytes( 32 );
    $privateKeyHex = $privateKeyBuffer->getHex();
    $ethAddressChkSum = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_address_from_key( $privateKeyHex );
    return [$ethAddressChkSum, $privateKeyHex];
}

function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_ETHER_send_ether_impl(
    $order_id,
    $product_id,
    $marketAddress,
    $eth_value,
    $providerUrl,
    $from_user_id
) {
    global $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_options;
    $thisWalletAddress = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getHotWalletAddress( $product_id, $from_user_id );
    if ( is_null( $thisWalletAddress ) ) {
        CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( sprintf( __( 'Configuration error! The "%s" setting is not set.', 'cryptocurrency-product-for-woocommerce' ), __( "Ethereum wallet address", 'cryptocurrency-product-for-woocommerce' ) ) );
        if ( !is_null( $order_id ) ) {
            update_post_meta( $order_id, 'status', __( 'Configuration error', 'cryptocurrency-product-for-woocommerce' ) );
        }
        return null;
    }
    $chainId = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getChainId();
    if ( null === $chainId ) {
        CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( sprintf( __( 'Configuration error! The "%s" setting is not set.', 'cryptocurrency-product-for-woocommerce' ), __( "Blockchain", 'cryptocurrency-product-for-woocommerce' ) ) );
        if ( !is_null( $order_id ) ) {
            update_post_meta( $order_id, 'status', __( 'Configuration error', 'cryptocurrency-product-for-woocommerce' ) );
        }
        return null;
    }
    $eth_value_wei = _CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_double_int_multiply( $eth_value, pow( 10, 18 ) );
    $eth_value_wei_str = $eth_value_wei->toString();
    // 1. check balance
    $blockchainNetwork = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getBlockchainNetwork();
    $httpProvider = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getHttpProvider();
    $web3 = new \Web3\Web3($httpProvider);
    $eth = $web3->eth;
    $eth_balance = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getBalanceEth( $thisWalletAddress, $providerUrl, $eth );
    if ( null === $eth_balance ) {
        CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "eth_balance is null" );
        if ( !is_null( $order_id ) ) {
            update_post_meta( $order_id, 'status', __( 'Network error', 'cryptocurrency-product-for-woocommerce' ) );
        }
        return null;
    }
    if ( $eth_balance->compare( $eth_value_wei ) < 0 ) {
        $eth_balance_str = $eth_balance->toString();
        CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "eth_balance_wei({$eth_balance_str}) < eth_value_wei({$eth_value_wei_str}) for {$order_id}" );
        if ( !is_null( $order_id ) ) {
            update_post_meta( $order_id, 'status', __( 'Insufficient funds', 'cryptocurrency-product-for-woocommerce' ) );
            // Load the order.
            $order = wc_get_order( $order_id );
            // Place the order to failed.
            $res = $order->update_status( 'failed', sprintf(
                __( '%3$s balance (%1$s wei) is less then the value requested: %2$s wei.', 'cryptocurrency-product-for-woocommerce' ),
                $eth_balance_str,
                $eth_value_wei_str,
                CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getBlockchainCurrencyTickerName()
            ) );
            if ( !$res ) {
                // failed to complete order
                CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "Failed to fail order: " . $order_id );
            }
        }
        CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_delete_last_txhash( $thisWalletAddress );
        return [null, null, true];
    }
    // 3. make payment if balance is enough
    $nonce = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_nonce( $thisWalletAddress, $providerUrl, $eth );
    if ( null === $nonce ) {
        CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "nonce is null" );
        if ( !is_null( $order_id ) ) {
            update_post_meta( $order_id, 'status', __( 'Network error', 'cryptocurrency-product-for-woocommerce' ) );
        }
        return null;
    }
    $gasLimit = intval( ( isset( $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_options['gas_limit'] ) ? $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_options['gas_limit'] : '200000' ) );
    $gasPrice = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_gas_price_wei( 20 );
    $gasPriceTip = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_gas_price_tip_wei( 20 );
    $thisWalletPrivKey = _CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getHotWalletPrivateKey( $product_id, $from_user_id );
    if ( is_null( $thisWalletPrivKey ) ) {
        CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( sprintf( __( 'Configuration error! The "%s" setting is not set.', 'cryptocurrency-product-for-woocommerce' ), __( "Ethereum wallet private key", 'cryptocurrency-product-for-woocommerce' ) ) . ". product_id=" . $product_id . "; thisWalletAddress=" . $thisWalletAddress );
        if ( !is_null( $order_id ) ) {
            update_post_meta( $order_id, 'status', __( 'Configuration error', 'cryptocurrency-product-for-woocommerce' ) );
        }
        return null;
    }
    $to = $marketAddress;
    $nonceb = \BitWasp\Buffertools\Buffer::int( $nonce );
    $gasPrice = \BitWasp\Buffertools\Buffer::int( $gasPrice );
    $gasLimit = \BitWasp\Buffertools\Buffer::int( $gasLimit );
    $transactionData = [
        'from'    => $thisWalletAddress,
        'nonce'   => '0x' . $nonceb->getHex(),
        'to'      => strtolower( $to ),
        'gas'     => '0x' . $gasLimit->getHex(),
        'value'   => '0x' . $eth_value_wei->toHex(),
        'chainId' => $chainId,
        'data'    => null,
    ];
    list( $error, $gasEstimate ) = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_gas_estimate( $transactionData, $eth );
    if ( null === $gasEstimate ) {
        CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "gasEstimate is null: " . $error );
        return null;
    }
    CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "gasEstimate: " . $gasEstimate->toHex() );
    if ( $gasLimit->getHex() === $gasEstimate->toHex() ) {
        CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "Too low gas limit specified in settings: " . $gasLimit->getHex() );
        return null;
    }
    $transactionData['gas'] = '0x' . $gasEstimate->toHex();
    unset($transactionData['from']);
    if ( is_null( $gasPriceTip ) ) {
        // pre-EIP1559
        $transactionData['gasPrice'] = '0x' . $gasPrice->getHex();
    } else {
        $transactionData['accessList'] = [];
        // EIP1559
        $transactionData['maxFeePerGas'] = '0x' . $gasPrice->getHex();
        $gasPriceTip = \BitWasp\Buffertools\Buffer::int( $gasPriceTip );
        $transactionData['maxPriorityFeePerGas'] = '0x' . $gasPriceTip->getHex();
    }
    $transaction = new \Web3p\EthereumTx\Transaction($transactionData);
    $signedTransaction = "0x" . $transaction->sign( $thisWalletPrivKey );
    $txHash = null;
    $eth->sendRawTransaction( (string) $signedTransaction, function ( $err, $transaction ) use(&$txHash, &$transactionData, $signedTransaction) {
        if ( $err !== null ) {
            CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "Failed to sendRawTransaction: " . $err );
            CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "Failed to sendRawTransaction: transactionData=" . print_r( $transactionData, true ) );
            CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "Failed to sendRawTransaction: signedTransaction=" . (string) $signedTransaction );
            return;
        }
        $txHash = $transaction;
    } );
    if ( null === $txHash ) {
        if ( !is_null( $order_id ) ) {
            update_post_meta( $order_id, 'status', __( 'Network error', 'cryptocurrency-product-for-woocommerce' ) );
        }
        return null;
    }
    if ( !is_null( $order_id ) ) {
        CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_ETHER_set_order_txhash(
            $order_id,
            $product_id,
            $txHash,
            $blockchainNetwork
        );
        update_post_meta( $order_id, 'txdata_' . $product_id, sanitize_text_field( $signedTransaction ) );
    }
    // the remaining balance
    $eth_balance_remaining = $eth_balance->subtract( $eth_value_wei );
    list( $eth_balance_remaining, $_ ) = $eth_balance_remaining->divide( new \phpseclib3\Math\BigInteger(pow( 10, 9 )) );
    $eth_balance_f = doubleval( $eth_balance_remaining->toString() ) / pow( 10, 9 );
    if ( !is_null( $product_id ) ) {
        $product = wc_get_product( $product_id );
        if ( $product ) {
            $minimumValue = apply_filters( 'woocommerce_quantity_input_min', 0, $product );
            if ( empty( $minimumValue ) || 0 == $minimumValue ) {
                $minimumValue = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_woocommerce_quantity_input_min( 0.01, $product );
            }
            $status = 'outofstock';
            if ( doubleval( $minimumValue ) < $eth_balance_f ) {
                $status = 'instock';
            }
            wc_update_product_stock_status( $product_id, $status );
            //            // adjust stock quantity for fee
            //            list($eth_value_diff_wei, $_) = $eth_value_diff_wei->divide(new \phpseclib3\Math\BigInteger(pow(10, 9)));
            //            $eth_value_f = floatval(doubleval($eth_value_diff_wei->toString()) / pow(10, 9));
            //            // the fee amount to decrease stock
            //            $eth_value_diff_wei = $eth_value_wei->subtract($eth_value_wei0);
            //            // adjust stock quantity for fee
            //            list($eth_value_diff_wei, $_) = $eth_value_diff_wei->divide(new \phpseclib3\Math\BigInteger(pow(10, 9)));
            //            $eth_value_f = floatval(doubleval($eth_value_diff_wei->toString()) / pow(10, 9));
            //            wc_update_product_stock( $product, $eth_value_f, 'decrease');
        }
    }
    if ( !is_null( $order_id ) ) {
        update_post_meta( $order_id, 'status', __( 'Success', 'cryptocurrency-product-for-woocommerce' ) );
    }
    return array($txHash, $nonce, false);
}

function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_ETHER_get_txhash_path(  $txHash, $blockchainNetwork  ) {
    global $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_options;
    $chainId = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getChainId();
    $blockchainInfo = apply_filters( 'ethereumico.io/blockchain-info', null, $chainId );
    if ( is_null( $blockchainInfo ) ) {
        return $txHash;
    }
    $view_transaction_url = $blockchainInfo['txhash_path_template'];
    return sprintf( $view_transaction_url, $txHash );
}

function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_ETHER_get_address_path(  $address, $blockchainNetwork  ) {
    global $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_options;
    $chainId = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getChainId();
    $blockchainInfo = apply_filters( 'ethereumico.io/blockchain-info', null, $chainId );
    if ( is_null( $blockchainInfo ) ) {
        return $address;
    }
    $view_address_url = $blockchainInfo['address_path_template'];
    return sprintf( $view_address_url, $address );
}

function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_ETHER_set_order_txhash(
    $order_id,
    $product_id,
    $txHash,
    $blockchainNetwork
) {
    $txHashPath = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_ETHER_get_txhash_path( $txHash, $blockchainNetwork );
    $order = wc_get_order( $order_id );
    $order->add_order_note( sprintf( __( 'Sent to blockchain. Transaction hash  <a target="_blank" href="%1$s">%2$s</a>.', 'cryptocurrency-product-for-woocommerce' ), $txHashPath, $txHash ) );
    update_post_meta( $order_id, 'ether_txhash_' . $product_id, sanitize_text_field( $txHash ) );
}

add_action(
    "CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_send_tx",
    "CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_send_tx",
    0,
    4
);
function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_send_tx(
    $contractAddress,
    $data,
    $gasLimit,
    $providerUrl,
    $restartOnError = true
) {
    global $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_options;
    // проверить, есть ли прошлая транзакция
    $thisWalletAddress = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getHotWalletAddress();
    if ( is_null( $thisWalletAddress ) ) {
        CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( sprintf( __( 'Configuration error! The "%s" setting is not set.', 'cryptocurrency-product-for-woocommerce' ), __( "Ethereum wallet address", 'cryptocurrency-product-for-woocommerce' ) ) );
        return;
    }
    $lasttxhash = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_last_txhash( $thisWalletAddress );
    $txhash = null;
    $nonce = null;
    $canceled = false;
    try {
        if ( $lasttxhash ) {
            $lastnonce = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_last_nonce( $thisWalletAddress );
            // если есть, проверить, завершена ли она
            $tx_confirmed = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_is_tx_confirmed( $lasttxhash, $lastnonce );
            if ( $tx_confirmed ) {
                //   - да, послать новую транзакцию
                list( $txhash, $nonce, $canceled ) = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_send_tx_impl(
                    $contractAddress,
                    $data,
                    $gasLimit,
                    $providerUrl
                );
            } else {
                if ( is_null( $tx_confirmed ) ) {
                    // nonce in last tx is outdated. remove it
                    CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_delete_last_txhash( $thisWalletAddress );
                    // CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_cancel_complete_order_task($order_id, $txhash, $nonce);
                }
            }
        } else {
            // нет последней транзакции. послать новую транзакцию
            list( $txhash, $nonce, $canceled ) = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_send_tx_impl(
                $contractAddress,
                $data,
                $gasLimit,
                $providerUrl
            );
        }
        if ( $txhash ) {
            // успех - запомнить новую последнюю транзакцию
            CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_set_last_txhash( $thisWalletAddress, $txhash, $nonce );
        } else {
            if ( !$canceled ) {
                // Неуспех - поставить себя снова в очередь
                if ( $restartOnError ) {
                    // wait 5 blocks
                    $blockchainInterblockPeriodSeconds = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getBlockchainInterblockPeriodSeconds();
                    $offset = 5 * $blockchainInterblockPeriodSeconds;
                    CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_enqueue_send_tx_task(
                        $contractAddress,
                        $data,
                        $gasLimit,
                        $providerUrl,
                        $offset
                    );
                }
            }
        }
    } catch ( Exception $ex ) {
        CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_send_tx: " . $ex->getMessage() );
        if ( $restartOnError ) {
            $offset = 60;
            CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_enqueue_send_tx_task(
                $contractAddress,
                $data,
                $gasLimit,
                $providerUrl,
                $offset
            );
        }
    }
    return $txhash;
}

function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_has_unconfirmed_tx(  $thisWalletAddress  ) {
    // проверить, есть ли прошлая транзакция
    $lasttxhash = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_last_txhash( $thisWalletAddress );
    if ( !$lasttxhash ) {
        return false;
    }
    // если есть, проверить, завершена ли она
    $lastnonce = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_last_nonce( $thisWalletAddress );
    $tx_confirmed = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_is_tx_confirmed( $lasttxhash, $lastnonce );
    if ( $tx_confirmed ) {
        return false;
    }
    if ( is_null( $tx_confirmed ) ) {
        // nonce in last tx is outdated. remove it
        CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_delete_last_txhash( $thisWalletAddress );
        return false;
    }
    return true;
}

// TODO: wait for a configured number of blocks
function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_is_tx_confirmed(
    $txhash,
    $lastnonce,
    $providerUrl = null,
    $product_id = null,
    $from_user_id = null,
    $thisWalletAddress = null
) {
    global $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_options;
    $httpProvider = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getHttpProvider();
    $web3 = new \Web3\Web3($httpProvider);
    $eth = $web3->eth;
    $is_confirmed = false;
    if ( is_null( $thisWalletAddress ) ) {
        $thisWalletAddress = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getHotWalletAddress( $product_id, $from_user_id );
        if ( is_null( $thisWalletAddress ) ) {
            CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( sprintf( __( 'Configuration error! The "%s" setting is not set.', 'cryptocurrency-product-for-woocommerce' ), __( "Ethereum wallet address", 'cryptocurrency-product-for-woocommerce' ) ) );
            return $is_confirmed;
        }
    }
    $eth->getTransactionByHash( $txhash, function ( $err, $transaction ) use(
        &$is_confirmed,
        $txhash,
        $lastnonce,
        $thisWalletAddress
    ) {
        if ( $err !== null ) {
            CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "Failed to getTransactionByHash: " . $err );
            $nonce = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_nonce( $thisWalletAddress );
            if ( !is_null( $nonce ) && intval( $lastnonce ) < intval( $nonce ) ) {
                // tx outdated flag
                $is_confirmed = null;
                CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "tx nonce({$lastnonce}) less then address nonce({$nonce})" );
            }
            return;
        }
        CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "transaction: " . print_r( $transaction, true ) );
        if ( is_null( $transaction ) ) {
            CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_increase_tx_errors_counter( $txhash );
            $errors_count = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_tx_errors_counter( $txhash );
            CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "tx ({$txhash}) is not found in blockchain. errors_count = " . $errors_count );
            if ( $errors_count >= 10 ) {
                $is_confirmed = null;
                CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "tx ({$txhash}) is_confirmed is set to null" );
            }
        } else {
            CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_reset_tx_errors_counter( $txhash );
            CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "tx ({$txhash}) errors_count reset" );
            $is_confirmed = property_exists( $transaction, "blockHash" ) && !empty( $transaction->blockHash ) && '0x0000000000000000000000000000000000000000000000000000000000000000' != $transaction->blockHash;
        }
    } );
    CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "is_confirmed({$txhash}): " . $is_confirmed );
    return $is_confirmed;
}

function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_is_tx_succeeded(  $txhash, $providerUrl  ) {
    $httpProvider = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getHttpProvider();
    $web3 = new \Web3\Web3($httpProvider);
    $eth = $web3->eth;
    $is_confirmed = false;
    $gas = NULL;
    $eth->getTransactionByHash( $txhash, function ( $err, $transaction ) use(&$gas, &$is_confirmed) {
        if ( $err !== null ) {
            CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "Failed to getTransactionByHash: " . $err );
            return;
        }
        //        CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log("transaction: " . print_r($transaction, true));
        if ( is_null( $transaction ) ) {
            CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "Failed to getTransactionByHash: transaction is null" );
            return;
        }
        $is_confirmed = property_exists( $transaction, "blockHash" ) && !empty( $transaction->blockHash ) && '0x0000000000000000000000000000000000000000000000000000000000000000' != $transaction->blockHash;
        $gas = $transaction->gas;
    } );
    if ( !$is_confirmed ) {
        return null;
    }
    $gasUsed = NULL;
    $status = NULL;
    $transactionReceipt = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getTransactionReceipt( $txhash, $eth );
    if ( is_null( $transactionReceipt ) ) {
        return null;
    }
    if ( property_exists( $transactionReceipt, "status" ) && !empty( $transactionReceipt->status ) ) {
        $status = $transactionReceipt->status;
    }
    if ( !is_null( $status ) ) {
        return boolval( intval( $status, 16 ) );
    }
    if ( property_exists( $transactionReceipt, "gasUsed" ) && !empty( $transactionReceipt->gasUsed ) ) {
        $gasUsed = $transactionReceipt->gasUsed;
    }
    if ( is_null( $gasUsed ) ) {
        return null;
    }
    return intval( $gas, 16 ) != intval( $gasUsed, 16 );
}

function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_last_txhash(  $thisWalletAddress  ) {
    $chainId = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getChainId();
    if ( null === $chainId ) {
        CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( sprintf( __( 'Configuration error! The "%s" setting is not set.', 'cryptocurrency-product-for-woocommerce' ), __( "Blockchain", 'cryptocurrency-product-for-woocommerce' ) ) );
        return null;
    }
    $option = $thisWalletAddress . "-cryptocurrency-product-for-woocommerce-queue-txhash-" . $chainId;
    $txhash = get_option( $option );
    return $txhash;
}

function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_last_nonce(  $thisWalletAddress  ) {
    $chainId = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getChainId();
    if ( null === $chainId ) {
        CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( sprintf( __( 'Configuration error! The "%s" setting is not set.', 'cryptocurrency-product-for-woocommerce' ), __( "Blockchain", 'cryptocurrency-product-for-woocommerce' ) ) );
        return null;
    }
    $option = $thisWalletAddress . "-cryptocurrency-product-for-woocommerce-queue-nonce-" . $chainId;
    $nonce = get_option( $option );
    return $nonce;
}

function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_tx_errors_counter(  $txhash  ) {
    $chainId = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getChainId();
    if ( null === $chainId ) {
        CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( sprintf( __( 'Configuration error! The "%s" setting is not set.', 'cryptocurrency-product-for-woocommerce' ), __( "Blockchain", 'cryptocurrency-product-for-woocommerce' ) ) );
        return null;
    }
    $option = $txhash . "-cryptocurrency-product-for-woocommerce-errors-counter-" . $chainId;
    $value = get_option( $option );
    if ( empty( $value ) ) {
        return 0;
    }
    return intval( $value );
}

function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_increase_tx_errors_counter(  $txhash  ) {
    $chainId = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getChainId();
    if ( null === $chainId ) {
        CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( sprintf( __( 'Configuration error! The "%s" setting is not set.', 'cryptocurrency-product-for-woocommerce' ), __( "Blockchain", 'cryptocurrency-product-for-woocommerce' ) ) );
        return null;
    }
    $option = $txhash . "-cryptocurrency-product-for-woocommerce-errors-counter-" . $chainId;
    $prev_value = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_tx_errors_counter( $txhash );
    $new_value = $prev_value + 1;
    $autoload = true;
    update_option( $option, $new_value, $autoload );
}

function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_reset_tx_errors_counter(  $txhash  ) {
    $chainId = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getChainId();
    if ( null === $chainId ) {
        CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( sprintf( __( 'Configuration error! The "%s" setting is not set.', 'cryptocurrency-product-for-woocommerce' ), __( "Blockchain", 'cryptocurrency-product-for-woocommerce' ) ) );
        return null;
    }
    $option = $txhash . "-cryptocurrency-product-for-woocommerce-errors-counter-" . $chainId;
    $new_value = 0;
    $autoload = true;
    update_option( $option, $new_value, $autoload );
}

function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_set_last_txhash(  $thisWalletAddress, $txhash, $nonce  ) {
    $chainId = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getChainId();
    if ( null === $chainId ) {
        CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( sprintf( __( 'Configuration error! The "%s" setting is not set.', 'cryptocurrency-product-for-woocommerce' ), __( "Blockchain", 'cryptocurrency-product-for-woocommerce' ) ) );
        return null;
    }
    $option = $thisWalletAddress . "-cryptocurrency-product-for-woocommerce-queue-txhash-" . $chainId;
    $new_value = $txhash;
    $autoload = true;
    update_option( $option, $new_value, $autoload );
    $option = $thisWalletAddress . "-cryptocurrency-product-for-woocommerce-queue-nonce-" . $chainId;
    $new_value = $nonce;
    $autoload = true;
    update_option( $option, $new_value, $autoload );
}

function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_delete_last_txhash(  $thisWalletAddress  ) {
    $chainId = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getChainId();
    if ( null === $chainId ) {
        CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( sprintf( __( 'Configuration error! The "%s" setting is not set.', 'cryptocurrency-product-for-woocommerce' ), __( "Blockchain", 'cryptocurrency-product-for-woocommerce' ) ) );
        return null;
    }
    $option = $thisWalletAddress . "-cryptocurrency-product-for-woocommerce-queue-txhash-" . $chainId;
    delete_option( $option );
    $option = $thisWalletAddress . "-cryptocurrency-product-for-woocommerce-queue-nonce-" . $chainId;
    delete_option( $option );
}

add_filter(
    'cryptocurrency_product_for_woocommerce_is_order_complete',
    'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_ETHER_is_order_complete_hook',
    10,
    4
);
function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_ETHER_is_order_complete_hook(
    $is_order_complete,
    $cryptocurrency_option,
    $order_id,
    $product_id
) {
    global $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_options;
    if ( 'Ether' !== $cryptocurrency_option ) {
        return $is_order_complete;
    }
    // order is not complete flag
    if ( false === $is_order_complete ) {
        return $is_order_complete;
    }
    // order is failed flag
    if ( is_null( $is_order_complete ) ) {
        return $is_order_complete;
    }
    $thisWalletAddress = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getHotWalletAddress( $product_id );
    if ( is_null( $thisWalletAddress ) ) {
        CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( sprintf( __( 'Configuration error! The "%s" setting is not set.', 'cryptocurrency-product-for-woocommerce' ), __( "Ethereum wallet address", 'cryptocurrency-product-for-woocommerce' ) ) );
        update_post_meta( $order_id, 'status', __( 'Configuration error', 'cryptocurrency-product-for-woocommerce' ) );
        return $is_order_complete;
    }
    $txhash = _CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_post_meta( $order_id, 'ether_txhash_' . $product_id, true );
    if ( empty( $txhash ) ) {
        // CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log("CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_ETHER_is_order_complete_hook: no ether_txhash_$product_id in the order complete task for order: " . $order_id);
        return false;
    }
    $providerUrl = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getWeb3Endpoint();
    try {
        $tx_succeeded = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_is_tx_succeeded( $txhash, $providerUrl );
        if ( $tx_succeeded ) {
            // success, do not change flag value
            $is_order_complete = $is_order_complete;
        } else {
            if ( is_null( $tx_succeeded ) ) {
                // tx is not confirmed yet
                $is_order_complete = false;
            } else {
                // transaction failed
                CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "transaction {$txhash} for {$order_id} has failed" );
                $is_order_complete = null;
                $order = wc_get_order( $order_id );
                // Place the order to failed.
                $blockchainNetwork = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getBlockchainNetwork();
                $txHashPath = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_ETHER_get_txhash_path( $txhash, $blockchainNetwork );
                $res = $order->update_status( 'failed', sprintf( __( 'Transaction <a target="_blank" href="%1$s">%2$s</a> has failed.', 'cryptocurrency-product-for-woocommerce' ), $txHashPath, $txhash ) );
                if ( !$res ) {
                    CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "Failed to fail order: " . $order_id );
                    // Неуспех - поставить себя снова в очередь
                    // wait 5 blocks
                    $blockchainInterblockPeriodSeconds = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getBlockchainInterblockPeriodSeconds();
                    $offset = 5 * $blockchainInterblockPeriodSeconds;
                    CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_enqueue_complete_order_task( $order_id, $product_id, $offset );
                }
            }
        }
    } catch ( Exception $ex ) {
        CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_ETHER_is_order_complete_hook: " . $ex->getMessage() );
    }
    return $is_order_complete;
}

function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_enqueue_send_tx_task(
    $contractAddress,
    $data,
    $gasLimit,
    $providerUrl,
    $offset = 0
) {
    global $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_options, $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_plugin_dir;
    $timestamp = time() + $offset;
    $hook = "CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_send_tx";
    $args = array(
        $contractAddress,
        $data,
        $gasLimit,
        $providerUrl
    );
    $thisWalletAddress = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getHotWalletAddress();
    if ( is_null( $thisWalletAddress ) ) {
        CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( sprintf( __( 'Configuration error! The "%s" setting is not set.', 'cryptocurrency-product-for-woocommerce' ), __( "Ethereum wallet address", 'cryptocurrency-product-for-woocommerce' ) ) );
        return;
    }
    $group = $thisWalletAddress . "-cryptocurrency-product-for-woocommerce-queue";
    // @see https://github.com/woocommerce/action-scheduler/issues/730
    // if (!class_exists('ActionScheduler', false) || !ActionScheduler::is_initialized()) {
    //     require_once($CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_plugin_dir . '/vendor/woocommerce/action-scheduler/classes/abstracts/ActionScheduler.php');
    //     ActionScheduler::init($CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_plugin_dir . '/vendor/woocommerce/action-scheduler/action-scheduler.php');
    // }
    $task_id = as_schedule_single_action(
        $timestamp,
        $hook,
        $args,
        $group
    );
    CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "Task send_tx({$contractAddress}) with id {$task_id} scheduled for group: {$group}" );
}

function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_ETHER_enqueue_send_ether_task(
    $order_id,
    $product_id,
    $marketAddress,
    $product_quantity,
    $providerUrl,
    $offset = 0,
    $from_user_id = null
) {
    global $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_options, $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_plugin_dir;
    $timestamp = time() + $offset;
    $hook = "CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_ETHER_send_ether";
    $args = array(
        $order_id,
        $product_id,
        $marketAddress,
        $product_quantity,
        $providerUrl,
        $from_user_id
    );
    $thisWalletAddress = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getHotWalletAddress( $product_id, $from_user_id );
    if ( is_null( $thisWalletAddress ) ) {
        CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( sprintf( __( 'Configuration error! The "%s" setting is not set.', 'cryptocurrency-product-for-woocommerce' ), __( "Ethereum wallet address", 'cryptocurrency-product-for-woocommerce' ) ) );
        return;
    }
    $group = $thisWalletAddress . "-cryptocurrency-product-for-woocommerce-queue";
    // @see https://github.com/woocommerce/action-scheduler/issues/730
    // if (!class_exists('ActionScheduler', false) || !ActionScheduler::is_initialized()) {
    //     require_once($CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_plugin_dir . '/vendor/woocommerce/action-scheduler/classes/abstracts/ActionScheduler.php');
    //     ActionScheduler::init($CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_plugin_dir . '/vendor/woocommerce/action-scheduler/action-scheduler.php');
    // }
    $task_id = as_schedule_single_action(
        $timestamp,
        $hook,
        $args,
        $group
    );
    CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "Task send_ether with id {$task_id} for order {$order_id} scheduled for group: {$group}" );
}

function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_nonce(  $accountAddress, $providerUrl = null, $eth = null  ) {
    if ( !$eth ) {
        $httpProvider = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getHttpProvider();
        $web3 = new \Web3\Web3($httpProvider);
        $eth = $web3->eth;
    }
    $nonce = 0;
    $eth->getTransactionCount( $accountAddress, function ( $err, $transactionCount ) use(&$nonce) {
        if ( $err !== null ) {
            CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "Failed to getTransactionCount: " . $err );
            $nonce = null;
            return;
        }
        $nonce = intval( $transactionCount->toString() );
    } );
    return $nonce;
}

function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getTransactionReceipt(  $txhash, $eth = null  ) {
    if ( !$eth ) {
        $httpProvider = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getHttpProvider();
        $web3 = new \Web3\Web3($httpProvider);
        $eth = $web3->eth;
    }
    $transactionReceiptRes = NULL;
    $eth->getTransactionReceipt( $txhash, function ( $err, $transactionReceipt ) use(&$transactionReceiptRes) {
        if ( $err !== null ) {
            CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "Failed to getTransactionReceipt: " . $err );
            return;
        }
        $transactionReceiptRes = $transactionReceipt;
        //        CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log("transactionReceipt: " . print_r($transactionReceipt, true));
    } );
    return $transactionReceiptRes;
}

/**
 * CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getLatestBlock
 *
 * @param  \Web3\Eth $eth
 * @return array {
 * @type string|null error The error message or null if no error
 * @type object|null The latest block object in the blockchain configured
 * }
 */
function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getLatestBlock(  $eth  ) {
    static $_block_saved = null;
    $error = null;
    if ( is_null( $_block_saved ) ) {
        $block = null;
        $eth->getBlockByNumber( 'latest', false, function ( $err, $_block ) use(&$block, &$error) {
            if ( $err !== null ) {
                CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "Failed to getBlockByNumber: " . $err );
                $error = $err;
                return;
            }
            $block = $_block;
            // CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log("CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getLatestBlock: " . print_r($_block, true));
            // CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log("CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getLatestBlock: latest");
        } );
        $_block_saved = $block;
    }
    return [$error, $_block_saved];
}

function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getBlock(  $blockHashOrBlockNumber, $eth = null  ) {
    if ( !$eth ) {
        $httpProvider = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getHttpProvider();
        $web3 = new \Web3\Web3($httpProvider);
        $eth = $web3->eth;
    }
    $blockRes = NULL;
    $eth->getBlockByHash( $blockHashOrBlockNumber, true, function ( $err, $block ) use(&$blockRes) {
        if ( $err !== null ) {
            CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "Failed to getBlock: " . $err );
            return;
        }
        $blockRes = $block;
        //        CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log("block: " . print_r($block, true));
    } );
    if ( is_null( $blockRes ) ) {
        $eth->getBlockByNumber( $blockHashOrBlockNumber, true, function ( $err, $block ) use(&$blockRes) {
            if ( $err !== null ) {
                CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "Failed to getBlock: " . $err );
                return;
            }
            $blockRes = $block;
            //            CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log("block: " . print_r($block, true));
        } );
    }
    return $blockRes;
}

function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getLogs(
    $address,
    $topics,
    $eth = null,
    $fromBlock = '0x0',
    $toBlock = 'latest'
) {
    /**
     * getLogs
     * 
     * Object - The filter options:
     * fromBlock: QUANTITY|TAG - (optional, default: "latest") Integer block number, or "latest" for the last mined block or "pending", "earliest" for not yet mined transactions.
     * toBlock: QUANTITY|TAG - (optional, default: "latest") Integer block number, or "latest" for the last mined block or "pending", "earliest" for not yet mined transactions.
     * address: DATA|Array, 20 Bytes - (optional) Contract address or a list of addresses from which logs should originate.
     * topics: Array of DATA, - (optional) Array of 32 Bytes DATA topics. Topics are order-dependent. Each topic can also be an array of DATA with “or” options.
     * blockhash: DATA, 32 Bytes - (optional, future) With the addition of EIP-234, blockHash will be a new filter option which restricts the logs returned to the single block with the 32-byte hash blockHash. Using blockHash is equivalent to fromBlock = toBlock = the block number with hash blockHash. If blockHash is present in in the filter criteria, then neither fromBlock nor toBlock are allowed.
     */
    if ( !$eth ) {
        $httpProvider = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getHttpProvider();
        $web3 = new \Web3\Web3($httpProvider);
        $eth = $web3->eth;
    }
    // CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log("CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getLogs: topics=" . print_r($topics, true));
    foreach ( $topics as $key => $topic ) {
        if ( is_int( $topic ) ) {
            $topicBn = new \phpseclib3\Math\BigInteger($topic);
            $topic = $topicBn->toHex();
        }
        if ( 0 === strpos( $topic, '0x' ) ) {
            $topic = substr( $topic, 2 );
            $topic = sprintf( '0x%064s', $topic );
            $topics[$key] = $topic;
        }
    }
    $logsRes = NULL;
    $args = [
        'fromBlock' => $fromBlock,
        'toBlock'   => $toBlock,
        'address'   => $address,
        'topics'    => array_values( $topics ),
    ];
    // CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log("CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getLogs: " . print_r($args, true));
    $eth->getLogs( $args, function ( $err, $logs ) use(&$logsRes) {
        if ( $err !== null ) {
            CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "Failed to getLogs: " . $err );
            return;
        }
        $logsRes = $logs;
        //        CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log("logs: " . print_r($logs, true));
    } );
    return $logsRes;
}

function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_send_tx_impl(
    $contractAddress,
    $data,
    $gasLimit,
    $providerUrl
) {
    global $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_options;
    $thisWalletAddress = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getHotWalletAddress();
    if ( is_null( $thisWalletAddress ) ) {
        CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( sprintf( __( 'Configuration error! The "%s" setting is not set.', 'cryptocurrency-product-for-woocommerce' ), __( "Ethereum wallet address", 'cryptocurrency-product-for-woocommerce' ) ) );
        return null;
    }
    $chainId = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getChainId();
    if ( null === $chainId ) {
        CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( sprintf( __( 'Configuration error! The "%s" setting is not set.', 'cryptocurrency-product-for-woocommerce' ), __( "Blockchain", 'cryptocurrency-product-for-woocommerce' ) ) );
        return null;
    }
    // 4. call payToken if allowance is enough
    $httpProvider = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getHttpProvider();
    $web3 = new \Web3\Web3($httpProvider);
    $eth = $web3->eth;
    $nonce = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_nonce( $thisWalletAddress, $providerUrl, $eth );
    if ( null === $nonce ) {
        CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "nonce is null" );
        return null;
    }
    $gasPrice = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_gas_price_wei( 20 );
    $gasPriceTip = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_gas_price_tip_wei( 20 );
    $thisWalletPrivKey = _CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getHotWalletPrivateKey();
    if ( is_null( $thisWalletPrivKey ) ) {
        CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( sprintf( __( 'Configuration error! The "%s" setting is not set.', 'cryptocurrency-product-for-woocommerce' ), __( "Ethereum wallet private key", 'cryptocurrency-product-for-woocommerce' ) ) );
        return null;
    }
    $to = $contractAddress;
    $nonceb = \BitWasp\Buffertools\Buffer::int( $nonce );
    $gasPrice = \BitWasp\Buffertools\Buffer::int( $gasPrice );
    $gasLimit = \BitWasp\Buffertools\Buffer::int( $gasLimit );
    $transactionData = [
        'from'    => $thisWalletAddress,
        'nonce'   => '0x' . $nonceb->getHex(),
        'to'      => strtolower( $to ),
        'gas'     => '0x' . $gasLimit->getHex(),
        'value'   => '0x0',
        'chainId' => $chainId,
        'data'    => '0x' . $data,
    ];
    list( $error, $gasEstimate ) = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_gas_estimate( $transactionData, $eth );
    if ( null === $gasEstimate ) {
        CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "gasEstimate is null: " . $error );
        return null;
    }
    CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "gasEstimate: " . $gasEstimate->toHex() );
    if ( $gasLimit->getHex() === $gasEstimate->toHex() ) {
        CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "Too low gas limit specified in settings: " . $gasLimit->getHex() );
        return null;
    }
    $transactionData['gas'] = '0x' . $gasEstimate->toHex();
    unset($transactionData['from']);
    if ( is_null( $gasPriceTip ) ) {
        // pre-EIP1559
        $transactionData['gasPrice'] = '0x' . $gasPrice->getHex();
    } else {
        $transactionData['accessList'] = [];
        // EIP1559
        $transactionData['maxFeePerGas'] = '0x' . $gasPrice->getHex();
        $gasPriceTip = \BitWasp\Buffertools\Buffer::int( $gasPriceTip );
        $transactionData['maxPriorityFeePerGas'] = '0x' . $gasPriceTip->getHex();
    }
    $transaction = new \Web3p\EthereumTx\Transaction($transactionData);
    $signedTransaction = "0x" . $transaction->sign( $thisWalletPrivKey );
    $txHash = null;
    $eth->sendRawTransaction( (string) $signedTransaction, function ( $err, $transaction ) use(&$txHash, &$transactionData, $signedTransaction) {
        if ( $err !== null ) {
            CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "Failed to sendRawTransaction: " . $err );
            CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "Failed to sendRawTransaction: transactionData=" . print_r( $transactionData, true ) );
            CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "Failed to sendRawTransaction: signedTransaction=" . (string) $signedTransaction );
            return;
        }
        $txHash = $transaction;
    } );
    if ( null === $txHash ) {
        return null;
    }
    return array($txHash, $nonce, false);
}

function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_encodeParameters(  $types, $params  ) {
    $ethABI = new \Web3\Contracts\Ethabi([
        'address'      => new Web3\Contracts\Types\Address(),
        'bool'         => new Web3\Contracts\Types\Boolean(),
        'bytes'        => new Web3\Contracts\Types\Bytes(),
        'dynamicBytes' => new Web3\Contracts\Types\DynamicBytes(),
        'int'          => new Web3\Contracts\Types\Integer(),
        'string'       => new Web3\Contracts\Types\Str(),
        'uint'         => new Web3\Contracts\Types\Uinteger(),
    ]);
    $_data = $ethABI->encodeParameters( $types, $params );
    return $_data;
}

function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_ETHER_is_order_payed(  $order_id, $product_id  ) {
    // backward compatibility
    $txhash = _CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_post_meta( $order_id, 'ether_txhash', true );
    if ( !empty( $txhash ) ) {
        return true;
    }
    $txhash = _CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_post_meta( $order_id, 'ether_txhash_' . $product_id, true );
    return !empty( $txhash );
}

add_action(
    'cryptocurrency_product_for_woocommerce_get_cryptocurrency_address',
    'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_ETHER_ALL_get_cryptocurrency_address_hook',
    10,
    3
);
function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_ETHER_ALL_get_cryptocurrency_address_hook(  $address, $cryptocurrency_option, $order_id  ) {
    if ( "ETHEREUM" !== apply_filters( 'cryptocurrency_product_for_woocommerce_get_base_blockchain', '', $cryptocurrency_option ) ) {
        return $address;
    }
    $_billing_cryptocurrency_ethereum_address = _CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_post_meta( $order_id, '_billing_cryptocurrency_ethereum_address', true );
    if ( empty( $_billing_cryptocurrency_ethereum_address ) ) {
        $order = wc_get_order( $order_id );
        if ( !$order ) {
            return $address;
        }
        $user_id = $order->get_customer_id();
        if ( $user_id <= 0 ) {
            return $address;
        }
        $_billing_cryptocurrency_ethereum_address = get_user_meta( $user_id, 'user_ethereum_wallet_address', true );
    }
    if ( empty( $_billing_cryptocurrency_ethereum_address ) ) {
        return $address;
    }
    return $_billing_cryptocurrency_ethereum_address;
}

add_action(
    'cryptocurrency_product_for_woocommerce_clean_order_product_txhashes',
    'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_ETHER_clean_order_product_txhashes_hook',
    10,
    4
);
function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_ETHER_clean_order_product_txhashes_hook(
    $cryptocurrency_option,
    $order_id,
    $product_id,
    $lasttxhash
) {
    if ( 'Ether' !== $cryptocurrency_option ) {
        return;
    }
    $order = wc_get_order( $order_id );
    // Place the order to failed.
    $blockchainNetwork = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getBlockchainNetwork();
    $txHashPath = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_ETHER_get_txhash_path( $lasttxhash, $blockchainNetwork );
    $res = $order->update_status( 'failed', sprintf( __( 'Transaction <a target="_blank" href="%1$s">%2$s</a> has failed.', 'cryptocurrency-product-for-woocommerce' ), $txHashPath, $lasttxhash ) );
    if ( !$res ) {
        CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "Failed to fail order: " . $order_id );
    }
    delete_post_meta( $order_id, 'ether_txhash_' . $product_id );
}

add_action(
    'cryptocurrency_product_for_woocommerce_enqueue_send_task',
    'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_ETHER_enqueue_send_task_hook',
    10,
    5
);
function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_ETHER_enqueue_send_task_hook(
    $cryptocurrency_option,
    $order_id,
    $product_id,
    $marketAddress,
    $product_quantity
) {
    if ( 'Ether' !== $cryptocurrency_option ) {
        return;
    }
    // send Ether
    if ( CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_ETHER_is_order_payed( $order_id, $product_id ) ) {
        $txhash = _CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_post_meta( $order_id, 'ether_txhash_' . $product_id, true );
        CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "Payment found for order {$order_id}, product {$product_id}: {$txhash}. Skip payment." );
        CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_enqueue_complete_order_task( $order_id, $product_id );
        return;
    }
    $providerUrl = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getWeb3Endpoint();
    CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_ETHER_enqueue_send_ether_task(
        $order_id,
        $product_id,
        $marketAddress,
        $product_quantity,
        $providerUrl
    );
}

function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getWeb3Endpoint() {
    global $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_options;
    $infuraApiKey = ( isset( $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_options['infuraApiKey'] ) ? esc_attr( $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_options['infuraApiKey'] ) : '' );
    $blockchainNetwork = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getBlockchainNetwork();
    if ( empty( $blockchainNetwork ) ) {
        $blockchainNetwork = 'mainnet';
    }
    $web3Endpoint = "https://" . esc_attr( $blockchainNetwork ) . ".infura.io/v3/" . esc_attr( $infuraApiKey );
    return $web3Endpoint;
}

function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getBlockchainCurrencyTicker() {
    global $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_options;
    $currency_ticker_default = "ETH";
    $chainId = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getChainId();
    $blockchainInfo = apply_filters( 'ethereumico.io/blockchain-info', null, $chainId );
    if ( is_null( $blockchainInfo ) ) {
        CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getBlockchainCurrencyTicker: Bad chain id:" . $chainId );
        return $currency_ticker_default;
    }
    return $blockchainInfo['currency_ticker'];
}

function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_is_ether_product_type_disabled() {
    global $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_options;
    return false;
}

function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getBlockchainCurrencyTickerName() {
    global $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_options;
    $currency_name_default = __( 'Ether', 'cryptocurrency-product-for-woocommerce' );
    $chainId = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getChainId();
    $blockchainInfo = apply_filters( 'ethereumico.io/blockchain-info', null, $chainId );
    if ( is_null( $blockchainInfo ) ) {
        CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getBlockchainCurrencyTickerName: Bad chain id:" . $chainId );
        return $currency_name_default;
    }
    return $blockchainInfo['currency_name'];
}

function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getBlockchainName() {
    global $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_options;
    $blockchain_display_name_default = __( 'Ethereum', 'cryptocurrency-product-for-woocommerce' );
    $chainId = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getChainId();
    $blockchainInfo = apply_filters( 'ethereumico.io/blockchain-info', null, $chainId );
    if ( is_null( $blockchainInfo ) ) {
        CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getBlockchainCurrencyTickerName: Bad chain id:" . $chainId );
        return $blockchain_display_name_default;
    }
    return $blockchainInfo['blockchain_display_name'];
}

function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_geEthereumAddressFieldLabel() {
    $blockchain_display_name = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getBlockchainName();
    return sprintf( __( '%1$s Address', 'cryptocurrency-product-for-woocommerce' ), $blockchain_display_name );
    return $blockchain_display_name;
}

function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getMultiSendAccumulationPeriod() {
    global $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_options;
    return 60;
}

function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getChainId() {
    static $_saved_chain_id = null;
    if ( is_null( $_saved_chain_id ) ) {
        $_saved_chain_id = _CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getChainId_impl();
    }
    return $_saved_chain_id;
}

function _CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getChainId_impl() {
    global $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_options;
    $blockchainNetwork = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getBlockchainNetwork();
    if ( empty( $blockchainNetwork ) ) {
        $blockchainNetwork = 'mainnet';
    }
    $blockchainId = apply_filters( 'ethereumico.io/blockchain-info/blockchain-id', null, $blockchainNetwork );
    if ( is_null( $blockchainId ) ) {
        CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "Bad blockchain_network setting:" . $blockchainNetwork );
    }
    // CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log("_CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getChainId_impl: blockchainNetwork = " . $blockchainNetwork . '; blockchainId = ' . $blockchainId);
    return $blockchainId;
}

function _CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getChainId_network_impl(  $providerUrl  ) {
    if ( empty( $providerUrl ) ) {
        return null;
    }
    $option_name = 'ethereumicoio-blockchain-id-' . hash( 'haval256,4', $providerUrl );
    $_version = get_option( $option_name, '' );
    if ( '' === $_version ) {
        $_version = null;
        try {
            $httpProvider = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getHttpProvider();
            $web3 = new \Web3\Web3($httpProvider);
            $net = $web3->net;
            $net->version( function ( $err, $version ) use(&$_version) {
                if ( $err !== null ) {
                    CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "Failed to get blockchain version: " . $err );
                    return;
                }
                $_version = intval( $version );
            } );
        } catch ( Exception $ex ) {
            CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "_CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getChainId_network_impl: " . $ex->getMessage() );
            $_version = null;
        }
    }
    if ( !is_null( $_version ) ) {
        $_version = intval( $_version );
    }
    return $_version;
}

/**
 * Get the blockchain API node type
 *
 * @return string infuraio|custom
 */
function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getBlockchainAPINodeType() {
    global $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_options;
    $blockchainAPINodeType = 'infuraio';
    if ( isset( $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_options['blockchain_api_node'] ) && !empty( $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_options['blockchain_api_node'] ) ) {
        $blockchainAPINodeType = $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_options['blockchain_api_node'];
    } else {
        if ( isset( $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_options['web3Endpoint'] ) && !empty( $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_options['web3Endpoint'] ) ) {
            $blockchainAPINodeType = 'custom';
        }
    }
    // CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log('CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getBlockchainNetwork: blockchainAPINodeType = ' . $blockchainAPINodeType);
    return $blockchainAPINodeType;
}

function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getBlockchainNetwork() {
    global $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_options;
    $blockchainNetwork = 'mainnet';
    $blockchainAPINodeType = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getBlockchainAPINodeType();
    switch ( $blockchainAPINodeType ) {
        case 'infuraio':
            if ( isset( $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_options['blockchain_network'] ) && !empty( $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_options['blockchain_network'] ) ) {
                $blockchainNetwork = strtolower( $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_options['blockchain_network'] );
            }
            break;
        case 'custom':
            $web3Endpoint = ( isset( $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_options['web3Endpoint'] ) ? esc_attr( $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_options['web3Endpoint'] ) : '' );
            if ( !empty( $web3Endpoint ) ) {
                $chainId = _CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getChainId_network_impl( $web3Endpoint );
                $blockchainNetwork = apply_filters( 'ethereumico.io/blockchain-info/blockchain-name', $blockchainNetwork, $chainId );
            }
            break;
    }
    return $blockchainNetwork;
}

function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getBlockchainInterblockPeriodSeconds() {
    $chainId = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getChainId();
    $blockchainInfo = apply_filters( 'ethereumico.io/blockchain-info', null, $chainId );
    if ( is_null( $blockchainInfo ) ) {
        return 15;
    }
    return $blockchainInfo['interblock_period_seconds'];
}

/**
 * CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getBalanceEth
 *
 * @param  string $thisWalletAddress
 * @param  string $providerUrl
 * @param  \Web3\Eth $eth
 * @return \phpseclib3\Math\BigInteger|null
 */
function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getBalanceEth(  $thisWalletAddress, $providerUrl, $eth = null  ) {
    if ( is_null( $eth ) ) {
        $httpProvider = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getHttpProvider();
        $web3 = new \Web3\Web3($httpProvider);
        $eth = $web3->eth;
    }
    $ether_balance_wei = null;
    $eth->getBalance( $thisWalletAddress, function ( $err, $balance ) use(&$ether_balance_wei) {
        if ( $err !== null ) {
            CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "Failed to getBalance: " . $err );
            return;
        }
        $ether_balance_wei = $balance;
    } );
    return $ether_balance_wei;
}

function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getHotWalletAddress(  $product_id = null, $from_user_id = null  ) {
    global $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_options;
    $wallet = null;
    if ( !is_null( $from_user_id ) ) {
        $vendor_id = $from_user_id;
        if ( user_can( $vendor_id, 'administrator' ) ) {
            if ( isset( $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_options['wallet_address'] ) ) {
                $wallet = $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_options['wallet_address'];
            }
        } else {
            if ( CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_is_vendor_user( $vendor_id ) ) {
            }
        }
    } else {
        if ( !is_null( $product_id ) ) {
            // background task processing
            $vendor_id = get_post_field( 'post_author_override', $product_id );
            if ( empty( $vendor_id ) ) {
                $vendor_id = get_post_field( 'post_author', $product_id );
            }
            if ( user_can( $vendor_id, 'administrator' ) ) {
                if ( isset( $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_options['wallet_address'] ) ) {
                    $wallet = $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_options['wallet_address'];
                }
            } else {
                if ( CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_is_vendor_user( $vendor_id ) ) {
                }
            }
        }
    }
    if ( !is_null( $wallet ) && !empty( $wallet ) ) {
        return esc_attr( $wallet );
    }
    if ( current_user_can( 'administrator' ) ) {
        if ( isset( $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_options['wallet_address'] ) ) {
            $wallet = $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_options['wallet_address'];
        }
    } else {
        if ( CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_is_vendor_user() ) {
        } else {
            $user_id = get_current_user_id();
            if ( $user_id <= 0 ) {
                // background task processing
                $vendor_id = get_post_field( 'post_author_override', $product_id );
                if ( empty( $vendor_id ) ) {
                    $vendor_id = get_post_field( 'post_author', $product_id );
                }
                if ( user_can( $vendor_id, 'administrator' ) ) {
                    if ( isset( $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_options['wallet_address'] ) ) {
                        $wallet = $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_options['wallet_address'];
                    }
                } else {
                    if ( CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_is_vendor_user( $vendor_id ) ) {
                    }
                }
            }
        }
    }
    if ( is_null( $wallet ) || empty( $wallet ) ) {
        if ( isset( $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_options['wallet_address'] ) ) {
            $wallet = $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_options['wallet_address'];
        }
    }
    if ( is_null( $wallet ) || empty( $wallet ) ) {
        return null;
    }
    return esc_attr( $wallet );
}

function _CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getHotWalletPrivateKey(  $product_id = null, $from_user_id = null  ) {
    global $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_options;
    $privateKey = null;
    if ( !is_null( $from_user_id ) ) {
        $vendor_id = $from_user_id;
        if ( user_can( $vendor_id, 'administrator' ) ) {
            if ( isset( $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_options['wallet_private_key'] ) ) {
                $privateKey = $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_options['wallet_private_key'];
            }
        } else {
            if ( CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_is_vendor_user( $vendor_id ) ) {
            }
        }
    } else {
        if ( !is_null( $product_id ) ) {
            // background task processing
            $vendor_id = get_post_field( 'post_author_override', $product_id );
            if ( empty( $vendor_id ) ) {
                $vendor_id = get_post_field( 'post_author', $product_id );
            }
            if ( user_can( $vendor_id, 'administrator' ) ) {
                if ( isset( $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_options['wallet_private_key'] ) ) {
                    $privateKey = $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_options['wallet_private_key'];
                }
            } else {
                if ( CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_is_vendor_user( $vendor_id ) ) {
                }
            }
        }
    }
    if ( !is_null( $privateKey ) && !empty( $privateKey ) ) {
        return esc_attr( $privateKey );
    }
    if ( current_user_can( 'administrator' ) ) {
        if ( isset( $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_options['wallet_private_key'] ) ) {
            $privateKey = $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_options['wallet_private_key'];
        }
    } else {
        if ( CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_is_vendor_user() ) {
        } else {
            $user_id = get_current_user_id();
            if ( $user_id <= 0 ) {
                // background task processing
                $vendor_id = get_post_field( 'post_author_override', $product_id );
                if ( empty( $vendor_id ) ) {
                    $vendor_id = get_post_field( 'post_author', $product_id );
                }
                if ( user_can( $vendor_id, 'administrator' ) ) {
                    if ( isset( $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_options['wallet_private_key'] ) ) {
                        $privateKey = $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_options['wallet_private_key'];
                    }
                } else {
                    if ( CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_is_vendor_user( $vendor_id ) ) {
                    }
                }
            }
        }
    }
    if ( is_null( $privateKey ) || empty( $privateKey ) ) {
        if ( isset( $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_options['wallet_private_key'] ) ) {
            $privateKey = $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_options['wallet_private_key'];
        }
    }
    if ( is_null( $privateKey ) || empty( $privateKey ) ) {
        return null;
    }
    return esc_attr( $privateKey );
}

add_filter(
    'cryptocurrency_product_for_woocommerce_get_user_wallet_address',
    'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_ETHER_get_user_wallet_address',
    20,
    3
);
function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_ETHER_get_user_wallet_address(  $user_wallet_address, $cryptocurrency_option, $user_id  ) {
    if ( "ETHEREUM" !== apply_filters( 'cryptocurrency_product_for_woocommerce_get_base_blockchain', '', $cryptocurrency_option ) ) {
        return $user_wallet_address;
    }
    if ( !function_exists( 'ETHEREUM_WALLET_get_wallet_address' ) ) {
        return $user_wallet_address;
    }
    return ETHEREUM_WALLET_get_wallet_address( $user_id );
}

add_filter(
    'cryptocurrency_product_for_woocommerce_get_billing_address_meta_key',
    'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_ETHER_get_billing_address_meta_key',
    20,
    2
);
function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_ETHER_get_billing_address_meta_key(  $billing_address_meta_key, $cryptocurrency_option  ) {
    if ( "ETHEREUM" !== apply_filters( 'cryptocurrency_product_for_woocommerce_get_base_blockchain', '', $cryptocurrency_option ) ) {
        return $billing_address_meta_key;
    }
    return "billing_cryptocurrency_ethereum_address";
}

add_filter(
    'cryptocurrency_product_for_woocommerce_get_user_wallet_meta_keys',
    'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_ETHER_get_user_wallet_meta_keys',
    20,
    2
);
function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_ETHER_get_user_wallet_meta_keys(  $userWalletMetaKeys, $cryptocurrency_option  ) {
    if ( "ETHEREUM" !== apply_filters( 'cryptocurrency_product_for_woocommerce_get_base_blockchain', '', $cryptocurrency_option ) ) {
        return $userWalletMetaKeys;
    }
    global $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_options;
    if ( !isset( $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_options['wallet_meta'] ) || empty( trim( $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_options['wallet_meta'] ) ) ) {
        return $userWalletMetaKeys;
    }
    return $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_options['wallet_meta'];
}

add_filter(
    'cryptocurrency_product_for_woocommerce_get_valid_blockchain_address',
    'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_ETHER_get_valid_blockchain_address',
    20,
    2
);
function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_ETHER_get_valid_blockchain_address(  $address, $cryptocurrency_option  ) {
    if ( "ETHEREUM" !== apply_filters( 'cryptocurrency_product_for_woocommerce_get_base_blockchain', '', $cryptocurrency_option ) ) {
        return $address;
    }
    if ( \Web3\Utils::isAddress( $address ) ) {
        return $address;
    }
    return null;
}

//----------------------------------------------------------------------------//
//                            Enqueue Scripts                                 //
//----------------------------------------------------------------------------//
function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_ETHER_enqueue_script() {
    global $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_plugin_url_path;
    // global $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_options;
    global $post;
    // $options = $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_options;
    $min = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min' );
    if ( !wp_script_is( 'web3', 'queue' ) && !wp_script_is( 'web3', 'done' ) ) {
        wp_dequeue_script( 'web3' );
        wp_deregister_script( 'web3' );
        wp_register_script(
            'web3',
            $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_plugin_url_path . "/web3.min.js",
            array('jquery'),
            '1.3.4'
        );
    }
    if ( !wp_script_is( 'bignumber', 'queue' ) && !wp_script_is( 'bignumber', 'done' ) ) {
        wp_dequeue_script( 'bignumber' );
        wp_deregister_script( 'bignumber' );
        wp_register_script(
            'bignumber',
            $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_plugin_url_path . "/bignumber.min.js",
            array('jquery'),
            '9.0.1'
        );
    }
    if ( !wp_script_is( 'cryptocurrency-product-for-woocommerce', 'queue' ) && !wp_script_is( 'cryptocurrency-product-for-woocommerce', 'done' ) ) {
        wp_dequeue_script( 'cryptocurrency-product-for-woocommerce' );
        wp_deregister_script( 'cryptocurrency-product-for-woocommerce' );
        wp_register_script(
            'cryptocurrency-product-for-woocommerce',
            $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_plugin_url_path . "/cryptocurrency-product-for-woocommerce{$min}.js",
            array('jquery', 'web3', 'bignumber'),
            '3.16.14'
        );
    }
    $product_id = null;
    if ( $post ) {
        $product = wc_get_product( $post->ID );
        if ( $product ) {
            $product_id = $product->get_id();
        }
    }
    $thisWalletAddress = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getHotWalletAddress( $product_id );
    if ( is_null( $thisWalletAddress ) ) {
        $thisWalletAddress = '';
    }
    wp_localize_script( 'cryptocurrency-product-for-woocommerce', 'cryptocurrency', apply_filters( 'cryptocurrency_product_for_woocommerce_wp_localize_script', [
        'web3Endpoint'  => esc_html( CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getWeb3Endpoint() ),
        'walletAddress' => esc_html( $thisWalletAddress ),
    ] ) );
}

add_action( 'cryptocurrency_product_for_woocommerce_enqueue_script', 'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_ETHER_enqueue_script' );
add_filter(
    'cryptocurrency_product_for_woocommerce_woocommerce_quantity_input_min',
    'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_ETHER_woocommerce_quantity_input_min_hook',
    20,
    3
);
function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_ETHER_woocommerce_quantity_input_min_hook(  $min, $cryptocurrency_option, $product_id  ) {
    if ( 'Ether' !== $cryptocurrency_option ) {
        return $min;
    }
    return 0.001;
}

add_filter(
    'cryptocurrency_product_for_woocommerce_woocommerce_quantity_input_step',
    'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_ETHER_woocommerce_quantity_input_step_hook',
    20,
    3
);
function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_ETHER_woocommerce_quantity_input_step_hook(  $step, $cryptocurrency_option, $product_id  ) {
    if ( 'Ether' !== $cryptocurrency_option ) {
        return $step;
    }
    return 1.0E-5;
}

//----------------------------------------------------------------------------//
//                      Ethereum address verification                         //
//----------------------------------------------------------------------------//
function _CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_wei_to_ether(  $balance  ) {
    $powDecimals = new \phpseclib3\Math\BigInteger(pow( 10, 18 ));
    list( $q, $r ) = $balance->divide( $powDecimals );
    $sR = $r->toString();
    $tokenDecimalChar = '.';
    $tokenDecimals = 18;
    $strBalanceDecimals = sprintf( '%018s', $sR );
    $strBalanceDecimals2 = substr( $strBalanceDecimals, 0, $tokenDecimals );
    if ( str_pad( "", $tokenDecimals, "0" ) == $strBalanceDecimals2 ) {
        $strBalance = rtrim( $q->toString() . $tokenDecimalChar . $strBalanceDecimals, '0' );
    } else {
        $strBalance = rtrim( $q->toString() . $tokenDecimalChar . $strBalanceDecimals2, '0' );
    }
    $strBalance = rtrim( $strBalance, $tokenDecimalChar );
    return $strBalance;
}

add_filter(
    'cryptocurrency_product_for_woocommerce_after_checkout_validation',
    'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_ETHER_ALL_after_checkout_validation',
    20,
    4
);
function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_ETHER_ALL_after_checkout_validation(
    $cryptocurrency_option,
    $product_id,
    $data,
    $errors
) {
    global $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_options;
    if ( "ETHEREUM" !== apply_filters( 'cryptocurrency_product_for_woocommerce_get_base_blockchain', '', $cryptocurrency_option ) ) {
        return;
    }
    $user_id = get_current_user_id();
    $isUserLoggedIn = $user_id > 0;
    $walletDisabled = ( isset( $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_options['wallet_field_disable'] ) ? esc_attr( $CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_options['wallet_field_disable'] ) : '' );
    $isWalletFieldDisabled = !empty( $walletDisabled );
    $isWalletAddressGeneratorExists = apply_filters( 'cryptocurrency_product_for_woocommerce_is_wallet_address_generator_exists', false, $cryptocurrency_option );
    $isWCGuestBuyAllowed = !WC()->checkout()->is_registration_required();
    $isWCCheckoutRegistrationEnabled = WC()->checkout()->is_registration_enabled();
    $createAccount = isset( $data['createaccount'] ) && !empty( $data['createaccount'] );
    $isWalletAddressFieldShown = $isUserLoggedIn || !$isWalletAddressGeneratorExists || $isWCGuestBuyAllowed;
    if ( $isWalletAddressFieldShown && !$createAccount ) {
        $billing_address_meta_key = apply_filters( 'cryptocurrency_product_for_woocommerce_get_billing_address_meta_key', '', $cryptocurrency_option );
        if ( !empty( $billing_address_meta_key ) && isset( $data[$billing_address_meta_key] ) ) {
            $billing_address = (string) $data[$billing_address_meta_key];
            $billing_address = apply_filters( 'cryptocurrency_product_for_woocommerce_get_valid_blockchain_address', $billing_address, $cryptocurrency_option );
            if ( is_null( $billing_address ) ) {
                // valid address not found
                $key = $billing_address_meta_key;
                $field_label = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_geEthereumAddressFieldLabel();
                $errors->add( $key . '_required', apply_filters(
                    'woocommerce_checkout_required_field_notice',
                    sprintf( __( '%s is a required field.', 'woocommerce' ), '<strong>' . esc_html( $field_label ) . '</strong>' ),
                    $field_label,
                    $key
                ), array(
                    'id' => $key,
                ) );
            }
        }
    }
}

add_filter(
    'cryptocurrency_product_for_woocommerce_is_wallet_address_generator_exists',
    'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_ETHER_is_wallet_address_generator_exists',
    20,
    2
);
function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_ETHER_is_wallet_address_generator_exists(  $is_wallet_address_generator_exists, $cryptocurrency_option  ) {
    if ( "ETHEREUM" !== apply_filters( 'cryptocurrency_product_for_woocommerce_get_base_blockchain', '', $cryptocurrency_option ) ) {
        return $is_wallet_address_generator_exists;
    }
    return function_exists( 'ETHEREUM_WALLET_create_account' );
}

function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_get_gas_estimate(  $transactionParamsArray, $eth  ) {
    $gasEstimate = null;
    $error = null;
    $transactionParamsArrayCopy = $transactionParamsArray;
    unset($transactionParamsArrayCopy['nonce']);
    unset($transactionParamsArrayCopy['chainId']);
    $eth->estimateGas( $transactionParamsArrayCopy, function ( $err, $gas ) use(&$gasEstimate, &$error) {
        if ( $err !== null ) {
            CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( "Failed to estimateGas: " . $err );
            $error = $err;
            return;
        }
        $gasEstimate = $gas;
    } );
    return [$error, $gasEstimate];
}

/**
 * Get the HttpProvider connection object
 *
 * @return \Web3\Providers\HttpProvider
 */
function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getHttpProvider() {
    static $_httpProvider = null;
    if ( is_null( $_httpProvider ) ) {
        $providerUrl = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getWeb3Endpoint();
        $requestManager = new \Web3\RequestManagers\HttpRequestManager($providerUrl, 10);
        $_httpProvider = new \Web3\Providers\HttpProvider($requestManager);
        // CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log("CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getHttpProvider: " . $providerUrl);
    }
    return $_httpProvider;
}
