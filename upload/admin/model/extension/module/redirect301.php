<?php
class ModelExtensionModuleRedirect301 extends Model {

    /**
     * Создаёт таблицу oc_redirect, если её нет.
     * Если таблица уже есть — проверяет наличие всех нужных колонок и добавляет недостающие.
     *
     * Структура (совместимо с уже существующей oc_redirect):
     *   redirect_id    PK
     *   active         tinyint  1/0
     *   from_url       text
     *   to_url         text
     *   response_code  int 301/302/303/307/308
     *   date_start     date     null
     *   date_end       date     null
     *   times_used     int      счётчик срабатываний
     *   product_id     int      опционально, для редиректа на товар
     */
    public function createTable() {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "redirect` (
                `redirect_id`   INT(11)    NOT NULL AUTO_INCREMENT,
                `active`        TINYINT(1) DEFAULT 0,
                `from_url`      TEXT,
                `to_url`        TEXT,
                `response_code` INT(3)     DEFAULT 301,
                `date_start`    DATE       DEFAULT NULL,
                `date_end`      DATE       DEFAULT NULL,
                `times_used`    INT(5)     DEFAULT 0,
                `product_id`    INT(11)    DEFAULT NULL,
                PRIMARY KEY (`redirect_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
        ");

        // Добавляем недостающие колонки (на случай, если таблица была создана ранее в усечённом виде)
        $required = [
            'active'        => "TINYINT(1) DEFAULT 0",
            'from_url'      => "TEXT",
            'to_url'        => "TEXT",
            'response_code' => "INT(3) DEFAULT 301",
            'date_start'    => "DATE DEFAULT NULL",
            'date_end'      => "DATE DEFAULT NULL",
            'times_used'    => "INT(5) DEFAULT 0",
            'product_id'    => "INT(11) DEFAULT NULL",
        ];

        $cols = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "redirect`");
        $existing = [];
        foreach ($cols->rows as $row) {
            $existing[$row['Field']] = true;
        }

        foreach ($required as $col => $def) {
            if (!isset($existing[$col])) {
                $this->db->query("ALTER TABLE `" . DB_PREFIX . "redirect` ADD COLUMN `" . $col . "` " . $def);
            }
        }

        $this->create404Table();
    }

    /**
     * Создаёт таблицу лога 404.
     */
    public function create404Table() {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "redirect_404` (
                `id`         INT(11)       NOT NULL AUTO_INCREMENT,
                `url`        VARCHAR(1000) NOT NULL,
                `referer`    VARCHAR(1000) DEFAULT NULL,
                `ip`         VARCHAR(45)   DEFAULT NULL,
                `user_agent` VARCHAR(512)  DEFAULT NULL,
                `hits`       INT(11)       NOT NULL DEFAULT 1,
                `first_seen` DATETIME      NOT NULL,
                `last_seen`  DATETIME      NOT NULL,
                PRIMARY KEY (`id`),
                INDEX `idx_url` (`url`(255))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
        ");

        // Добавляем колонки если таблица уже существовала без них
        $cols = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "redirect_404`");
        $existing = [];
        foreach ($cols->rows as $row) { $existing[$row['Field']] = true; }

        if (!isset($existing['ip'])) {
            $this->db->query("ALTER TABLE `" . DB_PREFIX . "redirect_404` ADD COLUMN `ip` VARCHAR(45) DEFAULT NULL AFTER `referer`");
        }
        if (!isset($existing['user_agent'])) {
            $this->db->query("ALTER TABLE `" . DB_PREFIX . "redirect_404` ADD COLUMN `user_agent` VARCHAR(512) DEFAULT NULL AFTER `ip`");
        }
    }

    /**
     * НЕ дропает таблицу: oc_redirect может использоваться другими частями магазина / содержать данные.
     * Только сбрасывает кэш.
     */
    public function dropTable() {
        // intentionally no DROP TABLE
    }

    /* ---------- 404 log ---------- */

    public function get404s($data = []) {
        $sql = "SELECT * FROM `" . DB_PREFIX . "redirect_404` WHERE 1=1";

        if (!empty($data['filter_url'])) {
            $sql .= " AND `url` LIKE '%" . $this->db->escape($data['filter_url']) . "%'";
        }

        $sort_data = ['url', 'hits', 'first_seen', 'last_seen'];
        $sort  = (isset($data['sort']) && in_array($data['sort'], $sort_data)) ? $data['sort'] : 'hits';
        $order = (isset($data['order']) && $data['order'] === 'ASC') ? 'ASC' : 'DESC';

        $sql .= " ORDER BY `" . $sort . "` " . $order;

        if (isset($data['start']) || isset($data['limit'])) {
            $start = isset($data['start']) ? max(0, (int)$data['start']) : 0;
            $limit = isset($data['limit']) ? max(1, (int)$data['limit']) : 20;
            $sql .= " LIMIT " . $start . "," . $limit;
        }

        return $this->db->query($sql)->rows;
    }

    public function getTotal404s($data = []) {
        $sql = "SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "redirect_404` WHERE 1=1";

        if (!empty($data['filter_url'])) {
            $sql .= " AND `url` LIKE '%" . $this->db->escape($data['filter_url']) . "%'";
        }

        return (int)$this->db->query($sql)->row['total'];
    }

    public function delete404($id) {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "redirect_404` WHERE `id` = '" . (int)$id . "'");
    }

    public function deleteAll404s() {
        $this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "redirect_404`");
    }

    public function get404($id) {
        return $this->db->query("SELECT * FROM `" . DB_PREFIX . "redirect_404` WHERE `id` = '" . (int)$id . "'")->row;
    }

    public function addRedirect($data) {
        $this->db->query("
            INSERT INTO `" . DB_PREFIX . "redirect`
            SET `active`        = '" . (int)!empty($data['active']) . "',
                `from_url`      = '" . $this->db->escape($this->normalizeFromUrl($data['from_url'])) . "',
                `to_url`        = '" . $this->db->escape(trim((string)$data['to_url'])) . "',
                `response_code` = '" . (int)$data['response_code'] . "',
                `date_start`    = " . $this->dateOrNull($data['date_start']) . ",
                `date_end`      = " . $this->dateOrNull($data['date_end']) . ",
                `product_id`    = " . $this->intOrNull($data['product_id']) . ",
                `times_used`    = 0
        ");

        return $this->db->getLastId();
    }

    public function editRedirect($redirect_id, $data) {
        $this->db->query("
            UPDATE `" . DB_PREFIX . "redirect`
            SET `active`        = '" . (int)!empty($data['active']) . "',
                `from_url`      = '" . $this->db->escape($this->normalizeFromUrl($data['from_url'])) . "',
                `to_url`        = '" . $this->db->escape(trim((string)$data['to_url'])) . "',
                `response_code` = '" . (int)$data['response_code'] . "',
                `date_start`    = " . $this->dateOrNull($data['date_start']) . ",
                `date_end`      = " . $this->dateOrNull($data['date_end']) . ",
                `product_id`    = " . $this->intOrNull($data['product_id']) . "
            WHERE `redirect_id` = '" . (int)$redirect_id . "'
        ");
    }

    public function deleteRedirect($redirect_id) {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "redirect` WHERE `redirect_id` = '" . (int)$redirect_id . "'");
    }

    public function resetCounter($redirect_id) {
        $this->db->query("UPDATE `" . DB_PREFIX . "redirect` SET `times_used` = 0 WHERE `redirect_id` = '" . (int)$redirect_id . "'");
    }

    public function getRedirect($redirect_id) {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "redirect` WHERE `redirect_id` = '" . (int)$redirect_id . "'");
        return $query->row;
    }

    public function getRedirects($data = []) {
        $sql = "SELECT * FROM `" . DB_PREFIX . "redirect` WHERE 1=1";

        if (!empty($data['filter_from_url'])) {
            $sql .= " AND `from_url` LIKE '%" . $this->db->escape($data['filter_from_url']) . "%'";
        }

        if (!empty($data['filter_to_url'])) {
            $sql .= " AND `to_url` LIKE '%" . $this->db->escape($data['filter_to_url']) . "%'";
        }

        if (isset($data['filter_active']) && $data['filter_active'] !== '') {
            $sql .= " AND `active` = '" . (int)$data['filter_active'] . "'";
        }

        $sort_data = [
            'redirect_id', 'active', 'from_url', 'to_url',
            'response_code', 'date_start', 'date_end', 'times_used'
        ];

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY `" . $data['sort'] . "`";
        } else {
            $sql .= " ORDER BY `redirect_id`";
        }

        $sql .= (isset($data['order']) && $data['order'] === 'DESC') ? " DESC" : " ASC";

        if (isset($data['start']) || isset($data['limit'])) {
            $start = isset($data['start']) ? max(0, (int)$data['start']) : 0;
            $limit = isset($data['limit']) ? max(1, (int)$data['limit']) : 20;
            $sql .= " LIMIT " . $start . "," . $limit;
        }

        $query = $this->db->query($sql);
        return $query->rows;
    }

    public function getTotalRedirects($data = []) {
        $sql = "SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "redirect` WHERE 1=1";

        if (!empty($data['filter_from_url'])) {
            $sql .= " AND `from_url` LIKE '%" . $this->db->escape($data['filter_from_url']) . "%'";
        }

        if (!empty($data['filter_to_url'])) {
            $sql .= " AND `to_url` LIKE '%" . $this->db->escape($data['filter_to_url']) . "%'";
        }

        if (isset($data['filter_active']) && $data['filter_active'] !== '') {
            $sql .= " AND `active` = '" . (int)$data['filter_active'] . "'";
        }

        $query = $this->db->query($sql);
        return (int)$query->row['total'];
    }

    /**
     * Проверяет, есть ли уже редирект с таким from_url (для валидации уникальности).
     * Возвращает redirect_id найденной записи или 0.
     */
    public function getRedirectByFromUrl($from_url, $exclude_id = 0) {
        $from_url = $this->normalizeFromUrl($from_url);
        $query = $this->db->query("
            SELECT `redirect_id` FROM `" . DB_PREFIX . "redirect`
            WHERE `from_url` = '" . $this->db->escape($from_url) . "'
              AND `redirect_id` <> '" . (int)$exclude_id . "'
            LIMIT 1
        ");
        return $query->num_rows ? (int)$query->row['redirect_id'] : 0;
    }

    /* ---------- helpers ---------- */

    private function normalizeFromUrl($url) {
        $url = trim((string)$url);
        if ($url === '' || $url === '/') {
            return $url;
        }
        // оставляем только path-часть, без query
        $parts = parse_url($url);
        $path  = isset($parts['path']) ? $parts['path'] : $url;
        $path  = '/' . ltrim($path, '/');
        return rtrim($path, '/');
    }

    private function dateOrNull($v) {
        $v = trim((string)$v);
        if ($v === '' || $v === '0000-00-00') {
            return 'NULL';
        }
        // строгая проверка формата YYYY-MM-DD
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $v)) {
            return 'NULL';
        }
        return "'" . $this->db->escape($v) . "'";
    }

    private function intOrNull($v) {
        if ($v === null || $v === '' || (int)$v <= 0) {
            return 'NULL';
        }
        return "'" . (int)$v . "'";
    }
}
