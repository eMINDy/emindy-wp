<?php

namespace EMINDY\Core;

if ( ! defined( 'ABSPATH' ) ) {
        exit;
}

/**
 * Diagnostic helpers to surface staging issues in debug logs.
 */
class Diagnostics {
        /**
         * Hook debug checks.
         */
        public static function register() {
                add_action( 'init', [ __CLASS__, 'run_staging_checks' ], 5 );
        }

        /**
         * Run staging-only diagnostics when WP_DEBUG is enabled.
         *
         * Logs missing class definitions and custom tables that should be
         * created by the activation hook. This helps catch fatals before they
         * reach production.
         */
        public static function run_staging_checks() {
                if ( ! self::should_log() ) {
                        return;
                }

                self::verify_classes();
                self::verify_tables();
        }

        /**
         * Confirm all required classes are loaded.
         */
        private static function verify_classes() {
                $classes = [
                        '\\EMINDY\\Core\\CPT',
                        '\\EMINDY\\Core\\Taxonomy',
                        '\\EMINDY\\Core\\Shortcodes',
                        '\\EMINDY\\Core\\Content_Inject',
                        '\\EMINDY\\Core\\Meta',
                        '\\EMINDY\\Core\\Schema',
                        '\\EMINDY\\Core\\Admin',
                        '\\EMINDY\\Core\\Ajax',
                        '\\EMINDY\\Core\\Analytics',
                ];

                $missing = array_values(
                        array_filter(
                                $classes,
                                static function ( $class ) {
                                        return ! class_exists( $class );
                                }
                        )
                );

                if ( $missing ) {
                        self::log( 'Missing required classes: ' . implode( ', ', $missing ) );
                }
        }

        /**
         * Confirm activation-created tables exist.
         */
        private static function verify_tables() {
                global $wpdb;

                $tables   = [
                        $wpdb->prefix . 'emindy_newsletter',
                        $wpdb->prefix . 'emindy_analytics',
                ];
                $missing  = [];

                foreach ( $tables as $table ) {
                        $exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table ) ) );

                        if ( $table !== $exists ) {
                                $missing[] = $table;
                        }
                }

                if ( $missing ) {
                        self::log(
                                'Missing custom tables: ' . implode( ', ', $missing ) . '. Reactivate the plugin to rerun the activation hook and recreate them.'
                        );
                }
        }

        /**
         * Whether diagnostics should be logged.
         *
         * @return bool
         */
        private static function should_log() {
                $is_staging = function_exists( 'wp_get_environment_type' ) && 'staging' === wp_get_environment_type();

                return defined( 'WP_DEBUG' ) && WP_DEBUG && $is_staging;
        }

        /**
         * Write a message to the error log.
         *
         * @param string $message Message to log.
         */
        private static function log( $message ) {
                if ( empty( $message ) ) {
                        return;
                }

                error_log( '[eMINDy Core] ' . $message );
        }
}
