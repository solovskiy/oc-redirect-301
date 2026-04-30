<?php
class ControllerExtensionModuleRedirect301 extends Controller {

    // Ключ кэша — одна запись на весь список редиректов
    const CACHE_KEY = 'redirect301.list';

    // Время жизни кэша — 24 часа
    const CACHE_TTL = 86400;

    public function index() {
        // 1. Берём path-часть REQUEST_URI без query-string и без trailing slash
        if (empty($_SERVER['REQUEST_URI'])) {
            return;
        }

        $request_uri  = (string)$_SERVER['REQUEST_URI'];
        $query_string = '';

        $qpos = strpos($request_uri, '?');
        if ($qpos !== false) {
            $query_string = substr($request_uri, $qpos + 1);
            $request_uri  = substr($request_uri, 0, $qpos);
        }

        $uri = rtrim(rawurldecode($request_uri), '/');

        if ($uri === '') {
            return; // главная — не трогаем
        }

        // 2. Получаем карту редиректов (из кэша или БД)
        $redirects = $this->cache->get(self::CACHE_KEY);

        if (!is_array($redirects)) {
            $this->load->model('extension/module/redirect301');
            $redirects = $this->model_extension_module_redirect301->getRedirects();
            $this->cache->set(self::CACHE_KEY, $redirects, self::CACHE_TTL);
        }

        if (empty($redirects) || !isset($redirects[$uri])) {
            return;
        }

        $rule  = $redirects[$uri];
        $today = date('Y-m-d');

        // 3. Проверка периода действия
        if (!empty($rule['date_start']) && $today < $rule['date_start']) {
            return;
        }
        if (!empty($rule['date_end']) && $today > $rule['date_end']) {
            return;
        }

        // 4. Определяем целевой URL
        $target = $this->resolveTarget($rule);
        if ($target === '') {
            return;
        }

        // 5. Сохраняем query string исходного запроса (если есть и в target нет своей)
        if ($query_string !== '' && strpos($target, '?') === false) {
            $target .= '?' . $query_string;
        }

        // 6. Защита от петли
        $current_full = $request_uri . ($query_string !== '' ? '?' . $query_string : '');
        if ($target === $current_full || $target === $request_uri) {
            return;
        }

        // 7. HTTP-код
        $code = (int)$rule['response_code'];
        if (!in_array($code, [301, 302, 303, 307, 308], true)) {
            $code = 301;
        }

        // 8. Инкремент счётчика
        $this->load->model('extension/module/redirect301');
        $this->model_extension_module_redirect301->incrementCounter($rule['redirect_id']);

        // 9. Сам редирект
        if (!headers_sent()) {
            header('Location: ' . $target, true, $code);
        }
        exit;
    }

    /**
     * Вызывается из пропатченного error/not_found.php — логирует 404.
     * Пропускает статику, боты уже не в счёт (они просто ботируют).
     */
    public function log404() {
        if (empty($_SERVER['REQUEST_URI'])) {
            return;
        }

        $uri = rawurldecode(parse_url((string)$_SERVER['REQUEST_URI'], PHP_URL_PATH));

        // Пропускаем статические файлы
        if (preg_match('/\.(jpe?g|png|gif|svg|ico|webp|css|js|woff2?|ttf|eot|map|mp4|pdf|zip|txt|xml)$/i', $uri)) {
            return;
        }

        // Пропускаем admin
        if (strpos($uri, '/admin') === 0) {
            return;
        }

        $referer = isset($_SERVER['HTTP_REFERER']) ? (string)$_SERVER['HTTP_REFERER'] : '';

        try {
            $this->load->model('extension/module/redirect301');
            $this->model_extension_module_redirect301->log404($uri, $referer);
        } catch (\Exception $e) {
            // Таблица ещё не создана — молча пропускаем
        }
    }

    /**
     * Строит итоговый URL:
     * - если задан product_id, ссылка строится через $url->link('product/product', ...)
     * - иначе используется to_url как есть
     */
    private function resolveTarget(array $rule) {
        if (!empty($rule['product_id'])) {
            return $this->url->link('product/product', 'product_id=' . (int)$rule['product_id']);
        }

        $to = trim((string)$rule['to_url']);
        if ($to === '') {
            return '';
        }

        // Относительный путь — оставляем как есть; абсолютный URL — тоже как есть.
        return $to;
    }
}
