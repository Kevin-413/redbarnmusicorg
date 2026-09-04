<?php
/*
Copyright 2012 DIYthemes, LLC. Patent pending. All rights reserved.
License: DIYthemes Software License Agreement
License URI: https://diythemes.com/thesis/rtfm/software-license-agreement/
*/
require_once(TEMPLATEPATH. '/thesis.php');
global $thesis; // explicit global declaration for WP-CLI compatibility
$thesis = new thesis;
/*
WARNING: This file will be overwritten during Thesis updates.
If you wish to add your own PHP customizations, you should use
your current Skin’s custom.php file:

/wp-content/thesis/skins/your-current-skin/custom.php

or the Thesis master.php file:

/wp-content/thesis/master.php

For reference, the custom.php file applies only to your current Skin, but
the master.php file applies to your site, regardless of the current Skin.
*/