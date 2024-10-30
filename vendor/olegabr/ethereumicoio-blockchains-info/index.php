<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

$dir = trailingslashit(dirname(__FILE__));

/**
 * Supported blockchains
 */

global $ETHEREUMICOIO_BLOCKCHAINS_INFO_VERSION;
$ETHEREUMICOIO_BLOCKCHAINS_INFO_VERSION = 1006;

/**
 * Infura chains
 */

include_once $dir . 'ethereum-mainnet.php';
include_once $dir . 'ethereum-sepolia.php';
include_once $dir . 'ethereum-goerli.php';

include_once $dir . 'palm-mainnet.php';
include_once $dir . 'palm-testnet.php';

include_once $dir . 'aurora-mainnet.php';
include_once $dir . 'aurora-testnet.php';

include_once $dir . 'polygon-mainnet.php';
include_once $dir . 'polygon-mumbai.php';

include_once $dir . 'optimism-mainnet.php';
include_once $dir . 'optimism-goerli.php';
include_once $dir . 'optimism-kovan.php';

include_once $dir . 'arbitrum-mainnet.php';
include_once $dir . 'arbitrum-goerli.php';
include_once $dir . 'arbitrum-rinkeby.php';

include_once $dir . 'avalanche-mainnet.php';
include_once $dir . 'avalanche-fuji.php';

/**
 * Non-infura chains
 */

include_once $dir . 'bsc-mainnet.php';
include_once $dir . 'bsc-testnet.php';

include_once $dir . 'pulsechain-mainnet.php';
include_once $dir . 'pulsechain-testnet-v4.php';
