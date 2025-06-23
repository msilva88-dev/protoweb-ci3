<?php


class PW_Model_News
extends CI_Model
{
    function get_news($slug = FALSE)
    {
        if ($slug === FALSE)
        {
            $query = $this->db->get('news');

            return $query->result_array();
        }

        $query = $this->db->get_where('news', ['slug' => $slug]);

        return $query->row_array();
    }

    function set_news()
    {
        $this->load->helper('url');

        $slug = url_title($this->input->post('title'), 'dash', TRUE);

        $data = [
            'title' => $this->input->post('title'),
            'slug' => $slug,
            'text' => $this->input->post('text')
        ];

        return $this->db->insert('news', $data);
    }

    function __construct()
    {
        $this->load->database();
    }
}
