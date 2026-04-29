<?php
class ModelExtensionModuleRedirect301 extends Model {

    /**
     * Создаёт таблицу редиректов.
     * IF NOT EXISTS — безопасно, не упадёт если таблица уже есть.
     *
     * Структура:
     *   id        — первичный ключ
     *   from_url  — откуда редиректим (относительный путь, напр. /old-page)
     *   to_url    — куда редиректим (относительный или абсолютный)
     *   status    — 1 активен, 0 выключен
     *   sort_order — порядок (на будущее)
     */
    public function createTable() {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "redirect301` (
                `id`         int(11)      NOT NULL AUTO_INCREMENT,
                `from_url`   varchar(255) NOT NULL,
                `to_url`     varchar(255) NOT NULL,
                `status`     tinyint(1)   NOT NULL DEFAULT 1,
                `sort_order` int(11)      NOT NULL DEFAULT 0,
                PRIMARY KEY (`id`),
                INDEX `idx_from_url` (`from_url`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
        ");
    }

    /**
     * Удаляет таблицу при деинсталляции модуля
     */
    public function dropTable() {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "redirect301`");
    }

    /**
     * Добавляет новый редирект
     */
    public function addRedirect($data) {
        $this->db->query("
            INSERT INTO `" . DB_PREFIX . "redirect301` 
            SET `from_url` = '" . $this->db->escape($data['from_url']) . "', 
                `to_url` = '" . $this->db->escape($data['to_url']) . "', 
                `status` = '" . (int)$data['status'] . "', 
                `sort_order` = '" . (int)$data['sort_order'] . "'
        ");
        
        return $this->db->getLastId();
    }

    /**
     * Редактирует редирект
     */
    public function editRedirect($id, $data) {
        $this->db->query("
            UPDATE `" . DB_PREFIX . "redirect301` 
            SET `from_url` = '" . $this->db->escape($data['from_url']) . "', 
                `to_url` = '" . $this->db->escape($data['to_url']) . "', 
                `status` = '" . (int)$data['status'] . "', 
                `sort_order` = '" . (int)$data['sort_order'] . "' 
            WHERE `id` = '" . (int)$id . "'
        ");
    }

    /**
     * Удаляет редирект
     */
    public function deleteRedirect($id) {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "redirect301` WHERE `id` = '" . (int)$id . "'");
    }

    /**
     * Получает редирект по ID
     */
    public function getRedirect($id) {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "redirect301` WHERE `id` = '" . (int)$id . "'");
        
        return $query->row;
    }

    /**
     * Получает список редиректов с пагинацией
     */
    public function getRedirects($data = []) {
        $sql = "SELECT * FROM `" . DB_PREFIX . "redirect301`";
        
        $sort_data = ['from_url', 'to_url', 'status', 'sort_order'];
        
        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        } else {
            $sql .= " ORDER BY sort_order";
        }
        
        if (isset($data['order']) && ($data['order'] == 'DESC')) {
            $sql .= " DESC";
        } else {
            $sql .= " ASC";
        }
        
        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }
            
            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }
            
            $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        }
        
        $query = $this->db->query($sql);
        
        return $query->rows;
    }

    /**
     * Получает общее количество редиректов
     */
    public function getTotalRedirects() {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "redirect301`");
        
        return $query->row['total'];
    }
}
