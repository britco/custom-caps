<?php
namespace CustomCaps;

/*
Plugin Name: Custom Caps
Plugin URI:
Description: Enables editing of custom capabilities for users
Author: Paul Dufour
Version: 0.5
Author URI: http://www.brit.co
*/


// Get the list of custom caps.. Gets all caps added manually in the db && ones
// defined via the filter custom_caps.
function get_custom_caps($user_id='') {
  global $wp_roles;
  
  $db_custom_caps = array();

  // See https://github.com/WordPress/WordPress/blob/4.0.3/wp-admin/user-edit.php#L504
  if(!empty($user_id)) {
    if(!is_object($user_id)) {
      $user = get_user_by('id', $user_id);
    } else {
      $user = $user_id;
    }

    foreach ($user->caps as $cap => $value) {
      if (!$wp_roles->is_role($cap)) {
        $db_custom_caps[] = $cap;
      }
    }
  }
  
  $plugin_custom_caps = apply_filters('custom_caps', array());
  
  $custom_caps = array_unique(array_merge($db_custom_caps, $plugin_custom_caps));
  
  sort($custom_caps);
  
  return $custom_caps;
}

// Display cap-edit form on user-edit
function cap_edit_view($user_id) {
  global $profileuser;
  
  if (!current_user_can('edit_users') || !current_user_can('edit_user', $user_id)) {
    return false;
  }
  
  $custom_caps = array_fill_keys(get_custom_caps($profileuser), 0);
  
  // Fill in grant status for any permissions that have been set for this user
  $user_caps = array();
  foreach($profileuser->caps as $cap => $grant) {
    if(!array_key_exists($cap, $custom_caps)) {
      continue;
    }
    
    $custom_caps[$cap] = $grant;
  }

  ?>
  <h3>Additional Capabilities</h3>
  <table class="form-table">
  <tbody><tr>
    <th><label>Capabilities</label></th>
    <td>
      <?php foreach($custom_caps as $cap => $grant): ?>
        <p>
          <label>
            <input name="custom_caps[<?php echo esc_attr($cap); ?>]" type="checkbox" <?php checked($grant); ?> />
            <?php echo esc_html($cap); ?>
          </label>
        </p>
      <?php endforeach; ?>
    </td>
  </tr></tbody></table>
<?php
}

// Add and remove caps as needed when a user is edited from the admin
function cap_edit_save($user_id) {
  if (!current_user_can('edit_users') || !current_user_can('edit_user', $user_id)) {
    return false;
  }
  
  $custom_caps = get_custom_caps($user_id);
  
  // Find out which caps need to get added, and which need to get removed
  if(empty($_POST['custom_caps'])) {
    $remove_caps = $custom_caps;
    $add_caps = array();
  } else {
    $add_caps = (array)array_intersect(array_keys($_POST['custom_caps']), $custom_caps);
    $remove_caps = (array)array_diff($custom_caps, array_keys($_POST['custom_caps']));
  }
  
  if(empty($add_caps) && empty($remove_caps)) {
    return;
  }
  
  $user = get_user_by('id', $user_id);
  
  foreach($add_caps as $cap) {
    $user->add_cap($cap);
  }
  
  foreach($remove_caps as $cap) {
    $user->remove_cap($cap);
  }
}

add_action('edit_user_profile', __NAMESPACE__ . '\cap_edit_view');
add_action('show_user_profile', __NAMESPACE__ . '\cap_edit_view');
add_action('edit_user_profile_update', __NAMESPACE__ . '\cap_edit_save');
add_action('personal_options_update', __NAMESPACE__ . '\cap_edit_save');

// Don't need to show this anymore since we replaced it with a checklist
add_filter('additional_capabilities_display', '__return_false');
