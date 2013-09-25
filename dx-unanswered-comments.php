<?php
/**
 * Plugin Name: DX Unanswered Comments
 * Description: Filter your admin comments that have not received a reply by internal user yet.
 * Author: nofearinc
 * Author URI: http://devwp.eu/
 * Version: 1.3
 * License: GPL2+
 * Text Domain: dxuc
 * 
 */

class DX_Unanswered_Comments {
	
	public function __construct() {
		$this->setup();
		add_action( 'admin_enqueue_scripts', array( $this, 'add_top_active_link_script' ) );
		add_action( 'admin_menu', array( $this, 'add_non_replied_comments_plugin_page' ) );
		add_filter( 'views_edit-comments', array( $this, 'filter_comment_top_links' ) );
		add_filter( 'comments_clauses', array( $this, 'filter_only_non_replied_comments' ) );
	}
	
	public function setup() {
		include_once plugin_dir_path( __FILE__ ) . '/inc/dxuc-helper.class.php';
	}
	
	public function filter_comment_top_links( $views ) {
		$dxuc_comment_count = get_option( 'dxuc_comment_count', false );
		
		if( ! empty( $dxuc_comment_count ) ) {
			include_once plugin_dir_path( __FILE__ ) . '/inc/dxuc-add-comment-count-top.php';
		}
		$non_replied_text = apply_filters( 'dxuc_non_replied_text', __( 'Non-replied', 'dxuc' ) );
		$non_replied_root = apply_filters( 'dxuc_non_replied_top_level', __( 'Non-replied - Top Level', 'dxuc' ) );
		
		$views['non-replied'] = "<a href='edit-comments.php?comment_status=non_replied'>$non_replied_text</a>"; 
		$views['non-replied-root'] = "<a href='edit-comments.php?comment_status=non_replied&top_level=true'>$non_replied_root</a>";
		
		return $views;
	}
	
	public function filter_only_non_replied_comments( $clauses ) {
		global $current_user;
	 
		if( is_admin() && ! empty( $_GET['comment_status'] ) && $_GET['comment_status'] === 'non_replied' ) {
			// get all needed posts (as comment__in but it doesn't exist yet)
			global $wpdb;
			
			// Get the IDs for admin users that are supposed to reply
			$internal_user_ids_list = DXUC_Helper::get_internal_user_ids_list();
			if( empty( $internal_user_ids_list ) )
				return $clauses;
			
			// Get non-replied comment IDs array
			$non_replied_comments = DXUC_Helper::get_non_replied_comments( $internal_user_ids_list );

			if ( empty( $non_replied_comments ) )
				return $clauses;
			
			$non_replied_comments_list = implode( ',' ,  $non_replied_comments );
			// add it to the where clauses
			$where = $clauses['where'];
				
			if( ! empty( $where ) ) {
				$where .= " AND ";
			}
			
			$top_level = false;
			if( ! empty( $_GET['top_level'] ) && 'true' === $_GET['top_level'] ) {
				$top_level = true;
			}
			
			// Filter where clause for getting proper comments
			$where = DXUC_Helper::filter_comments_and_top_sql( $where, $top_level, $non_replied_comments_list, $internal_user_ids_list );
			
			$clauses['where'] = apply_filters( 'dxuc_comments_filter_where', $where );
		}
		
		return $clauses;
	}
	
	public function add_non_replied_comments_plugin_page() {
		add_submenu_page( 'options-general.php', __( 'DX Unanswered Comments', 'dxuc' ), 
				__( 'DX Unanswered Comments', 'dxuc' ), 'manage_options', 'dx-unanswered-comments', 
				array( $this, 'add_plugin_menu_page_callback' ) );
	}
	
	public function add_plugin_menu_page_callback() {
		include_once "dx-unanswered-comments-admin-page.php";
	}
	
	public function add_top_active_link_script( $hook ) {
		if ( 'edit-comments.php' === $hook ) {
			wp_enqueue_script( 'dxuc-script', plugin_dir_url( __FILE__ ) . '/js/dxuc-script.js'  );
			wp_enqueue_style( 'dxuc-style', plugin_dir_url( __FILE__ ) . '/css/dxuc-style.css'  );
		}
	}
	
}

new DX_Unanswered_Comments();