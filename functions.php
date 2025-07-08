<?php
add_action( 'wp_enqueue_scripts', function() {
  // Enqueue parent theme stylesheet
  wp_enqueue_style( 'listingpro', get_template_directory_uri() . '/style.css' );
  // Enqueue child theme stylesheet, loading after parent
  wp_enqueue_style( 'listingpro-child',
    get_stylesheet_directory_uri() . '/style.css',
    ['listingpro'],
    filemtime( get_stylesheet_directory() . '/style.css' )
  );
});

add_action('rest_api_init', function () {
    register_rest_route('listingpro_child/v1', '/create-listing', [
        'methods'             => 'POST',
        'callback'            => 'handle_create_listing_api',
        'permission_callback' => '__return_true', // NOTE: You may want to add auth for security
    ]);
});

function handle_create_listing_api(WP_REST_Request $request) {
    $params = $request->get_json_params();

    if (empty($params['title'])) {
        return new WP_REST_Response(['error' => 'Missing title.'], 400);
    }

    $url = create_listingpro_listing($params);

    if (is_wp_error($url)) {
        return new WP_REST_Response(['error' => $url->get_error_message()], 500);
    }

    return new WP_REST_Response([
        'success' => true,
        'url'     => $url
    ], 200);
}
function create_listingpro_listing($data) {
    $title = sanitize_text_field($data['title']);

    // Step 0: Check for existing listing by title
    $existing = get_page_by_title($title, OBJECT, 'listing');
    
    $current_user_id = get_current_user_id();
    if (!$current_user_id) {
        $user = get_user_by('login', '<YOUR_USERNAME>'); // Replace <YOUR_USERNAME> with the actual username
        $current_user_id = $user ? $user->ID : 0;
    }

    if ($existing) {
        $post_id = $existing->ID;

        // Update post content if provided
        wp_update_post([
            'ID'           => $post_id,
            'post_content' => wp_kses_post($data['description'] ?? $existing->post_content),
            //'post_author'  => $current_user_id,
        ]);
    } else {
        
        // Step 1: Insert a new listing post
        $post_id = wp_insert_post([
            'post_type'    => 'listing',
            'post_title'   => $title,
            'post_content' => wp_kses_post($data['description'] ?? ''),
            'post_status'  => 'publish',
            'post_author'  => $current_user_id,
        ]);

        if (is_wp_error($post_id)) {
            return $post_id;
        }
    }

    delete_post_meta($post_id, 'lp_listingpro_options_fields');
    delete_post_meta($post_id, 'lp_listingpro_options');

    $features_to_add = $data['lp_fields']['features'] ?? [];
    $feature_ids = [];
    foreach ($features_to_add as $feature_name) {
        $term = get_term_by('name', $feature_name, 'features');
        if (!$term) {
            $term = wp_insert_term($feature_name, 'features');
            if (!is_wp_error($term)) {
                $feature_ids[] = $term['term_id'];
            }
        } else {
            $feature_ids[] = $term->term_id;
        }
    }
    
    // error_log('Final feature IDs: ' . print_r($feature_ids));
    //Step 2: Set lp_listingpro_options_fields (features, filters)
    if (!empty($data['lp_fields'])) {
        //Fix for missing 'features' key not showing in the UI 
        wp_set_object_terms($post_id, $feature_ids, 'features');
        unset($data['lp_fields']['features']);
        $data['lp_fields']['lp_feature'] = $feature_ids;
        update_post_meta($post_id, 'lp_listingpro_options_fields', $data['lp_fields']);
    }
    // Step 3: Set lp_listingpro_options (location, hours, contact info)
    if (!empty($data['lp_options'])) {
        update_post_meta($post_id, 'lp_listingpro_options', $data['lp_options']);
    }

    // Step 4: Set Taxonomies – expect string names/slugs
    if (!empty($data['Categories'])) {
        $cats = is_array($data['Categories']) ? $data['Categories'] : explode(',', $data['Categories']);
        wp_set_object_terms($post_id, array_map('trim', $cats), 'listing-category');
    }

    if (!empty($data['Locations'])) {
        $locs = is_array($data['Locations']) ? $data['Locations'] : explode(',', $data['Locations']);
        wp_set_object_terms($post_id, array_map('trim', $locs), 'location');
    }

    if (!empty($data['Tags'])) {
        $tags = is_array($data['Tags']) ? $data['Tags'] : explode(',', $data['Tags']);
        wp_set_object_terms($post_id, array_map('trim', $tags), 'list-tags');
    }
    
    return get_permalink($post_id);
}
