<?php

global $ETHEREUMICOIO_BLOCKCHAINS_INFO_VERSION;

add_filter('ethereumico.io/supported-blockchains', function ($blockchains) {
    return array_merge($blockchains, array(
        "polygon-mainnet" => __('Polygon Mainnet', 'ethereum-wallet'),
    ));
}, 20, 1);

add_filter('ethereumico.io/supported-blockchains-info', function ($blockchains) {
    $info = apply_filters('ethereumico.io/blockchain-info', $blockchains && isset($blockchains['a137']) ? $blockchains['a137'] : null, 137);
    return array_merge($blockchains, array(
        "polygon" => $info,
        "polygon-mainnet" => $info,
        "a137" => $info,
    ));
}, $ETHEREUMICOIO_BLOCKCHAINS_INFO_VERSION, 1);

add_filter('ethereumico.io/blockchain-info/blockchain-id', function ($blockchainId, $blockchainName) {
    if (!in_array($blockchainName, [
        'polygon', 'polygon-mainnet'
    ])) {
        return $blockchainId;
    }
    return 137;
}, $ETHEREUMICOIO_BLOCKCHAINS_INFO_VERSION, 2);

add_filter('ethereumico.io/blockchain-info/blockchain-name', function ($blockchainName, $chainId) {
    if (137 !== intval($chainId)) {
        return $blockchainName;
    }
    return 'polygon';
}, $ETHEREUMICOIO_BLOCKCHAINS_INFO_VERSION, 2);

add_filter('ethereumico.io/blockchain-info', function ($info, $chainId) {
    if (137 !== intval($chainId)) {
        return $info;
    }
    $etherscanApiKey = apply_filters('ethereumico.io/blockchain-explorer-api-key', '', $chainId);

    $base_url = 'https://api.polygonscan.com/api?';
    //https://api.polygonscan.com/api?module=gastracker&action=gasoracle
    $gas_price_api_url = str_replace('%25', '%', $base_url . http_build_query(array(
        'module' => 'gastracker',
        'action' => 'gasoracle',
        'apikey' => $etherscanApiKey,
    )));
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
    $erc1155_token_tx_list_api_url = str_replace('%25', '%', $base_url . http_build_query(array_merge($params_base, array(
        'action' => 'token1155tx',
    ))));

    return array_merge($info ? $info : array(), array(
        'blockchain_name' => 'polygon',
        'blockchain_display_name' => 'Polygon',
        'interblock_period_seconds' => 2,
        'currency_ticker' => 'MATIC',
        'currency_name' => 'MATIC',
        'fungible_token_name' => 'ERC20',
        'bulk_balance_contract_address' => '0x7dE9558A41d04E62e220984436aE1Dd08927176d',
        'multisend_contract_address' => '0xe5c6BABcB9209994a989C0339d90fa4a120F0CB6',
        'uniswap_v2_router_address' => null,
        'uniswap_v3_router_address' => '0xE592427A0AEce92De3Edee1F18E0157C05861564',
        'txhash_path_template' => 'https://polygonscan.com/tx/%s',
        'address_path_template' => 'https://polygonscan.com/address/%s',
        'nft_default_external_url_template' => 'https://opensea.io/assets/matic/%s/%s',
        'gas_price_api_url' => $gas_price_api_url,
        'ethprice_api_url' => $ethprice_api_url,
        'tx_list_api_url_template' => $tx_list_api_url,
        'internal_tx_list_api_url_template' => $internal_tx_list_api_url,
        'token_tx_list_api_url_template' => $token_tx_list_api_url,
        'nft_token_tx_list_api_url_template' => $nft_token_tx_list_api_url,
        'erc1155_token_tx_list_api_url_template' => $erc1155_token_tx_list_api_url,
        'base_explorer_display_url' => 'polygonscan.com',
        'explorer_api_url' => 'https://polygonscan.com/myapikey',
        'explorer_register_url' => 'https://polygonscan.com/register',
    ));
}, $ETHEREUMICOIO_BLOCKCHAINS_INFO_VERSION, 2);
