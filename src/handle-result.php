<?php
function parsePgError($error) {
    $error_msg = $error;
    if (preg_match('/ERROR:\s(.*?)(\s(?:DETAIL|CONTEXT):|$)/', $error, $matches)) {
        $error_msg = trim($matches[1]);
    }
    $errorMappings = [
        'duplicate key value violates unique constraint' => 'Duplicated entity not allowed',
        'violates foreign key constraint' => 'Cannot continue, this record is linked to others'
    ];
    foreach ($errorMappings as $pattern => $replacement) {
        if (str_contains($error, $pattern)) {
            if (str_contains($_SERVER['REQUEST_URI'], '/admin')) {
                return [$replacement, $error];
            }
            return [$replacement];
        }
    }
    return [$error_msg];
}
?>

<?php if (!empty($error)) {
    $error = parsePgError($error);
    ?>
<div class="alert alert-danger text-center alert-dismissible fade show" role="alert">
    <?= $error[0] ?>
    <?php if (count($error) > 1) { ?>
        <hr>
        <strong>Detailed error: </strong><?= $error[1] ?>
    <?php } ?>
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<?php } ?>

<?php if (isset($success)) { ?>
<script type="application/javascript">
setTimeout(() => $('.alert.alert-success').alert('close'), 3000)
</script>
<div class="alert alert-success text-center fade show" role="alert">
    <?= $success ?>
</div>
<?php } ?>
