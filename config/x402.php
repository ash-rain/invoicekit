<?php

return [
    /*
    |--------------------------------------------------------------------------
    | x402 Payment Protocol Configuration
    |--------------------------------------------------------------------------
    |
    | Configure your wallet address, network, asset, and pricing for x402
    | payment-gated API endpoints. See X402.md for wallet setup instructions.
    |
    */

    // Your EVM wallet address that receives payments
    'pay_to' => env('X402_PAY_TO', ''),

    // CAIP-2 network identifier
    // Testnet: eip155:84532 (Base Sepolia)
    // Mainnet: eip155:8453  (Base)
    'network' => env('X402_NETWORK', 'eip155:84532'),

    // USDC contract address (matches the network above)
    // Base Sepolia: 0x036CbD53842c5426634e7929541eC2318f3dCF7e
    // Base mainnet: 0x833589fCD6eDb6E08f4c7C32D4f71b54bdA02913
    'asset' => env('X402_ASSET', '0x036CbD53842c5426634e7929541eC2318f3dCF7e'),

    // Payment facilitator URL (Coinbase-operated public facilitator)
    'facilitator_url' => env('X402_FACILITATOR_URL', 'https://x402.org/facilitator'),

    // Maximum seconds a signed payment authorization remains valid
    'max_timeout_seconds' => (int) env('X402_MAX_TIMEOUT_SECONDS', 300),

    // Price per API call in USD (converted to USDC atomic units: $1.00 = 1_000_000)
    'price_usd' => (float) env('X402_PRICE_USD', 0.001),

    // HTTP client timeout for facilitator calls (seconds)
    'facilitator_timeout' => (int) env('X402_FACILITATOR_TIMEOUT', 10),
];
