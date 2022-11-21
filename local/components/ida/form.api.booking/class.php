<?php

use Test\Entities\RealEstateEntity as RealEstateEntity;
use Test\Entities\BuildingEntity as BuildingEntity;
use IDA\Classes\Block\Getter as BlockGetter;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

class ApiBookingFormComponent extends \CBitrixComponent
{
    public function onPrepareComponentParams($arParams)
    {
        $arParams['SITE_ID'] = empty($_REQUEST['site']) ? SITE_ID : $_REQUEST['site'];
        $arParams['SITE_DIR'] = SITE_DIR;
        if (!$arParams['REAL_ESTATE_ID']) {
            $arParams['REAL_ESTATE_ID'] = $_REQUEST['real_estate_id'];
        }
        if ($arParams['REAL_ESTATE_ID']) {
            $arParams['REAL_ESTATE_ID'] = intval($arParams['REAL_ESTATE_ID']);
        }
        $arParams['ANTI_SPAM_NAME'] = bitrix_sessid();
        $arParams['SPENT_TIME'] = $_REQUEST[$arParams['ANTI_SPAM_NAME']];

        $free_status = $this->isFreeBooking($arParams['REAL_ESTATE_ID']);
        if ($free_status == 'Y') {
            app()->SetPageProperty('pay_class', '_not-pay');
        } else if ($free_status == 'N') {
            app()->SetPageProperty('pay_class', '_pay');
        }
        $arParams['FREE_STATUS'] = $free_status;
        
        return $arParams;
    }

    /**
     * @return string Y|N|U
     */
    public function isFreeBooking($real_estate_id)
    {
        $result = 'U';

        if (!$real_estate_id) {
            return $result;
        }

        $cacheTtl = COMPONENTS_CACHE_TTL;
        $key = 'real_estate_id=' . $real_estate_id;

        $obCache = new \CPHPCache();
        if ($obCache->InitCache($cacheTtl, __FUNCTION__ . $key)) {
            $result = $obCache->GetVars();
        } elseif ($obCache->StartDataCache()) {
            /**
             * @var $item \Test\Entities\RealEstateEntity
             */
            $item = \IDA\Classes\Block\Getter::instance()
                ->setFilter([
                    '=IBLOCK_TYPE' => 'catalog',
                    '=IBLOCK_CODE' => 'real-estate',
                    '=ACTIVE' => 'Y',
                    '=ID' => $real_estate_id,
                ])
                ->setClassName(\Test\Entities\RealEstateEntity::className())
                ->setSelectFields(['ID', 'IBLOCK_ID'])
                ->setUseTilda(false)
                ->getOne();

            if ($item) {
                $article_id = $item->getArticleId();
                if ($article_id) {
                    try {
                        // $result = dataApi()->isFreeBooking($article_id) ? 'Y' : 'N';
                        $result = 'Y';
                    } catch (\Exception $e) {
                        $result = 'U';
                    }
                }
            }

            $obCache->EndDataCache($result);
        }

        return $result;
    }

    public function executeComponent()
    {
        if ($this->getTemplateName() === 'ajax') {
            $this->executeReceiveAjaxData();
        } else {
            $this->executeRenderForm();
        }
    }

    /**
     * receive data through ajax, validate according rules and save
     * @throws Exception
     */
    public function executeReceiveAjaxData()
    {
        $this->arResult = [
            'success' => true,
            'message' => 'OK',
        ];

        try
        {
            if (!check_bitrix_sessid()) {
                throw new \Exception('Сессия истекла. Обновите страницу');
            }
            
            $id = $this->arParams['REAL_ESTATE_ID'];
            if (!$id) {
                throw new \Exception('Объект недвижимости не выбран');
            }
            
            $free_status = $this->arParams['FREE_STATUS'];
            if (!in_array($free_status, ['Y', 'N'])) {
                throw new \Exception('Объект недвижимости невозможно забронировать');
            }
            
            $api = dataLKApi2();
            if (!$api) {
                throw new \Exception('Доступы не сконфигурированы');
            }

            /**
             * @var $item RealEstateEntity
             */
            $item = BlockGetter::instance()
                ->setFilter([
                    '=IBLOCK_TYPE' => 'catalog',
                    '=IBLOCK_CODE' => 'real-estate',
                    '=ACTIVE' => 'Y',
                    '=ID' => $id,
                ])
                ->setClassName(RealEstateEntity::className())
                ->setSelectFields(['ID', 'IBLOCK_ID'])
                ->getOne();
            
            if (!$item) {
                throw new \Exception('Объект недвижимости не доступен для бронирования');
            }

            $article_id = $item->getArticleId();

            if (!$article_id) {
                throw new \Exception('Объект недвижимости не доступен для бронирования');
            }
            
            $result = $api->addBooking($article_id);
            if (!$result || !($result['result']['errorCode'] == 0)) {
                throw new \Exception('Не удалось забронировать объект недвижимости');
            }
            
        }
        catch (\Exception $e)
        {
            $this->arResult['success'] = false;
            $this->arResult['message'] = $e->getMessage();
        }

        $this->IncludeComponentTemplate();
    }

    public function executeRenderForm()
    {
        $template = '';
        $id = $this->arParams['REAL_ESTATE_ID'];
        if ($id > 0) {
            $free_status = $this->arParams['FREE_STATUS'];
            if (!in_array($free_status, ['Y', 'N'])) {
                $template = 'empty';
            }
        }

        /**
         * @var $item RealEstateEntity
         */
        $item = BlockGetter::instance()
            ->setFilter([
                '=IBLOCK_TYPE' => 'catalog',
                '=IBLOCK_CODE' => 'real-estate',
                '=ACTIVE' => 'Y',
                '=ID' => $id,
            ])
            ->setClassName(RealEstateEntity::className())
            ->setSelectFields(['ID', 'IBLOCK_ID'])
            ->getOne();

        if ($building = $item->getBuilding()) {
            $this->arResult['BUILDING_ID'] = mb_strtoupper($building->getCode());
        }

        $this->includeComponentTemplate($template);
    }
}