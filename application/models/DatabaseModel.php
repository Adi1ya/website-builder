<?php
defined('BASEPATH') or exit('No direct script access allowed');

class DatabaseModel extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function fetchTableData($tableName, $condition = [], $orderField = 'id', $orderBy = 'DESC')
    {
        if (empty($tableName)) {
            return false;
        }

        if (!empty($condition)) {
            $this->db->where($condition);
        }

        $this->db->order_by($orderField, $orderBy);
        $query = $this->db->get($tableName);

        return $query->result();
    }


    public function insertIntoTable($tableName, $insertData)
    {
        if (!empty($tableName) && !empty($insertData)) {
            if ($this->db->insert($tableName, $insertData)) {
                return $this->db->insert_id();
            }
        }
        return false;
    }


    public function updateTableData($tableName, $updateData, $condition)
    {
        if (!empty($tableName) && !empty($updateData) && !empty($condition)) {
            $this->db->where($condition);
            return $this->db->update($tableName, $updateData);
        }

        return false;
    }
}
