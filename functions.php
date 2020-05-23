<?php
if (!session_id()){
    session_start();
}

/*
 * CHILD THEME SCRIPTS AND STYLE
 * style.css
*/
add_action( 'wp_enqueue_scripts', 'storefront_child_theme_enqueue_styles' );
function storefront_child_theme_enqueue_styles() {
    wp_enqueue_style( 'storefrontchild-style', get_stylesheet_uri(), array( 'storefront-style' ), wp_get_theme()->get('Version') );
}

/*
 * PLUS 1 TO Product Views Count
*/
function change_product_views_count( $id ){
	
	
	$_SESSION['user-have-watched'][$id] = get_current_user_id();
	
	$currentViews = 0;
	if( get_post_meta( $id, 'product-views', true ) )
		$currentViews = get_post_meta( $id, 'product-views', true );
	
	$newViewsCount = $currentViews + 1;
	
	if( update_post_meta(  $id, 'product-views', $newViewsCount ) )
		return true;
	else
		return false;
	
}

/*
 * action wp_footer
 * CALL change_product_views_count() IF REQUIRED
 * Session NAME user-have-watched
 * [user-have-watched][product_id] == current_iser_id
*/
add_action('wp_footer', 'change_views_count_action');
function change_views_count_action(){
	
	if( is_user_logged_in() ){
		if ( is_product() ){
			
			global $product;
			$id = $product->get_id();
			
			//  check if USER WATCHED THIS PRODUCT
			if( !isset( $_SESSION['user-have-watched'][$id] ) && $_SESSION['user-have-watched'][$id] !== get_current_user_id() ){
				change_product_views_count( $id );
			}
			
			
		}
	}
		
}

/*
 * DELETE SESSIONS ON logout
 * wp_logout action used
*/
function delete_views_count_sessions() {
	unset(  $_SESSION['user-have-watched'] );
}
add_action( 'wp_logout', 'delete_views_count_sessions'  );

/*
 * SAVE THE LAST TIME ORDERED THIS PRODUCT
 * woocommerce_thankyou action used
*/
add_action('woocommerce_thankyou', 'update_last_time_ordered', 10, 1);
function update_last_time_ordered( $order_id ) {
    if ( ! $order_id )
        return;

    // Allow code execution only once 
    if( ! get_post_meta( $order_id, '_thankyou_action_done', true ) ) {

		// Get an instance of the WC_Order object
        $order = wc_get_order( $order_id );

        // Loop through order items
        foreach ( $order->get_items() as $item_id => $item ) {

            // Get the product object
            $product = $item->get_product();

            // Get the product Id
            $product_id = $product->get_id();
			
			update_post_meta(  $product_id, 'last-time-ordered', time() );
        }

    }
}



?>