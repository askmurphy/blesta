<?php

class ClientMain extends ClientController
{
    public function preAction()
    {
        parent::preAction();
        $this->requireLogin();
        Language::loadLang('sepa_export_plugin', null, PLUGINDIR . 'sepa_export' . DS . 'language' . DS);

        // Omdat we een eigen structure.pdt gebruiken, zetten we hier geen setView voor bootstrap.
    }

    public function index()
    {
        $this->uses(['SepaExport.SepaMandates']);

        $client_id = $this->Session->read('blesta_client_id');
        $mandate = $this->SepaMandates->getByClient($client_id);

        $this->set('page_title', Language::_('SepaExportPlugin.client.page_title', true));
        $this->set('mandate', $mandate);
    }

    public function manage()
    {
        $this->uses(['SepaExport.SepaMandates']);

        $client_id = $this->Session->read('blesta_client_id');
        $mandate = $this->SepaMandates->getByClient($client_id);

        if (!empty($this->post)) {
            if ($this->post['action'] === 'cancel') {
                $this->SepaMandates->updateStatus($client_id, 'revoked');
                $this->flashMessage('message', Language::_('SepaExportPlugin.client.revoked', true));
            }
            $this->redirect($this->base_uri . 'plugin/sepa_export/client_main/index/');
        }

        $this->set('page_title', Language::_('SepaExportPlugin.client.page_title', true));
        $this->set('mandate', $mandate);
    }
}
