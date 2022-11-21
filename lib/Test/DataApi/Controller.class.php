<?php

namespace IDA\DataApi;

/**
 * Class Controller
 * @package IDA\DataApi
 */
class Controller extends BaseController
{
    /**
     * @param $project_ids string[]
     * @param $cacheTtl bool
     * @return array
     */
    public function getProjectsList($project_ids = [], $cacheTtl = false)
    {
        $filter = serialize($project_ids);
        $cacheTtl = is_numeric($cacheTtl) ? $cacheTtl : COMPONENTS_CACHE_TTL;
        $result = '';
        $obCache = new \CPHPCache();
        if ($obCache->InitCache($cacheTtl, __FUNCTION__ . $filter)) {
            $result = $obCache->GetVars();
        }
        elseif ($obCache->StartDataCache()) {
            $result = $this->getRawProjectsList($project_ids);
            $obCache->EndDataCache($result);
        }

        return $result;
    }

    /**
     * @param $project_ids string[]
     * @return array
     */
    public function getRawProjectsList($project_ids = [])
    {
        $result = [];
        $data = $this->getDevelopmentProjects($project_ids);
        if ($data['result']) {
            $result = $data['result']['developmentprojects'];
        }
        return $result;
    }

    /**
     * @param $telephone string
     * @return string
     */
    public function getCustomerId($telephone =  false)
    {
        $result = '';

        if (!$telephone) {
            $telephone = getSessionPhone();
        }

        if (!$telephone) {
            return false;
        }

        $data = parent::getCustomerId($telephone);
        if (!isErrors()) {
            $result = $data['result']['customerId'];
        }
        
        return $result;
    }
    
    /**
     * @return array
     */
    public function getCustomerProfile()
    {
        $result = [];
        $data = parent::getCustomerProfile();
        if (!isErrors()) {
            $result = $data['result'];
        }
        
        return $result;
    }
    
    /**
     * @return array
     */
    public function getCustomerSalutationNotifications()
    {
        $result = [];
        $data = parent::getCustomerSalutationNotifications();
        if (!isErrors()) {
            $result = $data['result'];
        }
        
        return $result;
    }
    
    /**
     * @param $invoiceId string
     * @param $cacheTtl bool
     * @return array
     */
    public function getInvoice($invoiceId, $cacheTtl = false)
    {
        $result = [];
        $data = parent::getInvoice($invoiceId, $cacheTtl);
        if (!isErrors()) {
            $result = $data['result'];
        }
        
        return $result;
    }
    
    /**
     * @return array
     */
    public function getCustomerAgreements()
    {
        $result = [];
        $data = parent::getCustomerAgreements();
        if (!isErrors()) {
            $result = $data['result']['agreements'];
        }
        
        return $result;
    }
    
    /**
     * @return array
     */
    public function getCustomerAgreementsFull()
    {
        $result = [];
        $data = parent::getCustomerAgreementsFull();
        if (!isErrors()) {
            $result = $data['result']['agreements'];
        }
        
        return $result;
    }
    
    /**
     * @param $agreementId string
     * @param $cacheTtl bool
     * @return array
     */
    public function getAgreementInvoices($agreementId, $cacheTtl = false)
    {
        $result = [];
        $data = parent::getAgreementInvoices($agreementId, $cacheTtl);
        if (!isErrors()) {
            $result = $data['result']['invoices'];
        }
        
        return $result;
    }
    
    /**
     * @param $agreementId string
     * @param $cacheTtl bool
     * @return array
     */
    public function getAgreementRealtyobjects($agreementId, $cacheTtl = false)
    {
        $result = [];
        $data = parent::getAgreementRealtyobjects($agreementId, $cacheTtl);
        if (!isErrors()) {
            $result = $data['result']['realtyobjects'];
        }
        
        return $result;
    }
    
    /**
     * @param $agreementId string
     * @param $cacheTtl bool
     * @return array
     */
    public function getAgreementPaymentplans($agreementId, $cacheTtl = false)
    {
        $result = [];
        $data = parent::getAgreementPaymentplans($agreementId, $cacheTtl);     
        if (!isErrors()) {
            $result = $data['result']['paymentplans'];
        }
        
        return $result;
    }
    
    /**
     * @param $paymentplanId string
     * @param $cacheTtl bool
     * @return array
     */
    public function getPaymentplan($paymentplanId, $cacheTtl = false)
    {
        $result = [];
        $data = parent::getPaymentplan($paymentplanId, $cacheTtl);    
        if (!isErrors()) {
            $result = $data['result'];
        }
        
        return $result;
    }
    
    /**
     * @param $inspectionId string
     * @param $cacheTtl bool
     * @return array
     */
    public function getInspection($inspectionId, $cacheTtl = false)
    {
        $result = [];
        $data = parent::getInspection($inspectionId, $cacheTtl);
        if (!isErrors()) {
            $result = $data['result'];
        }
        
        return $result;
    }
    
    /**
     * @param $agreementId string
     * @param $realtyobjectId string
     * @param $dateFrom string
     * @param $dateTill string
     * @param $cacheTtl bool
     * @return array
     */
    public function getInspectionSchedule($agreementId, $realtyobjectId, $dateFrom, $dateTill, $cacheTtl = false)
    {
        $result = [];
        $data = parent::getInspectionSchedule($agreementId, $realtyobjectId, $dateFrom, $dateTill, $cacheTtl);  
        if (!isErrors()) {
            $result = $data['result'];
        }
        
        return $result;
    }
    
    /**
     * @param $processstageId string
     * @param $realtyobjectId string
     * @param $dateFrom string
     * @param $dateTill string
     * @return string
     */
    public function addProcessstageInspection($processstageId, $realtyobjectId, $dateFrom, $dateTill)
    {
        $result = '';
        $data = parent::addProcessstageInspection($processstageId, $realtyobjectId, $dateFrom, $dateTill);
        if (!isErrors()) {
            $result = $data['result']['inspectionId'];
        }
        
        return $result;
    }
    
    /**
     * @return array
     */
    public function getCustomerPreliminaryInvoices()
    {
        $result = [];
        $data = parent::getCustomerPreliminaryInvoices();
        if (!isErrors()) {
            $invoices = $data['result']['invoices'];
            foreach ($invoices as $invoice) {
                $result[] = $invoice['invoiceId'];
            }
        }
        
        return $result;
    }
    
    /**
     * @return array
     */
    public function getCustomerPreliminaryInvoicesFull()
    {
        $result = [];
        $data = parent::getCustomerPreliminaryInvoicesFull();   
        if (!isErrors()) {
            $result = $data['result']['invoices'];
        }
        
        return $result;
    }
    
    /**
     * @return array
     */
    public function getBookedRealtyobjects()
    {
        $result = [];
        $data = parent::getBookedRealtyobjects();
        if (!isErrors()) {
            $result = $data['result']['realtyobjects'];
        }
        
        return $result;
    }
    
    /**
     * @param $realtyobjectId string
     * @param $agreementId string
     * @param $cacheTtl bool
     * @return array
     */
    public function getRealtyobject($realtyobjectId, $agreementId, $cacheTtl = false)
    {
        $result = [];
        $data = parent::getRealtyobject($realtyobjectId, $agreementId, $cacheTtl);    
        if (!isErrors()) {
            $result = $data['result'];
        }
        
        return $result;
    }
    
    /**
     * @return array
     */
    public function getQuestionnaireAttributesValues()
    {
        $result = [];
        $data = parent::getQuestionnaireAttributesValues();
        if (!isErrors()) {
            $result = $data['result']['attributes'];
        }
        
        return $result;
    }
    
    /**
     * @return bool
     */
    public function isProfileFilled()
    {
        return $this->checkCustomerShortQuestionnaire();
    }
    
    /**
     * @param $realtyobjectId string
     * @param $realtyobject array
     * @param $cacheTtl bool
     * @return array
     * @throws \Exception
     */
    public function getBookingInfo($realtyobjectId, &$realtyobject = [], $cacheTtl = false)
    {
        $result = [];
        
        if (!$realtyobjectId) {
            throw new \Exception('Unknown realtyobjectId');
        }
        $filter = serialize([
            'realtyobjectId' => $realtyobjectId,
        ]);
        $cacheTtl = is_numeric($cacheTtl) ? $cacheTtl : COMPONENTS_CACHE_TTL;
        $obCache = new \CPHPCache();
        if ($obCache->InitCache($cacheTtl, __FUNCTION__ . $filter)) {
            $result = $obCache->GetVars();
        }
        elseif ($obCache->StartDataCache()) {
            
            $result = $this->getRealtyObjects([$realtyobjectId]);
            $result = $result['result'];
            if (!is_array($result)) {
                throw new \Exception('Unknown result');
            }
            if (!array_key_exists('realtyobjects', $result)) {
                throw new \Exception('Unknown result');
            }
            $realty_object = reset($result['realtyobjects']);
            if (!is_array($realty_object)) {
                throw new \Exception('Unknown realty object');
            }
            $result = $realty_object;
                    
            $obCache->EndDataCache($result);
        }
        $realtyobject = $result;
        
        return $result['bookingInfo'];
    }

    /**
     * @param $agreement_id string
     * @param $cacheTtl bool
     * @return array
     */
    public function getAgreementProcess($agreement_id, $cacheTtl = false)
    {
        $result = [];
        $data = parent::getAgreementProcess($agreement_id, $cacheTtl);
        if (!isErrors()) {
            $result = $data['result']['processstages'];
        }
        
        return $result;
    }

}