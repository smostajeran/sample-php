<?php
require __DIR__ . '/vendor/autoload.php';

header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Method: POST');
header('Access-Control-Allow-Origin: https://ui.pcon-solutions.com');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0); // Preflight request. Reply successfully:
}


// Fetch the incoming POST request payload
$incomingData = file_get_contents('php://input');
$data = json_decode($incomingData, true);

if ($data && isset($data['obxUrl'])) {
    handlePricing($data);
}

function handlePricing($data) {
    $obxUrl = $data['obxUrl'];

    // Download and parse OBX file
    $obxContent = file_get_contents($obxUrl);
    $xml = simplexml_load_string($obxContent);

    $articles = [];

    // Loop through bskArticle elements for price calculations
    foreach ($xml->xpath('//bskArticle') as $bskArticle) {
        $basketId = (string)$bskArticle['basketId'];
        $originalPrice = (float)$bskArticle->itemPrice['value'];

        // Keep the original price, no discounts applied
        $newPriceEUR = $originalPrice;

        // Convert the price from EUR to AED using the exchange rate of 4.5
        $newPriceAED = $newPriceEUR * 4.5;

        $articles[] = [
            'basketId' => $basketId,
            'salesPrice' => $newPriceAED
        ];
    }

    // Prepare JSON response
    $response = [
        'currency' => 'AED',  // Changed currency to AED
        'articles' => $articles
    ];

    // Respond back to the server with the new pricing
    header('Content-Type: application/json');
    echo json_encode($response);
}

?>
