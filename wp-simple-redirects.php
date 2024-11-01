<?php
/*
Plugin Name: Simple Redirects
Description: Easily create a list of URLs that you would like to 301 or 302 redirect to another page or site with wildcard support.
Version: 1.0
Author: Mishal Patel
Author URI: https://www.mishalpatel.com/
License: GPLv2
*/

if (!class_exists("SimpleRedirects")) {
	
	class SimpleRedirects {
		
		/**
		 * create_menu function
		 * generate the link to the options page under settings
		 * @access public
		 * @return void
		 */
		function create_menu() {
		  add_options_page('Simple Redirects', 'Simple Redirects', 'manage_options', 'simple-redirects', array($this,'options_page'));
		}
		
		/**
		 * options_page function
		 * generate the options page in the wordpress admin
		 * @access public
		 * @return void
		 */
		function options_page() {
		?>
		<div class="wrap simple_redirects">
			<script>
				jQuery(document).ready(function(){
					jQuery('span.simple-redirects-delete').html('Delete').css({'color':'red','cursor':'pointer'}).click(function(){
						var confirm_delete = confirm('Delete This Redirect?');
						if (confirm_delete) {							
							jQuery(this).parent().parent().remove();
							jQuery('#simple_redirects_form').submit();
						}
					});
					
					jQuery('.simple_redirects .examples').hide().before('<p><a class="reveal-examples" href="#">Examples</a></p>')
					jQuery('.reveal-examples').click(function(){
						jQuery(this).parent().siblings('.examples').slideToggle();
						return false;
					});
				});
			</script>
		<?php
			if (isset($_POST['simple_redirects'])) {
				echo '<div id="message" class="updated"><p>Settings saved</p></div>';
			}
		?>
			<h2>Simple Redirects</h2>
			
			<div>
				<p>Simple redirects work similar to the format that Apache uses: the request should be relative to your WordPress root. The destination can be either a full URL to any page on the web, or relative to your WordPress root. The Redirection rule can be either 301 for permanent redirection or 302 for temporary redirection.</p>
			</div>
			
			<form method="post" id="simple_redirects_form" action="options-general.php?page=simple-redirects&savedata=true">
			<?php wp_nonce_field( 'save_redirects', '_simple_redirects_nonce' ); ?>
			<table class="widefat">
				<thead>
					<tr style="background:#c3c3c3;">
						<th colspan="2">Request</th>
						<th colspan="2">Destination</th>
						<th colspan="2">Redirection</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td colspan="2"><small>example: /about.htm</small></td>
						<td colspan="2"><small>example: <?php echo get_option('home'); ?>/about/</small></td>
						<td colspan="2"><small>301 or 302</small></td>
					</tr>
					<?php echo $this->expand_redirects(); ?>
					<tr style="background:#8cad7c;">
						<td colspan="6"><span>ADD NEW RULE :</span</td>
					</tr>
					<tr style="background:#8cad7c;">
						<td style="width:36%;"><input type="text" name="simple_redirects[request][]" value="" style="width:99%;" /></td>
						<td style="width:2%;">&raquo;</td>
						<td style="width:50%;"><input type="text" name="simple_redirects[destination][]" value="" style="width:99%;" /></td>
						<td style="width:2%;">&raquo;</td>
						<td style="width:10%;"><select name="simple_redirects[redirection][]" ><option value="301" selected>301</option><option value="302">302</option></select></td>
						<td><span></span></td>
					</tr>
				</tbody>
			</table>
			
			<?php $wildcard_checked = (get_option('simple_redirects_wildcard') === 'true' ? ' checked="checked"' : ''); ?>
			<p><input type="checkbox" name="simple_redirects[wildcard]" id="simple-redirects-wildcard"<?php echo $wildcard_checked; ?> /><label for="simple-redirects-wildcard"> Use Wildcards?</label></p>

			<?php $https_checked = (get_option('simple_redirects_https') === 'true' ? ' checked="checked"' : ''); ?>
			<p><input type="checkbox" name="simple_redirects[https]" id="simple-redirects-https"<?php echo $https_checked; ?> /><label for="simple-redirects-https"> Use HTTPS?</label></p>

			
			<p class="submit"><input type="submit" name="submit_simple_redirects" class="button-primary" value="<?php _e('Save Changes') ?>" /></p>
			</form>
			<div class="examples">
				<h4>Example</h4>
				<ul>
					<li><strong>Request:</strong> /old-page/</li>
					<li><strong>Destination:</strong> /new-page/</li>
					<li><strong>Redirection:</strong> 301</li>
				</ul>
				
				<h3>Wildcards</h3>
				<p>To use wildcards, put an asterisk (*) after the folder name that you want to redirect.</p>
				<h4>Example</h4>
				<ul>
					<li><strong>Request:</strong> /old-folder/*</li>
					<li><strong>Destination:</strong> /redirect-everything-here/</li>
					<li><strong>Redirection:</strong> 301</li>
				</ul>
		
				<p>You can also use the asterisk in the destination to replace whatever it matched in the request if you like. Something like this:</p>
				<h4>Example</h4>
				<ul>
					<li><strong>Request:</strong> /old-folder/*</li>
					<li><strong>Destination:</strong> /some/other/folder/*</li>
					<li><strong>Redirection:</strong> 301</li>
				</ul>
				<p>Or:</p>
				<ul>
					<li><strong>Request:</strong> /old-folder/*/content/</li>
					<li><strong>Destination:</strong> /some/other/folder/*</li>
					<li><strong>Redirection:</strong> 301</li>
				</ul>
			</div>
		</div>
		<?php
		} // end of function options_page
		
		/**
		 * expand_redirects function
		 * utility function to return the current list of redirects as form fields
		 * @access public
		 * @return string <html>
		 */
		function expand_redirects() {
			$redirects = get_option('simple_redirects');
			$output = '';
			if (!empty($redirects)) {
				foreach ($redirects as $request) {
					$output .= '
					
					<tr>
						<td><input type="text" name="simple_redirects[request][]" value="'.$request[0].'" style="width:99%" /></td>
						<td>&raquo;</td>
						<td><input type="text" name="simple_redirects[destination][]" value="'.$request[1].'" style="width:99%;" /></td>
						<td>&raquo;</td>
						<td><select name="simple_redirects[redirection][]"><option value="301" '. (($request[2] == 301)?"selected":"") .'>301</option><option value="302" '. (($request[2] == 302)?"selected":"") .'>302</option></select></td>
						<td><span class="simple-redirects-delete"></span></td>
					</tr>
					
					';
				}
			} // end if
			return $output;
		} // end of function expand_redirects
		
		/**
		 * save_redirects function
		 * save the redirects from the options page to the database
		 * @access public
		 * @param mixed $data
		 * @return void
		 */
		function save_redirects($data) {
			if ( !current_user_can('manage_options') )  { wp_die( 'You do not have sufficient permissions to access this page.' ); }
			check_admin_referer( 'save_redirects', '_simple_redirects_nonce' );
			
			$data = $_POST['simple_redirects'];

			$redirects = array();
			
			for($i = 0; $i < sizeof($data['request']); ++$i) {
				$request = trim( sanitize_text_field( $data['request'][$i] ) );
				$destination = trim( sanitize_text_field( $data['destination'][$i] ) );
				$redirection = trim( sanitize_text_field( $data['redirection'][$i] ) );
				
				if ($request == '' || $destination == '' || $redirection == '') { continue; }
				elseif ($request == '/' || $request == '/wp-admin' || $request == '/wp-login.php') { continue; }
				else { 
				    $redirects[] = array($request,$destination, $redirection);
				}
			}
			
			update_option('simple_redirects', $redirects);
			
			if (isset($data['wildcard'])) {
				update_option('simple_redirects_wildcard', 'true');
			}
			else {
				delete_option('simple_redirects_wildcard');
			}
			
			if (isset($data['https'])) {
				update_option('simple_redirects_https', 'true');
			}
			else {
				delete_option('simple_redirects_https');
			}
		} // end of function save_redirects
		
		/**
		 * redirect function
		 * Read the list of redirects and if the current page 
		 * is found in the list, send the visitor on her way
		 * @access public
		 * @return void
		 */
		function redirect() {
			
			$force_ssl = (get_option('simple_redirects_https'));
			if ($force_ssl) {
				$this->force_ssl();
			}
			
			// this is what the user asked for (strip out home portion, case insensitive)
			$userrequest = str_ireplace(get_option('home'),'',$this->get_address());
			$userrequest = rtrim($userrequest,'/');
			
			$redirects = get_option('simple_redirects');
			if (!empty($redirects)) {
				
				$wildcard = get_option('simple_redirects_wildcard');
				$do_redirect = '';
				
				// compare user request to each 301 stored in the db
				foreach ($redirects as $storedrequest) {
					// check if we should use regex search 
					if ($wildcard === 'true' && strpos($storedrequest[0],'*') !== false) {
						// wildcard redirect
						
						// don't allow people to accidentally lock themselves out of admin
						if ( strpos($userrequest, '/wp-login') !== 0 && strpos($userrequest, '/wp-admin') !== 0 ) {
							// Make sure it gets all the proper decoding and rtrim action
							$storedrequest[0] = str_replace('*','(.*)',$storedrequest[0]);
							$pattern = '/^' . str_replace( '/', '\/', rtrim( $storedrequest[0], '/' ) ) . '/';
							$storedrequest[1] = str_replace('*','$1',$storedrequest[1]);
							$output = preg_replace($pattern, $storedrequest[1], $userrequest);
							if ($output !== $userrequest) {
								// pattern matched, perform redirect
								$do_redirect = $output;
							}
						}
					}
					elseif(urldecode($userrequest) == rtrim($storedrequest[0],'/')) {
						// simple comparison redirect
						$do_redirect = $storedrequest[1];
					}
					
					// redirect. the second condition here prevents redirect loops as a result of wildcards.
					if ($do_redirect !== '' && trim($do_redirect,'/') !== trim($userrequest,'/')) {
						// check if destination needs the domain prepended
						if (strpos($do_redirect,'/') === 0){
							$do_redirect = home_url().$do_redirect;
						}
						wp_redirect( $do_redirect, $storedrequest[2] );
						exit();
					}
					else { unset($redirects); }
				}
			}
		} // end funcion redirect
		
		/**
		 * get_address function
		 * utility function to get the full address of the current request
		 * @access public
		 * @return void
		 */
		function get_address() {
			// return the full address
			return $this->set_protocol().'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		} // end function get_address
		
		
		/**
		 * set_protocol function
		 * utility function to set the protocol for the current request
		 * @access public
		 * @return void
		 */
		function set_protocol() {
			// Set the base protocol to http
			$protocol = 'http';
			// check for https
			if ( isset( $_SERVER["HTTPS"] ) && strtolower( $_SERVER["HTTPS"] ) == "on" ) {
    			$protocol .= "s";
			}
			
			return $protocol;
		} // end function set_protocol
		
		
		/**
		 * create_settings_link function
		 * utility function to add settings link on plugin page
		 * @access public
		 * @return void
		 */
		function create_settings_link($links) { 
			$settings_link = '<a href="options-general.php?page=simple-redirects">'.__('Settings', 'simple-redirects').'</a>'; 
			array_unshift($links, $settings_link); 
			return $links; 
		} // end function create_settings_link
		
		/**
		 * force_ssl function
		 * utility function to force pages to HTTPS
		 * @access public
		 * @return void
		 */
		function force_ssl(){
            if (!is_ssl()) {
                wp_redirect('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], 301);
                exit();
            }
        }
		
	} // end class SimpleRedirects
	
} // end check for existance of class

// instantiate
$redirect_plugin = new SimpleRedirects();

if (isset($redirect_plugin)) {
	// add the redirect action, high priority
	add_action('init', array($redirect_plugin,'redirect'), 1);

	// create the menu
	add_action('admin_menu', array($redirect_plugin,'create_menu'));
	
	// create settings link
	add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($redirect_plugin,'create_settings_link'));

	// if submitted, process the data
	if (isset($_POST['simple_redirects'])) {
		add_action('admin_init', array($redirect_plugin,'save_redirects'));
	}
}

