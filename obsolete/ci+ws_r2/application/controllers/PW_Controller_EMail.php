<?php


class PW_Controller_EMail
extends CI_Controller
{
    function index()
    {
        $this->load->view('email');
    }

    function send_mail()
    {
        //$from_email = 'marcio_silva@riseup.net';
        //$from_email = 'rentme_sender@riseup.net';
        //$from_email = 'marcio_delgado@murena.io';
        //$from_email = 'pupu.cat.00@gmail.com';
        $from_email = 'rentme.sender@gmail.com';
        //$from_email = 'rentme.sender@aol.com';
        $name_email = 'RentMe Sender';
        //$name_email = 'pupu cat';
        //$name_email = 'Marcio';
        $to_email = $this->input->post('email');

        //Load email library
        $this->load->library('email');

        /*
        $config['useragent'] = 'CodeIgniter'; // opt: String; desc: The "user agent".
        $config['protocol'] = 'mail'; // opt: 'mail', 'sendmail', 'smtp'; desc: The mail sending protocol.
        $config['mailpath'] = '/usr/sbin/sendmail'; // opt: String (path); desc: The server path to Sendmail.
        $config['smtp_host'] = ''; // opt: String (protocol://domain); desc: SMTP Server Address.
        $config['smtp_user'] = ''; // opt: String; desc: SMTP Username.
        $config['smtp_pass'] = ''; // opt: String; desc: SMTP Password.
        $config['smtp_port'] = 25; // opt: Integer; desc: SMTP Port.
        $config['smtp_timeout'] = 5; // opt: Integer; desc: SMTP Timeout (in seconds).
        $config['smtp_keepalive'] = FALSE; // opt: Boolean; desc: Enable persistent SMTP connections.
        $config['smtp_crypto'] = ''; // opt: 'tls', 'ssl'; desc: SMTP Encryption.
        $config['wordwrap'] = TRUE; // opt: Boolean; desc: Enable word-wrap.
        $config['wrapchars'] = 76; // opt: Integer, ?; desc: Character count to wrap at.
        $config['mailtype'] = 'text'; // opt: 'text', 'html';
                                      // desc: Type of mail.
                                      //       If you send HTML email you must send it as a complete web page.
                                      //       Make sure you don't have any relative links
                                      //       or relative image paths otherwise they will not work.
        $config['charset'] = $config['charset']; // opt: ?; desc: Character set (utf-8, iso-8859-1, etc.).
        $config['validate'] = FALSE; // opt: Boolean; desc: Whether to validate the email address.
        $config['priority'] = 3; // opt: 1, 2, 3, 4, 5; desc: Email Priority. 1 = highest. 5 = lowest. 3 = normal.
        $config['crlf'] = '\n'; // opt: '\r\n' (DOS/NT like), '\n' (POSIX/Unix like), '\r' (Amiga/MacOS);
                                // desc: Newline character. (Use '\r\n' to comply with RFC 5322).
        $config['newline'] = '\n'; // opt: '\r\n' (DOS/NT like), '\n' (POSIX/Unix like), '\r' (Amiga/MacOS);
                                   // desc: Newline character. (Use '\r\n' to comply with RFC 5322).
        $config['bcc_batch_mode'] = FALSE; // opt: Boolean; desc: Enable BCC Batch Mode.
        $config['bcc_batch_size'] = 200; // opt: Integer; desc: Number of emails in each BCC batch.
        $config['dsn'] = FALSE; // opt: Boolean; desc: Enable notify message from server.
        */

        $config['useragent'] = 'RentMe';

        // It is not a protocol, is a library method
        // (PHP mail(), sendmail software and smtp directly)
        $config['protocol'] = 'mail';
        //$config['protocol'] = 'smtp';
        //$config['protocol'] = 'sendmail';
        //$config['mailpath'] = '/usr/sbin/sendmail';
        //$config['mailpath'] = '/usr/libexec/sendmail/sendmail';
        //$config['mailpath'] = '/var/lib/mini-sendmail/mini_sendmail';

        //$config['smtp_host'] = 'mail.riseup.net';
        //$config['smtp_user'] = 'marcio_silva';
        //$config['smtp_pass'] = 'Brx48471304';

        //$config['smtp_host'] = 'smtp.gmail.com';
        //$config['smtp_user'] = 'pupu.cat.00';
        //$config['smtp_pass'] = 'Pupu2020';

        $config['smtp_host'] = 'smtp.gmail.com';
        $config['smtp_user'] = 'rentme.sender';
        $config['smtp_pass'] = 'aaql' . 'tugn' . 'pihm' . 'razm';
        //$config['smtp_pass'] = 'goodplacetorent7days';

        // STARTTLS
        $config['smtp_port'] = 587;
        $config['smtp_crypto'] = 'tls';

        // TLS
        //$config['smtp_port'] = 465;
        //$config['smtp_crypto'] = 'ssl';

        //$config['smtp_host'] = 'mail.ecloud.global';
        //$config['smtp_user'] = 'marcio_delgado';
        //$config['smtp_pass'] = 'Brx48471304';

        // STARTTLS
        //$config['smtp_port'] = 587;
        //$config['smtp_crypto'] = 'tls';

        //$config['smtp_keepalive'] = TRUE;
        //$config['smtp_timeout'] = 60*1;

        //$config['crlf'] = '\r\n';
        //$config['newline'] = '\r\n';

        //$config['mailtype'] = 'html';
        //$config['validation'] = TRUE;
        //$config['wordwrap'] = TRUE;
        //$config['wrapchars'] = 76;

        //$config['dsn'] = TRUE;

        $this->email->initialize($config);

        $this->email->from($from_email, $name_email);
        $this->email->to($to_email);
        $this->email->subject('Email Test');
        $this->email->message('Testing the email class.');

        //Send mail
        /*
        if ($this->email->send(FALSE))
            $this->session
              ->set_flashdata('email_sent', 'Email sent successfully.');
        else
            $this->session
              ->set_flashdata('email_sent', 'Error in sending Email.');
        */
        //$this->email->send();
        $this->email->send(FALSE);
        $this->session->set_flashdata(
            'email_sent',
            $this->email->print_debugger(['headers'])
//            . $this->email->print_debugger(['subject'])
//            . $this->email->print_debugger(['body'])
        );

        $this->load->view('email');
    }

    function __construct()
    {
        parent::__construct();
        $this->load->helper('form');
        $this->load->library('session');
    }
}
