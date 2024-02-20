<?php
/**
 * Template Loader
 *
 */

defined( 'ABSPATH' ) || exit;

/**
 * Template loader class.
 */
class MAS_WPJMC_Template_Loader {

    /**
     * Store the company page ID.
     *
     * @var integer
     */
    public static $companies_page_id = 0;

    /**
     * Hook in methods.
     */
    public static function init() {
        self::$companies_page_id  = mas_wpjmc_get_page_id( 'companies' );

        // Supported themes.
        add_filter( 'template_include', array( __CLASS__, 'template_loader' ) );
    }

    /**
     * Load a template.
     *
     * Handles template usage so that we can use our own templates instead of the themes.
     *
     * Templates are in the 'templates' folder. mas-wp-job-manager-company looks for theme.
     * overrides in /theme/mas-wp-job-manager-company/ by default.
     *
     * For beginners, it also looks for a mas-wp-job-manager-company.php template first. If the user adds.
     * this to the theme (containing a mas-wp-job-manager-company() inside) this will be used for all.
     * mas-wp-job-manager-company templates.
     *
     * @param string $template Template to load.
     * @return string
     */
    public static function template_loader( $template ) {

        if ( is_embed() ) {
            return $template;
        }

        $default_file = self::get_template_loader_default_file();


        if ( $default_file ) {
            /**
             * Filter hook to choose which files to find before mas-wp-job-manager-company does it's own logic.
             *
             * @since 1.0.0
             * @var array
             */
            $search_files = self::get_template_loader_files( $default_file );
            $template     = locate_template( $search_files );

            if ( ! $template ) {
                $template = mas_wpjmc()->plugin_dir . 'templates/' . $default_file;
            }

        }

        return $template;
    }

    /**
     * Get the default filename for a template.
     *
     * @since  1.0.0
     * @return string
     */
    private static function get_template_loader_default_file() {
        if ( is_singular( 'company' ) ) {
            $default_file = 'single-company.php';
        } elseif ( is_post_type_archive( 'company' ) || is_page( self::$companies_page_id ) || mas_wpjmc_is_company_taxonomy() ) {
            $default_file = 'archive-company.php';
        } else {
            $default_file = '';
        }
        return $default_file;
    }

    /**
     * Get an array of filenames to search for a given template.
     *
     * @since  1.0.0
     * @param  string $default_file The default file name.
     * @return string[]
     */
    private static function get_template_loader_files( $default_file ) {
        $templates   = apply_filters( 'mas_company_template_loader_files', array(), $default_file );

        if ( is_page_template() ) {
            $templates[] = get_page_template_slug();
        }

        if ( is_singular( 'company' ) ) {
            $object       = get_queried_object();
            $name_decoded = urldecode( $object->post_name );
            if ( $name_decoded !== $object->post_name ) {
                $templates[] = "single-company-{$name_decoded}.php";
            }
            $templates[] = "single-company-{$object->post_name}.php";
        }

        if ( mas_wpjmc_is_company_taxonomy() ) {
            $object      = get_queried_object();
            $templates[] = 'taxonomy-' . $object->taxonomy . '-' . $object->slug . '.php';
            $templates[] = '/mas-wp-job-manager-company/' . 'taxonomy-' . $object->taxonomy . '-' . $object->slug . '.php';

            $templates[] = 'taxonomy-' . $object->taxonomy . '.php';
            $templates[] = '/mas-wp-job-manager-company/' . 'taxonomy-' . $object->taxonomy . '.php';
        }

        $templates[] = $default_file;
        $templates[] = '/mas-wp-job-manager-company/' . $default_file;

        return array_unique( $templates );
    }

}

add_action( 'init', array( 'MAS_WPJMC_Template_Loader', 'init' ) );