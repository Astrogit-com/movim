<?php

use App\Configuration;

class AdminMain extends \Movim\Widget\Base
{
    public function load()
    {
        $form = $_POST;

        $configuration = Configuration::get();

        if (isset($form) && !empty($form) && isset($form['adminform'])) {
            if (isset($form['password'])
            && isset($form['repassword'])
            && $form['password'] != '' && $form['repassword'] != ''
            && $form['password'] == $form['repassword']) {
                $configuration->password = password_hash($form['password'], PASSWORD_DEFAULT);
            }

            $form['restrictsuggestions'] = (isset($form['restrictsuggestions']));
            $form['chatonly'] = (isset($form['chatonly']));

            unset($form['password']);
            unset($form['repassword']);
            unset($form['submit']);
            unset($form['adminform']);

            foreach ($form as $key => $value) {
                $configuration->$key = $value;
            }

            $configuration->save();
        }
    }

    public function display()
    {
        $l = Movim\i18n\Locale::start();

        $this->view->assign('conf', Configuration::get());
        $this->view->assign('logs', [
                0 => $this->__('log.empty'),
                1 => $this->__('log.syslog'),
                2 => $this->__('log.syslog_files')
        ]);

        $this->view->assign('langs', $l->getList());
        $this->view->assign('countries', getCountries());
    }
}
