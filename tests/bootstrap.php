<?php
declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', __DIR__ . '/../' );
}

if ( ! defined( 'EMINDY_PRIMARY_TOPIC_META' ) ) {
    define( 'EMINDY_PRIMARY_TOPIC_META', '_em_primary_topic' );
}

require __DIR__ . '/../vendor/autoload.php';

require_once __DIR__ . '/../wp-content/plugins/emindy-core/includes/helpers.php';
require_once __DIR__ . '/../wp-content/plugins/emindy-core/includes/class-emindy-schema.php';
require_once __DIR__ . '/../wp-content/plugins/emindy-core/includes/class-emindy-shortcodes.php';
