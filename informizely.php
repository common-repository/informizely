<?php
/**
 * @package Informizely
 */
/*
Plugin Name: informizely
Description: Informizely allows you to quickly get winning insights by showing beautiful and precisely targeted website surveys in your WordPress site.
Version: 1.0.0
Author: Informizely
Author URI: https://www.informizely.com
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html
*/

if (!function_exists('add_action')) {
    exit;
}

add_action('admin_menu', 'informizely_admin_menu');
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'informizely_add_settings_link');
add_action('admin_init', 'informizely_register_settings');
add_action('admin_post_informizely_site_id', 'informizely_set_site_id');
add_action('wp_footer', 'informizely_add_tag');

function informizely_admin_menu()
{
    add_options_page('Informizely', 'Informizely', 'manage_options', 'informizely', 'informizely_options_page');
}

function informizely_options_page()
{
    if (isset($_GET['settings-updated'])) : ?>
		<?php informizely_check_installation(); ?>
   <?php endif; ?>

    <form action="options.php" method="POST" style="padding: 2rem">
        <img src="<?php echo plugins_url( 'assets/Logo.png', __FILE__ ); ?>" height="40"
                style="padding-top: 0px; padding-bottom: 0px;" alt="Informizely"/>
        <h1>Get actionable insights quickly with Informizely surveys</h1>
        <div style='display: inline-block; border: 3px solid #777; padding: 1em 2em; margin-top: 1em;'>
			<p>All you need to do to show Informizely surveys on your WordPress website is to enter its Informizely Site ID.</p>
			<p>You can find the Site ID of your website on <a href="https://app.informizely.com/Home/SiteIds" target="_blank">this page</a> in your Informizely dashboard.</p>
            <?php settings_fields('informizely-settings'); ?>
            <?php do_settings_sections('informizely-settings'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="informizely-site-id">Informizely Site ID:</label></th>
                    <td>
                        <input type="text" id="informizely-site-id" name="informizely-site-id" value="<?php echo esc_attr(get_option('informizely-site-id')); ?>"
                                style="width:100%;" autocomplete="off">
                        <p class="description">(leave empty to disable)</p>
                    </td>
                </tr>
            </table>
            <?php submit_button('Save changes'); ?>
        </div>
    </form>
    <?php
}

function informizely_check_installation()
{
    $site_id = esc_attr(get_option('informizely-site-id'));
    if (strlen($site_id) == 0) {
		?>
           <div id="message" class="notice notice-warning is-dismissible">
                <p><strong><?php _e('Informizely is disabled.', 'informizely'); ?></strong></p>
            </div>
		<?php
	} else if (!preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/', $site_id)) {
		// The site ID must have the format of a valid GUID.
		?>
			<div id="message" class="notice notice-error is-dismissible">
				<p>
					<strong>Incorrect Site ID. Please enter the correct Site ID for your Informizely Site.</strong>
				</p>
			</div>
		<?php
   } else {
		?>
			<div id="message" class="notice notice-success is-dismissible">
				<p>
					<strong><em>The Informizely tag has been installed successfully on your WordPress website!</em></strong>
				</p>
			</div>
		<?php
	}
}

function informizely_add_settings_link($links)
{
    $links[] = '<a href="' . admin_url('options-general.php?page=informizely') . '">' . __('Settings') . '</a>';
    return $links;
}

function informizely_register_settings()
{
    register_setting('informizely-settings', 'informizely-site-id');
}

function informizely_set_site_id()
{
	// The option 'informizely-site-id' has already been checked in 'informizely_check_installation()' to be a valid GUID.
    if (get_option('informizely-site-id') !== false) {
        update_option('informizely-site-id', $_POST['informizely-site-id']);
    } else {
        add_option('informizely-site-id', '');
    }
    wp_redirect(admin_url('/options-general.php?page=informizely'), 301);
}

function informizely_add_tag()
{
    $site_id = esc_attr(get_option('informizely-site-id'));

    if (strlen($site_id) != 36) {
        return;
    }

	// Load the site configuration asynchronously from the URL "https://insitez.blob.core.windows.net/site/<site_id>.js" (the term "insitez" refers to the previous name of Informizely).

    ?>
<!-- Informizely tag. -->
	<script id="_informizely_script_tag" type="text/javascript">
		var IzWidget = IzWidget || {};
		(function (d) {
		var scriptElement = d.createElement('script');
		scriptElement.type = 'text/javascript'; scriptElement.async = true;
		scriptElement.src = "<?php echo sprintf("https://insitez.blob.core.windows.net/site/%s.js", $site_id); ?>";
		var node = d.getElementById('_informizely_script_tag');
		node.parentNode.insertBefore(scriptElement, node);
		})(document);
	</script>
	<noscript><a href="https://www.informizely.com/">Informizely customer feedback surveys</a></noscript>
	<!-- End Informizely tag. -->
    <?php
}
