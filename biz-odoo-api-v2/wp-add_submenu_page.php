<?php

    if ( ! function_exists( 'biz_odoo_api_add_submenu_page_to_biz_solution' ) ):
        /**
         * Adds Biz Plasgate Menu and Page
         * Check if Biz Solution Menu and Page already exists otherwise add default menu and page
         */
        function biz_odoo_api_add_submenu_page_to_biz_solution()
        {
            
            global $menu, $submenu;
            $main_menu_slug			= 'biz-solution-page';
            $this_submenu_slug		= 'biz-odoo-api-page';



            if ( !isset($submenu[ $this_submenu_slug ]) ):

                $parent_slug		=	$main_menu_slug;
                $page_title			=	"Biz WordPress Plugin";
                $menu_title			=	"Biz Odoo API";
                $capability			=	"manage_options";
                $menu_slug			=	$this_submenu_slug;
                $function			=	"biz_odoo_api_option_page";
                $position			=	3;

                add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function, $position);

            endif;
        }

        add_action('admin_menu', 'biz_odoo_api_add_submenu_page_to_biz_solution', 99);

    endif;


    if ( ! function_exists( 'biz_odoo_api_option_page' ) ):
        /**
         * Load view for Biz PlasGate Page
         */
        function biz_odoo_api_option_page()
        {
            $option_page = BIZ_ODOO_API_PLUGIN_DIR . '/pages/biz_odoo_api_option_page.php';

            if ( ! is_readable( $option_page ) )
            {
                ?>
                <div class="notice notice-error">
                    <p>
                        <?php
                        printf(
                            esc_html__( $option_page.' is missing! Please contact BizSolution for technical supports.', 'biz-solution' ),
                            '<a href="' . esc_url( 'https://bizsolution.com.kh' ) . '" target="_blank" rel="noopener noreferrer">',
                            '</a>'
                        );
                        ?>
                    </p>
                </div>
                <?php
            }
            else
            {
                require $option_page;
            }
        }
    endif;