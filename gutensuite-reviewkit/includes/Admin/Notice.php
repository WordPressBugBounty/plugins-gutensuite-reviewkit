<?php
namespace Gutensuite\Reviewkit\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Promotional admin notice for Reviewkit Pro.
 *
 * Shows on the Dashboard and Plugins screens only. Dismissible per user.
 */
class Notice {

	const DISMISS_META = 'gutensuite_reviewkit_pro_notice_dismissed';

	/**
	 * Register hooks.
	 */
	public function __construct() {
		add_action( 'admin_notices', array( $this, 'render_notice' ) );
		add_action( 'wp_ajax_gutensuite_reviewkit_dismiss_pro_notice', array( $this, 'ajax_dismiss' ) );
	}

	/**
	 * Whether the notice should render on the current screen.
	 *
	 * @return bool
	 */
	private function should_render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		if ( defined( 'GUTENSUITE_REVIEWKITPRO_VERSION' ) ) {
			return false;
		}

		$screen = get_current_screen();
		if ( ! $screen || ! in_array( $screen->id, array( 'dashboard', 'plugins' ), true ) ) {
			return false;
		}

		return ! get_user_meta( get_current_user_id(), self::DISMISS_META, true );
	}

	/**
	 * Promotion URL.
	 *
	 * @return string
	 */
	private function get_promo_url() {
		return apply_filters( 'gutensuite_reviewkit_pro_notice_url', 'https://reviewkit.gutensuite.net/pricing' );
	}

	/**
	 * Render the notice markup, inline CSS and dismiss script.
	 *
	 * @return void
	 */
	public function render_notice() {
		if ( ! $this->should_render() ) {
			return;
		}

		$promo_url = esc_url( $this->get_promo_url() );
		$nonce     = wp_create_nonce( 'gsk_dismiss_pro_notice' );
		$ajax_url  = esc_url( admin_url( 'admin-ajax.php' ) );
		?>
		<style id="gsk-pro-notice-css">
			.gsk-pro-notice {
				box-sizing: border-box;
				margin-block: 16px 8px;
				margin-inline: 2px 20px;
				display: flex;
				align-items: center;
				gap: 16px;
				padding: 16px 20px;
				border: 1px solid #30343b;
				border-radius: 8px;
				background: linear-gradient(100deg, #14161a 0%, #22262d 100%);
				color: #f2f3f5;
				box-shadow: 0 1px 2px rgba(0, 0, 0, 0.12);
				position: relative;
				overflow: hidden;
				transition: opacity 180ms ease-out, transform 180ms ease-out;
			}
			.gsk-pro-notice::before {
				content: "";
				position: absolute;
				inset-block: 0;
				inset-inline-start: 0;
				width: 3px;
				background: #f5b83d;
			}
			.gsk-pro-notice.is-dismissing {
				opacity: 0;
				transform: translateY(-4px);
			}
			.gsk-pro-notice__icon {
				flex: none;
				display: flex;
				align-items: center;
				justify-content: center;
				width: 36px;
				height: 36px;
				border-radius: 50%;
				background: rgba(245, 184, 61, 0.14);
				color: #f5b83d;
			}
			.gsk-pro-notice__icon .dashicons {
				font-size: 20px;
				width: 20px;
				height: 20px;
			}
			.gsk-pro-notice__content {
				flex: 1;
				min-width: 0;
				font-size: 14px;
				line-height: 1.55;
			}
			.gsk-pro-notice__title {
				margin: 0 0 2px;
				padding: 0;
				font-size: 15px;
				font-weight: 600;
				color: #ffffff;
			}
			.gsk-pro-notice__text {
				margin: 0;
				color: rgba(255, 255, 255, 0.82);
			}
			.gsk-pro-notice__cta {
				display: inline-block;
				margin-inline-start: 6px;
				padding: 6px 14px;
				border-radius: 6px;
				background: #f5b83d;
				color: #17181a;
				font-size: 13px;
				font-weight: 600;
				line-height: 1.4;
				text-decoration: none;
				white-space: nowrap;
			}
			.gsk-pro-notice__cta:hover,
			.gsk-pro-notice__cta:focus {
				background: #ffc95e;
				color: #17181a;
				text-decoration: none;
			}
			.gsk-pro-notice__dismiss {
				flex: none;
				display: flex;
				align-items: center;
				justify-content: center;
				width: 40px;
				height: 40px;
				margin-inline-end: -8px;
				padding: 0;
				border: 0;
				border-radius: 6px;
				background: transparent;
				color: rgba(255, 255, 255, 0.7);
				cursor: pointer;
			}
			.gsk-pro-notice__dismiss:hover {
				background: rgba(255, 255, 255, 0.1);
				color: #ffffff;
			}
			.gsk-pro-notice__dismiss:focus {
				box-shadow: none;
			}
			.gsk-pro-notice__dismiss:focus-visible,
			.gsk-pro-notice__cta:focus-visible {
				outline: 2px solid #f5b83d;
				outline-offset: 2px;
			}
			@media (max-width: 600px) {
				.gsk-pro-notice {
					margin-inline: 10px 10px;
					flex-wrap: wrap;
					align-items: flex-start;
				}
				.gsk-pro-notice__content {
					flex-basis: calc(100% - 72px);
				}
				.gsk-pro-notice__dismiss {
					position: absolute;
					top: 6px;
					inset-inline-end: 6px;
				}
			}
			@media (prefers-reduced-motion: reduce) {
				.gsk-pro-notice {
					transition: none;
				}
			}
		</style>

		<div id="gsk-pro-notice" class="gsk-pro-notice" role="status">
			<span class="gsk-pro-notice__icon" aria-hidden="true">
				<span class="dashicons dashicons-star-filled"></span>
			</span>
			<div class="gsk-pro-notice__content">
				<p class="gsk-pro-notice__title">
					<?php esc_html_e( 'Introducing Reviewkit Pro', 'gutensuite-reviewkit' ); ?>
				</p>
				<p class="gsk-pro-notice__text">
					<?php
					printf(
						/* translators: %s: promotion link */
						esc_html__( 'Build review widgets without code, now at %s.', 'gutensuite-reviewkit' ),
						'<a class="gsk-pro-notice__cta" href="' . $promo_url . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Black Friday prices', 'gutensuite-reviewkit' ) . '</a>'
					);
					?>
				</p>
			</div>
			<button type="button" class="gsk-pro-notice__dismiss" aria-label="<?php esc_attr_e( 'Dismiss this notice', 'gutensuite-reviewkit' ); ?>">
				<span class="dashicons dashicons-no-alt" aria-hidden="true"></span>
			</button>
		</div>

		<script>
			( function() {
				var notice = document.getElementById( 'gsk-pro-notice' );
				if ( ! notice ) {
					return;
				}
				var button = notice.querySelector( '.gsk-pro-notice__dismiss' );
				button.addEventListener( 'click', function() {
					notice.classList.add( 'is-dismissing' );
					window.setTimeout( function() {
						notice.remove();
					}, 200 );

					var body = new URLSearchParams();
					body.append( 'action', 'gutensuite_reviewkit_dismiss_pro_notice' );
					body.append( 'nonce', '<?php echo esc_js( $nonce ); ?>' );

					fetch( '<?php echo esc_js( $ajax_url ); ?>', {
						method: 'POST',
						credentials: 'same-origin',
						body: body
					} );
				} );
			} )();
		</script>
		<?php
	}

	/**
	 * AJAX handler: persist dismissal for the current user.
	 *
	 * @return void
	 */
	public function ajax_dismiss() {
		check_ajax_referer( 'gsk_dismiss_pro_notice', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You are not allowed to do that.', 'gutensuite-reviewkit' ) ), 403 );
		}

		update_user_meta( get_current_user_id(), self::DISMISS_META, 1 );
		wp_send_json_success();
	}
}
