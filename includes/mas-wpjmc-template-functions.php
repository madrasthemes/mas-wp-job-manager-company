<?php
/*
 *
 * Template Functions
 *
 */

/**
 * Update the get_the_company_name
 */
if ( ! function_exists( 'mas_wpjmc_get_job_listing_company_name' ) ) {
    function mas_wpjmc_get_job_listing_company_name( $company_name, $post ) {
        $company_id = get_post_meta( $post->ID, '_company_id', true );
        if( ! empty( $company_id ) ) {
            $company_name = get_the_title( $company_id );
        }

        return $company_name;
    }
}

if ( ! function_exists( 'mas_wpjmc_edit_submit_job_form_fields' ) ) {
    function mas_wpjmc_edit_submit_job_form_fields( $fields ) {
        $fields['company']['company_name']['label'] = get_option( 'job_manager_job_submission_required_company' ) ? __( 'Branch  Name', 'mas-wp-job-manager-company' ) : esc_html__( 'Company / Branch  Name', 'mas-wp-job-manager-company' ) ;
        $fields['company']['company_name']['required'] = get_option( 'job_manager_job_submission_required_company' ) ? false : true ;
        $fields['company']['company_website']['label'] =get_option( 'job_manager_job_submission_required_company' ) ?  esc_html__( 'Branch Website', 'mas-wp-job-manager-company' ) : esc_html__( 'Company / Branch Website', 'mas-wp-job-manager-company' ) ;
        $fields['company']['company_video']['label'] = get_option( 'job_manager_job_submission_required_company' ) ? esc_html__( 'Branch Video', 'mas-wp-job-manager-company' ) : esc_html__( 'Company / Branch Video', 'mas-wp-job-manager-company' ) ;
        $fields['company']['company_twitter']['label'] =get_option( 'job_manager_job_submission_required_company' ) ?  esc_html__( 'Branch Twitter', 'mas-wp-job-manager-company' ) :  esc_html__( 'Company / Branch Twitter', 'mas-wp-job-manager-company' ) ;
        $fields['company']['company_logo']['label'] = get_option( 'job_manager_job_submission_required_company' ) ? esc_html__( 'Branch Logo', 'mas-wp-job-manager-company' ) : esc_html__( 'Company / Branch Logo', 'mas-wp-job-manager-company' ) ;
        $fields['company']['company_id'] = array(
            'label'         => esc_html__( 'Company', 'mas-wp-job-manager-company' ),
            'type'          => 'select',
            'required'      => get_option( 'job_manager_job_submission_required_company' ) ? true : false,
            'placeholder'   => esc_html__( 'Choose a Company', 'mas-wp-job-manager-company' ),
            'priority'      => 0,
            'options'       => mas_wpjmc()->company->job_manager_get_current_user_companies_select_options(),
        );

        if( get_option( 'job_manager_job_submission_required_company' ) ) {
            unset( $fields['company']['company_tagline'] );
        }

        return $fields;
    }
}

if ( ! function_exists( 'mas_wpjmc_edit_job_listing_search_conditions' ) ) {
    function mas_wpjmc_edit_job_listing_search_conditions( $conditions, $job_manager_keyword ) {
        global $wpdb;
        $conditions[] = "{$wpdb->posts}.ID IN ( SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key IN ( '_company_id' ) AND meta_value Like ( SELECT ID FROM {$wpdb->posts} WHERE post_type IN ( 'company' ) AND post_title LIKE '%" . esc_sql( $job_manager_keyword ) . "%' ) )";

        return $conditions;
    }
}

if ( ! function_exists( 'mas_wpjmc_email_notifications' ) ) {
    function mas_wpjmc_email_notifications( $email_notifications ) {
        $email_notifications[] = 'MAS_WPJMC_Email_Admin_New_Company';
        $email_notifications[] = 'MAS_WPJMC_Email_Admin_Updated_Company';
        return $email_notifications;
    }
}

if ( ! function_exists( 'mas_wpjmc_email_init' ) ) {
    function mas_wpjmc_email_init() {
        include_once mas_wpjmc()->plugin_dir . 'includes/emails/class-mas-wp-job-manager-company-email-admin-new-company.php';
        include_once mas_wpjmc()->plugin_dir . 'includes/emails/class-mas-wp-job-manager-company-email-admin-updated-company.php';
    }
}

if ( ! function_exists( 'mas_wpjmc_send_new_company_notification' ) ) {
    function mas_wpjmc_send_new_company_notification( $company_id ) {
        do_action( 'job_manager_send_notification', 'admin_new_company', [ 'company_id' => $company_id ] );
    }
}

if ( ! function_exists( 'mas_wpjmc_send_updated_company_notification' ) ) {
    function mas_wpjmc_send_updated_company_notification( $company_id ) {
        do_action( 'job_manager_send_notification', 'admin_updated_company', [ 'company_id' => $company_id ] );
    }
}

if ( ! function_exists( 'mas_wpjmc_single_company_content_open' ) ) {
    function mas_wpjmc_single_company_content_open() {
        ?><div class="container"><?php
    }
}

if ( ! function_exists( 'mas_wpjmc_single_company_header' ) ) {
    function mas_wpjmc_single_company_header() {
        ?>
        <div class="company-contact-details">
            <?php if( ! ( function_exists( 'twentynineteen_can_show_post_thumbnail' ) && twentynineteen_can_show_post_thumbnail() ) ) : ?>
            <div class="company-data">
                <div class="company-logo">
                    <?php $logo =  get_the_company_logo( null, 'thumbnail' ) ? get_the_company_logo( null, 'thumbnail' ) : apply_filters( 'job_manager_default_company_logo', JOB_MANAGER_PLUGIN_URL . '/assets/images/company.png' ); ?>
                    <img src="<?php echo esc_url( $logo ) ?>" class="company-logo--image" alt="<?php the_title(); ?>">
                </div>
                <div class="company-data__content media-body">
                    <?php 
                    the_title( '<h1 class="company-title">', '</h1>' );
                    endif;
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
                    if( ! ( function_exists( 'twentynineteen_can_show_post_thumbnail' ) && twentynineteen_can_show_post_thumbnail() ) ) : ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php
    }
}

if ( ! function_exists( 'mas_wpjmc_single_company_features' ) ) {
    function mas_wpjmc_single_company_features() {
        $args = apply_filters( 'mas_wpjmc_single_company_features_args', array(
            'company_headquarters'  => array(
                'title' => esc_html__( 'Headquarters', 'mas-wp-job-manager-company' ),
                'content' => mas_wpjmc_get_the_meta_data( '_company_headquarters' ),
            ),
            'company_since'  => array(
                'title' => esc_html__( 'Founded', 'mas-wp-job-manager-company' ),
                'content' => mas_wpjmc_get_the_meta_data( '_company_since' ),
            ),
            'company_strength'  => array(
                'title' => esc_html__( 'Employees', 'mas-wp-job-manager-company' ),
                'content' => mas_wpjmc_get_taxomony_data( 'company_strength' ),
            ),
            'company_category'  => array(
                'title' => esc_html__( 'Industry', 'mas-wp-job-manager-company' ),
                'content' => mas_wpjmc_get_taxomony_data( 'company_category' ),
            ),
            'company_revenue'  => array(
                'title' => esc_html__( 'Revenue', 'mas-wp-job-manager-company' ),
                'content' => mas_wpjmc_get_taxomony_data( 'company_revenue' ),
            ),
            'company_average_salary'  => array(
                'title' => esc_html__( 'Avg. Salary', 'mas-wp-job-manager-company' ),
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