<?php
/**
 * Template for displaying a single review card.
 *
 * @package GutensuiteReviewKit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<article class="gutensuite-reviewkit-reviews__item">
	<div class="gutensuite-reviewkit-reviews__item-header">
		<div class="gutensuite-reviewkit-reviews__item-left">
			<div class="gutensuite-reviewkit-reviews__item-rating">
				<?php
				$rating_alt = sprintf(
					/* translators: %d: The rating number (1-5) */
					__( 'Rating %d stars', 'gutensuite-reviewkit' ),
					$review->rating
				);
				?>
				<img class="gutensuite-reviewkit-reviews__item-rating-image" src="<?php echo esc_url( gtp_get_star_image_url( $review->rating ) ); ?>" alt="<?php echo esc_attr( $rating_alt ); ?>">
			</div>
			<div class="gutensuite-reviewkit-reviews__item-author">
				<?php echo esc_html__( 'By', 'gutensuite-reviewkit' ); ?> <span class="gutensuite-reviewkit-reviews__item-author-name"><?php echo esc_html( $review->customer['name'] ); ?></span>
			</div>
		</div>

		<div class="gutensuite-reviewkit-reviews__item-right">
			<p class="gutensuite-reviewkit-reviews__item-time"><?php echo esc_html( $times_ago ); ?></p>
		</div>
	</div>

    <h4 class="gutensuite-reviewkit-reviews__item-title"><?php echo esc_html( $review->reviewTitle ); // phpcs:ignore ?></h4>
    <p class="gutensuite-reviewkit-reviews__item-content"><?php echo esc_html( $review->reviewBody ); // phpcs:ignore ?></p>
</article>