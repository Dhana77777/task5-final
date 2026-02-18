<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$message = "";

/* ---------- ADD POST (Prepared + Validation) ---------- */
if (isset($_POST['add'])) {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);

    if (strlen($title) < 3) {
        $message = "Title must be at least 3 characters.";
    } elseif (strlen($content) < 3) {
        $message = "Content must be at least 3 characters.";
    } else {
        $stmt = $conn->prepare("INSERT INTO posts(title, content) VALUES (?, ?)");
        $stmt->bind_param("ss", $title, $content);
        $stmt->execute();
        $message = "Post added successfully!";
    }
}

/* ---------- SEARCH ---------- */
$search = "";
if (isset($_GET['search'])) {
    $search = trim($_GET['search']);
}

/* ---------- PAGINATION ---------- */
$limit = 5;
$page = 1;

if (isset($_GET['page'])) {
    $page = (int)$_GET['page'];
    if ($page < 1) $page = 1;
}

$start = ($page - 1) * $limit;

/* ---------- COUNT POSTS ---------- */
if ($search != "") {
    $countStmt = $conn->prepare("SELECT COUNT(*) AS total FROM posts WHERE title LIKE ? OR content LIKE ?");
    $like = "%$search%";
    $countStmt->bind_param("ss", $like, $like);
} else {
    $countStmt = $conn->prepare("SELECT COUNT(*) AS total FROM posts");
}

$countStmt->execute();
$countResult = $countStmt->get_result();
$totalPosts = $countResult->fetch_assoc()['total'];

$totalPages = ($totalPosts > 0) ? ceil($totalPosts / $limit) : 1;

/* ---------- FETCH POSTS ---------- */
if ($search != "") {
    $stmt = $conn->prepare("SELECT * FROM posts WHERE title LIKE ? OR content LIKE ? ORDER BY id DESC LIMIT ?, ?");
    $like = "%$search%";
    $stmt->bind_param("ssii", $like, $like, $start, $limit);
} else {
    $stmt = $conn->prepare("SELECT * FROM posts ORDER BY id DESC LIMIT ?, ?");
    $stmt->bind_param("ii", $start, $limit);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
<div class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3>Welcome <?php echo $_SESSION['user']; ?> 👋</h3>
            <p class="text-muted">Role: <b><?php echo $_SESSION['role']; ?></b></p>
        </div>
        <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>

    <?php if ($message != "") { ?>
        <div class="alert alert-info"><?php echo $message; ?></div>
    <?php } ?>

    <!-- SEARCH -->
    <form method="get" class="d-flex mb-4">
        <input type="text" name="search" class="form-control me-2"
               placeholder="Search posts..." value="<?php echo htmlspecialchars($search); ?>">
        <button class="btn btn-success">Search</button>
    </form>

    <!-- ADD POST -->
    <div class="card p-3 mb-4">
        <h4>Add New Post</h4>

        <form method="post">
            <input type="text" name="title" class="form-control mb-2" placeholder="Title" required>
            <textarea name="content" class="form-control mb-2" placeholder="Content" required></textarea>
            <button name="add" class="btn btn-primary">Add Post</button>
        </form>
    </div>

    <h4 class="mb-3">All Posts</h4>

    <!-- POSTS -->
    <?php if ($result->num_rows > 0) { ?>
        <?php while ($row = $result->fetch_assoc()) { ?>
            <div class="card p-3 mb-3">
                <h5><?php echo htmlspecialchars($row['title']); ?></h5>
                <p><?php echo nl2br(htmlspecialchars($row['content'])); ?></p>

                <?php if ($_SESSION['role'] == 'admin') { ?>
                    <a class="btn btn-sm btn-outline-danger"
                       href="delete.php?id=<?php echo $row['id']; ?>"
                       onclick="return confirm('Are you sure you want to delete this post?')">
                        Delete
                    </a>
                <?php } else { ?>
                    <p class="text-muted"><small>Only admin can delete posts.</small></p>
                <?php } ?>
            </div>
        <?php } ?>
    <?php } else { ?>
        <p class="text-muted">No posts found.</p>
    <?php } ?>

    <!-- PAGINATION -->
    <nav>
        <ul class="pagination mt-4">

            <li class="page-item <?php if($page <= 1) echo 'disabled'; ?>">
                <a class="page-link" href="?search=<?php echo $search; ?>&page=<?php echo $page-1; ?>">Previous</a>
            </li>

            <?php for ($i = 1; $i <= $totalPages; $i++) { ?>
                <li class="page-item <?php if($page == $i) echo 'active'; ?>">
                    <a class="page-link" href="?search=<?php echo $search; ?>&page=<?php echo $i; ?>">
                        <?php echo $i; ?>
                    </a>
                </li>
            <?php } ?>

            <li class="page-item <?php if($page >= $totalPages) echo 'disabled'; ?>">
                <a class="page-link" href="?search=<?php echo $search; ?>&page=<?php echo $page+1; ?>">Next</a>
            </li>

        </ul>
    </nav>

</div>
</body>
</html>
