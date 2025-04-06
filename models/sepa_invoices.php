<?php
class SepaInvoices extends SepaExportModel
{
    /**
     * Haalt openstaande facturen op voor een gegeven klantengroep.
     *
     * @param int $client_group_id Het ID van de klantengroep
     * @return array Een array van factuurobjecten
     */
    public function getOpenInvoicesByClientGroup($client_group_id)
    {
        // Dit is een voorbeeld-query. Pas de tabelnamen en kolommen aan je eigen Blesta-DB aan.
        // In Blesta staan facturen in de `invoices`-tabel, en `client_group_id` staat meestal in de `clients`-tabel.
        // Je moet dus waarschijnlijk joinen op de `clients`-tabel om de groep te filteren.
        
        return $this->Record->select(['invoices.*', 'clients.id' => 'client_id', 'clients.client_group_id'])
            ->from('invoices')
            ->innerJoin('clients', 'clients.id', '=', 'invoices.client_id', false)
            ->where('clients.client_group_id', '=', $client_group_id)
            ->where('invoices.status', '=', 'active') // of 'open', afhankelijk van je DB
            ->fetchAll();
    }
}
