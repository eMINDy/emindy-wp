<?php

use PHPUnit\Framework\TestCase;
use EMINDY\Core\Schema;

class SchemaTest extends TestCase {
    protected function setUp(): void {
        parent::setUp();
        \Brain\Monkey\setUp();
    }

    protected function tearDown(): void {
        \Brain\Monkey\tearDown();
        parent::tearDown();
    }

    public function test_build_exercise_howto_schema_maps_steps_and_timing(): void {
        global $wp_testing_meta;

        $post              = new WP_Post( 10 );
        $post->post_title  = 'Box Breathing';
        $post->post_content = 'Breathe in 4, hold, out 4.';
        $post->post_excerpt = '';

        $wp_testing_meta = [
            10 => [
                'em_steps_json'   => wp_json_encode(
                    [
                        [ 'label' => 'Inhale for four', 'duration' => 4 ],
                        [ 'label' => 'Hold', 'duration' => 4 ],
                    ]
                ),
                'em_total_seconds'   => 120,
                'em_prep_seconds'    => 10,
                'em_perform_seconds' => 110,
                'em_supplies'        => wp_json_encode( [ 'Quiet space' ] ),
                'em_tools'           => 'Timer, Chair',
                'em_yield'           => 'Relaxation',
            ],
        ];

        $schema = Schema::build_exercise_howto_schema( $post );

        $this->assertIsArray( $schema );
        $this->assertSame( 'HowTo', $schema['@type'] );
        $this->assertCount( 2, $schema['step'] );
        $this->assertSame( 'Inhale for four', $schema['step'][0]['name'] );
        $this->assertSame( 'PT4S', $schema['step'][0]['timeRequired'] );
        $this->assertSame( 'PT120S', $schema['totalTime'] );
        $this->assertSame( 'PT10S', $schema['prepTime'] );
        $this->assertSame( 'PT110S', $schema['performTime'] );

        $this->assertSame(
            [
                [ '@type' => 'HowToSupply', 'name' => 'Quiet space' ],
            ],
            $schema['supply']
        );
        $this->assertSame(
            [
                [ '@type' => 'HowToTool', 'name' => 'Timer' ],
                [ '@type' => 'HowToTool', 'name' => 'Chair' ],
            ],
            $schema['tool']
        );
        $this->assertSame( 'Relaxation', $schema['yield'] );
    }
}
