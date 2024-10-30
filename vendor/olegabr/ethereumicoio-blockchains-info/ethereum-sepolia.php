<?php

global $ETHEREUMICOIO_BLOCKCHAINS_INFO_VERSION;

add_filter('ethereumico.io/supported-blockchains', function ($blockchains) {
    return array_merge($blockchains, array(
        "sepolia" => __('Ethereum Sepolia Testnet', 'ethereum-wallet'),
    ));
}, 11, 1);

add_filter('ethereumico.io/supported-blockchains-info', function ($blockchains) {
    $info = apply_filters('ethereumico.io/blockchain-info', $blockchains && isset($blockchains['a11155111']) ? $blockchains['a11155111'] : null, 11155111);
    return array_merge($blockchains, array(
        "sepolia" => $info,
        "a11155111" => $info,
    ));
}, $ETHEREUMICOIO_BLOCKCHAINS_INFO_VERSION, 1);

add_filter('ethereumico.io/blockchain-info/blockchain-id', function ($blockchainId, $blockchainName) {
    if (!in_array($blockchainName, ['sepolia'])) {
        return $blockchainId;
    }
    return 11155111;
}, $ETHEREUMICOIO_BLOCKCHAINS_INFO_VERSION, 2);

add_filter('ethereumico.io/blockchain-info/blockchain-name', function ($blockchainName, $chainId) {
    if (11155111 !== intval($chainId)) {
        return $blockchainName;
    }
    return 'sepolia';
}, $ETHEREUMICOIO_BLOCKCHAINS_INFO_VERSION, 2);

add_filter('ethereumico.io/blockchain-info', function ($info, $chainId) {
    if (11155111 !== intval($chainId)) {
        return $info;
    }
    $etherscanApiKey = apply_filters('ethereumico.io/blockchain-explorer-api-key', '', $chainId);

    $base_url = 'https://api-sepolia.etherscan.io/api?';
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
        'blockchain_name' => 'sepolia',
        'blockchain_display_name' => 'Ethereum Sepolia',
        'interblock_period_seconds' => 12,
        'currency_ticker' => 'ETH',
        'currency_name' => 'Ether',
        'fungible_token_name' => 'ERC20',
        'bulk_balance_contract_address' => '0x0564705e6a2DDBf823f8d37E5d68abAB28f131EB',
        'multisend_contract_address' => '0x77913766661274651d367A013861B64111E77A3f',
        'uniswap_v2_router_address' => null,
        'uniswap_v3_router_address' => null,
        'txhash_path_template' => 'https://sepolia.etherscan.io/tx/%s',
        'address_path_template' => 'https://sepolia.etherscan.io/address/%s',
        'nft_default_external_url_template' => null,
        'gas_price_api_url' => $gas_price_api_url,
        'ethprice_api_url' => $ethprice_api_url,
        'tx_list_api_url_template' => $tx_list_api_url,
        'internal_tx_list_api_url_template' => $internal_tx_list_api_url,
        'token_tx_list_api_url_template' => $token_tx_list_api_url,
        'nft_token_tx_list_api_url_template' => $nft_token_tx_list_api_url,
        'erc1155_token_tx_list_api_url_template' => $erc1155_token_tx_list_api_url,
        'base_explorer_display_url' => 'sepolia.etherscan.io',
        'explorer_api_url' => 'https://etherscan.io/myapikey',
        'explorer_register_url' => 'https://etherscan.io/register',
    ));
}, $ETHEREUMICOIO_BLOCKCHAINS_INFO_VERSION, 2);
