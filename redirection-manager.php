<?php
/*
Plugin Name: Redirection Manager
Description: allows to add redirection rules
Version:     1.0
Author:      Roman

 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

//  function to register redirection

function Create_Register_Post_Type()
{
    $labels = array(
        'name' => _x('Redirection Manager', 'Post type general name', 'textdomain'),
        'singular_name' => _x('rule', 'Post type singular name', 'textdomain'),
        'menu_name' => _x('redirection manager', 'Admin Menu text', 'textdomain'),
        'name_admin_bar' => _x('rule', 'Add New on Toolbar', 'textdomain'),

    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'rule'),
        'capability_type' => 'post',
        'menu_icon' => 'dashicons-randomize',
        'has_archive' => true,
        'hierarchical' => false,
        'rewrite' => array('slug' => 'rule'),
        'capability_type' => 'post',
        'supports' => array(
            'title',
        ),

    );

    register_post_type('rule', $args);
}

add_action('init', 'Create_Register_Post_Type');

/**
 * Register meta box for redirection post type
 */
function redirection_rule_box()
{
    $rule = 'rule';

    add_meta_box(
        'redirection_rule_box',
        'redirection_rules',
        'redirection_rule_inputs',
        $rule
    );

}
add_action('add_meta_boxes', 'redirection_rule_box');

function redirection_rule_inputs($post)
{
    $redirection_type = get_post_meta($post->ID, 'redirection_type', true);
    $redirection_from = get_post_meta($post->ID, 'redirection_from', true);
    $redirection_to = get_post_meta($post->ID, 'redirection_to', true);
    ?>
<label for="redirection_type_field">Description for this field</label>
<select name="redirection_type_field" id="redirection_type_field" class="postbox">
    <option value="301" <?php selected($redirection_type, '301');?>>301</option>
    <option value="302" <?php selected($redirection_type, '302');?>>302</option>
</select>

<label for="redirection_from_field">redirection from:</label>
<input type="text" value="<?php echo $redirection_from ?>" id="redirection_from_field" name="redirection_from_field">


<label for="redirection_to_field">redirection to:</label>
<input type="text" value="<?php echo $redirection_to ?>" id="redirection_to_field" name="redirection_to_field">










<?php
}

function wporg_save_postdata($post_id)
{
    // save redirection type
    if (array_key_exists('redirection_type_field', $_POST)) {
        update_post_meta(
            $post_id,
            'redirection_type',
            $_POST['redirection_type_field']
        );
    }

    // save redirection from
    if (array_key_exists('redirection_from_field', $_POST)) {
        update_post_meta(
            $post_id,
            'redirection_from',
            $_POST['redirection_from_field']
        );
    }

    // save redirection to
    if (array_key_exists('redirection_to_field', $_POST)) {
        update_post_meta(
            $post_id,
            'redirection_to',
            $_POST['redirection_to_field']
        );
    }

}
add_action('save_post', 'wporg_save_postdata');

// apply redirection rule

add_action('template_redirect', 'redirect_to_home_from_about_page');

function redirect_to_home_from_about_page()
{

    global $post;

    $args = array(
        'post_type' => 'rule',
        'post_status' => 'publish',
        'orderby' => 'due_date',

    );

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        // $view .= print_r($query);
        while ($query->have_posts()) {
            $query->the_post();

            $redirection_type = get_post_meta($post->ID, 'redirection_type', true);
            $redirection_from = get_post_meta($post->ID, 'redirection_from', true);
            $redirection_to = get_post_meta($post->ID, 'redirection_to', true);
            $redirection_absolute = get_home_url() . $redirection_from;

            $current_url = home_url(add_query_arg(array(), $wp->request));
            $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
            // echo $redirection_absolute, '-absolute url-' . '<br>', $actual_link, '-actual link-' . '<br>', $redirection_from, '-redirection from-' . '<br>', $redirection_to, '-redirection to-' . '<br>', $redirection_type, '-redirection type-' . '<br>', '<br> <br>';
            if (($actual_link) == $redirection_absolute) {
                wp_redirect($redirection_to, $redirection_type);
                exit;

            }

        }
        wp_reset_postdata();
    }

}