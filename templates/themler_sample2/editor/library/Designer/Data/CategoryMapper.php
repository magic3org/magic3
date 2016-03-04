<?php

Designer::load('Designer_Data_Mapper');

class Designer_Data_CategoryMapper extends Designer_Data_Mapper
{
    public function __construct()
    {
        parent::__construct('Category', 'categories', 'id');
    }

    public function find($filter = array())
    {
        $where = array();
        if (isset($filter['extension']))
            $where[] = 'extension = ' . $this->_db->Quote($filter['extension']);
        if (isset($filter['title']))
            $where[] = 'title = ' . $this->_db->Quote($filter['title']);

        $result = $this->_loadObjects($where, isset($filter['limit']) ? (int)$filter['limit'] : 0, true);
        return $result;
    }

    public function create()
    {
        $row = $this->_create();
        $row->setLocation(1, 'last-child'); 
        $row->published = 1;
        $row->params = '{"category_layout":"","image":""}';
        $row->metadata = '{"author":"","robots":""}';
        $row->language = '*';
        return $row;
    }

    public function delete($id)
    {
        $status = $this->_cascadeDelete('content', array('category' => $id));
        if (is_string($status))
            return $this->_error($status, 1);
        return parent::delete($id);
    }
    
    public function save($category)
    {
        $status = parent::save($category);
        if (is_string($status))
            return $this->_error($status, 1);
        if (!$category->rebuildPath($category->id))
            return $this->_error($category->getError(), 1);
        if (!$category->rebuild($category->id, $category->lft, $category->level, $category->path))
            return $this->_error($category->getError(), 1);
        return null;
    }
}