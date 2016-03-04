<?php

/**
 * Base data mapper class.
 */
class Designer_Data_Mapper
{
    protected $_db;
    protected $_entity;
    protected $_table;
    protected $_pk;

    public function __construct($entity, $table, $pk)
    {
        $this->_entity = $entity;
        $this->_table = $table;
        $this->_pk = $pk;
        $this->_db = JFactory::getDBO();
    }

    public function exists($filter = array())
    {
        $row = $this->findOne($filter);
        if (is_string($row))
            return $this->_error($list, 1);
        return !is_null($row);
    }

    public function findOne($filter = array())
    {
        $filter['limit'] = 1;
        $list = $this->find($filter);
        if (is_string($list))
            return $this->_error($list, 1);
        if (0 == count($list)) {
            $null = null;
            return $null;
        }
        return $list[0];
    }

    public function find($filter = array())
    {
        $result = $this->_loadObjects();
        return $result;
    }


    public function fetch($id)
    {
        $row = JTable::getInstance($this->_entity);
        $row->load($id);
        return $row;
    }

    public function delete($id)
    {
        $row = $this->fetch($id);
        if (!$row->delete($id))
            return $this->_error($row->getError(), 1);
        return null;
    }

    public function save($row)
    {
        if (!$row->check())
            return $this->_error($row->getError(), 1);
        if (!$row->store())
            return $this->_error($row->getError(), 1);
        if (!$row->checkin())
            return $this->_error($row->getError(), 1);
        return null;
    }
    
    protected function _create()
    {
        $result = JTable::getInstance($this->_entity);
        return $result;
    }

    protected function _loadObjects($where = array(), $limit = 0)
    {
        $query = 'SELECT * FROM #__' . $this->_table
            . (count($where) ? ' WHERE ' . implode(' AND ', $where) : '')
            . ' ORDER BY ' . $this->_pk;
        $this->_db->setQuery($query, 0, $limit);
        $rows = $this->_db->loadAssocList();
        if ($this->_db->getErrorNum())
            return $this->_error($this->_db->stderr(), 1);
        $result = array();
        for ($i = 0; $i < count($rows); $i++) {
            $result[$i] = JTable::getInstance($this->_entity);
            $result[$i]->bind($rows[$i]);
        }
        return $result;
    }

    protected function _cascadeDelete($mapper, $filter)
    {
        $menuItems = Designer_Data_Mappers::get($mapper);
        $itemsList = $menuItems->find($filter);
        if (is_string($itemsList))
            return $this->_error($itemsList, 1);
        foreach ($itemsList as $item) {
            $status = $menuItems->delete($item->id);
            if (is_string($status))
                return $this->_error($status, 1);
        }
        return null;
    }

    protected function _error($error, $code)
    {
        Designer_Data_Mappers::error($error, $code);
    }
}