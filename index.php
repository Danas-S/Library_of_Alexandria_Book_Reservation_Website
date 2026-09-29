<?php
require_once 'db_config.php';
session_start();

$mysqli = db_connect();

/* Flash messages */
$flash_error   = $_SESSION['flash_error']   ?? '';
$flash_success = $_SESSION['flash_success'] ?? '';
unset($_SESSION['flash_error'], $_SESSION['flash_success']);

/* Categories */
$cats = [];
$cq = $mysqli->query("SELECT category_code, category_description FROM categories ORDER BY category_description");
while ($row = $cq->fetch_assoc()) {
    $cats[] = $row;
}

/* Search inputs */
$title    = is_string($_GET['title'] ?? null) ? trim($_GET['title']) : '';
$author   = is_string($_GET['author'] ?? null) ? trim($_GET['author']) : '';
$category = filter_var($_GET['category'] ?? 0, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]) ?: 0;
$hideReserved = ($_GET['hide_reserved'] ?? '') === '1';

/* Only accept categories that exist in the dropdown. */
$catMap = [];
foreach ($cats as $c) {
    $catMap[$c['category_code']] = $c['category_description'];
}
if (!array_key_exists($category, $catMap)) $category = 0;

$page = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 1;
$limit = 5;

/* WHERE building */
$where  = [];
$params = [];
$types  = '';

if ($title !== '') {
    $where[]  = "b.title LIKE ?";
    $params[] = "%$title%";
    $types   .= 's';
}
if ($author !== '') {
    $where[]  = "b.author LIKE ?";
    $params[] = "%$author%";
    $types   .= 's';
}
if ($category !== 0) {
    $where[]  = "b.category_code = ?";
    $params[] = $category;
    $types   .= 'i';
}
if ($hideReserved) {
    $where[] = "rb.isbn IS NULL";
}

$whereSQL = $where ? "WHERE " . implode(" AND ", $where) : "";

/* Count rows */
$fromSQL = "FROM books b LEFT JOIN reserved_books rb ON rb.isbn = b.isbn";
$count = $mysqli->prepare("SELECT COUNT(*) $fromSQL $whereSQL");
if ($types) $count->bind_param($types, ...$params);
$count->execute();
$count->bind_result($total);
$count->fetch();
$count->close();

$pages = max(1, (int)ceil($total / $limit));
$page = min($page, $pages);
$offset = ($page - 1) * $limit;

/* Keep the same search when moving between pages. */
$searchParams = [
    'title' => $title,
    'author' => $author,
    'category' => $category
];
if ($hideReserved) $searchParams['hide_reserved'] = 1;

/* Fetch records */
$sql = "SELECT b.isbn, b.title, b.author, b.category_code, rb.isbn AS reserved_isbn
        $fromSQL
        $whereSQL
        ORDER BY b.title, b.isbn
        LIMIT ? OFFSET ?";

$stmt = $mysqli->prepare($sql);
$bindTypes = $types . 'ii';
$bindParams = array_merge($params, [$limit,$offset]);
$stmt->bind_param($bindTypes, ...$bindParams);
$stmt->execute();
$result = $stmt->get_result();

include 'header.php';
?>

<!-- -------------------- LIBRARY PAGE UI ------------------- -->

<h2>Explore the Alexandria Archives</h2>

<?php if ($flash_error): ?>
    <div class="alert"><?php echo htmlspecialchars($flash_error, ENT_QUOTES, 'UTF-8'); ?></div>
<?php endif; ?>

<?php if ($flash_success): ?>
    <div class="success"><?php echo htmlspecialchars($flash_success, ENT_QUOTES, 'UTF-8'); ?></div>
<?php endif; ?>

<form method="get">

<div class="form-group">
    <label>Title</label>
    <input name="title" value="<?=htmlspecialchars($title, ENT_QUOTES, 'UTF-8')?>">
</div>

<div class="form-group">
    <label>Author</label>
    <input name="author" value="<?=htmlspecialchars($author, ENT_QUOTES, 'UTF-8')?>">
</div>

<div class="form-group">
    <label>Category</label>
    <select name="category">
        <option value="0">All</option>
        <?php foreach($cats as $c): ?>
            <option value="<?=(int)$c['category_code']?>"
                <?php if ($category==$c['category_code']) echo "selected"; ?>>
                <?=htmlspecialchars((string)($c['category_description']), ENT_QUOTES, 'UTF-8')?>
            </option>
        <?php endforeach; ?>
    </select>
</div>

<label>
  <input type="checkbox" name="hide_reserved" value="1"
     <?php if($hideReserved) echo 'checked'; ?>>
  Hide reserved books
</label>

<input type="submit" value="Search Scrolls">

</form>

<table class="table">
<tr>
<th>ISBN</th>
<th>Scroll</th>
<th>Category</th>
<th>Status</th>
<th>Action</th>
</tr>

<?php while($row = $result->fetch_assoc()): ?>
<?php
$isbn = $row['isbn'];
$busy = $row['reserved_isbn'] !== null;
?>

<tr>
<td><?=htmlspecialchars((string)($isbn), ENT_QUOTES, 'UTF-8')?></td>
<td><strong><?=htmlspecialchars((string)($row['title']), ENT_QUOTES, 'UTF-8')?></strong><br><?=htmlspecialchars((string)($row['author']), ENT_QUOTES, 'UTF-8')?></td>
<td><?=htmlspecialchars((string)($catMap[$row['category_code']] ?? 'Unknown'), ENT_QUOTES, 'UTF-8')?></td>

<td>
<span class="status <?=$busy ? 'reserved':'available'?>">
 <?=$busy?'Reserved':'Available'?>
</span>
</td>

<td class="actions">
<?php if(!$busy && isset($_SESSION['username'])): ?>
<form method="post" action="reserve.php">
<input type="hidden" name="isbn" value="<?=htmlspecialchars((string)($isbn), ENT_QUOTES, 'UTF-8')?>">
<input type="hidden" name="return_url" value="<?=htmlspecialchars($_SERVER['REQUEST_URI'], ENT_QUOTES, 'UTF-8')?>">
<button>Reserve</button>
</form>
<?php elseif(!$busy): ?>
<em>Login to reserve</em>
<?php else: ?>
<em>Unavailable</em>
<?php endif; ?>
</td>
</tr>

<?php endwhile; ?>
</table>

<!-- PAGINATION -->
<div class="pagination">
<?php for($p=1;$p<=$pages;$p++): ?>
<a href="?<?=htmlspecialchars(http_build_query(array_merge($searchParams, ['page' => $p])), ENT_QUOTES, 'UTF-8')?>" class="<?=$p==$page?'current':''?>">
<?=$p?>
</a>
<?php endfor; ?>
</div>

<?php
$stmt->close();
$mysqli->close();
include 'footer.php';
?>
