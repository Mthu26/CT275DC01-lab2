<?php
/* Đoạn mã xử lý PHP. */

define('TITLE', 'Hiệu chỉnh Trích dẫn');

require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/footer.php';

$has_access = ensure_admin_access();
$error_message = null;
$success_message = null;
$reason = null;

$form_data = [
    'id' => '',
    'quote' => '',
    'source' => '',
    'favorite' => false
];

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
                $quote = $statement->fetch();

                if ($quote) {
                    $form_data = $quote;
                } else {
                    $error_message = 'Không tìm thấy trích dẫn';
                }
            }
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $form_data['id'] = $_POST['id'] ?? '';
            $form_data['quote'] = trim($_POST['quote'] ?? '');
            $form_data['source'] = trim($_POST['source'] ?? '');
            $form_data['favorite'] = isset($_POST['favorite']);

            if (
                $form_data['id'] === '' ||
                $form_data['quote'] === '' ||
                $form_data['source'] === ''
            ) {
                $error_message = 'Vui lòng nhập đầy đủ thông tin';
            } else {
                $statement = $pdo->prepare(
                    'UPDATE quotes
                     SET quote = :quote,
                         source = :source,
                         favorite = :favorite
                     WHERE id = :id'
                );

                $statement->execute([
                    ':quote' => $form_data['quote'],
                    ':source' => $form_data['source'],
                    ':favorite' => $form_data['favorite'],
                    ':id' => $form_data['id']
                ]);

                $success_message = 'Đã cập nhật trích dẫn thành công';
            }
        }
    } catch (PDOException $e) {
        $error_message = 'Không thể xử lý dữ liệu';
        $reason = $e->getMessage();
    }
}
?>

<?php render_page_header(); ?>

<h2>Hiệu chỉnh Trích dẫn</h2>

<?php if (!empty($success_message)): ?>
    <p>
        <strong><?= html_escape($success_message) ?></strong>
    </p>
<?php endif; ?>

<?php if (!empty($error_message)): ?>
    <?php include __DIR__ . '/../partials/show_error.php'; ?>
<?php endif; ?>

<?php if ($has_access && !empty($form_data['id'])): ?>

    <form action="edit_quote.php" method="post">

        <input type="hidden"
               name="id"
               value="<?= html_escape($form_data['id']) ?>">

        <p>
            <label>
                Trích dẫn:
                <input type="text"
                       name="quote"
                       value="<?= html_escape($form_data['quote']) ?>">
            </label>
        </p>

        <p>
            <label>
                Nguồn:
                <input type="text"
                       name="source"
                       value="<?= html_escape($form_data['source']) ?>">
            </label>
        </p>

        <p>
            <label>
                <input type="checkbox"
                       name="favorite"
                       <?= $form_data['favorite'] ? 'checked' : '' ?>>
                Yêu thích
            </label>
        </p>

        <p>
            <input type="submit" value="Cập nhật Trích dẫn">
        </p>

    </form>

<?php endif; ?>

<?php render_page_footer(); ?>