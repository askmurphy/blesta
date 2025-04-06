<?php

class SepaMandates extends SepaExportModel
{
    public function getByClient($client_id)
    {
        return $this->Record->select()->from('sepa_mandates')->where('client_id', '=', $client_id)->fetch();
    }

    public function updateStatus($client_id, $status)
    {
        return $this->Record->where('client_id', '=', $client_id)
            ->update('sepa_mandates', ['status' => $status]);
    }
}
