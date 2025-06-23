<!--
Copyright (c) 2024-2025, M&aacute;rcio Delgado <marcio@libreware.info>
All rights reserved.

Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions
are met:

1. Redistributions of source code must retain the above copyright
   notice, this list of conditions and the following disclaimer.
2. Redistributions in binary form must reproduce the above copyright
   notice, this list of conditions and the following disclaimer in
   the documentation and/or other materials provided with the
   distribution.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
"AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT,
INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION)
HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT,
STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED
OF THE POSSIBILITY OF SUCH DAMAGE.
-->
<?php
$RGT_COPY = 'Copyright (c) 2024-2025, M&aacute;rcio Delgado';
$URL_COPY = 'https://opensource.org/licenses/BSD-2-Clause';
$INT_COPY = base64_encode(hash_file('sha384', $URL_COPY, true));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link crossorigin="anonymous"
          href="<?= $url['css'] ?>"
          integrity="sha384-<?= $sha2_384['css'] ?>"
          rel="stylesheet">
    <link crossorigin="anonymous"
          href="<?= $URL_COPY ?>"
          integrity="sha384-<?= $INT_COPY ?>"
          rel="license">
    <meta charset="UTF-8">
    <meta content="Chat Prototype" name="DC.title">
    <meta content="Copyright (c) 2024-2025, M&aacute;rcio Delgado"
          name="DC.rights">
    <meta content="M&aacute;rcio Delgado" name="DC.creator">
    <meta content="2024-2025" name="DC.date">
    <meta content="https://libreware.info" name="DC.identifier">
    <meta content="<?= $URL_COPY ?>"
          name="DC.license">
    <title>Dynamic Chat<?= $html_txt_env ?></title>
    <script type="application/rdf+xml">
        <rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
                 xmlns:dc="http://purl.org/dc/elements/1.1/"
                 xmlns:cc="http://creativecommons.org/ns#">
            <cc:Work rdf:about="">
                <dc:title>Prototype Chat</dc:title>
                <dc:creator>
                    <cc:Agent>
                        <dc:title>M&aacute;rcio Delgado</dc:title>
                    </cc:Agent>
                </dc:creator>
                <dc:rights><?= $RGT_COPY ?></dc:rights>
            <dc:license rdf:resource="<?= $URL_COPY ?>"/>
        </cc:Work>
    </rdf:RDF>
    </script>
</head>
<body>
    <h2>Chat Room<?= $html_txt_env ?></h2>
    <div id="chat-box-div"></div>
    <button id="load-more-button">Load More</button>
    <form action="<?= site_url('chat/submit_message') ?>"
          id="chat-form" method="POST" >
        <input id="sender-id-input" type="number" value="1">
        <input id="receiver-id-input" type="number" value="2">
        <input id="booking-no-input" type="hidden" value="FOO123456">
        <input id="product-id-input" type="hidden" value="1">
        <input id="subject-input" type="hidden"
               value="Booking Request : FOO123456">
        <input id="page-url-input" type="hidden"
               value="<?= site_url('chat') ?>">
        <input id="base-url-input" type="hidden" value="<?= site_url('') ?>">
        <input cols="40" id="message-input" maxlength="250" name="message"
               placeholder="Type a personal message here..." required
               rows="10" type="text">
        <button class="summit-button1" name="submit"
                type="submit">Send Message</button>
    </form>
    <script crossorigin="anonymous" defer
            integrity="sha384-<?= $sha2_384['jslog'] ?>"
            src="<?= $url['jslog'] ?>"></script>
    <script crossorigin="anonymous"
            data-dynjson-api-url="<?= site_url('chat') ?>"
            data-ws-url="<?= $url['ws'] ?>" defer
            integrity="sha384-<?= $sha2_384['js'] ?>"
            src="<?= $url['js'] ?>"></script>
</body>
</html>
