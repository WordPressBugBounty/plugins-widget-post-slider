<?php
/**
 * Thumbnail Size.
 *
 * @package           Widget_Post_Slider
 */

add_action( 'after_setup_theme', 'sp_widget_post_slider_register_image_size' );

/**
 * Register the slider thumbnail size.
 *
 * Hooked on after_setup_theme rather than running at file load so the
 * registration happens at the WP-defined extension point and doesn't
 * fire before the rest of WP is ready.
 *
 * @return void
 */
function sp_widget_post_slider_register_image_size() {
	add_image_size( 'wps_thumbnail_size', 360, 250, true );
}


/**
 * Upper bound for the number of slides a single widget instance may render.
 *
 * Acts as a sanity ceiling so a stored or POSTed count cannot produce an
 * arbitrarily large get_posts() query. Filterable for sites that genuinely
 * need a larger slider; values < 1 fall back to the default ceiling.
 *
 * @return int
 */
function sp_widget_post_slider_max_count() {
	$max = (int) apply_filters( 'widget_post_slider_max_count', 50 );
	return $max > 0 ? $max : 50;
}


// Widget.
add_action( 'widgets_init', 'sp_widget_post_slider_register' );

/**
 * Register Post Slider Widget.
 *
 * @return void
 */
function sp_widget_post_slider_register() {
	register_widget( 'SP_Widget_Post_Slider' );
}

/**
 * The Post Slider Widget class.
 *
 * Stored instance shape:
 *   - title    string  Display title; passed through the widget_title filter on render.
 *   - cat_name string  'all' sentinel for "no category filter", otherwise a term ID as a string.
 *                      Stored as a string so selected() comparisons in form() match the <option value>.
 *   - count    int     Slides to query; coerced to >= 1 (defaults to 5).
 */
class SP_Widget_Post_Slider extends WP_Widget {

	/**
	 * Widget setup.
	 */
	public function __construct() {
		parent::__construct(
			'sp_widget_post_slider', // Base ID.
			esc_html__( 'Widget Post Slider', 'widget-post-slider' ), // Name.
			array( 'description' => esc_html__( 'Widget Post Slider to display posts', 'widget-post-slider' ) ) // Args.
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * Render notes:
	 *   - cat_name === 'all' is the sentinel for "no category filter"; any other value is treated as a term ID.
	 *   - sp_widget_post_slider_enqueue_assets() is called here as a fallback for the Legacy Widget Block,
	 *     where is_active_widget() in scripts.php does not fire.
	 *   - The global $post is reassigned so setup_postdata() makes template tags (get_permalink,
	 *     get_the_title, has_post_thumbnail) target the current loop item; wp_reset_postdata() restores it.
	 *   - Posts without a featured image fall back to a shipped placeholder SVG sized to the
	 *     registered wps_thumbnail_size (360x250) so every slide has consistent dimensions; CSS
	 *     stretches it to the widget area like real thumbnails.
	 *
	 * @param array $args     Sidebar arguments (before/after_widget, before/after_title).
	 * @param array $instance The widget instance.
	 * @return void
	 */
	public function widget( $args, $instance ) {
		$before_widget = isset( $args['before_widget'] ) ? $args['before_widget'] : '';
		$after_widget  = isset( $args['after_widget'] ) ? $args['after_widget'] : '';
		$before_title  = isset( $args['before_title'] ) ? $args['before_title'] : '';
		$after_title   = isset( $args['after_title'] ) ? $args['after_title'] : '';

		$raw_title = isset( $instance['title'] ) ? $instance['title'] : '';
		$title     = apply_filters( 'widget_title', $raw_title, $instance, $this->id_base );
		$count = isset( $instance['count'] ) ? absint( $instance['count'] ) : 5;
		if ( $count < 1 ) {
			$count = 5;
		}
		$count = min( $count, sp_widget_post_slider_max_count() );
		$cat_name = isset( $instance['cat_name'] ) ? sanitize_text_field( $instance['cat_name'] ) : 'all';

		echo wp_kses_post( $before_widget );

		if ( $title ) {
			echo wp_kses_post( $before_title . $title . $after_title );
		}

		$query_args = array(
			'posts_per_page' => $count,
			'no_found_rows'  => true,
		);
		if ( 'all' !== $cat_name && (int) $cat_name > 0 ) {
			$query_args['cat'] = (int) $cat_name;
		}

		$posts = get_posts( $query_args );

		if ( ! empty( $posts ) ) {
			sp_widget_post_slider_enqueue_assets();
			?>
			<div class="sp-widget-post-slider-section">
				<?php
				global $post;
				$placeholder_src = esc_url( WIDGET_POST_SLIDER_URL . 'assets/images/placeholder.svg' );
				foreach ( $posts as $post ) {
					setup_postdata( $post );
					if ( has_post_thumbnail() ) {
						$image_html = get_the_post_thumbnail( $post->ID, 'wps_thumbnail_size', array( 'class' => 'wps-image' ) );
					} else {
						$image_html = sprintf(
							'<img class="wps-image wps-image--placeholder" src="%s" width="360" height="250" alt="" loading="lazy" />',
							$placeholder_src
						);
					}
					?>
					<div class="widget-post-slider">
						<a href="<?php echo esc_url( get_permalink() ); ?>"><?php echo wp_kses_post( $image_html ); ?></a>
						<div class="wps-caption"><a href="<?php echo esc_url( get_permalink() ); ?>"><?php echo esc_html( get_the_title() ); ?></a></div>
					</div>
					<?php
				}
				wp_reset_postdata();
				?>
			</div>
			<?php
		}

		echo wp_kses_post( $after_widget );
	}

	/**
	 * Sanitize and persist the submitted widget settings.
	 *
	 * cat_name is normalized to either the 'all' sentinel or a non-negative integer term ID
	 * stored as a string, so selected() comparisons against <option value> in form() match.
	 *
	 * @param array $new_instance Values submitted from the widget form.
	 * @param array $old_instance Previously stored values (used as the merge base).
	 * @return array The sanitized instance to persist.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['title'] = isset( $new_instance['title'] ) ? sanitize_text_field( $new_instance['title'] ) : '';

		$cat_name = isset( $new_instance['cat_name'] ) ? sanitize_text_field( $new_instance['cat_name'] ) : 'all';
		if ( 'all' !== $cat_name ) {
			$term_id  = absint( $cat_name );
			$cat_name = ( $term_id > 0 && term_exists( $term_id, 'category' ) ) ? (string) $term_id : 'all';
		}
		$instance['cat_name'] = $cat_name;

		$count             = isset( $new_instance['count'] ) ? absint( $new_instance['count'] ) : 5;
		$count             = $count > 0 ? $count : 5;
		$instance['count'] = min( $count, sp_widget_post_slider_max_count() );

		return $instance;
	}

	/**
	 * The form function.
	 *
	 * @param array $instance The widget instance.
	 * @return void
	 */
	public function form( $instance ) {
		$defaults = array(
			'title'    => __( 'Widget Post Slider', 'widget-post-slider' ),
			'cat_name' => 'all',
			'count'    => 5,
		);
		$instance = wp_parse_args( (array) $instance, $defaults );
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Widget Title:', 'widget-post-slider' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" />
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'cat_name' ) ); ?>"><?php esc_html_e( 'Select Category:', 'widget-post-slider' ); ?></label>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'cat_name' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'cat_name' ) ); ?>">
				<option value="all" <?php selected( $instance['cat_name'], 'all' ); ?>><?php esc_html_e( 'All Categories', 'widget-post-slider' ); ?></option>
				<?php
				$categories = get_categories( array( 'hide_empty' => false ) );
				foreach ( $categories as $category ) {
					?>
					<option value="<?php echo esc_attr( $category->term_id ); ?>" <?php selected( (string) $instance['cat_name'], (string) $category->term_id ); ?>><?php echo esc_html( $category->name ); ?></option>
					<?php
				}
				?>
			</select>
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'count' ) ); ?>"><?php esc_html_e( 'Slide Count', 'widget-post-slider' ); ?></label>
			<input class="widefat" type="number" min="1" step="1" id="<?php echo esc_attr( $this->get_field_id( 'count' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'count' ) ); ?>" value="<?php echo esc_attr( $instance['count'] ); ?>" />
		</p>

		<?php
	}
}
