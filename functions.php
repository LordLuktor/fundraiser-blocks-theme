<?php
/**
 * Fundraiser Blocks Theme Functions
 *
 * @package Fundraiser_Blocks
 * @since 1.0.0
 */

// Exit if accessed directly - Security measure
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme Setup
 */
function fundraiser_blocks_setup() {
	// Add default posts and comments RSS feed links to head
	add_theme_support( 'automatic-feed-links' );

	// Let WordPress manage the document title
	add_theme_support( 'title-tag' );

	// Enable support for Post Thumbnails
	add_theme_support( 'post-thumbnails' );
	set_post_thumbnail_size( 1200, 675, true );

	// Add custom image sizes
	add_image_size( 'fundraiser-campaign-card', 600, 400, true );
	add_image_size( 'fundraiser-campaign-hero', 1920, 1080, true );

	// Enable HTML5 support
	add_theme_support( 'html5', array(
		'search-form',
		'comment-form',
		'comment-list',
		'gallery',
		'caption',
		'style',
		'script',
	) );

	// Enable responsive embeds
	add_theme_support( 'responsive-embeds' );

	// Enable block styles
	add_theme_support( 'wp-block-styles' );

	// Enable editor styles
	add_theme_support( 'editor-styles' );

	// Enable align wide
	add_theme_support( 'align-wide' );

	// Enable custom line height
	add_theme_support( 'custom-line-height' );

	// Enable custom spacing
	add_theme_support( 'custom-spacing' );

	// Enable custom units
	add_theme_support( 'custom-units' );

	// Enable link color
	add_theme_support( 'link-color' );

	// Disable custom colors (use theme.json palette only)
	add_theme_support( 'disable-custom-colors' );

	// Disable custom font sizes (use theme.json sizes only)
	add_theme_support( 'disable-custom-font-sizes' );

	// Remove feed links (prevent information disclosure)
	remove_action( 'wp_head', 'feed_links', 2 );
	remove_action( 'wp_head', 'feed_links_extra', 3 );
}
add_action( 'after_setup_theme', 'fundraiser_blocks_setup' );

/**
 * Enqueue theme styles and scripts
 */
function fundraiser_blocks_enqueue_scripts() {
	// Main stylesheet
	wp_enqueue_style(
		'fundraiser-blocks-style',
		get_stylesheet_uri(),
		array(),
		wp_get_theme()->get( 'Version' )
	);

	// Custom theme CSS
	if ( file_exists( get_template_directory() . '/assets/css/theme.css' ) ) {
		wp_enqueue_style(
			'fundraiser-blocks-theme',
			get_template_directory_uri() . '/assets/css/theme.css',
			array( 'fundraiser-blocks-style' ),
			wp_get_theme()->get( 'Version' )
		);
	}

	// Only load scripts if needed
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'fundraiser_blocks_enqueue_scripts' );

/**
 * Security: Add security headers
 */
function fundraiser_blocks_security_headers() {
	// Prevent clickjacking
	header( 'X-Frame-Options: SAMEORIGIN' );

	// XSS Protection
	header( 'X-XSS-Protection: 1; mode=block' );

	// Prevent MIME sniffing
	header( 'X-Content-Type-Options: nosniff' );

	// Referrer Policy
	header( 'Referrer-Policy: strict-origin-when-cross-origin' );

	// Permissions Policy (formerly Feature Policy)
	header( "Permissions-Policy: geolocation=(), microphone=(), camera=()" );
}
add_action( 'send_headers', 'fundraiser_blocks_security_headers' );

/**
 * Security: Remove WordPress version from head
 */
remove_action( 'wp_head', 'wp_generator' );

/**
 * Security: Remove WordPress version from RSS feeds
 */
add_filter( 'the_generator', '__return_empty_string' );

/**
 * Security: Disable XML-RPC (prevent brute force attacks)
 */
add_filter( 'xmlrpc_enabled', '__return_false' );

/**
 * Security: Remove RSD link from head
 */
remove_action( 'wp_head', 'rsd_link' );

/**
 * Security: Remove Windows Live Writer manifest link
 */
remove_action( 'wp_head', 'wlwmanifest_link' );

/**
 * Security: Remove shortlink from head
 */
remove_action( 'wp_head', 'wp_shortlink_wp_head' );

/**
 * Security: Disable user enumeration
 */
function fundraiser_blocks_disable_user_enumeration( $redirect, $request ) {
	if ( preg_match( '/\?author=([0-9]*)(\/*)/i', $request ) ) {
		return home_url( '/404' );
	}
	return $redirect;
}
add_filter( 'redirect_canonical', 'fundraiser_blocks_disable_user_enumeration', 10, 2 );

/**
 * Security: Remove jQuery Migrate (if not needed)
 */
function fundraiser_blocks_remove_jquery_migrate( $scripts ) {
	if ( ! is_admin() && isset( $scripts->registered['jquery'] ) ) {
		$script = $scripts->registered['jquery'];

		if ( $script->deps ) {
			$script->deps = array_diff( $script->deps, array( 'jquery-migrate' ) );
		}
	}
}
add_action( 'wp_default_scripts', 'fundraiser_blocks_remove_jquery_migrate' );

/**
 * Register block patterns
 */
function fundraiser_blocks_register_patterns() {
	// Register pattern category
	register_block_pattern_category(
		'fundraiser',
		array( 'label' => __( 'Fundraiser', 'fundraiser-blocks' ) )
	);
}
add_action( 'init', 'fundraiser_blocks_register_patterns' );

/**
 * Add custom body classes
 */
function fundraiser_blocks_body_classes( $classes ) {
	// Add class for homepage
	if ( is_front_page() ) {
		$classes[] = 'fundraiser-home';
	}

	// Add class for campaigns
	if ( is_singular( 'fundraiser_campaign' ) || is_post_type_archive( 'fundraiser_campaign' ) ) {
		$classes[] = 'fundraiser-campaigns';
	}

	// Add has-sidebar class if active sidebar
	if ( is_active_sidebar( 'sidebar-1' ) ) {
		$classes[] = 'has-sidebar';
	}

	// Add fundraising method classes for campaign pages
	if ( is_page() ) {
		global $post;
		$page_template = get_post_meta( $post->ID, '_wp_page_template', true );

		if ( $page_template === 'page-campaign-template' ) {
			// Get fundraising method settings
			$enable_donations = get_post_meta( $post->ID, '_enable_donations', true );
			$enable_products = get_post_meta( $post->ID, '_enable_products', true );
			$enable_raffles = get_post_meta( $post->ID, '_enable_raffles', true );

			// Add classes based on enabled methods
			if ( $enable_donations !== '1' ) {
				$classes[] = 'hide-donations';
			}
			if ( $enable_products !== '1' ) {
				$classes[] = 'hide-products';
			}
			if ( $enable_raffles !== '1' ) {
				$classes[] = 'hide-raffles';
			}
		}
	}

	return $classes;
}
add_filter( 'body_class', 'fundraiser_blocks_body_classes' );

/**
 * Add CSS for conditional rendering of campaign sections
 */
function fundraiser_blocks_conditional_css() {
	if ( ! is_page() ) {
		return;
	}

	global $post;
	$page_template = get_post_meta( $post->ID, '_wp_page_template', true );

	if ( $page_template !== 'page-campaign-template' ) {
		return;
	}

	?>
	<style>
		/* Hide sections based on fundraising methods */
		body.hide-donations .campaign-donations-section {
			display: none !important;
		}
		body.hide-products .campaign-products-section {
			display: none !important;
		}
		body.hide-raffles .campaign-raffles-section {
			display: none !important;
		}
	</style>
	<?php
}
add_action( 'wp_head', 'fundraiser_blocks_conditional_css' );

/**
 * Sanitize SVG uploads
 */
function fundraiser_blocks_sanitize_svg( $file ) {
	// Only for SVG files
	if ( $file['type'] !== 'image/svg+xml' ) {
		return $file;
	}

	// Basic SVG sanitization
	$svg_content = file_get_contents( $file['tmp_name'] );

	// Remove potentially dangerous elements
	$dangerous_patterns = array(
		'/<script[^>]*>.*?<\/script>/is',
		'/on\w+="[^"]*"/i',
		'/on\w+=\'[^\']*\'/i',
	);

	$svg_content = preg_replace( $dangerous_patterns, '', $svg_content );

	// Save sanitized content
	file_put_contents( $file['tmp_name'], $svg_content );

	return $file;
}
add_filter( 'wp_handle_upload_prefilter', 'fundraiser_blocks_sanitize_svg' );

/**
 * Allow SVG uploads (with sanitization)
 */
function fundraiser_blocks_mime_types( $mimes ) {
	$mimes['svg'] = 'image/svg+xml';
	return $mimes;
}
add_filter( 'upload_mimes', 'fundraiser_blocks_mime_types' );

/**
 * Fix SVG thumbnails in media library
 */
function fundraiser_blocks_fix_svg_thumb_display( $response, $attachment, $meta ) {
	if ( $response['type'] === 'image' && $response['subtype'] === 'svg+xml' && class_exists( 'SimpleXMLElement' ) ) {
		try {
			$path = get_attached_file( $attachment->ID );
			if ( file_exists( $path ) ) {
				$svg = new SimpleXMLElement( file_get_contents( $path ) );
				$width = (int) $svg['width'];
				$height = (int) $svg['height'];

				if ( $width && $height ) {
					$response['sizes'] = array(
						'full' => array(
							'url' => $response['url'],
							'width' => $width,
							'height' => $height,
							'orientation' => $width > $height ? 'landscape' : 'portrait',
						),
					);
				}
			}
		} catch ( Exception $e ) {
			// Silently fail
		}
	}
	return $response;
}
add_filter( 'wp_prepare_attachment_for_js', 'fundraiser_blocks_fix_svg_thumb_display', 10, 3 );

/**
 * Customize excerpt length
 */
function fundraiser_blocks_excerpt_length( $length ) {
	return 25;
}
add_filter( 'excerpt_length', 'fundraiser_blocks_excerpt_length' );

/**
 * Customize excerpt more string
 */
function fundraiser_blocks_excerpt_more( $more ) {
	return '...';
}
add_filter( 'excerpt_more', 'fundraiser_blocks_excerpt_more' );

/**
 * Performance: Defer non-critical JavaScript
 */
function fundraiser_blocks_defer_scripts( $tag, $handle, $src ) {
	// Skip if admin or login page
	if ( is_admin() || is_login() ) {
		return $tag;
	}

	// List of scripts to defer
	$defer_scripts = array(
		'fundraiser-blocks-theme',
	);

	if ( in_array( $handle, $defer_scripts, true ) ) {
		return str_replace( ' src', ' defer src', $tag );
	}

	return $tag;
}
add_filter( 'script_loader_tag', 'fundraiser_blocks_defer_scripts', 10, 3 );

/**
 * Performance: Add preconnect for external resources
 */
function fundraiser_blocks_resource_hints( $urls, $relation_type ) {
	if ( 'preconnect' === $relation_type ) {
		$urls[] = array(
			'href' => 'https://fonts.googleapis.com',
			'crossorigin',
		);
		$urls[] = array(
			'href' => 'https://fonts.gstatic.com',
			'crossorigin',
		);
	}
	return $urls;
}
add_filter( 'wp_resource_hints', 'fundraiser_blocks_resource_hints', 10, 2 );

/**
 * Custom campaign card shortcode
 */
function fundraiser_blocks_campaign_card_shortcode( $atts ) {
	$atts = shortcode_atts( array(
		'id' => 0,
	), $atts );

	$campaign_id = intval( $atts['id'] );

	if ( ! $campaign_id ) {
		return '';
	}

	$campaign = get_post( $campaign_id );

	if ( ! $campaign || $campaign->post_type !== 'fundraiser_campaign' ) {
		return '';
	}

	// Get campaign meta
	$goal = get_post_meta( $campaign_id, '_campaign_goal', true );
	$raised = get_post_meta( $campaign_id, '_campaign_raised', true );
	$percentage = $goal > 0 ? min( 100, ( $raised / $goal ) * 100 ) : 0;

	ob_start();
	?>
	<div class="campaign-card">
		<?php if ( has_post_thumbnail( $campaign_id ) ) : ?>
			<div class="campaign-card-image">
				<?php echo get_the_post_thumbnail( $campaign_id, 'fundraiser-campaign-card' ); ?>
			</div>
		<?php endif; ?>
		<div class="campaign-card-content" style="padding: 1.5rem;">
			<h3 style="margin-top: 0;">
				<a href="<?php echo esc_url( get_permalink( $campaign_id ) ); ?>" style="text-decoration: none;">
					<?php echo esc_html( $campaign->post_title ); ?>
				</a>
			</h3>
			<div class="campaign-card-excerpt" style="margin-bottom: 1rem; color: #64748b;">
				<?php echo esc_html( wp_trim_words( $campaign->post_excerpt, 15 ) ); ?>
			</div>
			<div class="campaign-progress-bar" style="margin-bottom: 0.75rem;">
				<div class="campaign-progress-fill" style="width: <?php echo esc_attr( $percentage ); ?>%;"></div>
			</div>
			<div class="campaign-stats" style="display: flex; justify-content: space-between; font-size: 0.875rem;">
				<span><strong><?php echo esc_html( number_format( $percentage, 0 ) ); ?>%</strong> funded</span>
				<span><strong>$<?php echo esc_html( number_format( $raised, 2 ) ); ?></strong> raised</span>
			</div>
		</div>
	</div>
	<?php
	return ob_get_clean();
}
add_shortcode( 'campaign_card', 'fundraiser_blocks_campaign_card_shortcode' );

/**
 * Add admin notice if WooCommerce is not active
 */
function fundraiser_blocks_woocommerce_notice() {
	if ( ! class_exists( 'WooCommerce' ) && current_user_can( 'activate_plugins' ) ) {
		?>
		<div class="notice notice-warning">
			<p><?php esc_html_e( 'The Fundraiser Blocks theme works best with WooCommerce installed and activated.', 'fundraiser-blocks' ); ?></p>
		</div>
		<?php
	}
}
add_action( 'admin_notices', 'fundraiser_blocks_woocommerce_notice' );

/**
 * Create demo user on theme activation
 */
function fundraiser_blocks_create_demo_user() {
	$demo_username = 'demo_fundraiser';
	$demo_email = 'demo@fundraiser.local';
	
	// Check if demo user already exists
	if ( ! username_exists( $demo_username ) && ! email_exists( $demo_email ) ) {
		$user_id = wp_create_user( $demo_username, 'demo123!Pass', $demo_email );
		
		if ( ! is_wp_error( $user_id ) ) {
			// Set user role to fundraiser (or subscriber if role doesn't exist)
			$user = new WP_User( $user_id );
			if ( get_role( 'fundraiser' ) ) {
				$user->set_role( 'fundraiser' );
			} else {
				$user->set_role( 'subscriber' );
			}
			
			// Update user meta
			update_user_meta( $user_id, 'first_name', 'Demo' );
			update_user_meta( $user_id, 'last_name', 'User' );
			update_user_meta( $user_id, 'description', 'This is a demonstration account for testing the fundraiser platform.' );
		}
	}
}
add_action( 'after_switch_theme', 'fundraiser_blocks_create_demo_user' );

/**
 * Handle demo login endpoint
 */
function fundraiser_blocks_demo_login() {
	// Check if this is the demo login page
	if ( isset( $_SERVER['REQUEST_URI'] ) && strpos( $_SERVER['REQUEST_URI'], '/demo-login/' ) !== false ) {
		
		// Security: Add nonce for CSRF protection
		$nonce = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';
		
		// If already logged in as demo user, redirect to dashboard
		if ( is_user_logged_in() ) {
			$current_user = wp_get_current_user();
			if ( $current_user->user_login === 'demo_fundraiser' ) {
				wp_safe_redirect( home_url( '/campaign-dashboard/' ) );
				exit;
			}
		}
		
		// Get demo user
		$demo_user = get_user_by( 'login', 'demo_fundraiser' );
		
		if ( ! $demo_user ) {
			// Create demo user if it doesn't exist
			fundraiser_blocks_create_demo_user();
			$demo_user = get_user_by( 'login', 'demo_fundraiser' );
		}
		
		if ( $demo_user ) {
			// Log out current user if any
			if ( is_user_logged_in() ) {
				wp_logout();
			}
			
			// Set authentication cookies
			wp_set_current_user( $demo_user->ID );
			wp_set_auth_cookie( $demo_user->ID );
			do_action( 'wp_login', $demo_user->user_login, $demo_user );
			
			// Redirect to campaign dashboard
			wp_safe_redirect( home_url( '/campaign-dashboard/' ) );
			exit;
		}
	}
}
add_action( 'template_redirect', 'fundraiser_blocks_demo_login', 1 );

/**
 * Add demo notice to admin bar
 */
function fundraiser_blocks_demo_admin_bar_notice( $wp_admin_bar ) {
	if ( is_user_logged_in() ) {
		$current_user = wp_get_current_user();
		if ( $current_user->user_login === 'demo_fundraiser' ) {
			$wp_admin_bar->add_node( array(
				'id'    => 'demo-notice',
				'title' => '🎯 Demo Mode - View Only',
				'href'  => '#',
				'meta'  => array(
					'class' => 'demo-mode-notice',
					'title' => 'You are logged in as a demo user',
				),
			) );
		}
	}
}
add_action( 'admin_bar_menu', 'fundraiser_blocks_demo_admin_bar_notice', 100 );

/**
 * Style the demo notice
 */
function fundraiser_blocks_demo_notice_styles() {
	if ( is_user_logged_in() ) {
		$current_user = wp_get_current_user();
		if ( $current_user->user_login === 'demo_fundraiser' ) {
			echo '<style>
				#wp-admin-bar-demo-notice .ab-item {
					background: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%);
					color: #fff !important;
					font-weight: 600;
				}
				#wp-admin-bar-demo-notice:hover .ab-item {
					background: linear-gradient(135deg, #1d4ed8 0%, #6d28d9 100%);
				}
			</style>';
		}
	}
}
add_action( 'wp_head', 'fundraiser_blocks_demo_notice_styles' );
add_action( 'admin_head', 'fundraiser_blocks_demo_notice_styles' );

/**
 * Automatically create campaign page when a campaign is created/published
 */
function fundraiser_blocks_create_campaign_page( $post_id, $post, $update ) {
	// Only for fundraiser_campaign post type
	if ( $post->post_type !== 'fundraiser_campaign' ) {
		return;
	}

	// Skip autosaves and revisions
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( wp_is_post_revision( $post_id ) ) {
		return;
	}

	// Check if page already exists for this campaign
	$existing_page_id = get_post_meta( $post_id, '_campaign_page_id', true );

	if ( $existing_page_id && get_post( $existing_page_id ) ) {
		// Page already exists, just update the title if needed
		$page = get_post( $existing_page_id );
		if ( $page->post_title !== $post->post_title ) {
			wp_update_post( array(
				'ID' => $existing_page_id,
				'post_title' => $post->post_title,
			) );
		}
		return;
	}

	// Get starter template content
	$template_content = file_get_contents( get_template_directory() . '/starter-template-content.html' );

	// If template doesn't exist, use basic content
	if ( ! $template_content ) {
		$template_content = '<!-- wp:paragraph --><p>Welcome to this fundraising campaign! Edit this page to tell your story.</p><!-- /wp:paragraph -->';
	}

	// Create page for this campaign
	$page_data = array(
		'post_title'   => $post->post_title,
		'post_content' => $template_content,
		'post_status'  => 'publish',
		'post_author'  => $post->post_author,
		'post_type'    => 'page',
		'post_name'    => $post->post_name . '-campaign-page',
		'page_template' => 'page-campaign-template',
	);

	$page_id = wp_insert_post( $page_data );

	if ( $page_id && ! is_wp_error( $page_id ) ) {
		// Store page ID with campaign
		update_post_meta( $post_id, '_campaign_page_id', $page_id );

		// Store campaign ID with page
		update_post_meta( $page_id, '_campaign_id', $post_id );

		// Set page template
		update_post_meta( $page_id, '_wp_page_template', 'page-campaign-template' );

		// Create a product category for this campaign
		$category_slug = sanitize_title( $post->post_name ) . '-products';

		$term = term_exists( $category_slug, 'product_cat' );

		if ( ! $term ) {
			$term = wp_insert_term(
				$post->post_title . ' Products',
				'product_cat',
				array(
					'slug' => $category_slug,
					'description' => 'Products for ' . $post->post_title,
				)
			);
		}

		if ( ! is_wp_error( $term ) ) {
			$category_id = isset( $term['term_id'] ) ? $term['term_id'] : $term;

			// Store category ID with campaign
			update_post_meta( $post_id, '_campaign_product_category', $category_id );

			// Store category ID with page
			update_post_meta( $page_id, '_campaign_product_category', $category_id );
		}

		// Copy fundraising method selections to the page
		$enable_donations = get_post_meta( $post_id, '_enable_donations', true );
		$enable_products = get_post_meta( $post_id, '_enable_products', true );
		$enable_raffles = get_post_meta( $post_id, '_enable_raffles', true );

		update_post_meta( $page_id, '_enable_donations', $enable_donations );
		update_post_meta( $page_id, '_enable_products', $enable_products );
		update_post_meta( $page_id, '_enable_raffles', $enable_raffles );
	}
}
add_action( 'save_post', 'fundraiser_blocks_create_campaign_page', 10, 3 );

/**
 * Create dashboard page for new fundraiser users
 */
function fundraiser_blocks_create_user_dashboard( $user_id ) {
	$user = get_userdata( $user_id );

	// Only for fundraiser role
	if ( ! in_array( 'fundraiser', (array) $user->roles, true ) ) {
		return;
	}

	// Check if dashboard already exists
	$existing_dashboard = get_user_meta( $user_id, 'fundraiser_dashboard_page_id', true );

	if ( $existing_dashboard && get_post( $existing_dashboard ) ) {
		return;
	}

	// Create dashboard page
	$dashboard_content = '<!-- wp:heading --><h2>My Campaigns</h2><!-- /wp:heading --><!-- wp:shortcode -->[fundraiser_campaigns_list]<!-- /wp:shortcode -->';

	$page_data = array(
		'post_title'   => sanitize_text_field( $user->display_name ) . "'s Dashboard",
		'post_content' => $dashboard_content,
		'post_status'  => 'publish',
		'post_author'  => $user_id,
		'post_type'    => 'page',
		'post_name'    => sanitize_title( $user->user_login ) . '-dashboard',
	);

	$page_id = wp_insert_post( $page_data );

	if ( $page_id && ! is_wp_error( $page_id ) ) {
		update_user_meta( $user_id, 'fundraiser_dashboard_page_id', $page_id );
	}
}
add_action( 'user_register', 'fundraiser_blocks_create_user_dashboard', 10, 1 );

/**
 * Add meta boxes for fundraising method selection
 */
function fundraiser_blocks_add_campaign_meta_boxes() {
	add_meta_box(
		'fundraising_methods',
		'Fundraising Methods',
		'fundraiser_blocks_render_fundraising_methods_meta_box',
		'fundraiser_campaign',
		'side',
		'high'
	);
}
add_action( 'add_meta_boxes', 'fundraiser_blocks_add_campaign_meta_boxes' );

/**
 * Render fundraising methods meta box
 */
function fundraiser_blocks_render_fundraising_methods_meta_box( $post ) {
	wp_nonce_field( 'fundraising_methods_nonce', 'fundraising_methods_nonce_field' );

	$enable_donations = get_post_meta( $post->ID, '_enable_donations', true );
	$enable_products = get_post_meta( $post->ID, '_enable_products', true );
	$enable_raffles = get_post_meta( $post->ID, '_enable_raffles', true );

	// Default to all enabled for new campaigns
	if ( $enable_donations === '' ) {
		$enable_donations = '1';
	}
	if ( $enable_products === '' ) {
		$enable_products = '1';
	}
	if ( $enable_raffles === '' ) {
		$enable_raffles = '1';
	}
	?>
	<p>
		<label>
			<input type="checkbox" name="enable_donations" value="1" <?php checked( $enable_donations, '1' ); ?> />
			Enable Monetary Donations
		</label>
	</p>
	<p>
		<label>
			<input type="checkbox" name="enable_products" value="1" <?php checked( $enable_products, '1' ); ?> />
			Enable Product Sales
		</label>
	</p>
	<p>
		<label>
			<input type="checkbox" name="enable_raffles" value="1" <?php checked( $enable_raffles, '1' ); ?> />
			Enable Raffle Tickets
		</label>
	</p>
	<p class="description">Select which fundraising methods to use for this campaign. The campaign page will show only the selected sections.</p>
	<?php
}

/**
 * Save fundraising methods meta
 */
function fundraiser_blocks_save_fundraising_methods( $post_id ) {
	// Check nonce
	if ( ! isset( $_POST['fundraising_methods_nonce_field'] ) ||
	     ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['fundraising_methods_nonce_field'] ) ), 'fundraising_methods_nonce' ) ) {
		return;
	}

	// Check autosave
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	// Check permissions
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	// Save checkbox values
	$enable_donations = isset( $_POST['enable_donations'] ) ? '1' : '0';
	$enable_products = isset( $_POST['enable_products'] ) ? '1' : '0';
	$enable_raffles = isset( $_POST['enable_raffles'] ) ? '1' : '0';

	update_post_meta( $post_id, '_enable_donations', $enable_donations );
	update_post_meta( $post_id, '_enable_products', $enable_products );
	update_post_meta( $post_id, '_enable_raffles', $enable_raffles );

	// Also update the corresponding page if it exists
	$page_id = get_post_meta( $post_id, '_campaign_page_id', true );

	if ( $page_id ) {
		update_post_meta( $page_id, '_enable_donations', $enable_donations );
		update_post_meta( $page_id, '_enable_products', $enable_products );
		update_post_meta( $page_id, '_enable_raffles', $enable_raffles );
	}
}
add_action( 'save_post_fundraiser_campaign', 'fundraiser_blocks_save_fundraising_methods' );

/**
 * Allow fundraiser role to edit page slugs/URLs
 */
function fundraiser_blocks_add_fundraiser_capabilities() {
	$fundraiser_role = get_role( 'fundraiser' );
	
	if ( $fundraiser_role ) {
		// Add page editing capabilities
		$fundraiser_role->add_cap( 'edit_pages' );
		$fundraiser_role->add_cap( 'edit_published_pages' );
		$fundraiser_role->add_cap( 'publish_pages' );
		$fundraiser_role->add_cap( 'delete_published_pages' );
		
		// Prevent access to others' pages
		$fundraiser_role->remove_cap( 'edit_others_pages' );
		$fundraiser_role->remove_cap( 'delete_others_pages' );
	}
}
add_action( 'init', 'fundraiser_blocks_add_fundraiser_capabilities' );

/**
 * Restrict fundraisers to editing only their own pages
 */
function fundraiser_blocks_restrict_page_editing( $allcaps, $caps, $args, $user ) {
	// Only for fundraisers
	if ( ! in_array( 'fundraiser', (array) $user->roles, true ) ) {
		return $allcaps;
	}
	
	// Check if editing a post/page
	if ( isset( $args[0] ) && in_array( $args[0], array( 'edit_post', 'delete_post', 'edit_page', 'delete_page' ), true ) ) {
		if ( isset( $args[2] ) ) {
			$post = get_post( $args[2] );
			
			// If not their post, deny
			if ( $post && (int) $post->post_author !== (int) $user->ID ) {
				$allcaps[ $caps[0] ] = false;
			}
		}
	}
	
	return $allcaps;
}
add_filter( 'user_has_cap', 'fundraiser_blocks_restrict_page_editing', 10, 4 );

/**
 * Redirect fundraisers away from wp-admin dashboard
 */
function fundraiser_blocks_redirect_fundraisers_from_admin() {
	// Get current user
	$user = wp_get_current_user();
	
	// Only redirect fundraisers (not administrators)
	if ( in_array( 'fundraiser', (array) $user->roles, true ) && ! in_array( 'administrator', (array) $user->roles, true ) ) {
		// Allow AJAX requests
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return;
		}
		
		// Allow specific admin pages (like profile editing and campaign management)
		$allowed_pages = array( 'profile.php', 'admin-ajax.php', 'post-new.php', 'post.php', 'edit.php' );
		$current_page = basename( sanitize_text_field( wp_unslash( $_SERVER['PHP_SELF'] ?? '' ) ) );
		
		if ( in_array( $current_page, $allowed_pages, true ) ) {
			return;
		}
		
		// Redirect to custom fundraiser dashboard
		wp_safe_redirect( home_url( '/fundraiser-dashboard/' ) );
		exit;
	}
}
add_action( 'admin_init', 'fundraiser_blocks_redirect_fundraisers_from_admin' );

/**
 * Hide admin bar for fundraisers (except when in demo mode)
 */
function fundraiser_blocks_hide_admin_bar( $show ) {
	$user = wp_get_current_user();
	
	// Hide for fundraisers, but show for demo user
	if ( in_array( 'fundraiser', (array) $user->roles, true ) && $user->user_login !== 'demo_fundraiser' ) {
		return false;
	}
	
	return $show;
}
add_filter( 'show_admin_bar', 'fundraiser_blocks_hide_admin_bar' );

/**
 * Redirect after login based on user role
 */
function fundraiser_blocks_login_redirect( $redirect_to, $request, $user ) {
	// Check if user has roles
	if ( isset( $user->roles ) && is_array( $user->roles ) ) {
		// Redirect fundraisers to their dashboard
		if ( in_array( 'fundraiser', $user->roles, true ) ) {
			return home_url( '/fundraiser-dashboard/' );
		}
	}
	
	return $redirect_to;
}
add_filter( 'login_redirect', 'fundraiser_blocks_login_redirect', 10, 3 );

/**
 * Filter products in campaign template to show only the campaign's category
 */
function fundraiser_blocks_filter_campaign_products( $query_args, $attributes = null, $block = null ) {
	// Only filter product collections in campaign template pages
	if ( ! is_page() ) {
		return $query_args;
	}

	global $post;

	if ( ! $post ) {
		return $query_args;
	}

	// Check if this page uses the campaign template
	$page_template = get_post_meta( $post->ID, '_wp_page_template', true );

	if ( $page_template !== 'page-campaign-template' ) {
		return $query_args;
	}

	// Get the campaign's product category
	$category_id = get_post_meta( $post->ID, '_campaign_product_category', true );

	if ( ! $category_id ) {
		return $query_args;
	}

	// Add category filter to the query
	if ( ! isset( $query_args['tax_query'] ) ) {
		$query_args['tax_query'] = array();
	}

	$query_args['tax_query'][] = array(
		'taxonomy' => 'product_cat',
		'field'    => 'term_id',
		'terms'    => $category_id,
	);

	return $query_args;
}
add_filter( 'woocommerce_product_query_tax_query', 'fundraiser_blocks_filter_campaign_products', 10, 3 );

/**
 * Filter WooCommerce product query for block editor product collections
 */
function fundraiser_blocks_filter_product_collection_query( $query, $request, $server ) {
	// Only filter on campaign template pages
	if ( ! is_page() ) {
		return $query;
	}

	global $post;

	if ( ! $post ) {
		return $query;
	}

	// Check if this page uses the campaign template
	$page_template = get_post_meta( $post->ID, '_wp_page_template', true );

	if ( $page_template !== 'page-campaign-template' ) {
		return $query;
	}

	// Get the campaign's product category
	$category_id = get_post_meta( $post->ID, '_campaign_product_category', true );

	if ( ! $category_id ) {
		return $query;
	}

	// Add category filter to the query
	if ( ! isset( $query['tax_query'] ) ) {
		$query['tax_query'] = array();
	}

	$query['tax_query'][] = array(
		'taxonomy' => 'product_cat',
		'field'    => 'term_id',
		'terms'    => array( $category_id ),
	);

	return $query;
}
add_filter( 'woocommerce_rest_product_object_query', 'fundraiser_blocks_filter_product_collection_query', 10, 3 );

/**
 * Restrict fundraisers to only see products from their campaigns
 */
function fundraiser_blocks_restrict_fundraiser_products( $query ) {
	// Only in admin and for product queries
	if ( ! is_admin() || ! $query->is_main_query() ) {
		return;
	}

	// Only for fundraiser role
	$user = wp_get_current_user();
	if ( ! in_array( 'fundraiser', (array) $user->roles, true ) ) {
		return;
	}

	// Only for product post type
	if ( $query->get( 'post_type' ) !== 'product' ) {
		return;
	}

	// Get all campaigns for this user
	$campaigns = get_posts( array(
		'post_type' => 'fundraiser_campaign',
		'author' => $user->ID,
		'posts_per_page' => -1,
		'fields' => 'ids',
	) );

	if ( empty( $campaigns ) ) {
		// No campaigns = no products
		$query->set( 'post__in', array( 0 ) );
		return;
	}

	// Get all product categories for these campaigns
	$category_ids = array();
	foreach ( $campaigns as $campaign_id ) {
		$cat_id = get_post_meta( $campaign_id, '_campaign_product_category', true );
		if ( $cat_id ) {
			$category_ids[] = $cat_id;
		}
	}

	if ( empty( $category_ids ) ) {
		// No categories = no products
		$query->set( 'post__in', array( 0 ) );
		return;
	}

	// Filter by categories
	$tax_query = $query->get( 'tax_query' );

	if ( ! is_array( $tax_query ) ) {
		$tax_query = array();
	}

	$tax_query[] = array(
		'taxonomy' => 'product_cat',
		'field'    => 'term_id',
		'terms'    => $category_ids,
	);

	$query->set( 'tax_query', $tax_query );
}
add_action( 'pre_get_posts', 'fundraiser_blocks_restrict_fundraiser_products' );

/**
 * Restrict fundraisers to only edit products from their campaigns
 */
function fundraiser_blocks_restrict_product_editing( $allcaps, $caps, $args, $user ) {
	// Only for fundraisers
	if ( ! in_array( 'fundraiser', (array) $user->roles, true ) ) {
		return $allcaps;
	}

	// Check if editing a product
	if ( isset( $args[0] ) && in_array( $args[0], array( 'edit_post', 'delete_post' ), true ) ) {
		if ( isset( $args[2] ) ) {
			$post = get_post( $args[2] );

			// Only check products
			if ( $post && $post->post_type === 'product' ) {
				// Get all campaigns for this user
				$campaigns = get_posts( array(
					'post_type' => 'fundraiser_campaign',
					'author' => $user->ID,
					'posts_per_page' => -1,
					'fields' => 'ids',
				) );

				if ( empty( $campaigns ) ) {
					// No campaigns = can't edit any products
					$allcaps[ $caps[0] ] = false;
					return $allcaps;
				}

				// Get all product categories for these campaigns
				$user_category_ids = array();
				foreach ( $campaigns as $campaign_id ) {
					$cat_id = get_post_meta( $campaign_id, '_campaign_product_category', true );
					if ( $cat_id ) {
						$user_category_ids[] = (int) $cat_id;
					}
				}

				if ( empty( $user_category_ids ) ) {
					// No categories = can't edit any products
					$allcaps[ $caps[0] ] = false;
					return $allcaps;
				}

				// Get product categories
				$product_categories = wp_get_post_terms( $post->ID, 'product_cat', array( 'fields' => 'ids' ) );

				// Check if product is in any of the user's campaign categories
				$has_access = false;
				foreach ( $user_category_ids as $user_cat_id ) {
					if ( in_array( $user_cat_id, $product_categories, true ) ) {
						$has_access = true;
						break;
					}
				}

				if ( ! $has_access ) {
					$allcaps[ $caps[0] ] = false;
				}
			}
		}
	}

	return $allcaps;
}
add_filter( 'user_has_cap', 'fundraiser_blocks_restrict_product_editing', 10, 4 );

/**
 * Auto-assign campaign's product category to new products
 * Note: This requires a campaign context - products should be created from campaign admin
 */
function fundraiser_blocks_auto_assign_product_category( $post_id, $post, $update ) {
	// Only for new products (not updates)
	if ( $update ) {
		return;
	}

	// Only for products
	if ( $post->post_type !== 'product' ) {
		return;
	}

	// Only for fundraisers
	$user = wp_get_current_user();
	if ( ! in_array( 'fundraiser', (array) $user->roles, true ) ) {
		return;
	}

	// Check if a campaign_id was passed (from campaign admin interface)
	$campaign_id = isset( $_POST['campaign_id'] ) ? intval( $_POST['campaign_id'] ) : 0;

	if ( ! $campaign_id ) {
		// Try to get the most recent campaign for this user
		$campaigns = get_posts( array(
			'post_type' => 'fundraiser_campaign',
			'author' => $user->ID,
			'posts_per_page' => 1,
			'orderby' => 'date',
			'order' => 'DESC',
			'fields' => 'ids',
		) );

		$campaign_id = ! empty( $campaigns ) ? $campaigns[0] : 0;
	}

	if ( ! $campaign_id ) {
		return;
	}

	// Get the campaign's category
	$category_id = get_post_meta( $campaign_id, '_campaign_product_category', true );

	if ( ! $category_id ) {
		return;
	}

	// Assign the category to the product
	wp_set_post_terms( $post_id, array( (int) $category_id ), 'product_cat', false );
}
add_action( 'wp_insert_post', 'fundraiser_blocks_auto_assign_product_category', 10, 3 );

/**
 * Shortcode to list user's campaigns
 */
function fundraiser_blocks_campaigns_list_shortcode( $atts ) {
	if ( ! is_user_logged_in() ) {
		return '<p>Please log in to view your campaigns.</p>';
	}

	$user_id = get_current_user_id();

	$campaigns = get_posts( array(
		'post_type' => 'fundraiser_campaign',
		'author' => $user_id,
		'posts_per_page' => -1,
		'orderby' => 'date',
		'order' => 'DESC',
	) );

	if ( empty( $campaigns ) ) {
		return '<p>You don\'t have any campaigns yet. <a href="' . esc_url( home_url( '/campaign-wizard/' ) ) . '">Create your first campaign</a>.</p>';
	}

	$output = '<div class="fundraiser-campaigns-list">';

	foreach ( $campaigns as $campaign ) {
		$page_id = get_post_meta( $campaign->ID, '_campaign_page_id', true );
		$page_url = $page_id ? get_permalink( $page_id ) : '#';
		$edit_url = home_url( '/campaign-detail/?campaign_id=' . $campaign->ID );

		$enable_donations = get_post_meta( $campaign->ID, 'fundraiser_donations_enabled', true );
		$enable_products = get_post_meta( $campaign->ID, 'fundraiser_products_enabled', true );
		$enable_raffles = get_post_meta( $campaign->ID, 'fundraiser_raffles_enabled', true );

		$methods = array();
		if ( $enable_donations === '1' ) {
			$methods[] = 'Donations';
		}
		if ( $enable_products === '1' ) {
			$methods[] = 'Products';
		}
		if ( $enable_raffles === '1' ) {
			$methods[] = 'Raffles';
		}

		$methods_str = ! empty( $methods ) ? implode( ', ', $methods ) : 'None enabled';

		$output .= '<div class="campaign-item" style="border: 1px solid #ddd; padding: 1.5rem; margin-bottom: 1rem; border-radius: 8px;">';
		$output .= '<h3 style="margin-top: 0;"><a href="' . esc_url( $page_url ) . '">' . esc_html( $campaign->post_title ) . '</a></h3>';
		$output .= '<p><strong>Methods:</strong> ' . esc_html( $methods_str ) . '</p>';
		$output .= '<p>';
		$output .= '<a href="' . esc_url( $page_url ) . '" class="button">View Campaign Page</a> ';
		$output .= '<a href="' . esc_url( $edit_url ) . '" class="button">Edit Campaign</a>';
		$output .= '</p>';
		$output .= '</div>';
	}

	$output .= '</div>';
	$output .= '<p><a href="' . esc_url( home_url( '/campaign-wizard/' ) ) . '" class="button button-primary">Create New Campaign</a></p>';

	return $output;
}
add_shortcode( 'fundraiser_campaigns_list', 'fundraiser_blocks_campaigns_list_shortcode' );

/**
 * Add product editing capabilities to fundraiser role
 */
function fundraiser_blocks_add_product_capabilities() {
	$fundraiser_role = get_role( 'fundraiser' );

	if ( $fundraiser_role ) {
		// Add product editing capabilities
		$fundraiser_role->add_cap( 'edit_products' );
		$fundraiser_role->add_cap( 'edit_published_products' );
		$fundraiser_role->add_cap( 'publish_products' );
		$fundraiser_role->add_cap( 'delete_published_products' );
		$fundraiser_role->add_cap( 'upload_files' );

		// Prevent access to others' products
		$fundraiser_role->remove_cap( 'edit_others_products' );
		$fundraiser_role->remove_cap( 'delete_others_products' );
	}
}
add_action( 'init', 'fundraiser_blocks_add_product_capabilities' );

// ==================== FUNDRAISER DASHBOARD SYSTEM ====================

/**
 * Enqueue assets for fundraiser dashboard
 */
function fundraiser_dashboard_enqueue_assets() {
	$dashboard_pages = array('fundraiser-dashboard', 'my-campaigns', 'campaign-detail', 'campaign-wizard', 'product-request', 'manual-entry', 'my-reports');
	
	if (!is_page($dashboard_pages)) {
		return;
	}

	// Enqueue Vue.js 3
	wp_enqueue_script('vue', 'https://unpkg.com/vue@3/dist/vue.global.js', array(), '3.0', true);
	
	// Enqueue Chart.js for analytics
	if (is_page(array('fundraiser-dashboard', 'my-reports', 'campaign-detail'))) {
		wp_enqueue_script('chartjs', 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js', array(), '4.4.0', true);
	}
	
	// Enqueue media library
	wp_enqueue_media();
	
	// Localize script for AJAX
	wp_localize_script('jquery', 'fundraiserData', array(
		'nonce' => wp_create_nonce('wp_rest'),
		'apiUrl' => rest_url('fundraiser-pro/v1/'),
		'userId' => get_current_user_id(),
		'homeUrl' => home_url(),
	));
}
add_action('wp_enqueue_scripts', 'fundraiser_dashboard_enqueue_assets');

/**
 * Render dashboard navigation menu
 */
function fundraiser_dashboard_menu() {
	$current_url = home_url($_SERVER['REQUEST_URI']);
	
	$menu_items = array(
		array('url' => home_url('/fundraiser-dashboard/'), 'label' => 'Dashboard', 'icon' => '📊'),
		array('url' => home_url('/my-campaigns/'), 'label' => 'Campaigns', 'icon' => '📋'),
		array('url' => home_url('/product-request/'), 'label' => 'Request Products', 'icon' => '🎨'),
		array('url' => home_url('/manual-entry/'), 'label' => 'Manual Entry', 'icon' => '✍️'),
		array('url' => home_url('/my-reports/'), 'label' => 'Reports', 'icon' => '📈'),
	);
	
	$output = '<nav class="fundraiser-dashboard-nav" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 1rem 2rem; margin: -2rem -2rem 2rem -2rem; border-radius: 8px 8px 0 0;">';
	$output .= '<ul style="list-style: none; margin: 0; padding: 0; display: flex; gap: 1.5rem; flex-wrap: wrap;">';
	
	foreach ($menu_items as $item) {
		$is_active = (strpos($current_url, $item['url']) !== false);
		$active_style = $is_active ? 'background: rgba(255,255,255,0.3); border-bottom: 3px solid white;' : '';
		
		$output .= '<li style="margin: 0;">';
		$output .= '<a href="' . esc_url($item['url']) . '" style="color: white; text-decoration: none; padding: 0.75rem 1.25rem; display: inline-block; border-radius: 6px; transition: all 0.3s; ' . $active_style . '">';
		$output .= '<span style="font-size: 1.2rem; margin-right: 0.5rem;">' . $item['icon'] . '</span>';
		$output .= '<span style="font-weight: 600;">' . esc_html($item['label']) . '</span>';
		$output .= '</a>';
		$output .= '</li>';
	}
	
	// Add logout link
	$output .= '<li style="margin-left: auto;">';
	$output .= '<a href="' . esc_url(wp_logout_url(home_url())) . '" style="color: white; text-decoration: none; padding: 0.75rem 1.25rem; display: inline-block; border-radius: 6px; transition: all 0.3s;">';
	$output .= '<span style="font-size: 1.2rem; margin-right: 0.5rem;">🚪</span>';
	$output .= '<span style="font-weight: 600;">Logout</span>';
	$output .= '</a>';
	$output .= '</li>';
	
	$output .= '</ul>';
	$output .= '</nav>';
	
	return $output;
}

/**
 * Shortcode: Fundraiser Dashboard Overview
 */
function fundraiser_dashboard_overview_shortcode($atts) {
	if (!is_user_logged_in()) {
		return '<p>Please log in to view your dashboard.</p>';
	}
	
	$user_id = get_current_user_id();
	global $wpdb;
	
	// Get user's campaigns count
	$campaigns_count = count(get_posts(array(
		'post_type' => 'fundraiser_campaign',
		'author' => $user_id,
		'post_status' => array('publish', 'draft', 'pending'),
		'posts_per_page' => -1,
	)));
	
	// Get total raised across all campaigns
	$campaigns = get_posts(array(
		'post_type' => 'fundraiser_campaign',
		'author' => $user_id,
		'post_status' => 'publish',
		'posts_per_page' => -1,
		'fields' => 'ids',
	));
	
	$total_raised = 0;
	foreach ($campaigns as $campaign_id) {
		$analytics = $wpdb->get_row($wpdb->prepare(
			"SELECT (donations_total + raffle_sales_total) as total_raised FROM {$wpdb->prefix}fundraiser_campaign_analytics WHERE campaign_id = %d ORDER BY date DESC LIMIT 1",
			$campaign_id
		));
		if ($analytics) {
			$total_raised += floatval($analytics->total_raised);
		}
	}
	
	// Get active raffles count from custom table
	$raffles_count = $wpdb->get_var($wpdb->prepare(
		"SELECT COUNT(*) FROM {$wpdb->prefix}fundraiser_raffles r
		INNER JOIN {$wpdb->prefix}posts p ON r.campaign_id = p.ID
		WHERE p.post_author = %d AND r.status = 'active'",
		$user_id
	));
	
	// Get pending cash transactions count
	$pending_cash = count(get_posts(array(
		'post_type' => 'fundraiser_cash',
		'author' => $user_id,
		'post_status' => 'pending',
		'posts_per_page' => -1,
	)));
	
	$output = '';
	
	// Navigation menu
	$output .= fundraiser_dashboard_menu();
	
	// Welcome message
	$user_info = get_userdata($user_id);
	$output .= '<div style="margin-bottom: 2rem;">';
	$output .= '<h1 style="margin: 0 0 0.5rem 0; font-size: 2rem;">Welcome back, ' . esc_html($user_info->display_name) . '!</h1>';
	$output .= '<p style="color: #666; font-size: 1.1rem;">Here\'s what\'s happening with your fundraising campaigns.</p>';
	$output .= '</div>';
	
	// Statistics cards
	$output .= '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">';
	
	// Total Campaigns Card
	$output .= '<div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">';
	$output .= '<div style="font-size: 2.5rem; margin-bottom: 0.5rem;">📋</div>';
	$output .= '<div style="font-size: 2rem; font-weight: bold; margin-bottom: 0.25rem;">' . $campaigns_count . '</div>';
	$output .= '<div style="font-size: 0.9rem; opacity: 0.9;">Total Campaigns</div>';
	$output .= '</div>';
	
	// Total Raised Card
	$output .= '<div style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">';
	$output .= '<div style="font-size: 2.5rem; margin-bottom: 0.5rem;">💰</div>';
	$output .= '<div style="font-size: 2rem; font-weight: bold; margin-bottom: 0.25rem;">$' . number_format($total_raised, 2) . '</div>';
	$output .= '<div style="font-size: 0.9rem; opacity: 0.9;">Total Raised</div>';
	$output .= '</div>';
	
	// Active Raffles Card
	$output .= '<div style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">';
	$output .= '<div style="font-size: 2.5rem; margin-bottom: 0.5rem;">🎟️</div>';
	$output .= '<div style="font-size: 2rem; font-weight: bold; margin-bottom: 0.25rem;">' . $raffles_count . '</div>';
	$output .= '<div style="font-size: 0.9rem; opacity: 0.9;">Active Raffles</div>';
	$output .= '</div>';
	
	// Pending Cash Card
	$output .= '<div style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); color: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">';
	$output .= '<div style="font-size: 2.5rem; margin-bottom: 0.5rem;">⏳</div>';
	$output .= '<div style="font-size: 2rem; font-weight: bold; margin-bottom: 0.25rem;">' . $pending_cash . '</div>';
	$output .= '<div style="font-size: 0.9rem; opacity: 0.9;">Pending Approvals</div>';
	$output .= '</div>';
	
	$output .= '</div>';
	
	// Quick Actions
	$output .= '<div style="background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 2rem;">';
	$output .= '<h2 style="margin: 0 0 1.5rem 0; font-size: 1.5rem;">Quick Actions</h2>';
	$output .= '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">';
	
	$actions = array(
		array('url' => home_url('/campaign-wizard/'), 'label' => 'New Campaign', 'icon' => '➕', 'color' => '#667eea'),
		array('url' => home_url('/raffle-management/'), 'label' => 'Start Raffle', 'icon' => '🎟️', 'color' => '#4facfe'),
		array('url' => home_url('/product-request/'), 'label' => 'Request Products', 'icon' => '🎨', 'color' => '#f093fb'),
		array('url' => home_url('/my-campaigns/'), 'label' => 'View Campaigns', 'icon' => '📋', 'color' => '#00d4aa'),
		array('url' => home_url('/manual-entry/'), 'label' => 'Manual Entry', 'icon' => '✍️', 'color' => '#fa709a'),
	);
	
	foreach ($actions as $action) {
		$output .= '<a href="' . esc_url($action['url']) . '" style="display: block; text-align: center; padding: 1.5rem; background: ' . $action['color'] . '; color: white; text-decoration: none; border-radius: 8px; transition: transform 0.2s; box-shadow: 0 2px 4px rgba(0,0,0,0.1);" onmouseover="this.style.transform=\'translateY(-3px)\'" onmouseout="this.style.transform=\'translateY(0)\'">';
		$output .= '<div style="font-size: 2rem; margin-bottom: 0.5rem;">' . $action['icon'] . '</div>';
		$output .= '<div style="font-weight: 600;">' . esc_html($action['label']) . '</div>';
		$output .= '</a>';
	}
	
	$output .= '</div>';
	$output .= '</div>';
	
	// Recent Campaigns Section
	$output .= '<div style="background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">';
	$output .= '<h2 style="margin: 0 0 1.5rem 0; font-size: 1.5rem;">Your Campaigns</h2>';
	$output .= do_shortcode('[fundraiser_campaigns_list]');
	$output .= '</div>';
	
	return $output;
}
add_shortcode('fundraiser_dashboard_overview', 'fundraiser_dashboard_overview_shortcode');

/**
 * Complete admin lockout for fundraisers
 */
function fundraiser_complete_admin_lockout() {
	$user = wp_get_current_user();
	
	// Allow AJAX requests
	if (defined('DOING_AJAX') && DOING_AJAX) {
		return;
	}
	
	// Check if user is fundraiser
	if (in_array('fundraiser', $user->roles) && is_admin()) {
		// Redirect to fundraiser dashboard instead of wp-admin
		wp_safe_redirect(home_url('/fundraiser-dashboard/'));
		exit;
	}
}
add_action('admin_init', 'fundraiser_complete_admin_lockout', 1);

/**
 * Remove admin bar for fundraisers
 */
function fundraiser_remove_admin_bar() {
	if (in_array('fundraiser', wp_get_current_user()->roles)) {
		show_admin_bar(false);
	}
}
add_action('wp_loaded', 'fundraiser_remove_admin_bar');

/**
 * Redirect fundraisers after login
 */
function fundraiser_login_redirect($redirect_to, $request, $user) {
	if (isset($user->roles) && is_array($user->roles)) {
		if (in_array('fundraiser', $user->roles)) {
			return home_url('/fundraiser-dashboard/');
		}
	}
	return $redirect_to;
}
add_filter('login_redirect', 'fundraiser_login_redirect', 10, 3);


// ==================== CAMPAIGN MANAGEMENT SHORTCODES ====================

/**
 * Shortcode: Campaign Manager (list all campaigns)
 */
function fundraiser_campaigns_manager_shortcode($atts) {
	if (!is_user_logged_in()) {
		return '<p>Please log in to manage your campaigns.</p>';
	}
	
	$user_id = get_current_user_id();
	$filter = isset($_GET['filter']) ? sanitize_text_field($_GET['filter']) : 'all';
	
	// Get campaigns based on filter
	$args = array(
		'post_type' => 'fundraiser_campaign',
		'author' => $user_id,
		'posts_per_page' => -1,
		'orderby' => 'date',
		'order' => 'DESC',
	);
	
	if ($filter === 'active') {
		$args['post_status'] = 'publish';
	} elseif ($filter === 'draft') {
		$args['post_status'] = 'draft';
	} else {
		$args['post_status'] = array('publish', 'draft', 'pending');
	}
	
	$campaigns = get_posts($args);
	global $wpdb;
	
	$output = '';
	
	// Navigation menu
	$output .= fundraiser_dashboard_menu();
	
	// Page header
	$output .= '<div style="margin-bottom: 2rem;">';
	$output .= '<h1 style="margin: 0 0 0.5rem 0; font-size: 2rem;">My Campaigns</h1>';
	$output .= '<p style="color: #666; font-size: 1.1rem;">Manage all your fundraising campaigns.</p>';
	$output .= '</div>';
	
	// Filter buttons
	$output .= '<div style="margin-bottom: 2rem;">';
	$output .= '<div style="display: inline-flex; gap: 0.5rem; background: #f5f5f5; padding: 0.5rem; border-radius: 8px;">';
	
	$filters = array(
		'all' => 'All Campaigns',
		'active' => 'Active',
		'draft' => 'Drafts',
	);
	
	foreach ($filters as $key => $label) {
		$active = ($filter === $key) ? 'background: white; box-shadow: 0 2px 4px rgba(0,0,0,0.1);' : '';
		$output .= '<a href="?' . http_build_query(array('filter' => $key)) . '" style="padding: 0.75rem 1.5rem; text-decoration: none; color: #333; border-radius: 6px; transition: all 0.2s; ' . $active . '">' . esc_html($label) . '</a>';
	}
	
	$output .= '</div>';
	$output .= '<a href="' . esc_url(home_url('/campaign-wizard/')) . '" style="float: right; padding: 0.75rem 1.5rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; text-decoration: none; border-radius: 6px; font-weight: 600;">+ Create New Campaign</a>';
	$output .= '<div style="clear: both;"></div>';
	$output .= '</div>';
	
	// Campaigns grid
	if (empty($campaigns)) {
		$output .= '<div style="text-align: center; padding: 4rem 2rem; background: white; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">';
		$output .= '<div style="font-size: 4rem; margin-bottom: 1rem;">📋</div>';
		$output .= '<h3 style="margin: 0 0 1rem 0;">No campaigns found</h3>';
		$output .= '<p style="color: #666; margin-bottom: 2rem;">Get started by creating your first fundraising campaign.</p>';
		$output .= '<a href="' . esc_url(home_url('/campaign-wizard/')) . '" style="display: inline-block; padding: 1rem 2rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; text-decoration: none; border-radius: 8px; font-weight: 600;">Create First Campaign</a>';
		$output .= '</div>';
	} else {
		$output .= '<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1.5rem;">';
		
		foreach ($campaigns as $campaign) {
			$campaign_id = $campaign->ID;
			$goal = floatval(get_post_meta($campaign_id, 'fundraiser_goal', true));
			
			// Get analytics
			$analytics = $wpdb->get_row($wpdb->prepare(
				"SELECT total_raised FROM {$wpdb->prefix}fundraiser_campaign_analytics WHERE campaign_id = %d",
				$campaign_id
			));
			
			$total_raised = $analytics ? floatval($analytics->total_raised) : 0;
			$progress = $goal > 0 ? min(100, ($total_raised / $goal) * 100) : 0;
			
			// Get methods enabled
			$donations_enabled = get_post_meta($campaign_id, 'fundraiser_donations_enabled', true);
			$products_enabled = get_post_meta($campaign_id, 'fundraiser_products_enabled', true);
			$raffles_enabled = get_post_meta($campaign_id, 'fundraiser_raffles_enabled', true);
			
			$methods = array();
			if ($donations_enabled) $methods[] = '💰 Donations';
			if ($products_enabled) $methods[] = '🛍️ Products';
			if ($raffles_enabled) $methods[] = '🎟️ Raffles';
			
			$page_id = get_post_meta($campaign_id, '_campaign_page_id', true);
			$page_url = $page_id ? get_permalink($page_id) : '#';
			
			// Campaign card
			$output .= '<div style="background: white; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); overflow: hidden; transition: transform 0.2s;" onmouseover="this.style.transform=\'translateY(-4px)\'" onmouseout="this.style.transform=\'translateY(0)\'">';
			
			// Status badge
			$status_color = ($campaign->post_status === 'publish') ? '#10b981' : '#f59e0b';
			$status_label = ($campaign->post_status === 'publish') ? 'Active' : ucfirst($campaign->post_status);
			$output .= '<div style="padding: 1rem 1.5rem; background: ' . $status_color . '; color: white; font-size: 0.875rem; font-weight: 600;">' . $status_label . '</div>';
			
			$output .= '<div style="padding: 1.5rem;">';
			$output .= '<h3 style="margin: 0 0 1rem 0; font-size: 1.25rem;"><a href="' . esc_url($page_url) . '" style="color: #333; text-decoration: none;">' . esc_html($campaign->post_title) . '</a></h3>';
			
			// Progress bar
			$output .= '<div style="margin-bottom: 1rem;">';
			$output .= '<div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem; font-size: 0.875rem;">';
			$output .= '<span style="font-weight: 600;">$' . number_format($total_raised, 0) . ' raised</span>';
			$output .= '<span style="color: #666;">of $' . number_format($goal, 0) . '</span>';
			$output .= '</div>';
			$output .= '<div style="height: 8px; background: #e5e7eb; border-radius: 4px; overflow: hidden;">';
			$output .= '<div style="height: 100%; background: linear-gradient(90deg, #667eea 0%, #764ba2 100%); width: ' . $progress . '%;"></div>';
			$output .= '</div>';
			$output .= '<div style="text-align: right; font-size: 0.875rem; color: #666; margin-top: 0.25rem;">' . round($progress, 1) . '%</div>';
			$output .= '</div>';
			
			// Methods enabled
			if (!empty($methods)) {
				$output .= '<div style="margin-bottom: 1rem; font-size: 0.875rem; color: #666;">';
				$output .= implode(' • ', $methods);
				$output .= '</div>';
			}
			
			// Action buttons
			$output .= '<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">';
			$output .= '<a href="' . esc_url(home_url('/campaign-detail/?campaign_id=' . $campaign_id)) . '" style="padding: 0.75rem; text-align: center; background: #667eea; color: white; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 0.875rem;">Manage</a>';
			$output .= '<a href="' . esc_url($page_url) . '" style="padding: 0.75rem; text-align: center; background: #e5e7eb; color: #333; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 0.875rem;" target="_blank">View Page</a>';
			$output .= '</div>';
			
			$output .= '</div>';
			$output .= '</div>';
		}
		
		$output .= '</div>';
	}
	
	return $output;
}
add_shortcode('fundraiser_campaigns_manager', 'fundraiser_campaigns_manager_shortcode');

/**
 * Shortcode: Campaign Detail Tabs
 */
function campaign_detail_tabs_shortcode($atts) {
	if (!is_user_logged_in()) {
		return '<p>Please log in to view campaign details.</p>';
	}
	
	$campaign_id = isset($_GET['campaign_id']) ? intval($_GET['campaign_id']) : 0;
	$tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'overview';
	
	if (!$campaign_id) {
		return '<p>No campaign specified. <a href="' . esc_url(home_url('/my-campaigns/')) . '">Go to My Campaigns</a></p>';
	}
	
	$campaign = get_post($campaign_id);
	
	if (!$campaign || $campaign->post_type !== 'fundraiser_campaign') {
		return '<p>Campaign not found.</p>';
	}
	
	// Verify ownership
	if ($campaign->post_author != get_current_user_id() && !current_user_can('manage_options')) {
		return '<p>You do not have permission to view this campaign.</p>';
	}
	
	$output = '';
	
	// Navigation menu
	$output .= fundraiser_dashboard_menu();
	
	// Campaign header
	$output .= '<div style="background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 2rem;">';
	$output .= '<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem;">';
	$output .= '<h1 style="margin: 0; font-size: 2rem;">' . esc_html($campaign->post_title) . '</h1>';
	
	$page_id = get_post_meta($campaign_id, '_campaign_page_id', true);
	if ($page_id) {
		$page_url = get_permalink($page_id);
		$output .= '<a href="' . esc_url($page_url) . '" style="padding: 0.75rem 1.5rem; background: #667eea; color: white; text-decoration: none; border-radius: 6px; font-weight: 600;" target="_blank">View Public Page</a>';
	}
	
	$output .= '</div>';
	
	// Tabs
	$tabs = array(
		'overview' => array('label' => 'Overview', 'icon' => '📊'),
		'settings' => array('label' => 'Settings', 'icon' => '⚙️'),
		'products' => array('label' => 'Products', 'icon' => '🛍️'),
		'raffles' => array('label' => 'Raffles', 'icon' => '🎟️'),
		'page-editor' => array('label' => 'Page Editor', 'icon' => '📝'),
		'manual-entry' => array('label' => 'Manual Entry', 'icon' => '✍️'),
		'reports' => array('label' => 'Reports', 'icon' => '📈'),
	);
	
	$output .= '<div style="border-bottom: 2px solid #e5e7eb;">';
	$output .= '<div style="display: flex; gap: 0.5rem; overflow-x: auto; flex-wrap: wrap;">';
	
	foreach ($tabs as $key => $info) {
		$active = ($tab === $key) ? 'border-bottom: 3px solid #667eea; color: #667eea; margin-bottom: -2px;' : 'color: #666;';
		$output .= '<a href="?' . http_build_query(array('campaign_id' => $campaign_id, 'tab' => $key)) . '" style="padding: 1rem 1.5rem; text-decoration: none; font-weight: 600; transition: all 0.2s; ' . $active . '">';
		$output .= '<span style="margin-right: 0.5rem;">' . $info['icon'] . '</span>';
		$output .= $info['label'];
		$output .= '</a>';
	}
	
	$output .= '</div>';
	$output .= '</div>';
	$output .= '</div>';
	
	// Tab content
	$output .= '<div style="background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">';
	
	switch ($tab) {
		case 'overview':
			$output .= do_shortcode('[campaign_overview_tab campaign_id="' . $campaign_id . '"]');
			break;
		case 'settings':
			$output .= do_shortcode('[campaign_settings_tab campaign_id="' . $campaign_id . '"]');
			break;
		case 'products':
			$output .= do_shortcode('[campaign_products_tab campaign_id="' . $campaign_id . '"]');
			break;
		case 'raffles':
			$output .= do_shortcode('[campaign_raffles_tab campaign_id="' . $campaign_id . '"]');
			break;
		case 'page-editor':
			$output .= do_shortcode('[campaign_page_editor_tab campaign_id="' . $campaign_id . '"]');
			break;
		case 'manual-entry':
			$output .= do_shortcode('[campaign_manual_entry_tab campaign_id="' . $campaign_id . '"]');
			break;
		case 'reports':
			$output .= do_shortcode('[campaign_reports_tab campaign_id="' . $campaign_id . '"]');
			break;
		default:
			$output .= '<p>Tab content coming soon...</p>';
	}
	
	$output .= '</div>';
	
	return $output;
}
add_shortcode('campaign_detail_tabs', 'campaign_detail_tabs_shortcode');

/**
 * Shortcode: Campaign Overview Tab
 */
function campaign_overview_tab_shortcode($atts) {
	$atts = shortcode_atts(array('campaign_id' => 0), $atts);
	$campaign_id = intval($atts['campaign_id']);
	
	if (!$campaign_id) {
		return '<p>No campaign specified.</p>';
	}
	
	global $wpdb;
	$campaign = get_post($campaign_id);
	$goal = floatval(get_post_meta($campaign_id, 'fundraiser_goal', true));
	
	// Get analytics
	$analytics = $wpdb->get_row($wpdb->prepare(
		"SELECT * FROM {$wpdb->prefix}fundraiser_campaign_analytics WHERE campaign_id = %d ORDER BY date DESC LIMIT 1",
		$campaign_id
	));

	$donation_revenue = $analytics ? floatval($analytics->donations_total) : 0;
	$product_revenue = 0; // Products not implemented yet
	$raffle_revenue = $analytics ? floatval($analytics->raffle_sales_total) : 0;
	$total_raised = $donation_revenue + $product_revenue + $raffle_revenue;
	$total_donors = $analytics ? intval($analytics->unique_donors) : 0;
	
	$progress = $goal > 0 ? min(100, ($total_raised / $goal) * 100) : 0;
	
	$output = '<h2 style="margin: 0 0 1.5rem 0;">Campaign Overview</h2>';
	
	// Statistics cards
	$output .= '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">';
	
	// Total Raised
	$output .= '<div style="padding: 1.5rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 8px;">';
	$output .= '<div style="font-size: 0.875rem; opacity: 0.9; margin-bottom: 0.5rem;">Total Raised</div>';
	$output .= '<div style="font-size: 2rem; font-weight: bold;">$' . number_format($total_raised, 2) . '</div>';
	$output .= '</div>';
	
	// Goal
	$output .= '<div style="padding: 1.5rem; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; border-radius: 8px;">';
	$output .= '<div style="font-size: 0.875rem; opacity: 0.9; margin-bottom: 0.5rem;">Goal</div>';
	$output .= '<div style="font-size: 2rem; font-weight: bold;">$' . number_format($goal, 2) . '</div>';
	$output .= '</div>';
	
	// Progress
	$output .= '<div style="padding: 1.5rem; background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; border-radius: 8px;">';
	$output .= '<div style="font-size: 0.875rem; opacity: 0.9; margin-bottom: 0.5rem;">Progress</div>';
	$output .= '<div style="font-size: 2rem; font-weight: bold;">' . round($progress, 1) . '%</div>';
	$output .= '</div>';
	
	// Total Donors
	$output .= '<div style="padding: 1.5rem; background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); color: white; border-radius: 8px;">';
	$output .= '<div style="font-size: 0.875rem; opacity: 0.9; margin-bottom: 0.5rem;">Total Donors</div>';
	$output .= '<div style="font-size: 2rem; font-weight: bold;">' . $total_donors . '</div>';
	$output .= '</div>';
	
	$output .= '</div>';
	
	// Revenue breakdown
	$output .= '<h3 style="margin: 2rem 0 1rem 0;">Revenue Breakdown</h3>';
	$output .= '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem;">';
	
	$output .= '<div style="padding: 1rem; border: 2px solid #667eea; border-radius: 8px;">';
	$output .= '<div style="font-size: 0.875rem; color: #666; margin-bottom: 0.25rem;">Donations</div>';
	$output .= '<div style="font-size: 1.5rem; font-weight: bold; color: #667eea;">$' . number_format($donation_revenue, 2) . '</div>';
	$output .= '</div>';
	
	$output .= '<div style="padding: 1rem; border: 2px solid #f093fb; border-radius: 8px;">';
	$output .= '<div style="font-size: 0.875rem; color: #666; margin-bottom: 0.25rem;">Products</div>';
	$output .= '<div style="font-size: 1.5rem; font-weight: bold; color: #f093fb;">$' . number_format($product_revenue, 2) . '</div>';
	$output .= '</div>';
	
	$output .= '<div style="padding: 1rem; border: 2px solid #4facfe; border-radius: 8px;">';
	$output .= '<div style="font-size: 0.875rem; color: #666; margin-bottom: 0.25rem;">Raffles</div>';
	$output .= '<div style="font-size: 1.5rem; font-weight: bold; color: #4facfe;">$' . number_format($raffle_revenue, 2) . '</div>';
	$output .= '</div>';
	
	$output .= '</div>';
	
	// Campaign description
	if (!empty($campaign->post_content)) {
		$output .= '<h3 style="margin: 2rem 0 1rem 0;">Description</h3>';
		$output .= '<div style="padding: 1.5rem; background: #f9fafb; border-radius: 8px; line-height: 1.6;">';
		$output .= wp_kses_post($campaign->post_content);
		$output .= '</div>';
	}
	
	return $output;
}
add_shortcode('campaign_overview_tab', 'campaign_overview_tab_shortcode');


/**
 * Shortcode: Campaign Settings Tab
 */
function campaign_settings_tab_shortcode($atts) {
	$atts = shortcode_atts(array('campaign_id' => 0), $atts);
	$campaign_id = intval($atts['campaign_id']);
	
	if (!$campaign_id) {
		return '<p>No campaign specified.</p>';
	}
	
	$campaign = get_post($campaign_id);
	$goal = get_post_meta($campaign_id, 'fundraiser_goal', true);
	$duration = get_post_meta($campaign_id, 'fundraiser_duration', true);
	$donations_enabled = get_post_meta($campaign_id, 'fundraiser_donations_enabled', true);
	$products_enabled = get_post_meta($campaign_id, 'fundraiser_products_enabled', true);
	$raffles_enabled = get_post_meta($campaign_id, 'fundraiser_raffles_enabled', true);
	$video_url = get_post_meta($campaign_id, 'fundraiser_video_url', true);
	
	$output = '<h2 style="margin: 0 0 1.5rem 0;">Campaign Settings</h2>';
	
	$output .= '<div id="campaign-settings-app">';
	$output .= '<form id="campaign-settings-form" style="max-width: 800px;">';
	
	// Basic Information Section
	$output .= '<div style="background: #f9fafb; padding: 1.5rem; border-radius: 8px; margin-bottom: 1.5rem;">';
	$output .= '<h3 style="margin: 0 0 1rem 0; font-size: 1.25rem;">Basic Information</h3>';
	
	$output .= '<div style="margin-bottom: 1.5rem;">';
	$output .= '<label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Campaign Title</label>';
	$output .= '<input type="text" v-model="settings.title" style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 1rem;" placeholder="Enter campaign title">';
	$output .= '</div>';
	
	$output .= '<div style="margin-bottom: 1.5rem;">';
	$output .= '<label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Description</label>';
	$output .= '<textarea v-model="settings.description" rows="5" style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 1rem; resize: vertical;" placeholder="Describe your campaign..."></textarea>';
	$output .= '</div>';
	
	$output .= '<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">';
	$output .= '<div>';
	$output .= '<label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Fundraising Goal ($)</label>';
	$output .= '<input type="number" v-model="settings.goal" step="0.01" min="0" style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 1rem;" placeholder="10000">';
	$output .= '</div>';
	$output .= '<div>';
	$output .= '<label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Duration (days)</label>';
	$output .= '<input type="number" v-model="settings.duration" min="1" max="365" style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 1rem;" placeholder="30">';
	$output .= '</div>';
	$output .= '</div>';
	
	$output .= '</div>';
	
	// Fundraising Methods Section
	$output .= '<div style="background: #f9fafb; padding: 1.5rem; border-radius: 8px; margin-bottom: 1.5rem;">';
	$output .= '<h3 style="margin: 0 0 1rem 0; font-size: 1.25rem;">Fundraising Methods</h3>';
	$output .= '<p style="color: #666; margin-bottom: 1rem;">Select which fundraising methods to enable for this campaign.</p>';
	
	$output .= '<div style="display: grid; gap: 1rem;">';
	
	$output .= '<label style="display: flex; align-items: center; padding: 1rem; background: white; border: 2px solid #d1d5db; border-radius: 8px; cursor: pointer; transition: all 0.2s;" :style="{borderColor: settings.donations_enabled ? \'#667eea\' : \'#d1d5db\', background: settings.donations_enabled ? \'#ede9fe\' : \'white\'}">';
	$output .= '<input type="checkbox" v-model="settings.donations_enabled" style="width: 20px; height: 20px; margin-right: 1rem;">';
	$output .= '<div>';
	$output .= '<div style="font-weight: 600; font-size: 1.1rem;">💰 Direct Donations</div>';
	$output .= '<div style="color: #666; font-size: 0.875rem;">Allow supporters to make direct monetary contributions</div>';
	$output .= '</div>';
	$output .= '</label>';
	
	$output .= '<label style="display: flex; align-items: center; padding: 1rem; background: white; border: 2px solid #d1d5db; border-radius: 8px; cursor: pointer; transition: all 0.2s;" :style="{borderColor: settings.products_enabled ? \'#667eea\' : \'#d1d5db\', background: settings.products_enabled ? \'#ede9fe\' : \'white\'}">';
	$output .= '<input type="checkbox" v-model="settings.products_enabled" style="width: 20px; height: 20px; margin-right: 1rem;">';
	$output .= '<div>';
	$output .= '<div style="font-weight: 600; font-size: 1.1rem;">🛍️ Product Sales</div>';
	$output .= '<div style="color: #666; font-size: 0.875rem;">Sell custom merchandise and products</div>';
	$output .= '</div>';
	$output .= '</label>';
	
	$output .= '<label style="display: flex; align-items: center; padding: 1rem; background: white; border: 2px solid #d1d5db; border-radius: 8px; cursor: pointer; transition: all 0.2s;" :style="{borderColor: settings.raffles_enabled ? \'#667eea\' : \'#d1d5db\', background: settings.raffles_enabled ? \'#ede9fe\' : \'white\'}">';
	$output .= '<input type="checkbox" v-model="settings.raffles_enabled" style="width: 20px; height: 20px; margin-right: 1rem;">';
	$output .= '<div>';
	$output .= '<div style="font-weight: 600; font-size: 1.1rem;">🎟️ Raffles</div>';
	$output .= '<div style="color: #666; font-size: 0.875rem;">Run raffle drawings with prizes</div>';
	$output .= '</div>';
	$output .= '</label>';
	
	$output .= '</div>';
	$output .= '</div>';
	
	// Media Section
	$output .= '<div style="background: #f9fafb; padding: 1.5rem; border-radius: 8px; margin-bottom: 1.5rem;">';
	$output .= '<h3 style="margin: 0 0 1rem 0; font-size: 1.25rem;">Media</h3>';
	
	$output .= '<div style="margin-bottom: 1.5rem;">';
	$output .= '<label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Video URL (YouTube, Vimeo, etc.)</label>';
	$output .= '<input type="url" v-model="settings.video_url" style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 1rem;" placeholder="https://youtube.com/watch?v=...">';
	$output .= '</div>';
	
	$output .= '</div>';
	
	// Status Section
	$output .= '<div style="background: #f9fafb; padding: 1.5rem; border-radius: 8px; margin-bottom: 1.5rem;">';
	$output .= '<h3 style="margin: 0 0 1rem 0; font-size: 1.25rem;">Campaign Status</h3>';
	
	$output .= '<div>';
	$output .= '<label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Status</label>';
	$output .= '<select v-model="settings.status" style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 1rem;">';
	$output .= '<option value="publish">Active (Published)</option>';
	$output .= '<option value="draft">Draft (Not visible to public)</option>';
	$output .= '<option value="pending">Pending Review</option>';
	$output .= '</select>';
	$output .= '</div>';
	
	$output .= '</div>';
	
	// Save Button
	$output .= '<div style="display: flex; gap: 1rem;">';
	$output .= '<button type="button" @click="saveSettings" :disabled="saving" style="padding: 1rem 2rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 8px; font-weight: 600; font-size: 1rem; cursor: pointer; transition: all 0.2s;">';
	$output .= '<span v-if="!saving">💾 Save Changes</span>';
	$output .= '<span v-else>⏳ Saving...</span>';
	$output .= '</button>';
	$output .= '<div v-if="message" :style="{padding: \'1rem\', borderRadius: \'6px\', background: messageType === \'success\' ? \'#d1fae5\' : \'#fee2e2\', color: messageType === \'success\' ? \'#065f46\' : \'#991b1b\'}">{{ message }}</div>';
	$output .= '</div>';
	
	$output .= '</form>';
	$output .= '</div>';
	
	// Vue.js app
	$output .= '<script type="text/javascript">';
	$output .= '/* <![CDATA[ */';
	$output .= 'if (typeof Vue !== "undefined") {';
	$output .= 'const { createApp } = Vue;';
	$output .= 'createApp({';
	$output .= 'data() {';
	$output .= 'return {';
	$output .= 'settings: {';
	$output .= 'title: ' . json_encode($campaign->post_title) . ',';
	$output .= 'description: ' . json_encode($campaign->post_content) . ',';
	$output .= 'goal: ' . json_encode($goal ? floatval($goal) : 0) . ',';
	$output .= 'duration: ' . json_encode($duration ? intval($duration) : 30) . ',';
	$output .= 'donations_enabled: ' . json_encode(!empty($donations_enabled)) . ',';
	$output .= 'products_enabled: ' . json_encode(!empty($products_enabled)) . ',';
	$output .= 'raffles_enabled: ' . json_encode(!empty($raffles_enabled)) . ',';
	$output .= 'video_url: ' . json_encode($video_url ?: '') . ',';
	$output .= 'status: ' . json_encode($campaign->post_status) . ',';
	$output .= '},';
	$output .= 'saving: false,';
	$output .= 'message: "",';
	$output .= 'messageType: "success"';
	$output .= '};';
	$output .= '},';
	$output .= 'methods: {';
	$output .= 'async saveSettings() {';
	$output .= 'this.saving = true;';
	$output .= 'this.message = "";';
	$output .= 'try {';
	$output .= 'const response = await fetch(fundraiserData.apiUrl + "campaigns/' . $campaign_id . '", {';
	$output .= 'method: "PUT",';
	$output .= 'headers: {';
	$output .= '"Content-Type": "application/json",';
	$output .= '"X-WP-Nonce": fundraiserData.nonce';
	$output .= '},';
	$output .= 'body: JSON.stringify(this.settings)';
	$output .= '});';
	$output .= 'const data = await response.json();';
	$output .= 'if (response.ok) {';
	$output .= 'this.message = "Settings saved successfully!";';
	$output .= 'this.messageType = "success";';
	$output .= 'setTimeout(() => { this.message = ""; }, 3000);';
	$output .= '} else {';
	$output .= 'this.message = data.message || "Failed to save settings";';
	$output .= 'this.messageType = "error";';
	$output .= '}';
	$output .= '} catch (error) {';
	$output .= 'this.message = "Error: " + error.message;';
	$output .= 'this.messageType = "error";';
	$output .= '} finally {';
	$output .= 'this.saving = false;';
	$output .= '}';
	$output .= '}';
	$output .= '}';
	$output .= '}).mount("#campaign-settings-app");';
	$output .= '}';
	$output .= '/* ]]> */';
	$output .= '</script>';
	
	return $output;
}
add_shortcode('campaign_settings_tab', 'campaign_settings_tab_shortcode');


/**
 * Shortcode: Campaign Creation Wizard
 */
function campaign_creation_wizard_shortcode($atts) {
	if (!is_user_logged_in()) {
		return '<p>Please log in to create a campaign.</p>';
	}
	
	$output = '';
	
	// Navigation menu
	$output .= fundraiser_dashboard_menu();

	// Vue app mounting point - entire template is generated by JavaScript to avoid WordPress content filters
	$output .= '<div id="campaign-wizard-app"></div>';

	// Enqueue external Vue.js app script
	wp_enqueue_script(
		'campaign-wizard',
		get_template_directory_uri() . '/assets/js/campaign-wizard.js',
		array('vue'),
		'1.0.1',
		true
	);

	return $output;
}
add_shortcode('campaign_creation_wizard', 'campaign_creation_wizard_shortcode');

// Prevent wptexturize from affecting our shortcodes with JavaScript
add_filter('no_texturize_shortcodes', 'fundraiser_no_texturize_shortcodes');
function fundraiser_no_texturize_shortcodes($shortcodes) {
	$shortcodes[] = 'campaign_creation_wizard';
	$shortcodes[] = 'campaign_settings_tab';
	$shortcodes[] = 'campaign_products_tab';
	$shortcodes[] = 'campaign_reports_tab';
	$shortcodes[] = 'campaign_raffles_tab';
	$shortcodes[] = 'campaign_manual_entry_tab';
	$shortcodes[] = 'product_request_form';
	return $shortcodes;
}

// Fix JavaScript that WordPress has mangled with HTML entities
add_filter('the_content', 'fundraiser_fix_javascript_entities', 999);
function fundraiser_fix_javascript_entities($content) {
	// Only fix if we have Vue.js scripts in the content
	if (strpos($content, 'createApp') !== false || strpos($content, 'Vue') !== false) {
		// Convert HTML entities back to their original characters within script tags
		$content = preg_replace_callback(
			'/<script([^>]*)>(.*?)<\/script>/is',
			function($matches) {
				$attributes = $matches[1];
				$script_content = $matches[2];
				// Decode HTML entities in the JavaScript
				$script_content = str_replace('&#038;', '&', $script_content);
				$script_content = html_entity_decode($script_content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
				return '<script' . $attributes . '>' . $script_content . '</script>';
			},
			$content
		);
	}
	return $content;
}


// ==================== PRODUCT SYSTEM SHORTCODES ====================

/**
 * Shortcode: Product Request Form
 */
function product_request_form_shortcode($atts) {
	if (!is_user_logged_in()) {
		return '<p>Please log in to request products.</p>';
	}
	
	$user_id = get_current_user_id();
	
	// Get user's campaigns
	$campaigns = get_posts(array(
		'post_type' => 'fundraiser_campaign',
		'author' => $user_id,
		'post_status' => 'publish',
		'posts_per_page' => -1,
		'orderby' => 'date',
		'order' => 'DESC',
	));
	
	$output = '';
	
	// Navigation menu
	$output .= fundraiser_dashboard_menu();
	
	// Page header
	$output .= '<div style="margin-bottom: 2rem;">';
	$output .= '<h1 style="margin: 0 0 0.5rem 0; font-size: 2rem;">Request Products</h1>';
	$output .= '<p style="color: #666; font-size: 1.1rem;">Submit artwork and product requests for your campaign.</p>';
	$output .= '</div>';
	
	if (empty($campaigns)) {
		$output .= '<div style="text-align: center; padding: 4rem 2rem; background: white; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">';
		$output .= '<div style="font-size: 4rem; margin-bottom: 1rem;">📋</div>';
		$output .= '<h3 style="margin: 0 0 1rem 0;">No campaigns found</h3>';
		$output .= '<p style="color: #666; margin-bottom: 2rem;">You need to create a campaign before requesting products.</p>';
		$output .= '<a href="' . esc_url(home_url('/campaign-wizard/')) . '" style="display: inline-block; padding: 1rem 2rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; text-decoration: none; border-radius: 8px; font-weight: 600;">Create Campaign</a>';
		$output .= '</div>';
		return $output;
	}
	
	// Prepare campaign data for JavaScript
	$campaigns_data = array();
	foreach ($campaigns as $campaign) {
		$campaigns_data[] = array(
			'id' => $campaign->ID,
			'title' => $campaign->post_title
		);
	}

	// Localize script data
	wp_localize_script('product-request', 'fundraiserData', array(
		'campaigns' => $campaigns_data,
		'apiUrl' => rest_url('fundraiser-pro/v1/'),
		'nonce' => wp_create_nonce('wp_rest'),
		'homeUrl' => home_url()
	));

	// Vue app mounting point - entire template is generated by JavaScript to avoid WordPress content filters
	$output .= '<div id="product-request-app"></div>';

	// Enqueue external Vue.js app script
	wp_enqueue_script(
		'product-request',
		get_template_directory_uri() . '/assets/js/product-request.js',
		array('vue'),
		'1.0.0',
		true
	);
	
	return $output;
}
add_shortcode('product_request_form', 'product_request_form_shortcode');


/**
 * Shortcode: Campaign Products Tab
 */
function campaign_products_tab_shortcode($atts) {
	$atts = shortcode_atts(array('campaign_id' => 0), $atts);
	$campaign_id = intval($atts['campaign_id']);
	
	if (!$campaign_id) {
		return '<p>No campaign specified.</p>';
	}
	
	// Get campaign's product category
	$category_id = get_post_meta($campaign_id, '_campaign_product_category', true);
	
	$output = '<h2 style="margin: 0 0 1.5rem 0;">Campaign Products</h2>';
	
	if (!$category_id) {
		$output .= '<div style="text-align: center; padding: 3rem 2rem; background: #f9fafb; border-radius: 8px;">';
		$output .= '<div style="font-size: 3rem; margin-bottom: 1rem;">🛍️</div>';
		$output .= '<h3 style="margin: 0 0 1rem 0;">No products yet</h3>';
		$output .= '<p style="color: #666; margin-bottom: 2rem;">Request products with your custom artwork to sell in this campaign.</p>';
		$output .= '<a href="' . esc_url(home_url('/product-request/?campaign_id=' . $campaign_id)) . '" style="display: inline-block; padding: 1rem 2rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; text-decoration: none; border-radius: 8px; font-weight: 600;">Request Products</a>';
		$output .= '</div>';
		return $output;
	}
	
	// Get products in this campaign's category
	$args = array(
		'post_type' => 'product',
		'posts_per_page' => -1,
		'tax_query' => array(
			array(
				'taxonomy' => 'product_cat',
				'field' => 'term_id',
				'terms' => $category_id,
			),
		),
	);
	
	$products = get_posts($args);
	
	if (empty($products)) {
		$output .= '<div style="text-align: center; padding: 3rem 2rem; background: #f9fafb; border-radius: 8px;">';
		$output .= '<div style="font-size: 3rem; margin-bottom: 1rem;">⏳</div>';
		$output .= '<h3 style="margin: 0 0 1rem 0;">Products being created</h3>';
		$output .= '<p style="color: #666;">Your product request is being processed. Products will appear here once they\'re ready.</p>';
		$output .= '</div>';
		return $output;
	}
	
	$output .= '<div id="products-tab-app">';
	
	$output .= '<p style="margin-bottom: 1.5rem; color: #666;">Edit the prices for your campaign products. Changes are saved automatically.</p>';
	
	$output .= '<div style="display: grid; gap: 1rem;">';
	
	foreach ($products as $product) {
		$product_obj = wc_get_product($product->ID);
		if (!$product_obj) continue;
		
		$regular_price = $product_obj->get_regular_price();
		$sale_price = $product_obj->get_sale_price();
		$image_url = wp_get_attachment_image_url($product_obj->get_image_id(), 'thumbnail');
		
		$output .= '<div style="display: flex; gap: 1.5rem; padding: 1.5rem; background: #f9fafb; border-radius: 8px;">';
		
		// Product image
		if ($image_url) {
			$output .= '<div style="flex-shrink: 0;">';
			$output .= '<img src="' . esc_url($image_url) . '" style="width: 100px; height: 100px; object-fit: cover; border-radius: 6px;">';
			$output .= '</div>';
		}
		
		// Product info and pricing
		$output .= '<div style="flex: 1;">';
		$output .= '<h3 style="margin: 0 0 0.5rem 0; font-size: 1.1rem;">' . esc_html($product->post_title) . '</h3>';
		$output .= '<div style="color: #666; font-size: 0.875rem; margin-bottom: 1rem;">' . esc_html($product_obj->get_type()) . '</div>';
		
		$output .= '<div style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 1rem; align-items: end;">';
		
		$output .= '<div>';
		$output .= '<label style="display: block; font-weight: 600; margin-bottom: 0.25rem; font-size: 0.875rem;">Regular Price ($)</label>';
		$output .= '<input type="number" v-model="prices[' . $product->ID . '].regular_price" @change="savePrice(' . $product->ID . ')" step="0.01" min="0" style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 6px;">';
		$output .= '</div>';
		
		$output .= '<div>';
		$output .= '<label style="display: block; font-weight: 600; margin-bottom: 0.25rem; font-size: 0.875rem;">Sale Price ($)</label>';
		$output .= '<input type="number" v-model="prices[' . $product->ID . '].sale_price" @change="savePrice(' . $product->ID . ')" step="0.01" min="0" style="width: 100%; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 6px;" placeholder="Optional">';
		$output .= '</div>';
		
		$output .= '<div>';
		$output .= '<a href="' . esc_url(get_permalink($product->ID)) . '" target="_blank" style="display: inline-block; padding: 0.5rem 1rem; background: #667eea; color: white; text-decoration: none; border-radius: 6px; font-size: 0.875rem; font-weight: 600;">View Product</a>';
		$output .= '</div>';
		
		$output .= '</div>';
		
		$output .= '<div v-if="saving[' . $product->ID . ']" style="color: #10b981; font-size: 0.875rem; margin-top: 0.5rem;">💾 Saving...</div>';
		$output .= '<div v-if="saved[' . $product->ID . ']" style="color: #10b981; font-size: 0.875rem; margin-top: 0.5rem;">✓ Saved</div>';
		
		$output .= '</div>';
		$output .= '</div>';
	}
	
	$output .= '</div>';
	$output .= '</div>';
	
	// Vue.js app
	$output .= '<script type="text/javascript">';
	$output .= '/* <![CDATA[ */';
	$output .= 'if (typeof Vue !== "undefined") {';
	$output .= 'const { createApp } = Vue;';
	$output .= 'createApp({';
	$output .= 'data() {';
	$output .= 'return {';
	$output .= 'prices: {';
	foreach ($products as $product) {
		$product_obj = wc_get_product($product->ID);
		if (!$product_obj) continue;
		$output .= $product->ID . ': {';
		$output .= 'regular_price: ' . json_encode($product_obj->get_regular_price() ?: '') . ',';
		$output .= 'sale_price: ' . json_encode($product_obj->get_sale_price() ?: '') . '';
		$output .= '},';
	}
	$output .= '},';
	$output .= 'saving: {},';
	$output .= 'saved: {}';
	$output .= '};';
	$output .= '},';
	$output .= 'methods: {';
	$output .= 'async savePrice(productId) {';
	$output .= 'this.saving[productId] = true;';
	$output .= 'this.saved[productId] = false;';
	$output .= 'try {';
	$output .= 'const response = await fetch(fundraiserData.apiUrl + "products/" + productId + "/prices", {';
	$output .= 'method: "PUT",';
	$output .= 'headers: {';
	$output .= '"Content-Type": "application/json",';
	$output .= '"X-WP-Nonce": fundraiserData.nonce';
	$output .= '},';
	$output .= 'body: JSON.stringify(this.prices[productId])';
	$output .= '});';
	$output .= 'if (response.ok) {';
	$output .= 'this.saved[productId] = true;';
	$output .= 'setTimeout(() => { this.saved[productId] = false; }, 2000);';
	$output .= '}';
	$output .= '} catch (error) {';
	$output .= 'console.error("Error saving price:", error);';
	$output .= '} finally {';
	$output .= 'this.saving[productId] = false;';
	$output .= '}';
	$output .= '}';
	$output .= '}';
	$output .= '}).mount("#products-tab-app");';
	$output .= '}';
	$output .= '/* ]]> */';
	$output .= '</script>';
	
	return $output;
}
add_shortcode('campaign_products_tab', 'campaign_products_tab_shortcode');


// ==================== MANUAL ENTRY SHORTCODES ====================

/**
 * Shortcode: Campaign Manual Entry Tab
 */
function campaign_manual_entry_tab_shortcode($atts) {
	$atts = shortcode_atts(array('campaign_id' => 0), $atts);
	$campaign_id = intval($atts['campaign_id']);
	
	if (!$campaign_id) {
		return '<p>No campaign specified.</p>';
	}
	
	$campaign = get_post($campaign_id);
	
	// Get raffles for this campaign
	$raffles = get_posts(array(
		'post_type' => 'fundraiser_raffle',
		'meta_query' => array(
			array(
				'key' => 'campaign_id',
				'value' => $campaign_id,
			),
		),
		'posts_per_page' => -1,
	));
	
	$output = '<h2 style="margin: 0 0 1.5rem 0;">Manual Entry</h2>';
	$output .= '<p style="margin-bottom: 2rem; color: #666;">Record in-person cash payments and raffle ticket sales.</p>';
	
	$output .= '<div id="manual-entry-app">';
	
	$output .= '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">';
	
	// Cash Donation Card
	$output .= '<div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 2rem; border-radius: 12px;">';
	$output .= '<h3 style="margin: 0 0 1.5rem 0; font-size: 1.5rem;">💰 Cash Donation</h3>';
	
	$output .= '<div style="margin-bottom: 1rem;">';
	$output .= '<label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Donor Name *</label>';
	$output .= '<input type="text" v-model="cashDonation.donor_name" style="width: 100%; padding: 0.75rem; border: none; border-radius: 6px; color: #333;">';
	$output .= '</div>';
	
	$output .= '<div style="margin-bottom: 1rem;">';
	$output .= '<label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Email (optional)</label>';
	$output .= '<input type="email" v-model="cashDonation.donor_email" style="width: 100%; padding: 0.75rem; border: none; border-radius: 6px; color: #333;">';
	$output .= '</div>';
	
	$output .= '<div style="margin-bottom: 1rem;">';
	$output .= '<label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Amount ($) *</label>';
	$output .= '<input type="number" v-model="cashDonation.amount" step="0.01" min="0" style="width: 100%; padding: 0.75rem; border: none; border-radius: 6px; color: #333;">';
	$output .= '</div>';
	
	$output .= '<div style="margin-bottom: 1rem;">';
	$output .= '<label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Payment Method</label>';
	$output .= '<select v-model="cashDonation.payment_method" style="width: 100%; padding: 0.75rem; border: none; border-radius: 6px; color: #333;">';
	$output .= '<option value="cash">Cash</option>';
	$output .= '<option value="check">Check</option>';
	$output .= '<option value="money_order">Money Order</option>';
	$output .= '</select>';
	$output .= '</div>';
	
	$output .= '<div style="margin-bottom: 1.5rem;">';
	$output .= '<label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Notes</label>';
	$output .= '<textarea v-model="cashDonation.notes" rows="2" style="width: 100%; padding: 0.75rem; border: none; border-radius: 6px; color: #333; resize: vertical;"></textarea>';
	$output .= '</div>';
	
	$output .= '<button @click="submitCashDonation" :disabled="submittingCash" style="width: 100%; padding: 0.75rem; background: white; color: #667eea; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 1rem;">';
	$output .= '<span v-if="!submittingCash">Submit Donation</span>';
	$output .= '<span v-else>Submitting...</span>';
	$output .= '</button>';
	
	$output .= '<div v-if="cashMessage" :style="{marginTop: \'1rem\', padding: \'0.75rem\', borderRadius: \'6px\', background: cashMessageType === \'success\' ? \'rgba(255,255,255,0.2)\' : \'rgba(220,38,38,0.2)\'}">{{ cashMessage }}</div>';
	
	$output .= '</div>';
	
	// Raffle Entry Card
	$output .= '<div style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 2rem; border-radius: 12px;">';
	$output .= '<h3 style="margin: 0 0 1.5rem 0; font-size: 1.5rem;">🎟️ Raffle Entry</h3>';
	
	if (empty($raffles)) {
		$output .= '<div style="text-align: center; padding: 2rem;">';
		$output .= '<div style="margin-bottom: 1rem; opacity: 0.8;">No raffles created yet</div>';
		$output .= '<div style="font-size: 0.875rem; opacity: 0.7;">Create a raffle in the Raffles tab to record ticket sales here.</div>';
		$output .= '</div>';
	} else {
		$output .= '<div style="margin-bottom: 1rem;">';
		$output .= '<label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Select Raffle *</label>';
		$output .= '<select v-model="raffleEntry.raffle_id" style="width: 100%; padding: 0.75rem; border: none; border-radius: 6px; color: #333;">';
		$output .= '<option value="">-- Select a raffle --</option>';
		foreach ($raffles as $raffle) {
			$output .= '<option value="' . $raffle->ID . '">' . esc_html($raffle->post_title) . '</option>';
		}
		$output .= '</select>';
		$output .= '</div>';
		
		$output .= '<div style="margin-bottom: 1rem;">';
		$output .= '<label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Participant Name *</label>';
		$output .= '<input type="text" v-model="raffleEntry.participant_name" style="width: 100%; padding: 0.75rem; border: none; border-radius: 6px; color: #333;">';
		$output .= '</div>';
		
		$output .= '<div style="margin-bottom: 1rem;">';
		$output .= '<label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Email (optional)</label>';
		$output .= '<input type="email" v-model="raffleEntry.participant_email" style="width: 100%; padding: 0.75rem; border: none; border-radius: 6px; color: #333;">';
		$output .= '</div>';
		
		$output .= '<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">';
		
		$output .= '<div>';
		$output .= '<label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Tickets *</label>';
		$output .= '<input type="number" v-model="raffleEntry.ticket_count" min="1" style="width: 100%; padding: 0.75rem; border: none; border-radius: 6px; color: #333;">';
		$output .= '</div>';
		
		$output .= '<div>';
		$output .= '<label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Amount ($) *</label>';
		$output .= '<input type="number" v-model="raffleEntry.amount" step="0.01" min="0" style="width: 100%; padding: 0.75rem; border: none; border-radius: 6px; color: #333;">';
		$output .= '</div>';
		
		$output .= '</div>';
		
		$output .= '<button @click="submitRaffleEntry" :disabled="submittingRaffle" style="width: 100%; padding: 0.75rem; background: white; color: #f093fb; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 1rem;">';
		$output .= '<span v-if="!submittingRaffle">Submit Entry</span>';
		$output .= '<span v-else>Submitting...</span>';
		$output .= '</button>';
		
		$output .= '<div v-if="raffleMessage" :style="{marginTop: \'1rem\', padding: \'0.75rem\', borderRadius: \'6px\', background: raffleMessageType === \'success\' ? \'rgba(255,255,255,0.2)\' : \'rgba(220,38,38,0.2)\'}">{{ raffleMessage }}</div>';
	}
	
	$output .= '</div>';
	
	$output .= '</div>';
	
	// Recent entries
	$output .= '<div style="background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">';
	$output .= '<h3 style="margin: 0 0 1rem 0;">Recent Entries (Pending Approval)</h3>';
	$output .= '<div v-if="loading" style="text-align: center; padding: 2rem; color: #666;">Loading...</div>';
	$output .= '<div v-else-if="recentEntries.length === 0" style="text-align: center; padding: 2rem; color: #666;">No pending entries</div>';
	$output .= '<div v-else style="overflow-x: auto;">';
	$output .= '<table style="width: 100%; border-collapse: collapse;">';
	$output .= '<thead>';
	$output .= '<tr style="background: #f9fafb; border-bottom: 2px solid #e5e7eb;">';
	$output .= '<th style="padding: 0.75rem; text-align: left;">Type</th>';
	$output .= '<th style="padding: 0.75rem; text-align: left;">Name</th>';
	$output .= '<th style="padding: 0.75rem; text-align: left;">Amount</th>';
	$output .= '<th style="padding: 0.75rem; text-align: left;">Date</th>';
	$output .= '<th style="padding: 0.75rem; text-align: left;">Status</th>';
	$output .= '</tr>';
	$output .= '</thead>';
	$output .= '<tbody>';
	$output .= '<tr v-for="entry in recentEntries" :key="entry.id" style="border-bottom: 1px solid #e5e7eb;">';
	$output .= '<td style="padding: 0.75rem;">{{ entry.type === "cash" ? "💰 Cash" : "🎟️ Raffle" }}</td>';
	$output .= '<td style="padding: 0.75rem;">{{ entry.name }}</td>';
	$output .= '<td style="padding: 0.75rem;">${{ parseFloat(entry.amount).toFixed(2) }}</td>';
	$output .= '<td style="padding: 0.75rem;">{{ new Date(entry.date).toLocaleDateString() }}</td>';
	$output .= '<td style="padding: 0.75rem;"><span style="padding: 0.25rem 0.75rem; background: #fef3c7; color: #92400e; border-radius: 4px; font-size: 0.875rem;">Pending</span></td>';
	$output .= '</tr>';
	$output .= '</tbody>';
	$output .= '</table>';
	$output .= '</div>';
	$output .= '</div>';
	
	$output .= '</div>';
	
	// Vue.js app
	$output .= '<script type="text/javascript">';
	$output .= '/* <![CDATA[ */';
	$output .= 'if (typeof Vue !== "undefined") {';
	$output .= 'const { createApp } = Vue;';
	$output .= 'createApp({';
	$output .= 'data() {';
	$output .= 'return {';
	$output .= 'cashDonation: {';
	$output .= 'campaign_id: ' . $campaign_id . ',';
	$output .= 'donor_name: "",';
	$output .= 'donor_email: "",';
	$output .= 'amount: 0,';
	$output .= 'payment_method: "cash",';
	$output .= 'notes: ""';
	$output .= '},';
	$output .= 'raffleEntry: {';
	$output .= 'raffle_id: "",';
	$output .= 'participant_name: "",';
	$output .= 'participant_email: "",';
	$output .= 'ticket_count: 1,';
	$output .= 'amount: 0';
	$output .= '},';
	$output .= 'submittingCash: false,';
	$output .= 'submittingRaffle: false,';
	$output .= 'cashMessage: "",';
	$output .= 'raffleMessage: "",';
	$output .= 'cashMessageType: "success",';
	$output .= 'raffleMessageType: "success",';
	$output .= 'recentEntries: [],';
	$output .= 'loading: false';
	$output .= '};';
	$output .= '},';
	$output .= 'mounted() {';
	$output .= 'this.loadRecentEntries();';
	$output .= '},';
	$output .= 'methods: {';
	$output .= 'async submitCashDonation() {';
	$output .= 'if (!this.cashDonation.donor_name || !this.cashDonation.amount || this.cashDonation.amount <= 0) {';
	$output .= 'this.cashMessage = "Please fill in required fields";';
	$output .= 'this.cashMessageType = "error";';
	$output .= 'return;';
	$output .= '}';
	$output .= 'this.submittingCash = true;';
	$output .= 'this.cashMessage = "";';
	$output .= 'try {';
	$output .= 'const response = await fetch(fundraiserData.apiUrl + "cash-transactions", {';
	$output .= 'method: "POST",';
	$output .= 'headers: {';
	$output .= '"Content-Type": "application/json",';
	$output .= '"X-WP-Nonce": fundraiserData.nonce';
	$output .= '},';
	$output .= 'body: JSON.stringify(this.cashDonation)';
	$output .= '});';
	$output .= 'const data = await response.json();';
	$output .= 'if (response.ok) {';
	$output .= 'this.cashMessage = "Cash donation recorded! Pending admin approval.";';
	$output .= 'this.cashMessageType = "success";';
	$output .= 'this.cashDonation = {campaign_id: ' . $campaign_id . ', donor_name: "", donor_email: "", amount: 0, payment_method: "cash", notes: ""};';
	$output .= 'this.loadRecentEntries();';
	$output .= 'setTimeout(() => { this.cashMessage = ""; }, 3000);';
	$output .= '} else {';
	$output .= 'this.cashMessage = data.message || "Failed to record donation";';
	$output .= 'this.cashMessageType = "error";';
	$output .= '}';
	$output .= '} catch (error) {';
	$output .= 'this.cashMessage = "Error: " + error.message;';
	$output .= 'this.cashMessageType = "error";';
	$output .= '} finally {';
	$output .= 'this.submittingCash = false;';
	$output .= '}';
	$output .= '},';
	$output .= 'async submitRaffleEntry() {';
	$output .= 'if (!this.raffleEntry.raffle_id || !this.raffleEntry.participant_name || !this.raffleEntry.ticket_count || !this.raffleEntry.amount) {';
	$output .= 'this.raffleMessage = "Please fill in required fields";';
	$output .= 'this.raffleMessageType = "error";';
	$output .= 'return;';
	$output .= '}';
	$output .= 'this.submittingRaffle = true;';
	$output .= 'this.raffleMessage = "";';
	$output .= 'try {';
	$output .= 'const response = await fetch(fundraiserData.apiUrl + "raffle-entries", {';
	$output .= 'method: "POST",';
	$output .= 'headers: {';
	$output .= '"Content-Type": "application/json",';
	$output .= '"X-WP-Nonce": fundraiserData.nonce';
	$output .= '},';
	$output .= 'body: JSON.stringify(this.raffleEntry)';
	$output .= '});';
	$output .= 'const data = await response.json();';
	$output .= 'if (response.ok) {';
	$output .= 'this.raffleMessage = "Raffle entry recorded! Tickets: " + data.ticket_numbers.join(", ");';
	$output .= 'this.raffleMessageType = "success";';
	$output .= 'this.raffleEntry = {raffle_id: "", participant_name: "", participant_email: "", ticket_count: 1, amount: 0};';
	$output .= 'setTimeout(() => { this.raffleMessage = ""; }, 5000);';
	$output .= '} else {';
	$output .= 'this.raffleMessage = data.message || "Failed to record entry";';
	$output .= 'this.raffleMessageType = "error";';
	$output .= '}';
	$output .= '} catch (error) {';
	$output .= 'this.raffleMessage = "Error: " + error.message;';
	$output .= 'this.raffleMessageType = "error";';
	$output .= '} finally {';
	$output .= 'this.submittingRaffle = false;';
	$output .= '}';
	$output .= '},';
	$output .= 'async loadRecentEntries() {';
	$output .= 'this.loading = true;';
	$output .= 'try {';
	$output .= 'const response = await fetch(fundraiserData.apiUrl + "campaigns/' . $campaign_id . '/recent-entries", {';
	$output .= 'headers: {';
	$output .= '"X-WP-Nonce": fundraiserData.nonce';
	$output .= '}';
	$output .= '});';
	$output .= 'if (response.ok) {';
	$output .= 'this.recentEntries = await response.json();';
	$output .= '}';
	$output .= '} catch (error) {';
	$output .= 'console.error("Error loading entries:", error);';
	$output .= '} finally {';
	$output .= 'this.loading = false;';
	$output .= '}';
	$output .= '}';
	$output .= '}';
	$output .= '}).mount("#manual-entry-app");';
	$output .= '}';
	$output .= '/* ]]> */';
	$output .= '</script>';
	
	return $output;
}
add_shortcode('campaign_manual_entry_tab', 'campaign_manual_entry_tab_shortcode');


// ==================== REPORTING SHORTCODES ====================

/**
 * Shortcode: Campaign Reports Tab
 */
function campaign_reports_tab_shortcode($atts) {
	$atts = shortcode_atts(array('campaign_id' => 0), $atts);
	$campaign_id = intval($atts['campaign_id']);
	
	if (!$campaign_id) {
		return '<p>No campaign specified.</p>';
	}
	
	global $wpdb;
	$campaign = get_post($campaign_id);
	
	$output = '<h2 style="margin: 0 0 1.5rem 0;">Campaign Reports & Analytics</h2>';
	
	$output .= '<div id="reports-app">';
	
	// Loading state
	$output .= '<div v-if="loading" style="text-align: center; padding: 4rem;">';
	$output .= '<div style="font-size: 3rem; margin-bottom: 1rem;">📊</div>';
	$output .= '<div style="color: #666;">Loading analytics...</div>';
	$output .= '</div>';
	
	$output .= '<div v-else>';
	
	// Key metrics cards
	$output .= '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem;">';
	
	$output .= '<div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 1.5rem; border-radius: 12px;">';
	$output .= '<div style="font-size: 0.875rem; opacity: 0.9; margin-bottom: 0.5rem;">Total Raised</div>';
	$output .= '<div style="font-size: 2rem; font-weight: bold;">${{ analytics.total_raised.toLocaleString() }}</div>';
	$output .= '</div>';
	
	$output .= '<div style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 1.5rem; border-radius: 12px;">';
	$output .= '<div style="font-size: 0.875rem; opacity: 0.9; margin-bottom: 0.5rem;">Total Donors</div>';
	$output .= '<div style="font-size: 2rem; font-weight: bold;">{{ analytics.total_donors }}</div>';
	$output .= '</div>';
	
	$output .= '<div style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; padding: 1.5rem; border-radius: 12px;">';
	$output .= '<div style="font-size: 0.875rem; opacity: 0.9; margin-bottom: 0.5rem;">Campaign Views</div>';
	$output .= '<div style="font-size: 2rem; font-weight: bold;">{{ analytics.campaign_views }}</div>';
	$output .= '</div>';
	
	$output .= '<div style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); color: white; padding: 1.5rem; border-radius: 12px;">';
	$output .= '<div style="font-size: 0.875rem; opacity: 0.9; margin-bottom: 0.5rem;">Avg Donation</div>';
	$output .= '<div style="font-size: 2rem; font-weight: bold;">${{ avgDonation }}</div>';
	$output .= '</div>';
	
	$output .= '</div>';
	
	// Charts row
	$output .= '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">';
	
	// Revenue Sources Chart
	$output .= '<div style="background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">';
	$output .= '<h3 style="margin: 0 0 1.5rem 0;">Revenue Sources</h3>';
	$output .= '<canvas id="revenue-chart" style="max-height: 300px;"></canvas>';
	$output .= '</div>';
	
	// Progress Over Time Chart
	$output .= '<div style="background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">';
	$output .= '<h3 style="margin: 0 0 1.5rem 0;">Fundraising Progress</h3>';
	$output .= '<canvas id="progress-chart" style="max-height: 300px;"></canvas>';
	$output .= '</div>';
	
	$output .= '</div>';
	
	// Revenue breakdown table
	$output .= '<div style="background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 2rem;">';
	$output .= '<h3 style="margin: 0 0 1.5rem 0;">Revenue Breakdown</h3>';
	$output .= '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">';
	
	$output .= '<div style="padding: 1.5rem; background: #f9fafb; border-radius: 8px; border-left: 4px solid #667eea;">';
	$output .= '<div style="font-size: 0.875rem; color: #666; margin-bottom: 0.5rem;">💰 Donations</div>';
	$output .= '<div style="font-size: 1.5rem; font-weight: bold; color: #667eea;">${{ analytics.donation_revenue.toLocaleString() }}</div>';
	$output .= '<div style="font-size: 0.875rem; color: #666; margin-top: 0.5rem;">{{ donationPercentage }}% of total</div>';
	$output .= '</div>';
	
	$output .= '<div style="padding: 1.5rem; background: #f9fafb; border-radius: 8px; border-left: 4px solid #f093fb;">';
	$output .= '<div style="font-size: 0.875rem; color: #666; margin-bottom: 0.5rem;">🛍️ Products</div>';
	$output .= '<div style="font-size: 1.5rem; font-weight: bold; color: #f093fb;">${{ analytics.product_revenue.toLocaleString() }}</div>';
	$output .= '<div style="font-size: 0.875rem; color: #666; margin-top: 0.5rem;">{{ productPercentage }}% of total</div>';
	$output .= '</div>';
	
	$output .= '<div style="padding: 1.5rem; background: #f9fafb; border-radius: 8px; border-left: 4px solid #4facfe;">';
	$output .= '<div style="font-size: 0.875rem; color: #666; margin-bottom: 0.5rem;">🎟️ Raffles</div>';
	$output .= '<div style="font-size: 1.5rem; font-weight: bold; color: #4facfe;">${{ analytics.raffle_revenue.toLocaleString() }}</div>';
	$output .= '<div style="font-size: 0.875rem; color: #666; margin-top: 0.5rem;">{{ rafflePercentage }}% of total</div>';
	$output .= '</div>';
	
	$output .= '</div>';
	$output .= '</div>';
	
	// Export section
	$output .= '<div style="background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">';
	$output .= '<h3 style="margin: 0 0 1rem 0;">Export Data</h3>';
	$output .= '<p style="color: #666; margin-bottom: 1.5rem;">Download your campaign data for external analysis.</p>';
	$output .= '<div style="display: flex; gap: 1rem;">';
	$output .= '<a :href="exportUrl + \'?format=csv\'" style="padding: 0.75rem 1.5rem; background: #10b981; color: white; text-decoration: none; border-radius: 6px; font-weight: 600;">📄 Export CSV</a>';
	$output .= '</div>';
	$output .= '</div>';
	
	$output .= '</div>';
	$output .= '</div>';
	
	// Chart.js and Vue.js app
	$output .= '<script>';
	$output .= 'if (typeof Vue !== "undefined" && typeof Chart !== "undefined") {';
	$output .= 'const { createApp } = Vue;';
	$output .= 'createApp({';
	$output .= 'data() {';
	$output .= 'return {';
	$output .= 'loading: true,';
	$output .= 'analytics: {';
	$output .= 'total_raised: 0,';
	$output .= 'donation_revenue: 0,';
	$output .= 'product_revenue: 0,';
	$output .= 'raffle_revenue: 0,';
	$output .= 'total_donors: 0,';
	$output .= 'campaign_views: 0,';
	$output .= 'progress_over_time: []';
	$output .= '},';
	$output .= 'revenueChart: null,';
	$output .= 'progressChart: null';
	$output .= '};';
	$output .= '},';
	$output .= 'computed: {';
	$output .= 'avgDonation() {';
	$output .= 'return this.analytics.total_donors > 0 ? (this.analytics.total_raised / this.analytics.total_donors).toFixed(2) : "0.00";';
	$output .= '},';
	$output .= 'donationPercentage() {';
	$output .= 'return this.analytics.total_raised > 0 ? ((this.analytics.donation_revenue / this.analytics.total_raised) * 100).toFixed(1) : 0;';
	$output .= '},';
	$output .= 'productPercentage() {';
	$output .= 'return this.analytics.total_raised > 0 ? ((this.analytics.product_revenue / this.analytics.total_raised) * 100).toFixed(1) : 0;';
	$output .= '},';
	$output .= 'rafflePercentage() {';
	$output .= 'return this.analytics.total_raised > 0 ? ((this.analytics.raffle_revenue / this.analytics.total_raised) * 100).toFixed(1) : 0;';
	$output .= '},';
	$output .= 'exportUrl() {';
	$output .= 'return fundraiserData.apiUrl + "campaigns/' . $campaign_id . '/export";';
	$output .= '}';
	$output .= '},';
	$output .= 'async mounted() {';
	$output .= 'await this.loadAnalytics();';
	$output .= 'this.createCharts();';
	$output .= '},';
	$output .= 'methods: {';
	$output .= 'async loadAnalytics() {';
	$output .= 'try {';
	$output .= 'const response = await fetch(fundraiserData.apiUrl + "analytics/campaign/' . $campaign_id . '", {';
	$output .= 'headers: { "X-WP-Nonce": fundraiserData.nonce }';
	$output .= '});';
	$output .= 'if (response.ok) {';
	$output .= 'this.analytics = await response.json();';
	$output .= '}';
	$output .= '} catch (error) {';
	$output .= 'console.error("Error loading analytics:", error);';
	$output .= '} finally {';
	$output .= 'this.loading = false;';
	$output .= '}';
	$output .= '},';
	$output .= 'createCharts() {';
	$output .= 'this.$nextTick(() => {';
	$output .= 'const revenueCtx = document.getElementById("revenue-chart");';
	$output .= 'const progressCtx = document.getElementById("progress-chart");';
	$output .= 'if (!revenueCtx || !progressCtx) return;';
	$output .= 'this.revenueChart = new Chart(revenueCtx, {';
	$output .= 'type: "doughnut",';
	$output .= 'data: {';
	$output .= 'labels: ["Donations", "Products", "Raffles"],';
	$output .= 'datasets: [{';
	$output .= 'data: [this.analytics.donation_revenue, this.analytics.product_revenue, this.analytics.raffle_revenue],';
	$output .= 'backgroundColor: ["#667eea", "#f093fb", "#4facfe"],';
	$output .= 'borderWidth: 0';
	$output .= '}]';
	$output .= '},';
	$output .= 'options: {';
	$output .= 'responsive: true,';
	$output .= 'maintainAspectRatio: true,';
	$output .= 'plugins: {';
	$output .= 'legend: { position: "bottom" }';
	$output .= '}';
	$output .= '}';
	$output .= '});';
	$output .= 'const progressData = this.analytics.progress_over_time || [];';
	$output .= 'this.progressChart = new Chart(progressCtx, {';
	$output .= 'type: "line",';
	$output .= 'data: {';
	$output .= 'labels: progressData.map(d => d.date),';
	$output .= 'datasets: [{';
	$output .= 'label: "Total Raised",';
	$output .= 'data: progressData.map(d => d.daily_total),';
	$output .= 'borderColor: "#667eea",';
	$output .= 'backgroundColor: "rgba(102, 126, 234, 0.1)",';
	$output .= 'tension: 0.4,';
	$output .= 'fill: true';
	$output .= '}]';
	$output .= '},';
	$output .= 'options: {';
	$output .= 'responsive: true,';
	$output .= 'maintainAspectRatio: true,';
	$output .= 'plugins: {';
	$output .= 'legend: { display: false }';
	$output .= '},';
	$output .= 'scales: {';
	$output .= 'y: { beginAtZero: true }';
	$output .= '}';
	$output .= '}';
	$output .= '});';
	$output .= '});';
	$output .= '}';
	$output .= '}';
	$output .= '}).mount("#reports-app");';
	$output .= '}';
	$output .= '/* ]]> */';
	$output .= '</script>';
	
	return $output;
}
add_shortcode('campaign_reports_tab', 'campaign_reports_tab_shortcode');


// ==================== RAFFLE MANAGEMENT SHORTCODES ====================

/**
 * Shortcode: Campaign Raffles Tab
 */
function campaign_raffles_tab_shortcode($atts) {
	$atts = shortcode_atts(array('campaign_id' => 0), $atts);
	$campaign_id = intval($atts['campaign_id']);
	
	if (!$campaign_id) {
		return '<p>No campaign specified.</p>';
	}
	
	global $wpdb;
	$campaign = get_post($campaign_id);

	// Get raffles for this campaign from custom table
	$raffles = $wpdb->get_results($wpdb->prepare(
		"SELECT * FROM {$wpdb->prefix}fundraiser_raffles WHERE campaign_id = %d ORDER BY created_at DESC",
		$campaign_id
	));
	
	$output = '<h2 style="margin: 0 0 1.5rem 0;">Raffles</h2>';
	
	$output .= '<div id="raffles-tab-app">';
	
	// Create raffle button
	$output .= '<div style="margin-bottom: 2rem;">';
	$output .= '<button @click="showCreateForm = !showCreateForm" style="padding: 0.75rem 1.5rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">';
	$output .= '<span v-if="!showCreateForm">+ Create New Raffle</span>';
	$output .= '<span v-else>✖ Cancel</span>';
	$output .= '</button>';
	$output .= '</div>';
	
	// Create raffle form
	$output .= '<div v-show="showCreateForm" style="background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 2rem;">';
	$output .= '<h3 style="margin: 0 0 1.5rem 0;">Create New Raffle</h3>';
	
	$output .= '<div style="display: grid; gap: 1rem;">';
	
	$output .= '<div>';
	$output .= '<label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Raffle Title *</label>';
	$output .= '<input type="text" v-model="newRaffle.title" style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px;" placeholder="e.g., Grand Prize Drawing">';
	$output .= '</div>';
	
	$output .= '<div>';
	$output .= '<label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Prize Description *</label>';
	$output .= '<textarea v-model="newRaffle.prize" rows="3" style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; resize: vertical;" placeholder="Describe the prize..."></textarea>';
	$output .= '</div>';
	
	$output .= '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">';
	
	$output .= '<div>';
	$output .= '<label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Ticket Price ($) *</label>';
	$output .= '<input type="number" v-model="newRaffle.ticket_price" step="0.01" min="0" style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px;" placeholder="5.00">';
	$output .= '</div>';
	
	$output .= '<div>';
	$output .= '<label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Total Tickets *</label>';
	$output .= '<input type="number" v-model="newRaffle.total_tickets" min="1" style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px;" placeholder="100">';
	$output .= '</div>';
	
	$output .= '<div>';
	$output .= '<label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Drawing Date *</label>';
	$output .= '<input type="date" v-model="newRaffle.draw_date" style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px;">';
	$output .= '</div>';
	
	$output .= '</div>';
	
	$output .= '<div>';
	$output .= '<button @click="createRaffle" :disabled="creating" style="padding: 0.75rem 2rem; background: #10b981; color: white; border: none; border-radius: 6px; font-weight: 600; cursor: pointer;">';
	$output .= '<span v-if="!creating">Create Raffle</span>';
	$output .= '<span v-else>Creating...</span>';
	$output .= '</button>';
	$output .= '<div v-if="createMessage" :style="{marginTop: \'1rem\', padding: \'0.75rem\', borderRadius: \'6px\', background: createMessageType === \'success\' ? \'#d1fae5\' : \'#fee2e2\', color: createMessageType === \'success\' ? \'#065f46\' : \'#991b1b\'}">{{ createMessage }}</div>';
	$output .= '</div>';
	
	$output .= '</div>';
	$output .= '</div>';
	
	// Raffles list
	if (empty($raffles)) {
		$output .= '<div style="text-align: center; padding: 4rem 2rem; background: white; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">';
		$output .= '<div style="font-size: 4rem; margin-bottom: 1rem;">🎟️</div>';
		$output .= '<h3 style="margin: 0 0 1rem 0;">No raffles yet</h3>';
		$output .= '<p style="color: #666; margin-bottom: 2rem;">Create your first raffle to start selling tickets.</p>';
		$output .= '</div>';
	} else {
		$output .= '<div style="display: grid; gap: 1.5rem;">';
		
		foreach ($raffles as $raffle) {
			$raffle_id = $raffle->id;
			$prize = $raffle->prize_details;
			$ticket_price = floatval($raffle->ticket_price);
			$total_tickets = intval($raffle->total_tickets);
			$tickets_sold = intval($raffle->tickets_sold);
			$draw_date = $raffle->draw_date;

			$progress = $total_tickets > 0 ? ($tickets_sold / $total_tickets) * 100 : 0;
			$revenue = $tickets_sold * $ticket_price;

			$status = ($raffle->status === 'active') ? 'Active' : 'Draft';
			$status_color = ($raffle->status === 'active') ? '#10b981' : '#6b7280';

			$output .= '<div style="background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">';

			// Header
			$output .= '<div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1.5rem;">';
			$output .= '<div style="flex: 1;">';
			$output .= '<h3 style="margin: 0 0 0.5rem 0; font-size: 1.5rem;">' . esc_html($raffle->title) . '</h3>';
			$output .= '<div style="display: inline-block; padding: 0.25rem 0.75rem; background: ' . $status_color . '; color: white; border-radius: 4px; font-size: 0.875rem; font-weight: 600;">' . $status . '</div>';
			$output .= '</div>';
			$output .= '</div>';

			// Prize info
			if ($prize) {
				$output .= '<div style="padding: 1rem; background: #f9fafb; border-radius: 8px; margin-bottom: 1.5rem;">';
				$output .= '<div style="font-weight: 600; margin-bottom: 0.5rem;">🏆 Prize</div>';
				$output .= '<div style="color: #666;">' . esc_html($prize) . '</div>';
				$output .= '</div>';
			}
			
			// Stats grid
			$output .= '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">';
			
			$output .= '<div>';
			$output .= '<div style="font-size: 0.875rem; color: #666; margin-bottom: 0.25rem;">Ticket Price</div>';
			$output .= '<div style="font-size: 1.25rem; font-weight: bold;">$' . number_format($ticket_price, 2) . '</div>';
			$output .= '</div>';
			
			$output .= '<div>';
			$output .= '<div style="font-size: 0.875rem; color: #666; margin-bottom: 0.25rem;">Tickets Sold</div>';
			$output .= '<div style="font-size: 1.25rem; font-weight: bold;">' . $tickets_sold . ' / ' . $total_tickets . '</div>';
			$output .= '</div>';
			
			$output .= '<div>';
			$output .= '<div style="font-size: 0.875rem; color: #666; margin-bottom: 0.25rem;">Revenue</div>';
			$output .= '<div style="font-size: 1.25rem; font-weight: bold; color: #10b981;">$' . number_format($revenue, 2) . '</div>';
			$output .= '</div>';
			
			if ($draw_date) {
				$output .= '<div>';
				$output .= '<div style="font-size: 0.875rem; color: #666; margin-bottom: 0.25rem;">Drawing Date</div>';
				$output .= '<div style="font-size: 1.25rem; font-weight: bold;">' . date('M j, Y', strtotime($draw_date)) . '</div>';
				$output .= '</div>';
			}
			
			$output .= '</div>';
			
			// Progress bar
			$output .= '<div style="margin-bottom: 1.5rem;">';
			$output .= '<div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem; font-size: 0.875rem;">';
			$output .= '<span style="font-weight: 600;">Sales Progress</span>';
			$output .= '<span style="color: #666;">' . round($progress, 1) . '%</span>';
			$output .= '</div>';
			$output .= '<div style="height: 8px; background: #e5e7eb; border-radius: 4px; overflow: hidden;">';
			$output .= '<div style="height: 100%; background: linear-gradient(90deg, #4facfe 0%, #00f2fe 100%); width: ' . $progress . '%;"></div>';
			$output .= '</div>';
			$output .= '</div>';
			
			// Action buttons
			$output .= '<div style="display: flex; gap: 0.5rem;">';
			$output .= '<a href="' . esc_url(admin_url('post.php?post=' . $raffle_id . '&action=edit')) . '" style="padding: 0.5rem 1rem; background: #667eea; color: white; text-decoration: none; border-radius: 6px; font-size: 0.875rem; font-weight: 600;">Edit Details</a>';
			$output .= '<a href="' . esc_url(home_url('/campaign-detail/?campaign_id=' . $campaign_id . '&tab=manual-entry')) . '" style="padding: 0.5rem 1rem; background: #f093fb; color: white; text-decoration: none; border-radius: 6px; font-size: 0.875rem; font-weight: 600;">Record Sales</a>';
			$output .= '</div>';
			
			$output .= '</div>';
		}
		
		$output .= '</div>';
	}
	
	$output .= '</div>';
	
	// Vue.js app
	$output .= '<script type="text/javascript">';
	$output .= '/* <![CDATA[ */';
	$output .= 'if (typeof Vue !== "undefined") {';
	$output .= 'const { createApp } = Vue;';
	$output .= 'createApp({';
	$output .= 'data() {';
	$output .= 'return {';
	$output .= 'showCreateForm: false,';
	$output .= 'newRaffle: {';
	$output .= 'campaign_id: ' . $campaign_id . ',';
	$output .= 'title: "",';
	$output .= 'prize: "",';
	$output .= 'ticket_price: 5,';
	$output .= 'total_tickets: 100,';
	$output .= 'draw_date: ""';
	$output .= '},';
	$output .= 'creating: false,';
	$output .= 'createMessage: "",';
	$output .= 'createMessageType: "success"';
	$output .= '};';
	$output .= '},';
	$output .= 'methods: {';
	$output .= 'async createRaffle() {';
	$output .= 'if (!this.newRaffle.title || !this.newRaffle.prize || !this.newRaffle.ticket_price || !this.newRaffle.total_tickets) {';
	$output .= 'this.createMessage = "Please fill in all required fields";';
	$output .= 'this.createMessageType = "error";';
	$output .= 'return;';
	$output .= '}';
	$output .= 'this.creating = true;';
	$output .= 'this.createMessage = "";';
	$output .= 'try {';
	$output .= 'const formData = new FormData();';
	$output .= 'formData.append("action", "create_raffle");';
	$output .= 'formData.append("campaign_id", this.newRaffle.campaign_id);';
	$output .= 'formData.append("title", this.newRaffle.title);';
	$output .= 'formData.append("prize", this.newRaffle.prize);';
	$output .= 'formData.append("ticket_price", this.newRaffle.ticket_price);';
	$output .= 'formData.append("total_tickets", this.newRaffle.total_tickets);';
	$output .= 'formData.append("draw_date", this.newRaffle.draw_date);';
	$output .= 'formData.append("nonce", fundraiserData.nonce);';
	$output .= 'const response = await fetch(fundraiserData.homeUrl + "/wp-admin/admin-ajax.php", {';
	$output .= 'method: "POST",';
	$output .= 'body: formData';
	$output .= '});';
	$output .= 'const data = await response.json();';
	$output .= 'if (data.success) {';
	$output .= 'this.createMessage = "Raffle created successfully!";';
	$output .= 'this.createMessageType = "success";';
	$output .= 'setTimeout(() => { location.reload(); }, 1500);';
	$output .= '} else {';
	$output .= 'this.createMessage = data.data || "Failed to create raffle";';
	$output .= 'this.createMessageType = "error";';
	$output .= '}';
	$output .= '} catch (error) {';
	$output .= 'this.createMessage = "Error: " + error.message;';
	$output .= 'this.createMessageType = "error";';
	$output .= '} finally {';
	$output .= 'this.creating = false;';
	$output .= '}';
	$output .= '}';
	$output .= '}';
	$output .= '}).mount("#raffles-tab-app");';
	$output .= '}';
	$output .= '/* ]]> */';
	$output .= '</script>';
	
	return $output;
}
add_shortcode('campaign_raffles_tab', 'campaign_raffles_tab_shortcode');

/**
 * AJAX handler for creating raffles
 */
function fundraiser_ajax_create_raffle() {
	check_ajax_referer('wp_rest', 'nonce');
	
	if (!current_user_can('edit_posts')) {
		wp_send_json_error('Insufficient permissions');
	}
	
	$campaign_id = intval($_POST['campaign_id']);
	$title = sanitize_text_field($_POST['title']);
	$prize = sanitize_textarea_field($_POST['prize']);
	$ticket_price = floatval($_POST['ticket_price']);
	$total_tickets = intval($_POST['total_tickets']);
	$draw_date = sanitize_text_field($_POST['draw_date']);
	
	// Verify campaign ownership
	$campaign = get_post($campaign_id);
	if (!$campaign || $campaign->post_author != get_current_user_id()) {
		wp_send_json_error('You do not own this campaign');
	}
	
	// Create raffle post
	$raffle_id = wp_insert_post(array(
		'post_type' => 'fundraiser_raffle',
		'post_title' => $title,
		'post_status' => 'publish',
		'post_author' => get_current_user_id(),
	));
	
	if (is_wp_error($raffle_id)) {
		wp_send_json_error($raffle_id->get_error_message());
	}
	
	// Save meta data
	update_post_meta($raffle_id, 'campaign_id', $campaign_id);
	update_post_meta($raffle_id, 'prize_details', $prize);
	update_post_meta($raffle_id, 'ticket_price', $ticket_price);
	update_post_meta($raffle_id, 'total_tickets', $total_tickets);
	if ($draw_date) {
		update_post_meta($raffle_id, 'draw_date', $draw_date);
	}
	
	wp_send_json_success(array('raffle_id' => $raffle_id));
}
add_action('wp_ajax_create_raffle', 'fundraiser_ajax_create_raffle');


// ==================== PAGE EDITOR SHORTCODE ====================

/**
 * Shortcode: Campaign Page Editor Tab
 */
function campaign_page_editor_tab_shortcode($atts) {
	$atts = shortcode_atts(array('campaign_id' => 0), $atts);
	$campaign_id = intval($atts['campaign_id']);
	
	if (!$campaign_id) {
		return '<p>No campaign specified.</p>';
	}
	
	$campaign = get_post($campaign_id);
	
	// Get the campaign's page
	$page_id = get_post_meta($campaign_id, '_campaign_page_id', true);
	
	$output = '<h2 style="margin: 0 0 1.5rem 0;">Campaign Page Editor</h2>';
	
	if (!$page_id) {
		$output .= '<div style="text-align: center; padding: 4rem 2rem; background: #f9fafb; border-radius: 8px;">';
		$output .= '<div style="font-size: 4rem; margin-bottom: 1rem;">📄</div>';
		$output .= '<h3 style="margin: 0 0 1rem 0;">No page created yet</h3>';
		$output .= '<p style="color: #666; margin-bottom: 2rem;">A campaign page will be automatically created when you publish your campaign.</p>';
		$output .= '</div>';
		return $output;
	}
	
	$page = get_post($page_id);
	if (!$page) {
		return '<p>Campaign page not found.</p>';
	}
	
	$output .= '<div id="page-editor-app">';
	
	// Info box
	$output .= '<div style="padding: 1.5rem; background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 8px; margin-bottom: 2rem;">';
	$output .= '<div style="font-weight: 600; margin-bottom: 0.5rem;">ℹ️ About the Page Editor</div>';
	$output .= '<div style="color: #0c4a6e; font-size: 0.875rem;">Edit the basic content of your campaign landing page. For advanced editing, use the "Full Editor" link below.</div>';
	$output .= '</div>';
	
	// Page settings
	$output .= '<div style="background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 2rem;">';
	$output .= '<h3 style="margin: 0 0 1.5rem 0;">Page Settings</h3>';
	
	$output .= '<div style="margin-bottom: 1.5rem;">';
	$output .= '<label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Page Title</label>';
	$output .= '<input type="text" v-model="pageData.title" style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 1rem;">';
	$output .= '</div>';
	
	$output .= '<div style="margin-bottom: 1.5rem;">';
	$output .= '<label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Page URL Slug</label>';
	$output .= '<div style="display: flex; align-items: center; gap: 0.5rem;">';
	$output .= '<span style="color: #666; font-size: 0.875rem;">' . home_url('/') . '</span>';
	$output .= '<input type="text" v-model="pageData.slug" style="flex: 1; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 1rem;">';
	$output .= '</div>';
	$output .= '<div style="color: #666; font-size: 0.875rem; margin-top: 0.5rem;">Only use lowercase letters, numbers, and hyphens</div>';
	$output .= '</div>';
	
	$output .= '<div>';
	$output .= '<label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Page Status</label>';
	$output .= '<select v-model="pageData.status" style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 1rem;">';
	$output .= '<option value="publish">Published (Visible to public)</option>';
	$output .= '<option value="draft">Draft (Not visible)</option>';
	$output .= '</select>';
	$output .= '</div>';
	
	$output .= '</div>';
	
	// Content editor
	$output .= '<div style="background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 2rem;">';
	$output .= '<h3 style="margin: 0 0 1.5rem 0;">Page Content</h3>';
	
	$output .= '<div style="margin-bottom: 1rem;">';
	$output .= '<textarea v-model="pageData.content" rows="15" style="width: 100%; padding: 1rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 1rem; font-family: monospace; resize: vertical;"></textarea>';
	$output .= '<div style="color: #666; font-size: 0.875rem; margin-top: 0.5rem;">You can use basic HTML tags here. The campaign details (goal, progress, donation button) are automatically displayed.</div>';
	$output .= '</div>';
	
	$output .= '</div>';
	
	// Action buttons
	$output .= '<div style="display: flex; gap: 1rem; flex-wrap: wrap;">';
	$output .= '<button @click="savePage" :disabled="saving" style="padding: 1rem 2rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">';
	$output .= '<span v-if="!saving">💾 Save Changes</span>';
	$output .= '<span v-else>⏳ Saving...</span>';
	$output .= '</button>';
	
	$output .= '<a :href="pageUrl" target="_blank" style="padding: 1rem 2rem; background: #f3f4f6; color: #374151; text-decoration: none; border-radius: 8px; font-weight: 600; display: inline-flex; align-items: center;">👁️ Preview Page</a>';
	
	$output .= '<a href="' . esc_url(admin_url('post.php?post=' . $page_id . '&action=edit')) . '" style="padding: 1rem 2rem; background: #e5e7eb; color: #374151; text-decoration: none; border-radius: 8px; font-weight: 600; display: inline-flex; align-items: center;">✏️ Full Editor</a>';
	
	$output .= '</div>';
	
	$output .= '<div v-if="message" :style="{marginTop: \'1rem\', padding: \'1rem\', borderRadius: \'6px\', background: messageType === \'success\' ? \'#d1fae5\' : \'#fee2e2\', color: messageType === \'success\' ? \'#065f46\' : \'#991b1b\'}">{{ message }}</div>';

	$output .= '</div>';

	// Enqueue external Vue.js app script
	wp_enqueue_script(
		'page-editor',
		get_template_directory_uri() . '/assets/js/page-editor.js',
		array('vue'),
		'1.0.0',
		true
	);

	// Localize script data for the page editor (must be after enqueue)
	wp_localize_script('page-editor', 'pageEditorData', array(
		'page' => array(
			'title' => $page->post_title,
			'slug' => $page->post_name,
			'content' => $page->post_content,
			'status' => $page->post_status
		),
		'pageId' => $page_id,
		'homeUrl' => home_url(),
		'nonce' => wp_create_nonce('wp_rest')
	));
	
	return $output;
}
add_shortcode('campaign_page_editor_tab', 'campaign_page_editor_tab_shortcode');


// ==================== STANDALONE PAGES ====================

/**
 * Shortcode: Standalone Manual Entry Page
 */
function standalone_manual_entry_shortcode($atts) {
	if (!is_user_logged_in()) {
		return '<p>Please log in to record manual entries.</p>';
	}
	
	$user_id = get_current_user_id();
	
	// Get user's campaigns
	$campaigns = get_posts(array(
		'post_type' => 'fundraiser_campaign',
		'author' => $user_id,
		'post_status' => 'publish',
		'posts_per_page' => -1,
	));
	
	$output = '';
	
	// Navigation menu
	$output .= fundraiser_dashboard_menu();
	
	// Page header
	$output .= '<div style="margin-bottom: 2rem;">';
	$output .= '<h1 style="margin: 0 0 0.5rem 0; font-size: 2rem;">Manual Entry</h1>';
	$output .= '<p style="color: #666; font-size: 1.1rem;">Record in-person cash payments and raffle ticket sales in bulk.</p>';
	$output .= '</div>';
	
	if (empty($campaigns)) {
		$output .= '<div style="text-align: center; padding: 4rem 2rem; background: white; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">';
		$output .= '<div style="font-size: 4rem; margin-bottom: 1rem;">📋</div>';
		$output .= '<h3 style="margin: 0 0 1rem 0;">No campaigns found</h3>';
		$output .= '<p style="color: #666; margin-bottom: 2rem;">Create a campaign first to record manual entries.</p>';
		$output .= '<a href="' . esc_url(home_url('/campaign-wizard/')) . '" style="display: inline-block; padding: 1rem 2rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; text-decoration: none; border-radius: 8px; font-weight: 600;">Create Campaign</a>';
		$output .= '</div>';
		return $output;
	}
	
	$output .= '<div style="background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">';
	
	// Campaign selector
	$output .= '<div style="margin-bottom: 2rem; padding-bottom: 2rem; border-bottom: 2px solid #e5e7eb;">';
	$output .= '<label style="display: block; font-weight: 600; margin-bottom: 1rem; font-size: 1.25rem;">Select Campaign</label>';
	$output .= '<select onchange="window.location.href=\'' . esc_url(home_url('/campaign-detail/')) . '?campaign_id=\'+this.value+\'&tab=manual-entry\'" style="width: 100%; max-width: 500px; padding: 1rem; border: 2px solid #d1d5db; border-radius: 8px; font-size: 1rem;">';
	$output .= '<option value="">-- Choose a campaign --</option>';
	foreach ($campaigns as $campaign) {
		$output .= '<option value="' . $campaign->ID . '">' . esc_html($campaign->post_title) . '</option>';
	}
	$output .= '</select>';
	$output .= '<div style="color: #666; font-size: 0.875rem; margin-top: 1rem;">💡 Tip: Use the campaign detail page for quick single entries. This page is for bulk data entry.</div>';
	$output .= '</div>';
	
	// Quick access cards
	$output .= '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">';
	
	foreach ($campaigns as $campaign) {
		$campaign_id = $campaign->ID;
		$page_id = get_post_meta($campaign_id, '_campaign_page_id', true);
		$page_url = $page_id ? get_permalink($page_id) : '#';
		
		$output .= '<div style="padding: 2rem; background: linear-gradient(135deg, #f9fafb 0%, #e5e7eb 100%); border-radius: 12px; border-left: 4px solid #667eea;">';
		$output .= '<h3 style="margin: 0 0 1rem 0; font-size: 1.25rem;">' . esc_html($campaign->post_title) . '</h3>';
		$output .= '<div style="display: flex; flex-direction: column; gap: 0.75rem;">';
		$output .= '<a href="' . esc_url(home_url('/campaign-detail/?campaign_id=' . $campaign_id . '&tab=manual-entry')) . '" style="padding: 0.75rem 1rem; background: #667eea; color: white; text-decoration: none; border-radius: 6px; text-align: center; font-weight: 600;">Record Entries</a>';
		$output .= '<a href="' . esc_url($page_url) . '" target="_blank" style="padding: 0.75rem 1rem; background: white; color: #667eea; text-decoration: none; border-radius: 6px; text-align: center; font-weight: 600; border: 2px solid #667eea;">View Campaign</a>';
		$output .= '</div>';
		$output .= '</div>';
	}
	
	$output .= '</div>';
	$output .= '</div>';
	
	return $output;
}
add_shortcode('standalone_manual_entry', 'standalone_manual_entry_shortcode');

/**
 * Shortcode: Global Reports Dashboard
 */
function global_reports_dashboard_shortcode($atts) {
	if (!is_user_logged_in()) {
		return '<p>Please log in to view reports.</p>';
	}
	
	$user_id = get_current_user_id();
	global $wpdb;
	
	// Get user's campaigns
	$campaigns = get_posts(array(
		'post_type' => 'fundraiser_campaign',
		'author' => $user_id,
		'post_status' => 'publish',
		'posts_per_page' => -1,
		'fields' => 'ids',
	));
	
	$output = '';
	
	// Navigation menu
	$output .= fundraiser_dashboard_menu();
	
	// Page header
	$output .= '<div style="margin-bottom: 2rem;">';
	$output .= '<h1 style="margin: 0 0 0.5rem 0; font-size: 2rem;">All Campaign Reports</h1>';
	$output .= '<p style="color: #666; font-size: 1.1rem;">View analytics across all your campaigns.</p>';
	$output .= '</div>';
	
	if (empty($campaigns)) {
		$output .= '<div style="text-align: center; padding: 4rem 2rem; background: white; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">';
		$output .= '<div style="font-size: 4rem; margin-bottom: 1rem;">📊</div>';
		$output .= '<h3 style="margin: 0 0 1rem 0;">No campaigns to report on</h3>';
		$output .= '<p style="color: #666; margin-bottom: 2rem;">Create a campaign to start tracking analytics.</p>';
		$output .= '<a href="' . esc_url(home_url('/campaign-wizard/')) . '" style="display: inline-block; padding: 1rem 2rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; text-decoration: none; border-radius: 8px; font-weight: 600;">Create Campaign</a>';
		$output .= '</div>';
		return $output;
	}
	
	// Calculate totals
	$total_raised = 0;
	$total_donors = 0;
	$campaign_data = array();
	
	foreach ($campaigns as $campaign_id) {
		$analytics = $wpdb->get_row($wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}fundraiser_campaign_analytics WHERE campaign_id = %d",
			$campaign_id
		));
		
		if ($analytics) {
			$total_raised += floatval($analytics->total_raised);
			$total_donors += intval($analytics->total_donors);
			
			$campaign = get_post($campaign_id);
			$campaign_data[] = array(
				'id' => $campaign_id,
				'title' => $campaign->post_title,
				'raised' => floatval($analytics->total_raised),
				'goal' => floatval(get_post_meta($campaign_id, 'fundraiser_goal', true)),
				'donors' => intval($analytics->total_donors),
			);
		}
	}
	
	// Sort by amount raised
	usort($campaign_data, function($a, $b) {
		return $b['raised'] - $a['raised'];
	});
	
	// Global statistics
	$output .= '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">';
	
	$output .= '<div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">';
	$output .= '<div style="font-size: 0.875rem; opacity: 0.9; margin-bottom: 0.5rem;">Total Raised (All Campaigns)</div>';
	$output .= '<div style="font-size: 2.5rem; font-weight: bold;">$' . number_format($total_raised, 2) . '</div>';
	$output .= '</div>';
	
	$output .= '<div style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">';
	$output .= '<div style="font-size: 0.875rem; opacity: 0.9; margin-bottom: 0.5rem;">Active Campaigns</div>';
	$output .= '<div style="font-size: 2.5rem; font-weight: bold;">' . count($campaigns) . '</div>';
	$output .= '</div>';
	
	$output .= '<div style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">';
	$output .= '<div style="font-size: 0.875rem; opacity: 0.9; margin-bottom: 0.5rem;">Total Donors</div>';
	$output .= '<div style="font-size: 2.5rem; font-weight: bold;">' . $total_donors . '</div>';
	$output .= '</div>';
	
	$avg_per_campaign = count($campaigns) > 0 ? $total_raised / count($campaigns) : 0;
	$output .= '<div style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); color: white; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">';
	$output .= '<div style="font-size: 0.875rem; opacity: 0.9; margin-bottom: 0.5rem;">Avg per Campaign</div>';
	$output .= '<div style="font-size: 2.5rem; font-weight: bold;">$' . number_format($avg_per_campaign, 0) . '</div>';
	$output .= '</div>';
	
	$output .= '</div>';
	
	// Campaign performance table
	$output .= '<div style="background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">';
	$output .= '<h2 style="margin: 0 0 1.5rem 0;">Campaign Performance</h2>';
	
	$output .= '<div style="overflow-x: auto;">';
	$output .= '<table style="width: 100%; border-collapse: collapse;">';
	$output .= '<thead>';
	$output .= '<tr style="background: #f9fafb; border-bottom: 2px solid #e5e7eb;">';
	$output .= '<th style="padding: 1rem; text-align: left;">Campaign</th>';
	$output .= '<th style="padding: 1rem; text-align: right;">Goal</th>';
	$output .= '<th style="padding: 1rem; text-align: right;">Raised</th>';
	$output .= '<th style="padding: 1rem; text-align: center;">Progress</th>';
	$output .= '<th style="padding: 1rem; text-align: center;">Donors</th>';
	$output .= '<th style="padding: 1rem; text-align: center;">Actions</th>';
	$output .= '</tr>';
	$output .= '</thead>';
	$output .= '<tbody>';
	
	foreach ($campaign_data as $campaign) {
		$progress = $campaign['goal'] > 0 ? min(100, ($campaign['raised'] / $campaign['goal']) * 100) : 0;
		$progress_color = $progress >= 100 ? '#10b981' : ($progress >= 75 ? '#f59e0b' : '#3b82f6');
		
		$output .= '<tr style="border-bottom: 1px solid #e5e7eb;">';
		$output .= '<td style="padding: 1rem; font-weight: 600;">' . esc_html($campaign['title']) . '</td>';
		$output .= '<td style="padding: 1rem; text-align: right;">$' . number_format($campaign['goal'], 0) . '</td>';
		$output .= '<td style="padding: 1rem; text-align: right; font-weight: 600; color: #10b981;">$' . number_format($campaign['raised'], 0) . '</td>';
		$output .= '<td style="padding: 1rem; text-align: center;">';
		$output .= '<div style="display: inline-flex; align-items: center; gap: 0.5rem; min-width: 100px;">';
		$output .= '<div style="flex: 1; height: 8px; background: #e5e7eb; border-radius: 4px; overflow: hidden;">';
		$output .= '<div style="height: 100%; background: ' . $progress_color . '; width: ' . $progress . '%;"></div>';
		$output .= '</div>';
		$output .= '<span style="font-size: 0.875rem; font-weight: 600;">' . round($progress, 0) . '%</span>';
		$output .= '</div>';
		$output .= '</td>';
		$output .= '<td style="padding: 1rem; text-align: center;">' . $campaign['donors'] . '</td>';
		$output .= '<td style="padding: 1rem; text-align: center;">';
		$output .= '<a href="' . esc_url(home_url('/campaign-detail/?campaign_id=' . $campaign['id'] . '&tab=reports')) . '" style="padding: 0.5rem 1rem; background: #667eea; color: white; text-decoration: none; border-radius: 6px; font-size: 0.875rem; font-weight: 600; display: inline-block;">View Details</a>';
		$output .= '</td>';
		$output .= '</tr>';
	}
	
	$output .= '</tbody>';
	$output .= '</table>';
	$output .= '</div>';
	
	$output .= '</div>';
	
	return $output;
}
add_shortcode('global_reports_dashboard', 'global_reports_dashboard_shortcode');

