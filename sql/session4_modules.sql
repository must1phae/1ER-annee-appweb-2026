-- =====================================================
--  Session 4 — Table modules
--  Base de données : webapp1ere
-- =====================================================

USE webapp1ere;

CREATE TABLE IF NOT EXISTS modules (
    id                 INT          UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id            INT          UNSIGNED NOT NULL,
    module_name        VARCHAR(150) NOT NULL,
    teacher            VARCHAR(150) DEFAULT NULL,
    difficulty         ENUM('Easy','Medium','Hard')   NOT NULL DEFAULT 'Medium',
    career_importance  ENUM('Low','Medium','High')    NOT NULL DEFAULT 'Medium',
    progress           TINYINT      UNSIGNED NOT NULL DEFAULT 0 CHECK (progress <= 100),
    understanding_level ENUM('Low','Medium','High')   NOT NULL DEFAULT 'Medium',
    exam_date          DATE         NOT NULL,
    created_at         TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id  (user_id),
    INDEX idx_exam_date (exam_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
