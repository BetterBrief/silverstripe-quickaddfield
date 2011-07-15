<?php

define('MOD_QA_PATH',rtrim(dirname(__FILE__), DIRECTORY_SEPARATOR));
$folders = explode(DIRECTORY_SEPARATOR,MOD_QA_PATH);
define('MOD_QA_DIR',rtrim(array_pop($folders),DIRECTORY_SEPARATOR));
unset($folders);
