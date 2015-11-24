<?php
// @codingStandardsIgnoreStart
class AdminTest extends WP_Ajax_UnitTestCase {
  // @codingStandardsIgnoreEnd
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
    
    parent::setUp();
    
    $this->user = get_user_by('id', $this->factory->user->create(
      array(
        'role' => 'administrator'
      )
    ));
    
    wp_set_current_user($this->user->ID);
    
    $_POST = array_merge((array)$_POST, array(
      'action' =>	'heartbeat',
      'custom-caps-update-nonce' => wp_create_nonce('custom-caps-update'),
      '_wp_http_referer' => get_admin_url()
    ));
    
    $_REQUEST = array_merge($_POST, $_GET);
  }
  
  public function testAddCap() {
    global $wpdb;
    
    ob_start();
        
    // Post to the update function
    $_POST['custom_caps'] = array('create_post_tag_terms' => 'on');
    
    // Mimic posting to the profile edit screen on the admin with $user->id as
    // the loged in user
    do_action('admin_init');
    do_action('personal_options_update', $this->user->ID);
    
    $this->user = get_user_by('id', $this->user->ID);
    
    $caps = array_keys($this->user->caps);
    
    $this->assertEquals(true, in_array('create_post_tag_terms', $caps));
  }
  
  public function testRemoveCap() {
    global $wpdb;
    
    // Add the test cap
    $this->user->add_cap('create_post_tag_terms');
    
    // Remove the test cap
    
    // Post to the update function with an empty array (which is the same as the
    // user unchecking all custom caps)
    $_POST['custom_caps'] = array();
    
    do_action('admin_init');
    do_action('personal_options_update', $this->user->ID);
    
    $this->user = get_user_by('id', $this->user->ID);
    
    $caps = array_keys($this->user->caps);
    
    $this->assertEquals(!in_array('create_post_tag_terms', $caps), true);
  }
  
}
