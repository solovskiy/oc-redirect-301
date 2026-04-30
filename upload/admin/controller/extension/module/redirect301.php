<?php
class ControllerExtensionModuleRedirect301 extends Controller {

    private $error = [];

    public function index() {
        $this->load->language('extension/module/redirect301');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('extension/module/redirect301');
        $this->getList();
    }

    public function add() {
        $this->load->language('extension/module/redirect301');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('extension/module/redirect301');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $this->model_extension_module_redirect301->addRedirect($this->request->post);
            $this->cache->delete('redirect301.list');

            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link('extension/module/redirect301', 'user_token=' . $this->session->data['user_token'] . $this->buildUrl(), true));
        }

        $this->getForm();
    }

    public function edit() {
        $this->load->language('extension/module/redirect301');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('extension/module/redirect301');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $this->model_extension_module_redirect301->editRedirect($this->request->get['redirect_id'], $this->request->post);
            $this->cache->delete('redirect301.list');

            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link('extension/module/redirect301', 'user_token=' . $this->session->data['user_token'] . $this->buildUrl(), true));
        }

        $this->getForm();
    }

    public function delete() {
        $this->load->language('extension/module/redirect301');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('extension/module/redirect301');

        if (isset($this->request->post['selected']) && $this->validateDelete()) {
            foreach ((array)$this->request->post['selected'] as $redirect_id) {
                $this->model_extension_module_redirect301->deleteRedirect($redirect_id);
            }
            $this->cache->delete('redirect301.list');

            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link('extension/module/redirect301', 'user_token=' . $this->session->data['user_token'] . $this->buildUrl(), true));
        }

        $this->getList();
    }

    /**
     * Сброс счётчика times_used для одного редиректа.
     * GET-параметр redirect_id.
     */
    public function reset() {
        $this->load->language('extension/module/redirect301');
        $this->load->model('extension/module/redirect301');

        if (!$this->user->hasPermission('modify', 'extension/module/redirect301')) {
            $this->session->data['error_warning'] = $this->language->get('error_permission');
        } elseif (isset($this->request->get['redirect_id'])) {
            $this->model_extension_module_redirect301->resetCounter($this->request->get['redirect_id']);
            $this->session->data['success'] = $this->language->get('text_success');
        }

        $this->response->redirect($this->url->link('extension/module/redirect301', 'user_token=' . $this->session->data['user_token'] . $this->buildUrl(), true));
    }

    protected function getList() {
        $sort  = isset($this->request->get['sort'])  ? $this->request->get['sort']  : 'redirect_id';
        $order = isset($this->request->get['order']) ? $this->request->get['order'] : 'DESC';
        $page  = isset($this->request->get['page'])  ? max(1, (int)$this->request->get['page']) : 1;

        $filter_from_url = isset($this->request->get['filter_from_url']) ? $this->request->get['filter_from_url'] : '';
        $filter_to_url   = isset($this->request->get['filter_to_url'])   ? $this->request->get['filter_to_url']   : '';
        $filter_active   = isset($this->request->get['filter_active'])   ? $this->request->get['filter_active']   : '';

        $url = $this->buildUrl();

        $data['breadcrumbs'] = [
            ['text' => $this->language->get('text_home'),      'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)],
            ['text' => $this->language->get('text_extension'), 'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)],
            ['text' => $this->language->get('heading_title'),  'href' => $this->url->link('extension/module/redirect301', 'user_token=' . $this->session->data['user_token'] . $url, true)],
        ];

        $data['add']       = $this->url->link('extension/module/redirect301/add',    'user_token=' . $this->session->data['user_token'] . $url, true);
        $data['delete']    = $this->url->link('extension/module/redirect301/delete', 'user_token=' . $this->session->data['user_token'] . $url, true);
        $data['log404_url'] = $this->url->link('extension/module/redirect301/log404', 'user_token=' . $this->session->data['user_token'], true);

        $limit = (int)$this->config->get('config_limit_admin');
        if ($limit < 1) { $limit = 20; }

        $filter_data = [
            'filter_from_url' => $filter_from_url,
            'filter_to_url'   => $filter_to_url,
            'filter_active'   => $filter_active,
            'sort'            => $sort,
            'order'           => $order,
            'start'           => ($page - 1) * $limit,
            'limit'           => $limit
        ];

        $redirect_total = $this->model_extension_module_redirect301->getTotalRedirects($filter_data);
        $results        = $this->model_extension_module_redirect301->getRedirects($filter_data);

        $data['redirects'] = [];
        foreach ($results as $r) {
            $data['redirects'][] = [
                'redirect_id'   => $r['redirect_id'],
                'from_url'      => $r['from_url'],
                'to_url'        => $r['to_url'],
                'product_id'    => $r['product_id'],
                'response_code' => $r['response_code'],
                'date_start'    => (!empty($r['date_start']) && $r['date_start'] !== '0000-00-00') ? $r['date_start'] : '',
                'date_end'      => (!empty($r['date_end'])   && $r['date_end']   !== '0000-00-00') ? $r['date_end']   : '',
                'times_used'    => (int)$r['times_used'],
                'active'        => (int)$r['active'],
                'status_text'   => $r['active'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled'),
                'edit'          => $this->url->link('extension/module/redirect301/edit',  'user_token=' . $this->session->data['user_token'] . '&redirect_id=' . $r['redirect_id'] . $url, true),
                'reset'         => $this->url->link('extension/module/redirect301/reset', 'user_token=' . $this->session->data['user_token'] . '&redirect_id=' . $r['redirect_id'] . $url, true),
            ];
        }

        $data['user_token'] = $this->session->data['user_token'];

        $data['error_warning'] = '';
        if (isset($this->error['warning']))               { $data['error_warning'] = $this->error['warning']; }
        elseif (isset($this->session->data['error_warning'])) {
            $data['error_warning'] = $this->session->data['error_warning'];
            unset($this->session->data['error_warning']);
        }

        $data['success'] = '';
        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        }

        $data['selected'] = isset($this->request->post['selected']) ? (array)$this->request->post['selected'] : [];

        // sort links
        $sort_url_base = '';
        if ($filter_from_url !== '') { $sort_url_base .= '&filter_from_url=' . urlencode($filter_from_url); }
        if ($filter_to_url   !== '') { $sort_url_base .= '&filter_to_url='   . urlencode($filter_to_url);   }
        if ($filter_active   !== '') { $sort_url_base .= '&filter_active='   . urlencode($filter_active);   }
        $sort_url_base .= ($order === 'ASC') ? '&order=DESC' : '&order=ASC';
        if (isset($this->request->get['page'])) { $sort_url_base .= '&page=' . (int)$this->request->get['page']; }

        $data['sort_from_url']      = $this->url->link('extension/module/redirect301', 'user_token=' . $this->session->data['user_token'] . '&sort=from_url'      . $sort_url_base, true);
        $data['sort_to_url']        = $this->url->link('extension/module/redirect301', 'user_token=' . $this->session->data['user_token'] . '&sort=to_url'        . $sort_url_base, true);
        $data['sort_active']        = $this->url->link('extension/module/redirect301', 'user_token=' . $this->session->data['user_token'] . '&sort=active'        . $sort_url_base, true);
        $data['sort_response_code'] = $this->url->link('extension/module/redirect301', 'user_token=' . $this->session->data['user_token'] . '&sort=response_code' . $sort_url_base, true);
        $data['sort_date_start']    = $this->url->link('extension/module/redirect301', 'user_token=' . $this->session->data['user_token'] . '&sort=date_start'    . $sort_url_base, true);
        $data['sort_date_end']      = $this->url->link('extension/module/redirect301', 'user_token=' . $this->session->data['user_token'] . '&sort=date_end'      . $sort_url_base, true);
        $data['sort_times_used']    = $this->url->link('extension/module/redirect301', 'user_token=' . $this->session->data['user_token'] . '&sort=times_used'    . $sort_url_base, true);

        // pagination
        $pagination = new Pagination();
        $pagination->total = $redirect_total;
        $pagination->page  = $page;
        $pagination->limit = $limit;
        $pagination->url   = $this->url->link('extension/module/redirect301', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);
        $data['pagination'] = $pagination->render();

        $data['results'] = sprintf(
            $this->language->get('text_pagination'),
            ($redirect_total) ? (($page - 1) * $limit) + 1 : 0,
            ((($page - 1) * $limit) > ($redirect_total - $limit)) ? $redirect_total : ((($page - 1) * $limit) + $limit),
            $redirect_total,
            ceil($redirect_total / $limit)
        );

        $data['filter_from_url'] = $filter_from_url;
        $data['filter_to_url']   = $filter_to_url;
        $data['filter_active']   = $filter_active;

        $data['sort']  = $sort;
        $data['order'] = $order;

        $data['header']      = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer']      = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/redirect301_list', $data));
    }

    protected function getForm() {
        $data['text_form'] = !isset($this->request->get['redirect_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');

        foreach (['warning', 'from_url', 'to_url', 'response_code', 'date_start', 'date_end', 'product_id'] as $f) {
            $data['error_' . $f] = isset($this->error[$f]) ? $this->error[$f] : '';
        }

        $url = $this->buildUrl();

        $data['breadcrumbs'] = [
            ['text' => $this->language->get('text_home'),      'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)],
            ['text' => $this->language->get('text_extension'), 'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)],
            ['text' => $this->language->get('heading_title'),  'href' => $this->url->link('extension/module/redirect301', 'user_token=' . $this->session->data['user_token'] . $url, true)],
        ];

        if (!isset($this->request->get['redirect_id'])) {
            $data['action'] = $this->url->link('extension/module/redirect301/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
        } else {
            $data['action'] = $this->url->link('extension/module/redirect301/edit', 'user_token=' . $this->session->data['user_token'] . '&redirect_id=' . (int)$this->request->get['redirect_id'] . $url, true);
        }

        $data['cancel'] = $this->url->link('extension/module/redirect301', 'user_token=' . $this->session->data['user_token'] . $url, true);

        $redirect_info = [];
        if (isset($this->request->get['redirect_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $redirect_info = $this->model_extension_module_redirect301->getRedirect($this->request->get['redirect_id']);
        }

        $defaults = [
            'from_url'      => isset($this->request->get['prefill_from']) ? rawurldecode($this->request->get['prefill_from']) : '',
            'to_url'        => '',
            'response_code' => 301,
            'active'        => 1,
            'date_start'    => '',
            'date_end'      => '',
            'product_id'    => '',
            'times_used'    => 0,
        ];

        foreach ($defaults as $field => $default) {
            if (isset($this->request->post[$field])) {
                $val = $this->request->post[$field];
            } elseif (!empty($redirect_info) && array_key_exists($field, $redirect_info)) {
                $val = $redirect_info[$field];
                if (in_array($field, ['date_start', 'date_end']) && ($val === null || $val === '0000-00-00')) {
                    $val = '';
                }
            } else {
                $val = $default;
            }
            $data[$field] = $val;
        }

        // подгружаем имя товара для autocomplete (если product_id задан)
        $data['product'] = '';
        if (!empty($data['product_id'])) {
            $this->load->model('catalog/product');
            $product_info = $this->model_catalog_product->getProduct((int)$data['product_id']);
            if ($product_info) {
                $data['product'] = $product_info['name'];
            }
        }

        $data['response_codes'] = [
            ['value' => 301, 'text' => '301 ' . $this->language->get('text_301')],
            ['value' => 302, 'text' => '302 ' . $this->language->get('text_302')],
            ['value' => 303, 'text' => '303 ' . $this->language->get('text_303')],
            ['value' => 307, 'text' => '307 ' . $this->language->get('text_307')],
            ['value' => 308, 'text' => '308 ' . $this->language->get('text_308')],
        ];

        $data['user_token']  = $this->session->data['user_token'];
        $data['header']      = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer']      = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/redirect301_form', $data));
    }

    protected function validateForm() {
        if (!$this->user->hasPermission('modify', 'extension/module/redirect301')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        $from_url = isset($this->request->post['from_url']) ? trim($this->request->post['from_url']) : '';
        if ($from_url === '' || mb_strlen($from_url) > 1000) {
            $this->error['from_url'] = $this->language->get('error_from_url');
        }

        $to_url     = isset($this->request->post['to_url']) ? trim($this->request->post['to_url']) : '';
        $product_id = isset($this->request->post['product_id']) ? (int)$this->request->post['product_id'] : 0;

        // Должно быть задано либо to_url, либо product_id
        if ($to_url === '' && $product_id <= 0) {
            $this->error['to_url'] = $this->language->get('error_to_url');
        } elseif ($to_url !== '' && mb_strlen($to_url) > 1000) {
            $this->error['to_url'] = $this->language->get('error_to_url');
        }

        $allowed_codes = [301, 302, 303, 307, 308];
        $code = isset($this->request->post['response_code']) ? (int)$this->request->post['response_code'] : 301;
        if (!in_array($code, $allowed_codes, true)) {
            $this->error['response_code'] = $this->language->get('error_response_code');
        }

        foreach (['date_start', 'date_end'] as $df) {
            $v = isset($this->request->post[$df]) ? trim($this->request->post[$df]) : '';
            if ($v !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $v)) {
                $this->error[$df] = $this->language->get('error_date');
            }
        }

        // Проверка date_start <= date_end
        $ds = isset($this->request->post['date_start']) ? trim($this->request->post['date_start']) : '';
        $de = isset($this->request->post['date_end'])   ? trim($this->request->post['date_end'])   : '';
        if ($ds !== '' && $de !== '' && $ds > $de) {
            $this->error['date_end'] = $this->language->get('error_date_range');
        }

        // Уникальность from_url
        if (!isset($this->error['from_url'])) {
            $exclude = isset($this->request->get['redirect_id']) ? (int)$this->request->get['redirect_id'] : 0;
            $existing_id = $this->model_extension_module_redirect301->getRedirectByFromUrl($from_url, $exclude);
            if ($existing_id) {
                $this->error['from_url'] = sprintf($this->language->get('error_from_url_exists'), $existing_id);
            }
        }

        if ($this->error && !isset($this->error['warning'])) {
            $this->error['warning'] = $this->language->get('error_warning');
        }

        return !$this->error;
    }

    protected function validateDelete() {
        if (!$this->user->hasPermission('modify', 'extension/module/redirect301')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        return !$this->error;
    }

    /**
     * Builds query-string suffix preserving filters/sort/page state.
     */
    protected function buildUrl() {
        $url = '';
        $params = ['filter_from_url', 'filter_to_url', 'filter_active', 'sort', 'order', 'page'];
        foreach ($params as $p) {
            if (isset($this->request->get[$p]) && $this->request->get[$p] !== '') {
                $url .= '&' . $p . '=' . urlencode($this->request->get[$p]);
            }
        }
        return $url;
    }

    /* ===== 404 log ===== */

    public function log404() {
        $this->load->language('extension/module/redirect301');
        $this->document->setTitle($this->language->get('heading_title_404'));
        $this->load->model('extension/module/redirect301');

        $this->getLog404List();
    }

    public function deleteLog404() {
        $this->load->language('extension/module/redirect301');
        $this->load->model('extension/module/redirect301');

        if (!$this->user->hasPermission('modify', 'extension/module/redirect301')) {
            $this->session->data['error_warning'] = $this->language->get('error_permission');
        } elseif (isset($this->request->post['selected'])) {
            foreach ((array)$this->request->post['selected'] as $id) {
                $this->model_extension_module_redirect301->delete404($id);
            }
            $this->session->data['success'] = $this->language->get('text_success');
        }

        $this->response->redirect($this->url->link('extension/module/redirect301/log404', 'user_token=' . $this->session->data['user_token'], true));
    }

    public function clearLog404() {
        $this->load->language('extension/module/redirect301');
        $this->load->model('extension/module/redirect301');

        if (!$this->user->hasPermission('modify', 'extension/module/redirect301')) {
            $this->session->data['error_warning'] = $this->language->get('error_permission');
        } else {
            $this->model_extension_module_redirect301->deleteAll404s();
            $this->session->data['success'] = $this->language->get('text_success');
        }

        $this->response->redirect($this->url->link('extension/module/redirect301/log404', 'user_token=' . $this->session->data['user_token'], true));
    }

    /**
     * Быстрое создание редиректа из 404-записи: открывает форму с pre-filled from_url.
     */
    public function createFromLog() {
        $this->load->model('extension/module/redirect301');

        if (isset($this->request->get['id'])) {
            $entry = $this->model_extension_module_redirect301->get404($this->request->get['id']);
            if ($entry) {
                $this->response->redirect(
                    $this->url->link('extension/module/redirect301/add',
                        'user_token=' . $this->session->data['user_token'] . '&prefill_from=' . urlencode($entry['url']),
                        true
                    )
                );
                return;
            }
        }

        $this->response->redirect($this->url->link('extension/module/redirect301/log404', 'user_token=' . $this->session->data['user_token'], true));
    }

    protected function getLog404List() {
        $sort  = isset($this->request->get['sort'])  ? $this->request->get['sort']  : 'hits';
        $order = isset($this->request->get['order']) ? $this->request->get['order'] : 'DESC';
        $page  = isset($this->request->get['page'])  ? max(1, (int)$this->request->get['page']) : 1;
        $filter_url = isset($this->request->get['filter_url']) ? $this->request->get['filter_url'] : '';

        $limit = (int)$this->config->get('config_limit_admin');
        if ($limit < 1) { $limit = 20; }

        $filter_data = [
            'filter_url' => $filter_url,
            'sort'       => $sort,
            'order'      => $order,
            'start'      => ($page - 1) * $limit,
            'limit'      => $limit
        ];

        // Создаём таблицу если ещё не существует (на случай обновления без переустановки)
        $this->model_extension_module_redirect301->create404Table();

        $total   = $this->model_extension_module_redirect301->getTotal404s($filter_data);
        $results = $this->model_extension_module_redirect301->get404s($filter_data);

        $data['entries'] = [];
        foreach ($results as $r) {
            // Проверяем, есть ли уже редирект для этого URL.
            // Если есть — кнопка превращается в "Редактировать" и ведёт сразу на edit.
            $existing_redirect_id = $this->model_extension_module_redirect301->getRedirectByFromUrl($r['url']);

            if ($existing_redirect_id) {
                $action_link  = $this->url->link('extension/module/redirect301/edit', 'user_token=' . $this->session->data['user_token'] . '&redirect_id=' . $existing_redirect_id, true);
                $action_label = $this->language->get('button_edit_redirect');
                $action_class = 'btn-primary';
                $action_icon  = 'fa-pencil';
            } else {
                $action_link  = $this->url->link('extension/module/redirect301/createFromLog', 'user_token=' . $this->session->data['user_token'] . '&id=' . $r['id'], true);
                $action_label = $this->language->get('button_create_redirect');
                $action_class = 'btn-success';
                $action_icon  = 'fa-plus';
            }

            $data['entries'][] = [
                'id'                   => $r['id'],
                'url'                  => $r['url'],
                'referer'              => $r['referer'],
                'ip'                   => $r['ip'],
                'user_agent'           => $r['user_agent'],
                'hits'                 => (int)$r['hits'],
                'first_seen'           => $r['first_seen'],
                'last_seen'            => $r['last_seen'],
                'existing_redirect_id' => $existing_redirect_id,
                'action_link'          => $action_link,
                'action_label'         => $action_label,
                'action_class'         => $action_class,
                'action_icon'          => $action_icon,
            ];
        }

        $data['user_token'] = $this->session->data['user_token'];

        $data['error_warning'] = '';
        if (isset($this->session->data['error_warning'])) {
            $data['error_warning'] = $this->session->data['error_warning'];
            unset($this->session->data['error_warning']);
        }

        $data['success'] = '';
        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        }

        $data['selected'] = isset($this->request->post['selected']) ? (array)$this->request->post['selected'] : [];

        $sort_url = '&order=' . ($order === 'ASC' ? 'DESC' : 'ASC');
        if ($filter_url !== '') { $sort_url .= '&filter_url=' . urlencode($filter_url); }

        $data['sort_url']        = $this->url->link('extension/module/redirect301/log404', 'user_token=' . $this->session->data['user_token'] . '&sort=url'        . $sort_url, true);
        $data['sort_hits']       = $this->url->link('extension/module/redirect301/log404', 'user_token=' . $this->session->data['user_token'] . '&sort=hits'       . $sort_url, true);
        $data['sort_last_seen']  = $this->url->link('extension/module/redirect301/log404', 'user_token=' . $this->session->data['user_token'] . '&sort=last_seen'  . $sort_url, true);
        $data['sort_first_seen'] = $this->url->link('extension/module/redirect301/log404', 'user_token=' . $this->session->data['user_token'] . '&sort=first_seen' . $sort_url, true);

        $data['delete_url'] = $this->url->link('extension/module/redirect301/deleteLog404', 'user_token=' . $this->session->data['user_token'], true);
        $data['clear_url']  = $this->url->link('extension/module/redirect301/clearLog404',  'user_token=' . $this->session->data['user_token'], true);
        $data['back_url']   = $this->url->link('extension/module/redirect301', 'user_token=' . $this->session->data['user_token'], true);

        $data['filter_url'] = $filter_url;
        $data['sort']  = $sort;
        $data['order'] = $order;

        $pagination = new Pagination();
        $pagination->total = $total;
        $pagination->page  = $page;
        $pagination->limit = $limit;

        $purl = 'user_token=' . $this->session->data['user_token'] . '&sort=' . $sort . '&order=' . $order;
        if ($filter_url !== '') { $purl .= '&filter_url=' . urlencode($filter_url); }
        $pagination->url = $this->url->link('extension/module/redirect301/log404', $purl . '&page={page}', true);

        $data['pagination'] = $pagination->render();
        $data['results']    = sprintf(
            $this->language->get('text_pagination'),
            $total ? ($page - 1) * $limit + 1 : 0,
            (($page - 1) * $limit > $total - $limit) ? $total : ($page - 1) * $limit + $limit,
            $total,
            ceil($total / $limit)
        );

        $data['breadcrumbs'] = [
            ['text' => $this->language->get('text_home'),      'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)],
            ['text' => $this->language->get('text_extension'), 'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)],
            ['text' => $this->language->get('heading_title'),  'href' => $this->url->link('extension/module/redirect301', 'user_token=' . $this->session->data['user_token'], true)],
            ['text' => $this->language->get('heading_title_404'), 'href' => $this->url->link('extension/module/redirect301/log404', 'user_token=' . $this->session->data['user_token'], true)],
        ];

        $data['header']      = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer']      = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/redirect301_404', $data));
    }

    /* ===== end 404 log ===== */

    /**
     * Вызывается автоматически при установке модуля.
     */
    public function install() {
        $this->load->model('extension/module/redirect301');
        $this->model_extension_module_redirect301->createTable();
    }

    /**
     * Вызывается при удалении модуля.
     * Таблицу не дропаем — только сбрасываем кэш.
     */
    public function uninstall() {
        $this->cache->delete('redirect301.list');
    }
}
