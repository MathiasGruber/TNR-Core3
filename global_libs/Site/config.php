<?php

$config = array(
    'production' => array(
        'mysql_host' => '',
        'mysql_user' => '',
        'mysql_pass' => '',
        'mysql_name' => 'core3',
        'mysql_port' => 3306,
        'mysql_sock' => 'tmp/mysql.sock',

        'memcache_host' => '127.0.0.1',
        'memcache_port' => 11211,

        "solveMediaChallengeKey" => "",
        "solveMediaVerificationKey" => "",
        "solveMediaHashKey" => "",

        'email_username' => '',
        'email_password' => '',

        'media_bucket' => 'theninja-media',
        'media_root' => '',
        'media_aws_key' => '',
        'media_aws_secret' => ''
    ),
    'stage' => array(
        'mysql_host' => '',
        'mysql_user' => '',
        'mysql_pass' => "",
        'mysql_name' => 'core3',
        'mysql_port' => 3306,
        'mysql_sock' => 'tmp/mysql.sock',

        'memcache_host' => '127.0.0.1',
        'memcache_port' => 11211,

        "solveMediaChallengeKey" => "",
        "solveMediaVerificationKey" => "",
        "solveMediaHashKey" => "",

        'email_username' => '',
        'email_password' => '',

        'media_bucket' => 'theninja-media',
        'media_root' => 'https://theninja-media.s3-us-west-2.amazonaws.com',
        'media_aws_key' => '',
        'media_aws_secret' => ''
    ),
    'local' => array(
        'mysql_host' => '127.0.0.1',
        'mysql_user' => 'root',
        'mysql_pass' => '',
        'mysql_name' => 'core3',
        'mysql_port' => 3306,
        'mysql_sock' => 'localhost:/Applications/MAMP/tmp/mysql/mysql.sock',

        'memcache_host' => 'localhost',
        'memcache_port' => 11211,

        "solveMediaChallengeKey" => "",
        "solveMediaVerificationKey" => "",
        "solveMediaHashKey" => "",

        'email_username' => '',
        'email_password' => '',

        'media_bucket' => 'theninja-media',
        'media_root' => 'https://theninja-media.s3-us-west-2.amazonaws.com',
        'media_aws_key' => '',
        'media_aws_secret' => ''
    ),
);

define('MYSQL_HOST', $config[ENV]['mysql_host']);
define('MYSQL_USER', $config[ENV]['mysql_user']);
define('MYSQL_PASS', $config[ENV]['mysql_pass']);
define('MYSQL_NAME', $config[ENV]['mysql_name']);
define('MYSQL_PORT', $config[ENV]['mysql_port']);
define('MYSQL_SOCK', $config[ENV]['mysql_sock']);

define('MEMCACHE_HOST', $config[ENV]['memcache_host']);
define('MEMCACHE_PORT', $config[ENV]['memcache_port']);

define('SOLVEMEDIA_CHALLENGE', $config[ENV]['solveMediaChallengeKey']);
define('SOLVEMEDIA_VERIFICATION', $config[ENV]['solveMediaVerificationKey']);
define('SOLVEMEDIA_HASH', $config[ENV]['solveMediaHashKey']);

define('EMAIL_USERNAME', $config[ENV]['email_username']);
define('EMAIL_PASSWORD', $config[ENV]['email_password']);

define('MEDIA_BUCKET', $config[ENV]['media_bucket']);
define('MEDIA_ROOT', $config[ENV]['media_root']);
define('MEDIA_AWS_KEY', $config[ENV]['media_aws_key']);
define('MEDIA_AWS_SECRET', $config[ENV]['media_aws_secret']);