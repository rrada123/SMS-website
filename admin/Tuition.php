<?php
// Maintenance status
define('MAINTENANCE_MODE', false); // Set to true to simulate maintenance mode

if (MAINTENANCE_MODE) {
    // Redirect to maintenance page
    header("Location: /maintenance.php");
    exit(); // Ensure no further code is executed
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "school_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    http_response_code(503); // Service Unavailable
    echo "Payment services are currently under maintenance. Please try again later.";
    exit();
}

// Retrieve webhook data (typically JSON)
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Debugging: Print raw input and data to log file (for debugging purposes only, remove in production)
file_put_contents('webhook_debug.log', "Raw Input: " . $input . "\nData: " . print_r($data, true) . "\n", FILE_APPEND);

// Validate data
if (is_array($data) && isset($data['payer_id'], $data['payment_amount'], $data['transaction_id'])) {
    $payer_id = $data['payer_id'];
    $payment_amount = $data['payment_amount'];
    $transaction_id = $data['transaction_id'];

    // Check if payment is valid
    if ($payment_amount <= 0 || empty($payer_id) || empty($transaction_id)) {
        http_response_code(400); // Bad Request
        echo ".";
        exit();
    }

    // Check if the transaction ID has already been processed
    $sql = "SELECT COUNT(*) as count FROM payments WHERE transaction_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $transaction_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row['count'] > 0) {
        http_response_code(200); // OK
        echo "Duplicate transaction.";
        exit();
    }

    // Fetch the current balance
    $sql = "SELECT balance FROM students WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $payer_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        http_response_code(404); // Not Found
        echo "Payer not found.";
        exit();
    }

    $row = $result->fetch_assoc();
    $current_balance = $row['balance'];
    $new_balance = $current_balance - $payment_amount;

    // Update balance
    $sql = "UPDATE students SET balance = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("di", $new_balance, $payer_id);
    $stmt->execute();

    // Record the payment
    $sql = "INSERT INTO payments (transaction_id, payer_id, amount) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sid", $transaction_id, $payer_id, $payment_amount);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        http_response_code(200); // OK
        echo "Payment processed successfully.";
    } else {
        http_response_code(500); // Internal Server Error
        echo "Error processing payment.";
    }

    $stmt->close();
} else {
    http_response_code(400); // Bad Request
    echo ".";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Maintenance</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f2f2f2;
            margin: 0;
        }
        .container {
            text-align: center;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #e74c3c;
        }
        p {
            color: #555;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Service Maintenance</h1>
        <p>Our payment services are currently undergoing maintenance. Please try again later.</p>
        <p>We apologize for the inconvenience.</p>
    </div>
</body>
</html>
