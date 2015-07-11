<?php

class AdminTest extends WP_Ajax_UnitTestCase {
  // ^ Doesn't actually use ajax testing, but the class has useful helpers that
  // aren't available in the normal test case
  protected $updated_usermeta = array();
  
  public function setUp() {
    // Add new custom cap
    add_filter('custom_caps', function($caps) {
      $caps[] = 'create_post_tag_terms';
      return $caps;
    });
    
    // Track metadata changes
    add_filter('update_user_metadata', function($true, $object_id, $meta_key, $meta_value, $prev_value) {
      $this->updated_usermeta[$object_id][$meta_key] = $meta_value;
      return $true;
    }, 10, 5);
    
    return call_user_func_array(array($this, 'parent::setUp'), func_get_args());
  }
  
  public function testAddCap() {
    global $wpdb;
    
    // Set current user to the admin user
    $user = get_user_by('id', 1);
    wp_set_current_user(1);
    
    // Post to the update function
    $_POST['custom_caps'] = array('create_post_tag_terms' => 'on');
    
    // Mimic posting to the profile edit screen on the admin with $user->id as
    // the loged in user
    do_action('admin_init');
    do_action('personal_options_update', $user->ID);
    
    $user = get_user_by('id', 1);
    
    $caps = array_keys($user->caps);
    
    $this->assertEquals(in_array('create_post_tag_terms', $caps), true);
  }
  
  public function testRemoveCap() {
    global $wpdb;
    
    // Set current user to the admin user
    $user = get_user_by('id', 1);
    wp_set_current_user(1);
    
    // Add the test cap
    $user->add_cap('create_post_tag_terms');
    
    $user = get_user_by('id', 1);
    
    // Remove the test cap
    
    // Post to the update function with an empty array (which is the same as the
    // user unchecking all custom caps)
    $_POST['custom_caps'] = array();
    
    do_action('admin_init');
    do_action('personal_options_update', $user->ID);
    
    $user = get_user_by('id', 1);
    
    $caps = array_keys($user->caps);
    
    $this->assertEquals(!in_array('create_post_tag_terms', $caps), true);
  }
}

