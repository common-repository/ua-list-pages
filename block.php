<?php
/**
Plugin Name: List Pages Block
Plugin URI: https://github.com/UdiAzulay/ua-list-pages
Version: 1.1
Author: Udi Azulay
Author uri: http://www.modern-sys.com
Description: Add Gutenberg block to display child pages / posts with images
*/

function enqueue_block_ua_list_pages() 
{
	wp_enqueue_script(
		'ua-list-pages-block-js',
		esc_url( plugin_dir_url(__FILE__) . 'block.js'),
		array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor', 'wp-components' ),
		'1.0.0', true // Enqueue the script in the footer.
	);
}
add_action( 'enqueue_block_editor_assets', 'enqueue_block_ua_list_pages' );

function render_block_ua_list_pages( $attributes ) {
	$args = array(
        'post_type'        => $attributes['postType'],
		'posts_per_page'   => $attributes['postsToShow'],
		'post_status'      => 'publish',
		'order'            => $attributes['order'],
		'orderby'          => $attributes['orderBy'],
		'suppress_filters' => false,
	);

	if ( isset( $attributes['categories'] ) ) {
		$args['category'] = $attributes['categories'];
    }
    if ( $args['post_type'] == 'page' && !isset($args['post_parent']) ) $args['post_parent'] = get_the_ID();

	$recent_posts = get_posts( $args );

	$list_items_markup = '';

	$displayTitle = isset( $attributes['displayPostTitle'] ) ? $attributes['displayPostTitle'] : true;
	$displayPostDate = isset( $attributes['displayPostDate'] ) && $attributes['displayPostDate'];
	$displatPostContent = isset( $attributes['displayPostContent'] ) && $attributes['displayPostContent'];
	$displayPostContentRadio = isset( $attributes['displayPostContentRadio'] ) ? $attributes['displayPostContentRadio'] : 'excerpt';
	$excerptLength = $attributes['excerptLength'];
    $image_size = isset( $attributes['imageSize'] ) ? $attributes['imageSize'] : null;
    $alignClass = isset( $attributes['align'] ) ? 'text-align:' . $attributes['align'] : null;
	$positions = isset( $attributes['positions'] ) ? $attributes['positions'] : 'TIDC';
	$positionFormat = $positions;

	$positionFormat = str_replace('T', '%1$s', $positionFormat);
	$positionFormat = str_replace('I', '%2$s', $positionFormat);
	$positionFormat = str_replace('D', '%3$s', $positionFormat);
	$positionFormat = str_replace('C', '%4$s', $positionFormat);

	foreach ( $recent_posts as $post ) {
        $title_markup = null;
        $image_markup = null;
        $date_markup = null;
        $content_markup = null;

        if ($displayTitle) {
            $title = get_the_title( $post );
            if ( ! $title ) $title = __( '(no title)' );
            $title_markup = sprintf('<a class="title" style="%3$s" href="%1$s">%2$s</a>', esc_url( get_permalink( $post ) ), $title, $alignClass);
        }
        if ( $image_size &&  has_post_thumbnail($post) ) 
            $image_markup .= '<a class="image-link" style="' . $alignClass . '"  href="' . esc_url( get_permalink( $post ) ) . '">' . get_the_post_thumbnail( $post, $image_size ) . '</a>';

        if ( $displayPostDate ) {
            $date_markup = sprintf(
                '<time datetime="%1$s" class="wp-block-latest-posts__post-date">%2$s</time>',
                esc_attr( get_the_date( 'c', $post ) ),
                esc_html( get_the_date( '', $post ) )
            );
        }
        
		if ( $displatPostContent && $displayPostContentRadio === 'excerpt' ) {
            $post_excerpt = $post->post_excerpt;
            if ( ! ( $post_excerpt ) ) $post_excerpt = $post->post_content;
            $trimmed_excerpt = esc_html( wp_trim_words( $post_excerpt, $excerptLength, ' &hellip; ' ) );
            $content_markup .= sprintf( '<div class="wp-block-latest-posts__post-excerpt">%1$s', $trimmed_excerpt );
            if ( strpos( $trimmed_excerpt, ' &hellip; ' ) !== false )
                $content_markup .= sprintf('<a href="%1$s">%2$s</a>', esc_url( get_permalink( $post ) ), __( 'Read more' ) );
			$content_markup .= '</div>';
        }
        if ( $displatPostContent && $displayPostContentRadio === 'full_post' ) {
            $content_markup .= sprintf(
                '<div class="wp-block-latest-posts__post-full-content">%1$s</div>',
                wp_kses_post( html_entity_decode( $post->post_content, ENT_QUOTES, get_option( 'blog_charset' ) ) )
            );
        }
        $isAutoExcerpt = ( isset( $attributes['className'] ) ) &&  $attributes['className'] == 'is-style-autoExcerpt';
        $item_markup = sprintf($positionFormat, $title_markup, $image_markup, $date_markup, $content_markup);
        $list_items_markup .= '<li>' .$item_markup . '</li>';
	}

	$class = 'wp-block-latest-posts wp-block-latest-posts__list';
	if ( isset( $attributes['postLayout'] ) && 'grid' === $attributes['postLayout'] ) $class .= ' is-grid';
	if ( isset( $attributes['columns'] ) && 'grid' === $attributes['postLayout'] ) $class .= ' columns-' . $attributes['columns'];
	if ( $displayPostDate ) $class .= ' has-dates';
	if ( isset( $attributes['className'] ) ) $class .= ' ' . $attributes['className'];

	return sprintf('<ul class="ua-list-pages %1$s">%2$s</ul>', esc_attr( $class ), $list_items_markup);
}

function register_block_ua_list_pages() {
	register_block_type(
		'ua-list-pages/block',
		array(
			'attributes'      => array(
				'postType'                   => array(
					'type' => 'string',
                    'enum' => array( 'post', 'page' ),
                    'default' => 'post'
                ),
                'imageSize' => array(
                    'type'    => 'string',
					'enum' => array( '', '50x50', 'thumbnail', 'small', 'medium', 'large' ),
                ),
                'positions' => array(
					'type'    => 'string',
                    'enum' => array( 'TIDC', 'ITDC', 'TICD', 'ITCD' ),
                ),
                'align'                   => array(
					'type' => 'string',
					'enum' => array( '', 'left', 'center', 'right', 'wide', 'full' ),
				),
				'className'               => array(
					'type' => 'string',
				),
				'categories'              => array(
					'type' => 'string',
				),
				'postsToShow'             => array(
					'type'    => 'number',
					'default' => 5,
				),
				'displayPostTitle'      => array(
					'type'    => 'boolean',
					'default' => true,
				),
				'displayPostContent'      => array(
					'type'    => 'boolean',
					'default' => false,
				),
				'displayPostContentRadio' => array(
					'type'    => 'string',
					'default' => 'excerpt',
				),
				'excerptLength'           => array(
					'type'    => 'number',
					'default' => 55,
				),
				'displayPostDate'         => array(
					'type'    => 'boolean',
					'default' => false,
				),
				'postLayout'              => array(
					'type'    => 'string',
					'default' => 'list',
				),
				'columns'                 => array(
					'type'    => 'number',
					'default' => 3,
				),
				'order'                   => array(
					'type'    => 'string',
					'default' => 'desc',
				),
				'orderBy'                 => array(
					'type'    => 'string',
					'default' => 'date',
				),
			),
			'render_callback' => 'render_block_ua_list_pages',
		)
	);
}
add_action( 'init', 'register_block_ua_list_pages' );
