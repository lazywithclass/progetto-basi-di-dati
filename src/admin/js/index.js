$(document).ready(function() {
  $('#create-user').click(function(e) {
    e.preventDefault();
    $('#content').html(`
                    <h2>Create Reader</h2>
                    <p>The reader will be linked to the library you're admin of</p>
                    <form action="create_reader.php" method="post" class="needs-validation" novalidate>
                        <div class="form-group">
                            <label for="username">Username:</label>
                            <input type="text" name="username" id="username" class="form-control" required>
                            <div class="invalid-feedback">Please enter a username.</div>
                        </div>
                        <div class="form-group">
                            <label for="password">Password:</label>
                            <input type="password" name="password" id="password" class="form-control" required>
                            <div class="invalid-feedback">Please enter a password.</div>
                        </div>
                        <div class="form-group">
                            <label for="fiscal_code">Fiscal Code:</label>
                            <input type="text" name="fiscal_code" id="fiscal_code" class="form-control" required>
                            <div class="invalid-feedback">Please enter a fiscal code.</div>
                        </div>
                        <div class="form-group">
                            <label for="name">Name:</label>
                            <input type="text" name="name" id="name" class="form-control" required>
                            <div class="invalid-feedback">Please enter a name.</div>
                        </div>
                        <div class="form-group">
                            <label for="surname">Surname:</label>
                            <input type="text" name="surname" id="surname" class="form-control" required>
                            <div class="invalid-feedback">Please enter a surname.</div>
                        </div>
                        <button type="submit" class="btn btn-primary">Create User</button>
                    </form>
                `);
    const forms = document.getElementsByClassName('needs-validation');
    Array.prototype.filter.call(forms, function(form) {
      form.addEventListener('submit', function(event) {
        if (form.checkValidity() === false) {
          event.preventDefault();
          event.stopPropagation();
        }
        form.classList.add('was-validated');
      }, false);
    });
  });
});
