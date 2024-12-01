<?php
require('dbconn.php');

$activeSection = 'borrowedBooks'; // Default section
$userId = $_GET['id'] ?? null; // Assuming user ID is passed as a parameter
$user = []; // Array to hold user information
$payment_result = []; // Array for payment history
$borrowed_result = []; // Array for borrowed books
$overdue_result = []; // Array for overdue books

if ($userId) {
    // Fetch user profile information
    $userSql = "SELECT Name, EmailId, MobNo FROM LMS.user WHERE IDNo = '$userId'";
    $userResult = $conn->query($userSql);
    $user = $userResult->fetch_assoc();

    // Fetch payment history
    $paymentSql = "SELECT id, amount, payment_date FROM payments WHERE user_id = '$userId'";
    $payment_result = $conn->query($paymentSql);

    // Fetch borrowed books
    $borrowedSql = "
    SELECT r.BookId, b.Title, r.Date_of_Issue, r.Due_Date, r.Dues
    FROM LMS.record r
    JOIN LMS.book b ON r.BookId = b.BookId
    WHERE r.IDNo = '$userId'";
    $borrowed_result = $conn->query($borrowedSql);

    // Fetch overdue books
    $overdueSql = "
    SELECT r.BookId, b.Title, r.Date_of_Issue, r.Due_Date, r.Dues
    FROM LMS.record r
    JOIN LMS.book b ON r.BookId = b.BookId
    WHERE r.IDNo = '$userId' AND r.Due_Date < CURDATE()"; // Overdue books
    $overdue_result = $conn->query($overdueSql);

    if (isset($_POST['section'])) {
        $activeSection = $_POST['section']; // Set active section based on button clicked
    }
} else {
    echo "No student ID specified.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/sidebar.css">
    <title>LMS - User Profile</title>
    <style>
        /* Main content area */
        .content {
            flex-grow: 1;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            background-color: #f8f9fa;
            padding: 20px;
            margin-left: 250px;
            overflow-y: auto;
        }

        /* Button styling */
        .button-group {
            margin-bottom: 20px;
        }

        .button {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-right: 10px; /* Space between buttons */
        }

        .button:hover {
            background-color: #0056b3; /* Darker shade on hover */
        }

        /* Table styling */
        .table-container {
            width: 100%;
            padding: 20px;
            border-radius: 10px;
            background-color: white;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 10px;
            text-align: left;
            border: 1px solid #dee2e6;
        }

        th {
            background-color: #f1f1f1;
            font-weight: bold;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        /* Adjust form layout */
        .form-inline {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 10px;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <h1>LMS</h1>
        <button onclick="location.href='index.php'">Home</button>
        <div class="divider"></div>
        <button onclick="location.href='message.php'">Messages</button>
        <div class="divider"></div>
        <button onclick="location.href='register.php'">Register Student</button>
        <div class="divider"></div>
        <button onclick="location.href='student.php'">Manage Students</button>
        <div class="divider"></div>
        <button onclick="location.href='record.php'">Records</button>
        <div class="divider"></div>
        <button onclick="location.href='book.php'">All Books</button>
        <div class="divider"></div>
        <button onclick="location.href='addbook.php'">Add Books</button>
        <div class="divider"></div>
        <button onclick="location.href='requests.php'">Issue/Return Requests</button>
        <div class="divider"></div>
        <button onclick="location.href='recommendations.php'">Book Recommendations</button>
        <div class="divider"></div>
        <button onclick="location.href='current.php'">Currently Issued Books</button>
        <div class="divider"></div>
        <button onclick="location.href='transaction.php'">Transactions</button>
        <div class="divider"></div>
        <button onclick="location.href='logout.php'" style="margin-bottom: 20px;">Logout</button>
    </div>

    <!-- Main Content Area -->
    <div class="content">
        <div class="table-container">
            <h2>Profile Information</h2>
            <table>
                <tr><th>Name</th><td><?php echo htmlspecialchars($user['Name']); ?></td></tr>
                <tr><th>Email</th><td><?php echo htmlspecialchars($user['EmailId']); ?></td></tr>
                <tr><th>Mobile</th><td><?php echo htmlspecialchars($user['MobNo']); ?></td></tr>
            </table>

            <div class="divider"></div>

            <!-- Buttons for displaying sections -->
            <form method="post">
                <div class="button-group">
                    <button class="button" name="section" value="paymentHistory">Payment History</button>
                    <button class="button" name="section" value="borrowedBooks">Borrowed Books</button>
                    <button class="button" name="section" value="overdueBooks">Overdue Books</button>
                </div>
            </form>

            <!-- Payment History Section -->
            <?php if ($activeSection === 'paymentHistory') { ?>
                <div id="paymentHistory" class="section">
                    <h2>Payment History</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Payment ID</th>
                                <th>Amount</th>
                                <th>Payment Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($payment = $payment_result->fetch_assoc()) { ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($payment['id']); ?></td>
                                    <td><?php echo htmlspecialchars($payment['amount']); ?></td>
                                    <td><?php echo htmlspecialchars($payment['payment_date']); ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            <?php } ?>

            <div class="divider"></div>

            <!-- Borrowed Books Section -->
            <?php if ($activeSection === 'borrowedBooks') { ?>
                <div id="borrowedBooks" class="section">
                    <h2>Borrowed Books</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Book ID</th>
                                <th>Title</th>
                                <th>Date of Issue</th>
                                <th>Due Date</th>
                                <th>Dues</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($borrowed = $borrowed_result->fetch_assoc()) { ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($borrowed['BookId']); ?></td>
                                    <td><?php echo htmlspecialchars($borrowed['Title']); ?></td>
                                    <td><?php echo htmlspecialchars($borrowed['Date_of_Issue']); ?></td>
                                    <td><?php echo htmlspecialchars($borrowed['Due_Date']); ?></td>
                                    <td><?php echo htmlspecialchars($borrowed['Dues']); ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            <?php } ?>

            <div class="divider"></div>

            <!-- Overdue Books Section -->
            <?php if ($activeSection === 'overdueBooks') { ?>
                <div id="overdueBooks" class="section">
                    <h2>Overdue Books</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Book ID</th>
                                <th>Title</th>
                                <th>Date of Issue</th>
                                <th>Due Date</th>
                                <th>Dues</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($overdue = $overdue_result->fetch_assoc()) { ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($overdue['BookId']); ?></td>
                                    <td><?php echo htmlspecialchars($overdue['Title']); ?></td>
                                    <td><?php echo htmlspecialchars($overdue['Date_of_Issue']); ?></td>
                                    <td><?php echo htmlspecialchars($overdue['Due_Date']); ?></td>
                                    <td><?php echo htmlspecialchars($overdue['Dues']); ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            <?php } ?>
        </div>
    </div>
</body>
</html>
