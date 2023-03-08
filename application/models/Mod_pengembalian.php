<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Create By : Aryo
 * Youtube : Aryo Coding
 */
class Mod_pengembalian extends CI_Model
{
	var $table = 'pengembalian';
	var $column_search = array('a.nama','b.nama_alat','c.nama_satuan','d.nama_jabatan','e.kondisi');
	var $column_order = array('a.nama','b.nama_alat','c.nama_satuan','d.nama_jabatan','e.kondisi');
	var $order = array('id_pengembalian' => 'desc'); 
	function __construct()
	{
		parent::__construct();
		$this->load->database();
	}

		private function _get_datatables_query()
	{
		$level = $this->session->userdata['id_level'];
		$id_jurusan = $this->session->userdata['id_jurusan'];
		if ($level=='6' || $level=='9') {
			$this->db->where('a.id_jurusan',$id_jurusan);
		}
		$this->db->select('a.*,b.nama_alat,c.nama_satuan,d.nama_jabatan,e.kondisi');
		$this->db->join('alat b','a.id_alat=b.id_alat', 'left');
		$this->db->join('satuan c','a.id_satuan=c.id', 'left');
		$this->db->join('jabatan d', 'a.id_jabatan=d.id_jabatan', 'left');
		$this->db->join('kondisi e', 'a.id_kondisi=e.id_kondisi','left');
		$this->db->from('pengembalian a');
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
		$level = $this->session->userdata['id_level'];
		$id_jurusan = $this->session->userdata['id_jurusan'];
		if ($level=='6') {
			$this->db->where('a.id_jurusan',$id_jurusan);
		}
		$this->db->join('alat b','a.id_alat=b.id_alat', 'left');
		$this->db->join('satuan c','a.id_satuan=c.id', 'left');
		$this->db->join('jabatan d', 'a.id_jabatan=d.id_jabatan', 'left');
		$this->db->from('pengembalian a');
		return $this->db->count_all_results();
	}

	function insert($table, $data)
    {
        $insert = $this->db->insert($table, $data);
        return $insert;
    }

        function update($id, $data)
    {
        $this->db->where('id_pengembalian', $id);
        $this->db->update('pengembalian', $data);
    }

  function update_kerusakan($id, $data)
    {
        $this->db->where('id_pengembalian', $id);
        $this->db->update('kerusakan_alat', $data);
    }
      function update_perbaikan($id, $data)
    {
        $this->db->where('id_pengembalian', $id);
        $this->db->update('perbaikan_alat', $data);
    }

        function get($id)
    {   

        $this->db->where('a.id_pengembalian',$id);
        return $this->db->get('pengembalian a')->row();
    }

        function delete($id, $table)
    {
        $this->db->where('id_pengembalian', $id);
        $this->db->delete($table);
    }

     function delete_perbaikan($id, $table)
    {
        $this->db->where('id_pengembalian', $id);
        $this->db->delete($table);
    }

     function delete_kerusakan($id, $table)
    {
        $this->db->where('id_pengembalian', $id);
        $this->db->delete($table);
    }
       function getImage($id)
    {
        $this->db->select('foto,id_kondisi');
        $this->db->where('id_pengembalian', $id);
        return $this->db->get('pengembalian');
    }

    function cek_nama_peminjam($nama='')
    {
    	$this->db->like('nama', $nama);
        $this->db->where('status', '0');
        return $this->db->get('peminjaman');
    }
 
 	function get_peminjam($id='')
    {
        $this->db->where('id_peminjaman', $id);
        return $this->db->get('peminjaman');
    }
    
     function get_pengembalian($id='')
    {
        $this->db->select('SUM(stok_in) as stok_in');
        $this->db->where('id_peminjaman', $id);
        return $this->db->get('pengembalian');
    }

    function cek_kerusakan_alat($id_pengembalian='')
    {
    	$this->db->where('id_pengembalian', $id_pengembalian);
        return $this->db->get('kerusakan_alat');
    }

    function cek_perbaikan_alat($id_pengembalian='')
    {
    	$this->db->where('id_pengembalian', $id_pengembalian);
        return $this->db->get('perbaikan_alat');
    }
}