<?php
require_once __DIR__ . '/api_handler.php';

$currencies = [
    'bitcoin' => 'Bitcoin',
    'ethereum' => 'Ethereum',
    'dogecoin' => 'Dogecoin',
];

$selectedCurrency = isset($_GET['currency']) ? strtolower(trim($_GET['currency'])) : 'bitcoin';
if (!array_key_exists($selectedCurrency, $currencies)) {
    $selectedCurrency = 'bitcoin';
}

$result = fetchCryptoData($selectedCurrency);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crypto Price Tracker</title>
</head>
<body>
    <center>
    

    <form action="form.php" method="get">
        <h1>Crypto Price Tracker</h1>
        <select name="currency" id="currency" required>
            <?php foreach ($currencies as $value => $label): ?>
                <option value="<?php echo htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $selectedCurrency === $value ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
                </option>
            <?php endforeach; ?>
        </select><br><br>
        <button type="submit">Refresh</button>
    </form>
    

    <?php if (!$result['success']): ?>
        <p><?php echo htmlspecialchars($result['error'], ENT_QUOTES, 'UTF-8'); ?></p>
    <?php else: ?>
        <?php
        $data = $result['data'];
        $price = $data['usd'] ?? null;
        $change = $data['usd_24h_change'] ?? null;
        ?>
        <p><b><?php echo htmlspecialchars(strtoupper($currencies[$selectedCurrency]), ENT_QUOTES, 'UTF-8'); ?></b></p>
        <p><b>Price: </b>$<?php echo $price !== null ? number_format((float) $price, 2) : 'N/A'; ?></p>
        <p><b>24-Hour Change:</b> <?php echo $change !== null ? number_format((float) $change, 2) : 'N/A'; ?>%</p>
    <?php endif; ?>

    </center>
</body>
</html>
