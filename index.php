<?php
/*
  Plugin Name: Registration Page Customizer for WooCommerce
  Plugin URI: https://sarathlal.com/
  Description: Allow admin to customize WooCommerce registration page
  Author: Sarathlal N
  Version: 1.0
  Author URI: https://sarathlal.com/
 */

class WooRegisterCustomize {

    public function __construct() {

        add_action('admin_enqueue_scripts', array($this, 'enqueue_script'));
        add_action('admin_init', array($this, 'register_setting'));
        add_action('admin_menu', array($this, 'create_options_page'));
        add_action('woocommerce_register_form_start', array($this, 'render_extra_register_fields'));
        add_action('woocommerce_created_customer', array($this, 'save_extra_register_fields'));
        add_filter('woocommerce_account_menu_items', array($this, 'create_new_menu_link'), 40);
        add_action('init', array($this, 'register_endpoint'));
        add_action('woocommerce_account_additional-info_endpoint', array($this, 'my_account_additional_info_content'));
        add_action('init', array($this, 'handle_additional_info_form'));
    }

    //Enque script
    function enqueue_script() {
        wp_enqueue_script('customizer_script', plugin_dir_url(__FILE__) . 'js/customizer.js');
        wp_enqueue_script('jquery_ui', plugin_dir_url(__FILE__) . 'js/jquery-ui.js');
        wp_enqueue_script('core_ui', plugin_dir_url(__FILE__) . 'js/jquery.ui.core.js');
        wp_enqueue_script('mouse_ui', plugin_dir_url(__FILE__) . 'js/jquery.ui.mouse.js');
        //wp_enqueue_script('widget_ui', plugin_dir_url(__FILE__) . 'js/jquery.ui.widget.js');
        wp_enqueue_script('sortable_ui', plugin_dir_url(__FILE__) . 'js/jquery.ui.sortable.js');
        wp_enqueue_style('customizer_css', plugin_dir_url(__FILE__) . 'css/customizer.css', false, '1.0.0');
    }

    //Register new setting for plugin with in new group
    function register_setting() {
        register_setting('woo_reg_customizer_group', 'woo_reg_customizer', array($this, 'customizer_callback'));
    }

    //Create Option Page
    function create_options_page() {
        add_options_page('Custom fields in WooCommerce Registrtion Page', 'Registration Customizer', 'manage_options', 'registration-fields', array($this, 'option_page_content'));
    }

    //Option Page content
    function option_page_content() {
        ?>
        <div>
            <?php screen_icon(); ?>
            <h2>Customize WooCommerce Registration page with your custom fields</h2>
            <form id="create-new-form">
                <div class="create-widget">
                    <p>
                        <label>Select Input Type</label>
                        <select id="new-input-type">
                            <option value="text">Text</option>
                            <option value="select">Select</option>
                            <option value="radio">Radio</option>
                        </select>
                        <button id="add-new">Create New Field</button>
                    </p>
                    <p class="hint"><b>Hint:</b> Text field only require label. Select field require label & dropdown values. Please enter dropdown values in comma seperated like <code> option 1, option 2, option 3</code></p>
                </div>
                <ul id="elements">
                    <?php
                    $saved_data = get_option('woo_reg_customizer');
                    //Decode JSON string as an array
                    $saved_data = json_decode($saved_data, true);
                    if (is_array($saved_data) and !empty($saved_data)) {
                    foreach ($saved_data as $element) {
                        //If item is text box
                        if ($element["item"] == "text") {
                            ?>
                            <li data-item="text" data-label="">
                                <h4 class="title">Text Field</h4>
                                <p><label for="">Label</label> <input type="text" name="label" value="<?php echo $element["label"]; ?>" required/></p>
                                <span><a title="Remove This Element" class="remove">X</a></span>
                            </li>
                            <?php
                            //If item is select
                        } elseif ($element["item"] == "select") {
                            ?>
                            <li data-item="select" data-label="" data-values="">
                                <h4 class="title">Select Field</h4>
                                <p><label for="">Label</label> <input type="text" name="label" value="<?php echo $element["label"]; ?>"  required/></p>
                                <p><label for="">Values</label> <input type="text" name="values" value="<?php echo $element["values"]; ?>" required/></p>
                                <span><a title="Remove This Element" class="remove">X</a></span>
                            </li>
                            <?php
                            //If item is radio
                        } elseif ($element["item"] == "radio") {
                            ?>
                            <li data-item="radio" data-label="" data-values="">
                                <h4 class="title">Radio Buttons</h4>
                                <p><label for="">Label</label> <input type="text" name="label" value="<?php echo $element["label"]; ?>"  required/></p>
                                <p><label for="">Values</label> <input type="text" name="values" value="<?php echo $element["values"]; ?>" required/></p>
                                <span><a title="Remove This Element" class="remove">X</a></span>
                            </li>                           
                            <?php
                            //More to test later...
                        } else {
                            
                        }
                    }
					}
                    ?>
                </ul>
            </form>
            <form id="submit-data" method="post" action="options.php">
                <?php settings_fields('woo_reg_customizer_group'); ?>
                <input type="hidden" id="woo_reg_customizer" name="woo_reg_customizer" value="<?php echo get_option('woo_reg_customizer'); ?>" />
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    function render_extra_register_fields() {
        $saved_data = get_option('woo_reg_customizer');
        $saved_data = json_decode($saved_data, true);
        //var_dump($saved_data);
        if (is_array($saved_data) and !empty($saved_data)) {
        foreach ($saved_data as $element) {
            if ($element["item"] == "text") {
                $name = preg_replace('/\s+/', '_', $element["label"]);
                $name = strtolower($name);
                ?>
                <p class="form-row form-row-wide">
                    <label for="<?php echo $name; ?>"><?php echo $element["label"]; ?></label>
                    <input class="input-text" id="<?php echo $name; ?>" name="<?php echo $name; ?>" type="text" value="">
                </p>
                <?php
            } elseif ($element["item"] == "select") {
                $options = explode(',', $element["values"]);
                $options = array_map('trim', $options);
                $name = preg_replace('/\s+/', '_', $element["label"]);
                $name = strtolower($name);
                ?>
                <p class="form-row form-row-wide">
                    <label for=""><?php echo $element["label"]; ?></label>
                    <select name="<?php echo $name; ?>">
                        <option value="">Select <?php echo $element["label"]; ?></option>
                        <?php foreach ($options as $option) { ?>
                            <option value="<?php echo $option; ?>"><?php echo $option; ?></option>
                        <?php } ?>
                    </select>
                </p>
				<?php
            } elseif ($element["item"] == "radio") {
                $options = explode(',', $element["values"]);
                $options = array_map('trim', $options);
                $name = preg_replace('/\s+/', '_', $element["label"]);
                $name = strtolower($name);
                ?>
                <p class="form-row form-row-wide">
                    <label for=""><?php echo $element["label"]; ?></label>
                    <?php foreach ($options as $option) { ?>
						<input type="radio" value="<?php echo $option; ?>" name="<?php echo $name; ?>"/> <?php echo $option; ?>
                    <?php } ?>
                </p>                
                <?php
            } else {
                
            }
        }
		}
    }

    function save_extra_register_fields($customer_id) {
        $saved_data = get_option('woo_reg_customizer');
        $saved_data = json_decode($saved_data, true);
        if (is_array($saved_data) and !empty($saved_data)) {
        foreach ($saved_data as $element) {
            $name = preg_replace('/\s+/', '_', $element["label"]);
            $name = strtolower($name);
            if (isset($_POST[$name])) {
                update_user_meta($customer_id, $name, sanitize_text_field($_POST[$name]));
            }
        }
		}
    }

    function create_new_menu_link($menu_links) {
        $menu_links = array_slice($menu_links, 0, 5, true) + array('additional-info' => 'Additional Info') + array_slice($menu_links, 5, NULL, true);
        return $menu_links;
    }

    function register_endpoint() {
        // Check WP_Rewrite
        add_rewrite_endpoint('additional-info', EP_PAGES);
    }

    function my_account_additional_info_content() {

        $user_id = get_current_user_id();
        $user = get_userdata($user_id);

        if (!$user)
            return;
        ?>
        <form method='post' action=''>
            <?php wp_nonce_field('handle_additional_info_form', 'nonce_additional_info_form'); ?>
            <?php
            $saved_data = get_option('woo_reg_customizer');
            $saved_data = json_decode($saved_data, true);
            if (is_array($saved_data) and !empty($saved_data)) {
            foreach ($saved_data as $element) {
                if ($element["item"] == "text") {
                    $name = preg_replace('/\s+/', '_', $element["label"]);
                    $name = strtolower($name);
                    $user_meta_data = get_user_meta($user_id, $name, true);
                    ?>
                    <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                        <label for="<?php echo $name; ?>"><?php echo $element["label"]; ?></label>
                        <input type="text" class="" name="<?php echo $name; ?>" id="<?php echo $name; ?>" value="<?php echo $user_meta_data; ?>">
                    </p>
                    <?php
                } elseif ($element["item"] == "select") {
                    $options = explode(',', $element["values"]);
                    $options = array_map('trim', $options);
                    $name = preg_replace('/\s+/', '_', $element["label"]);
                    $name = strtolower($name);
                    $user_meta_data = get_user_meta($user_id, $name, true);
                    ?>
                    <p class="form-row form-row-wide">
                        <label for=""><?php echo $element["label"]; ?></label>
                        <select name="<?php echo $name; ?>">
                            <?php
                            foreach ($options as $option) {
                                $selected = $user_meta_data == $option ? "selected" : "";
                                ?>
                                <option value="<?php echo $option; ?>" <?php echo $selected; ?> ><?php echo $option; ?></option>
                            <?php } ?>
                        </select>
                    </p>
                    <?php
                } elseif ($element["item"] == "radio") {
                    $options = explode(',', $element["values"]);
                    $options = array_map('trim', $options);
                    $name = preg_replace('/\s+/', '_', $element["label"]);
                    $name = strtolower($name);
                    $user_meta_data = get_user_meta($user_id, $name, true);
                    ?>
                    <p class="form-row form-row-wide">
                        <label for=""><?php echo $element["label"]; ?></label>
                        <?php
                        foreach ($options as $option) {
							$selected = $user_meta_data == $option ? "checked" : "";
							?>
                            <input type="radio" value="<?php echo $option; ?>" name="<?php echo $name; ?>" <?php echo $selected; ?> ><?php echo $option; ?></option>
                        <?php } ?>
                    </p>                    
                    <?php
                } else {
                    
                }
            }
			}
            ?>
            <input type='submit' value='Submit'/>
        </form>
        <?php
    }

    function handle_additional_info_form() {
        if (!empty($_POST['nonce_additional_info_form'])) {
            if (!wp_verify_nonce($_POST['nonce_additional_info_form'], 'handle_additional_info_form')) {
                die('You are not authorized to perform this action.');
            } else {
                $customer_id = get_current_user_id();

                $saved_data = get_option('woo_reg_customizer');
                $saved_data = json_decode($saved_data, true);
                if (is_array($saved_data) and !empty($saved_data)) {
                foreach ($saved_data as $element) {
                    $name = preg_replace('/\s+/', '_', $element["label"]);
                    $name = strtolower($name);
                    if (isset($_POST[$name])) {
                        update_user_meta($customer_id, $name, sanitize_text_field($_POST[$name]));
                    }
                }
				}
            }
        }
    }

}

$WooRegisterCustomizer = new WooRegisterCustomize;
