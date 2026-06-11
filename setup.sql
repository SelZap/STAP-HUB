-- ============================================================
-- STAP-HUB Clean Setup Script
-- ============================================================

DROP DATABASE IF EXISTS stap_hub;
CREATE DATABASE stap_hub;
USE stap_hub;

-- ============================================================
-- TABLE: admins
CREATE TABLE admins (
    admin_id        INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    admin_name      VARCHAR(100)    NOT NULL,
    email           VARCHAR(150)    NOT NULL UNIQUE,
    password_hash   VARCHAR(255)    NOT NULL,
    is_superuser    TINYINT(1)      NOT NULL DEFAULT 0,
    created_at      TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    last_login      TIMESTAMP       NULL,

    PRIMARY KEY (admin_id)
);

-- ============================================================
-- TABLE: stap_nodes
CREATE TABLE stap_nodes (
    node_id         INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    node_name       VARCHAR(100)    NOT NULL,
    location_label  VARCHAR(150)    NOT NULL,
    api_key         VARCHAR(255)    NOT NULL UNIQUE,
    last_heartbeat  TIMESTAMP       NULL,
    status          ENUM(
                        'online',
                        'offline',
                        'error'
                    )               NOT NULL DEFAULT 'offline',
    registered_at   TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (node_id)
);

-- ============================================================
-- TABLE: cameras
CREATE TABLE cameras (
    camera_id       INT UNSIGNED        NOT NULL AUTO_INCREMENT,
    node_id         INT UNSIGNED        NOT NULL,
    usb_index       TINYINT UNSIGNED    NOT NULL,
    label           VARCHAR(100)        NOT NULL,
    direction       VARCHAR(50)         NULL,
    status          ENUM(
                        'active',
                        'inactive',
                        'error'
                    )                   NOT NULL DEFAULT 'active',
    registered_at   TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (camera_id),
    FOREIGN KEY (node_id)
        REFERENCES stap_nodes (node_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    INDEX idx_cameras_node_id (node_id)
);

-- ============================================================
-- TABLE: admin_activity_logs
CREATE TABLE admin_activity_logs (
    log_id          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    admin_id        INT UNSIGNED    NOT NULL,
    target_type     VARCHAR(50)     NULL,
    target_id       INT UNSIGNED    NULL,
    target_label    VARCHAR(100)    NULL,
    details         TEXT            NULL,
    performed_at    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (log_id),
    FOREIGN KEY (admin_id)
        REFERENCES admins (admin_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    INDEX idx_logs_admin_id     (admin_id),
    INDEX idx_logs_performed_at (performed_at)
);

-- ============================================================
-- TABLE: traffic_snapshots (with March 30 alterations applied)
CREATE TABLE traffic_snapshots (
    snapshot_id         INT UNSIGNED        NOT NULL AUTO_INCREMENT,
    camera_id           INT UNSIGNED        NOT NULL,
    vehicle_count       SMALLINT UNSIGNED   NOT NULL DEFAULT 0,
    cars                SMALLINT UNSIGNED   NOT NULL DEFAULT 0,
    trucks              SMALLINT UNSIGNED   NOT NULL DEFAULT 0,
    motorcycles         SMALLINT UNSIGNED   NOT NULL DEFAULT 0,
    mini_bus            SMALLINT UNSIGNED   NOT NULL DEFAULT 0,
    ambulance           SMALLINT UNSIGNED   NOT NULL DEFAULT 0,
    fire_truck          SMALLINT UNSIGNED   NOT NULL DEFAULT 0,
    tricycle            SMALLINT UNSIGNED   NOT NULL DEFAULT 0,
    jeepney             SMALLINT UNSIGNED   NOT NULL DEFAULT 0,
    congestion_level    ENUM('A','B','C','D','E','F') NOT NULL DEFAULT 'A',
    image_url           VARCHAR(500)        NULL,
    video_url           VARCHAR(500)        NULL,
    captured_at         TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (snapshot_id),
    FOREIGN KEY (camera_id)
        REFERENCES cameras (camera_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    INDEX idx_snapshots_camera_id   (camera_id),
    INDEX idx_snapshots_captured_at (captured_at)
);

-- ============================================================
-- TABLE: traffic_lights
CREATE TABLE traffic_lights (
    light_id        INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    node_id         INT UNSIGNED    NOT NULL,
    location_label  VARCHAR(150)    NOT NULL,
    current_state   ENUM(
                        'red',
                        'yellow',
                        'green'
                    )               NOT NULL DEFAULT 'red',
    mode            ENUM(
                        'auto',
                        'manual',
                        'hazard'
                    )               NOT NULL DEFAULT 'auto',
    green_duration  SMALLINT UNSIGNED NULL,
    red_duration    SMALLINT UNSIGNED NULL,
    last_updated    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP
                                    ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (light_id),
    FOREIGN KEY (node_id)
        REFERENCES stap_nodes (node_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    INDEX idx_lights_node_id (node_id)
);

-- ============================================================
-- TABLE: weather_logs
CREATE TABLE weather_logs (
    weather_id      INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    node_id         INT UNSIGNED    NOT NULL,
    rain_intensity  ENUM(
                        'none',
                        'light',
                        'moderate',
                        'heavy'
                    )               NOT NULL DEFAULT 'none',
    recorded_at     TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (weather_id),
    FOREIGN KEY (node_id)
        REFERENCES stap_nodes (node_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    INDEX idx_weather_node_id     (node_id),
    INDEX idx_weather_recorded_at (recorded_at)
);

-- ============================================================
-- TABLE: alerts
CREATE TABLE alerts (
    alert_id        INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    node_id         INT UNSIGNED    NOT NULL,
    camera_id       INT UNSIGNED    NULL,
    type            VARCHAR(50)     NOT NULL,
    severity        ENUM(
                        'low',
                        'medium',
                        'high',
                        'critical'
                    )               NOT NULL DEFAULT 'low',
    message         TEXT            NOT NULL,
    is_resolved     TINYINT(1)      NOT NULL DEFAULT 0,
    triggered_at    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    resolved_at     TIMESTAMP       NULL,

    PRIMARY KEY (alert_id),
    FOREIGN KEY (node_id)
        REFERENCES stap_nodes (node_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    FOREIGN KEY (camera_id)
        REFERENCES cameras (camera_id)
        ON UPDATE CASCADE
        ON DELETE SET NULL,

    INDEX idx_alerts_node_id      (node_id),
    INDEX idx_alerts_camera_id    (camera_id),
    INDEX idx_alerts_is_resolved  (is_resolved),
    INDEX idx_alerts_triggered_at (triggered_at)
);

-- ============================================================
-- TABLE: footage_requests (with March 30 alterations applied)
CREATE TABLE footage_requests (
    request_id              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    requester_name          VARCHAR(150)    NOT NULL,
    requester_organization  VARCHAR(150)    NULL,
    requester_address       TEXT            NULL,
    camera_id               INT UNSIGNED    NOT NULL,
    requester_email         VARCHAR(150)    NOT NULL,
    requester_contact       VARCHAR(50)     NOT NULL,
    incident_date           DATE            NULL,
    incident_time           VARCHAR(50)     NULL,
    names_involved          TEXT            NULL,
    incident_description    TEXT            NULL,
    request_nature          ENUM(
                                'academic',
                                'personal',
                                'legal',
                                'media',
                                'other'
                            )               NOT NULL,
    footage_date            DATE            NOT NULL,
    footage_time_start      TIME            NOT NULL,
    footage_time_end        TIME            NOT NULL,
    status                  ENUM(
                                'pending',
                                'under_review',
                                'approved',
                                'rejected'
                            )               NOT NULL DEFAULT 'pending',
    handled_by              INT UNSIGNED    NULL,
    created_at              TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at              TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP
                                            ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (request_id),
    FOREIGN KEY (camera_id)
        REFERENCES cameras (camera_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    FOREIGN KEY (handled_by)
        REFERENCES admins (admin_id)
        ON UPDATE CASCADE
        ON DELETE SET NULL,

    INDEX idx_requests_camera_id  (camera_id),
    INDEX idx_requests_handled_by (handled_by),
    INDEX idx_requests_status     (status)
);

-- ============================================================
-- TABLE: request_messages
CREATE TABLE request_messages (
    message_id          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    request_id          INT UNSIGNED    NOT NULL,
    sender_type         ENUM(
                            'admin',
                            'system'
                        )               NOT NULL DEFAULT 'system',
    admin_id            INT UNSIGNED    NULL,
    message             TEXT            NOT NULL,
    requirement_list    TEXT            NULL,
    sent_at             TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (message_id),
    FOREIGN KEY (request_id)
        REFERENCES footage_requests (request_id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    FOREIGN KEY (admin_id)
        REFERENCES admins (admin_id)
        ON UPDATE CASCADE
        ON DELETE SET NULL,

    INDEX idx_messages_request_id (request_id),
    INDEX idx_messages_admin_id   (admin_id)
);

-- ============================================================
-- TABLE: incident_reports (with May 16 alteration applied)
CREATE TABLE incident_reports (
    incident_id             BIGINT UNSIGNED     NOT NULL AUTO_INCREMENT,
    incident_date           DATE                NOT NULL,
    incident_time           TIME                NOT NULL,
    environmental_condition ENUM(
                                'clear',
                                'cloudy',
                                'rainy',
                                'foggy',
                                'night'
                            )                   NOT NULL,
    location_description    VARCHAR(500)        NOT NULL,
    vehicle_type            ENUM(
                                'car',
                                'truck',
                                'motorcycle',
                                'bus',
                                'mini_bus',
                                'tricycle',
                                'jeepney',
                                'ambulance',
                                'fire_truck',
                                'emergency_vehicle'
                            )                   NULL,
    vehicle_count           TINYINT UNSIGNED    NULL,
    people_hurt             TINYINT(1)          NOT NULL DEFAULT 0,
    injured_count           TINYINT UNSIGNED    NULL,
    description             TEXT                NOT NULL,
    reporting_party_name    VARCHAR(255)        NOT NULL,
    reporter_email          VARCHAR(255)        NULL,
    status                  ENUM(
                                'pending',
                                'reviewed'
                            )                   NOT NULL DEFAULT 'pending',
    reviewed_by             INT UNSIGNED        NULL,
    reviewed_at             TIMESTAMP           NULL,
    created_at              TIMESTAMP           NULL,
    updated_at              TIMESTAMP           NULL,

    PRIMARY KEY (incident_id),
    FOREIGN KEY (reviewed_by)
        REFERENCES admins (admin_id)
        ON UPDATE CASCADE
        ON DELETE SET NULL,

    INDEX idx_incident_reports_status      (status),
    INDEX idx_incident_reports_reviewed_by (reviewed_by)
);

-- ============================================================
-- VERIFY
SHOW TABLES;
-- ============================================================
