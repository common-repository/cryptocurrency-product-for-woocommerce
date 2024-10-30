<?php

// Not supported by infura.io
// global $ETHEREUMICOIO_BLOCKCHAINS_INFO_VERSION;

// add_filter('ethereumico.io/supported-blockchains', function ($blockchains) {
//     return array_merge($blockchains, array(
//         "bsc" => __('Binance Smart Chain (BSC) Mainnet', 'ethereum-wallet'),
//     ));
// }, 200, 1);

add_filter('ethereumico.io/supported-blockchains-info', function ($blockchains) {
    $info = apply_filters('ethereumico.io/blockchain-info', $blockchains && isset($blockchains['a56']) ? $blockchains['a56'] : null, 56);
    return array_merge($blockchains, array(
        "bsc" => $info,
        "a56" => $info,
    ));
}, $ETHEREUMICOIO_BLOCKCHAINS_INFO_VERSION, 1);

add_filter('ethereumico.io/blockchain-info/blockchain-id', function ($blockchainId, $blockchainName) {
    if (!in_array($blockchainName, ['bsc'])) {
        return $blockchainId;
    }
    return 56;
}, $ETHEREUMICOIO_BLOCKCHAINS_INFO_VERSION, 2);

add_filter('ethereumico.io/blockchain-info/blockchain-name', function ($blockchainName, $chainId) {
    if (56 !== intval($chainId)) {
        return $blockchainName;
    }
    return 'bsc';
}, $ETHEREUMICOIO_BLOCKCHAINS_INFO_VERSION, 2);

add_filter('ethereumico.io/blockchain-info', function ($info, $chainId) {
    if (56 !== intval($chainId)) {
        return $info;
    }
    $etherscanApiKey = apply_filters('ethereumico.io/blockchain-explorer-api-key', '', $chainId);

    $base_url = 'https://api.bscscan.com/api?';
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
        'blockchain_name' => 'bsc',
        'blockchain_display_name' => 'Binance Smart Chain',
        'interblock_period_seconds' => 3,
        'currency_ticker' => 'BNB',
        'currency_name' => 'BNB',
        'fungible_token_name' => 'BEP20',
        'bulk_balance_contract_address' => '0x12604351421f013D9D42fdBb78Af21D63d011331',
        'multisend_contract_address' => '0xe5c6BABcB9209994a989C0339d90fa4a120F0CB6',
        'uniswap_v2_router_address' => '0x10ED43C718714eb63d5aA57B78B54704E256024E',
        'uniswap_v3_router_address' => null,
        'txhash_path_template' => 'https://bscscan.com/tx/%s',
        'address_path_template' => 'https://bscscan.com/address/%s',
        'nft_default_external_url_template' => null,
        'gas_price_api_url' => $gas_price_api_url,
        'ethprice_api_url' => $ethprice_api_url,
        'tx_list_api_url_template' => $tx_list_api_url,
        'internal_tx_list_api_url_template' => $internal_tx_list_api_url,
        'token_tx_list_api_url_template' => $token_tx_list_api_url,
        'nft_token_tx_list_api_url_template' => $nft_token_tx_list_api_url,
        'erc1155_token_tx_list_api_url_template' => null,
        'base_explorer_display_url' => 'bscscan.com',
        'explorer_api_url' => 'https://bscscan.com/myapikey',
        'explorer_register_url' => 'https://bscscan.com/register',
    ));
}, $ETHEREUMICOIO_BLOCKCHAINS_INFO_VERSION, 2);
