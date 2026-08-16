<?php
/* Đoạn mã xử lý PHP. */

define('TITLE', 'Xóa Trích dẫn');

require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/footer.php';

$has_access = ensure_admin_access();
$error_message = null;
$success_message = null;
$reason = null;
$quote_details = null;

if (!$has_access) {
    $error_message = 'Bạn không có quyền truy cập trang này';
} else {
    try {
        $pdo = get_database_connection();

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $id = $_GET['id'] ?? '';

            if ($id === '') {
                $error_message = 'Không tìm thấy ID trích dẫn';
            } else {
                $statement = $pdo->prepare(
                    'SELECT id, quote, source, favorite
                     FROM quotes
                     WHERE id = :id'
                );

                $statement->execute([':id' => $id]);
                $quote_details = $statement->fetch();

                if (!$quote_details) {
                    $error_message = 'Không tìm thấy trích dẫn';
                }
            }
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? '';

            if ($id === '') {
                $error_message = 'Không tìm thấy ID trích dẫn';
            } else {
                $statement = $pdo->prepare(
                    'DELETE FROM quotes WHERE id = :id'
                );

                $statement->execute([':id' => $id]);

                if ($statement->rowCount() > 0) {
                    $success_message = 'Đã xóa trích dẫn thành công';
                } else {
                    $error_message = 'Không tìm thấy trích dẫn để xóa';
                }
            }
        }
    } catch (PDOException $e) {
        $error_message = 'Không thể xóa trích dẫn';
        $reason = $e->getMessage();
    }
}
?>

<?php render_page_header(); ?>

<h2>Xóa Trích dẫn</h2>

<?php if (!empty($success_message)): ?>

    <p>
        <strong><?= html_escape($success_message) ?></strong>
    </p>

<?php endif; ?>

<?php if (!empty($error_message)): ?>

    <?php include __DIR__ . '/../partials/show_error.php'; ?>

<?php endif; ?>

<?php if ($has_access && !empty($quote_details)): ?>

    <p>Bạn có chắc chắn muốn xóa trích dẫn này không?</p>

    <blockquote>
        <?= html_escape($quote_details['quote']) ?>
    </blockquote>

    <p>
        - <?= html_escape($quote_details['source']) ?>
    </p>

    <form action="delete_quote.php" method="post">

        <input type="hidden"
               name="id"
               value="<?= html_escape($quote_details['id']) ?>">

        <input type="submit" value="Xác nhận xóa">

    </form>

<?php endif; ?>

<?php render_page_footer(); ?>