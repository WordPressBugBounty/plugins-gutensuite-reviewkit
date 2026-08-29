<?php
/**
 * Template for displaying Trustpilot review grid.
 *
 * @package Gutensuite_Reviewkit
 */

$rating_text = gtp_get_rating_text();
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

	<div class="gutensuite-reviewkit-reviews style-<?php echo esc_attr( $atts['style'] ?? 'grid' ); ?>">
		<?php if ( ! empty( $review_data ) ) : ?>
			<?php
			foreach ( $review_data as $key => $review ) :
				$review      = (object) $review;
				$time_string = strtotime( $review->dates['experiencedDate'] );
				$times_ago   = gtp_times_ago( $time_string );
				?>
				<?php include GUTENSUITE_REVIEWKIT_ROOT_DIR_PATH . 'includes/templates/cards/card-1.php'; ?>
			<?php endforeach; ?>
		<?php endif; ?>
	</div>
</section>
