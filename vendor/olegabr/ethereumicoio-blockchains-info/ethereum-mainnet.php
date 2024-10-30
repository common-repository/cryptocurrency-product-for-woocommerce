<?php

global $ETHEREUMICOIO_BLOCKCHAINS_INFO_VERSION;

add_filter('ethereumico.io/supported-blockchains', function ($blockchains) {
    return array_merge($blockchains, array(
        "mainnet" => __('Ethereum Mainnet', 'ethereum-wallet'),
    ));
}, $ETHEREUMICOIO_BLOCKCHAINS_INFO_VERSION, 1);

add_filter('ethereumico.io/supported-blockchains-info', function ($blockchains) {
    $info = apply_filters('ethereumico.io/blockchain-info', $blockchains && isset($blockchains['a1']) ? $blockchains['a1'] : null, 1);
    return array_merge($blockchains, array(
        "mainnet" => $info,
        "a1" => $info,
    ));
}, $ETHEREUMICOIO_BLOCKCHAINS_INFO_VERSION, 1);

add_filter('ethereumico.io/blockchain-info/blockchain-id', function ($blockchainId, $blockchainName) {
    if (!in_array($blockchainName, ['mainnet'])) {
        return $blockchainId;
    }
    return 1;
}, $ETHEREUMICOIO_BLOCKCHAINS_INFO_VERSION, 2);

add_filter('ethereumico.io/blockchain-info/blockchain-name', function ($blockchainName, $chainId) {
    if (1 !== intval($chainId)) {
        return $blockchainName;
    }
    return 'mainnet';
}, $ETHEREUMICOIO_BLOCKCHAINS_INFO_VERSION, 2);

add_filter('ethereumico.io/blockchain-info', function ($info, $chainId) {
    if (1 !== intval($chainId)) {
        return $info;
    }
    $etherscanApiKey = apply_filters('ethereumico.io/blockchain-explorer-api-key', '', $chainId);

    $base_url = 'https://api.etherscan.io/api?';
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
        'blockchain_name' => 'mainnet',
        'blockchain_display_name' => 'Ethereum',
        'interblock_period_seconds' => 12,
        'currency_ticker' => 'ETH',
        'currency_name' => 'Ether',
        'fungible_token_name' => 'ERC20',
        'bulk_balance_contract_address' => '0x7Ad543E63eaDfE5812eAA165980650912248E146',
        'multisend_contract_address' => '0xCD5485b34c9902527bbEE21F69312fe2A73bc802',
        'uniswap_v2_router_address' => '0x7a250d5630B4cF539739dF2C5dAcb4c659F2488D',
        'uniswap_v3_router_address' => '0xE592427A0AEce92De3Edee1F18E0157C05861564',
        'txhash_path_template' => 'https://etherscan.io/tx/%s',
        'address_path_template' => 'https://etherscan.io/address/%s',
        'nft_default_external_url_template' => 'https://opensea.io/assets/ethereum/%s/%s',
        'gas_price_api_url' => $gas_price_api_url,
        'ethprice_api_url' => $ethprice_api_url,
        'tx_list_api_url_template' => $tx_list_api_url,
        'internal_tx_list_api_url_template' => $internal_tx_list_api_url,
        'token_tx_list_api_url_template' => $token_tx_list_api_url,
        'nft_token_tx_list_api_url_template' => $nft_token_tx_list_api_url,
        'erc1155_token_tx_list_api_url_template' => $erc1155_token_tx_list_api_url,
        'base_explorer_display_url' => 'etherscan.io',
        'explorer_api_url' => 'https://etherscan.io/myapikey',
        'explorer_register_url' => 'https://etherscan.io/register',
    ));
}, $ETHEREUMICOIO_BLOCKCHAINS_INFO_VERSION, 2);
