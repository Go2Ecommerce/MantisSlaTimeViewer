<?php

class SlaTimeViewerPlugin extends MantisPlugin
{
    var $slaTimeViewerApi;

    function register()
    {
        $this->name = 'SlaTimeViewer';
        $this->description = 'Plugin to display time of tracking sla in subpage';
        $this->page = '';
        $this->version = '1.0.0';
        $this->requires = array('MantisCore' => '2.0.0');
        $this->author = 'michal@go2ecommerce.pl';
        $this->contact = '';
        $this->url = 'https://agencja-ecommerce.pl';
    }

    function init()
    {
        plugin_require_api('core/SlaTimeViewerApi.class.php');
        require_api( 'summary_api.php' );
    }

    function hooks()
    {
        return array(
            'EVENT_MENU_SUMMARY' => 'summary_menu'
        );
    }

    /**
	 * Retrieve a link to a plugin page with temporary filter parameter.
	 * @param string $p_page Plugin page name
	 * @return string
	 */
	private function get_url_with_filter( $p_page ) {
		static $s_filter_param;

		if( $s_filter_param === null ) {
			$t_filter = summary_get_filter();
			$s_filter_param = filter_get_temporary_key_param( $t_filter );
		}

		return helper_url_combine( plugin_page( $p_page ), $s_filter_param );
	}

	/**
	 * Event hook to add the plugin's tab to the Summary page menu.
	 * @return array
	 */
	function summary_menu() {
		$t_menu_items[] = '<a href="'
			. $this->get_url_with_filter( 'timeviewer.php' )
			. '">'
			. plugin_lang_get( 'tab_label' )
			. '</a>';
		return $t_menu_items;
	}
}
