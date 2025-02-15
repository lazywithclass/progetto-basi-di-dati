<?php if (!empty($error)) { ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <?= $error ?>
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<?php } ?>

<?php if (isset($success)) { ?>
<script type="application/javascript">
setTimeout(() => $('.alert.alert-success').alert('close'), 3000)
</script>
<div class="alert alert-success fade show" role="alert">
    <?= $success ?>
</div>
<?php } ?>
