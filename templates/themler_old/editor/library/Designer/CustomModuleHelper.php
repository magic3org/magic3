<?php
    class CustomModuleHelper extends JModuleHelper {

        public static function clean() {
            $modules = & JModuleHelper::_load();
            $modules = null;
        }
    }
?>