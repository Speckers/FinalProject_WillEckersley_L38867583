<?php
# Get the formation and stat from user inputs
$formation = $_POST['formation']; // Or $_GET, depending on your method
$stat = $_POST['stat'];           

# Run the Python script
$command = "python playerssquadtool\machine.py $formation $stat";

# Execute the Python script and capture the output
$output = shell_exec($command);

# Debugging: print the raw output to check what's returned by Python
echo "<pre>$output</pre>";

# Decode the JSON output from Python
$decoded_output = json_decode($output, true);

# Check if JSON is valid
if ($decoded_output !== null && isset($decoded_output['starting_eleven']) && is_array($decoded_output['starting_eleven'])) {
    echo "<h2>Optimised Starting Eleven for Formation: {$decoded_output['formation']}</h2>";
    echo "<p>Based on performance metric: {$stat}</p>";
    echo "<ul>";
    foreach ($decoded_output['starting_eleven'] as $player) {
        echo "<li>{$player['Player']} ({$player['Pos']}) - Predicted {$stat}: " . round($player['Predicted Metric'], 2) . "</li>";
    }
    echo "</ul>";
} else {
    echo "<p>Error: No valid starting eleven found in the Python script output.</p>";
    
    echo "<pre>$output</pre>";  
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Generated Squad - Football Squad Planner</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-800 text-white min-h-screen flex flex-col items-center justify-center">

    <header class="text-center mb-8">
        <h1 class="text-4xl font-bold mb-2">Optimised Starting XI for Formation: <?= htmlspecialchars($formation) ?></h1>
        <p class="text-gray-400">Based on performance metric: <?= htmlspecialchars($stat) ?></p>
    </header>

    <!-- Football Pitch-->
    <div class="w-full max-w-3xl mb-8">
        <svg id="pitch" class="w-full h-[400px]" viewBox="0 0 100 60">
            <!-- Field -->
            <rect x="0" y="0" width="100" height="60" fill="green" stroke="white" stroke-width="2" />
            <!-- Middle line -->
            <line x1="50" y1="0" x2="50" y2="60" stroke="white" stroke-width="2" />
            <!-- Center Circle -->
            <circle cx="50" cy="30" r="5" fill="transparent" stroke="white" stroke-width="2" />
            <!-- Penalty box -->
            <rect x="5" y="10" width="20" height="40" fill="transparent" stroke="white" stroke-width="2" />
            <rect x="75" y="10" width="20" height="40" fill="transparent" stroke="white" stroke-width="2" />
           
        </svg>
    </div>

    <!-- Player Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <?php foreach ($decoded_output['starting_eleven'] as $player): ?>
            <div class="bg-gray-800 p-4 rounded-lg shadow-lg">
                <div class="text-center">
                    <h2 class="text-xl font-semibold text-white"><?= htmlspecialchars($player['Player']) ?></h2>
                    <p class="text-gray-400"><?= htmlspecialchars($player['Pos']) ?></p>
                    <p class="text-green-500">Predicted <?= htmlspecialchars($stat) ?>: <?= number_format($player['Predicted Metric'], 2) ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Back Button -->
    <div class="mb-4">
        <a href="select_criteria.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl transition">Back to Criteria</a>
    </div>

    <!-- Scripts to render players on the pitch -->
    <script>
        const players = <?php echo json_encode($decoded_output['starting_eleven']); ?>;
        
        // Example function to render players based on formation and their positions
        function renderPlayers(formation, players) {
            const pitch = document.getElementById("pitch");

            // Clear existing markers
            const playerMarkers = pitch.querySelectorAll('.player');
            playerMarkers.forEach(marker => marker.remove());

            // Define positions for different formations
            const positions = {
                '4-3-3': {
                    'GK': { x: 50, y: 5 },
                    'DEF': [
                        { x: 25, y: 45 }, { x: 35, y: 45 }, { x: 65, y: 45 }, { x: 75, y: 45 }
                    ],
                    'MID': [
                        { x: 30, y: 30 }, { x: 50, y: 30 }, { x: 70, y: 30 }
                    ],
                    'FWD': [
                        { x: 40, y: 10 }, { x: 60, y: 10 }, { x: 50, y: 10 }
                    ]
                },
                '4-4-2': {
                    'GK': { x: 50, y: 5 },
                    'DEF': [
                        { x: 25, y: 45 }, { x: 35, y: 45 }, { x: 65, y: 45 }, { x: 75, y: 45 }
                    ],
                    'MID': [
                        { x: 30, y: 30 }, { x: 50, y: 30 }, { x: 70, y: 30 }, { x: 50, y: 20 }
                    ],
                    'FWD': [
                        { x: 40, y: 10 }, { x: 60, y: 10 }
                    ]
                },
                '3-5-2': {
                    'GK': { x: 50, y: 5 },
                    'DEF': [
                        { x: 35, y: 45 }, { x: 50, y: 45 }, { x: 65, y: 45 }
                    ],
                    'MID': [
                        { x: 25, y: 30 }, { x: 40, y: 30 }, { x: 50, y: 30 }, { x: 60, y: 30 }, { x: 75, y: 30 }
                    ],
                    'FWD': [
                        { x: 40, y: 10 }, { x: 60, y: 10 }
                    ]
                },
            };

            // Loop through players and place them on the pitch
            players.forEach((player, index) => {
                const pos = positions[formation][player.Pos][index];

                const playerElement = document.createElementNS("http://www.w3.org/2000/svg", "circle");
                playerElement.setAttribute("class", "player");
                playerElement.setAttribute("cx", pos.x);
                playerElement.setAttribute("cy", pos.y);
                playerElement.setAttribute("r", "1.5");
                playerElement.setAttribute("fill", player.Pos === "GK" ? "blue" : "yellow");

                pitch.appendChild(playerElement);
            });
        }

        // Render the players on the pitch
        renderPlayers('<?= $formation ?>', players);
    </script>
</body>
</html>


