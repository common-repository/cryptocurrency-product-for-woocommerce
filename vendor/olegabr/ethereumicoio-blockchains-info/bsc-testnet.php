<?php

// Not supported by infura.io
// global $ETHEREUMICOIO_BLOCKCHAINS_INFO_VERSION;

// add_filter('ethereumico.io/supported-blockchains', function ($blockchains) {
//     return array_merge($blockchains, array(
//         "bsctest" => __('Binance Smart Chain (BSC) Mumbai Testnet', 'ethereum-wallet'),
//     ));
// }, 200, 1);

add_filter('ethereumico.io/supported-blockchains-info', function ($blockchains) {
    $info = apply_filters('ethereumico.io/blockchain-info', $blockchains && isset($blockchains['a97']) ? $blockchains['a97'] : null, 97);
    return array_merge($blockchains, array(
        "bsctest" => $info,
        "a97" => $info,
    ));
}, $ETHEREUMICOIO_BLOCKCHAINS_INFO_VERSION, 1);

add_filter('ethereumico.io/blockchain-info/blockchain-id', function ($blockchainId, $blockchainName) {
    if (!in_array($blockchainName, ['bsctest'])) {
        return $blockchainId;
    }
    return 97;
}, $ETHEREUMICOIO_BLOCKCHAINS_INFO_VERSION, 2);

add_filter('ethereumico.io/blockchain-info/blockchain-name', function ($blockchainName, $chainId) {
    if (97 !== intval($chainId)) {
        return $blockchainName;
    }
    return 'bsctest';
}, $ETHEREUMICOIO_BLOCKCHAINS_INFO_VERSION, 2);

add_filter('ethereumico.io/blockchain-info', function ($info, $chainId) {
    if (97 !== intval($chainId)) {
        return $info;
    }
    $etherscanApiKey = apply_filters('ethereumico.io/blockchain-explorer-api-key', '', $chainId);

    $base_url = 'https://api-testnet.bscscan.com/api?';
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

    return array_merge($info ? $info : array(), array(
        'blockchain_name' => 'bsctest',
        'blockchain_display_name' => 'Binance Smart Chain Mumbai',
        'interblock_period_seconds' => 3,
        'currency_ticker' => 'BNB',
        'currency_name' => 'BNB',
        'fungible_token_name' => 'BEP20',
        'bulk_balance_contract_address' => '0xb9d539878EaD1A14e8b69ebD886AFCec973941bb',
        'multisend_contract_address' => '0x0fd9EDCC7207fF58f88cCA86f4A38aA562F1235a',
        'uniswap_v2_router_address' => '0xD99D1c33F9fC3444f8101754aBC46c52416550D1',
        'uniswap_v3_router_address' => null,
        'txhash_path_template' => 'https://testnet.bscscan.com/tx/%s',
        'address_path_template' => 'https://testnet.bscscan.com/address/%s',
        'nft_default_external_url_template' => null,
        'gas_price_api_url' => $gas_price_api_url,
        'ethprice_api_url' => $ethprice_api_url,
        'tx_list_api_url_template' => $tx_list_api_url,
        'internal_tx_list_api_url_template' => $internal_tx_list_api_url,
        'token_tx_list_api_url_template' => $token_tx_list_api_url,
        'nft_token_tx_list_api_url_template' => $nft_token_tx_list_api_url,
        'erc1155_token_tx_list_api_url_template' => null,
        'base_explorer_display_url' => 'testnet.bscscan.com',
        'explorer_api_url' => 'https://bscscan.com/myapikey',
        'explorer_register_url' => 'https://bscscan.com/register',
    ));
}, $ETHEREUMICOIO_BLOCKCHAINS_INFO_VERSION, 2);
