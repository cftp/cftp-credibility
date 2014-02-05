<?php
/*
Plugin Name: Credibility
Plugin URI:  https://github.com/cftp/credit-me
Description: Add a credit to WordPress media
Author:      Code For The People
Version:     1.0
Author URI:  http://codeforthepeople.com/
Text Domain: credibility
Domain Path: /languages/
License:     GPL v2 or later

Copyright © 2014 Code for the People Ltd

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

*/

function credibility_get_attachment( $attachment_id ) {

    $attachment = get_post( $attachment_id );
    return array(
        'alt' => get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true ),
        'caption' => $attachment->post_excerpt,
        'description' => $attachment->post_content,
        'href' => get_permalink( $attachment->ID ),
        'src' => $attachment->guid,
        'meta' => wp_get_attachment_metadata( $attachment_id ),
        'title' => $attachment->post_title
    );
}

function credibility_attachment_fields( $fields, $post ) {

	$credits = get_post_meta( $post->ID, 'credits', true );

	if ( empty( $credits ) ) {
		$image_meta = credibility_get_attachment( $post->ID );
    	if ( isset( $image_meta['meta']['image_meta'] ) and isset( $image_meta['meta']['image_meta']['credit'] ) ) {
        	$credits = $image_meta['meta']['image_meta']['credit'];
        }

	}

    $fields['credits'] = array(
        'label'        => __('Credits', 'credibility'),
        'input'        => 'text',
        'value'        => $credits,
        'show_in_edit' => true,
    );

    return $fields;

}
add_filter( 'attachment_fields_to_edit', 'credibility_attachment_fields', 10, 2 );

function credibility_update_attachment_meta( $attachment ) {

    global $post;

    update_post_meta( $post->ID, 'credits', $attachment['attachments'][$post->ID]['credits'] );

    return $attachment;

}
add_filter( 'attachment_fields_to_save', 'credibility_update_attachment_meta', 4);

function credibility_media_xtra_fields() {

    $post_id = $_POST['id'];
    $meta = stripslashes( $_POST['attachments'][$post_id ]['credits'] );
    update_post_meta( $post_id, 'credits', $meta );
    clean_post_cache( $post_id );

}
add_action('wp_ajax_save-attachment-compat', 'credibility_media_xtra_fields', 0, 1);
