<?php
/**
 * Copyright (c) 2020 - Thomas Sjolshagen <thomas@eighty20results.com>
 * ALL RIGHTS RESERVED
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */
namespace E20R\SingleUseTrial\Views;

/**
 * @class E20R\SingleUseTrial\Views\Settings
 */
class Settings {

    /**
     * Generate the view for the Membership Level Settings
     * 
     * @param array $level_settings
     * @param int $level_id
     * 
     * @return string
     */
    public static function membership_level( $level_settings, $level_id ) {
        $level_html = '';
        $level_html .= sprintf('\t<h3 class="topborder">%s</h3>\n', \__('Single Use Trial Settings', 'e20r-single-use-trial' ));
        $level_html .= sprintf('\t<p class="e20r-description">\n');
        $level_html .= sprintf('\t\t<p class="e20r-description">\n');
        $level_html .= sprintf('\t\t\t%s\n</p>\n', \__(
                "Should we prevent members from signing up for this membership level more than once?",
                "e20r-single-use-trial"));
        $level_html .= sprintf('\t<table class="form-table">\n');
        $level_html .= sprintf('\t\t<tbody>\n');
        $level_html .= sprintf('\t\t\t<tr>\n');
        $level_html .= sprintf('\t\t\t\t<th scope="row" valign="top"><label for="e20r-single-use-trial">\n');
        $level_html .= sprintf('\t\t\t\t\t%s\n', __( "Limit sign-ups to single use?", "e20r-single-use-trial" ));
        $level_html .= sprintf('\t\t\t\t\t</label>\n');
        $level_html .= sprintf('\t\t\t\t</th>\n');
        $level_html .= sprintf('\t\t\t\t<td>\n');
        $checked_html = isset( $level_settings[ $level_id ] ) ?
                        \checked( (bool)$level_settings[ $level_id ], true ) :
                        null;
        $level_html .= sprintf('\t\t\t\t\t<input type="checkbox" name="e20r-single-use-trial" id="e20r-single-use-trial" value="1" %s>\n', $checked_html);
        $level_html .= sprintf('\t\t\t\t</td>\n');
        $level_html .= sprintf('\t\t\t</tr>\n');
        $level_html .= sprintf('\t\t</tbody>\n');
        $level_html .= sprintf('\t</table>\n');

        return $level_html;
    }
}
