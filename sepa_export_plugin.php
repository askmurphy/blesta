<?php
use Blesta\Core\Util\Events\Common\EventInterface;

/**
 * SepaExport Plugin Handler
 *
 * @link https://www.rhodyon.eu Arthur Kerkmeester
 */
class SepaExportPlugin extends Plugin
{
    public function __construct()
    {
        // Laad de vereiste componenten
        Loader::loadComponents($this, ['Input', 'Record']);
        
        // Laad de taalbestanden vanuit de plugin-map
        Language::loadLang('sepa_export_plugin', null, dirname(__FILE__) . DS . 'language' . DS);
        
        // Laad de configuratie
        $this->loadConfig(dirname(__FILE__) . DS . 'config.json');
    }
    
    /**
     * Voert alle benodigde installatie-acties uit
     *
     * @param int $plugin_id Het ID van de te installeren plugin
     */
    public function install($plugin_id)
    {
        try {
            // Maak de sepa_export_batches tabel aan
            $this->Record
                ->setField('id', [
                    'type' => 'INT',
                    'size' => "11",
                    'unsigned' => true,
                    'auto_increment' => true,
                ])
                ->setKey(['id'], 'primary')
                ->setField('name', [
                    'type' => 'VARCHAR',
                    'size' => "255",
                ])
                ->setField('total_amount', [
                    'type' => 'VARCHAR',
                    'size' => "20",
                    'default' => 0.00,
                ])
                ->setField('invoice_ids', [
                    'type' => 'TEXT',
                ])
                ->setField('created_at', [
                    'type' => 'DATETIME',
                ])
                ->create('sepa_export_batches', true);
                
            // Maak de sepa_mandates tabel aan
            $this->Record
                ->setField('id', [
                    'type' => 'INT',
                    'size' => "11",
                    'unsigned' => true,
                    'auto_increment' => true,
                ])
                ->setKey(['id'], 'primary')
                ->setField('client_id', [
                    'type' => 'INT',
                    'size' => "11",
                    'unsigned' => true,
                ])
                ->setField('iban', [
                    'type' => 'VARCHAR',
                    'size' => "34",
                ])
                ->setField('bic', [
                    'type' => 'VARCHAR',
                    'size' => "11",
                    'is_null' => true,
                ])
                ->setField('account_holder', [
                    'type' => 'VARCHAR',
                    'size' => "255",
                    'is_null' => true,
                ])
                ->setField('mandate_reference', [
                    'type' => 'VARCHAR',
                    'size' => "255",
                    'is_null' => true,
                ])
                ->setField('status', [
                    'type' => 'ENUM',
                    'size' => "'active','revoked','pending'",
                    'default' => 'active'
                ])
                ->setField('created_at', [
                    'type' => 'DATETIME',
                    'is_null' => true,
                ])
                ->create('sepa_mandates', true);
                
        } catch (Exception $e) {
            // Stel de foutmelding in als er een probleem is (bijv. onvoldoende rechten)
            $this->Input->setErrors(['db' => ['create' => $e->getMessage()]]);
            return;
        }
    }
    
    /**
     * Voert alle benodigde opschoonacties uit bij het deinstalleren van de plugin
     *
     * @param int $plugin_id Het ID van de plugin die wordt verwijderd
     * @param bool $last_instance True als dit de laatste instantie van de plugin is, anders false
     */
    public function uninstall($plugin_id, $last_instance)
    {
        if ($last_instance) {
            try {
                $this->Record->drop('sepa_export_batches');
            } catch (Exception $e) {
                // Als de foutcode 1051 (Unknown table) voorkomt, negeer deze
                if (strpos($e->getMessage(), '1051') === false) {
                    $this->Input->setErrors(['db' => ['drop' => $e->getMessage()]]);
                    return;
                }
            }
            
            try {
                $this->Record->drop('sepa_mandates');
            } catch (Exception $e) {
                if (strpos($e->getMessage(), '1051') === false) {
                    $this->Input->setErrors(['db' => ['drop' => $e->getMessage()]]);
                    return;
                }
            }
        }
    }
    
    /**
     * Geeft alle acties terug voor het toevoegen van navigatielinks in Blesta
     *
     * @return array Een numeriek geordend array van acties
     */
 
    
    public function getActions()
{
    return [
        // Admin link voor testbatches
        [
            'action' => 'nav_secondary_staff',
            'uri' => 'plugin/sepa_export/admin_main/index/',
            'name' => 'SEPA Export',
            'options' => ['parent' => 'billing/'],
            'enabled' => 1
        ],
        // Nieuwe exportlink voor echte factuurdata
        [
            'action' => 'export_client_group',
            'uri' => 'plugin/sepa_export/admin_main/export_client_group/?client_group_id=1',
            'name' => Language::_('AdminMain.export_client_group', true),
            'enabled' => 1
        ]
    ];
}




    /**
     * Geeft de configuratie van de plugin terug
     *
     * @return array Een array met de configuratiegegevens
     */
    public function getConfiguration()
    {
        return [
            'tab' => 'settings', // Dit is de controlleractie
            'method' => 'index',
            'label' => Language::_('SepaExportPlugin.name', true)
        ];
    }
}
