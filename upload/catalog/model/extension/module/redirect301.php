<?php
class ModelExtensionModuleRedirect301 extends Model {

    /**
     * Возвращает все активные редиректы в виде хэш-карты:
     * [
     *   '/old-url' => [
     *      'redirect_id'   => int,
     *      'to_url'        => string,
     *      'response_code' => int,
     *      'date_start'    => 'YYYY-MM-DD' | null,
     *      'date_end'      => 'YYYY-MM-DD' | null,
     *      'product_id'    => int | null,
     *   ],
     *   ...
     * ]
     *
     * Фильтрация по дате выполняется уже на этапе чтения из кэша (в catalog-controller),
     * чтобы не инвалидировать кэш каждые сутки.
     */
    public function getRedirects() {
        $query = $this->db->query("
            SELECT `redirect_id`, `from_url`, `to_url`, `response_code`,
                   `date_start`, `date_end`, `product_id`
            FROM `" . DB_PREFIX . "redirect`
            WHERE `active` = 1
        ");

        $map = [];
        foreach ($query->rows as $row) {
            $key = rtrim((string)$row['from_url'], '/');
            if ($key === '') { continue; }

            $map[$key] = [
                'redirect_id'   => (int)$row['redirect_id'],
                'to_url'        => (string)$row['to_url'],
                'response_code' => (int)$row['response_code'] ?: 301,
                'date_start'    => (!empty($row['date_start']) && $row['date_start'] !== '0000-00-00') ? $row['date_start'] : null,
                'date_end'      => (!empty($row['date_end'])   && $row['date_end']   !== '0000-00-00') ? $row['date_end']   : null,
                'product_id'    => !empty($row['product_id']) ? (int)$row['product_id'] : null,
            ];
        }

        return $map;
    }

    /**
     * Атомарный инкремент счётчика срабатываний.
     * Вызывается перед самим header()-редиректом.
     */
    public function incrementCounter($redirect_id) {
        $this->db->query("
            UPDATE `" . DB_PREFIX . "redirect`
            SET `times_used` = `times_used` + 1
            WHERE `redirect_id` = '" . (int)$redirect_id . "'
        ");
    }

    /**
     * Логирует 404: инкрементирует счётчик если запись есть, иначе вставляет новую.
     */
    public function log404($url, $referer = '') {
        $url        = substr((string)$url, 0, 1000);
        $referer    = substr((string)$referer, 0, 1000);
        $ip         = substr($this->getClientIp(), 0, 45);
        $user_agent = substr(isset($_SERVER['HTTP_USER_AGENT']) ? (string)$_SERVER['HTTP_USER_AGENT'] : '', 0, 512);

        $query = $this->db->query("
            SELECT `id` FROM `" . DB_PREFIX . "redirect_404`
            WHERE `url` = '" . $this->db->escape($url) . "'
            LIMIT 1
        ");

        if ($query->num_rows) {
            $this->db->query("
                UPDATE `" . DB_PREFIX . "redirect_404`
                SET `hits`       = `hits` + 1,
                    `last_seen`  = NOW(),
                    `ip`         = '" . $this->db->escape($ip) . "',
                    `user_agent` = '" . $this->db->escape($user_agent) . "'
                WHERE `id` = '" . (int)$query->row['id'] . "'
            ");
        } else {
            $this->db->query("
                INSERT INTO `" . DB_PREFIX . "redirect_404`
                SET `url`        = '" . $this->db->escape($url) . "',
                    `referer`    = '" . $this->db->escape($referer) . "',
                    `ip`         = '" . $this->db->escape($ip) . "',
                    `user_agent` = '" . $this->db->escape($user_agent) . "',
                    `hits`       = 1,
                    `first_seen` = NOW(),
                    `last_seen`  = NOW()
            ");
        }
    }

    private function getClientIp() {
        foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_REAL_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $key) {
            if (!empty($_SERVER[$key])) {
                // X-Forwarded-For может содержать список — берём первый
                $ip = trim(explode(',', $_SERVER[$key])[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        return '';
    }
}
