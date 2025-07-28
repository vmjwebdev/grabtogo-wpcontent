<?php
if (!current_user_can('seller')) {
    wp_die('Access denied');
}

get_header('dashboard'); ?>

<div class="dashboard-content-container" data-simplebar>
    <div class="dashboard-content-inner">

        <h3>Manage Instant Offers</h3>
        <p>Use this form to add or update limited-time offers on your products.</p>

        <div class="instant-offer-form">
            <?php
            // Only show products owned by current vendor
            $current_user = wp_get_current_user();
            $args = array(
                'post_type'      => 'product',
                'posts_per_page' => -1,
                'author'         => $current_user->ID,
                'post_status'    => 'publish',
            );

            $products = get_posts($args);
            if (!empty($products)) {
                foreach ($products as $product) {
                    echo '<div style="border:1px solid #ddd;padding:15px;margin-bottom:20px;">';
                    echo '<h4>' . esc_html($product->post_title) . '</h4>';

                    // ACF form for the product
                    acf_form(array(
                        'post_id'       => $product->ID,
                        'field_groups'  => array('[frontend_admin group=6870d107e491e]'), // Replace with your ACF group key
                        'submit_value'  => 'Save Offer',
                        'updated_message' => 'Offer updated successfully',
                    ));

                    echo '</div>';
                }
            } else {
                echo '<p>No products found.</p>';
            }
            ?>
        </div>

    </div>
</div>

<?php get_footer('dashboard'); ?>