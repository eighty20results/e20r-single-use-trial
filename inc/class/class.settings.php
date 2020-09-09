<?php

namespace E20R\SingleUseTrial\Views;

class Settings {

    public static function membership_level( $level_settings, $level_id ) {

        printf( '\t<h3 class="topborder">%s</h3>\n', __( 'Single Use Trial Settings', 'e20r-single-use-trial' ));
        printf('\t<p class="e20r-description">\n');
        printf('\t\t<p class="e20r-description">\n');
        printf('\t\t\t%s\n</p>\n', __(
                "Should we prevent members from signing up for this membership level more than once?",
                "e20r-single-use-trial"
            )
        );
        printf('\t<table class="form-table">\n');
        printf('\t\t<tbody>\n');
        printf('\t\t\t<tr>\n');
        printf('\t\t\t\t<th scope="row" valign="top"><label for="e20r-single-use-trial">\n');
        printf('\t\t\t\t\t%s\n', __( "Limit sign-ups to single use?", "e20r-single-use-trial" ));
        printf('\t\t\t\t\t</label>\n');
        printf('\t\t\t\t</th>\n');
        printf('\t\t\t\t<td>\n');
        $checked_html = isset( $level_settings[ $level_id ] ) ?
                        checked( (bool)$level_settings[ $level_id ], true ) :
                        null;
        printf('\t\t\t\t\t<input type="checkbox" name="e20r-single-use-trial" id="e20r-single-use-trial" value="1" %s>\n', $checked_html);
        printf('\t\t\t\t</td>\n');
        printf('\t\t\t</tr>\n');
        printf('\t\t</tbody>\n');
        printf('\t</table>\n');
}