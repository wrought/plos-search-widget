<?php
/*
Plugin Name: PLOS Search Widget
Plugin URI: http://plos.org/
Description: Adds a PLOS search widget to your site.
Version: 0.1
Author: Matt Senate, based off Samir Shah
Author URI: http://plos.org/
License: GPLv2 or later
*/

if( !defined( 'ABSPATH' ) )
	exit;

class PLOSSearch {
	function __construct() {
		add_action( 'widgets_init', array( $this, 'register_widget' ) );
		if( !is_admin() ) {
			add_filter( 'query_vars', array( $this, 'filter_query_vars' ) );
			add_action( 'template_redirect', array( $this, 'search_redirect' ) );
		}
	}
	
	function filter_query_vars( $qvs ) {
		$qvs[] = 'plos-s';
		return $qvs;
	}
	
	function register_widget() {
		register_widget( 'PLOSSearchWidget' );
	}
	
	function search_redirect() {
		if( ! $plos = get_query_var( 'plos-s' ) )
			return;

		$vars = array( 
			'unformattedQuery' => "everything:" . get_search_query( false ),
		);
		
		$scheme = ( $plos == 'ssl' ) ? 'https' : 'http';
		wp_redirect( $scheme . '://plosone.org/search/advanced?' . http_build_query( $vars ) );
		exit;
	}
}

class PLOSSearchWidget extends WP_Widget {
	private $counter = 1;
	
	function __construct() {
		parent::__construct( 'plos', 'PLOS Search', array( 'classname' => 'widget_plos', 'description' => 'PLOS article search form for your site.' ) );
	}

	function widget( $args, $instance ) {
		extract( $args );
		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );

		echo $before_widget;
		if ( $title )
			echo $before_title . $title . $after_title;
		
		$id = $this->counter;
		$ssl = $instance['ssl'] ? 'ssl' : 'no-ssl';
		
		echo "<form role='search' method='get' class='plos-search' id='plos-search-$id' action='" . esc_url( home_url( '/' ) ) . "' ><div>
	<label class='screen-reader-text' for='plos-s-$id'>" . __('Search PLOS articles for:') . "</label>
	<input type='text' name='s' id='plos-s-$id' /><input type='hidden' name='plos-s' value='$ssl' />
	<input type='submit' id='plos-submit-$id' value='" . __( 'Search' ) . "' />
	</div></form>";
		
		echo $after_widget;
		$this->counter += 1;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $new_instance;
		$instance['title'] = strip_tags( $instance['title'] );
		$instance['ssl'] = isset( $instance['ssl'] );
		return $instance;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'ssl' => false, 'logo' => false ) );
		$title = array( 'val' => esc_attr( $instance['title'] ), 'id' => $this->get_field_id('title'), 'name' => $this->get_field_name('title') );
		$ssl = array( 'checked' => checked( $instance['ssl'], true, false ), 'id' => $this->get_field_id('ssl'), 'name' => $this->get_field_name('ssl') );
		
		echo "<p><label for='{$title['id']}'>" . __('Title:') . "</label> <input class='widefat' id='{$title['id']}' name='{$title['name']}' type='text' value='{$title['val']}' /></p>";
		/* echo "<p><label for='{$ssl['id']}'><input id='{$ssl['id']}' name='{$ssl['name']}' type='checkbox' {$ssl['checked']} /> " . __('Use SSL') . '</label></p>'; */
	}
}

new PLOSSearch();
