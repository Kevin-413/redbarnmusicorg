<!DOCTYPE html>
<?php
global $thesis;
echo apply_filters('thesis_html_tag', "<html". apply_filters('thesis_html_attributes', $thesis->wp->language_attributes()). ">"). "\n";
if (is_object($thesis->skin->_boxes->active['thesis_html_head']))
	$thesis->skin->_boxes->active['thesis_html_head']->html();
if (is_object($thesis->skin->_boxes->active['thesis_html_body']))
	$thesis->skin->_boxes->active['thesis_html_body']->html(); ?>
</html>