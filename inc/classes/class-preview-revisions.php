<?php
/**
 * Class to add revisions preview functionality.
 *
 * @package preview-revisions
 */

/**
 * Preview_Revisions class.
 */
class Preview_Revisions {

	/**
	 * Construct method.
	 */
	public function __construct() {

		$this->setup_hooks();

	}

	/**
	 * Function to setup hooks.
	 *
	 * @return void
	 */
	public function setup_hooks() {

		// Actions.
		add_action( 'admin_head', array( $this, 'preview_revisions' ) );

		// Filters.
		add_filter( 'posts_request', array( $this, 'modify_posts_request' ) );
		add_filter( 'posts_results', array( $this, 'inherit_parent_status' ) );
		add_filter( 'the_posts', array( $this, 'undo_inherit_parent_status' ) );

	}

	/**
	 * Function to add preview revisions button.
	 *
	 * @return void
	 */
	public function preview_revisions() {

		$current_screen = get_current_screen();

		if ( ! $current_screen || 'revision' !== $current_screen->id ) {
			return;
		}

		// Create nonce.
		$nonce = wp_create_nonce( 'preview-revision-nonce' );

		$site_link    = get_site_url();
		$preview_link = add_query_arg(
			array(
				'_wpnonce'     => $nonce,
				'post_preview' => 'revision_preview',
				'post_type'    => 'revision',
				'preview'      => 'true',
			),
			$site_link
		);

		?>
		<script type="text/javascript">

			setTimeout(() => {

				let button = document.createElement( 'a' );
				button.setAttribute( 'class', 'button restore-revision' );
				button.innerText = <?php echo wp_json_encode( __( 'Preview', 'preview-revisions' ) ); ?>;
				let appendDoc = document.querySelectorAll( '.revisions-meta .diff-meta-to' );


				let AddPreviewButtonLink = function() {
					let searchParams = new URLSearchParams( window.location.search );
					let revId        = searchParams.get( 'to' );

					if ( ! revId ) {
						revId = searchParams.get( 'revision' );
					}

					let preview_link = <?php echo wp_json_encode( esc_url_raw( $preview_link ) ); ?> + '&p=' + revId;
					button.setAttribute( 'href', preview_link );
					appendDoc[0].appendChild( button );
				};

				setInterval( AddPreviewButtonLink, 500 );
			}, 300 );
		</script>
		<?php

	}

	/**
	 * Function to modify the post request.
	 *
	 * @param string $posts_request Posts Request.
	 *
	 * @return string $posts_request Modified Posts Request.
	 */
	public function modify_posts_request( $posts_request ) {

		if ( is_admin() ) {
			return $posts_request;
		}

		if ( ! isset( $_REQUEST['_wpnonce'] ) || empty( $_REQUEST['_wpnonce'] ) ) {
			return $posts_request;
		}

		$nonce_verification = sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) );

		if ( ! wp_verify_nonce( $nonce_verification, 'preview-revision-nonce' ) ) {
			return $posts_request;
		}

		if ( ! isset( $_GET['post_preview'] ) || empty( $_GET['post_preview'] || 'revision_preview' !== sanitize_text_field( wp_unslash( $_GET['post_preview'] ) ) ) ) {
			return $posts_request;
		}

		if ( ! isset( $_GET['post_type'] ) || empty( $_GET['post_type'] ) || 'revision' !== sanitize_text_field( wp_unslash( $_GET['post_type'] ) ) ) {
			return $posts_request;
		}

		if ( ! isset( $_GET['p'] ) || empty( $_GET['p'] ) ) {
			return $posts_request;
		}

		$revision_id = (int) filter_input( INPUT_GET, 'p', FILTER_VALIDATE_INT );

		$revision = wp_get_post_revision( $revision_id );

		if ( ! $revision || 'revision' !== $revision->post_type ) {
			return $posts_request;
		}

		$pub_post = get_post( $revision->post_parent );

		if ( ! $pub_post ) {
			return $posts_request;
		}

		$type_obj = get_post_type_object( $pub_post->post_type );

		if ( ! $type_obj ) {
			return $posts_request;
		}

		if ( ! current_user_can( 'read_post', $revision_id ) || ! current_user_can( 'edit_post', $revision_id ) ) {
			return $posts_request;
		}

		$posts_request = str_replace( "post_type = 'post'", "post_type = 'revision'", $posts_request );

		return str_replace( "post_type = '{$pub_post->post_type}'", "post_type = 'revision'", $posts_request );

	}

	/**
	 * Add posts_results post status to work the functionality.
	 *
	 * @param array $posts_results Posts Results.
	 *
	 * @return array $posts_results Modified Posts Results.
	 */
	public function inherit_parent_status( $posts_results ) {

		global $wp_post_statuses;

		$wp_post_statuses['inherit']->protected = true;
		return $posts_results;

	}

	/**
	 * Undo the post status.
	 *
	 * @param array $posts Posts Results.
	 *
	 * @return array
	 */
	public function undo_inherit_parent_status( $posts ) {

		global $wp_post_statuses;

		$wp_post_statuses['inherit']->protected = false;
		return $posts;

	}
}
