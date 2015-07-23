<?php
if (!defined('ABP01_LOADED') || !ABP01_LOADED) {
    exit;
}

/**
 * This class implements a basic notion of a unit system.
 * It provides the basic interface required for this plug-in as well as a factory method to create concrete unit systems.
 * Currently supported unit systems are:
 * - metric;
 * - imperial.
 * */
abstract class Abp01_UnitSystem {
    /**
     * Represents the metric system.
     * Use this as an argument of Abp01_UnitSystem::create() to get a new instance of the metric system class
     * */
    const METRIC = 'metric';

    /**
     * Represents the imperial system.
     * Use this as an argument of Abp01_UnitSystem::create() to get a new instance of the imperial system class
     * */
    const IMPERIAL = 'imperial';

    /**
     * Creates a concrete unit system using the given key.
     * If no unit system is supported for the given key, null is returned.
     * @param string $system The unit system
     * @return Abp01_UnitSystem A corresponding derived class or null if no corresponding class is found
     * */
    public static function create($system) {
        if (!self::isSupported($system)) {
            return null;
        }
        $className = 'Abp01_UnitSystem_' . ucfirst($system);
        if (class_exists($className)) {
            return new $className();
        } else {
            return null;
        }
    }

    /**
     * Checks if a unit system for the given key is supported or not
     * @param string $system The unit system key to check for
     * @return bool True if supported, false otherwise
     * */
    public static function isSupported($system) {
        return $system == self::METRIC || $system == self::IMPERIAL;
    }

    /**
     * Returns the unit, as a symbol, used to measure distances
     * @return string The distance measurement unit symbol
     * */
    abstract public function getDistanceUnit();

    /**
     * Returns the unit, as a symbol, used to measure lengths (linear object dimensions)
     * @return string The length measurement unit symbol
     * */
    abstract public function getLengthUnit();

    /**
     * Returns the unit, as a symbol, used to measure heights
     * @return string The height measurement unit symbol
     * */
    abstract public function getHeightUnit();
}
