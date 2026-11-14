<?php
/**
 * Template for displaying Trustpilot review carousel.
 *
 * @package Gutensuite_Reviewkit
 */

defined( 'ABSPATH' ) || exit;

$rating_text = gtp_get_rating_text();

$carousel_settings = array(
	'autoplay'        => true,
	'delay'           => 3000,
	'loop'            => true,
	'pause_on_hover'  => true,
	'slides_per_view' => array(
		'desktop' => 3,
		'tablet'  => 2,
		'mobile'  => 1.2,
	),
	'space_between'   => array(
		'desktop' => 20,
		'tablet'  => 10,
		'mobile'  => 5,
	),
	'pagination'      => 'bullets',
	'navigation'      => true,
	'centeredSlides'  => false,
	'speed'           => 400,
);

	$carousel_settings = wp_json_encode( $carousel_settings );
?>
<section class="gutensuite-reviewkit-wrap">
	<header class="gutensuite-reviewkit-header">
		<div class="gutensuite-reviewkit-header__top">
			<h2 class="gutensuite-reviewkit-header__title"><?php echo esc_html( $rating_text ); ?></h2>
			<img class="gutensuite-reviewkit-header__rating-image" src="<?php echo esc_url( gtp_get_star_image_url( $b_data->trustScore ) ); // phpcs:ignore ?>" alt="<?php echo esc_attr__( 'Trustpilot rating', 'gutensuite-reviewkit' ); ?>">
		</div>

		<div class="gutensuite-reviewkit-header__bottom">
			<div class="gutensuite-reviewkit-header__stats">
				<span class="gutensuite-reviewkit-header__rated"><?php echo esc_html__( 'Rated', 'gutensuite-reviewkit' ); ?></span>
				<span class="gutensuite-reviewkit-header__score">
					<strong><?php echo esc_html( $b_data->trustScore ); // phpcs:ignore ?></strong> <?php echo esc_html__( 'out of 5 based on', 'gutensuite-reviewkit' ); ?>
				</span>
				<span class="gutensuite-reviewkit-header__count"><?php echo esc_html( $b_data->numberOfReviews ); // phpcs:ignore ?> <?php echo esc_html__( 'reviews', 'gutensuite-reviewkit' ); ?></span>
			</div>

			<div class="gutensuite-reviewkit-header__badge">
				<img class="gutensuite-reviewkit-header__badge-icon" src="<?php echo esc_url( gtp_get_single_star_image_url() ); ?>" alt="<?php echo esc_attr__( 'Trustpilot', 'gutensuite-reviewkit' ); ?>" />
				<span class="gutensuite-reviewkit-header__badge-name"><?php echo esc_html__( 'Trustpilot', 'gutensuite-reviewkit' ); ?></span>
			</div>
		</div>
	</header>

	<div class="style-<?php echo esc_attr( $atts['style'] ?? 'carousel' ); ?>">
		<div class="grr-swiper-activation swiper swiper-container" data-carousel-settings="<?php echo esc_attr( $carousel_settings ); ?>">
			<div class="swiper-wrapper">
				<?php if ( ! empty( $review_data ) ) : ?>
					<?php
					foreach ( $review_data as $key => $review ) :
						$review      = (object) $review;
						$time_string = strtotime( $review->dates['experiencedDate'] );
						$times_ago   = gtp_times_ago( $time_string );
						?>
						<div class="swiper-slide">
							<?php include GUTENSUITE_REVIEWKIT_ROOT_DIR_PATH . 'includes/templates/cards/card-1.php'; ?>
						</div>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
			<div class="swiper-pagination"></div>
		</div>
		<div class="swiper-button-container">
			<span class="swiper-button-next">
				<span class="dashicons dashicons-arrow-right-alt2"></span>
			</span>
			<span class="swiper-button-prev">
				<span class="dashicons dashicons-arrow-left-alt2"></span>
			</span>
		</div>
	</div>
</section>
