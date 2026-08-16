<?php
/* Đoạn mã xử lý PHP. */

define('TITLE', 'Xem tất cả các Trích dẫn');

require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/footer.php';

$has_access = ensure_admin_access();
$error_message = null;
$reason = null;
$quotes = [];

if (!$has_access) {
    $error_message = 'Bạn không có quyền truy cập trang này';
} else {
    try {
        $pdo = get_database_connection();

        $statement = $pdo->query(
            'SELECT id, quote, source, favorite, date_entered
             FROM quotes
             ORDER BY date_entered DESC'
        );

        $quotes = $statement->fetchAll();
    } catch (PDOException $e) {
        $error_message = 'Không thể lấy dữ liệu';
        $reason = $e->getMessage();
    }
}
?>

<?php render_page_header(); ?>

<h2>Tất cả các Trích dẫn</h2>

<?php if (!empty($error_message)): ?>

    <?php include __DIR__ . '/../partials/show_error.php'; ?>

<?php elseif (!empty($quotes)): ?>

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

            <p>
                <a href="edit_quote.php?id=<?= urlencode($quote['id']) ?>">Sửa</a>
                <->
                <a href="delete_quote.php?id=<?= urlencode($quote['id']) ?>">Xóa</a>
            </p>
        </div>

        <hr>

    <?php endforeach; ?>

<?php else: ?>

    <p>Không có trích dẫn nào.</p>

<?php endif; ?>

<?php render_page_footer(); ?>