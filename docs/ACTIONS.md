## Actions supported by this plugin

*Note:* The Filters and Actions sections are incomplete!

To best understand how to extend this plugin, we recommend searching through the plugin sources for calls to the `apply_filter()` and `do_action()` functions.

### 

Purpose: 

Dependencies: N/A

Default: 

```php

```
Example:
```php
add_action(
	''
	function( $array ) {
		return $array;
	},
	11
);
```

```php
add_action(
	'e20r_memberslist_process_bulk_cancel_done',
	function( $member_info_array ) {
		foreach( $member_info_array as $key => $user_info ) {
			// Cancel the membership level(s) for the user record based on WP_User->id
			...
		}
	},
	11
);
```
### e20r_memberslist_process_custom_bulk_actions

Purpose: Allows processing of custom bulk actions in the Members List

Dependencies: Depends on the '[e20r_memberlist_bulk_actions](https://github.com/eighty20results/e20r-members-list/docs/FILTERS.md#e20r_memberlist_bulk_actions) filter

Default: List (array) of bulk actions supplied by the calling function

```php
add_action(
	'e20r_memberslist_process_custom_bulk_actions',
	function( $nonce, $action, $bulk_actions, $data, $plural_name ) {
		// Exit if we're not processing our custom action.
	 	if ( ! in_array( 'bulk-my_delete_action', $bulk_actions ) ) {
	 		return;
	 	}
	 	...
	},
	10
);  
```
