<?php
class AdminSettings extends SepaExportController
{
    public function index()
    {
        $this->uses(['ClientGroups', 'Settings']);

        // Ophalen clientgroepen
        $client_groups = $this->ClientGroups->getAll(Configure::get('Blesta.company_id'));
        $group_options = [];

        foreach ($client_groups as $group) {
            $group_options[$group->id] = $group->name;
        }

        // Ophalen opgeslagen instellingen
        $selected_group = $this->Settings->getSetting(
            Configure::get('Blesta.company_id'),
            'sepa_export_client_group'
        )?->value;

        $creditor_id = $this->Settings->getSetting(
            Configure::get('Blesta.company_id'),
            'sepa_export_creditor_id'
        )?->value;

        // Opslaan bij POST
        if (!empty($this->post)) {
            $this->Settings->set(
                Configure::get('Blesta.company_id'),
                'sepa_export_client_group',
                $this->post['client_group']
            );
            $this->Settings->set(
                Configure::get('Blesta.company_id'),
                'sepa_export_creditor_id',
                $this->post['creditor_id']
            );

            $this->flashMessage('message', Language::_('AdminSettings.index.saved', true));
            $this->redirect($this->base_uri . 'plugin/sepa_export/admin_settings/');
        }

        $this->set('group_options', $group_options);
        $this->set('selected_group', $selected_group);
        $this->set('creditor_id', $creditor_id);

        return $this->render('admin/admin_settings', 'index');
    }
}
