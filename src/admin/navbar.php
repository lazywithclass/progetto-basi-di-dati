<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <h3><a class="navbar-brand" href="/admin">Quibreria</a></h3>
    <div class="navbar-collapse">
        <ul class="navbar-nav mr-auto">
            <?php
            $currentUrl = $_SERVER['REQUEST_URI'];

            $navItems = [
                ['label' => 'Manage Readers', 'link' => '/admin/manage-readers.php'],
                ['label' => 'Manage Books', 'link' => '/admin/manage-books.php'],
                ['label' => 'Manage Branches', 'link' => '/admin/manage-branches.php'],
                ['label' => 'Manage Loans', 'link' => '/admin/manage-loans.php']
            ];

            foreach ($navItems as $item) {
                $activeClass = (strpos($currentUrl, $item['link']) !== false) ? 'active' : '';
                echo '<li class="nav-item ' . $activeClass . '">
                        <a class="nav-link" href="' . $item['link'] . '">' . $item['label'] . '</a>
                      </li>';
            }
            ?>
        </ul>
        <span class="navbar-text mr-3">
            Logged in as <?php echo htmlspecialchars($_SESSION['username']); ?>
        </span>
        <form class="form-inline my-2 my-lg-0" action="logout.php" method="post">
            <button class="btn btn-outline-danger my-2 my-sm-0" type="submit" name="logout">Logout</button>
        </form>
    </div>
</nav>
