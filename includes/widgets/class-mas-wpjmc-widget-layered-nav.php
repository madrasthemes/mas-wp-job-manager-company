<?php
/**
 * Layered nav widget
 *
 * @package Widgets
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Widget layered nav class.
 */
class MAS_WPJMC_Widget_Layered_Nav extends WP_Widget {

    /**
     * Constructor.
     */
    public function __construct() {
        $widget_ops = array( 'description' => esc_html__( 'Add company filter widgets to your sidebar.', 'mas-wp-job-manager-company' ) );
        parent::__construct( 'mas_wpjmc_layered_nav', esc_html__( 'MAS Filter Company by Taxonomy', 'mas-wp-job-manager-company' ), $widget_ops );
    }

    /**
     * Updates a particular instance of a widget.
     *
     * @see WP_Widget->update
     *
     * @param array $new_instance New Instance.
     * @param array $old_instance Old Instance.
     *
     * @return array
     */
    public function update( $new_instance, $old_instance ) {
        $instance = array();
        if ( ! empty( $new_instance['title'] ) ) {
            $instance['title'] = strip_tags( stripslashes($new_instance['title']) );
        }
        if ( ! empty( $new_instance['taxonomy'] ) ) {
            $instance['taxonomy'] = $new_instance['taxonomy'];
        }
        if ( ! empty( $new_instance['query_type'] ) ) {
            $instance['query_type'] = $new_instance['query_type'];
        }
        return $instance;
    }

    /**
     * Outputs the settings update form.
     *
     * @see WP_Widget->form
     *
     * @param array $instance Instance.
     */
    public function form( $instance ) {
        global $wp_registered_sidebars;

        $taxonomy_array = mas_wpjmc_get_all_taxonomies();
        $title = isset( $instance['title'] ) ? $instance['title'] : '';
        $taxonomy = isset( $instance['taxonomy'] ) ? $instance['taxonomy'] : '';
        $query_type = isset( $instance['query_type'] ) ? $instance['query_type'] : 'and';

        // If no sidebars exists.
        if ( !$wp_registered_sidebars ) {
            echo '<p>'. esc_html__('No sidebars are available.', 'mas-wp-job-manager-company' ) .'</p>';
            return;
        }
        ?>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id('title') ); ?>"><?php esc_html_e( 'Title:', 'mas-wp-job-manager-company' ) ?></label>
            <input type="text" class="widefat" id="<?php echo esc_attr( $this->get_field_id('title') ); ?>" name="<?php echo esc_attr( $this->get_field_name('title') ); ?>" value="<?php echo esc_attr( $title ); ?>" />
        </p>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'taxonomy' ) ); ?>"><?php esc_html_e( 'Taxonomy:', 'mas-wp-job-manager-company' ); ?></label>
            <select id="<?php echo esc_attr( $this->get_field_id( 'taxonomy' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'taxonomy' ) ); ?>">
                <option value=""><?php esc_html_e( '&mdash; Select &mdash;', 'mas-wp-job-manager-company' ); ?></option>
                <?php foreach ( $taxonomy_array as $tax ) : ?>
                    <option value="<?php echo esc_attr( $tax['taxonomy'] ); ?>" <?php selected( $taxonomy, $tax['taxonomy'] ); ?>>
                        <?php echo esc_html( $tax['name'] ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'query_type' ) ); ?>"><?php esc_html_e( 'Query type:', 'mas-wp-job-manager-company' ); ?></label>
            <select id="<?php echo esc_attr( $this->get_field_id( 'query_type' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'query_type' ) ); ?>">
                <option value="and" <?php selected( $query_type, 'and' ); ?>><?php echo esc_html__( 'AND', 'mas-wp-job-manager-company' ); ?></option>
                <option value="or" <?php selected( $query_type, 'or' ); ?>><?php echo esc_html__( 'OR', 'mas-wp-job-manager-company' ); ?></option>
            </select>
        </p>
        <?php
    }

    /**
     * Output widget.
     *
     * @see WP_Widget
     *
     * @param array $args Arguments.
     * @param array $instance Instance.
     */
    public function widget( $args, $instance ) {
        if ( ! ( is_post_type_archive( 'company' ) || is_page( mas_wpjmc_get_page_id( 'companies' ) ) ) && ! mas_wpjmc_is_company_taxonomy() ) {
            return;
        }

        $_chosen_taxonomies = MAS_WPJMC_Query::get_layered_nav_chosen_taxonomies();
        $title              = isset( $instance['title'] ) ? $instance['title'] : esc_html__( 'Filter by', 'mas-wp-job-manager-company' );
        $taxonomy           = isset( $instance['taxonomy'] ) ? $instance['taxonomy'] : '';
        $query_type         = isset( $instance['query_type'] ) ? $instance['query_type'] : 'and';

        if ( ! taxonomy_exists( $taxonomy ) ) {
            return;
        }

        $get_terms_args = apply_filters( 'mas_wpjmc_layered_nav_terms_args', array( 'hide_empty' => '1' ) );

        $terms = get_terms( $taxonomy, $get_terms_args );

        if ( 0 === count( $terms ) ) {
            return;
        }

        ob_start();

        echo wp_kses_post( $args['before_widget'] );

        if ( ! empty($instance['title']) ) {
            echo wp_kses_post( $args['before_title'] . $title . $args['after_title'] );
        }

        $found = $this->layered_nav_list( $terms, $taxonomy, $query_type );

        echo wp_kses_post( $args['after_widget'] );

        // Force found when option is selected - do not force found on taxonomy taxonomies.
        if ( ! is_tax() && is_array( $_chosen_taxonomies ) && array_key_exists( $taxonomy, $_chosen_taxonomies ) ) {
            $found = true;
        }

        if ( ! $found ) {
            ob_end_clean();
        } else {
            echo ob_get_clean(); // @codingStandardsIgnoreLine
        }
    }

    /**
     * Return the currently viewed taxonomy name.
     *
     * @return string
     */
    protected function get_current_taxonomy() {
        return is_tax() ? get_queried_object()->taxonomy : '';
    }

    /**
     * Return the currently viewed term ID.
     *
     * @return int
     */
    protected function get_current_term_id() {
        return absint( is_tax() ? get_queried_object()->term_id : 0 );
    }

    /**
     * Return the currently viewed term slug.
     *
     * @return int
     */
    protected function get_current_term_slug() {
        return absint( is_tax() ? get_queried_object()->slug : 0 );
    }

    /**
     * Count jobs within certain terms, taking the main WP query into consideration.
     *
     * This query allows counts to be generated based on the viewed jobs, not all jobs.
     *
     * @param  array  $term_ids Term IDs.
     * @param  string $taxonomy Taxonomy.
     * @param  string $query_type Query Type.
     * @return array
     */
    protected function get_filtered_term_company_counts( $term_ids, $taxonomy, $query_type ) {
        global $wpdb;

        $tax_query  = MAS_WPJMC_Query::get_main_tax_query();
        $meta_query = MAS_WPJMC_Query::get_main_meta_query();
        $date_query = MAS_WPJMC_Query::get_main_date_query();

        if ( 'or' === $query_type ) {
            foreach ( $tax_query as $key => $query ) {
                if ( is_array( $query ) && $taxonomy === $query['taxonomy'] ) {
                    unset( $tax_query[ $key ] );
                }
            }
        }

        $meta_query     = new WP_Meta_Query( $meta_query );
        $date_query     = new WP_Date_Query( $date_query );
        $tax_query      = new WP_Tax_Query( $tax_query );
        $meta_query_sql = $meta_query->get_sql( 'post', $wpdb->posts, 'ID' );
        $date_query_sql = $date_query->get_sql( $wpdb->posts, 'ID' );
        $tax_query_sql  = $tax_query->get_sql( $wpdb->posts, 'ID' );

        // Generate query.
        $query           = array();
        $query['select'] = "SELECT COUNT( DISTINCT {$wpdb->posts}.ID ) as term_count, terms.term_id as term_count_id";
        $query['from']   = "FROM {$wpdb->posts}";
        $query['join']   = "
            INNER JOIN {$wpdb->term_relationships} AS term_relationships ON {$wpdb->posts}.ID = term_relationships.object_id
            INNER JOIN {$wpdb->term_taxonomy} AS term_taxonomy USING( term_taxonomy_id )
            INNER JOIN {$wpdb->terms} AS terms USING( term_id )
            " . $tax_query_sql['join'] . $meta_query_sql['join'];

        $query['where'] = "
            WHERE {$wpdb->posts}.post_type IN ( 'company' )
            AND {$wpdb->posts}.post_status = 'publish'"
            . $tax_query_sql['where'] . $meta_query_sql['where'] . $date_query_sql .
            'AND terms.term_id IN (' . implode( ',', array_map( 'absint', $term_ids ) ) . ')';

        $search = MAS_WPJMC_Query::get_main_search_query_sql();
        if ( $search ) {
            $query['where'] .= ' AND ' . $search;
        }

        $query['group_by'] = 'GROUP BY terms.term_id';
        $query             = apply_filters( 'mas_wpjmc_get_filtered_term_company_counts_query', $query );
        $query             = implode( ' ', $query );

        // We have a query - let's see if cached results of this query already exist.
        $query_hash    = md5( $query );

        // Maybe store a transient of the count values.
        $cache = apply_filters( 'mas_wpjmc_layered_nav_count_maybe_cache', true );
        if ( true === $cache ) {
            $cached_counts = (array) get_transient( 'mas_wpjmc_layered_nav_counts_' . $taxonomy );
        } else {
            $cached_counts = array();
        }

        if ( ! isset( $cached_counts[ $query_hash ] ) ) {
            $results                      = $wpdb->get_results( $query, ARRAY_A ); // @codingStandardsIgnoreLine
            $counts                       = array_map( 'absint', wp_list_pluck( $results, 'term_count', 'term_count_id' ) );
            $cached_counts[ $query_hash ] = $counts;
            if ( true === $cache ) {
                set_transient( 'mas_wpjmc_layered_nav_counts_' . $taxonomy, $cached_counts, DAY_IN_SECONDS );
            }
        }

        return array_map( 'absint', (array) $cached_counts[ $query_hash ] );
    }

    /**
     * Show list based layered nav.
     *
     * @param  array  $terms Terms.
     * @param  string $taxonomy Taxonomy.
     * @param  string $query_type Query Type.
     * @return bool   Will nav display?
     */
    protected function layered_nav_list( $terms, $taxonomy, $query_type ) {
        // List display.
        echo '<ul class="mas-wpjmc-widget-layered-nav-list tax-' . esc_attr( $taxonomy ) . '">';

        $term_counts        = $this->get_filtered_term_company_counts( wp_list_pluck( $terms, 'term_id' ), $taxonomy, $query_type );
        $_chosen_taxonomies = MAS_WPJMC_Query::get_layered_nav_chosen_taxonomies();
        $found              = false;

        foreach ( $terms as $term ) {
            $current_values = isset( $_chosen_taxonomies[ $taxonomy ]['terms'] ) ? $_chosen_taxonomies[ $taxonomy ]['terms'] : array();
            $option_is_set  = in_array( $term->slug, $current_values, true );
            $count          = isset( $term_counts[ $term->term_id ] ) ? $term_counts[ $term->term_id ] : 0;

            // Skip the term for the current archive.
            if ( $this->get_current_term_id() === $term->term_id ) {
                continue;
            }

            // Only show options with count > 0.
            if ( 0 < $count ) {
                $found = true;
            } elseif ( 0 === $count && ! $option_is_set ) {
                continue;
            }

            $filter_name    = 'filter_' . sanitize_title( $taxonomy );
            $current_filter = isset( $_GET[ $filter_name ] ) ? explode( ',', mas_wpjmc_clean( wp_unslash( $_GET[ $filter_name ] ) ) ) : array(); // WPCS: input var ok, CSRF ok.
            $current_filter = array_map( 'sanitize_title', $current_filter );

            if ( ! in_array( $term->slug, $current_filter, true ) ) {
                $current_filter[] = $term->slug;
            }

            $link = remove_query_arg( $filter_name, MAS_WPJMC::get_current_page_url() );

            // Add current filters to URL.
            foreach ( $current_filter as $key => $value ) {
                // Exclude query arg for current term archive term.
                if ( $value === $this->get_current_term_slug() ) {
                    unset( $current_filter[ $key ] );
                }

                // Exclude self so filter can be unset on click.
                if ( $option_is_set && $value === $term->slug ) {
                    unset( $current_filter[ $key ] );
                }
            }

            if ( ! empty( $current_filter ) ) {
                asort( $current_filter );
                $link = add_query_arg( $filter_name, implode( ',', $current_filter ), $link );

                // Add Query type Arg to URL.
                if ( 'or' === $query_type && ! ( 1 === count( $current_filter ) && $option_is_set ) ) {
                    $link = add_query_arg( 'query_type_' . sanitize_title( $taxonomy ), 'or', $link );
                }
                $link = str_replace( '%2C', ',', $link );
            }

            $count_html = apply_filters( 'mas_wpjmc_layered_nav_count', '<span class="count">(' . absint( $count ) . ')</span>', $count, $term );

            if ( $count > 0 || $option_is_set ) {
                $link      = apply_filters( 'mas_wpjmc_layered_nav_link', $link, $term, $taxonomy );
                $term_html = '<a rel="nofollow" href="' . esc_url( $link ) . '">' . esc_html( $term->name ) . $count_html . '</a>';
            } else {
                $link      = false;
                $term_html = '<span>' . esc_html( $term->name ) . '</span>';
            }

            echo '<li class="mas-wpjmc-widget-layered-nav-list__item mas-wpjmc-layered-nav-term ' . esc_attr( sanitize_title( $term->slug ) ) . ( $option_is_set ? ' mas-wpjmc-widget-layered-nav-list__item--chosen chosen' : '' ) . '">';
            echo wp_kses_post( apply_filters( 'mas_wpjmc_layered_nav_term_html', $term_html, $term, $link, $count ) );
            echo '</li>';
        }

        echo '</ul>';

        return $found;
    }
}
