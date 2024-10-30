<?php

global $ETHEREUMICOIO_BLOCKCHAINS_INFO_VERSION;

add_filter('ethereumico.io/supported-blockchains', function ($blockchains) {
    return array_merge($blockchains, array(
        "aurora-testnet" => __('Aurora Testnet', 'ethereum-wallet'),
    ));
}, 19, 1);

add_filter('ethereumico.io/supported-blockchains-info', function ($blockchains) {
    $info = apply_filters('ethereumico.io/blockchain-info', $blockchains && isset($blockchains['a1313161556']) ? $blockchains['a1313161556'] : null, 1313161556);
    return array_merge($blockchains, array(
        "aurora-testnet" => $info,
        "a1313161556" => $info,
    ));
}, $ETHEREUMICOIO_BLOCKCHAINS_INFO_VERSION, 1);

add_filter('ethereumico.io/blockchain-info/blockchain-id', function ($blockchainId, $blockchainName) {
    if (!in_array($blockchainName, ['aurora-testnet'])) {
        return $blockchainId;
    }
    return 1313161556;
}, $ETHEREUMICOIO_BLOCKCHAINS_INFO_VERSION, 2);

add_filter('ethereumico.io/blockchain-info/blockchain-name', function ($blockchainName, $chainId) {
    if (1313161556 !== intval($chainId)) {
        return $blockchainName;
    }
    return 'aurora-testnet';
}, $ETHEREUMICOIO_BLOCKCHAINS_INFO_VERSION, 2);

add_filter('ethereumico.io/blockchain-info', function ($info, $chainId) {
    if (1313161556 !== intval($chainId)) {
        return $info;
    }
    $etherscanApiKey = apply_filters('ethereumico.io/blockchain-explorer-api-key', '', $chainId);

    $base_url = 'https://api-testnet.aurorascan.dev/api?';
    $gas_price_api_url = null;
    // $gas_price_api_url = str_replace('%25', '%', $base_url . http_build_query(array(
    //     'module' => 'gastracker',
    //     'action' => 'gasoracle',
    //     'apikey' => $etherscanApiKey,
    // )));
    $ethprice_api_url = str_replace('%25', '%', $base_url . http_build_query(array(
        'module' => 'stats',
        'action' => 'ethprice',
        'apikey' => $etherscanApiKey,
    )));
    $params_base = array(
        'module' => 'account',
        'address' => '%s',
        'startblock' => '0',
        'endblock' => '99999999',
        'sort' => 'desc',
        'apikey' => $etherscanApiKey,
    );
    $tx_list_api_url = str_replace('%25', '%', $base_url . http_build_query(array_merge($params_base, array(
        'action' => 'txlist',
    ))));
    $internal_tx_list_api_url = str_replace('%25', '%', $base_url . http_build_query(array_merge($params_base, array(
        'action' => 'txlistinternal',
    ))));
    $token_tx_list_api_url = str_replace('%25', '%', $base_url . http_build_query(array_merge($params_base, array(
        'action' => 'tokentx',
    ))));
    $nft_token_tx_list_api_url = str_replace('%25', '%', $base_url . http_build_query(array_merge($params_base, array(
        'action' => 'tokennfttx',
    ))));

    return array_merge($info ? $info : array(), array(
        'blockchain_name' => 'aurora-testnet',
        'blockchain_display_name' => 'Aurora Testnet',
        'interblock_period_seconds' => 15,
        'currency_ticker' => 'ETH',
        'currency_name' => 'Ether',
        'fungible_token_name' => 'ERC20',
        'bulk_balance_contract_address' => null,
        'multisend_contract_address' => null,
        'uniswap_v2_router_address' => null,
        'uniswap_v3_router_address' => null,
        'txhash_path_template' => 'https://testnet.aurorascan.dev/tx/%s',
        'address_path_template' => 'https://testnet.aurorascan.dev/address/%s',
        'nft_default_external_url_template' => null,
        'gas_price_api_url' => $gas_price_api_url,
        'ethprice_api_url' => $ethprice_api_url,
        'tx_list_api_url_template' => $tx_list_api_url,
        'internal_tx_list_api_url_template' => $internal_tx_list_api_url,
        'token_tx_list_api_url_template' => $token_tx_list_api_url,
        'nft_token_tx_list_api_url_template' => $nft_token_tx_list_api_url,
        'erc1155_token_tx_list_api_url_template' => null,
        'base_explorer_display_url' => 'testnet.aurorascan.dev',
        'explorer_api_url' => 'https://aurorascan.dev/myapikey',
        'explorer_register_url' => 'https://aurorascan.dev/register',
    ));
}, $ETHEREUMICOIO_BLOCKCHAINS_INFO_VERSION, 2);
