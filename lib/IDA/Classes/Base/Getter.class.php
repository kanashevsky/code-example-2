<?php
/**
 * Created by PhpStorm.
 * User: Администратор
 * Date: 17.03.14
 * Time: 9:41
 */

namespace IDA\Classes\Base;

abstract class Getter
{
    const FETCH_MODE_ALL = 0;
    const FETCH_MODE_FIELDS = 1;
    const FETCH_MODE_PROPERTIES = 2;
    const FETCH_MODE_FETCH = 3;

    const HYDRATION_MODE_ARRAY = 0;
    const HYDRATION_MODE_OBJECTS_ARRAY = 1;
    const HYDRATION_MODE_OBJECTS_COLLECTION = 2;

    protected $fetchMode = self::FETCH_MODE_ALL;
    protected $hydrationMode = self::HYDRATION_MODE_OBJECTS_ARRAY;
    protected $arOrder = array('SORT' => 'asc');
    protected $arFilter = null;
    protected $arNavStartParams = null;
    protected $arSelectFields = null;
    protected $callbacks = array();
    protected $resultSetCallback = null;

    protected $hydrateById = false;

    protected function __construct() {}

    static $resultCache;

    /**
     * @var \IDA\Classes\Cache\Manager
     */
    protected $cacheManager;
    protected $cacheId;

    public function setCacheManager(\IDA\Classes\Cache\Manager $manager)
    {
        $this->cacheManager = $manager;
        return $this;
    }

    public function setCacheId($cacheId)
    {
        $this->cacheId = $cacheId;
        return $this;
    }

    /**
     * @return static
     */
    public function setHydrateById($mode)
    {
        $this->hydrateById = (bool) $mode;
        return $this;
    }

    /**
     * @return static
     */
    public function fetchById($value)
    {
        return $this->setHydrateById($value);
    }

    /**
     * @return static
     */
    public function setFetchMode($mode)
    {
        $this->fetchMode = (int) $mode;
        return $this;
    }

    /**
     * @return static
     */
    public function setOrder($arOrder)
    {
        $this->arOrder = $arOrder;
        return $this;
    }

    /**
     * @return static
     */
    public function setFilter($arFilter)
    {
        $this->arFilter = $arFilter;
        return $this;
    }

    /**
     * @return static
     */
    public function addFilter()
    {
        $args = func_get_args();

        if (!is_array($this->arFilter))
        {
            $this->arFilter = array();
        }

        if (count($args) == 1 && is_array($args[0]))
        {
            foreach ($args[0] as $k => $v)
            {
                $this->arFilter[$k] = $v;
            }
        }
        else if (count($args) == 2)
        {
            $this->arFilter[$args[0]] = $args[1];
        }
        else
        {
            throw new \Exception('Wrong arguments count or type for ' . __METHOD__);
        }

        return $this;
    }

    /**
     * @param $callback callable
     * @return $this
     * @throws \Exception
     */
    public function addCallback($callback)
    {
        if (!is_callable($callback))
        {
            throw new \Exception('Passed callback is not callable, ' . __METHOD__);
        }

        $this->callbacks[] = $callback;
        return $this;
    }

    /**
     * @param $callback callable
     * @return $this
     * @throws \Exception
     */
    public function setResultSetCallback($callback)
    {
        if (!is_callable($callback))
        {
            throw new \Exception('Passed callback is not callable, ' . __METHOD__);
        }

        $this->resultSetCallback = $callback;
        return $this;
    }

    /**
     * @param $mode bool
     * @return $this
     */
    public function setHydrationMode($mode)
    {
        $this->hydrationMode = $mode;
        return $this;
    }

    /**
     * @param $className string
     * @return $this
     * @throws \Exception
     */
    public function setClassName($className)
    {
        if (!class_exists($className))
        {
            throw new \Exception("Class $className doest not exist, " . __METHOD__);
        }

        $this->className = $className;
        return $this;
    }

    /**
     * @param $arNavStartParams array
     * @return static
     */
    public function setNavStartParams($arNavStartParams)
    {
        $this->arNavStartParams = $arNavStartParams;
        return $this;
    }

    /**
     * @param $size int
     * @return static
     */
    public function setPageSize($size)
    {
        $this->arNavStartParams['nPageSize'] = (int)$size;
        return $this;
    }

    /**
     * @param $pageNum int
     * @return static
     */
    public function setPageNum($pageNum)
    {
        $this->arNavStartParams['iNumPage'] = (int)$pageNum;
        return $this;
    }

    /**
     * @param $count int
     * @return static
     */
    public function setTopCount($count)
    {
        $this->arNavStartParams['nTopCount'] = (int)$count;
        return $this;
    }

    /**
     * @param $reason bool
     * @return static
     */
    public function setCheckoutOfRange($reason)
    {
        $this->arNavStartParams['checkOutOfRange'] = (bool)$reason;
        return $this;
    }

    /**
     * @param $arSelectFields array
     * @return static
     */
    public function setSelectFields($arSelectFields)
    {
        $this->arSelectFields = $arSelectFields;
        return $this;
    }

    public function get() {}


    /**
     * @return \IDA\Classes\Base\Entity|\IDA\Classes\Block\Entity|\IDA\Classes\Section\Entity
     */
    public function getOne()
    {
        $this->setTopCount(1);
        $retval = $this->get();
        return empty($retval) ? false : $retval[0];
    }

    /**
     * @return \IDA\Classes\Base\Entity|\IDA\Classes\Block\Entity|\IDA\Classes\Section\Entity
     */
    public function getById($id)
    {
        return $this->setHydrationMode(self::HYDRATION_MODE_OBJECTS_ARRAY)->addFilter('ID', $id)->getOne();
    }

    public function getArrayById($id)
    {
        return $this->setHydrationMode(self::HYDRATION_MODE_ARRAY)->addFilter('ID', $id)->getOne();
    }

    /**
     * @return \IDA\Classes\Base\Entity|\IDA\Classes\Block\Entity|\IDA\Classes\Section\Entity
     */
    public function getByCode($code, $iblockId = null)
    {
        $this->setHydrationMode(self::HYDRATION_MODE_OBJECTS_ARRAY)->addFilter('=CODE', $code);

        if ($iblockId)
        {
            $this->addFilter('IBLOCK_ID', $iblockId);
        }

        return $this->getOne();
    }

    public function getCacheId()
    {
        return empty($this->cacheId) ? md5(serialize(get_object_vars($this))) : $this->cacheId;
    }

    protected function cacheResult($result)
    {
        if (!$this->cacheManager) return false;
        $this->cacheManager->save($this->getCacheId(), $result);
    }

    protected function getCachedResult()
    {
        $retval = false;

        try
        {
            if (!$this->cacheManager)
            {
                throw new \Exception;
            }

            if (!$this->cacheManager->valid($this->getCacheId()))
            {
                throw new \Exception;
            }

            $retval = $this->cacheManager->get($this->getCacheId());
        }
        catch (\Exception $e)
        {

        }

        return $retval;
    }
}