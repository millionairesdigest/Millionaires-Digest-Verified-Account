<?php
/**
 * BuddyVerified Functions
 *
 * @since 2.4.0
 * @package BuddyVerified
 */

/**
 * BuddyVerified Functions.
 *
 * @since 2.4.0
 */
class BV_Functions {
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
		add_action( 'wp_head', array( $this, 'buddyverified_css' ) );
		add_filter( 'body_class', array( $this, 'buddyverified_add_theme_body_class' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'buddyverified_scripts_enqueue' ) );
	}

	/**
	 * Buddyverified_css function.
	 *
	 * @return void
	 */
	public function buddyverified_css() {

		$buddyverified_value = bp_get_option( 'buddyverified' );

		if ( ! empty( $buddyverified_value ) ) {
			?>
			<style>
				<?php echo esc_html( $buddyverified_value ); ?>
			</style>
			<?php

		}
	}

	/**
	 * Buddyverified_add_theme_body_class function.
	 *
	 * @param array $classes
	 * @return array
	 */
	public function buddyverified_add_theme_body_class( $classes ) {
		return array_merge( $classes, array( get_template() ) );
	}


	/**
	 * Buddyverified_scripts_enqueue function.
	 *
	 * @access public
	 * @return void
	 */
	public function buddyverified_scripts_enqueue() {
		wp_enqueue_style( 'verified-style', buddyverified()->url() . '/css/verified.css' );
		// wp_enqueue_script( 'verified-script', buddyverified()->url() . '/js/verified.js');
	}

}

/**
 * Bp_is_verified function.
 *
 * @param 	integer $user_id
 * @return 	boolean
 */
function bp_is_verified( $user_id ) {
	return apply_filters( 'bp_is_verified', get_user_meta( $user_id, 'bp-profile-verified', true ), $user_id );
}

/**
 * Bp_get_verified_image function.
 *
 * @param 	string $image
 * @return 	string
 */
function bp_get_verified_image( $image ) {
	$src = apply_filters( 'bp_get_verified_image', esc_url( VERIFIED_URL ) . '/images/' . $image . '.png' );
	return '<img class="verified-badge" src="' . $src . '" alt="verified badge">';
}

/**
 * Bp_get_verified_meta function.
 *
 * @param 	integer $user_id
 * @return 	array
 */
function bp_get_verified_meta( $user_id ) {
	return get_user_meta( $user_id, 'bp-verified', true );
}

/**
 * Bp_verified_image function.
 *
 * @param 	integer $user_id
 * @return 	string|boolean
 */
function bp_verified_image( $user_id ) {
	if ( bp_is_verified( $user_id ) ) {
		$meta = bp_get_verified_meta( $user_id );
		echo bp_get_verified_image( $meta['image'] );
	}
	return;
}

/**
 * Bp_show_verified_badge_username function.
 *
 * @param mixed $object
 * @return string
 */
function bp_show_verified_badge_username( $object ) {
	global $bp;

	$verified_meta = bp_get_verified_meta( $bp->displayed_user->id );

	if ( ! empty( $verified_meta ) ) {
		if ( isset( $verified_meta['profile'] ) && 'yes' === $verified_meta['profile'] && bp_is_verified( $bp->displayed_user->id ) ) {
			$object = $object . ' <span id="bp-verified-header">' . bp_get_verified_image( $verified_meta['image'] ) . '</span>';
		}
	}

	return $object;
}
add_filter( 'bp_get_displayed_user_mentionname', 'bp_show_verified_badge_username' );


/**
 * Bp_show_verified_badge_activity function.
 *
 * @access public
 * @param mixed $object
 * @return string
 */
function bp_show_verified_badge_activity( $object ) {
	global $bp, $activities_template;

	if ( ! bp_is_active( 'activity' ) ) {
		return;
	}

	if ( isset( $activities_template->activity->current_comment ) && 'activity_comment' === $activities_template->activity->current_comment->type ) { return $object; }

	$comments = isset( $activities_template->activity->current_comment ) ? $activities_template->activity->current_comment->user_id : (int) $activities_template->activity->user_id;
	$verified_meta = bp_get_verified_meta( $comments );

	if ( ! empty( $verified_meta ) ) {
		if ( isset( $verified_meta['activity'] ) && 'yes' === $verified_meta['activity'] && bp_is_verified( $comments ) ) {
			$object = '<span class="bp-verified">' . bp_get_verified_image( $verified_meta['image'] ) . '</span>' . $object;

	  	}
	}

	return $object;
}
add_filter( 'bp_get_activity_action_pre_meta', 'bp_show_verified_badge_activity' );


/**
 * Bp_show_verified_badge_activity_comment function.
 *
 * @param mixed $object
 * @return string
 */
function bp_show_verified_badge_activity_comment( $object ) {
	global $bp, $activities_template;

	if ( ! bp_is_active( 'activity' ) ) {
		return;
	}

	$comments = isset( $activities_template->activity->current_comment ) ? $activities_template->activity->current_comment->user_id : (int) $activities_template->activity->user_id;
	$verified_meta = bp_get_verified_meta( $comments );

	if ( ! empty( $verified_meta ) ) {
		if ( isset( $verified_meta['activity'] ) && 'yes' === $verified_meta['activity'] && bp_is_verified( $comments ) ) {
			$object = '<span class="buddyverified-comment">' . bp_get_verified_image( $verified_meta['image'] ) . '</span>' . $object;

	  	}
	}

	return $object;
}
add_filter( 'bp_activity_comment_name', 'bp_show_verified_badge_activity_comment' );


/**
 * Bp_show_verified_badge_members function.
 *
 * @param mixed $object
 * @return string
 */
function bp_show_verified_badge_members( $object ) {
	global $bp, $members_template;

	$member = isset( $members_template->members ) ? (int) $members_template->member->id : '';
	$verified_meta = bp_get_verified_meta( $member );

	if ( ! empty( $verified_meta ) ) {
		if ( isset( $verified_meta['member'] ) && 'yes' === $verified_meta['member'] && bp_is_verified( $member ) ) {
			$object = '<span class="bp-verified-members">' . bp_get_verified_image( $verified_meta['image'] ) . '</span>    ' . $object;
	  	}
	}

	return $object;
}
add_filter( 'bp_member_name', 'bp_show_verified_badge_members' );
add_filter( 'bp_get_group_member_link', 'bp_show_verified_badge_members' );
