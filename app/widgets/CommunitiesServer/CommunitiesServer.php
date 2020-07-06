<?php

use Moxl\Xec\Action\Disco\Items;
use Respect\Validation\Validator;
use Moxl\Xec\Action\Pubsub\Create;
use Moxl\Xec\Action\Pubsub\TestCreate;

use Cocur\Slugify\Slugify;

class CommunitiesServer extends \Movim\Widget\Base
{
    public function load()
    {
        $this->registerEvent('disco_items_handle', 'onDisco');
        $this->registerEvent('disco_items_error', 'onDiscoError');
        $this->registerEvent('pubsub_create_handle', 'onCreate');
        $this->registerEvent('pubsub_testcreate_handle', 'onTestCreate');
        $this->registerEvent('pubsub_testcreate_error', 'onTestCreateError');

        $this->addjs('communitiesserver.js');
    }

    public function onCreate($packet)
    {
        Notification::toast($this->__('communitiesserver.created'));

        list($origin, $node) = array_values($packet->content);
        $this->ajaxDisco($origin);
        $this->rpc('MovimUtils.reload', $this->route('community', [$origin, $node]));
    }

    public function onDisco($packet)
    {
        $origin = $packet->content;

        $this->rpc('MovimTpl.fill', '#communities_server', $this->prepareCommunitiesServer($origin));
    }

    public function onDiscoError($packet)
    {
        $origin = $packet->content;

        \App\Info::where('server', $origin)->delete();

        $this->rpc('MovimTpl.fill', '#communities_server', $this->prepareCommunitiesServer($origin));

        Notification::toast($this->__('communitiesserver.disco_error'));
    }

    public function onTestCreate($packet)
    {
        $origin = $packet->content;

        $view = $this->tpl();
        $view->assign('server', $origin);

        Dialog::fill($view->draw('_communitiesserver_add'));
    }

    public function onTestCreateError($packet)
    {
        Notification::toast($this->__('communitiesserver.no_creation'));
    }

    public function ajaxDisco($origin)
    {
        if (!$this->validateServer($origin)) {
            Notification::toast($this->__('communitiesserver.disco_error'));
            return;
        }

        $r = new Items;
        $r->setTo($origin)->request();
    }

    /*
     * Seriously ? We need to put this hack because of buggy XEP-0060...
     */
    public function ajaxTestAdd($origin)
    {
        if (!$this->validateServer($origin)) {
            return;
        }

        $t = new TestCreate;
        $t->setTo($origin)
          ->request();
    }

    public function ajaxAddConfirm($origin, $form)
    {
        if (!$this->validateServer($origin)) {
            return;
        }

        $validate_name = Validator::stringType()->length(4, 80);
        if (!$validate_name->validate($form->name->value)) {
            Notification::toast($this->__('communitiesserver.name_error'));
            return;
        }

        $slugify = new Slugify;
        $uri = $slugify->slugify($form->name->value);

        if ($uri == '') {
            Notification::toast($this->__('communitiesserver.name_error'));
            return;
        }

        $c = new Create;
        $c->setTo($origin)
          ->setNode($uri)
          ->setName($form->name->value)
          ->request();
    }

    public function prepareCommunitiesServer($origin)
    {
        $item = \App\Info::where('server', $origin)->where('node', '')->first();

        $view = $this->tpl();
        $view->assign('item', $item);
        $view->assign('nodes', \App\Info::where('server', $origin)
                                        ->where('node', '!=', '')
                                        ->orderBy('occupants', 'desc')
                                        ->get());
        $view->assign('server', $origin);

        if (isset($item->name)) {
            $this->rpc('Notification.setTitle', $this->__('page.communities') . ' • ' . $item->name);
        }

        return $view->draw('_communitiesserver');
    }

    /**
     * @brief Validate the server
     *
     * @param string $origin
     */
    private function validateServer($origin)
    {
        $validate_server = Validator::noWhitespace()->alnum('.-_')->length(6, 40);
        return ($validate_server->validate($origin));
    }

    public function display()
    {
        $this->view->assign('server', $this->get('s'));
    }
}
