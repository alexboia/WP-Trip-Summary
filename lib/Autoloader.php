<?php
class Abp01_Autoloader {
    private static $_libDir;

    private static $_initialized = false;

    private static $_prefix = 'Abp01_';

    public static function init($libDir) {
        if (!self::$_initialized) {
            spl_autoload_register(array(__CLASS__, 'autoload'));
            self::$_libDir = $libDir;
            self::$_initialized = true;
        }
    }

    private static function autoload($className) {
        $classPath = null;
        if (strpos($className, self::$_prefix) === 0) {
            $classPath = str_replace(self::$_prefix, '', $className);
            $classPath = self::_getRelativePath($classPath);
            $classPath = self::$_libDir . '/' . $classPath . '.php';
            if ($className == 'Abp01_Route_Track_GPXDocumentParser') {
                var_dump($classPath);
                var_dump(file_exists($classPath));
            }
        } else {
            $classPath = self::$_libDir . '/3rdParty/' . $className . '.php';
        }
        if (!empty($classPath) && file_exists($classPath)) {
            require_once $classPath;
        }
    }

    private static function _getRelativePath($className) {
        $classPath = array();
        $pathParts = explode('_', $className);
        $className = array_pop($pathParts);
        foreach($pathParts as $namePart) {
            $namePart[0] = strtolower($namePart[0]);
            $classPath[] = $namePart;
        }
        $classPath[] = $className;
        return implode('/', $classPath);
    }
}