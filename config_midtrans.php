<?php
// config_midtrans.php

// Midtrans Sandbox Credentials (Default Placeholders)
$midtrans_server_key = 'PLACEHOLDER_SERVER_KEY'; // Ganti di config_keys.php
$midtrans_client_key = 'PLACEHOLDER_CLIENT_KEY'; // Ganti di config_keys.php
$midtrans_is_production = false;

// Muat kredensial lokal dari file config_keys.php (yang sudah di-gitignore) jika ada
if (file_exists(__DIR__ . '/config_keys.php')) {
    include __DIR__ . '/config_keys.php';
}

/**
 * Fungsi untuk meminta Snap Token dari API Midtrans Snap
 * 
 * @param string $order_id ID Transaksi unik
 * @param int $gross_amount Total nominal pembayaran
 * @param array $customer_details Data diri peminjam
 * @return string|null Token Snap jika berhasil, null jika gagal
 */
function getSnapToken($order_id, $gross_amount, $customer_details) {
    global $midtrans_server_key, $midtrans_is_production;
    
    $base_url = $midtrans_is_production 
        ? 'https://app.midtrans.com/snap/v1/transactions' 
        : 'https://app.sandbox.midtrans.com/snap/v1/transactions';
    
    $payload = array(
        'transaction_details' => array(
            'order_id' => $order_id,
            'gross_amount' => (int)$gross_amount,
        ),
        'customer_details' => array(
            'first_name' => $customer_details['nama'],
            'phone' => $customer_details['no_hp'],
            'billing_address' => array(
                'first_name' => $customer_details['nama'],
                'phone' => $customer_details['no_hp'],
                'address' => 'NIK: ' . $customer_details['nik']
            )
        ),
        'credit_card' => array(
            'secure' => true
        )
    );
    
    $json_payload = json_encode($payload);
    
    $ch = curl_init($base_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json_payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Accept: application/json',
        'Authorization: Basic ' . base64_encode($midtrans_server_key . ':')
    ));
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code == 200 || $http_code == 201) {
        $result = json_decode($response, true);
        return isset($result['token']) ? $result['token'] : null;
    }
    
    return null;
}
?>
