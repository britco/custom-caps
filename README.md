# custom-caps

[![Circle CI](https://circleci.com/gh/britco/custom-caps.svg?style=svg)](https://circleci.com/gh/britco/custom-caps)

Adds ability to edit custom capabilities for users in the admin. Why this isn't in core is beyond me.

## How to use

Register a new capability:

````
add_filter('custom_caps', function($caps) {
  $caps[] = 'create_post_tag_terms';
  return $caps;
});
````

`$caps` is an array of custom capabilities (caps) that admins should be able to edit.

That's it! Now you can edit that capability for users by going to edit user screen.

**Before**

![image](https://cloud.githubusercontent.com/assets/1239145/8657168/e3ba1262-2950-11e5-8168-083f5d24e6ee.png)

**After**

![image](https://cloud.githubusercontent.com/assets/1239145/8657163/d96c2480-2950-11e5-944c-f5fd8bee549c.png)
