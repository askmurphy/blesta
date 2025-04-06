<?php
use Blesta\Core\Util\Events\Common\EventInterface;

/**
 * sepa_export plugin handler
 *
 * @link https://www.rhodyon.eu Arthur Kerkmeester
 */
class SepaExportPlugin extends Plugin
{
    public function __construct()
    {
        // Load components required by this plugin
        Loader::loadComponents($this, ['Input', 'Record']);

        Language::loadLang('sepa_export_plugin', null, dirname(__FILE__) . DS . 'language' . DS);
        $this->loadConfig(dirname(__FILE__) . DS . 'config.json');
    }

    /**
     * Performs any necessary bootstraping actions
     *
     * @param int $plugin_id The ID of the plugin being installed
     */
    public function install($plugin_id)
    {
        try {
            $this->Record
                ->setField(
                    'id',
                    [
                        'type' => 'INT',
                        'size' => "11",
                        'unsigned' => true,
                        'auto_increment' => true,
                    ]
                )
                ->setKey(['id'], 'primary')
                ->setField(
                    'name',
                    [
                        'type' => 'VARCHAR',
                        'size' => "255",
                    ]
                )
                ->setField(
                    'total_amount',
                    [
                        'type' => 'VARCHAR',
                        'size' => "20",
                        'default' => 0.00,
                    ]
                )
                ->setField(
                    'invoice_ids',
                    [
                        'type' => 'TEXT',
                    ]
                )
                ->setField(
                    'created_at',
                    [
                        'type' => 'DATETIME',
                    ]
                )
                ->create('sepa_export_batches', true);
                
        } catch (Exception $e) {
            // Error adding... no permission?
            $this->Input->setErrors(['db' => ['create' => $e->getMessage()]]);
            return;
        }
    }

    public function uninstall($plugin_id, $last_instance)
    {
        if ($last_instance) {
            try {
                // Remove database tables
                $this->Record->drop('sepa_export_batches');
            } catch (Exception $e) {
                // Error dropping... no permission?
                $this->Input->setErrors(['db' => ['create' => $e->getMessage()]]);
                return;
            }
        }
    }

     public function getActions()
     {
         return [
             // Admin (staff) link onder Billing
             [
                 'action' => 'nav_secondary_staff',
                 'uri' => 'plugin/sepa_export/admin_main/index/',
                 'name' => 'SEPA Export',
                 'options' => ['parent' => 'billing/'],
                 'enabled' => 1
             ],
             // Clientportaal link onder Account
             [
                 'action' => 'nav_secondary_client',
                 'uri' => 'plugin/sepa_export/client_main/index/',
                 'name' => Language::_('SepaExportPlugin.client.page_title', true),
                 'options' => ['parent' => 'account/'],
                 'enabled' => 1
             ]
         ];
     }

      
     
   public function getConfiguration()
   {
       return [
           'tab' => 'settings', // dit is de controller-actie
           'method' => 'index',
           'label' => Language::_('SepaExportPlugin.name', true)
       ];
   }

}
