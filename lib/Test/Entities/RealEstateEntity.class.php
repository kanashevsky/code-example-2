<?php

namespace IDA\Entities;

use IDA\Classes\Block\Getter as BlockGetter;

/**
 * Class RealEstateEntity
 * @package IDA\Entities
 */
class RealEstateEntity extends DefaultEntity
{
    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->getLangRawTitle();
    }

    /**
     * @return int
     */
    public function getRooms()
    {
        return intval($this->getPropValue('ROOMS'));
    }


    /**
     * @return bool
     */
    public function getIsApart()
    {
        return boolval($this->getPropValue('IS_APART'));
    }

    /**
     * @return string
     */
    public function getRoomsText()
    {
        return $this->getPropValue('ROOMS_TEXT');
    }

    /**
     * @return float
     */
    public function getArea()
    {
        return str2float($this->getPropValue('AREA'));
    }

    /**
     * @param $raw bool
     * @return float|bool
     */
    public function getPrice($raw = false)
    {
        $value = $this->getPropValue('PRICE');
        if ($raw) {
            return $value;
        }
        $value = str2float($value);
        return is_numeric($value) ? number_format($value, 0, '.', ' ') : false;
    }

    /**
     * @param $raw bool
     * @return float|bool
     */
    public function getPriceOld($raw = false)
    {
        $value = $this->getPropValue('PRICE_OLD');
        if ($raw) {
            return $value;
        }
        $value = str2float($value);
        return is_numeric($value) ? number_format($value, 0, '.', ' ') : false;
    }

    /**
     * @return bool
     */
    public function isDisabled()
    {
        return $this->getChecked('IS_DISABLED');
    }

    /**
     * @return int
     */
    public function getFloor()
    {
        $floor = $this->getPropValue('FLOOR');
        if (is_numeric($floor)) {
            return intval($floor);
        }
        return false;
    }

    /**
     * @return string
     */
    public function getApart()
    {
        return $this->getPropValue('APART');
    }

    /**
     * @return int
     */
    public function getSection()
    {
        return intval($this->getPropValue('SECTION'));
    }

    /**
     * @return int
     */
    public function getTypeId()
    {
        return $this->getPropValue('TYPE');
    }

    /**
     * @return DefaultEntity
     */
    public function getType()
    {
        static $types;

        $id = $this->getTypeId();
        if (!($id > 0)) {
            return false;
        }
        if (!isset($types[$id])) {
            $types[$id] = BlockGetter::instance()
                ->setFilter([
                    '=IBLOCK_TYPE' => 'manuals',
                    '=IBLOCK_CODE' => 'realty-types',
                    '=ACTIVE' => 'Y',
                    '=ID' => $id,
                ])
                ->setUseTilda(false)
                ->setClassName(DefaultEntity::className())
                ->getOne();
        }

        return $types[$id];
    }

    /**
     * @param $types array
     * @return bool
     */
    public function isType($types)
    {
        $type = $this->getType();
        if (!$type) {
            return false;
        }
        return in_array($type->getCode(), $types);
    }

    /**
     * @return string
     */
    public function getTypeName()
    {
        $result = '';
        $type = $this->getType();
        if ($type) {
            $result = $type->getLangRawTitle();
        }
        return $result;
    }

    /**
     * @return string
     */
    public function getTypeCode()
    {
        $result = '';
        $type = $this->getType();
        if ($type) {
            $result = $type->getCode();
        }
        return $result;
    }

    /**
     * @return int
     */
    public function getRenovationId()
    {
        return $this->getPropValue('RENOVATION');
    }

    /**
     * @return
     *
     */
    public function getRenovation()
    {
        static $renovations;

        $id = $this->getRenovationId();
        if (!($id > 0)) {
            return false;
        }
        if (!isset($renovations[$id])) {
            $renovations[$id] = BlockGetter::instance()
                ->setFilter([
                    '=IBLOCK_TYPE' => 'manuals',
                    '=IBLOCK_CODE' => 'renovations',
                    '=ACTIVE' => 'Y',
                    '=ID' => $id,
                ])
                ->setUseTilda(false)
                ->setClassName(DefaultEntity::className())
                ->getOne();
        }

        return $renovations[$id];
    }

    /**
     * @return int
     */
    public function getStatusId()
    {
        return $this->getPropValue('STATUS');
    }

    /**
     * @return string
     */
    public function getIsActive()
    {
        return $this->data['ACTIVE'];
    }


    /**
     * @return DefaultEntity
     */
    public function getStatus()
    {
        static $statuses;

        $id = $this->getStatusId();
        if (!($id > 0)) {
            return false;
        }

        if (!isset($statuses[$id])) {
            $statuses[$id] = BlockGetter::instance()
                ->setFilter([
                    '=IBLOCK_TYPE' => 'manuals',
                    '=IBLOCK_CODE' => 'estate_status',
                    '=ACTIVE' => 'Y',
                    '=ID' => $id,
                ])
                ->setUseTilda(false)
                ->setClassName(DefaultEntity::className())
                ->getOne();
        }
        $result = (array)$statuses[$id]->data;
        return [
            'id' => $result['ID'],
            'code' => $result['CODE'],
            'name' => $result['NAME']
        ];
    }

    /**
     * @param $default_value string
     * @return string
     */
    public function getRenovationText($default_value = '')
    {
        $result = '';
        $val = $this->getRenovation();
        if ($val) {
            $result = $val->getLangRawTitle();
            if ($val->getCode() != 'no-renovation') {
                $result = sprintf(__('%s'), $result);
            }
        }
        if (!$result) {
            $result = $default_value;
        }
        return $result;
    }

    /**
     * @return string
     */
    public function getApartInCatalogUrl()
    {
        $type = $this->getType();
        if (!$type) {
            return '';
        }
        $type = $type->getCode();
        $url = $this->getUrl();
        return SITE_DIR . 'catalog/' . $type . $url;
    }

    /**
     * @return bool|\IDA\Classes\Block\Image
     */
    public function getFlatLayout()
    {
        if ($this->hasImage('FLAT_LAYOUT')) {
            return $this->getImage('FLAT_LAYOUT');
        }
        return false;
    }

    /**
     * @return bool
     */
    public function hasFlatLayout()
    {
        return $this->getFlatLayout();
    }

    /**
     * @param $options array
     * @return string
     */
    public function getFlatLayoutThumbUrl($options)
    {
        if ($this->hasFlatLayout()) {
            return $this->getFlatLayout()->getThumbUrl($options);
        }
        return '';
    }

    /**
     * @return bool|\IDA\Classes\Block\Image
     */
    public function getFloorLayout()
    {
        if ($this->hasImage('FLOOR_LAYOUT')) {
            return $this->getImage('FLOOR_LAYOUT');
        }
        return false;
    }

    /**
     * @return bool
     */
    public function hasFloorLayout()
    {
        return $this->getFloorLayout();
    }

    /**
     * @param $options array
     * @return string
     */
    public function getFloorLayoutThumbUrl($options)
    {
        if ($this->hasFloorLayout()) {
            return $this->getFloorLayout()->getThumbUrl($options);
        }
        return '';
    }

    /**
     * @return int[]
     */
    public function getSalesIds()
    {
        return $this->getPropValue('SALES');
    }

    /**
     * @return string[]
     */
    public function getLayout3d()
    {
        return $this->getPropValue('LAYOUT_3D');
    }

    /**
     * @return array[]
     */
    public function getWindowViewUrls()
    {
        return $this->getPropValue('WINDOW_VIEW_URLS');
    }

    /**
     * @return array[]
     */
    public function getTags()
    {
        return $this->getPropValue('TAGS');
    }

    /**
    * @return int[]
    */
    public function getCeilingHeight()
    {
        return $this->getPropValue('CEILING_HEIGHT');
    }

    /**
    * @return string[]
    */
    public function getPeculiarities()
    {
        return $this->getPropValue('PECULIARITIES');
    }

    /**
     * @return SaleEntity[]
     */
    public function getSales()
    {
        static $store;

        $result = [];

        $ids = $this->getSalesIds();
        $unstored_ids = [];
        foreach ($ids as $id) {
            if (!$id) {
                continue;
            }
            if (!isset($store[$id])) {
                $unstored_ids[] = $id;
            }
        }

        if (!empty($unstored_ids)) {
            $items = BlockGetter::instance()
                ->setFilter([
                    '=IBLOCK_TYPE' => 'manuals',
                    '=IBLOCK_CODE' => 'sales',
                    '=ID' => $unstored_ids,
                    '=ACTIVE' => 'Y',
                ])
                ->setUseTilda(false)
                ->setSelectFields(['ID', 'IBLOCK_ID', 'CODE', 'NAME', 'PREVIEW_PICTURE'])
                ->setClassName(SaleEntity::className())
                ->setHydrateById(true)
                ->get();

            foreach ($items as $id => $item) {
                $store[$id] = $item;
            }
        }

        foreach ($ids as $id) {
            if ($store[$id]) {
                $result[] = $store[$id];
            }
        }

        return $result;
    }

    /**
     * @return bool
     */
    public function isSale()
    {
        return !empty($this->getSalesIds());
    }

    /**
     * @return \IDA\Classes\Block\File
     * @throws \Exception
     */
    public function getPDF()
    {
        return $this->getFile('PDF');
    }

    /**
     * @return bool
     */
    public function hasPDF()
    {
        return $this->hasFile('PDF');
    }

    /**
     * @param $allow_generate bool
     * @return string
     */
    public function getPDFUrl($allow_generate = false)
    {
        if ($this->hasPDF()) {
            return $this->getPDF()->getUrl();
        }
        if ($allow_generate && $this->isType(['apartments'])) {
            $section = $this->getParentSection();
            if (!$section) {
                return '';
            }
            $url = $this->getUrl();
            $url = SITE_DIR . 'pdf_apart_layout' . $url . $this->makePDFFileName();
            return $url;
        }
        return '';
    }

    /**
     * @param $allow_generate bool
     * @return bool
     */
    public function hasPDFUrl($allow_generate = false)
    {
        return strlen($this->getPDFUrl($allow_generate)) > 0;
    }

    /**
     * @return string
     */
    public function makePDFFileName()
    {
        return sprintf('IDA_%s.pdf', $this->getCode());
    }

    /**
     * @return int
     */
    public function getBuildingId()
    {
        return $this->getPropValue('BUILDING');
    }

    /**
     * @return BuildingEntity
     */
    public function getBuilding()
    {
        static $store;
        global $APPLICATION;
        $id = $this->getBuildingId();
        if (!($id > 0)) {
            return false;
        }
        if (!isset($store[$id])) {
            $store[$id] = BlockGetter::instance()
                ->setFilter([
                    '=IBLOCK_TYPE' => 'manuals',
                    '=IBLOCK_CODE' => 'buildings',
                    '=ACTIVE' => 'Y',
                    '=ID' => $id,
                ])
                ->setUseTilda(false)
                ->setClassName(BuildingEntity::className())
                ->getOne();
        }

        if (empty($store[$id])) {
            LocalRedirect('/404.php');
        }

        return $store[$id];
    }

    /**
     * @return string
     */
    public function getBuildingName()
    {
        $result = '';
        $building = $this->getBuilding();
        if ($building) {
            $result = $building->getFriendlyName();
        }
        return $result;
    }

    /**
     * @return int
     */
    public function getProjectId()
    {
        return $this->getPropValue('PROJECT');
    }

    /**
     * @return ProjectItem
     */
    public function getProject()
    {
        static $store;

        $id = $this->getProjectId();
        if (!($id > 0)) {
            return false;
        }
        if (!isset($store[$id])) {
            $store[$id] = BlockGetter::instance()
                ->setFilter([
                    '=IBLOCK_TYPE' => 'projects_page',
                    '=IBLOCK_CODE' => 'projects',
                    '=ACTIVE' => 'Y',
                    '=ID' => $id,
                ])
                ->setUseTilda(false)
                ->setClassName(ProjectItem::className())
                ->getOne();
        }

        return $store[$id];
    }

    /**
     * @return string
     */
    public function getClassifierId()
    {
        return $this->getPropRawValue('CLASSIFIERID');
    }

    /**
     * @return string
     */
    public function getArticleId()
    {
        return $this->getPropRawValue('ARTICLEID');
    }

    /**
     * @return string
     */
    public function isAssignment()
    {
        return $this->getPropRawValue('ASSIGNMENT');
    }

    /**
     * @return bool
     */
    public function isInFavorites()
    {
        return favorites()->isExist($this->getID());
    }

    /**
     * @return string
     */
    public function getBookingUrl()
    {
        $result = '';
        //TODO: вынести урл отдельно или вообще в класс все вынести новый
        $url = 'http://booking.test.ru/redirect?classifierid=%classifierid%&articleid=%articleid%';
        if ($this->getClassifierId() && $this->getArticleId()) {
            $result = strtr($url, [
                '%classifierid%' => $this->getClassifierId(),
                '%articleid%' => $this->getArticleId(),
            ]);
            $lead_id = getRequestOrCookieValue('utm_uid', true);
            if ($lead_id) {
                $result .= '&leadid=' . $lead_id;
            }
        }
        return $result;
    }

    /**
     * @return bool
     */
    public function isAllowBookingButton()
    {
        return (isShowBookingButton()) && (strlen($this->getBookingUrl()) > 0);
    }
}