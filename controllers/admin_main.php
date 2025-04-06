<?php
/**
 * sepa_export admin_main controller
 *
 * @link https://www.rhodyon.eu Arthur Kerkmeester
 */
class AdminMain extends SepaExportController
{
    public function preAction()
    {
        parent::preAction();
        $this->structure->set('page_title', Language::_('AdminMain.index.page_title', true));
    }

    public function index()
    {
        $this->uses(['SepaExport.SepaExportBatches']);
        $batches = $this->SepaExportBatches->getAll();
        $this->set('batches', $batches);
        return $this->render('admin_main', 'index');
    }

    public function generate()
    {
        $this->uses(['SepaExport.SepaExportBatches']);

        $this->SepaExportBatches->add([
            'name' => 'Testbatch ' . date('Y-m-d H:i:s'),
            'total_amount' => '123.45',
            'invoice_ids' => '101,102,103',
            'created_at' => date('Y-m-d H:i:s')
        ]);

        $this->flashMessage('message', Language::_('AdminMain.index.batch_created', true));
        $this->redirect($this->base_uri . 'plugin/sepa_export/admin_main/index/');
    }

    public function download()
    {
        $this->uses(['SepaExport.SepaExportBatches']);
        $id = $this->get[0] ?? null;

        if (!$id || !is_numeric($id)) {
            $this->redirect($this->base_uri . 'plugin/sepa_export/admin_main/index/');
        }

        $batch = $this->SepaExportBatches->get($id);

        if (!$batch) {
            $this->redirect($this->base_uri . 'plugin/sepa_export/admin_main/index/');
        }

        $xml = $this->generateSepaXml($batch);

        header('Content-Type: application/xml');
        header('Content-Disposition: attachment; filename="sepa_batch_' . $batch->id . '.xml"');
        echo $xml;
        exit;
    }

    public function delete()
    {
        $this->uses(['SepaExport.SepaExportBatches']);
        $id = $this->get[0] ?? null;

        if ($id && is_numeric($id)) {
            $this->SepaExportBatches->delete($id);
            $this->setMessage('success', Language::_('AdminMain.index.batch_deleted', true));
        }

        $this->redirect($this->base_uri . 'plugin/sepa_export/admin_main/index/');
    }

    private function generateSepaXml($batch)
    {
        $doc = new DOMDocument('1.0', 'UTF-8');
        $doc->formatOutput = true;

        $xml = $doc->createElement('Document');
        $xml->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns', 'urn:iso:std:iso:20022:tech:xsd:pain.008.001.02');
        $xml->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');

        $CstmrDrctDbtInitn = $doc->createElement('CstmrDrctDbtInitn');

        $GrpHdr = $doc->createElement('GrpHdr');
        $GrpHdr->appendChild($doc->createElement('MsgId', 'MSG-' . date('YmdHis')));
        $GrpHdr->appendChild($doc->createElement('CreDtTm', date('Y-m-d\TH:i:s')));
        $GrpHdr->appendChild($doc->createElement('NbOfTxs', '1'));
        $GrpHdr->appendChild($doc->createElement('CtrlSum', $batch->total_amount));

        $InitgPty = $doc->createElement('InitgPty');
        $InitgPty->appendChild($doc->createElement('Nm', 'Voorbeeld BV'));
        $GrpHdr->appendChild($InitgPty);
        $CstmrDrctDbtInitn->appendChild($GrpHdr);

        $PmtInf = $doc->createElement('PmtInf');
        $PmtInf->appendChild($doc->createElement('PmtInfId', 'PMT-' . date('YmdHis')));
        $PmtInf->appendChild($doc->createElement('PmtMtd', 'DD'));
        $PmtInf->appendChild($doc->createElement('BtchBookg', 'true'));
        $PmtInf->appendChild($doc->createElement('NbOfTxs', '1'));
        $PmtInf->appendChild($doc->createElement('CtrlSum', $batch->total_amount));

        $PmtTpInf = $doc->createElement('PmtTpInf');
        $SvcLvl = $doc->createElement('SvcLvl');
        $SvcLvl->appendChild($doc->createElement('Cd', 'SEPA'));
        $PmtTpInf->appendChild($SvcLvl);
        $PmtTpInf->appendChild($doc->createElement('SeqTp', 'RCUR'));
        $PmtInf->appendChild($PmtTpInf);

        $PmtInf->appendChild($doc->createElement('ReqdColltnDt', date('Y-m-d', strtotime('+1 day'))));

        $Cdtr = $doc->createElement('Cdtr');
        $Cdtr->appendChild($doc->createElement('Nm', 'Voorbeeld BV'));
        $PmtInf->appendChild($Cdtr);

        $CdtrAcct = $doc->createElement('CdtrAcct');
        $Id = $doc->createElement('Id');
        $Id->appendChild($doc->createElement('IBAN', 'NL00INGB0000000000'));
        $CdtrAcct->appendChild($Id);
        $PmtInf->appendChild($CdtrAcct);

        $CdtrAgt = $doc->createElement('CdtrAgt');
        $FinInstnId = $doc->createElement('FinInstnId');
        $FinInstnId->appendChild($doc->createElement('BIC', 'INGBNL2A'));
        $CdtrAgt->appendChild($FinInstnId);
        $PmtInf->appendChild($CdtrAgt);

        $PmtInf->appendChild($doc->createElement('ChrgBr', 'SLEV'));

        $DrctDbtTxInf = $doc->createElement('DrctDbtTxInf');
        $PmtId = $doc->createElement('PmtId');
        $PmtId->appendChild($doc->createElement('EndToEndId', 'E2E-' . $batch->id));
        $DrctDbtTxInf->appendChild($PmtId);

        $InstdAmt = $doc->createElement('InstdAmt', $batch->total_amount);
        $InstdAmt->setAttribute('Ccy', 'EUR');
        $DrctDbtTxInf->appendChild($InstdAmt);

        $DrctDbtTx = $doc->createElement('DrctDbtTx');
        $MndtRltdInf = $doc->createElement('MndtRltdInf');
        $MndtRltdInf->appendChild($doc->createElement('MndtId', 'MANDATE-123'));
        $MndtRltdInf->appendChild($doc->createElement('DtOfSgntr', '2024-01-01'));
        $DrctDbtTx->appendChild($MndtRltdInf);
        $DrctDbtTxInf->appendChild($DrctDbtTx);

        $DbtrAgt = $doc->createElement('DbtrAgt');
        $DbtrAgt->appendChild($FinInstnId->cloneNode(true));
        $DrctDbtTxInf->appendChild($DbtrAgt);

        $Dbtr = $doc->createElement('Dbtr');
        $Dbtr->appendChild($doc->createElement('Nm', 'Jan Klant'));
        $DrctDbtTxInf->appendChild($Dbtr);

        $DbtrAcct = $doc->createElement('DbtrAcct');
        $DbtrAcctId = $doc->createElement('Id');
        $DbtrAcctId->appendChild($doc->createElement('IBAN', 'NL55RABO0123456789'));
        $DbtrAcct->appendChild($DbtrAcctId);
        $DrctDbtTxInf->appendChild($DbtrAcct);

        $RmtInf = $doc->createElement('RmtInf');
        $RmtInf->appendChild($doc->createElement('Ustrd', 'Factuurbetaling batch ' . $batch->id));
        $DrctDbtTxInf->appendChild($RmtInf);

        $PmtInf->appendChild($DrctDbtTxInf);
        $CstmrDrctDbtInitn->appendChild($PmtInf);
        $xml->appendChild($CstmrDrctDbtInitn);
        $doc->appendChild($xml);

        return $doc->saveXML();
    }
}
