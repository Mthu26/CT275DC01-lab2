<?php
/* Đoạn mã xử lý PHP. */

define('TITLE', 'Tìm kiếm Trích dẫn');

require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/footer.php';

$keyword = trim($_GET['keyword'] ?? '');
$source = trim($_GET['source'] ?? '');

$quotes = [];
$sources = [];
$error_message = null;
$reason = null;

try {
    $pdo = get_database_connection();

    // Lấy danh sách nguồn/tác giả để đưa vào combobox
    $statement = $pdo->query(
        'SELECT DISTINCT source
         FROM quotes
         ORDER BY source'
    );

    $sources = $statement->fetchAll(PDO::FETCH_COLUMN);

    // Nếu người dùng đã submit form thì thực hiện tìm kiếm
    if (isset($_GET['search'])) {
        $sql = '
            SELECT id, quote, source, favorite
            FROM quotes
            WHERE quote ILIKE :keyword
        ';

        $params = [
            ':keyword' => '%' . $keyword . '%'
        ];

        // Nếu đã chọn nguồn/tác giả
        if ($source !== '') {
            $sql .= ' AND source = :source';
            $params[':source'] = $source;
        }

        $sql .= ' ORDER BY date_entered DESC';

        $statement = $pdo->prepare($sql);
        $statement->execute($params);

        $quotes = $statement->fetchAll();
    }
} catch (PDOException $e) {
    $error_message = 'Không thể tìm kiếm dữ liệu';
    $reason = $e->getMessage();
}
?>

<?php render_page_header(); ?>

<h2>Tìm kiếm Trích dẫn</h2>

<?php if (!empty($error_message)): ?>

    <?php include __DIR__ . '/../partials/show_error.php'; ?>

<?php endif; ?>

<form action="search.php" method="get">

    <p>
        <label>
            Từ khóa:
            <input
                type="text"
                name="keyword"
                value="<?= html_escape($keyword) ?>"
            >
        </label>
    </p>

    <p>
        <label>
            Nguồn/Tác giả:
            <select name="source">

                <option value="">-- Tất cả nguồn --</option>

                <?php foreach ($sources as $item): ?>
                    <option
                        value="<?= html_escape($item) ?>"
                        <?= $source === $item ? 'selected' : '' ?>
                    >
                        <?= html_escape($item) ?>
                    </option>
                <?php endforeach; ?>

            </select>
        </label>
    </p>

    <p>
        <input type="submit" name="search" value="Tìm kiếm">
    </p>

</form>

<?php if (isset($_GET['search'])): ?>

    <h3>Kết quả tìm kiếm</h3>

    <?php if (!empty($quotes)): ?>

        <?php foreach ($quotes as $quote): ?>

            <div>
                <blockquote>
                    <?= html_escape($quote['quote']) ?>
                </blockquote>

                <p>
                    - <?= html_escape($quote['source']) ?>

                    <?php if (!empty($quote['favorite'])): ?>
                        <strong> | Yêu thích!</strong>
                    <?php endif; ?>
                </p>
            </div>

            <hr>

        <?php endforeach; ?>

    <?php else: ?>

        <p>Không tìm thấy trích dẫn phù hợp.</p>

    <?php endif; ?>

<?php endif; ?>

<?php render_page_footer(); ?>