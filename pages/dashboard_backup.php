<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit();
}
echo "<h1>REDLINE Dashboard</h1>";
echo "<p>Welcome, " . $_SESSION['user']['username'] . "</p>";
?>
<!DOCTYPE html>
<html>
<head>
    <title>REDLINE</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div id="data">Loading...</div>
    <script>
    $.getJSON('../api/get-live-data.php', function(data) {
        if (data.success && data.latest) {
            $('#data').html(`
                <p>Speed: ${data.latest.speed} m/s</p>
                <p>Status: ${data.latest.status}</p>
                <p>Total Today: ${data.stats.total_today}</p>
            `);
        } else {
            $('#data').html('No data yet');
        }
    }).fail(function() {
        $('#data').html('Error loading data');
    });
    </script>
</body>
</html>
