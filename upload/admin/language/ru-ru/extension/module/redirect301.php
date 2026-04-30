<?php
// Heading
$_['heading_title']        = '301 Редиректы';

// Text
$_['text_success']         = 'Успешно: данные обновлены!';
$_['text_list']            = 'Список редиректов';
$_['text_filter']          = 'Фильтр';
$_['text_add']             = 'Добавить редирект';
$_['text_edit']            = 'Редактировать редирект';
$_['text_default']         = 'По умолчанию';
$_['text_enabled']         = 'Включено';
$_['text_disabled']        = 'Отключено';
$_['text_all']             = 'Все';
$_['text_no_results']      = 'Нет результатов!';
$_['text_home']            = 'Главная';
$_['text_extension']       = 'Расширения';
$_['text_pagination']      = 'Показано с %d по %d из %d (всего страниц: %d)';
$_['text_confirm_delete']  = 'Вы уверены, что хотите удалить выбранные редиректы?';
$_['text_confirm_reset']   = 'Сбросить счётчик использований?';

$_['text_301']             = 'Moved Permanently';
$_['text_302']             = 'Found (Temporary)';
$_['text_303']             = 'See Other';
$_['text_307']             = 'Temporary Redirect';
$_['text_308']             = 'Permanent Redirect';

// Column
$_['column_from_url']      = 'Откуда URL';
$_['column_to_url']        = 'Куда URL / Товар';
$_['column_response_code'] = 'Код';
$_['column_status']        = 'Статус';
$_['column_date_start']    = 'С даты';
$_['column_date_end']      = 'По дату';
$_['column_times_used']    = 'Срабатываний';
$_['column_action']        = 'Действие';

// Entry
$_['entry_from_url']       = 'Откуда URL';
$_['entry_to_url']         = 'Куда URL';
$_['entry_product']        = 'Товар (опционально)';
$_['entry_response_code']  = 'HTTP код';
$_['entry_status']         = 'Статус';
$_['entry_date_start']     = 'Активен с';
$_['entry_date_end']       = 'Активен по';
$_['entry_times_used']     = 'Срабатываний';

// Help
$_['help_from_url']        = 'Относительный путь, например: /old-page или /catalog/product. Без домена и без query string.';
$_['help_to_url']          = 'Куда направлять. Можно относительный путь (/new-page), либо абсолютный URL (https://...). Если задан товар ниже — это поле можно оставить пустым.';
$_['help_product']         = 'Если выбрать товар, ссылка будет автоматически построена через product_id. Имеет приоритет над "Куда URL".';

// Error
$_['error_warning']        = 'Внимание: внимательно проверьте форму на ошибки!';
$_['error_permission']     = 'Внимание: у вас нет прав на изменение редиректов!';
$_['error_from_url']       = 'Поле "Откуда URL" обязательно (1-1000 символов)!';
$_['error_from_url_exists']= 'Редирект с таким "Откуда" уже существует (id %d).';
$_['error_to_url']         = 'Укажите "Куда URL" или выберите товар!';
$_['error_response_code']  = 'Недопустимый HTTP код. Допустимы: 301, 302, 303, 307, 308.';
$_['error_date']           = 'Неверный формат даты (нужно YYYY-MM-DD).';
$_['error_date_range']     = 'Дата окончания должна быть позже даты начала.';

// 404 log
$_['heading_title_404']       = '404 Лог';
$_['column_404_url']          = 'URL (404)';
$_['column_404_referer']      = 'Источник';
$_['column_404_ip']           = 'IP';
$_['column_404_ua']           = 'User Agent';
$_['column_404_hits']         = 'Попыток';
$_['column_404_first_seen']   = 'Первый раз';
$_['column_404_last_seen']    = 'Последний раз';
$_['text_confirm_clear']      = 'Очистить весь лог 404? Это действие необратимо.';
$_['text_redirect_exists']    = 'Редирект уже создан';
$_['button_log404']           = '404 Лог';
$_['button_create_redirect']  = 'Создать редирект';
$_['button_edit_redirect']    = 'Редактировать редирект';
$_['button_clear']            = 'Очистить лог';
$_['button_delete_selected']  = 'Удалить выбранные';
$_['button_back']             = 'Назад';

// Button
$_['button_add']           = 'Добавить';
$_['button_delete']        = 'Удалить';
$_['button_save']          = 'Сохранить';
$_['button_cancel']        = 'Отмена';
$_['button_edit']          = 'Редактировать';
$_['button_filter']        = 'Применить фильтр';
$_['button_reset']         = 'Сбросить счётчик';
