## Filters supported by this plugin

*Note:* The Filters and Actions sections are incomplete!

To best understand how to extend this plugin, we recommend searching through the plugin sources for calls to the `apply_filter()` and `do_action()` functions.

### e20r_set_single_use_trial_use_ip

**NOTE**: This is potentially very unreliable and should not be used.

Modifies: Whether to try and use the IP address of the connecting system to determine if they're allowed to sign up for the trial membership.

Purpose: Try to use the IP address of the connecting system to determine if they're allowed to sign up to the trial membership level.

Dependencies: N/A

Default: Boolean `false` value

Example: 
```php
add_filter(
	'e20r_set_single_use_trial_use_ip',
	'__return_true'
);
```

### e20r_set_single_use_trial_level_ids

Modifies: The list of membership level IDs that are considererd trial memberships. By default, we only select free (zero cost) membership level IDs for this list, but you can use this filter to add for-fee levels as well, or remove some free levels, if you're so inclined.

Dependencies: N/A

Default: The PMPro Membership Level IDs that are free.

Example:
```php
add_filter(
	'e20r_set_single_use_trial_level_ids',
	function( $list_of_free_level_ids ) {
		// Add the membership level that has an ID of 100
		$list_of_free_level_ids[] = 100;
		// Return the list 
		return $list_of_free_level_ids;
	}
);
```
