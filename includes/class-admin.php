<?php
/**
 * BuddyVerified Admin
 *
 * @since 2.4.0
 * @package BuddyVerified
 */

/**
 * BuddyVerified Admin.
 *
 * @since 2.4.0
 */
class BV_Admin {
	/**
	 * Parent plugin class
	 *
	 * @var   class
	 * @since 2.4.0
	 */
	protected $plugin = null;

	/**
	 * Constructor
	 *
	 * @since  2.4.0
	 * @param  object $plugin Main plugin object.
	 * @return void
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
		$this->hooks();
	}

	/**
	 * Initiate our hooks
	 *
	 * @since  2.4.0
	 * @return void
	 */
	public function hooks() {
	}
}


if ( ! function_exists( 'load_sortable_user_meta_columns' ) ) {

	/**
	 * Load_sortable_user_meta_columns function.
	 *
	 * @access public
	 * @return void
	 */
	function load_buddyverified_user_columns() {
		$args = array( 'bp-profile-verified' => 'Verified' );
		new buddyverified_user_columns( $args );
	}
	add_action( 'admin_init', 'load_buddyverified_user_columns' );
}


if ( ! class_exists( 'buddyverified_user_columns' ) ) {

	/**
	 * Buddyverified_user_columns class.
	 */
	class buddyverified_user_columns {

		/**
		 * [$defaults description]
		 *
		 * @var array
		 */
		var $defaults = array(
			'nicename',
			'email',
			'url',
			'registered',
			'user_nicename',
			'user_email',
			'user_url',
			'user_registered',
			'display_name',
			'name',
			'post_count',
			'ID',
			'id',
			'user_login',
		);

		/**
		 * __construct function.
		 *
		 * @access public
		 * @param mixed $args
		 * @return void
		 */
		function __construct( $args ) {
			$this->args = $args;
			add_action( 'pre_user_query', array( &$this, 'query' ) );
			add_action( 'manage_users_custom_column',  array( &$this, 'content' ), 10, 3 );
			add_filter( 'manage_users_columns', array( &$this, 'columns' ) );
			add_filter( 'manage_users_sortable_columns', array( &$this, 'sortable' ) );
		}

		/**
		 * Query function.
		 *
		 * @access public
		 * @param mixed $query
		 * @return void
		 */
		function query( $query ) {
			$vars = $query->query_vars;

			if ( in_array( $vars['orderby'], $this->defaults, true ) ) {
				return;
			}

			if ( ! empty( $this->args[ $vars['orderby'] ] ) ) {
				$title = $this->args[ $vars['orderby'] ];
			}

			if ( ! empty( $title ) ) {
				   $query->query_from .= " LEFT JOIN wp_usermeta m ON ( wp_users.ID = m.user_id AND m.meta_key = '$vars[orderby]' )";

				   $query->query_orderby = 'ORDER BY m.meta_value ' . $vars['order'];

			}
		}

		/**
		 * Columns function.
		 *
		 * @access public
		 * @param mixed $columns
		 * @return array
		 */
		function columns( $columns ) {
			foreach ( $this->args as $key => $value ) {
				$columns[ $key ] = $value;
			}
			return $columns;
		}

		/**
		 * Sortable function.
		 *
		 * @access public
		 * @param mixed $columns
		 * @return array
		 */
		function sortable( $columns ) {
			foreach ( $this->args as $key => $value ) {
				$columns[ $key ] = $key;
			}
			return $columns;
		}

		/**
		 * Content function.
		 *
		 * @access public
		 * @param mixed $value
		 * @param mixed $column_name
		 * @param mixed $user_id
		 * @return string
		 */
		function content( $value, $column_name, $user_id ) {
			$user = get_userdata( $user_id );
			$meta = get_user_meta( $user_id, 'bp-verified', true ) ? get_user_meta( $user_id, 'bp-verified', true ) : null ;

			$value = $user->$column_name;

			$values = '';

			if ( $value ) {
				if ( $value ) {
					$values = '<div style="width: 20px; height: 20px; margin: 0 auto;"><img src="' . VERIFIED_URL . '/images/' . $meta['image'] . '.png"></div>' ;
				}
				return $values;
			}
		}
	}
}



/**
 * Buddyverified_meta_box function.
 *
 * @access public
 * @return void
 */
function buddyverified_meta_box() {

	add_meta_box(
	    'buddyverified_id',
		__( 'Verifiy User', 'buddyverified' ),
	    'buddyverified_inner_meta_box',
	    get_current_screen()->id
	);
}
add_action( 'bp_members_admin_user_metaboxes', 'buddyverified_meta_box' );


/**
 * Buddyverified_inner_meta_box function.
 *
 * @access public
 * @return void
 */
function buddyverified_inner_meta_box() {

	if ( ! empty( $_GET['user_id'] ) && is_numeric( $_GET['user_id'] ) ) {
		$user_id = $_GET['user_id'];
	} else {
		$user_id = get_current_user_id();
	}

	$meta = get_user_meta( $user_id, 'bp-verified', true ) ? get_user_meta( $user_id, 'bp-verified', true ) : null;
	$verified = get_user_meta( $user_id, 'bp-profile-verified', true ) ? get_user_meta( $user_id, 'bp-profile-verified', true ) : null;

	?>
	<table cellspacing="3px" style="border-collapse: collapse;">
		<thead>
			<tr>
				<th></th>
			</tr>
		</thead>

		<tbody>
			<tr>
				<td style="vertical-align:middle">Verify User:</td>
				<td><input type="checkbox" name="verified" value="1" <?php if ( $verified ) { echo 'checked="checked"'; } ?> /></td>
			</tr>
			<tr class="alt">
				<td style="vertical-align:middle">Badge:</td>
				<td style="vertical-align:middle">Choose image to display</td>
				<td><img src="<?php echo esc_url( VERIFIED_URL ); ?>/images/1.png"></td>
				<td><img src="<?php echo esc_url( VERIFIED_URL ); ?>/images/2.png"></td>
				<td><img src="<?php echo esc_url( VERIFIED_URL ); ?>/images/3.png"></td>
				<td><img src="<?php echo esc_url( VERIFIED_URL ); ?>/images/4.png"></td>
				<td><img src="<?php echo esc_url( VERIFIED_URL ); ?>/images/5.png"></td>
				<td><img src="<?php echo esc_url( VERIFIED_URL ); ?>/images/6.png"></td>
			</tr>
			<tr class="alt">
				<td></td>
				<td></td>
				<td><input type="radio" name="verified_image" value="1" <?php if ( '1' === $meta['image'] ) { echo 'checked="checked"'; } ?> /></td>
				<td><input type="radio" name="verified_image" value="2" <?php if ( '2' === $meta['image'] ) { echo 'checked="checked"'; } ?>/></td>
				<td><input type="radio" name="verified_image" value="3" <?php if ( '3' === $meta['image'] ) { echo 'checked="checked"'; } ?> /></td>
				<td><input type="radio" name="verified_image" value="4" <?php if ( '4' === $meta['image'] ) { echo 'checked="checked"'; } ?> /></td>
				<td><input type="radio" name="verified_image" value="5" <?php if ( '5' === $meta['image'] ) { echo 'checked="checked"'; } ?> /></td>
				<td><input type="radio" name="verified_image" value="6" <?php if ( '6' === $meta['image'] ) { echo 'checked="checked"'; } ?> /></td>
			</tr>
			<tr class="alt">
				<td style="vertical-align:top">Activity Badge:</td>
				<td>
					<input type="radio" name="verified_activity" value="yes" <?php if ( 'yes' === $meta['activity'] ) { echo 'checked="checked"'; } ?> />Yes
					<input type="radio" name="verified_activity" value="no"  <?php if ( 'no' === $meta['activity'] ) { echo 'checked="checked"'; } ?>/>No
					<p>Adds badge to activity stream avatar</p>
				</td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
			</tr>
			<tr>
				<td style="vertical-align:top">Profile Badge:</td>
				<td>
					<input type="radio" name="verified_profile" value="yes" <?php if ( 'yes' === $meta['profile'] ) { echo 'checked="checked"'; } ?> />Yes
					<input type="radio" name="verified_profile" value="no"  <?php if ( 'no' === $meta['profile'] ) { echo 'checked="checked"'; } ?>/>No
					<p>Adds badge to profile</p>
				</td>
			</tr>
			<tr>
				<td style="vertical-align:top">Members List Badge:</td>
				<td>
					<input type="radio" name="verified_member" value="yes" <?php if ( 'yes' === $meta['member'] ) { echo 'checked="checked"'; } ?> />Yes
					<input type="radio" name="verified_member" value="no"  <?php if ( 'no' === $meta['member'] ) { echo 'checked="checked"'; } ?>/>No
					<p>Adds badge to username in members lists</p>
				</td>
			</tr>
		</tbody>
	</table>

	<?php

}


/**
 * Buddyverified_save_metabox function.
 *
 * @access public
 * @return void
 */
function buddyverified_save_metabox( $array ) {

	if ( isset( $_POST['save'] ) && 'Update Profile' === $_POST['save'] ) {

		if ( ! empty( $_GET['user_id'] ) && is_numeric( $_GET['user_id'] ) ) {
			$user_id = $_GET['user_id'];
		} else {
			$user_id = get_current_user_id();
		}

		$text = isset( $_POST['verified_text'] ) ? $_POST['verified_text'] : '';
		$profile = isset( $_POST['verified_profile'] ) ? $_POST['verified_profile'] : '';
		$member = isset( $_POST['verified_member'] ) ? $_POST['verified_member'] : '';
		$activity = isset( $_POST['verified_activity'] ) ? $_POST['verified_activity'] : '';
		$image = isset( $_POST['verified_image'] ) ? $_POST['verified_image'] : '1';
		$verify = isset( $_POST['verified'] ) ? $_POST['verified'] : '';

		$bp_verified_arr = array(
			'profile' => sanitize_text_field( $profile ),
			'member' => sanitize_text_field( $member ),
			'activity' => sanitize_text_field( $activity ),
			'text' => sanitize_text_field( $text ),
			'image' => sanitize_text_field( $image ),
		);

		update_user_meta( $user_id, 'bp-verified', $bp_verified_arr );
		update_user_meta( $user_id, 'bp-profile-verified', sanitize_text_field( $verify ) );
	}
}
add_action( 'bp_members_admin_update_user', 'buddyverified_save_metabox', 5, 1 );

/**
 * [buddyverified_admin_css description]
 *
 * @return void
 */
function buddyverified_admin_css() {
	?>
	<style>
		th#bp-profile-verified.manage-column.column-bp-profile-verified {
			text-align:center;
			width: 10%;
		}
		#buddyverified_id table td {
			padding: 5px;
		}
	</style>
	<?php
}
add_action( 'admin_head', 'buddyverified_admin_css' );

/**
 * Your setting main function
 */
function buddyverified_admin_settings() {

	/* This is how you add a new section to BuddyPress settings */
	add_settings_section(
		/* the id of your new section */
		'buddyverified_section',
		/* the title of your section */
		__( 'BuddyVerified',  'buddyverified' ),
		/* the display function for your section's description */
		'buddyverified_setting_callback_section',
		/* BuddyPress settings */
		'buddypress'
	);

	/* This is how you add a new field to your plugin's section */
	add_settings_field(
	    /* the option name you want to use for your plugin */
	    'buddyverified',
	    /* The title for your setting */
	    __( 'BuddyVerified CSS', 'buddyverified' ),
	    /* Display function */
	    'buddyverified_setting_field_callback',
	    /* BuddyPress settings */
	    'buddypress',
	    /* Your plugin's section id */
	    'buddyverified_section'
	);

	/*
	   This is where you add your setting to BuddyPress ones
	   Here you are directly using intval as your validation function.
	*/
	register_setting(
	    /* BuddyPress settings */
	    'buddypress',
	    /* the option name you want to use for your plugin */
	    'buddyverified',
	    /* the validatation function you use before saving your option to the database */
	    ''
	);

}
add_action( 'bp_register_admin_settings', 'buddyverified_admin_settings', 999 );

/**
 * [buddyverified_setting_callback_section description]
 *
 * @return void
 */
function buddyverified_setting_callback_section() {

}

/**
 * [buddyverified_setting_field_callback description]
 *
 * @return void
 */
function buddyverified_setting_field_callback() {
	$buddyverified_value = bp_get_option( 'buddyverified' );
	?>
	<textarea id="buddyverified" name="buddyverified" cols="60" rows="8"><?php echo esc_textarea( $buddyverified_value ); ?></textarea>
	<p class="description"><?php esc_html_e( 'CSS added here will be added to <head>. Use this to customize badge placement.', 'buddyverified' ); ?></p>
	<?php
}
