<?php
class Abp01_Route_Track_AltitudeProfile {
    public $profile;

    public $distanceUnit;

    public $heightUnit;

    public function __construct(array $profile, $distanceUnit, $heightUnit) {
        $this->profile = $profile;
        $this->distanceUnit = $distanceUnit;
        $this->heightUnit = $heightUnit;
    }

    public static function fromSerializedDocument($serialized) {
        if (!$serialized || empty($serialized)) {
            return null;
        }
        return unserialize($serialized);
    }

    public function toPlainObject() {
        $data = new stdClass();
        $data->profile = &$this->profile;
        $data->distanceUnit = $this->distanceUnit;
        $data->heightUnit = $this->heightUnit;
        return $data;
    }

    public function matchesUnitSystem(Abp01_UnitSystem $check) {
        return $this->distanceUnit === $check->getDistanceUnit()
            && $this->heightUnit === $check->getHeightUnit();
    }

    public function serializeDocument() {
        return serialize($this);
    }

    public function toJson() {
        return json_encode($this);
    }
}