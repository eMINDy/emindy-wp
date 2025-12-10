<?php

use PHPUnit\Framework\TestCase;
use EMINDY\Core\Shortcodes;

class RelatedShortcodeTest extends TestCase {
    protected function setUp(): void {
        parent::setUp();
        \Brain\Monkey\setUp();
        WP_Query::$next_results = [];
        WP_Query::$last_args    = [];
        $GLOBALS['wp_testing_meta']          = [];
        $GLOBALS['wp_testing_terms']         = [];
        $GLOBALS['wp_testing_terms_by_slug'] = [];
    }

    protected function tearDown(): void {
        \Brain\Monkey\tearDown();
        parent::tearDown();
    }

    public function test_related_shortcodes_excludes_current_post_and_limits_count(): void {
        global $post, $wp_testing_meta;

        $post             = new WP_Post( 1 );
        $post->post_title = 'Focus exercise';
        $post->post_excerpt = 'A short description.';
        $post->post_type = 'em_exercise';

        $wp_testing_meta[1][ EMINDY_PRIMARY_TOPIC_META ] = 99;

        $related_one              = new WP_Post( 2 );
        $related_one->post_title  = 'Breathing basics';
        $related_one->post_excerpt = 'Learn to breathe.';

        $related_two              = new WP_Post( 3 );
        $related_two->post_title  = 'Calming body scan';
        $related_two->post_excerpt = 'Scan body to relax.';

        WP_Query::$next_results = [ [ $related_one, $related_two ] ];

        $output = Shortcodes::related( [ 'count' => 2 ] );

        $this->assertStringContainsString( 'em-related-grid', $output );
        $this->assertStringContainsString( $related_one->post_title, $output );
        $this->assertStringContainsString( $related_two->post_title, $output );

        $this->assertSame( [ 1 ], WP_Query::$last_args[0]['post__not_in'] );
        $this->assertSame( 2, WP_Query::$last_args[0]['posts_per_page'] );
        $this->assertArrayHasKey( 'tax_query', WP_Query::$last_args[0] );
        $this->assertSame( 99, WP_Query::$last_args[0]['tax_query'][0]['terms'][0] );
    }
}
