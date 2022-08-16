
<?php
class Donation_Model extends CI_Model
{

    public function insert_test_entry($data)
    {
        $this->db->insert('tbltest', $data);
        $insert_id = $this->db->insert_id();

        return  $insert_id;
    }
    function test_list()
    {
        $query = $this->db->get("tbltest");
        return $query;
    }

    public function insert_entry($data)
    {
        $this->db->insert('tbldonation_users', $data);
        $insert_id = $this->db->insert_id();

        return  $insert_id;
    }

    public function insert_donee($data)
    {
        $this->db->insert('tbldonee_users', $data);
        $insert_id = $this->db->insert_id();

        return  $insert_id;
    }

    public function insert_multiple_file($data)
    {
        $this->db->insert_batch('tbldonation_files', $data);
        $insert_id = $this->db->insert_id();

        return  $insert_id;
    }

    function donation_files($user_id, $isVideo)
    {
        $this->db->where('user_id', $user_id);
        if ($isVideo == '1') {
            $this->db->where('is_video', 1);
        }

        $query = $this->db->get("tbldonation_files");
        return $query;
    }

    public function record_donate_receive($data)
    {
        $this->db->insert('tbldonate_receive', $data);
        $insert_id = $this->db->insert_id();

        return  $insert_id;
    }


    public function record_donee_donor($data)
    {
        $this->db->insert('tbldonee_donor', $data);
        $insert_id = $this->db->insert_id();

        return  $insert_id;
    }


    public function update_entry($data, $id)
    {
        $this->db->where('user_id', $id);

        return  $this->db->update('tbldonation_users', $data);
    }

    public function delete_entry($id)
    {
        $this->db->where('id', $id);

        return  $this->db->delete('tbldonation_users');
    }

    function donation_users($userType)
    {
        $this->db->where('user_type', $userType);
        $query = $this->db->get("tbldonation_users");
        return $query;
    }


    function dm_last_id()
    {
        $this->db->where('user_type', 'DM');
        $query = $this->db->get("tbldonation_users");
        $row = $query->last_row();
        return $row->id;
    }
    function donation_all_users()
    {
        $query = $this->db->get("tbldonation_users");
        return $query;
    }
    function user_data($id)
    {
        $this->db->where('id', $id);
        $query = $this->db->get("tbldonation_users");
        return $query->row();
    }

    function donee_donor_data($doneeID, $donorID)
    {
        $this->db->where('donee_id', $doneeID);
        $this->db->where('donor_id', $donorID);
        $query = $this->db->get("tbldonee_donor");
        return $query->row();
    }

    function user_donee($id)
    {
        $this->db->where('id', $id);
        $query = $this->db->get("tbldonee_users");
        return $query->row();
    }

    function donee_list()
    {
        $query = $this->db->get("tbldonee_users");
        return $query;
    }

    function donate_receive_list($id)
    {
        $this->db->where('donee_id', $id);
        $this->db->order_by('receive_date', "DESC");
        $query = $this->db->get("tbldonate_receive");
        return $query;
    }

    function donate_paid_list($id)
    {
        $this->db->where('donor_id', $id);
        $this->db->order_by('receive_date', "DESC");
        $query = $this->db->get("tbldonate_receive");
        return $query;
    }


    function donor_donate_data($id)
    {
        $this->db->where('donor_id', $id);
        $this->db->order_by('receive_date', "DESC");
        $query = $this->db->get("tbldonate_receive");
        return $query;
    }

    function donee_donor_list($id)
    {
        $this->db->where('donee_id', $id);
        $query = $this->db->get("tbldonee_donor");
        return $query;
    }


    function donor_donee_list($id)
    {
        $this->db->where('donor_id', $id);
        $query = $this->db->get("tbldonee_donor");
        return $query;
    }

    function user_data2($id)
    {
        $this->db->where('user_id', $id);
        $query = $this->db->get("tbldonation_users");
        return $query->row();
    }
    function user_data_email($email)
    {
        $this->db->where('email', $email);
        $query = $this->db->get("tbldonation_users");
        return $query->row();
    }
    function user_login($email, $password, $user_type)
    {
        $this->db->where('email', $email);
        $this->db->where('password', $password);
        $this->db->where('user_type', $user_type);
        $query = $this->db->get("tbldonation_users");
        return $query->row();
    }
}
