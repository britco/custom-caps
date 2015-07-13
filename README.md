# custom-caps

Adds ability to edit custom capabilities for users in the admin. Why this isn't in core is beyond me.

## How to use

Register a new capability:

````
add_filter('custom_caps', function($caps) {
  $caps[] = 'create_post_tag_terms';
  return $caps;
});
````

`$caps` is an array of capabilities (caps) with the format $label => $value.
Label should be the human readable version of the capability, and value is the one
stored in the database.

That's it! Now you can edit that capability for users by going to edit user screen.

**Before:**
![image](https://cloud.githubusercontent.com/assets/1239145/8656986/d4e216c8-294f-11e5-981b-ffc1fea9d9e7.png)

**After::**
![image](https://cloud.githubusercontent.com/assets/1239145/8657041/2f7189a2-2950-11e5-9006-31f1dd5cab04.png)