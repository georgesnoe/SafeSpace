<?php

define('ADMIN_CONFIG_FILE', __DIR__ . '/admin_email.txt');
define('DEFAULT_ADMIN_EMAIL', 'owner@safespace.local');

define('PAYMENT_PROVIDER', 'cinetpay');
define('CINETPAY_SITE_ID', trim((string)(getenv('CINETPAY_SITE_ID') ?: '')));
define('CINETPAY_API_KEY', trim((string)(getenv('CINETPAY_API_KEY') ?: '')));
define('CINETPAY_MERCHANT_ID', trim((string)(getenv('CINETPAY_MERCHANT_ID') ?: '')));

define('PREMIUM_MONTHLY_FEE_FCFA', 2000);
define('CURRENCY_CODE', 'XOF');
