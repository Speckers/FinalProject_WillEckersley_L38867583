<?php

$host = "localhost";
$user = "root";
$password = "";
$database = "players"; 

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

#Filters
$search = $_GET['search'] ?? '';
$position = $_GET['position'] ?? '';
$limit = $_GET['limit'] ?? 20;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * (is_numeric($limit) ? $limit : 0);

#Sorting
$sort_by = $_GET['sort_by'] ?? 'Nation'; // Default sort by 'Nation'
$sort_order = $_GET['sort_order'] ?? 'ASC'; // Default sort order is ascending
$sort_order = ($sort_order == 'ASC') ? 'ASC' : 'DESC'; // Only allow ASC or DESC

#Escape column name for special characters like % and /
$escaped_sort_by = $conn->real_escape_string($sort_by);

#Build base query
$baseSql = "FROM `2022_2023stats2_in_` WHERE 1";
if (!empty($search)) {
    $search_escaped = $conn->real_escape_string($search);
    $baseSql .= " AND (Nation LIKE '%$search_escaped%' OR Squad LIKE '%$search_escaped%' OR Comp LIKE '%$search_escaped%')";
}
if (!empty($position)) {
    $baseSql .= " AND Pos = '" . $conn->real_escape_string($position) . "'";
}

#Get total records for pagination
$totalResult = $conn->query("SELECT COUNT(*) AS total $baseSql");
$totalRows = $totalResult->fetch_assoc()['total'];
$totalPages = ($limit === 'all') ? 1 : ceil($totalRows / $limit);

#Final SQL with limit and sorting
$sql = "SELECT * $baseSql ORDER BY `$escaped_sort_by` $sort_order";
if ($limit !== 'all') {
    $sql .= " LIMIT " . intval($limit) . " OFFSET $offset";
}
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Players Table</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white p-6">

<div class="max-w-full overflow-x-auto">
    <h1 class="text-3xl font-bold mb-4"> Player Stats Table</h1>

    <!-- Home Button -->
    <div class="mb-4">
        <a href="index.php" class="px-4 py-2 bg-blue-600 rounded hover:bg-blue-700 text-white">Home</a>
    </div>

    <form method="GET" class="flex flex-wrap gap-4 mb-6">
        <input type="text" name="search" placeholder="Search by Nation, Squad, Comp..." value="<?= htmlspecialchars($search) ?>" class="p-2 rounded bg-gray-700 text-white w-64">
        
        <select name="position" class="p-2 rounded bg-gray-700 text-white">
            <option value="">All Positions</option>
            <option value="GK" <?= $position == "GK" ? 'selected' : '' ?>>GK</option>
            <option value="DEF" <?= $position == "DEF" ? 'selected' : '' ?>>DEF</option>
            <option value="MID" <?= $position == "MID" ? 'selected' : '' ?>>MID</option>
            <option value="FWD" <?= $position == "FWD" ? 'selected' : '' ?>>FWD</option>
        </select>

        <select name="limit" class="p-2 rounded bg-gray-700 text-white">
            <option value="20" <?= $limit == 20 ? 'selected' : '' ?>>20 rows</option>
            <option value="50" <?= $limit == 50 ? 'selected' : '' ?>>50 rows</option>
            <option value="100" <?= $limit == 100 ? 'selected' : '' ?>>100 rows</option>
            <option value="all" <?= $limit == 'all' ? 'selected' : '' ?>>Show All</option>
        </select>

        <button type="submit" class="bg-blue-600 px-4 py-2 rounded hover:bg-blue-700">Filter</button>
    </form>

    <?php if ($result && $result->num_rows > 0): ?>
        <table class="min-w-max text-sm border-collapse border border-gray-600">
            <thead class="bg-gray-700">
                <tr>
                    <?php foreach ($result->fetch_fields() as $col): ?>
                        <?php
                        $column_name = $col->name;
                        $new_order = ($sort_order == 'ASC') ? 'DESC' : 'ASC'; #Toggle sort order
                        ?>
                        <th class="border border-gray-600 px-2 py-1">
                            <a href="?<?= http_build_query(array_merge($_GET, ['sort_by' => $column_name, 'sort_order' => $new_order])) ?>" class="text-white">
                                <?= htmlspecialchars($column_name) ?>
                                <?php if ($sort_by == $column_name): ?>
                                    <?= ($sort_order == 'ASC') ? '🔽' : '🔼' ?>
                                <?php endif; ?>
                            </a>
                        </th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody class="bg-gray-800">
                <?php
                $result->data_seek(0);
                while ($row = $result->fetch_assoc()):
                ?>
                    <tr class="hover:bg-gray-700">
                        <?php foreach ($row as $cell): ?>
                            <td class="border border-gray-700 px-2 py-1"><?= htmlspecialchars($cell) ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <!-- Pagination Links -->
        <?php if ($limit !== 'all'): ?>
            <div class="mt-6 flex justify-center space-x-4">
                <?php if ($page > 1): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" class="px-4 py-2 bg-blue-700 rounded hover:bg-blue-800">Previous</a>
                <?php endif; ?>

                <span class="px-4 py-2 bg-gray-700 rounded">Page <?= $page ?> of <?= $totalPages ?></span>

                <?php if ($page < $totalPages): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>" class="px-4 py-2 bg-blue-700 rounded hover:bg-blue-800">Next</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <p>No players found.</p>
    <?php endif; ?>
</div>

</body>
</html>

