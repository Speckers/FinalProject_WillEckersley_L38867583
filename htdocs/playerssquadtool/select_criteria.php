<?php

$servername = "localhost";
$username = "root";  
$password = "";      
$dbname = "players";  

# DB connection
$mysqli = new mysqli($servername, $username, $password, $dbname);

#Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

#Query to get all columns from the `2022_2023stats2_in_` table
$sql = "DESCRIBE 2022_2023stats2_in_";
$result = $mysqli->query($sql);

$columns = [];
if ($result->num_rows > 0) {
    #Fetch all column names
    while ($row = $result->fetch_assoc()) {
        $columns[] = $row['Field'];
    }
} else {
    die("Error retrieving columns from the database.");
}


$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Select Criteria - Football Squad Planner</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-800 text-white min-h-screen flex flex-col items-center justify-center">

    <div class="bg-gray-900 p-8 rounded-xl shadow-lg w-full max-w-lg">
        <h1 class="text-2xl font-bold text-center mb-6">Select Your Criteria</h1>

        <form method="POST" action="generate_squad.php" class="space-y-6">

            <!-- Criteria Selection -->
            <div>
                <label for="stat" class="block text-sm mb-2">Select performance criteria:</label>
                <select name="stat" id="stat" class="w-full p-3 rounded bg-gray-700 text-white">
                    <?php
                    // Display all columns as options
                    foreach ($columns as $column) {
                        // Skip the unwanted columns
                        if (!in_array($column, ['Pos', 'Squad', 'Player', 'Nation', 'Age', 'Comp', 'Born'])) {
                            echo "<option value=\"$column\">$column</option>";
                        }
                    }
                    
                    ?>
                </select>
            </div>

            <!-- Formation Selection -->
            <div>
                <label for="formation" class="block text-sm mb-2">Select formation:</label>
                <select name="formation" id="formation" class="w-full p-3 rounded bg-gray-700 text-white">
                    <option value="4-3-3">4-3-3</option>
                    <option value="4-4-2">4-4-2</option>
                    <option value="3-5-2">3-5-2</option>
                    <option value="4-2-3-1">4-2-3-1</option>
                </select>
            </div>

            <!-- Submit button -->
            <div class="text-center">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 py-3 px-6 rounded-xl font-semibold transition">
                    Generate Starting 11
                </button>
            </div>
        </form>

    </div>

    <a href="index.php" class="mt-6 text-sm text-blue-400 hover:underline">← Back to Home</a>

</body>
</html>
