<?php


add_filter( 'dxuc_non_replied_text', 'dxuc_filter_non_replied_text_top' );
add_filter( 'dxuc_non_replied_top_level', 'dxuc_filter_non_replied_top_level_text_top' );

function dxuc_filter_non_replied_text_top( $non_replied_text ) {
	$comment_count = dxuc_get_comments_count();
	
	return $non_replied_text . " <span class='dxuc-count dxuc-non-replied-count'>($comment_count)</span>";
}

function dxuc_filter_non_replied_top_level_text_top( $non_replied_top_level_text ) {
	$comment_count = dxuc_get_comments_count( true );
	
	return $non_replied_top_level_text . " <span class='dxuc-count dxuc-non-replied-top-count'>($comment_count)</span>";
}

function dxuc_get_comments_count( $top_level = false ) {
	$internal_user_ids_list = DXUC_Helper::get_internal_user_ids_list();
	global $wpdb;
	
	$not_spam = "comment_approved != 'spam'";
	
	$non_replied_comments = DXUC_Helper::get_non_replied_comments( $internal_user_ids_list );
	if( empty( $non_replied_comments ) )
		return $wpdb->get_var( "SELECT count(*) FROM $wpdb->comments WHERE $not_spam" );
	
	$non_replied_comments_list = implode( ',' ,  $non_replied_comments );
	
	
	$where = '';
	$sql = "SELECT count(*) FROM $wpdb->comments WHERE ";

	// Filter where clause for getting proper comments
	$where = DXUC_Helper::filter_comments_and_top_sql( $where, $top_level, $non_replied_comments_list, $internal_user_ids_list );
	
	$sql .= $where;
	
	$count = $wpdb->get_var( $sql );
	
	return $count;
}

