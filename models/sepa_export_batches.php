<?php
/**
 * sepa_export_batches Management
 *
 * @link https://www.rhodyon.eu Arthur Kerkmeester
 */
class SepaExportBatches extends SepaExportModel
{
    /**
     * Returns a list of records for the given company
     *
     * @param array $filters A list of filters for the query
     *
     *  - id
     *  - name
     *  - total_amount
     *  - invoice_ids
     *  - created_at
     * @param int $page The page number of results to fetch
     * @param array $order A key/value pair array of fields to order the results by
     * @return array An array of stdClass objects
     */
    public function getList(
        array $filters = [],
        $page = 1,
        array $order = ['id' => 'desc']
    ) {
        $records = $this->getRecord($filters)
            ->order($order)
            ->limit($this->getPerPage(), (max(1, $page) - 1) * $this->getPerPage())
            ->fetchAll();

        return $records;
    }

    /**
     * Returns the total number of record for the given filters
     *
     * @param array $filters A list of filters for the query
     *
     *  - id
     *  - name
     *  - total_amount
     *  - invoice_ids
     *  - created_at
     * @return int The total number of records for the given filters
     */
    public function getListCount(array $filters = [])
    {
        return $this->getRecord($filters)->numResults();
    }

    /**
     * Returns all records in the system for the given filters
     *
     * @param array $filters A list of filters for the query
     *
     *  - id
     *  - name
     *  - total_amount
     *  - invoice_ids
     *  - created_at
     * @param array $order A key/value pair array of fields to order the results by
     * @return array An array of stdClass objects
     */
    public function getAll(
        array $filters = [],
        array $order = ['id' => 'desc']
    ) {
        $records = $this->getRecord($filters)->order($order)->fetchAll();

        return $records;
    }

    /**
     * Fetches the record with the given identifier
     *
     * @param int $id The identifier of the record to fetch
     * @return mixed A stdClass object representing the record, false if no such record exists
     */
    public function get($id)
    {
        $record = $this->getRecord(['id' => $id])->fetch();

        return $record;
    }

    /**
     * Add a record
     *
     * @param array $vars An array of input data including:
     *
     *  - id
     *  - name
     *  - total_amount
     *  - invoice_ids
     *  - created_at
     * @return int The identifier of the record that was created, void on error
     */
    public function add(array $vars)
    {
        $this->Input->setRules($this->getRules($vars));

        if ($this->Input->validates($vars)) {
            $fields = ['id','name','total_amount','invoice_ids','created_at'];
            $this->Record->insert('sepa_export_batches', $vars, $fields);

            return $this->Record->lastInsertId();
        }
    }

    /**
     * Edit a record
     *
     * @param int $id The identifier of the record to edit
     * @param array $vars An array of input data including:
     *
     *  - id
     *  - name
     *  - total_amount
     *  - invoice_ids
     *  - created_at
     * @return int The identifier of the record that was updated, void on error
     */
    public function edit($id, array $vars)
    {
        
        $vars['id'] = $id;
        $this->Input->setRules($this->getRules($vars, true));

        if ($this->Input->validates($vars)) {
            $fields = ['id','name','total_amount','invoice_ids','created_at'];
            $this->Record->where('id', '=', $id)->update('sepa_export_batches', $vars, $fields);

            return $id;
        }
    }

    /**
     * Permanently deletes the given record
     *
     * @param int $id The identifier of the record to delete
     */
    public function delete($id)
    {
        // Delete a record
        $this->Record->from('sepa_export_batches')->
            where('sepa_export_batches.id', '=', $id)->
            delete();
    }

    /**
     * Returns a partial query
     *
     * @param array $filters A list of filters for the query
     *
     *  - id
     *  - name
     *  - total_amount
     *  - invoice_ids
     *  - created_at
     * @return Record A partially built query
     */
    private function getRecord(array $filters = [])
    {
        $this->Record->select()->from('sepa_export_batches');

        if (isset($filters['id'])) {
            $this->Record->where('sepa_export_batches.id', '=', $filters['id']);
        }

        if (isset($filters['name'])) {
            $this->Record->where('sepa_export_batches.name', '=', $filters['name']);
        }

        if (isset($filters['total_amount'])) {
            $this->Record->where('sepa_export_batches.total_amount', '=', $filters['total_amount']);
        }

        if (isset($filters['invoice_ids'])) {
            $this->Record->where('sepa_export_batches.invoice_ids', '=', $filters['invoice_ids']);
        }

        if (isset($filters['created_at'])) {
            $this->Record->where('sepa_export_batches.created_at', '=', $filters['created_at']);
        }

        return $this->Record;
    }

    /**
     * Returns all validation rules for adding/editing extensions
     *
     * @param array $vars An array of input key/value pairs
     *
     *  - id
     *  - name
     *  - total_amount
     *  - invoice_ids
     *  - created_at
     * @param bool $edit True if this if an edit, false otherwise
     * @return array An array of validation rules
     */
    private function getRules(array $vars, $edit = false)
    {
        $rules = [
            'id' => [
                'valid' => [
                    'if_set' => $edit,
                    'rule' => true,
                    'message' => Language::_('SepaExportBatches.!error.id.valid', true)
                ]
            ],
            'name' => [
                'valid' => [
                    'if_set' => $edit,
                    'rule' => true,
                    'message' => Language::_('SepaExportBatches.!error.name.valid', true)
                ]
            ],
            'total_amount' => [
                'valid' => [
                    'if_set' => $edit,
                    'rule' => true,
                    'message' => Language::_('SepaExportBatches.!error.total_amount.valid', true)
                ]
            ],
            'invoice_ids' => [
                'valid' => [
                    'if_set' => $edit,
                    'rule' => true,
                    'message' => Language::_('SepaExportBatches.!error.invoice_ids.valid', true)
                ]
            ],
            'created_at' => [
                'valid' => [
                    'if_set' => $edit,
                    'rule' => true,
                    'message' => Language::_('SepaExportBatches.!error.created_at.valid', true)
                ]
            ]
        ];

        return $rules;
    }
}
