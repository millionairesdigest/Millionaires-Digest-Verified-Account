<?php
/**
 * Plugin Name: BuddyPress User Info Widget
 * Version: 1.2.8
 * Description: Let Blog Admins show all/some of their BuddyPress profile fields on their blogs as widget
 * Credits: Concept by Bowe(http://bp-tricks.com) and Mercime(http://buddypress.org/developers/mercime)
 * License: GNU General Public License 2.0 (GPL) http://www.gnu.org/licenses/gpl.html
 * Author: Brajesh Singh( BuddyDev.com )
 * Author URI: https://buddydev.com
 * Plugin URI:https://buddydev.com/buddypress/buddypress-profile-widget-for-blogs/
 */

/***
 * Copyright (C) 2010 Brajesh Singh(buddydev.com)
 *
 * This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 3 of the License, or  any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with this program; if not, see <http://www.gnu.org/licenses>.
 */

class BPDev_BPProfile_Widget extends WP_Widget {

	public function __construct() {
		parent::__construct( false, $name = __( 'BuddyPress User Info', 'bp-profile-widget-for-blogs' ) );
	}

	public function widget( $args, $instance ) {
		extract( $args );

		//do not display widget if it should only show for the logged in user and the user is not logged in

		$role = isset( $instance['user_role'] ) ? $instance['user_role'] : '';
		if ( 'loggedin' === $role && ! is_user_logged_in() ) {
			return;
		} elseif ( 'displayed' == $role && ! bp_is_user() ) {
			return;
		}

		$user_id = 0;

		if ( 'loggedin' == $role ) {
			$user_id = get_current_user_id();
		} elseif ( 'displayed' == $role ) {
			$user_id = bp_displayed_user_id();
		}

		$title = $instance['title'];
		if ( $user_id ) {
			$user = get_user_by( 'id', $user_id );
			$title = str_replace( array( '%username%', '%display_name%', '%first_name%' ), array(
				$user->user_login,
				$user->display_name,
				$user->first_name
			), $title );
		}


		echo $before_widget;
		echo $before_title
		     . wp_kses_data( $title )
		     . $after_title;

		self::show_blog_profile( $instance );
		echo $after_widget;
	}

	public function update( $new_instance, $old_instance ) {

		$instance = $old_instance;

		foreach ( $new_instance as $key => $val ) {
			$instance[ $key ] = $val;//update the instance
		}

		return $instance;
	}

	public function form( $instance ) {

		$instance = wp_parse_args( (array) $instance, array(
			'title'       => __( 'My Profile', 'bp-profile-widget-for-blogs' ),
			'show_avatar' => 'yes',
			'user_role'   => 'administrator'
		) );

		$title = strip_tags( $instance['title'] );
		extract( $instance, EXTR_SKIP );

		?>

		<p>
			<label for="bpdev-widget-title">
				<?php _e( 'Title:', 'bp-profile-widget-for-blogs' ); ?>
				<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( stripslashes( $title ) ); ?>"/>
			</label>
		</p>

		<p><label for="bpdev-widget-role">
				<?php _e( 'List profiles for:', 'bp-profile-widget-for-blogs' ); ?>
				<select id="<?php echo $this->get_field_id( 'user_role' ); ?>"
				        name="<?php echo $this->get_field_name( 'user_role' ); ?>">
					<?php wp_dropdown_roles( $user_role ); ?>
					<option value="loggedin" <?php selected( $user_role, 'loggedin' ); ?>><?php _e( 'Logged In User', 'bp-profile-widget-for-blogs' ); ?></option>
					<option value="displayed" <?php selected( $user_role, 'displayed' ); ?>><?php _e( 'Displayed User', 'bp-profile-widget-for-blogs' ); ?></option>
				</select>
			</label>
		</p>


		<p>
			<label for="bpdev-widget-show-avatar"><?php _e( 'Show Avatar', 'bp-profile-widget-for-blogs' ); ?>
				<input type="radio" id="<?php echo $this->get_field_id( 'show_avatar' ); ?>" name="<?php echo $this->get_field_name( 'show_avatar' ); ?>" value="yes" <?php checked( $show_avatar, 'yes' ); ?> >Yes
				<input type="radio" id="<?php echo $this->get_field_id( 'show_avatar' ); ?>" name="<?php echo $this->get_field_name( 'show_avatar' ); ?>" value="no" <?php checked( $show_avatar, 'no' ); ?>>No
			</label>
		</p>
		<?php
		//get all xprofile fields and ask user whether to show them or not

		?>
		<h3><?php _e( 'Profile Fields Visibility', 'bp-profile-widget-for-blogs' ); ?></h3>
		<table>

			<?php if ( function_exists( 'bp_has_profile' ) ) : if ( bp_has_profile() ) : while ( bp_profile_groups() ) : bp_the_profile_group(); ?>
				<?php while ( bp_profile_fields() ) : bp_the_profile_field(); ?>

					<?php $fld_name = bp_get_the_profile_field_input_name();
						$fld_val        = isset( ${$fld_name} ) ? ${$fld_name} : 'yes';
					?>
					<tr>
						<td>
							<label for="<?php echo $fld_name; ?>"><?php bp_the_profile_field_name() ?></label>
						</td>
						<td>
							<input type="radio" id="<?php echo $this->get_field_id( $fld_name ); ?>" name="<?php echo $this->get_field_name( $fld_name ); ?>" value="yes" <?php checked( $fld_val, 'yes' ); ?> >Show
							<input type="radio" id="<?php echo $this->get_field_id( $fld_name ); ?>" name="<?php echo $this->get_field_name( $fld_name ); ?>" value="no" <?php checked( $fld_val, 'no' ); ?>>Hide
						</td>
					</tr>

				<?php endwhile;
			endwhile;
			endif;
			endif; ?>
		</table>

		<?php
	}

	public static function get_users( $user_role = null ) {

		$users = get_users( array( 'role' => $user_role, 'blog_id' => get_current_blog_id(), 'fields' => 'ID' ) );

		return $users;
	}


	public static function show_blog_profile( $instance ) {

		//if buddypress is not active, return
		if ( ! function_exists( 'buddypress' ) ) {
			return;
		}

		$show_avatar = $instance['show_avatar'];//we need to preserve for multi admin
		$user_role   = $instance['user_role'];

		unset( $instance['show_avatar'] );
		unset( $instance['title'] );//unset the title of the widget,because we will be iterating over the instance fields
		unset( $instance['user_role'] );//unset the title of the widget,because we will be iterating over the instance fields

        $users = array();

		if ( $user_role == 'loggedin' ) {
			$users[] = get_current_user_id();
		} elseif ( $user_role == 'displayed' ) {
			if ( bp_is_user() ) {
				$users[] = bp_displayed_user_id();
			}
		} else {
			$users = apply_filters( 'bp_blog_profile_for_users', self::get_users( $user_role ) );//may be we can improve it too
		}


		if ( empty( $users ) ) {
			return;
		}

		foreach ( $users as $user ) {

			$user_id = $user;//["user_id"];

			$op = "<table class='my-blog-profile bp-blog-user-profile bp-blog-user-profile-{$user}'>";

			if ( $show_avatar == 'yes' ) {
				$op .= "<tr class='user-avatar'>";
				$op .= "<td>" . bp_core_get_userlink( $user_id ) . "</td>";
				$op .= "<td>" . bp_core_fetch_avatar( array( 'item_id' => $user_id, 'type' => 'thumb' ) ) . "</td>";
				$op .= "</tr>";
			}

			//bad approach, because buddypress does not allow to fetch the field name from field key
			if ( function_exists( 'bp_has_profile' ) ) :
				if ( bp_has_profile( 'user_id=' . $user_id ) ) :

					while ( bp_profile_groups() ) : bp_the_profile_group();

						while ( bp_profile_fields() ) : bp_the_profile_field();

							$fld_name = bp_get_the_profile_field_input_name();
							if ( array_key_exists( $fld_name, $instance ) && $instance[ $fld_name ] == 'yes' ) {
								$op .= '<tr><td>' . bp_get_the_profile_field_name() . '</td><td>' .xprofile_get_field_data( bp_get_the_profile_field_id(),$user_id, 'comma' ) . '</td></tr>';
							}
						endwhile;
					endwhile;
				endif;
			endif;
			$op .= "</table>";
			echo $op;
		}
	}
}

/** Let us register the widget*/

function bpdev_register_bpprofile_for_blogs_widgets() {

	register_widget( 'BPDev_BPProfile_Widget' );
}

add_action( 'bp_widgets_init', 'bpdev_register_bpprofile_for_blogs_widgets' );


1
2
3
4
5
6
7
8
9
10
11
12
13
14
15
16
17
18
19
20
21
22
23
24
25
26
27
28
29
30
31
32
33
34
35
36
37
38
39
40
41
42
43
44
45
46
47
48
49
50
51
52
53
54
55
56
57
58
59
60
61
62
// Register and load the widget
function wpb_load_widget() {
    register_widget( 'wpb_widget' );
}
add_action( 'widgets_init', 'wpb_load_widget' );
 
// Creating the widget 
class wpb_widget extends WP_Widget {
 
function __construct() {
parent::__construct(
 
// Base ID of your widget
'wpb_widget', 
 
// Widget name will appear in UI
__('WPBeginner Widget', 'wpb_widget_domain'), 
 
// Widget description
array( 'description' => __( 'Sample widget based on WPBeginner Tutorial', 'wpb_widget_domain' ), ) 
);
}
 
// Creating widget front-end
 
public function widget( $args, $instance ) {
$title = apply_filters( 'widget_title', $instance['title'] );
 
// before and after widget arguments are defined by themes
echo $args['before_widget'];
if ( ! empty( $title ) )
echo $args['before_title'] . $title . $args['after_title'];
 
// This is where you run the code and display the output
echo __( 'Hello, World!', 'wpb_widget_domain' );
echo $args['after_widget'];
}
         
// Widget Backend 
public function form( $instance ) {
if ( isset( $instance[ 'title' ] ) ) {
$title = $instance[ 'title' ];
}
else {
$title = __( 'New title', 'wpb_widget_domain' );
}
// Widget admin form
?>
<p>
<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
</p>
<?php 
}
     
// Updating widget replacing old instances with new
public function update( $new_instance, $old_instance ) {
$instance = array();
$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
return $instance;
}
} // Class wpb_widget ends here
