<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Create By : Aryo
 * Youtube : Aryo Coding
 */
class Mod_ruang extends CI_Model
{
	var $table = 'ruang';
	var $column_search = array('nama_ruang'); 
	var $column_order = array('nama_ruang');
	var $order = array('id_ruang' => 'desc'); 
	function __construct()
	{
		parent::__construct();
		$this->load->database();
	}

		private function _get_datatables_query()
	{
		
		$this->db->from('ruang');
		$i = 0;

	foreach ($this->column_search as $item) // loop column 
	{
	if($_POST['search']['value']) // if datatable send POST for search
	{

	if($i===0) // first loop
	{
	$this->db->group_start(); // open bracket. query Where with OR clause better with bracket. because maybe can combine with other WHERE with AND.
	$this->db->like($item, $_POST['search']['value']);
	}
	else
	{
		$this->db->or_like($item, $_POST['search']['value']);
	}

		if(count($this->column_search) - 1 == $i) //last loop
		$this->db->group_end(); //close bracket
	}
	$i++;
	}

		if(isset($_POST['order'])) // here order processing
		{
			$this->db->order_by($this->column_order[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
		} 
		else if(isset($this->order))
		{
			$order = $this->order;
			$this->db->order_by(key($order), $order[key($order)]);
		}
	}

	function get_datatables()
	{
		$this->_get_datatables_query();
		if($_POST['length'] != -1)
			$this->db->limit($_POST['length'], $_POST['start']);
		$query = $this->db->get();
		return $query->result();
	}

	function count_filtered()
	{
		$this->_get_datatables_query();
		$query = $this->db->get()->result();
		return count($query);
	}

	function count_all()
	{

		$this->db->from('ruang');
		return $this->db->count_all_results();
	}

	function insert($table, $data)
    {
        $insert = $this->db->insert($table, $data);
        return $insert;
    }

        function update($id, $data)
    {
        $this->db->where('id_ruang', $id);
        $this->db->update('ruang', $data);
    }

        function get($id)
    {   
        $this->db->where('id_ruang',$id);
        return $this->db->get('ruang')->row();
    }

        function delete($id, $table)
    {
        $this->db->where('id_ruang', $id);
        $this->db->delete($table);
    }

 
}