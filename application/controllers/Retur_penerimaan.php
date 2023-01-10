<?php
defined('BASEPATH') OR exit('No direct script access allowed');
date_default_timezone_set('Asia/Jakarta');
/**
 * Create By : Aryo
 * Youtube : Aryo Coding
 */
class Retur_penerimaan extends MY_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->load->model(array('Mod_retur_penerimaan','Mod_masuk'));
        // $this->load->model('dashboard/Mod_dashboard');
    }

    public function index()
    {
        $link = $this->uri->segment(1);
        $level = $this->session->userdata['id_level'];
        // Cek Posisi Menu apakah Sub Menu Atau bukan
        $jml = $this->Mod_dashboard->get_akses_menu($link,$level)->num_rows();

        if ($jml > 0) {//Jika Menu
            $data['akses_menu'] = $this->Mod_dashboard->get_akses_menu($link,$level)->row();
            $a_menu = $this->Mod_dashboard->get_akses_menu($link,$level)->row();
            $akses=$a_menu->view;
        }else{
            $data['akses_menu'] = $this->Mod_dashboard->get_akses_submenu($link,$level)->row();
            $a_submenu = $this->Mod_dashboard->get_akses_submenu($link,$level)->row();
            $akses=$a_submenu->view;
        }
        


        if ($akses=="Y") {
            $this->hapus_all_cart();
            $this->template->load('layoutbackend','retur/retur_penerimaan',$data);
        }else{
            $data['page']=$link;
            $this->template->load('layoutbackend','admin/akses_ditolak',$data);
        }


    }

    public function ajax_list()
    {
        ini_set('memory_limit','512M');
        set_time_limit(3600);
        $list = $this->Mod_retur_penerimaan->get_datatables();
        $data = array();
        $no = $_POST['start'];
        foreach ($list as $pel) {

            $no++;
            $row = array();
            // $row[] = $no;

            $row[] = $pel->tanggal;
            $row[] = $pel->nama_supplier;
            $row[] = $pel->nama_barang;
            $row[] = $pel->nama_satuan;
            $row[] = $pel->ed;
            $row[] = $pel->jumlah;
            // <a class=\"btn btn-xs btn-outline-info \" href=\"javascript:void(0)\" title=\"Print\" onclick=\"cetak('$pel->id')\"><i class=\"fas fa-print\"></i></a>
            $row[] = "</i></a>  <a class=\"btn btn-xs btn-outline-primary edit\" href=\"javascript:void(0)\" title=\"Edit\" onclick=\"edit('$pel->id')\"><i class=\"fas fa-edit\"></i></a>  <a class=\"btn btn-xs btn-outline-danger delete\" href=\"javascript:void(0)\" title=\"Delete\"  onclick=\"hapus('$pel->id')\"><i class=\"fas fa-trash\"></i></a>  ";
            $data[] = $row;
        }

        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->Mod_retur_penerimaan->count_all(),
            "recordsFiltered" => $this->Mod_retur_penerimaan->count_filtered(),
            "data" => $data,
        );
        //output to json format
        echo json_encode($output);
    }

    public function insert()
    {
        $this->_validate();
        $list = $this->Mod_retur_penerimaan->get_detail(0);
        if (count($list) == 0) {
         echo json_encode(array("status" => FALSE, 'pesan' => 0));

         exit();
     }
     $waktu = date("H:i:s");
     $tanggal=$this->input->post('tanggal');
     $id_gudang = $this->session->userdata['id_gudang'];
     $id_user = $this->session->userdata['id_user'];
     $save  = array(
        'tanggal'         => $tanggal,
        'id_supplier'         => $this->input->post('supplier'),
        'id_penerimaan'         => $this->input->post('id_penerimaan'),
        'user_input'  => $id_user,
        'id_gudang'   =>  $id_gudang
    );
     $this->Mod_retur_penerimaan->insert("retur_penerimaan", $save);
     $id_retur_penerimaan = $this->db->insert_id();




     foreach ($list as $items) {
        $save_detail = array('id_retur_penerimaan' => $id_retur_penerimaan);
        $id_detail=$items->id;
        $this->Mod_retur_penerimaan->update_detail($id_detail, $save_detail);

        $save_stok  = array(
            'id_transaksi'         => $items->id,
            'id_barang'         => $items->id_barang,
            'tanggal'         => $tanggal,
            'transaksi'         => 'Retur Penerimaan',
            'keluar'         => $items->jumlah,
            'ed'         => $items->ed,
            'nobatch'         => $items->nobatch,
            'user_input'  => $id_user,
            'id_gudang'   =>  $id_gudang
        );
        $this->Mod_retur_penerimaan->insert("stok_opname", $save_stok);
    }

    echo json_encode(array("status" => TRUE));



}



public function update()
{
 $id_gudang = $this->session->userdata['id_gudang'];
 $id_user = $this->session->userdata['id_user'];
 $this->_validate();

 $id      = $this->input->post('id');
 $waktu = date("H:i:s");
 $tanggal=$this->input->post('tanggal');
 $save  = array(
    'tanggal'         => $tanggal,
    'id_supplier'         => $this->input->post('supplier')
);

 $this->Mod_retur_penerimaan->update($id, $save);
 $list = $this->Mod_retur_penerimaan->get_detail($id);
        /*if (count($list)==0) {
            $list = $this->Mod_retur_penerimaan->get_detail(0);
        }*/
        foreach ($list as $items) {
            $id_retur_penerimaan = $items->id_retur_penerimaan;
            $id_detail = $items->id;
            $jumlah = $items->jumlah;

            $save_detail = array('id_retur_penerimaan' => $id);
            $this->Mod_retur_penerimaan->update_detail($id_detail, $save_detail);

            $cek=$this->Mod_retur_penerimaan->get_stok($id_detail);
            if (count($cek) == 0) {
                $save_stok  = array(
                    'id_transaksi'         => $items->id,
                    'id_barang'         => $items->id_barang,
                    'tanggal'         => $tanggal,
                    'transaksi'         => 'Retur Penerimaan',
                    'keluar'         => $items->jumlah,
                    'ed'         => $items->ed,
                    'nobatch'         => $items->nobatch,
                    'user_input'  => $id_user,
                    'id_gudang'   =>  $id_gudang
                );
                $this->Mod_retur_penerimaan->insert("stok_opname", $save_stok);
            }else{
               $save_stok  = array(
                'keluar'         => $jumlah,
                'ed'         => $items->ed,
            );
               $this->Mod_retur_penerimaan->update_stok_opname($id_detail, $save_stok);
           }


       }

       echo json_encode(array("status" => TRUE));


   }

   public function edit($id)
   {
    $data = $this->Mod_retur_penerimaan->get($id);
    echo json_encode($data);
}

public function get_brg()
{

   $id = $this->input->get('term');
   $data = $this->Mod_retur_penerimaan->get_brg($id);
   if (count($data) > 0) {
    foreach ($data as $row) {
        $arr_result[] = array( 'label'  => $row->nama, 'produk_nama'  => $row->nama, 'produk_id' => $row->id, 'produk_harga' =>  $row->harga, 'id_kemasan' => $row->kemasan, 'nama_satuan' => $row->nama_satuan);
    }
    echo json_encode($arr_result);
}else{
    $arr_result = array( 'produk_nama'  => "Data Tidak di Temukan" );
    echo json_encode($arr_result);
}

}

public function get_supplier()
{
    $id = $this->input->get('term');
    $data = $this->Mod_retur_penerimaan->get_supplier($id);
    if (count($data) > 0) {

        foreach ($data as $row){
            $arr_result[] = array( 'value' => $row->id, 'label'  => $row->nama,  );
        } 
        echo json_encode($arr_result);
    }else{
        $arr_result = array( 'label'  => "Data Tidak di Temukan" );
        echo json_encode($arr_result);
    }
}

public function getAllSupplier()
{
   $data = $this->Mod_retur_penerimaan->get_supplier_all();
   echo json_encode($data);
}
public function delete()
{
    $id = $this->input->post('id');
    $list = $this->Mod_retur_penerimaan->get_detail($id);
    foreach ($list as $items) {

        $id_detail = $items->id;
        $this->Mod_retur_penerimaan->del_stok($id_detail, "stok_opname");

    }
    $this->Mod_retur_penerimaan->delete($id, 'retur_penerimaan'); 
    $this->Mod_retur_penerimaan->delete_detail($id, 'retur_penerimaan_detail');        
    echo json_encode(array("status" => TRUE));
}
private function _validate()
{
    $data = array();
    $data['error_string'] = array();
    $data['inputerror'] = array();
    $data['status'] = TRUE;

    if($this->input->post('vsup') == '')
    {
        $data['inputerror'][] = 'vsup';
        $data['error_string'][] = 'Supplier Tidak Boleh Kosong';
        $data['status'] = FALSE;
    }


    if($data['status'] === FALSE)
    {
        echo json_encode($data);
        exit();
    }
}



        function edit_to_cart(){ //fungsi Edit To Cart
            $id = $this->input->post('id');
        $this->load_cart($id); //tampilkan cart setelah added
    }

    function add_to_cart(){ //fungsi Add To Cart
       $id_user = $this->session->userdata['id_user'];
       $id_retur_penerimaan = $this->input->post('id');
       $id_penerimaan = $this->input->post('id_penerimaan');
       $list = $this->Mod_masuk->get_detail($id_penerimaan);
       $jml=0;
       foreach ($list as $items) {
        $id_barang = $items->id_barang;
        $nobatch = $items->nobatch;
        $s=$this->Mod_retur_penerimaan->get_sisa_stok($id_barang,$nobatch)->row();
        if ($s->sisa > 0) {
         $save_detail  = array(
            'id_barang'         => $items->id_barang,
            'id_kemasan'         => $items->kemasan,
            'jumlah'         => $s->sisa,
            'ed'         => $items->ed,
            'nobatch'         => $items->nobatch,
            'id_retur_penerimaan' => $id_retur_penerimaan,
            'id_detail_penerimaan' => $items->id,
            'id_user'   => $id_user

        );
         $this->Mod_retur_penerimaan->insert("retur_penerimaan_detail", $save_detail);
         $jml .= '1';
     }else{
        $jml .= '0';
    }
}
    if ($jml > 0 ) {
        $this->load_cart($id_retur_penerimaan);
    }else{
        echo "Sisa Stok Tidak Ada";
    }
}


    function show_cart($id_retur_penerimaan){ //Fungsi untuk menampilkan Cart
        $output = '';
        $no = 0;
        $total = 0;
        $list = $this->Mod_retur_penerimaan->get_detail($id_retur_penerimaan);
        foreach ($list as $items) {
            $id_barang = $items->id_barang;
            $nobatch = $items->nobatch;
            $s=$this->Mod_retur_penerimaan->get_sisa_stok($id_barang,$nobatch)->row();
            $no++;
            $output .='
            <tr>
            <td>'.$no.'</td>
            <td>'.$items->nama_barang.'</td>
            <td>'.$items->nama_satuan.'</td>';
            $output .= '<td><input type="text" size="5" class=" form-control item'.$no.'" onkeypress="return hanyaAngka(event)" value='.$items->jumlah.' sisa='.$s->sisa.' nobatch='.$nobatch.'></td>
            <td>'.$items->ed.'</td>
            <td>
            <button type="button" id_retur_penerimaan="'.$items->id_retur_penerimaan.'" no="'.$no.'"  id_detail="'.$items->id.'" class="hapus_cart btn btn-danger btn-xs">Hapus</button>
            <button type="button" id_retur_penerimaan="'.$items->id_retur_penerimaan.'" id_detail="'.$items->id.'" no="'.$no.'" class="simpan_cart btn btn-success btn-xs">simpan</button>
            </td>

            </tr>
            ';
        }

        return $output;
    }

    function load_cart($id_retur_penerimaan){ //load data cart
        echo $this->show_cart($id_retur_penerimaan);
    }


    function hapus_cart(){ //fungsi untuk menghapus item cart
        $id_retur_penerimaan = $this->input->post('id');
        $id_detail = $this->input->post('id_detail');
        $this->Mod_retur_penerimaan->delete($id_detail,'retur_penerimaan_detail');
        $this->Mod_retur_penerimaan->del_stok($id_detail,'stok_opname');
        $this->load_cart($id_retur_penerimaan);
    }

    function update_cart(){ //fungsi untuk update item cart

        $id_retur_penerimaan = $this->input->post('id');
        $id_detail = $this->input->post('id_detail');
        $save_detail  = array(
            'jumlah'         => $this->input->post('jumlah'),
        );

        $this->Mod_retur_penerimaan->update_detail($id_detail, $save_detail);
        $this->load_cart($id_retur_penerimaan);
    }

      function hapus_all_cart(){ //fungsi untuk menghapus item cart
        $id_retur_penerimaan='0';
        $this->Mod_retur_penerimaan->delete_detail($id_retur_penerimaan, 'retur_penerimaan_detail');
    }

    public function cetak()
    {
        $id = $this->input->post('id');
        $data['tb'] = $this->Mod_retur_penerimaan->get($id);
        $data['lap'] = $this->Mod_retur_penerimaan->get_cetak($id);
        $this->load->view('penerimaan/cetak_penerimaan',$data);

    }

    public function get_faktur()
    {
       $faktur = $this->input->get('term');
       $data = $this->Mod_retur_penerimaan->get_faktur($faktur);
       if (count($data) > 0) {

        foreach ($data as $row){
            $arr_result[] = array( 'value' => $row->id, 'label'  => $row->faktur,'supplier' => $row->id_supplier, 'nama_supplier' => $row->nama_supplier );

        } 
        echo json_encode($arr_result);
    }else{
        $arr_result = array( 'label'  => "Data Tidak di Temukan" );
        echo json_encode($arr_result);
    }
}
}