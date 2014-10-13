<?php
if (!defined('ABP01_LOADED') || !ABP01_LOADED) {
    exit;
}

class Abp01_Route_Manager {
    private static $_instance = null;

    private $_lastError = null;

    private $_env = null;

    public static function getInstance() {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    private function __construct() {
        $this->_env = Abp01_Env::getInstance();
    }

    public function saveRouteInfo($postId, $currentUserId, Abp01_Route_Info $info) {
        $postId = intval($postId);
        if ($postId <= 0 || $info == null) {
            throw new InvalidArgumentException();
        }

        $db = $this->_env->getDb();
        $table = $this->_env->getRouteDetailsTableName();

        $data = array(
            'post_ID' => $postId,
            'route_type' => $info->getType(),
            'route_data_serialized' => $info->toJson(),
            'route_data_last_modified_by' => $currentUserId,
            'route_data_last_modified_at' => $db->now()
        );

        $db->where('post_ID', $postId);
        if ($db->update($table, $data) !== false) {
            if ($db->count) {
                return true;
            } else {
                if ($db->insert($table, $data) === false) {
                    return false;
                } else {
                    return true;
                }
            }
        } else {
            return false;
        }
    }

    public function deleteRouteInfo($postId) {
        $postId = intval($postId);
        if ($postId <= 0) {
            throw new InvalidArgumentException();
        }

        $db = $this->_env->getDb();
        $table = $this->_env->getRouteDetailsTableName();

        $db->where('post_ID', $postId);
        if ($db->delete($table) === false) {
            return false;
        } else {
            return true;
        }
    }

    public function saveRouteTrack($postId, $currentUserId, Abp01_Route_Track $track) {
        $postId = intval($postId);
        if ($postId <= 0) {
            throw new InvalidArgumentException();
        }

        $db = $this->_env->getDb();
        $table = $this->_env->getRouteTrackTableName();
        $bounds = $track->getBounds();

        $data = array(
            'post_ID' => $postId,
            'route_track_file' => $track->getFile(),
            'route_min_lat' => $bounds->minLat,
            'route_min_lng' => $bounds->minLng,
            'route_max_lat' => $bounds->maxLat,
            'route_max_lng' => $bounds->maxLng,
            'route_min_alt' => $bounds->minAlt,
            'route_max_alt' => $bounds->maxAlt,
            'route_track_modified_at' => $db->now(),
            'route_track_modified_by' => $currentUserId
        );

        $db->where('post_ID', $postId);
        if ($db->update($table, $data)) {
            if ($db->count) {
                return true;
            } else {
                if ($db->insert($table, $data) === false) {
                    return false;
                } else {
                    return true;
                }
            }
        } else {
            return false;
        }
    }

    public function deleteRouteTrack($postId) {
        $postId = intval($postId);
        if ($postId <= 0) {
            throw new InvalidArgumentException();
        }

        $db = $this->_env->getDb();
        $table = $this->_env->getRouteTrackTableName();

        $db->where('post_ID', $postId);
        if ($db->delete($table) === false) {
            return false;
        } else {
            return true;
        }
    }

    public function getRouteInfo($postId) {
        $postId = intval($postId);
        if ($postId <= 0) {
            throw new InvalidArgumentException();
        }

        $db = $this->_env->getDb();
        $table = $this->_env->getRouteDetailsTableName();

        $db->where('post_ID', $postId);
        $row = $db->getOne($table);
        if (!$row) {
            return null;
        }

        $type = isset($row['route_type']) ? $row['route_type'] : null;
        $json = isset($row['route_data_serialized']) ? $row['route_data_serialized']: null;

        if (!$type || !$json) {
            return null;
        }

        return Abp01_Route_Info::fromJson($type, $json);
    }

    public function getRouteTrack($postId) {
        $postId = intval($postId);
        if ($postId <= 0) {
            throw new InvalidArgumentException();
        }

        $db = $this->_env->getDb();
        $table = $this->_env->getRouteTrackTableName();

        $db->where('post_ID', $postId);
        $row = $db->getOne($table);
        if (!$row) {
            return null;
        }

        if (isset($row['route_track_file']) && $row['route_track_file']) {
            $file = $row['route_track_file'];
            $bounds = new stdClass();
            $bounds->minLat = floatval($row['route_min_lat']);
            $bounds->minLng = floatval($row['route_min_lng']);
            $bounds->maxLat = floatval($row['route_max_lat']);
            $bounds->maxLng = floatval($row['route_max_lng']);
            $bounds->minAlt = floatval($row['route_min_alt']);
            $bounds->maxAlt = floatval($row['route_max_alt']);
            return new Abp01_Route_Track($file, $bounds);
        } else {
            return null;
        }
    }

    public function hasRouteTrack($postId) {
        $postId = intval($postId);
        if ($postId <= 0) {
            throw new InvalidArgumentException();
        }

        $db = $this->_env->getDb();
        $table = $this->_env->getRouteTrackTableName();

        $db->where('post_ID', $postId);
        $stats = $db->getOne($table, 'COUNT(*) AS cnt');
        if ($stats && is_array($stats)) {
            return $stats['cnt'] > 0;
        }

        return false;
    }

    public function hasRouteInfo($postId) {
        $postId = intval($postId);
        if ($postId <= 0) {
            throw new InvalidArgumentException();
        }

        $db = $this->_env->getDb();
        $table = $this->_env->getRouteDetailsTableName();

        $db->where('post_ID', $postId);
        $stats = $db->getOne($table, 'COUNT(*) AS cnt');
        if ($stats && is_array($stats)) {
            return $stats['cnt'] > 0;
        }

        return false;
    }

    public function getLastError() {
        return $this->_lastError;
    }
}