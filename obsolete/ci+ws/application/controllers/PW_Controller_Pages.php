<?php


class PW_Controller_Pages
extends CI_Controller
{
    function view($page = 'home')
    {
        if (!file_exists(APPPATH . 'views/pages/' . $page . '.php'))
            // Whoops, we don't have a page for that!
            show_404();

        // Capitalize the first letter
        $data['title'] = ucfirst($page);

        // Use DOM Document, not to use load->view nor print SGML directly.
        $dom = new DOMDocument();
        $html_el = $dom->createElement('html');

        $header_el = $dom->createElement('header');
        $title_el = $dom->createElement('title', 'CodeIgniter Tutorial');
        $header_el->appendChild($title_el);

        $body_el = $dom->createElement('body');

        $h1_el0 = $dom->createElement('h1');
        $body_el->appendChild($h1_el0);

        $page_el = $dom->createTextNode(
            $this->load->view('pages/' . $page, $data, TRUE)
        );
        $body_el->appendChild($page_el);

        $em_el0 = $dom->createElement('em', '&copy; 2015');
        $body_el->appendChild($em_el0);

        $html_el->appendChild($header_el);
        $html_el->appendChild($body_el);

        $dom->appendChild($html_el);

        echo $dom->saveHTML();

        //$this->load->view('templates/header', $data, TRUE);
        //$page = $this->load->view('pages/' . $page, $data, TRUE);
        //$this->load->view('templates/footer', $data, TRUE);
    }
}
