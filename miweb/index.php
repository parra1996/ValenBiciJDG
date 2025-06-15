<?php
$lang = $_GET['lang'] ?? 'es';  

$texts = [
    'es' => [
        'title' => 'Disponibilidad de ValenBisi ',
        'direccion' => 'Dirección',
        'numero' => 'Número',
        'abierto' => 'Abierto',
        'disponibles' => 'Disponibles',
        'libres' => 'Libres',
        'total' => 'Total',
        'actualizado' => 'Actualizado',
        'coordenadas' => 'Coordenadas',
        'si' => 'Sí',
        'no' => 'No',
        'ver_mapa' => 'Ver mapa de estaciones',
        'cambiar_idioma' => 'Cambiar a Inglés'
    ],
    'en' => [
        'title' => 'Availability of ValenBisi - parrita',
        'direccion' => 'Address',
        'numero' => 'Number',
        'abierto' => 'Open',
        'disponibles' => 'Available',
        'libres' => 'Free',
        'total' => 'Total',
        'actualizado' => 'Updated',
        'coordenadas' => 'Coordinates',
        'si' => 'Yes',
        'no' => 'No',
        'ver_mapa' => 'View station map',
        'cambiar_idioma' => 'Cambiar a Español'
    ]
];

$t = $texts[$lang];
$cambiarLang = $lang === 'es' ? 'en' : 'es';
?>

<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
 <meta charset="UTF-8">
 <meta name="viewport" content="width=device-width, initial-scale=1.0">
 <title><?= $t['title'] ?></title>
 <style>
 body {
 font-family: Arial, sans-serif;
 margin: 20px;
 background-color: #f9f9f9;
 }
 h1 {
 text-align: center;
 color: #333;
 }
 table {
 width: 80%;
 margin: 0 auto;
 border-collapse: collapse;
 background-color: #fff;
 }
 th, td {
 border: 1px solid #ddd;
 padding: 10px;
 text-align: center;
 }
 th {
 background-color: #4CAF50;
 color: white;
 }
 tr:nth-child(even) {
    background-color: #f2f2f2;
 }
 tr:hover {
 background-color: #ddd;
 }
 .green-button {
   display: block;
   width: 100%;
   padding: 12px;
   margin-top: 20px;
   background-color: #4CAF50;
   color: white;
   border: none;
   font-size: 16px;
   cursor: pointer;
 }
 .green-button:hover {
   background-color: #45a049;
 }
 .lang-button {
   margin: 10px auto;
   display: block;
   background-color: #45a049;
   color: white;
   border: none;
   padding: 10px 20px;
   font-size: 14px;
   cursor: pointer;
   border-radius: 5px;
 }
 .lang-button:hover {
   background-color: rgb(54, 115, 57);
 }
 </style>
</head>
<body>

<h1><?= $t['title'] ?></h1>
<form method="get" style="text-align: center;">
  <input type="hidden" name="lang" value="<?= $cambiarLang ?>">
  <button class="lang-button" type="submit"><?= $t['cambiar_idioma'] ?></button>
</form>

<?php


$baseUrl = "https://valencia.opendatasoft.com/api/explore/v2.1/catalog/datasets/valenbisi-disponibilitat-valenbisi-dsiponibilidad/records?";
$limit = 20;
$offset = 0;
$allStations = [];
$errorOccurred = false;
do {
    $url = $baseUrl . "limit=" . $limit . "&offset=" . $offset;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Accept: application/json"]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    if ($response === false) {
        echo "<p style='color: red; text-align: center;'>Error en cURL: " . curl_error($ch) . "</p>";
        $errorOccurred = true;
        break;
    }
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($httpCode != 200) {
        echo "<p style='color: red; text-align: center;'>Error en la solicitud a la API (Código HTTP: " . $httpCode . "). URL: " . $url . "</p>";
        $errorOccurred = true;
        break;
    }
    curl_close($ch);
    $data = json_decode($response, true);
    if ($data === null) {
        echo "<p style='color: red; text-align: center;'>Error al decodificar la respuesta JSON. Response: " . htmlspecialchars($response) . "</p>";
        $errorOccurred = true;
        break;
    }
    if (isset($data["results"]) && is_array($data["results"]) && count($data["results"]) > 0) {
        foreach ($data["results"] as $station) {
            $allStations[$station['number']] = [
                'address' => $station['address'],
                'open' => ($station['open'] == "T"),
                'available' => (int)$station['available'],
                'free' => (int)$station['free'],
                'total' => (int)$station['total'],
                'updated_at' => $station['updated_at'],
                'lat' => $station['geo_point_2d']['lat'],
                'lon' => $station['geo_point_2d']['lon']
            ];
        }
        $offset += $limit;
    } else {
        echo "<p style='color: orange; text-align: center;'>No hay resultados en esta página o el formato de la respuesta es incorrecto.</p>";
        var_dump($data);
        break;
    }
} while (isset($data["results"]) && is_array($data["results"]) && count($data["results"]) == $limit);

if (!$errorOccurred && !empty($allStations)) {
    $filePath = getcwd() . '/data.json';
    if(file_put_contents($filePath, json_encode($allStations))){
        echo "<p style='color: green; text-align: center;'>Datos guardados en: " . $filePath . "</p>";
    } else {
        echo "<p style='color: red; text-align: center;'>Error al guardar el archivo data.json. Verifica los permisos de escritura.</p>";
    }
} elseif (!$errorOccurred && empty($allStations)) {
    echo "<p style='color: orange; text-align: center;'>No se encontraron datos de estaciones.</p>";
}

if (!empty($allStations)) {
    echo "<table>";
    echo "<tr><th>{$t['direccion']}</th><th>{$t['numero']}</th><th>{$t['abierto']}</th><th>{$t['disponibles']}</th><th>{$t['libres']}</th><th>{$t['total']}</th><th>{$t['actualizado']}</th><th>{$t['coordenadas']}</th></tr>";
    foreach ($allStations as $number => $station) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($station['address']) . "</td>";
        echo "<td>" . $number . "</td>";
        echo "<td>" . ($station['open'] ? $t['si'] : $t['no']) . "</td>";
        echo "<td>" . $station['available'] . "</td>";
        echo "<td>" . $station['free'] . "</td>";
        echo "<td>" . $station['total'] . "</td>";
        echo "<td>" . $station['updated_at'] . "</td>";
        echo "<td>Lon(" . $station['lon'] . "), Lat(" . $station['lat'] . ")</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "
    <form action='mapearbicis.php' method='get'>
        <input type='hidden' name='lang' value='$lang'>
        <button type='submit' class='green-button'>{$t['ver_mapa']}</button>
    </form>
    ";
}
?>
</body>
</html>
