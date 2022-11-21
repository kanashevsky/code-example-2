<?php

namespace IDA\Classes\Block;

class Getter extends \IDA\Classes\Base\Getter
{
    protected $arGroupBy = null;
    protected $arSelectFields = null;
    protected $className = \IDA\Classes\Block\Entity::class;
    protected $useTilda = true;

    protected $total;

    /**
     * @static
     * @return Getter
     */
    public static function instance()
    {
        return new static();
    }

    /**
     * @return bool
     */
    public function getUseTilda()
    {
        return $this->useTilda;
    }

    /**
     * @param $reason bool
     * @return static
     */
    public function setUseTilda($reason)
    {
        $this->useTilda = $reason;
        return $this;
    }

    /**
     * @return Getter
     */
    public function setGroupBy($arGroupBy)
    {
        $this->arGroupBy = $arGroupBy;
        return $this;
    }

    /**
     * @param $pagingSize
     * @param $pageNum
     * @return Getter
     */
    public function paginate($pagingSize, $pageNum)
    {
        $this->setPageSize($pagingSize);
        $this->setPageNum(intval($pageNum) < 1 ? 1 : intval($pageNum));
        return $this;
    }

    /**
     * @return \CDBResult|\CIBlockResult|mixed|string
     */
    public function getResult()
    {
        $element = new \CIBlockElement();

        return $element->GetList(
            $this->arOrder,
            $this->arFilter,
            empty($this->arGroupBy) ? null : $this->arGroupBy,
            empty($this->arNavStartParams) ? null : $this->arNavStartParams,
            $this->arSelectFields
        );
    }

    /**
     * @return \IDA\Classes\Block\Entity[]
     */
    public function get()
    {
        if (\IDA\Classes\Registry::bitrixCacheEnabled() && ($retval = $this->getCachedResult()))
        {
            return $retval;
        }

        $retval = array();

        $resultSet = $this->getResult();

        if (isset($this->resultSetCallback))
        {
            $resultSet = call_user_func($this->resultSetCallback, $resultSet);
        }

        $key = -1;

        while ($obRes = $resultSet->GetNextElement(true, $this->useTilda))
        {
            switch ($this->fetchMode)
            {
                case self::FETCH_MODE_FIELDS:
                    $element = $obRes->GetFields();
                    break;

                case self::FETCH_MODE_PROPERTIES:
                    $element = $obRes->getProperties();
                    break;

                default:
                    $element = array_merge($obRes->GetFields(), $obRes->getProperties());
                    break;
            }

            foreach ((array) $this->callbacks as $callback)
            {
                if ($callbackResult = call_user_func($callback, $element))
                {
                    $element = $callbackResult;
                }
            }

            $key = $this->hydrateById ? $element['ID'] : ++$key;

            switch ($this->hydrationMode)
            {
                case self::HYDRATION_MODE_OBJECTS_ARRAY:
                case self::HYDRATION_MODE_OBJECTS_COLLECTION:
                    $className = $this->className;
                    $retval[$key] = new $className($element);
                break;


                default:
                    $retval[$key] = $element;
                break;
            }
        }

        if ($this->hydrationMode == self::HYDRATION_MODE_OBJECTS_COLLECTION)
        {
            $retval = new Collection($retval);
        }

        if (\IDA\Classes\Registry::bitrixCacheEnabled())
        {
            $this->cacheResult($retval);
        }

        return $retval;
    }

    public function getFoundRows()
    {
        $getter = clone $this;

        // в фильтре есть сложная логика, простая группировка не даст нужного
        // результата, получаем все элементы и считаем из
        if (true || array_key_exists(0, $this->arFilter))
        {
            $getter
                ->setNavStartParams(array())
                ->setOrder(array())
                ->setFetchMode(self::FETCH_MODE_FIELDS)
                ->setHydrationMode(self::HYDRATION_MODE_ARRAY)
                ->setSelectFields(array('ID'))
            ;

            if ($this->cacheManager)
            {
                $getter->setCacheManager($this->cacheManager);
            }

            $this->total = (int) count($getter->get());
        }
        else
        {
            $res = $getter
                ->setNavStartParams(null)
                ->setGroupBy(array('IBLOCK_ID'))
                    ->getResult()
                        ->Fetch();

            $this->total = empty($res) ? false : $res['CNT'];
        }

        return $this->total;
    }

    /**
     * @return \paging
     */
    public function getPagingObject($urlTemplate, $total = null)
    {
        if (isset($total))
        {
            $this->total = $total;
        }

        if (!isset($this->total))
        {
            $this->total = $this->getFoundRows();
        }

        $paging = new \IDA\paging($this->arNavStartParams['iNumPage'], $this->total, $this->arNavStartParams['nPageSize']);
        $paging->setFormat($urlTemplate);

        return $paging;
    }
}
