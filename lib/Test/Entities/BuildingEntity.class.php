<?php

namespace IDA\Entities;

use IDA\Classes\Block\Getter as BlockGetter;

/**
 * Class BuildingEntity
 * @package IDA\Entities
 */
class BuildingEntity extends DefaultEntity
{
    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->getLangRawTitle();
    }

    /**
     * @return string
     */
    public function getFriendlyName()
    {
        $result = $this->getLangPropText('FRIENDLY_NAME');
        if (!$result) {
            $result = $this->getTitle();
        }
        return $result;
    }


    public function getDeadline($formatting = false)
    {
        $deadline = $this->getLangPropText('DEADLINE');

        if ($formatting) {
            return $this->getDeadlineFormatter($deadline);
        }

        return $deadline;
    }

    /**
     * @return string
     */
    public function getAdditionalText()
    {
        $result = $this->getLangPropText('ADD_TEXT');
        return $result;
    }

    /**
     * @return string
     */
    public function getStoreysNumberStr()
    {
        $result = $this->getPropValue('STOREYSNUMBER');
        if ($result) {
            $result = num_decline((int)$result, 'этаж, этажа, этажей');
        }
        return $result;
    }

    /**
     * @return string
     */
    public function getNorthStreet()
    {
        return $this->getPropValue('NORTH_STREET');
    }

    /**
     * @return string
     */
    public function getWestStreet()
    {
        return $this->getPropValue('WEST_STREET');
    }

    /**
     * @return string
     */
    public function getEastStreet()
    {
        return $this->getPropValue('EAST_STREET');
    }

    /**
     * @return string
     */
    public function getSouthStreet()
    {
        return $this->getPropValue('SOUTH_STREET');
    }

    /**
     * @return int
     */
    public function getStoreysNumber()
    {
        $result = $this->getPropValue('STOREYSNUMBER');
        return $result;
    }

    /**
     * @return bool|\IDA\Classes\Block\Image
     */
    public function getPlanLayout()
    {
        if ($this->hasImage('PLAN_LAYOUT')) {
            return $this->getImage('PLAN_LAYOUT');
        }
        return false;
    }

    /**
     * @return bool
     */
    public function hasPlanLayout()
    {
        return $this->getPlanLayout();
    }

    /**
     * @param $options array
     * @return string
     */
    public function getPlanLayoutThumbUrl($options)
    {
        if ($this->hasPlanLayout()) {
            return $this->getPlanLayout()->getThumbUrl($options);
        }
        return '';
    }

    /**
     * @return int
     */
    public function getBuildingSection()
    {
        return $this->getPropValue('SECTION');
    }

    /**
     * @return string
     */
    public function getLink()
    {
        return $this->getPropValue('LINK');
    }

    /**
     * @return string
     */
    public function getProjectId()
    {
        return $this->getPropValue('PROJECT_ID');
    }

    /**
     * @return string
     */
    public function getRefStreet()
    {
        return $this->getLangPropText('REF_STREET');
    }

    /**
     * @return string
     */
    public function getCompassImageUrl()
    {
        if ($this->hasImage('COMPASS_IMAGE')) {
            return $this->getImage('COMPASS_IMAGE')->getUrl();
        }
        return '';
    }

    /**
     * @param $project_id int
     * @param $type_code string
     * @return string
     */
    public function getCatalogUrl($project_id, $type_code = '')
    {
        $type = $type_code ?: 'apartments';
        $template_url = '/catalog/%type%/?project=%project%&building[]=%building%';
        return strtr($template_url, [
            '%project%' => $project_id,
            '%building%' => $this->getID(),
            '%type%' => $type,
        ]);
    }

    /**
     * @return string
     */
    public function getRealtyNumeral()
    {
        return $this->getLangPropTextUnesc('REALTY_NUMERAL');
    }

    /**
     * @param $count int
     * @param $realty_type_code string
     * @return string
     */
    public function getNumAparts($count, $realty_type_code)
    {
        $result = '';
        $template = $this->getRealtyNumeral();
        $template = json_decode($template, true);
        if ($numerals = $template[$realty_type_code]) {
            $result = getNumEnding($count, array_values($numerals));
            if ($result) {
                $result = sprintf($result, $count);
            }
        }
        return $result;
    }

    /**
     * @return string
     */
    public function getPhase()
    {
        return $this->getPropValue('PHASE');
    }

}