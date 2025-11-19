<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$servername = "localhost";
$username = "root";
$password = "shubham@50554";  // Adjust as per your MySQL credentials
$dbname = "railway_reservation";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$booking_success = false;
$booking_details = [];
$total_price = 0;

if (isset($_GET['train_id'])) {
    $train_id = $_GET['train_id'];
    $sql = "SELECT * FROM trains WHERE id = $train_id";
    $result = $conn->query($sql);
    $train = $result->fetch_assoc();

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $number_of_seats = $_POST['number_of_seats'];
        if ($train['available_seats'] >= $number_of_seats) {
            $user_id = $_SESSION['user_id'];
            $passenger_name = $_POST['passenger_name'];
            $price_per_seat = $train['price_per_seat'];

            // Calculate the total price
            $total_price = $price_per_seat * $number_of_seats;

            $sql = "INSERT INTO reservations (user_id, train_id, passenger_name, number_of_seats, total_price) 
                    VALUES ('$user_id', '$train_id', '$passenger_name', '$number_of_seats', '$total_price')";
            if ($conn->query($sql) === TRUE) {
                $new_seats = $train['available_seats'] - $number_of_seats;
                $update_sql = "UPDATE trains SET available_seats = $new_seats WHERE id = $train_id";
                $conn->query($update_sql);

                // Set booking details
                $booking_success = true;
                $booking_details = [
                    'train_name' => $train['train_name'],
                    'source' => $train['source'],
                    'destination' => $train['destination'],
                    'number_of_seats' => $number_of_seats,
                    'passenger_name' => $passenger_name,
                    'price_per_seat' => $price_per_seat,
                    'total_price' => $total_price
                ];
            } else {
                echo "Error: " . $conn->error;
            }
        } else {
            echo "Not enough seats available!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Ticket - Railway Reservation</title>
    <style>
        body {
            background-image: url('confirm.jpeg');
            background-size: cover;
            background-position: center;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            color: white;
        }
        .container {
            padding: 50px;
        }
        form {
            max-width: 500px;
            margin: 30px auto;
            padding: 20px;
            background-color: rgba(0, 0, 0, 0.7);
            border-radius: 8px;
        }
        label {
            font-weight: bold;
        }
        input[type="text"], input[type="number"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            background-color: rgba(255, 255, 255, 0.8);
        }
        input[type="submit"] {
            width: 100%;
            padding: 10px;
            background-color: #4CAF50;
            border: none;
            border-radius: 5px;
            color: white;
            font-size: 16px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #45a049;
        }
        .confirmation {
            background-color: rgba(0, 0, 0, 0.7);
            padding: 20px;
            margin: 30px auto;
            max-width: 500px;
            border-radius: 8px;
        }
        .confirmation p {
            margin: 10px 0;
            font-size: 18px;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if ($booking_success) { ?>
            <div class="confirmation">
                <h2>Booking Confirmed!</h2>
                <p><strong>Passenger Name:</strong> <?php echo $booking_details['passenger_name']; ?></p>
                <p><strong>Train Name:</strong> <?php echo $booking_details['train_name']; ?></p>
                <p><strong>Source:</strong> <?php echo $booking_details['source']; ?></p>
                <p><strong>Destination:</strong> <?php echo $booking_details['destination']; ?></p>
                <p><strong>Seats Booked:</strong> <?php echo $booking_details['number_of_seats']; ?></p>
                <p><strong>Price per Seat:</strong> ₹<?php echo number_format($booking_details['price_per_seat'], 2); ?></p>
                <p><strong>Total Price:</strong> ₹<?php echo number_format($booking_details['total_price'], 2); ?></p>
            </div>
        <?php } else { ?>
            <h2>Book Ticket</h2>
            <?php if (isset($train)) { ?>
                <p><strong>Train Name:</strong> <?php echo $train['train_name']; ?></p>
                <p><strong>Source:</strong> <?php echo $train['source']; ?></p>
                <p><strong>Destination:</strong> <?php echo $train['destination']; ?></p>
                <p><strong>Available Seats:</strong> <?php echo $train['available_seats']; ?></p>
                <p><strong>Price per Seat:</strong> ₹<?php echo number_format($train['price_per_seat'], 2); ?></p>

                <form method="POST">
                    <label for="passenger_name">Passenger Name:</label><br>
                    <input type="text" name="passenger_name" required><br><br>

                    <label for="number_of_seats">Number of Seats:</label><br>
                    <input type="number" name="number_of_seats" required><br><br>

                    <input type="submit" value="Book Ticket">
                </form>
            <?php } ?>
        <?php } ?>
    </div>
</body>
</html>

<?php
$conn->close();
?>
