<?php
class ControllerExtensionModuleRedirect301 extends Controller {

    public function index() {
        $this->load->language('extension/module/redirect301');
        
        $this->document->setTitle($this->language->get('heading_title'));
        
        $this->load->model('extension/module/redirect301');
        
        // Логируем вызов index
        $this->log->write('Redirect301: index() called');
        
        $this->getList();
    }
    
    protected function getList() {
        if (isset($this->request->get['sort'])) {
            $sort = $this->request->get['sort'];
        } else {
            $sort = 'sort_order';
        }
        
        if (isset($this->request->get['order'])) {
            $order = $this->request->get['order'];
        } else {
            $order = 'ASC';
        }
        
        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
        } else {
            $page = 1;
        }
        
        $url = '';
        
        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }
        
        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }
        
        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }
        
        $data['breadcrumbs'] = [];
        
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        ];
        
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
        ];
        
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/redirect301', 'user_token=' . $this->session->data['user_token'] . $url, true)
        ];
        
        $data['add'] = $this->url->link('extension/module/redirect301/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
        $data['delete'] = $this->url->link('extension/module/redirect301/delete', 'user_token=' . $this->session->data['user_token'] . $url, true);
        
        $data['redirects'] = [];
        
        $filter_data = [
            'sort'  => $sort,
            'order' => $order,
            'start' => ($page - 1) * $this->config->get('config_limit_admin'),
            'limit' => $this->config->get('config_limit_admin')
        ];
        
        $redirect_total = $this->model_extension_module_redirect301->getTotalRedirects();
        
        $results = $this->model_extension_module_redirect301->getRedirects($filter_data);
        
        foreach ($results as $result) {
            $data['redirects'][] = [
                'id'         => $result['id'],
                'from_url'   => $result['from_url'],
                'to_url'     => $result['to_url'],
                'status'     => $result['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled'),
                'sort_order' => $result['sort_order'],
                'edit'       => $this->url->link('extension/module/redirect301/edit', 'user_token=' . $this->session->data['user_token'] . '&id=' . $result['id'] . $url, true)
            ];
        }
        
        $data['user_token'] = $this->session->data['user_token'];
        
        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }
        
        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }
        
        if (isset($this->request->post['selected'])) {
            $data['selected'] = (array)$this->request->post['selected'];
        } else {
            $data['selected'] = [];
        }
        
        $url = '';
        
        if ($order == 'ASC') {
            $url .= '&order=DESC';
        } else {
            $url .= '&order=ASC';
        }
        
        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }
        
        $data['sort_from_url'] = $this->url->link('extension/module/redirect301', 'user_token=' . $this->session->data['user_token'] . '&sort=from_url' . $url, true);
        $data['sort_to_url'] = $this->url->link('extension/module/redirect301', 'user_token=' . $this->session->data['user_token'] . '&sort=to_url' . $url, true);
        $data['sort_status'] = $this->url->link('extension/module/redirect301', 'user_token=' . $this->session->data['user_token'] . '&sort=status' . $url, true);
        $data['sort_sort_order'] = $this->url->link('extension/module/redirect301', 'user_token=' . $this->session->data['user_token'] . '&sort=sort_order' . $url, true);
        
        $url = '';
        
        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }
        
        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }
        
        $pagination = new Pagination();
        $pagination->total = $redirect_total;
        $pagination->page = $page;
        $pagination->limit = $this->config->get('config_limit_admin');
        $pagination->url = $this->url->link('extension/module/redirect301', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);
        
        $data['pagination'] = $pagination->render();
        
        $data['results'] = sprintf($this->language->get('text_pagination'), ($redirect_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($redirect_total - $this->config->get('config_limit_admin'))) ? $redirect_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $redirect_total, ceil($redirect_total / $this->config->get('config_limit_admin')));
        
        $data['sort'] = $sort;
        $data['order'] = $order;
        
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('extension/module/redirect301_list', $data));
    }
    
    public function add() {
        $this->load->language('extension/module/redirect301');
        
        $this->document->setTitle($this->language->get('heading_title'));
        
        $this->load->model('extension/module/redirect301');
        
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $this->model_extension_module_redirect301->addRedirect($this->request->post);
            
            $this->session->data['success'] = $this->language->get('text_success');
            
            $url = '';
            
            if (isset($this->request->get['sort'])) {
                $url .= '&sort=' . $this->request->get['sort'];
            }
            
            if (isset($this->request->get['order'])) {
                $url .= '&order=' . $this->request->get['order'];
            }
            
            if (isset($this->request->get['page'])) {
                $url .= '&page=' . $this->request->get['page'];
            }
            
            $this->response->redirect($this->url->link('extension/module/redirect301', 'user_token=' . $this->session->data['user_token'] . $url, true));
        }
        
        $this->getForm();
    }
    
    public function edit() {
        $this->load->language('extension/module/redirect301');
        
        $this->document->setTitle($this->language->get('heading_title'));
        
        $this->load->model('extension/module/redirect301');
        
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $this->model_extension_module_redirect301->editRedirect($this->request->get['id'], $this->request->post);
            
            $this->session->data['success'] = $this->language->get('text_success');
            
            $url = '';
            
            if (isset($this->request->get['sort'])) {
                $url .= '&sort=' . $this->request->get['sort'];
            }
            
            if (isset($this->request->get['order'])) {
                $url .= '&order=' . $this->request->get['order'];
            }
            
            if (isset($this->request->get['page'])) {
                $url .= '&page=' . $this->request->get['page'];
            }
            
            $this->response->redirect($this->url->link('extension/module/redirect301', 'user_token=' . $this->session->data['user_token'] . $url, true));
        }
        
        $this->getForm();
    }
    
    public function delete() {
        $this->load->language('extension/module/redirect301');
        
        $this->document->setTitle($this->language->get('heading_title'));
        
        $this->load->model('extension/module/redirect301');
        
        if (isset($this->request->post['selected']) && $this->validateDelete()) {
            foreach ($this->request->post['selected'] as $id) {
                $this->model_extension_module_redirect301->deleteRedirect($id);
            }
            
            $this->session->data['success'] = $this->language->get('text_success');
            
            $url = '';
            
            if (isset($this->request->get['sort'])) {
                $url .= '&sort=' . $this->request->get['sort'];
            }
            
            if (isset($this->request->get['order'])) {
                $url .= '&order=' . $this->request->get['order'];
            }
            
            if (isset($this->request->get['page'])) {
                $url .= '&page=' . $this->request->get['page'];
            }
            
            $this->response->redirect($this->url->link('extension/module/redirect301', 'user_token=' . $this->session->data['user_token'] . $url, true));
        }
        
        $this->getList();
    }
    
    protected function getForm() {
        $data['text_form'] = !isset($this->request->get['id']) ? $this->language->get('text_add') : $this->language->get('text_edit');
        
        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }
        
        if (isset($this->error['from_url'])) {
            $data['error_from_url'] = $this->error['from_url'];
        } else {
            $data['error_from_url'] = '';
        }
        
        if (isset($this->error['to_url'])) {
            $data['error_to_url'] = $this->error['to_url'];
        } else {
            $data['error_to_url'] = '';
        }
        
        $url = '';
        
        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }
        
        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }
        
        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }
        
        $data['breadcrumbs'] = [];
        
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        ];
        
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
        ];
        
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/redirect301', 'user_token=' . $this->session->data['user_token'] . $url, true)
        ];
        
        if (!isset($this->request->get['id'])) {
            $data['action'] = $this->url->link('extension/module/redirect301/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
        } else {
            $data['action'] = $this->url->link('extension/module/redirect301/edit', 'user_token=' . $this->session->data['user_token'] . '&id=' . $this->request->get['id'] . $url, true);
        }
        
        $data['cancel'] = $this->url->link('extension/module/redirect301', 'user_token=' . $this->session->data['user_token'] . $url, true);
        
        if (isset($this->request->get['id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $redirect_info = $this->model_extension_module_redirect301->getRedirect($this->request->get['id']);
        }
        
        if (isset($this->request->post['from_url'])) {
            $data['from_url'] = $this->request->post['from_url'];
        } elseif (!empty($redirect_info)) {
            $data['from_url'] = $redirect_info['from_url'];
        } else {
            $data['from_url'] = '';
        }
        
        if (isset($this->request->post['to_url'])) {
            $data['to_url'] = $this->request->post['to_url'];
        } elseif (!empty($redirect_info)) {
            $data['to_url'] = $redirect_info['to_url'];
        } else {
            $data['to_url'] = '';
        }
        
        if (isset($this->request->post['status'])) {
            $data['status'] = $this->request->post['status'];
        } elseif (!empty($redirect_info)) {
            $data['status'] = $redirect_info['status'];
        } else {
            $data['status'] = true;
        }
        
        if (isset($this->request->post['sort_order'])) {
            $data['sort_order'] = $this->request->post['sort_order'];
        } elseif (!empty($redirect_info)) {
            $data['sort_order'] = $redirect_info['sort_order'];
        } else {
            $data['sort_order'] = '';
        }
        
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('extension/module/redirect301_form', $data));
    }
    
    protected function validateForm() {
        if (!$this->user->hasPermission('modify', 'extension/module/redirect301')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        
        if ((utf8_strlen($this->request->post['from_url']) < 1) || (utf8_strlen($this->request->post['from_url']) > 255)) {
            $this->error['from_url'] = $this->language->get('error_from_url');
        }
        
        if ((utf8_strlen($this->request->post['to_url']) < 1) || (utf8_strlen($this->request->post['to_url']) > 255)) {
            $this->error['to_url'] = $this->language->get('error_to_url');
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
     * Вызывается автоматически при установке модуля через Extensions > Modules
     * Создаёт таблицу redirect301 если её нет
     */
    public function install() {
        $this->load->model('extension/module/redirect301');
        
        // Логируем начало установки
        $this->log->write('Redirect301: Starting install, DB_PREFIX = ' . (defined('DB_PREFIX') ? DB_PREFIX : 'NOT_DEFINED'));
        
        $this->model_extension_module_redirect301->createTable();
        
        // Проверяем, создалась ли таблица
        $check = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "redirect301'");
        if ($check->num_rows > 0) {
            $this->log->write('Redirect301: Table created successfully');
        } else {
            $this->log->write('Redirect301: Table creation failed');
        }
    }

    /**
     * Вызывается при удалении модуля
     * Удаляет таблицу и сбрасывает кэш
     */
    public function uninstall() {
        $this->load->model('extension/module/redirect301');
        $this->model_extension_module_redirect301->dropTable();

        // Сбрасываем кэш редиректов
        $this->cache->delete('redirect301.list');
    }
}
