<?php

if(function_exists('mpd_duplicate_over_multisite')){
	
	function init_mpd_auto_mode(){

		// Add the ids of the sites you want to auto copy posts to!!
		$main_site_ids = array(3);

		update_site_option( 'mpd_main_auto_site', $main_site_ids, true );

	}
	add_action('admin_init', 'init_mpd_auto_mode');

	function mpd_auto_copy_to_main($mpd_blogs,$source_post_id){

	    $main_blog_ids = get_site_option( 'mpd_main_auto_site', false, true );

	    $current_blog_id = get_current_blog_id();

	    $blogs = array();

	    if($main_blog_ids ){

	    	foreach ($main_blog_ids as $main_blog_id) {

	    		if($current_blog_id != $main_blog_id){

			        $args= array(

			                'source_id' => $current_blog_id,
			                'destination_id' => $main_blog_id,
			                'source_post_id' => $source_post_id

			         );

			        if(!mpd_is_there_a_persist($args)){

			            array_push($blogs, $main_blog_id);

			        }

			    }

	    	}

	    }
	    
	    return $blogs;

	}

	add_filter('mpd_selected_blogs','mpd_auto_copy_to_main', 20, 2);

	function mpd_log_persist_if_doesnt_exsist($source_post_id, $mpd_blog_id, $new_post_id){
	    
	    $options = get_option( 'mdp_settings' );
	    
	    if((isset($options['allow_persist']) || !$options)){

		    $current_blog_id = get_current_blog_id();

		    $args= array(

		        'source_id' => $current_blog_id,
		        'destination_id' => $mpd_blog_id,
		        'source_post_id' => $source_post_id

		    );

		    if(!mpd_is_there_a_persist($args)){

		        $persist_args = array(

		            'source_id'      => $current_blog_id,
		            'destination_id' => $mpd_blog_id,
		            'source_post_id' => $source_post_id,
		            'destination_post_id' => $new_post_id

		        );

		        mpd_add_persist($persist_args);

		    }
		}

	}
	add_action('mpd_single_metabox_after', 'mpd_log_persist_if_doesnt_exsist', 100, 3);

	function mpd_enter_the_loop_override($choice, $post_global, $post_id){

	    if(( isset($post_global["post_status"] ) ) 
	            && ( $post_global["post_status"] != "auto-draft" )
	            && ( $post_global["post_ID"] == $post_id )
	            ){
	        return true;
	    }

	    return false;

	}
	add_filter('mpd_enter_the_loop', 'mpd_enter_the_loop_override', 20, 3);

	function mpd_auto_on_notice(){
		?>
		<p>Auto Mode has been activated.</p>
		<?php
	}
	add_action('mpd_before_metabox_content', 'mpd_auto_on_notice');

	function mpd_show_metabox_auto_on($choice){

		$current_blog_id 	= get_current_blog_id();
		$main_blog_ids 		= get_site_option( 'mpd_main_auto_site', false, true );

		if($main_blog_ids){

			foreach ($main_blog_ids as $main_blog_id) {
				
				if(!in_array($current_blog_id, $main_blog_ids)){
					return false;
				}
			}
		}
		
		return true;
	}

	add_filter('mpd_show_metabox_post_status', 'mpd_show_metabox_auto_on');
	add_filter('mpd_show_metabox_prefix', 'mpd_show_metabox_auto_on');
	add_filter('mpd_show_site_list', 'mpd_show_metabox_auto_on');
	add_filter('mpd_show_select_all_checkboxes', 'mpd_show_metabox_auto_on');
	add_filter('mpd_show_metabox_persist', 'mpd_show_metabox_auto_on');
}