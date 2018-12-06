<?php

add_action( 'wp_enqueue_scripts', 'enqueue_parent_styles' );

function enqueue_parent_styles() {
   wp_enqueue_style( 'parent-style', get_template_directory_uri().'/style.css' );
}




// Register the widget
function test_load_widget() {
    register_widget( 'Test_Widget' );
}
add_action( 'widgets_init', 'test_load_widget' );

 
// Creating the widget 
class Test_Widget extends WP_Widget {
 
    function __construct() {
        parent::__construct(
        
            // Base ID
            'Test_Widget', 
            
            // Widget name will appear in UI
            __('Test Widget', 'twentyseventeen'), 
            
            // Widget description
            array( 'description' => __( 'Sample test widget', 'twentyseventeen' ), ) 
        );
    }


 
    // Creating widget front-end
    public function widget( $args, $instance ) {
        
        $title = apply_filters( 'widget_title', $instance['title'] );

        // Number of posts
        $number = ( ! empty( $instance['number'] ) ) ? absint( $instance['number'] ) : 3;
        // Number of excerpt characters
        $excerpt_number = ( ! empty( $instance['excerpt_number'] ) ) ? absint( $instance['excerpt_number'] ) : 20;

        $post_order = empty($instance['post_order']) ? '' : $instance['post_order'];
        $show_thumb = isset( $instance['show_thumb'] ) ? $instance['show_thumb'] : false;
        $show_excerpt = isset( $instance['show_excerpt'] ) ? $instance['show_excerpt'] : false;
        
        $t_query = new WP_Query( apply_filters( 'widget_posts_args', array(
			'posts_per_page'      => $number,
			'no_found_rows'       => true,
            'post_status'         => 'publish',
            'order'               => $post_order,
			'ignore_sticky_posts' => true,
		), $instance ) );

		if ( ! $t_query->have_posts() ) {
			return;
		}
        
        // echo before widget arguments
        echo $args['before_widget'];

        if ( ! empty( $title ) ) {
            echo $args['before_title'] . $title . $args['after_title'];
        }
        ?>

        <ul>
			<?php foreach ( $t_query->posts as $recent_post ) : ?>

				<?php
                $post_title = get_the_title( $recent_post->ID );
                $post_content = $recent_post->post_content;

                $title = ( ! empty( $post_title ) ) ? $post_title : __( '(no title)' );
                
                $thumbnail_id = get_post_thumbnail_id($recent_post->ID);
                $thumbnail_url = wp_get_attachment_image_src($thumbnail_id, '[thumbnail-size]', true);
                $thumbnail_meta = get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true);


                $post_excerpt = wp_trim_words( $post_content, $excerpt_number, '...');

				?>
				<li>
					<a href="<?php the_permalink( $recent_post->ID ); ?>"><?php echo $title ; ?></a>

					<?php if ( $show_thumb ) : ?>
						<div class="post-image">
                            <img src="<?php echo $thumbnail_url[0] ?>" alt="<?php echo $thumbnail_meta ?>">
                        </div>
					<?php endif; ?>

					<?php if ( $show_excerpt ) : ?>
						<p><?php echo $post_excerpt; ?></p>
					<?php endif; ?>

				</li>
			<?php endforeach; ?>
		</ul>

        <?php

        echo $args['after_widget'];
    }


            
    // Widget Backend 
    public function form( $instance ) {
        $title     = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
        $number    = isset( $instance['number'] ) ? absint( $instance['number'] ) : 3;

        $post_order_title = isset( $instance['post_order_title'] ) ? esc_attr( $instance['post_order_title'] ) : 'Post Order';
        $post_order = isset( $instance['post_order'] ) ? esc_attr( $instance['post_order'] ) : '';

		$excerpt_number = isset( $instance['excerpt_number'] ) ? absint( $instance['excerpt_number'] ) : 20;
		$show_thumb = isset( $instance['show_thumb'] ) ? (bool) $instance['show_thumb'] : false;
		$show_excerpt = isset( $instance['show_excerpt'] ) ? (bool) $instance['show_excerpt'] : false;

        ?>

        <!-- Title -->
		<p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
		    <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
        </p>

        <!-- Number of posts -->
		<p>
            <label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Number of posts to show:' ); ?></label>
		    <input class="tiny-text" id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="number" step="1" min="1" value="<?php echo $number; ?>" size="3" />
        </p>

        <!-- Display thumbnails -->
		<p>
            <input class="checkbox" type="checkbox"<?php checked( $show_thumb ); ?> id="<?php echo $this->get_field_id( 'show_thumb' ); ?>" name="<?php echo $this->get_field_name( 'show_thumb' ); ?>" />
		    <label for="<?php echo $this->get_field_id( 'show_thumb' ); ?>"><?php _e( 'Display post thumbnail?' ); ?></label>
        </p>

        <!-- Posts order -->
        <p>
            <label for="<?php echo $this->get_field_id('text'); ?>">Order: 
                <select id="<?php echo $this->get_field_id('post_order'); ?>" name="<?php echo $this->get_field_name('post_order'); ?>" type="text">
                    <option value='ASC'<?php echo ($post_order=='ASC')?'selected':''; ?>>Ascending</option>
                    <option value='DESC'<?php echo ($post_order=='DESC')?'selected':''; ?>>Descending</option> 
                </select>                
            </label>
        </p>

        <!-- Display excerpt -->
		<p>
            <input class="checkbox" type="checkbox"<?php checked( $show_excerpt ); ?> id="<?php echo $this->get_field_id( 'show_excerpt' ); ?>" name="<?php echo $this->get_field_name( 'show_excerpt' ); ?>" />
		    <label for="<?php echo $this->get_field_id( 'show_excerpt' ); ?>"><?php _e( 'Display post excerpt?' ); ?></label>
        </p>

        <!-- Number of excerpt character -->
        <p>
            <label for="<?php echo $this->get_field_id( 'excerpt_number' ); ?>"><?php _e( 'Set excerpt lengh:' ); ?></label>
		    <input class="tiny-text" id="<?php echo $this->get_field_id( 'excerpt_number' ); ?>" name="<?php echo $this->get_field_name( 'excerpt_number' ); ?>" type="number" step="1" min="10" value="<?php echo $excerpt_number; ?>" size="20" />
        </p>

        <?php 
    }
        
    // Updating widget replacing old instances with new
    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
        $instance['number'] = (int) $new_instance['number'];
        $instance['post_order'] = ( ! empty( $new_instance['post_order'] ) ) ? strip_tags( $new_instance['post_order'] ) : '';
        $instance['post_order_title'] = ( ! empty( $new_instance['post_order_title'] ) ) ? strip_tags( $new_instance['post_order_title'] ) : '';
        $instance['excerpt_number'] = (int) $new_instance['excerpt_number'];
		$instance['show_thumb'] = isset( $new_instance['show_thumb'] ) ? (bool) $new_instance['show_thumb'] : false;
		$instance['show_excerpt'] = isset( $new_instance['show_excerpt'] ) ? (bool) $new_instance['show_excerpt'] : false;
        return $instance;
    }

}
