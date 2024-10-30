<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
// Exit if accessed directly
function CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_options_page() {
    // Require admin privs
    if ( !current_user_can( 'manage_options' ) ) {
        return false;
    }
    $options = stripslashes_deep( get_option( 'cryptocurrency-product-for-woocommerce_options', array() ) );
    $new_options = array();
    // Which tab is selected?
    $possible_screens = array(
        'default' => esc_html( __( 'Standard', 'cryptocurrency-product-for-woocommerce' ) ),
    );
    $possible_screens = apply_filters( 'cryptocurrency_product_for_woocommerce_settings_tabs', $possible_screens );
    asort( $possible_screens );
    $current_screen = ( isset( $_GET['tab'] ) && isset( $possible_screens[$_GET['tab']] ) ? esc_attr( $_GET['tab'] ) : 'default' );
    if ( isset( $_POST['Submit'] ) ) {
        // Nonce verification
        check_admin_referer( 'cryptocurrency-product-for-woocommerce-update-options' );
        // Standard options screen
        if ( 'default' == $current_screen ) {
            //            $new_options['wallet_address']        = ( ! empty( $_POST['CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_wallet_address'] )       /*&& is_numeric( $_POST['CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_wallet_address'] )*/ )       ? sanitize_text_field($_POST['CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_wallet_address'])        : '';
            $new_options['blockchain_api_node'] = 'infuraio';
            $new_options['etherscanApiKey'] = ( !empty( $_POST['CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_etherscanApiKey'] ) ? sanitize_text_field( $_POST['CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_etherscanApiKey'] ) : '' );
            $new_options['gas_limit'] = ( !empty( $_POST['CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_gas_limit'] ) && is_numeric( $_POST['CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_gas_limit'] ) ? intval( sanitize_text_field( $_POST['CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_gas_limit'] ) ) : 400000 );
            $new_options['gas_price'] = ( !empty( $_POST['CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_gas_price'] ) && is_numeric( $_POST['CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_gas_price'] ) ? floatval( sanitize_text_field( $_POST['CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_gas_price'] ) ) : 0 );
            if ( !empty( $_POST['CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_wallet_private_key'] ) ) {
                $new_options['wallet_private_key'] = sanitize_text_field( $_POST['CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_wallet_private_key'] );
            }
            if ( 'infuraio' === $new_options['blockchain_api_node'] ) {
                $new_options['blockchain_network'] = ( !empty( $_POST['CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_blockchain_network'] ) ? sanitize_text_field( $_POST['CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_blockchain_network'] ) : 'mainnet' );
                $new_options['infuraApiKey'] = ( !empty( $_POST['CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_infuraApiKey'] ) ? sanitize_text_field( $_POST['CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_infuraApiKey'] ) : '' );
            }
            $new_options['wallet_meta'] = ( !empty( $_POST['CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_wallet_meta'] ) ? sanitize_text_field( $_POST['CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_wallet_meta'] ) : '' );
            $new_options['wallet_field_disable'] = ( !empty( $_POST['CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_wallet_field_disable'] ) ? 'on' : '' );
            $new_options['ether_product_type_disable'] = ( !empty( $_POST['CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_ether_product_type_disable'] ) ? 'on' : '' );
            $new_options['erc20_product_type_disable'] = ( !empty( $_POST['CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_erc20_product_type_disable'] ) ? 'on' : '' );
        }
        $new_options = apply_filters( 'cryptocurrency_product_for_woocommerce_get_save_options', $new_options, $current_screen );
        // Get all existing Cryptocurrency Product options
        $existing_options = get_option( 'cryptocurrency-product-for-woocommerce_options', array() );
        if ( (!isset( $new_options['wallet_private_key'] ) || empty( $new_options['wallet_private_key'] )) && (!isset( $existing_options['wallet_private_key'] ) || empty( $existing_options['wallet_private_key'] )) ) {
            // neither old nor new pkey value is set
            list( $ethAddressChkSum, $privateKeyHex ) = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_create_account();
            $new_options['wallet_address'] = $ethAddressChkSum;
            $new_options['wallet_private_key'] = $privateKeyHex;
        }
        if ( isset( $existing_options['wallet_private_key'] ) && !empty( $existing_options['wallet_private_key'] ) && isset( $new_options['wallet_private_key'] ) && !empty( $new_options['wallet_private_key'] ) && $existing_options['wallet_private_key'] != $new_options['wallet_private_key'] ) {
            // new pkey value is set. let's backup the old value
            $backup = "";
            if ( isset( $existing_options['_backup_wallet_private_keys'] ) ) {
                $backup = $existing_options['_backup_wallet_private_keys'];
            }
            if ( FALSE === strpos( $backup, $existing_options['wallet_private_key'] ) ) {
                $new_options['_backup_wallet_private_keys'] = $backup . ":" . $existing_options['wallet_private_key'];
            }
            // and calculate the new address from a pkey
            try {
                $ethAddressChkSum = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_address_from_key( $new_options['wallet_private_key'] );
                $new_options['wallet_address'] = $ethAddressChkSum;
            } catch ( \InvalidArgumentException $ex ) {
                CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( $ex->getMessage() );
                CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log( $ex->getTraceAsString() );
                add_settings_error(
                    'wallet_private_key',
                    esc_attr( 'bad_private_key_value' ),
                    sprintf( 'The "%1$s" value entered is not correct.', __( "Ethereum wallet private key", 'cryptocurrency-product-for-woocommerce' ) ),
                    'error'
                );
                unset($new_options['wallet_private_key']);
            }
        }
        // Merge $new_options into $existing_options to retain Cryptocurrency Product options from all other screens/tabs
        if ( $existing_options ) {
            $new_options = array_merge( $existing_options, $new_options );
        }
        if ( false !== get_option( 'cryptocurrency-product-for-woocommerce_options' ) ) {
            update_option( 'cryptocurrency-product-for-woocommerce_options', $new_options );
        } else {
            $deprecated = '';
            $autoload = 'no';
            add_option(
                'cryptocurrency-product-for-woocommerce_options',
                $new_options,
                $deprecated,
                $autoload
            );
        }
        ?>
        <div class="updated">
            <p><?php 
        _e( 'Settings saved.' );
        ?></p>
        </div>
    <?php 
    } else {
        if ( isset( $_POST['Reset'] ) ) {
            // Nonce verification
            check_admin_referer( 'cryptocurrency-product-for-woocommerce-update-options' );
            delete_option( 'cryptocurrency-product-for-woocommerce_options' );
        }
    }
    $existing_options = get_option( 'cryptocurrency-product-for-woocommerce_options', array() );
    if ( !isset( $existing_options['wallet_private_key'] ) || empty( $existing_options['wallet_private_key'] ) ) {
        // no pkey is set yet. Let's generate one
        list( $ethAddressChkSum, $privateKeyHex ) = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_create_account();
        $existing_options['wallet_address'] = $ethAddressChkSum;
        $existing_options['wallet_private_key'] = $privateKeyHex;
        if ( false !== get_option( 'cryptocurrency-product-for-woocommerce_options' ) ) {
            update_option( 'cryptocurrency-product-for-woocommerce_options', $existing_options );
        } else {
            $deprecated = '';
            $autoload = 'no';
            add_option(
                'cryptocurrency-product-for-woocommerce_options',
                $existing_options,
                $deprecated,
                $autoload
            );
        }
    }
    $options = stripslashes_deep( get_option( 'cryptocurrency-product-for-woocommerce_options', array() ) );
    ?>

    <div class="wrap">

        <h1><?php 
    _e( 'Cryptocurrency Product Settings', 'cryptocurrency-product-for-woocommerce' );
    ?></h1>

        <?php 
    settings_errors();
    ?>

        <section>
            <h1><?php 
    _e( 'Install and Configure Guide', 'cryptocurrency-product-for-woocommerce' );
    ?></h1>
            <p><?php 
    echo sprintf( __( 'Use the official %1$sInstall and Configure%2$s step by step guide to configure this plugin.', 'cryptocurrency-product-for-woocommerce' ), '<a href="https://ethereumico.io/knowledge-base/cryptocurrency-product-for-woocommerce-plugin-install-and-configure/" target="_blank">', '</a>' );
    ?></p>
        </section>

        <?php 
    if ( cryptocurrency_product_for_woocommerce_freemius_init()->is_not_paying() ) {
        echo '<section><h1>' . esc_html__( 'Awesome Premium Features', 'cryptocurrency-product-for-woocommerce' ) . '</h1>';
        echo esc_html__( 'ERC20 tokens support and more.', 'cryptocurrency-product-for-woocommerce' );
        echo ' <a href="' . cryptocurrency_product_for_woocommerce_freemius_init()->get_upgrade_url() . '">' . esc_html__( 'Upgrade Now!', 'cryptocurrency-product-for-woocommerce' ) . '</a>';
        echo '</section>';
    }
    $upgrade_message = '<p class="description">' . sprintf( __( '%1$sUpgrade Now!%2$s to enable this feature.', 'cryptocurrency-product-for-woocommerce' ), '<a href="' . cryptocurrency_product_for_woocommerce_freemius_init()->get_upgrade_url() . '" target="_blank">', '</a>' ) . '</p>';
    $disabled = 'disabled';
    ?>

        <h2 class="nav-tab-wrapper">
            <?php 
    if ( $possible_screens ) {
        foreach ( $possible_screens as $s => $sTitle ) {
            ?>
                <a href="<?php 
            echo admin_url( 'options-general.php?page=cryptocurrency-product-for-woocommerce&tab=' . esc_attr( $s ) );
            ?>" class="nav-tab<?php 
            if ( $s == $current_screen ) {
                echo ' nav-tab-active';
            }
            ?>"><?php 
            echo $sTitle;
            ?></a>
            <?php 
        }
    }
    ?>
        </h2>

        <form id="cryptocurrency-product-for-woocommerce_admin_form" method="post" action="">

            <?php 
    wp_nonce_field( 'cryptocurrency-product-for-woocommerce-update-options' );
    ?>

            <table class="form-table">

                <?php 
    if ( 'default' == $current_screen ) {
        ?>

                    <tr valign="top">
                        <th scope="row" colspan="2">
                            <h2> <?php 
        _e( "Admin Hot Wallet", 'cryptocurrency-product-for-woocommerce' );
        ?> </h2>
                        </th>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><?php 
        _e( "Ethereum wallet address", 'cryptocurrency-product-for-woocommerce' );
        ?></th>
                        <td>
                            <fieldset>
                                <label>
                                    <input disabled class="text" autocomplete="off" name="CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_wallet_address" type="text" maxlength="42" placeholder="0x0000000000000000000000000000000000000000" value="<?php 
        echo ( isset( $options['wallet_address'] ) && !empty( $options['wallet_address'] ) ? esc_attr( $options['wallet_address'] ) : '' );
        ?>">
                                    <p class="description"><?php 
        _e( "The Ethereum address of your wallet from which you would sell Ether ERC20, or NFT tokens.", 'cryptocurrency-product-for-woocommerce' );
        ?></p>
                                    <p class="description">
                                        <?php 
        echo sprintf( __( "This Ethereum address is auto generated first time you install this plugin. You can change it by changing the \"%s\" setting.", 'cryptocurrency-product-for-woocommerce' ), __( "Ethereum wallet private key", 'cryptocurrency-product-for-woocommerce' ) );
        ?>
                                    </p>
                                    <p class="description"><?php 
        _e( "Send your Ether and/or ERC20/ERC721 tokens to this address to be able to sell it.", 'cryptocurrency-product-for-woocommerce' );
        ?></p>
                                </label>
                            </fieldset>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><?php 
        _e( "Ethereum wallet private key", 'cryptocurrency-product-for-woocommerce' );
        ?></th>
                        <td>
                            <fieldset>
                                <label>
                                    <input class="text" autocomplete="off" name="CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_wallet_private_key" type="password" maxlength="128" value="">
                                    <p class="description"><?php 
        _e( "The private key of your Ethereum wallet from which you will sell Ether or ERC20 tokens. It is kept in a secret and <strong>never</strong> sent to the client side. See plugin documentation for additional security considerations.", 'cryptocurrency-product-for-woocommerce' );
        ?></p>
                                    <p class="description"><?php 
        _e( "This private key is auto generated first time you install this plugin. You can change it to your own if needed.", 'cryptocurrency-product-for-woocommerce' );
        ?></p>
                                    <p class="description">
                                        <a href="https://youtu.be/sD33huoylzU" target="_blank" rel="noopener noreferrer">
                                            <?php 
        _e( "See this video guide to learn how to import the MetaMask account private key.", 'cryptocurrency-product-for-woocommerce' );
        ?>
                                        </a>
                                    </p>
                                    <p class="description">
                                        <a href="https://youtu.be/tSn5fO_nXIQ" target="_blank" rel="noopener noreferrer">
                                            <?php 
        _e( "See this video guide to learn how to import the Ethereum Wallet account private key.", 'cryptocurrency-product-for-woocommerce' );
        ?>
                                        </a>
                                    </p>
                                </label>
                            </fieldset>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row" colspan="2">
                            <h2> <?php 
        _e( "Blockchain", 'cryptocurrency-product-for-woocommerce' );
        ?> </h2>
                        </th>
                    </tr>

                    <?php 
        $blockchain_api_nodes = [
            'infuraio' => __( 'Infura.io', 'cryptocurrency-product-for-woocommerce' ),
            'custom'   => __( 'Custom', 'cryptocurrency-product-for-woocommerce' ),
        ];
        $blockchain_api_node_current = ( isset( $options['blockchain_api_node'] ) && !empty( $options['blockchain_api_node'] ) ? $options['blockchain_api_node'] : (( isset( $options['web3Endpoint'] ) && !empty( $options['web3Endpoint'] ) ? 'custom' : 'infuraio' )) );
        // infura.io supported blockchains
        $blockchains = apply_filters( 'ethereumico.io/supported-blockchains', [] );
        ?>
                    <script type='text/javascript'>
                        if ('undefined' === typeof window['cryptocurrency']) {
                            window.cryptocurrency = {};
                        }
                        jQuery(document).ready(function() {
                            jQuery('#CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_blockchain_api_node').on('change', function(e) {
                                const blockchain_api_node = jQuery(this).find(":selected").val();
                                const blockchain_api_node_class = '.cryptocurrency-product-for-woocommerce-blockchain-api-node-' + blockchain_api_node;
                                jQuery('.cryptocurrency-product-for-woocommerce-blockchain-api-node').hide();
                                jQuery(blockchain_api_node_class).show();
                                switch (blockchain_api_node) {
                                    case 'infuraio':
                                        jQuery('#CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_blockchain_network').change();
                                        window.cryptocurrency._url_to_network_id_call_success = true;
                                        break;
                                    case 'custom':
                                        jQuery('#CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_web3Endpoint').change();
                                        break;
                                }
                            }).change();
                        });
                    </script>
                    <tr valign="top">
                        <th scope="row"><?php 
        _e( "Blockchain API Node", 'cryptocurrency-product-for-woocommerce' );
        ?></th>
                        <td>
                            <fieldset>
                                <label>
                                    <select name="CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_blockchain_api_node" id="CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_blockchain_api_node" class="select short">
                                        <?php 
        if ( is_array( $blockchain_api_nodes ) ) {
            foreach ( $blockchain_api_nodes as $blockchain_api_node => $blockchain_api_node_title ) {
                $selected = '';
                if ( $blockchain_api_node === $blockchain_api_node_current ) {
                    $selected = 'selected="selected"';
                }
                ?>
                                            <option value="<?php 
                echo esc_attr( $blockchain_api_node );
                ?>" <?php 
                echo $selected;
                ?>><?php 
                echo esc_html( $blockchain_api_node_title );
                ?></option>
                                        <?php 
            }
        }
        ?>
                                    </select>
                                    <p class="description"><?php 
        _e( "The blockchain API node used: infura.io or custom", 'cryptocurrency-product-for-woocommerce' );
        ?></p>
                                </label>
                            </fieldset>
                        </td>
                    </tr>

                    <tr valign="top" class="cryptocurrency-product-for-woocommerce-blockchain-api-node cryptocurrency-product-for-woocommerce-blockchain-api-node-infuraio">
                        <th scope="row"><?php 
        _e( "Blockchain", 'cryptocurrency-product-for-woocommerce' );
        ?></th>
                        <td>
                            <fieldset>
                                <label>
                                    <select name="CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_blockchain_network" id="CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_blockchain_network" class="select short">
                                        <?php 
        if ( is_array( $blockchains ) ) {
            foreach ( $blockchains as $blockchain => $blockchain_title ) {
                $selected = '';
                if ( isset( $options['blockchain_network'] ) && $blockchain === $options['blockchain_network'] ) {
                    $selected = 'selected="selected"';
                }
                ?>
                                            <option value="<?php 
                echo esc_attr( $blockchain );
                ?>" <?php 
                echo $selected;
                ?>><?php 
                echo esc_html( $blockchain_title );
                ?></option>
                                        <?php 
            }
        }
        ?>
                                    </select>
                                    <p class="description"><?php 
        _e( "The blockchain used: mainnet, goerli or sepolia. Use mainnet in production, and goerli or sepolia in test mode. See plugin documentation for the testing guide.", 'cryptocurrency-product-for-woocommerce' );
        ?></p>
                                    <p class="description"><strong><?php 
        _e( "NOTE: The Polygon, Optimism, Arbitrum and Avalanche C-Chain chains require special registration on the infura side.", 'cryptocurrency-product-for-woocommerce' );
        ?></strong></p>
                                </label>
                            </fieldset>
                        </td>
                    </tr>

                    <tr valign="top" class="cryptocurrency-product-for-woocommerce-blockchain-api-node cryptocurrency-product-for-woocommerce-blockchain-api-node-infuraio">
                        <th scope="row"><?php 
        _e( "Infura.io API Key", 'cryptocurrency-product-for-woocommerce' );
        ?><sup>*</sup></th>
                        <td>
                            <fieldset>
                                <label>
                                    <input class="text" name="CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_infuraApiKey" id="CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_infuraApiKey" type="text" maxlength="70" placeholder="<?php 
        _e( "Put your Infura.io API Key here", 'cryptocurrency-product-for-woocommerce' );
        ?>" value="<?php 
        echo ( isset( $options['infuraApiKey'] ) && !empty( $options['infuraApiKey'] ) ? esc_attr( $options['infuraApiKey'] ) : '' );
        ?>">
                                    <p class="description">
                                        <?php 
        echo sprintf(
            __( 'The API key for the %1$s. You need to register on this site to obtain it. Follow the %2$sGet infura API Key%3$s guide please.', 'cryptocurrency-product-for-woocommerce' ),
            '<a target="_blank" href="https://infura.io/register">https://infura.io/</a>',
            '<a target="_blank" href="https://ethereumico.io/knowledge-base/infura-api-key-guide/">',
            '</a>'
        );
        ?></p>
                                    <p class="description"><strong>
                                            <?php 
        echo sprintf( __( 'Note that this setting is ignored if the "%1$s" setting is set', 'cryptocurrency-product-for-woocommerce' ), __( "Ethereum Node JSON-RPC Endpoint", 'cryptocurrency-product-for-woocommerce' ) );
        ?></strong>
                                    </p>
                                </label>
                            </fieldset>
                        </td>
                    </tr>

                    <?php 
        $blockchainInfos = apply_filters( 'ethereumico.io/supported-blockchains-info', [] );
        $chainId = CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_getChainId();
        $blockchainInfo = apply_filters( 'ethereumico.io/blockchain-info', null, $chainId );
        // CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_log('CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_blockchain_print_options_hook3: chainId = ' . $chainId . '; ');
        $template = __( 'The API key for the %1$s. You need to %2$sregister%3$s on this site to obtain it.', 'cryptocurrency-product-for-woocommerce' );
        $explorer_api_url = ( $blockchainInfo ? $blockchainInfo['explorer_api_url'] : null );
        $explorer_register_url = ( $blockchainInfo ? $blockchainInfo['explorer_register_url'] : null );
        $base_explorer_display_url = ( $blockchainInfo ? $blockchainInfo['base_explorer_display_url'] : null );
        $blockchain_api_node_current = ( isset( $options['blockchain_api_node'] ) && !empty( $options['blockchain_api_node'] ) ? $options['blockchain_api_node'] : (( isset( $options['web3Endpoint'] ) && !empty( $options['web3Endpoint'] ) ? 'custom' : 'infuraio' )) );
        $failedNetworkIdRequestMessage = sprintf( __( "Failed to request blockchain network ID. Check your \"%s\" setting value.", 'cryptocurrency-product-for-woocommerce' ), __( "Ethereum Node JSON-RPC Endpoint", 'cryptocurrency-product-for-woocommerce' ) );
        ?>
                    <script type='text/javascript'>
                        if ('undefined' === typeof window['cryptocurrency']) {
                            window.cryptocurrency = {};
                        }
                        if ('undefined' === typeof window.cryptocurrency['_url_to_network_id_cache']) {
                            window.cryptocurrency._url_to_network_id_cache = {};
                        }
                        if ('undefined' === typeof window.cryptocurrency['_url_to_network_id_call_started']) {
                            window.cryptocurrency._url_to_network_id_call_started = {};
                        }

                        window.cryptocurrency.cryptocurrency_get_blockchain_network_id = function(web3Endpoint, blockchainInfos, callback) {
                            let alerts = {};

                            function process_alerts() {
                                Object.keys(alerts).forEach(function(message) {
                                    alert(message);
                                });
                            }

                            if ('' == web3Endpoint) {
                                callback.call(null, null, null, alerts);
                                process_alerts();
                                return;
                            }
                            if ('undefined' !== typeof window.cryptocurrency._url_to_network_id_cache[web3Endpoint]) {
                                window.cryptocurrency._url_to_network_id_call_success = true;
                                callback.call(null, null, window.cryptocurrency._url_to_network_id_cache[web3Endpoint], alerts);
                                process_alerts();
                                return;
                            }
                            if ('undefined' === typeof window.cryptocurrency._url_to_network_id_call_started[web3Endpoint]) {
                                window.cryptocurrency._url_to_network_id_call_started[web3Endpoint] = [];
                            }
                            window.cryptocurrency._url_to_network_id_call_started[web3Endpoint].push(callback);
                            if (window.cryptocurrency._url_to_network_id_call_started[web3Endpoint].length > 1) {
                                return;
                            }

                            window.cryptocurrency._url_to_network_id_call_success = '<?php 
        echo $failedNetworkIdRequestMessage;
        ?>';

                            if ('undefined' !== typeof window.cryptocurrency['_url_to_network_id_timeout_id'] &&
                                null !== window.cryptocurrency._url_to_network_id_timeout_id
                            ) {
                                clearTimeout(window.cryptocurrency._url_to_network_id_timeout_id);
                                window.cryptocurrency._url_to_network_id_timeout_id = null;
                            }
                            window.cryptocurrency._url_to_network_id_timeout_id = setTimeout(function() {
                                if (null !== window.cryptocurrency._url_to_network_id_timeout_id) {
                                    clearTimeout(window.cryptocurrency._url_to_network_id_timeout_id);
                                    window.cryptocurrency._url_to_network_id_timeout_id = null;
                                }
                                const dataRequest = '{"jsonrpc":"2.0","method":"net_version","params":[],"id":1}';
                                jQuery.ajax({
                                    url: web3Endpoint,
                                    type: "POST",
                                    data: dataRequest,
                                    contentType: "application/json; charset=utf-8",
                                    dataType: "json",
                                    success: function(data, textStatus, jqXHR) {
                                        // data: {"jsonrpc":"2.0","id":1,"result":"56"}
                                        if (!data) {
                                            console.log('Empty data returned for network', web3Endpoint);
                                            window.cryptocurrency._url_to_network_id_call_started[web3Endpoint].forEach(function(callback) {
                                                callback.call(null, 'Empty data returned for network ' + web3Endpoint, null, alerts);
                                            });
                                            process_alerts();
                                            return;
                                        }
                                        if ('undefined' === typeof data['id']) {
                                            console.log('No "id" field in the data returned for network', web3Endpoint);
                                            window.cryptocurrency._url_to_network_id_call_started[web3Endpoint].forEach(function(callback) {
                                                callback.call(null, 'No "id" field in the data returned for network ' + web3Endpoint, null, alerts);
                                            });
                                            process_alerts();
                                            return;
                                        }
                                        if (1 !== parseInt(data['id'])) {
                                            console.log('Wrong "id" field in the data returned for network', web3Endpoint);
                                            window.cryptocurrency._url_to_network_id_call_started[web3Endpoint].forEach(function(callback) {
                                                callback.call(null, 'Wrong "id" field in the data returned for network ' + web3Endpoint, null, alerts);
                                            });
                                            process_alerts();
                                            return;
                                        }
                                        if ('undefined' === typeof data['result']) {
                                            console.log('No "result" field in the data returned for network', web3Endpoint);
                                            window.cryptocurrency._url_to_network_id_call_started[web3Endpoint].forEach(function(callback) {
                                                callback.call(null, 'No "result" field in the data returned for network ' + web3Endpoint, null, alerts);
                                            });
                                            process_alerts();
                                            return;
                                        }
                                        window.cryptocurrency._url_to_network_id_call_success = true;
                                        const blockchainId = 'a' + data['result'];
                                        if ('undefined' === typeof blockchainInfos[blockchainId]) {
                                            console.log('Unsupported blockchainId(' + blockchainId + ') returned for', web3Endpoint, blockchainInfos);
                                            window.cryptocurrency._url_to_network_id_call_started[web3Endpoint].forEach(function(callback) {
                                                callback.call(null, 'Unsupported blockchainId(' + blockchainId + ') returned for ' + web3Endpoint, null, alerts);
                                            });
                                            process_alerts();
                                            return;
                                        }
                                        if ('undefined' === typeof window.cryptocurrency._url_to_network_id_cache[web3Endpoint]) {
                                            window.cryptocurrency._url_to_network_id_cache[web3Endpoint] = blockchainId;
                                        }
                                        window.cryptocurrency._url_to_network_id_call_started[web3Endpoint].forEach(function(callback) {
                                            callback.call(null, null, blockchainId, alerts);
                                        });
                                        process_alerts();
                                    },
                                    // @see https://stackoverflow.com/a/37311074/4256005
                                    error: function(xhr, status, error) {
                                        window.cryptocurrency._url_to_network_id_call_success = '<?php 
        echo $failedNetworkIdRequestMessage;
        ?>';
                                        // handle error
                                        console.log([xhr.status, xhr.responseText, status, error]);
                                        window.cryptocurrency._url_to_network_id_call_started[web3Endpoint].forEach(function(callback) {
                                            callback.call(null, '<?php 
        echo $failedNetworkIdRequestMessage;
        ?>', null, alerts);
                                        });
                                        process_alerts();
                                    }
                                })
                            }, 1000);
                        }

                        window.cryptocurrency.cryptocurrency_getBalance = function(web3Endpoint, callback) {
                            let alerts = {};

                            function process_alerts() {
                                Object.keys(alerts).forEach(function(message) {
                                    alert(message);
                                });
                            }

                            if ('' == web3Endpoint) {
                                callback.call(null, null, alerts);
                                process_alerts();
                                return;
                            }
                            const dataRequest = '{"jsonrpc":"2.0","method":"eth_getBalance","params":["0x476bb28bc6d0e9de04db5e19912c392f9a76535d", "latest"],"id":1}';

                            function processData(data) {
                                // data: {"jsonrpc":"2.0","id":1,"result":"0x28bf65e9896a69840"}
                                if (!data) {
                                    console.log('Empty data returned for network', web3Endpoint);
                                    callback.call(null, 'Empty data returned for network ' + web3Endpoint, alerts);
                                    process_alerts();
                                    return;
                                }
                                if ('undefined' === typeof data['id']) {
                                    console.log('No "id" field in the data returned for network', web3Endpoint);
                                    callback.call(null, 'No "id" field in the data returned for network ' + web3Endpoint, alerts);
                                    process_alerts();
                                    return;
                                }
                                if (1 !== parseInt(data['id'])) {
                                    console.log('Wrong "id" field in the data returned for network', web3Endpoint);
                                    callback.call(null, 'Wrong "id" field in the data returned for network ' + web3Endpoint, alerts);
                                    process_alerts();
                                    return;
                                }
                                if ('undefined' !== typeof data['error']) {
                                    if ('undefined' === typeof data['error']['message']) {
                                        console.log('No "message" field in the error data returned for network', web3Endpoint);
                                        callback.call(null, 'No "message" field in the error data returned for network ' + web3Endpoint, alerts);
                                        process_alerts();
                                        return;
                                    }
                                    console.log('The error message returned for network', web3Endpoint, ':', data['error']['message']);
                                    callback.call(null, data['error']['message'], alerts);
                                    process_alerts();
                                    return;
                                }
                                if ('undefined' === typeof data['result']) {
                                    console.log('No "result" field in the data returned for network', web3Endpoint);
                                    callback.call(null, 'No "result" field in the data returned for network ' + web3Endpoint, alerts);
                                    process_alerts();
                                    return;
                                }
                                callback.call(null, null, alerts);
                                process_alerts();
                            }
                            jQuery.ajax({
                                url: web3Endpoint,
                                type: "POST",
                                data: dataRequest,
                                contentType: "application/json; charset=utf-8",
                                dataType: "json",
                                success: processData,
                                // @see https://stackoverflow.com/a/37311074/4256005
                                error: function(xhr, status, error) {
                                    // handle error
                                    console.log([xhr.status, xhr.responseText, status, error]);
                                    if ('' !== xhr.responseText) {
                                        const data = JSON.parse(xhr.responseText);
                                        processData(data);
                                        return
                                    }
                                    callback.call(null, 'Failed to check selected infura.io blockchain availability', alerts);
                                    process_alerts();
                                }
                            })
                        }

                        jQuery(document).ready(function() {
                            const blockchainInfos = JSON.parse('<?php 
        echo json_encode( $blockchainInfos );
        ?>');

                            function cryptocurrency_blockchain_getWeb3Endpoint() {
                                const blockchain_api_node = jQuery('#CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_blockchain_api_node').find(":selected").val();
                                switch (blockchain_api_node) {
                                    case 'infuraio':
                                        const infuraApiKey = jQuery('#CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_infuraApiKey').val();
                                        const blockchain = jQuery('#CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_blockchain_network').find(":selected").val();
                                        return "https://" + blockchain + ".infura.io/v3/" + infuraApiKey;
                                    case 'custom':
                                        return jQuery('#CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_web3Endpoint').val();
                                }
                                return null;
                            }
                            window.cryptocurrency.cryptocurrency_blockchain_getWeb3Endpoint = cryptocurrency_blockchain_getWeb3Endpoint;

                            function cryptocurrency_blockchain_adjust_fields(blockchainId) {
                                let p = jQuery('#CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_etherscanApiKey').parent().find('p:first');
                                let asterix = jQuery('#CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_etherscanApiKey').parent().parent().parent().parent().find('th').find('sup');
                                if (null === blockchainId || 'undefined' === typeof blockchainInfos[blockchainId]) {
                                    p.hide();
                                    asterix.hide();
                                    return;
                                }
                                const blockchainInfo = blockchainInfos[blockchainId];
                                const template = '<?php 
        echo $template;
        ?>';
                                const arg1 = '<a target="_blank" href="' + blockchainInfo['explorer_api_url'] + '">' + blockchainInfo['base_explorer_display_url'] + '</a>';
                                const arg2 = '<a target="_blank" href="' + blockchainInfo['explorer_register_url'] + '">';
                                const arg3 = '</a>';
                                const description = template.replace('%1$s', arg1).replace('%2$s', arg2).replace('%3$s', arg3);
                                p.html(description);
                                if (null === blockchainInfo['explorer_api_url']) {
                                    p.hide();
                                    asterix.hide();
                                } else {
                                    p.show();
                                    asterix.show();
                                }
                            }
                            window.cryptocurrency.cryptocurrency_blockchain_adjust_fields = cryptocurrency_blockchain_adjust_fields;

                            jQuery('#CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_blockchain_network').on('change', function(e) {
                                const blockchain = jQuery(this).find(":selected").val();
                                cryptocurrency_blockchain_adjust_fields(blockchain);
                                const web3Endpoint = cryptocurrency_blockchain_getWeb3Endpoint();
                                window.cryptocurrency.cryptocurrency_getBalance.call(null, web3Endpoint, function(error, alerts) {
                                    if (null !== error) {
                                        cryptocurrency_blockchain_adjust_fields(null);
                                        if (-1 !== error.indexOf('project ID does not have access')) {
                                            error = '<?php 
        _e( 'Premium Infura.io account is required to use this network with the Infura.io service', 'cryptocurrency-product-for-woocommerce' );
        ?>';
                                        }
                                        alerts[error] = 1;
                                        window.cryptocurrency._url_to_network_id_call_success = error;
                                        return;
                                    }
                                    window.cryptocurrency._url_to_network_id_call_success = true;
                                });
                            });
                            <?php 
        if ( 'infuraio' === $blockchain_api_node_current ) {
            ?>
                                jQuery('#CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_blockchain_network').change();
                            <?php 
        }
        ?>

                            function cryptocurrency_refresh_blockchain(e) {
                                const web3Endpoint = jQuery('#CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_web3Endpoint').val();
                                window.cryptocurrency.cryptocurrency_get_blockchain_network_id.call(null, web3Endpoint, blockchainInfos, function(error, blockchainId, alerts) {
                                    if (null !== error) {
                                        cryptocurrency_blockchain_adjust_fields(null);
                                        alerts['<?php 
        echo $failedNetworkIdRequestMessage;
        ?>'] = 1;
                                        return;
                                    }
                                    cryptocurrency_blockchain_adjust_fields(blockchainId);
                                });
                            }
                            jQuery('#CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_web3Endpoint').on('change', cryptocurrency_refresh_blockchain);
                            <?php 
        if ( 'custom' === $blockchain_api_node_current ) {
            ?>
                                jQuery('#CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_web3Endpoint').change();
                            <?php 
        }
        ?>
                        });
                    </script>

                    <tr valign="top" class="cryptocurrency-product-for-woocommerce-blockchain-api-node cryptocurrency-product-for-woocommerce-blockchain-api-node-custom">
                        <th scope="row" colspan="2">
                            <h2><?php 
        _e( "Blockchain history and tx sending", 'cryptocurrency-product-for-woocommerce' );
        ?></h2>
                        </th>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><?php 
        _e( "Blockchain Explorer Api Key", 'cryptocurrency-product-for-woocommerce' );
        ?>
                            <?php 
        if ( !is_null( $explorer_api_url ) ) {
            ?>
                                <sup>*</sup>
                            <?php 
        }
        ?>
                        </th>
                        <td>
                            <fieldset>
                                <label>
                                    <input class="text" name="CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_etherscanApiKey" id="CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_etherscanApiKey" type="text" maxlength="35" placeholder="<?php 
        _e( "Put your Blockchain Explorer (like etherscan.io) Api Key there", 'cryptocurrency-product-for-woocommerce' );
        ?>" value="<?php 
        echo ( isset( $options['etherscanApiKey'] ) && !empty( $options['etherscanApiKey'] ) ? esc_attr( $options['etherscanApiKey'] ) : '' );
        ?>">
                                    <p><?php 
        echo sprintf(
            $template,
            '<a target="_blank" href="' . $explorer_api_url . '">' . $base_explorer_display_url . '</a>',
            '<a target="_blank" href="' . $explorer_register_url . '">',
            '</a>'
        );
        ?></p>
                                    <p><?php 
        _e( "Required for transactions history display and any ERC20 and NFT tokens related functionality.", 'cryptocurrency-product-for-woocommerce' );
        ?></p>
                                </label>
                            </fieldset>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><?php 
        _e( "Gas Limit", 'cryptocurrency-product-for-woocommerce' );
        ?></th>
                        <td>
                            <fieldset>
                                <label>
                                    <input class="text" name="CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_gas_limit" type="number" min="0" step="10000" maxlength="8" placeholder="400000" value="<?php 
        echo ( isset( $options['gas_limit'] ) && !empty( $options['gas_limit'] ) ? esc_attr( $options['gas_limit'] ) : '400000' );
        ?>">
                                    <p class="description"><?php 
        _e( "The maximum amount of gas to spend on your transactions. The actual value would be lower and estimated with standard Ethereum API. 400000 is a reasonable default value.", 'cryptocurrency-product-for-woocommerce' );
        ?></p>
                                </label>
                            </fieldset>
                        </td>
                    </tr>

                    <?php 
        $gas_price = ( isset( $options['gas_price'] ) && (!empty( $options['gas_price'] ) || '0' == $options['gas_price']) ? esc_attr( $options['gas_price'] ) : '20' );
        ?>
                    <tr valign="top">
                        <th scope="row"><?php 
        _e( "Gas price", 'cryptocurrency-product-for-woocommerce' );
        ?></th>
                        <td>
                            <fieldset>
                                <label>
                                    <input class="text" name="CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_gas_price" type="number" min="0" step="1" maxlength="8" value="<?php 
        echo $gas_price;
        ?>">
                                    <p class="description"><?php 
        _e( "The maximum gas price allowed in Gwei. Reasonable values are in a 50-250 ratio. The default value is 200 to ensure that your tx would be sent in most of the time.", 'cryptocurrency-product-for-woocommerce' );
        ?></p>
                                    <p class="description"><?php 
        _e( "The actual gas price used would be this value or less, depending on the current reasonable gas price in the blockchain.", 'cryptocurrency-product-for-woocommerce' );
        ?></p>
                                </label>
                            </fieldset>
                        </td>
                    </tr>

                    <tr valign="top" class="cryptocurrency-product-for-woocommerce-blockchain-api-node cryptocurrency-product-for-woocommerce-blockchain-api-node-custom">
                        <th scope="row" colspan="2">
                            <h2> <?php 
        _e( "Advanced Blockchain", 'cryptocurrency-product-for-woocommerce' );
        ?> </h2>
                        </th>
                    </tr>

                    <tr valign="top" class="cryptocurrency-product-for-woocommerce-blockchain-api-node cryptocurrency-product-for-woocommerce-blockchain-api-node-custom">
                        <th scope="row" colspan="2">
                            <strong><?php 
        _e( "Use these settings only if you want to use Ethereum node other than infura.io, or completely another EVM-compatible blockchain like Quorum, BSC, etc.", 'cryptocurrency-product-for-woocommerce' );
        ?></strong>
                        </th>
                    </tr>

                    <?php 
        ?>

                    <tr valign="top" class="cryptocurrency-product-for-woocommerce-blockchain-api-node cryptocurrency-product-for-woocommerce-blockchain-api-node-custom">
                        <th scope="row"><?php 
        _e( "Ethereum Node JSON-RPC Endpoint", 'cryptocurrency-product-for-woocommerce' );
        ?></th>
                        <td>
                            <fieldset>
                                <label>
                                    <input <?php 
        echo $disabled;
        ?> class="text" name="CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_web3Endpoint" id="CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_web3Endpoint" type="text" maxlength="1024" placeholder="<?php 
        _e( "Put your Ethereum Node JSON-RPC Endpoint here", 'cryptocurrency-product-for-woocommerce' );
        ?>" value="<?php 
        echo ( isset( $options['web3Endpoint'] ) && !empty( $options['web3Endpoint'] ) ? esc_attr( $options['web3Endpoint'] ) : '' );
        ?>">
                                    <p class="description">
                                        <?php 
        echo sprintf( __( 'The Ethereum Node JSON-RPC Endpoint URI. This is an advanced setting. Use with care. This setting supercedes the "%1$s" setting.', 'cryptocurrency-product-for-woocommerce' ), __( "Infura.io API Key", 'cryptocurrency-product-for-woocommerce' ) );
        ?>
                                    </p>
                                    <?php 
        echo $upgrade_message;
        ?>
                                </label>
                            </fieldset>
                        </td>
                    </tr>

                    <tr valign="top" class="cryptocurrency-product-for-woocommerce-blockchain-api-node cryptocurrency-product-for-woocommerce-blockchain-api-node-custom">
                        <th scope="row"><?php 
        _e( "Base crypto currency", 'cryptocurrency-product-for-woocommerce' );
        ?></th>
                        <td>
                            <fieldset>
                                <label>
                                    <input <?php 
        echo $disabled;
        ?> class="text" name="CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_currency_ticker" id="CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_currency_ticker" type="text" maxlength="1024" placeholder="<?php 
        _e( "Put blockchain name here", 'cryptocurrency-product-for-woocommerce' );
        ?>" value="<?php 
        echo ( isset( $options['currency_ticker'] ) && !empty( $options['currency_ticker'] ) ? esc_attr( $options['currency_ticker'] ) : 'ETH' );
        ?>">
                                    <p class="description">
                                        <?php 
        echo sprintf( __( 'The base crypto currency ticker for the blockchain configured, like ETH for Ethereum or BNB for Binance Smart Chain. This is an advanced setting most commonly used with the "%1$s" setting. Use with care.', 'cryptocurrency-product-for-woocommerce' ), __( "Ethereum Node JSON-RPC Endpoint", 'cryptocurrency-product-for-woocommerce' ) );
        ?>
                                    </p>
                                    <?php 
        echo $upgrade_message;
        ?>
                                </label>
                            </fieldset>
                        </td>
                    </tr>

                    <tr valign="top" class="cryptocurrency-product-for-woocommerce-blockchain-api-node cryptocurrency-product-for-woocommerce-blockchain-api-node-custom">
                        <th scope="row"><?php 
        _e( "Base crypto currency name", 'cryptocurrency-product-for-woocommerce' );
        ?></th>
                        <td>
                            <fieldset>
                                <label>
                                    <input <?php 
        echo $disabled;
        ?> class="text" name="CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_currency_ticker_name" id="CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_currency_ticker_name" type="text" maxlength="1024" placeholder="<?php 
        _e( "Put blockchain name here", 'cryptocurrency-product-for-woocommerce' );
        ?>" value="<?php 
        echo ( isset( $options['currency_ticker_name'] ) && !empty( $options['currency_ticker_name'] ) ? esc_attr( $options['currency_ticker_name'] ) : 'Ether' );
        ?>">
                                    <p class="description">
                                        <?php 
        echo sprintf( __( 'The base crypto currency ticker name for the blockchain configured, like Ether for Ethereum or BNB for Binance Smart Chain. This is an advanced setting most commonly used with the "%1$s" setting. Use with care.', 'cryptocurrency-product-for-woocommerce' ), __( "Ethereum Node JSON-RPC Endpoint", 'cryptocurrency-product-for-woocommerce' ) );
        ?>
                                    </p>
                                    <?php 
        echo $upgrade_message;
        ?>
                                </label>
                            </fieldset>
                        </td>
                    </tr>

                    <tr valign="top" class="cryptocurrency-product-for-woocommerce-blockchain-api-node cryptocurrency-product-for-woocommerce-blockchain-api-node-custom">
                        <th scope="row"><?php 
        _e( "Token standard name", 'cryptocurrency-product-for-woocommerce' );
        ?></th>
                        <td>
                            <fieldset>
                                <label>
                                    <input <?php 
        echo $disabled;
        ?> class="text" name="CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_token_standard_name" id="CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_token_standard_name" type="text" maxlength="1024" placeholder="<?php 
        _e( "Put token standard name here", 'cryptocurrency-product-for-woocommerce' );
        ?>" value="<?php 
        echo ( isset( $options['token_standard_name'] ) && !empty( $options['token_standard_name'] ) ? esc_attr( $options['token_standard_name'] ) : 'ERC20' );
        ?>">
                                    <p class="description">
                                        <?php 
        echo sprintf( __( 'The crypto currency token standard name for the blockchain configured, like ERC20 for Ethereum or BEP20 for Binance Smart Chain. This is an advanced setting most commonly used with the "%1$s" setting. Use with care.', 'cryptocurrency-product-for-woocommerce' ), __( "Ethereum Node JSON-RPC Endpoint", 'cryptocurrency-product-for-woocommerce' ) );
        ?>
                                    </p>
                                    <?php 
        echo $upgrade_message;
        ?>
                                </label>
                            </fieldset>
                        </td>
                    </tr>

                    <tr valign="top" class="cryptocurrency-product-for-woocommerce-blockchain-api-node cryptocurrency-product-for-woocommerce-blockchain-api-node-custom">
                        <th scope="row"><?php 
        _e( "Blockchain display name", 'cryptocurrency-product-for-woocommerce' );
        ?></th>
                        <td>
                            <fieldset>
                                <label>
                                    <input <?php 
        echo $disabled;
        ?> class="text" name="CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_blockchain_display_name" id="CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_blockchain_display_name" type="text" maxlength="1024" placeholder="<?php 
        _e( "Put blockchain name here", 'cryptocurrency-product-for-woocommerce' );
        ?>" value="<?php 
        echo ( isset( $options['blockchain_display_name'] ) && !empty( $options['blockchain_display_name'] ) ? esc_attr( $options['blockchain_display_name'] ) : 'Ethereum' );
        ?>">
                                    <p class="description">
                                        <?php 
        echo sprintf( __( 'The display name for the blockchain configured, like Ethereum or Binance Smart Chain. This is an advanced setting most commonly used with the "%1$s" setting. Use with care.', 'cryptocurrency-product-for-woocommerce' ), __( "Ethereum Node JSON-RPC Endpoint", 'cryptocurrency-product-for-woocommerce' ) );
        ?>
                                    </p>
                                    <?php 
        echo $upgrade_message;
        ?>
                                </label>
                            </fieldset>
                        </td>
                    </tr>

                    <tr valign="top" class="cryptocurrency-product-for-woocommerce-blockchain-api-node cryptocurrency-product-for-woocommerce-blockchain-api-node-custom">
                        <th scope="row"><?php 
        _e( "Transaction explorer URL", 'cryptocurrency-product-for-woocommerce' );
        ?></th>
                        <td>
                            <fieldset>
                                <label>
                                    <input <?php 
        echo $disabled;
        ?> class="text" name="CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_view_transaction_url" id="CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_view_transaction_url" type="text" maxlength="1024" placeholder="<?php 
        _e( "Put your Transaction explorer URL template here", 'cryptocurrency-product-for-woocommerce' );
        ?>" value="<?php 
        echo ( isset( $options['view_transaction_url'] ) && !empty( $options['view_transaction_url'] ) ? esc_attr( $options['view_transaction_url'] ) : '' );
        ?>">
                                    <p class="description">
                                        <?php 
        echo sprintf( __( 'The Ethereum transaction explorer URL template. It should contain %%s pattern to insert tx hash to. This is an advanced setting most commonly used with the "%1$s" setting. Use with care.', 'cryptocurrency-product-for-woocommerce' ), __( "Ethereum Node JSON-RPC Endpoint", 'cryptocurrency-product-for-woocommerce' ) );
        ?>
                                    </p>
                                    <?php 
        echo $upgrade_message;
        ?>
                                </label>
                            </fieldset>
                        </td>
                    </tr>

                    <tr valign="top" class="cryptocurrency-product-for-woocommerce-blockchain-api-node cryptocurrency-product-for-woocommerce-blockchain-api-node-custom">
                        <th scope="row"><?php 
        _e( "Address explorer URL", 'cryptocurrency-product-for-woocommerce' );
        ?></th>
                        <td>
                            <fieldset>
                                <label>
                                    <input <?php 
        echo $disabled;
        ?> class="text" name="CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_view_address_url" id="CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_view_address_url" type="text" maxlength="1024" placeholder="<?php 
        _e( "Put your Transaction explorer URL template here", 'cryptocurrency-product-for-woocommerce' );
        ?>" value="<?php 
        echo ( isset( $options['view_address_url'] ) && !empty( $options['view_address_url'] ) ? esc_attr( $options['view_address_url'] ) : '' );
        ?>">
                                    <p class="description">
                                        <?php 
        echo sprintf( __( 'The Ethereum address explorer URL template. It should contain %%s pattern to insert address hash to. This is an advanced setting most commonly used with the "%1$s" setting. Use with care.', 'cryptocurrency-product-for-woocommerce' ), __( "Ethereum Node JSON-RPC Endpoint", 'cryptocurrency-product-for-woocommerce' ) );
        ?>
                                    </p>
                                    <?php 
        echo $upgrade_message;
        ?>
                                </label>
                            </fieldset>
                        </td>
                    </tr>

                    <tr valign="top" class="cryptocurrency-product-for-woocommerce-blockchain-api-node cryptocurrency-product-for-woocommerce-blockchain-api-node-custom">
                        <th scope="row"><?php 
        _e( "Token Transactions List API URL", 'cryptocurrency-product-for-woocommerce' );
        ?></th>
                        <td>
                            <fieldset>
                                <label>
                                    <input <?php 
        echo $disabled;
        ?> class="text" name="CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_token_tx_list_api_url" id="CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_token_tx_list_api_url" type="text" maxlength="2048" placeholder="<?php 
        _e( "Put your Token Transactions List API URL template here", 'cryptocurrency-product-for-woocommerce' );
        ?>" value="<?php 
        echo ( isset( $options['token_tx_list_api_url'] ) && !empty( $options['token_tx_list_api_url'] ) ? esc_attr( $options['token_tx_list_api_url'] ) : '' );
        ?>">
                                    <p class="description">
                                        <?php 
        echo sprintf( __( 'The Ethereum Token Transactions List API URL template. It should contain %%s pattern to insert address hash to. This is an advanced setting most commonly used with the "%1$s" setting. Use with care.', 'cryptocurrency-product-for-woocommerce' ), __( "Ethereum Node JSON-RPC Endpoint", 'cryptocurrency-product-for-woocommerce' ) );
        ?>
                                    </p>
                                    <p class="description">
                                        <?php 
        echo sprintf( __( 'For the etherscan.io like APIs it would look like %1$s', 'cryptocurrency-product-for-woocommerce' ), '<pre>https://api.bscscan.com/api?module=account&action=tokentx&address=%s&startblock=0&endblock=99999999&sort=desc&apikey=1234567890</pre>' );
        ?></p>
                                    <?php 
        echo $upgrade_message;
        ?>
                                </label>
                            </fieldset>
                        </td>
                    </tr>

                    <?php 
        do_action( 'cryptocurrency_product_for_woocommerce_advanced_blockchain_print_options', $options, $current_screen );
        ?>

                    <tr valign="top">
                        <th scope="row" colspan="2">
                            <h2> <?php 
        _e( "Checkout Page Settings", 'cryptocurrency-product-for-woocommerce' );
        ?> </h2>
                        </th>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><?php 
        _e( "Ethereum Wallet meta key", 'cryptocurrency-product-for-woocommerce' );
        ?></th>
                        <td>
                            <fieldset>
                                <label>
                                    <?php 
        $wallet_meta_value = '';
        if ( isset( $options['wallet_meta'] ) && !empty( $options['wallet_meta'] ) ) {
            $wallet_meta_value = esc_attr( $options['wallet_meta'] );
        } else {
            if ( function_exists( 'ETHEREUM_WALLET_get_wallet_address' ) ) {
                $wallet_meta_value = "user_ethereum_wallet_address";
            }
        }
        ?>
                                    <input class="text" name="CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_wallet_meta" type="text" value="<?php 
        echo $wallet_meta_value;
        ?>">
                                    <p class="description"><?php 
        _e( "The meta key used in plugin like Ultimate Member for an Ethereum wallet address field in user registration form. It can be used here to pre-fill the Ethereum wallet field on the Checkout page.", 'cryptocurrency-product-for-woocommerce' );
        ?></p>
                                    <p class="description">
                                        <?php 
        echo sprintf(
            __( 'The <strong>user_ethereum_wallet_address</strong> can be used here to pre-fill this field from the %1$s%2$s%3$s on the Checkout page.', 'cryptocurrency-product-for-woocommerce' ),
            '<a href="https://ethereumico.io/product/wordpress-ethereum-wallet-plugin/" target="_blank" rel="nofollow">',
            'Wordpress Ethereum Wallet plugin',
            '</a>'
        );
        ?>
                                    </p>
                                </label>
                            </fieldset>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><?php 
        _e( "Disable Ethereum Wallet field?", 'cryptocurrency-product-for-woocommerce' );
        ?></th>
                        <td>
                            <fieldset>
                                <label>
                                    <input class="checkbox" name="CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_wallet_field_disable" type="checkbox" <?php 
        echo ( isset( $options['wallet_field_disable'] ) && !empty( $options['wallet_field_disable'] ) ? 'checked' : '' );
        ?>>
                                    <p class="description"><?php 
        _e( "If the Ethereum Wallet meta key value is used, you can disable the Ethereum wallet field on the Checkout page. It prevents user to buy tokens to any other address except the registered one.", 'cryptocurrency-product-for-woocommerce' );
        ?></p>
                                </label>
                            </fieldset>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><?php 
        _e( "Require enough Ether on a Checkout page?", 'cryptocurrency-product-for-woocommerce' );
        ?></th>
                        <td>
                            <fieldset>
                                <label>
                                    <input <?php 
        echo $disabled;
        if ( empty( $disabled ) && !in_array( 'ethereum-wallet-premium/ethereum-wallet.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
            echo 'disabled';
        }
        ?> class="checkbox" name="CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_require_enough_ether" type="checkbox" <?php 
        echo ( isset( $options['require_enough_ether'] ) && !empty( $options['require_enough_ether'] ) ? 'checked' : '' );
        ?>>
                                    <p class="description"><?php 
        _e( "If this setting is set, user would not be able to place an order if the Ether balance on her Ethereum Wallet plugin's generated account is not enough to pay for the order.", 'cryptocurrency-product-for-woocommerce' );
        ?></p>
                                    <p class="description">
                                        <?php 
        echo sprintf( __( 'Make sure to configure "%1$s" or "%2$s" settings to use this feature.', 'cryptocurrency-product-for-woocommerce' ), __( "Coinmarketcap.com API Key", 'cryptocurrency-product-for-woocommerce' ), __( "Cryptocompare.com API Key", 'cryptocurrency-product-for-woocommerce' ) );
        ?>
                                    </p>
                                    <?php 
        echo $upgrade_message;
        if ( !in_array( 'ethereum-wallet-premium/ethereum-wallet.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
            ?>
                                        <p class="description">
                                            <?php 
            echo sprintf( __( 'The %1$sEthereum Wallet PRO%2$s plugin is required for this feature.', 'cryptocurrency-product-for-woocommerce' ), '<a href="https://checkout.freemius.com/mode/dialog/plugin/4542/plan/7314/" target="_blank">', '</a>' );
            ?>
                                        </p>
                                    <?php 
        }
        ?>
                                </label>
                            </fieldset>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row" colspan="2">
                            <h2> <?php 
        _e( "Multi-Vendor Settings", 'cryptocurrency-product-for-woocommerce' );
        ?> </h2>
                        </th>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><?php 
        _e( "Vendor Fee", 'cryptocurrency-product-for-woocommerce' );
        ?></th>
                        <td>
                            <fieldset>
                                <label>
                                    <input <?php 
        echo $disabled;
        ?> class="text" name="CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_vendor_fee" type="number" min="0" step="1" maxlength="8" placeholder="0" value="<?php 
        echo ( isset( $options['vendor_fee'] ) && !empty( $options['vendor_fee'] ) ? esc_attr( $options['vendor_fee'] ) : '' );
        ?>">
                                    <p class="description">
                                        <?php 
        echo sprintf( __( 'The fee in %1$s vendor should pay to publish cryptocurrency product. This fee would be taken from a vendor\'s Ethereum Wallet account in Ether.', 'cryptocurrency-product-for-woocommerce' ), esc_attr( get_woocommerce_currency_symbol() ) );
        ?>
                                    </p>
                                    <?php 
        echo $upgrade_message;
        if ( in_array( 'wc-vendors-pro/wcvendors-pro.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) && (!function_exists( 'cryptocurrency_product_for_woocommerce_wcv_freemius_init' ) || !cryptocurrency_product_for_woocommerce_wcv_freemius_init()->is__premium_only() || !cryptocurrency_product_for_woocommerce_wcv_freemius_init()->can_use_premium_code()) ) {
            ?>
                                        <p class="description">
                                            <?php 
            if ( function_exists( 'cryptocurrency_product_for_woocommerce_wcv_freemius_init' ) ) {
                echo sprintf( __( 'Consider the %1$sCryptocurrency Product for WooCommerce WC Vendors Marketplace Addon%2$s for frontend multi-vendor features support.', 'cryptocurrency-product-for-woocommerce' ), '<a href="' . cryptocurrency_product_for_woocommerce_wcv_freemius_init()->get_upgrade_url() . '" target="_blank">', '</a>' );
            } else {
                echo sprintf( __( 'Consider the %1$sCryptocurrency Product for WooCommerce WC Vendors Marketplace Addon%2$s for frontend multi-vendor features support.', 'cryptocurrency-product-for-woocommerce' ), '<a href="https://checkout.freemius.com/mode/dialog/plugin/4888/plan/7859/" target="_blank">', '</a>' );
            }
            ?>
                                        </p>
                                    <?php 
        }
        ?>
                                    <?php 
        if ( !in_array( 'wc-vendors-pro/wcvendors-pro.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) && !in_array( 'wc-vendors/class-wc-vendors.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) && !in_array( 'wc-frontend-manager/wc_frontend_manager.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
            echo '<p class="description">' . sprintf(
                __( 'Install the free %1$sWC Vendors Marketplace%2$s plugin for simple multi-vendor features support, or the %3$sWC Vendors Marketplace PRO%4$s plugin for advanced frontend multi-vendor features support.', 'cryptocurrency-product-for-woocommerce' ),
                '<a href="https://wordpress.org/plugins/wc-vendors/" target="_blank" rel="noreferrer noopener nofollow">',
                '</a>',
                '<a href="https://www.wcvendors.com/product/wc-vendors-pro/partner/olegabr/?campaign=wcvendorspro" target="_blank" rel="noreferrer noopener sponsored nofollow">',
                '</a>'
            ) . '</p>';
            echo '<p class="description">' . sprintf( __( 'Install the free %1$sWCFM – Frontend Manager for WooCommerce%2$s plugin for advanced frontend multi-vendor features support.', 'cryptocurrency-product-for-woocommerce' ), '<a href="https://wordpress.org/plugins/wc-frontend-manager/" target="_blank" rel="noreferrer noopener nofollow">', '</a>' ) . '</p>';
        }
        ?>
                                </label>
                            </fieldset>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><?php 
        _e( "Disable Ether product type?", 'cryptocurrency-product-for-woocommerce' );
        ?></th>
                        <td>
                            <fieldset>
                                <label>
                                    <input <?php 
        echo $disabled;
        ?> class="checkbox" name="CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_ether_product_type_disable" type="checkbox" <?php 
        echo ( isset( $options['ether_product_type_disable'] ) && !empty( $options['ether_product_type_disable'] ) ? 'checked' : '' );
        ?>>
                                    <p class="description"><?php 
        _e( "If this setting is checked, the Ether product type would not be shown on the product edit page.", 'cryptocurrency-product-for-woocommerce' );
        ?></p>
                                    <?php 
        echo $upgrade_message;
        ?>
                                </label>
                            </fieldset>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><?php 
        _e( "Disable ERC20 token product type?", 'cryptocurrency-product-for-woocommerce' );
        ?></th>
                        <td>
                            <fieldset>
                                <label>
                                    <input <?php 
        echo $disabled;
        ?> class="checkbox" name="CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_erc20_product_type_disable" type="checkbox" <?php 
        echo ( isset( $options['erc20_product_type_disable'] ) && !empty( $options['erc20_product_type_disable'] ) ? 'checked' : '' );
        ?>>
                                    <p class="description"><?php 
        _e( "If this setting is checked, the ERC20 token product type would not be shown on the product edit page.", 'cryptocurrency-product-for-woocommerce' );
        ?></p>
                                    <?php 
        echo $upgrade_message;
        ?>
                                </label>
                            </fieldset>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row" colspan="2">
                            <h2> <?php 
        _e( "Advanced Settings", 'cryptocurrency-product-for-woocommerce' );
        ?> </h2>
                        </th>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><?php 
        _e( "MultiSend Accumulation Period", 'cryptocurrency-product-for-woocommerce' );
        ?></th>
                        <td>
                            <fieldset>
                                <label>
                                    <input <?php 
        echo $disabled;
        if ( empty( $disabled ) && (!function_exists( 'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_can_use_atomic_queue' ) || !CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_can_use_atomic_queue()) ) {
            echo 'disabled';
        }
        ?> class="text" name="CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_multisend_accumulation_period" type="number" min="0" step="1" maxlength="8" placeholder="60" value="<?php 
        echo ( isset( $options['multisend_accumulation_period'] ) && !empty( $options['multisend_accumulation_period'] ) ? esc_attr( $options['multisend_accumulation_period'] ) : '60' );
        ?>">
                                    <p class="description"><?php 
        echo _e( 'How much time to wait for new orders before triggering the multi send transaction, in seconds.', 'cryptocurrency-product-for-woocommerce' );
        ?></p>
                                    <p class="description"><?php 
        echo _e( 'The MultiSend can increase your order processing rate up to 200 times and save you a lot of gas fees.', 'cryptocurrency-product-for-woocommerce' );
        ?></p>
                                    <?php 
        if ( function_exists( 'CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_can_use_atomic_queue' ) && !CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_can_use_atomic_queue() ) {
            ?>
                                        <p class="description">
                                            <?php 
            echo CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_can_not_use_atomic_queue_help_msg();
            ?></p>
                                    <?php 
        }
        ?>
                                    <?php 
        echo $upgrade_message;
        ?>
                                </label>
                            </fieldset>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><?php 
        _e( "Expiration period", 'cryptocurrency-product-for-woocommerce' );
        ?></th>
                        <td>
                            <fieldset>
                                <label>
                                    <input <?php 
        echo $disabled;
        ?> class="text" name="CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_expiration_period" type="number" min="0" step="1" maxlength="8" placeholder="7" value="<?php 
        echo ( isset( $options['expiration_period'] ) && !empty( $options['expiration_period'] ) ? esc_attr( $options['expiration_period'] ) : '7' );
        ?>">
                                    <p class="description"><?php 
        echo _e( 'Number of days to wait till mark an order as expired if no payment or blockchain transaction confirmation is detected.', 'cryptocurrency-product-for-woocommerce' );
        ?></p>
                                    <?php 
        echo $upgrade_message;
        ?>
                                </label>
                            </fieldset>
                        </td>
                    </tr>

                <?php 
    }
    ?>
                <?php 
    do_action( 'cryptocurrency_product_for_woocommerce_print_options', $options, $current_screen );
    ?>

            </table>

            <h2><?php 
    _e( "Need help to configure this plugin?", 'cryptocurrency-product-for-woocommerce' );
    ?></h2>
            <p><?php 
    echo sprintf( __( 'Feel free to %1$shire me!%2$s', 'cryptocurrency-product-for-woocommerce' ), '<a target="_blank" href="https://ethereumico.io/product/configure-wordpress-plugins/" rel="noreferrer noopener sponsored nofollow">', '</a>' );
    ?></p>

            <h2><?php 
    _e( "Need help to develop a ERC20 or ERC721 token?", 'cryptocurrency-product-for-woocommerce' );
    ?></h2>
            <p><?php 
    echo sprintf( __( 'Feel free to %1$shire me!%2$s', 'cryptocurrency-product-for-woocommerce' ), '<a target="_blank" href="https://ethereumico.io/product/smart-contract-development-services/" rel="noreferrer noopener sponsored nofollow">', '</a>' );
    ?></p>

            <h2><?php 
    _e( "Want to perform an ICO Crowdsale from your Wordpress site?", 'cryptocurrency-product-for-woocommerce' );
    ?></h2>
            <p><?php 
    echo sprintf( __( 'Install the %1$sEthereum ICO WordPress plugin%2$s!', 'cryptocurrency-product-for-woocommerce' ), '<a target="_blank" href="https://ethereumico.io/product/ethereum-ico-wordpress-plugin/" rel="noreferrer noopener sponsored nofollow">', '</a>' );
    ?></p>

            <h2><?php 
    _e( "Want to create Ethereum wallets on your Wordpress site?", 'cryptocurrency-product-for-woocommerce' );
    ?></h2>
            <p><?php 
    echo sprintf( __( 'Install the %1$sWordPress Ethereum Wallet plugin%2$s!', 'cryptocurrency-product-for-woocommerce' ), '<a target="_blank" href="https://ethereumico.io/product/wordpress-ethereum-wallet-plugin/" rel="noreferrer noopener sponsored nofollow">', '</a>' );
    ?></p>

            <?php 
    if ( cryptocurrency_product_for_woocommerce_freemius_init()->is_not_paying() ) {
        ?>
                <h2><?php 
        _e( "Want to sell ERC20 token for fiat and/or Bitcoin?", 'cryptocurrency-product-for-woocommerce' );
        ?></h2>
                <p><?php 
        echo sprintf( __( 'Install the %1$sPRO plugin version%2$s!', 'cryptocurrency-product-for-woocommerce' ), '<a target="_blank" href="' . cryptocurrency_product_for_woocommerce_freemius_init()->get_upgrade_url() . '">', '</a>' );
        ?></p>

            <?php 
    }
    ?>

            <script type='text/javascript'>
                jQuery(document).ready(function() {
                    jQuery('#cryptocurrency-product-for-woocommerce_admin_form').on('submit', function() {
                        // do validation here
                        const valid = (
                            'undefined' === typeof window.cryptocurrency ||
                            'undefined' === typeof window.cryptocurrency._url_to_network_id_call_success ||
                            true === window.cryptocurrency._url_to_network_id_call_success
                        );
                        if (!valid) {
                            alert(window.cryptocurrency._url_to_network_id_call_success);
                        }
                        return valid;
                    });
                });
            </script>

            <p class="submit">
                <input class="button-primary" type="submit" name="Submit" value="<?php 
    _e( 'Save Changes', 'cryptocurrency-product-for-woocommerce' );
    ?>" />
                <input id="CRYPTOCURRENCY_PRODUCT_FOR_WOOCOMMERCE_reset_options" type="submit" name="Reset" onclick="return confirm('<?php 
    _e( 'Are you sure you want to delete all Cryptocurrency Product options?', 'cryptocurrency-product-for-woocommerce' );
    ?>')" value="<?php 
    _e( 'Reset', 'cryptocurrency-product-for-woocommerce' );
    ?>" />
            </p>

        </form>

        <p class="alignleft">
            <?php 
    echo sprintf( __( 'If you like <strong>Cryptocurrency Product for WooCommerce</strong> please leave us a %1$s rating. A huge thanks in advance!', 'cryptocurrency-product-for-woocommerce' ), '<a href="https://wordpress.org/support/plugin/cryptocurrency-product-for-woocommerce/reviews?rate=5#new-post" target="_blank" rel="noopener noreferer">★★★★★</a>' );
    ?>
        </p>


    </div>

<?php 
}
