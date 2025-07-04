<?php
/**
 * Plugin Name: Simple Reviews
 * Description: A simple WordPress plugin that registers a custom post type for product reviews and provides REST API support.
 * Version: 1.0.0
 * Author: Your Name
 */



 
if (!defined('ABSPATH')) {
    exit;
}

class Simple_Reviews {
    public function __construct() {
        add_action('init', [$this, 'register_product_review_cpt']);        
    }

    public function register_product_review_cpt() {
        register_post_type('product_review', [
            'labels'      => [
                'name'          => 'Product Reviews',
                'singular_name' => 'Product Review'
            ],
            'public'      => true,
            'supports'    => ['title', 'editor', 'custom-fields'],
            'show_in_rest' => true,
        ]);
    }

    public function register_rest_routes() {
        // Register REST endpoint for sentiment analysys
        register_rest_route('mock-api/v1', '/sentiment', [  // Here was the error
            'methods'  => 'POST', // Allow POST requests
            'callback' => [$this, 'analyze_sentiment'],
            'permission_callback' => '__return_true', // No permission check
        ]);



        register_rest_route('mock-api/v1', '/review-history/', [
            'methods'  => 'GET',
            'callback' => [$this, 'get_review_history'],
            'permission_callback' => '__return_true',
        ]);
    }

    // Analyse Sentiment from a text input
    public function analyze_sentiment($request) {
        $params = $request->get_json_params(); // Retreive JSON parameters
        $text = isset($params['text']) ? sanitize_text_field($params['text']) : '';
        
        if (empty($text)) {
            return new WP_Error('empty_text', 'No text provided for analysis.', ['status' => 400]);
        }

        $sentiment_scores = ['positive' => 0.9, 'negative' => 0.2, 'neutral' => 0.5];
        $random_sentiment = array_rand($sentiment_scores);
        return rest_ensure_response(['sentiment' => $random_sentiment, 'score' => $sentiment_scores[$random_sentiment]]);
    }

    public function get_review_history() {
        $reviews = get_posts([
            'post_type'      => 'product_review',
            'posts_per_page' => 5,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ]);
        
        $response = [];
        foreach ($reviews as $review) {
            $response[] = [
                'id'       => $review->ID,
                'title'    => $review->post_title,
                'sentiment'=> get_post_meta($review->ID, 'sentiment', true) ?? 'neutral',
                'score'    => get_post_meta($review->ID, 'sentiment_score', true) ?? 0.5,
            ];
        }

        return rest_ensure_response($response);
    }

//Task 3: Modify the Shortcode for Sentiment Highlighting (15 min)


// Fetch reviews to display product reviews with sentiment
    public function display_product_reviews() {
        $reviews = get_posts([
            'post_type'      => 'product_review',
            'posts_per_page' => 5,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ]);

        //Define css for highlighting       
        $output = '<style>
            .review-positive { color: green; font-weight: bold; }
            .review-negative { color: red; font-weight: bold; }
        </style>';

        $output .= '<ul>';

        //Loop each review
        foreach ($reviews as $review) {

            $sentiment = get_post_meta($review->ID, 'sentiment', true) ?? 'neutral';
            $class = ($sentiment === 'positive') ? 'review-positive' : (($sentiment === 'negative') ? 'review-negative' : '');
            $output .= "<li class='$class'>{$review->post_title} (Sentiment: $sentiment)</li>";
        }
        // Add each review with sentiment styling
        $output .= '</ul>';

        return $output;
    }


    // Register shortcode to use the function here
    add_shortcode('product_reviews', 'display_product_reviews');
}
new Simple_Reviews();


// MILOS CODE HERE / TASK 2

  // Hook to init the REST API
    add_action('rest_api_init', finction(){
        // Register REST route here
        register_rest_route('mock-api/v1', '/review-history', array (
            'methods' => 'GET', // Define HTTP method
            'callback' => 'get_review_history', // Define callback function
        ));
    });

    // Callbackfunction to handle the endpoint
    function get_review_history() {
        // Query for the last 5 product reviews
    $reviews = get_posts(array(
        'post_type' => 'product_review', // Define custom post type
        'numberposts' => 5, //Limit
        'orderby' => 'date',
        'order' => 'DESC',
    ));

    // Preparation data for JSON
    $data = array();
    foreach ($reviews as $review) {
        $sentiment = get_post_meta($review ->ID, 'score', true); // This should retreive the score
        $data[] = array(
            'id' => $review->ID,
            'title' => $review ->post_title,
            'sentiment' => $sentiment,
            'score' => $score,
        );

    }
    // Return my data as a JSON

    return rest_ensure_response($data);
    }
// END HERE

// Add a new API route wp-json/mock-api/v1/outliers to detect sentiment outliers

add_action('rest_api_init', function() {
    register_rest_route('mock-api/v1', 'outliers', [
        'methods' => 'GET',
        'callback' => function() {
            return ['message' => 'SearchAtlas']
        }
    ]
}