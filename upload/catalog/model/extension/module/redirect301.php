<?php
class ModelExtensionModuleRedirect301 extends Model {

    /**
     * Возвращает все активные редиректы в виде хэш-карты:
     * [ '/old-url' => '/new-url', ... ]
     *
     * Хэш-карта вместо массива — поиск O(1) вместо O(n)
     */
    public function getRedirects() {
        $query = $this->db->query("
            SELECT `from_url`, `to_url`
            FROM `" . DB_PREFIX . "redirect301`
            WHERE `status` = 1
        ");

        // Строим хэш-карту из результата
        $map = [];
        foreach ($query->rows as $row) {
            // Нормализуем from_url: убираем trailing slash для единообразия
            $key = rtrim($row['from_url'], '/');
            $map[$key] = $row['to_url'];
        }

        return $map;
    }
}
