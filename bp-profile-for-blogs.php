<?php
 /**
 * Plugin Name: BuddyPress Profile Widget for Blogs
 * Version: 1.2.4
 * Description: Let Blog Admins show all/some of their BuddyPress profile fields on their blogs as widget
 * Credits: Concept by Bowe(http://bp-tricks.com) and Mercime(http://buddypress.org/developers/mercime)
 * Requires at least: BuddyPress 1.5
 * Tested up to: BuddyPress 2.3.3
 * License: GNU General Public License 2.0 (GPL) http://www.gnu.org/licenses/gpl.html
 * Author: Brajesh Singh( BuddyDev.com )
 * Author URI: http://buddydev.com
 * Plugin URI:http://buddydev.com/buddypress/buddypress-profile-widget-for-blogs/
 * Last updated: september 15, 2015
 * Network: true
 */
 
 /***
    Copyright (C) 2010 Brajesh Singh(buddydev.com)

    This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 3 of the License, or  any later version.

    This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

    You should have received a copy of the GNU General Public License along with this program; if not, see <http://www.gnu.org/licenses>.

    */
 
 class BPDev_BPProfile_Widget extends WP_Widget {
	 
 	public function __construct() {
		parent::__construct( false, $name = __( 'BP User Profile for Blogs', 'bpdev' ) );
	}
	
	
	public function widget( $args, $instance ) {
		extract( $args );
	
		echo $before_widget; 
		echo $before_title
		  . $instance['title']
		  . $after_title; 
			
			self::show_blog_profile( $instance );/*** show the profile fields**/
			
	
		echo $after_widget;
	}

	public function update( $new_instance, $old_instance ) {
		
		$instance = $old_instance;
		
		foreach( $new_instance as $key => $val ) {
			$instance[ $key ] = $val;//update the instance
		}
		
		return $instance;
	}

	public function form( $instance ) {
		
		$instance = wp_parse_args( (array) $instance, array( 
			'title'			=> __( 'My Profile', 'bpdev' ),
			'show_avatar'	=> 'yes',
			'user_role'		=> 'administrator'
			) );
		
		$title = strip_tags( $instance['title'] );
		extract( $instance, EXTR_SKIP );
		
	?>

		<p><label for="bpdev-widget-title"><?php _e('Title:', 'bpdev'); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( stripslashes( $title ) ); ?>" /></label></p>

                <p><label for="bpdev-widget-role"><?php _e('List profiles for:', 'bpdev'); ?> <select  id="<?php echo $this->get_field_id( 'user_role' ); ?>" name="<?php echo $this->get_field_name( 'user_role' ); ?>" ><?php wp_dropdown_roles($user_role);?> </select></label></p>
		

                <p>
				<label for="bpdev-widget-show-avatar"><?php _e( 'Show Avatar' , 'bpdev'); ?>
					<input type="radio" id="<?php echo $this->get_field_id( 'show_avatar' ); ?>" name="<?php echo $this->get_field_name( 'show_avatar' ); ?>"  value="yes" <?php checked( $show_avatar, 'yes' );?> >Yes
					<input type="radio" id="<?php echo $this->get_field_id( 'show_avatar' ); ?>" name="<?php echo $this->get_field_name( 'show_avatar' ); ?>"  value="no" <?php checked( $show_avatar, 'no' );?>>No
				</label>
			</p>
			<?php
			//get all xprofile fields and ask user whether to show them or not
			
			?>
			<h3><?php _e( 'Profile Fields Visibility', 'bpdev' );?></h3>
			<table>
			
			<?php if ( function_exists( 'bp_has_profile' ) ) : if ( bp_has_profile( ) ) : while ( bp_profile_groups() ) : bp_the_profile_group(); ?>
			<?php while ( bp_profile_fields() ) : bp_the_profile_field(); ?>
			
			<?php $fld_name = bp_get_the_profile_field_input_name();
				$fld_val= isset( ${$fld_name} ) ? ${$fld_name} : 'yes' ;
			?>
			<tr><td>
			<label for="<?php echo $fld_name; ?>"><?php bp_the_profile_field_name() ?></label>
			</td><td>
			<input type="radio" id="<?php echo $this->get_field_id( $fld_name ); ?>" name="<?php echo $this->get_field_name( $fld_name); ?>"  value="yes" <?php checked( $fld_val, 'yes' );?> >Show
			<input type="radio" id="<?php echo $this->get_field_id( $fld_name); ?>" name="<?php echo $this->get_field_name( $fld_name); ?>"  value="no" <?php checked( $fld_val, 'no' );?>>Hide
			</td>
			</tr>
			
		<?php endwhile;
		endwhile;
		endif;
		endif;?>
</table>
		
	<?php
	}

	public static function get_users( $user_role = null ) {
	 
		$users = get_users( array( 'role' => $user_role, 'blog_id'=> get_current_blog_id(), 'fields' => 'ID' ));
       return $users; 
	}
 
	
	
	public static function show_blog_profile( $instance ) {
		
		//if buddypress is not active, return
		if( ! function_exists( 'buddypress' ) ) {
			return;
		}
	
		$show_avatar = $instance['show_avatar'];//we need to preserve for multi admin
		$user_role = $instance['user_role'];

		unset( $instance['show_avatar'] );
		unset( $instance['title'] );//unset the title of the widget,because we will be iterating over the instance fields
		unset( $instance['user_role'] );//unset the title of the widget,because we will be iterating over the instance fields


		$users = apply_filters( 'bp_blog_profile_for_users', self::get_users( $user_role ) );//may be we can improve it too

		if( empty( $users ) ) {
			return;
		}
	
		foreach ( $users as $user ) {

			$user_id = $user;//["user_id"];

			$op = "<table class='my-blog-profile bp-blog-user-profile bp-blog-user-profile-{$user}'>";

			if( $show_avatar == 'yes' ) {
				$op .= "<tr class='user-avatar'>";
				$op .= "<td>" . bp_core_get_userlink( $user_id ) ."</td>";
				$op .= "<td>" . bp_core_fetch_avatar( array( 'item_id' => $user_id, 'type' => 'thumb' ) ) ."</td>" ;
				$op .= "</tr>";
			}

			//bad approach, because buddypress does not allow to fetch the field name from field key
			if ( function_exists( 'bp_has_profile' ) ) : 
					if ( bp_has_profile( 'user_id=' . $user_id ) ) :

						while ( bp_profile_groups() ) : bp_the_profile_group();

							while ( bp_profile_fields() ) : bp_the_profile_field();

									$fld_name = bp_get_the_profile_field_input_name();
									if( array_key_exists( $fld_name, $instance ) && $instance[ $fld_name ] == 'yes' )
										$op .= '<tr><td>' . bp_get_the_profile_field_name() . '</td><td>' . bp_get_profile_field_data( array( 'field' => bp_get_the_profile_field_id(), 'user_id' => $user_id ) ) . '</td></tr>';
							endwhile;
						endwhile;
					endif;
				endif;
			$op.="</table>";
		 echo $op;
		 }
 }
 }
 /** Let us register the widget*/

 function bpdev_register_bpprofile_for_blogs_widgets() {
	 
	register_widget( 'BPDev_BPProfile_Widget' );
}
add_action( 'bp_widgets_init', 'bpdev_register_bpprofile_for_blogs_widgets' );
