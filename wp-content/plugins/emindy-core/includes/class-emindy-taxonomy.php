<?php
namespace EMINDY\Core;
if ( ! defined( 'ABSPATH' ) ) exit;

class Taxonomy {
    /**
     * Register all custom taxonomies used by the eMINDy CPTs.  Multiple taxonomies are
     * registered here so they can be reused across exercises, videos and articles.
     *
     * The default WordPress recommendation is to register taxonomies during the
     * `init` hook and to attach them via the `taxonomies` argument when registering
     * the associated post types【776845342899939†L80-L84】.  This method therefore
     * delegates to a helper to register each taxonomy and then inserts default
     * terms for each one.
     */
    public static function register_all() {
        self::register_taxonomies();
        self::insert_default_terms();
    }

    /**
     * Register the custom taxonomies used on eMINDy.  Each taxonomy is defined
     * with singular and plural labels, whether it is hierarchical and the slug
     * used in permalinks.  They are all set to be public and exposed in the
     * REST API.
     */
    protected static function register_taxonomies() {
        $defs = [
            // slug      => [ plural label,      singular label, hierarchical, rewrite slug ]
            'topic'       => [ 'Topics',       'Topic',        true,  'topics'       ],
            'technique'   => [ 'Techniques',   'Technique',    true,  'techniques'   ],
            'duration'    => [ 'Durations',    'Duration',     false, 'duration'     ],
            'format'      => [ 'Formats',      'Format',       false, 'format'       ],
            'use_case'    => [ 'Use Cases',    'Use Case',     true,  'use-case'     ],
            'level'       => [ 'Levels',       'Level',        false, 'level'        ],
            'a11y_feature'=> [ 'Accessibility Features', 'Accessibility Feature', false, 'a11y' ],
        ];
        foreach ( $defs as $slug => $args ) {
            [ $plural, $singular, $hierarchical, $rewrite_slug ] = $args;
            register_taxonomy( $slug, [ 'em_exercise', 'em_video', 'em_article' ], [
                'labels'       => [
                    'name'          => __( $plural, 'emindy-core' ),
                    'singular_name' => __( $singular, 'emindy-core' ),
                ],
                'public'       => true,
                'show_in_rest' => true,
                'hierarchical' => $hierarchical,
                'rewrite'      => [ 'slug' => $rewrite_slug ],
            ] );
        }
    }

    /**
     * Insert a set of sensible default terms into each taxonomy.  This ensures
     * that the site has starter terms to select from without requiring manual
     * creation in the admin.  Terms are only inserted if they don’t already
     * exist, so this method can safely run on every page load without
     * duplicating data.
     */
    protected static function insert_default_terms() {
        $terms = [
            'topic' => [
                // Core wellbeing themes used across videos, exercises and articles.
                [ 'Stress Relief',       'stress-relief'       ],
                [ 'Anxiety & Clarity',   'anxiety-clarity'     ],
                [ 'Confidence & Growth', 'confidence-growth'    ],
                [ 'Quick Reset',         'quick-reset'          ],
                [ 'Hope & Inspiration',  'hope-inspiration'     ],
                [ 'Sleep & Focus',       'sleep-focus'          ],
            ],
            'technique' => [
                // Techniques and modalities offered in practices.
                [ 'Breathing',        'breathing'        ],
                [ 'Body Scan',        'body-scan'        ],
                [ 'Grounding',        'grounding'        ],
                [ 'Journaling',       'journaling'       ],
                [ 'Affirmations',     'affirmations'     ],
                [ 'Visualization',    'visualization'    ],
                [ 'Sleep Routine',    'sleep-routine'    ],
                [ 'Mindful Walking',  'mindful-walking'  ],
            ],
            'duration' => [
                // Suggested practice lengths for filtering content.
                [ '30s',    '30s'      ],
                [ '1m',     '1m'       ],
                [ '2-5m',   '2-5m'     ],
                [ '6-10m',  '6-10m'    ],
                [ '10m+',   '10m-plus' ],
            ],
            'format' => [
                // Content formats spanning media types.
                [ 'Video',     'video'     ],
                [ 'Article',   'article'   ],
                [ 'Worksheet', 'worksheet' ],
                [ 'Exercise',  'exercise'  ],
                [ 'Test',      'test'      ],
                [ 'Audio',     'audio'     ],
                [ 'Checklist', 'checklist' ],
            ],
            'use_case' => [
                // Situational use cases; confirm relevance during future audits. @todo Phase 4 content review
                [ 'Morning',        'morning'        ],
                [ 'Bedtime',        'bedtime'        ],
                [ 'Work Break',     'work-break'     ],
                [ 'Study Focus',    'study-focus'    ],
                [ 'Commute',        'commute'        ],
                [ 'Before Sleep',   'before-sleep'   ],
                [ 'Focus Block',    'focus-block'    ],
                [ 'Social Context', 'social-context' ],
            ],
            'level' => [
                // Experience levels for exercises.
                [ 'Beginner',     'beginner'     ],
                [ 'Gentle',       'gentle'       ],
                [ 'Intermediate', 'intermediate' ],
                [ 'Deep',         'deep'         ],
            ],
            'a11y_feature' => [
                // Accessibility features to flag alternative formats and support tools.
                [ 'Captions',           'captions'            ],
                [ 'Transcript',         'transcript'          ],
                [ 'Keyboard-friendly',  'keyboard-friendly'   ],
                [ 'Low-vision friendly','low-vision-friendly' ],
                [ 'No-music version',   'no-music-version'    ],
            ],
        ];
        foreach ( $terms as $tax => $list ) {
            foreach ( $list as $term ) {
                [ $name, $slug ] = $term;
                if ( ! term_exists( $slug, $tax ) ) {
                    wp_insert_term( $name, $tax, [ 'slug' => $slug ] );
                }
            }
        }
    }
}
