<?php
/*
 *
 * Template Functions
 *
 */

if ( ! function_exists( 'mas_wpjmc_single_company_content_open' ) ) {
    function mas_wpjmc_single_company_content_open() {
        ?><div class="container"><?php
    }
}

if ( ! function_exists( 'mas_wpjmc_single_company_header' ) ) {
    function mas_wpjmc_single_company_header() {
        ?>
        <div class="company-contact-details">
            <div class="company-data">
                <div class="company-logo">
                    <?php $logo =  get_the_company_logo( null, 'thumbnail' ) ? get_the_company_logo( null, 'thumbnail' ) : apply_filters( 'job_manager_default_company_logo', JOB_MANAGER_PLUGIN_URL . '/assets/images/company.png' ); ?>
                    <img src="<?php echo esc_url( $logo ) ?>" class="company-logo--image" alt="<?php the_title(); ?>">
                </div>
                <div class="company-data__content media-body">
                    <?php 
                    the_title( '<h1 class="company-title">', '</h1>' );
                    if( ! empty ( mas_wpjmc_get_the_meta_data( '_company_tagline' ) || ! empty ( mas_wpjmc_get_the_meta_data( '_company_website' ) ) ) || ! empty ( mas_wpjmc_get_the_meta_data( '_company_email' ) ) || ! empty ( mas_wpjmc_get_the_meta_data( '_company_twitter' ) ) || ! empty ( mas_wpjmc_get_the_meta_data( '_company_facebook' ) ) || ! empty ( mas_wpjmc_get_the_meta_data( '_company_phone' ) ) ) {
                        ?>
                        <div class="company-data__content--list">
                            <?php if( ! empty ( $company_tagline = mas_wpjmc_get_the_meta_data( '_company_tagline' ) ) ) : ?>
                                <span class="company-data__content--list-item"><?php echo esc_html( $company_tagline ); ?></span>
                            <?php endif; ?>
                            <?php if( ! empty ( $company_website = mas_wpjmc_get_the_meta_data( '_company_website' ) ) ) : ?>
                                <span class="company-data__content--list-item">
                                    <a href="<?php echo esc_url( $company_website ); ?>" target="_blank">
                                        <?php echo esc_html( $company_website ); ?>
                                    </a>
                                </span>
                            <?php endif; ?>
                            <?php if( ! empty ( $company_email = mas_wpjmc_get_the_meta_data( '_company_email' ) ) ) : ?>
                                <span class="company-data__content--list-item">
                                    <a href="mailto:<?php echo esc_url( $company_email ); ?>" target="_blank"><?php echo esc_html( $company_email ); ?></a>
                                </span>
                            <?php endif; ?>
                            <?php if( ! empty ( $company_twitter = mas_wpjmc_get_the_meta_data( '_company_twitter' ) ) ) : ?>
                                <span class="company-data__content--list-item">
                                    <a href="<?php echo esc_url( $company_twitter ); ?>" target="_blank">
                                        <?php echo esc_html( $company_twitter ); ?>
                                    </a>
                                </span>
                            <?php endif; ?>
                            <?php if( ! empty ( $company_facebook = mas_wpjmc_get_the_meta_data( '_company_facebook' ) ) ) : ?>
                                <span class="company-data__content--list-item">
                                    <a href="<?php echo esc_url( $company_facebook ); ?>" target="_blank">
                                        <?php echo esc_html( $company_facebook ); ?>
                                    </a>
                                </span>
                            <?php endif; ?>
                            <?php if( ! empty ( $company_phone = mas_wpjmc_get_the_meta_data( '_company_phone' ) ) ) : ?>
                                <span class="company-data__content--list-item">
                                    <a href="tel:<?php echo esc_url( $company_phone ); ?>" target="_blank">
                                        <?php echo esc_html( $company_phone ); ?>
                                    </a>
                                </span>
                            <?php endif; ?>
                        </div>
                        <?php
                    }
                    ?>
                </div>
            </div>
        </div>
        <?php
    }
}

if ( ! function_exists( 'mas_wpjmc_single_company_features' ) ) {
    function mas_wpjmc_single_company_features() {
        $args = apply_filters( 'mas_wpjmc_single_company_features_args', array(
            'company_headquarters'  => array(
                'title' => __( 'Headquarters', 'front' ),
                'content' => mas_wpjmc_get_the_meta_data( '_company_headquarters' ),
            ),
            'company_since'  => array(
                'title' => __( 'Founded', 'front' ),
                'content' => mas_wpjmc_get_the_meta_data( '_company_since' ),
            ),
            'company_employees_strength'  => array(
                'title' => __( 'Employees', 'front' ),
                'content' => mas_wpjmc_get_taxomony_data( 'company_employees_strength' ),
            ),
            'company_industry'  => array(
                'title' => __( 'Industry', 'front' ),
                'content' => mas_wpjmc_get_taxomony_data( 'company_industry', null, true ),
            ),
            'company_revenue'  => array(
                'title' => __( 'Revenue', 'front' ),
                'content' => mas_wpjmc_get_taxomony_data( 'company_revenue' ),
            ),
            'company_average_salary'  => array(
                'title' => __( 'Avg. Salary', 'front' ),
                'content' => mas_wpjmc_get_taxomony_data( 'company_average_salary' ),
            ),
        ) );

        if( is_array( $args ) && count( $args ) > 0 ) {
            $i = 0;
            foreach( $args as $key => $arg ) :
                if( isset( $arg['content'] ) && !empty( $arg['content'] ) ) :
                    $i++;
                    break;
                endif;
            endforeach;
            if( $i > 0 ) :
                ?><div class="company-features"><div class="company-features__inner"><?php
                    foreach( $args as $arg ) :
                        if( isset( $arg['content'] ) && !empty( $arg['content'] ) ) :
                        ?>
                            <div class="company-feature">
                                <span class="company-feature__title"><?php echo wp_kses_post( $arg['title'] ); ?></span>
                                <span class="company-feature__content"><?php echo wp_kses_post( $arg['content'] ); ?></span>
                            </div>
                        <?php
                        endif;
                    endforeach;
                ?></div></div><?php
            endif;
        }
    }
}

if ( ! function_exists( 'mas_wpjmc_single_company_description' ) ) {
    function mas_wpjmc_single_company_description() {
        if( ! empty( get_the_content() ) ) {
            ?><div class="company-description"><?php
                the_content();
            ?></div><?php
        }
    }
}

if ( ! function_exists( 'mas_wpjmc_single_company_video' ) ) {
    function mas_wpjmc_single_company_video( $post = null ) {
        $video_embed = false;
        $video       = mas_wpjmc_get_the_meta_data( '_company_video' );
        $filetype    = wp_check_filetype( $video );

        if ( ! empty( $video ) ) {
            // FV WordPress Flowplayer Support for advanced video formats.
            if ( shortcode_exists( 'flowplayer' ) ) {
                $video_embed = '[flowplayer src="' . esc_url( $video ) . '"]';
            } elseif ( ! empty( $filetype['ext'] ) ) {
                $video_embed = wp_video_shortcode( array( 'src' => $video ) );
            } else {
                $video_embed = wp_oembed_get( $video );
            }
        }

        $video_embed = apply_filters( 'the_company_video_embed', $video_embed, $post );

        if ( $video_embed ) {
            echo '<div class="company_video mb-5">' . $video_embed . '</div>'; // WPCS: XSS ok.
        }
    }
}

if ( ! function_exists( 'mas_wpjmc_single_company_content_close' ) ) {
    function mas_wpjmc_single_company_content_close() {
        ?></div><?php
    }
}

if ( ! function_exists( 'mas_wpjmc_company_loop_open' ) ) {
    function mas_wpjmc_company_loop_open() {
        ?><div class="company-inner"><?php
    }
}

if ( ! function_exists( 'mas_wpjmc_company_loop_content' ) ) {
    function mas_wpjmc_company_loop_content() {
        ?>
        <div class="company-logo">
            <?php $logo =  get_the_company_logo( null, 'thumbnail' ) ? get_the_company_logo( null, 'thumbnail' ) : apply_filters( 'job_manager_default_company_logo', JOB_MANAGER_PLUGIN_URL . '/assets/images/company.png' ); ?>
            <img src="<?php echo esc_url( $logo ) ?>" class="company-logo--image" alt="<?php the_title(); ?>">
        </div>
        <div class="company-body">
            <h3 class="company-title">
                <a href="<?php the_permalink(); ?>" class="company-title--link">
                    <?php the_title(); ?>
                </a>
            </h3>
            <div class="company-excerpt">
                <?php the_excerpt(); ?>
            </div>
        </div>
        <?php
    }
}

if ( ! function_exists( 'mas_wpjmc_company_loop_close' ) ) {
    function mas_wpjmc_company_loop_close() {
        ?></div><?php
    }
}

if ( ! function_exists( 'mas_wpjmc_pagination' ) ) {
    function mas_wpjmc_pagination() {
        global $wp_query;
        $total   = isset( $total ) ? $total : mas_wpjmc_get_loop_prop( 'total_pages' );
        $current = isset( $current ) ? $current : mas_wpjmc_get_loop_prop( 'current_page' );
        $base    = isset( $base ) ? $base : esc_url_raw( str_replace( 999999999, '%#%', remove_query_arg( '', get_pagenum_link( 999999999, false ) ) ) );
        $format  = isset( $format ) ? $format : '';
        if ( $total <= 1 ) {
            return;
        }
        ?><nav class="mas-wpjmc-pagination pagination"><?php
            echo paginate_links( apply_filters( 'mas_wpjmc_pagination_args', array( // WPCS: XSS ok.
                'base'         => $base,
                'format'       => $format,
                'add_args'     => false,
                'current'      => max( 1, $current ),
                'total'        => $total,
                'prev_text'    => is_rtl() ? '&rarr;' : '&larr;',
                'next_text'    => is_rtl() ? '&larr;' : '&rarr;',
                'type'         => 'list',
                'end_size'     => 2,
                'mid_size'     => 2,
            ) ) );
        ?></nav><?php
    }
}