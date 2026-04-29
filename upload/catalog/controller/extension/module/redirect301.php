<?php
class ControllerExtensionModuleRedirect301 extends Controller {

    // Ключ кэша — одна запись на весь список редиректов
    const CACHE_KEY = 'redirect301.list';

    // Время жизни кэша — 24 часа
    const CACHE_TTL = 86400;

    public function index() {
        // Берём путь из URI, без query string и без trailing slash
        // Например: /old-page или /catalog/product
        $uri = isset($_SERVER['REQUEST_URI'])
            ? rtrim(parse_url(rawurldecode($_SERVER['REQUEST_URI']), PHP_URL_PATH), '/')
            : '';

        // Пустой URI — главная страница, редиректы для неё не делаем
        if (empty($uri)) {
            return;
        }
        // Логируем URI
        $this->log->write('Redirect301 catalog: checking URI ' . $uri);
        // Пробуем взять список редиректов из кэша
        $redirects = $this->cache->get(self::CACHE_KEY);

        // Кэш пустой — грузим из БД и кэшируем
        if ($redirects === false || $redirects === null) {
            $this->load->model('extension/module/redirect301');
            $redirects = $this->model_extension_module_redirect301->getRedirects();
            $this->cache->set(self::CACHE_KEY, $redirects, self::CACHE_TTL);
            $this->log->write('Redirect301 catalog: loaded redirects from DB, count: ' . count($redirects));
        }

        // Ищем совпадение по from_url
        if (!empty($redirects) && isset($redirects[$uri])) {
            // Отдаём 301 и прерываем выполнение
            $this->log->write('Redirect301 catalog: redirecting ' . $uri . ' to ' . $redirects[$uri]);
            header('Location: ' . $redirects[$uri], true, 301);
            exit;
        } else {
            $this->log->write('Redirect301 catalog: no redirect found for ' . $uri);
        }
    }
}
