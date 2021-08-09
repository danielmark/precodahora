<?php

$baseJSONParams = ["gtin","horas","latitude","longitude","raio","pagina","ordenar"];
@$paramsJSON = json_decode(file_get_contents('php://input'), true);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && is_array($paramsJSON)) {

    foreach ($baseJSONParams as $params) {
        if (!array_key_exists($params, $paramsJSON)) {
            http_response_code(400);
            echo "Parametro:" . $params . " não declarado.";
            exit;
        }
    }

    $baseUrl = 'https://precodahora.ba.gov.br/produtos/';

    $ch = curl_init($baseUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 1);
    $result = curl_exec($ch);
    curl_close($ch);

    //Get data-id
    $doc = new DOMDocument();
    @$doc->loadHTML($result);
    $nodes = $doc->getElementsByTagName('title');
    $title = $nodes->item(0)->nodeValue;
    $metas = $doc->getElementsByTagName('meta');

    $csrfToken = null;
    for ($i = 0; $i < $metas->length; $i++) {
        $meta = $metas->item($i);
        if ($meta->getAttribute('id') == 'validate')
            $csrfToken = $meta->getAttribute('data-id');
    }

    preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $result, $matches);
    $cookies = array();
    foreach ($matches[1] as $item) {
        parse_str($item, $cookie);
        $cookies = array_merge($cookies, $cookie);
    }

    //Get data result

    //Data to request
    $data = array(
        'gtin' => '7896006755517',
        'horas' => 72,
        'latitude' => '-12.97111',
        'longitude' => '-38.51083',
        'raio' => 15,
        'pagina' => 1,
        'ordenar' => "preco.asc"
    );

    $vars = http_build_query($data) . "\n";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $vars);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    //Set header to request
    $headers = [
        'Accept: */*',
        'Accept-Language: pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7',
        'Connection: keep-alive',
        'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
        'Origin: ' . $baseUrl,
        'Referer: ' . $baseUrl,
        'Sec-Fetch-Dest: empty',
        'Sec-Fetch-Mode: cors',
        'Sec-Fetch-Site: same-origin',
        'Sec-Fetch-Site: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/92.0.4515.131 Safari/537.36',
        'X-CSRFToken: ' . $csrfToken,
        'X-Requested-With: XMLHttpRequest',
        'Cookie: ' . 'session=' . $cookies['session'] . ';token=' . $cookies['token']
    ];
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $server_output = curl_exec($ch);
    curl_close($ch);

    header('Content-Type: application/json');
    print  $server_output;
}else{
    http_response_code(400);
    echo "Requisicao invalida.";
}