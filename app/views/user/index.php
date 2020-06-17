<?php include_once APP_PATH . DS . 'views' . DS . 'header.php' ?>

<?php include_once APP_PATH . DS . 'views' . DS . 'menu.php' ?>

    <main class="container grid grid-justify-center">

        <div class="grid-col-12">

            <form method="POST" action="<?php echo ROOT_URL ?>/user">

            </form>

        </div>

        <div class="grid-col-12">

            <?php 
            
                if(!empty($DATA['users'])) {
                    echo <<<USERS
                    <table>
                        <tr><th>Login</th><th>Imię</th><th>Nazwisko</th><th>Aktywny</th><th>Data logowania</th></tr>
                    USERS;

                    foreach ($DATA['users'] as $USER) {
                        $USER['active'] = $USER['active'] ? 'Tak' : 'Nie';
                        $link = ROOT_URL . '/user/show/' . $USER['id'];
                        echo <<<USER
                            <tr>
                                <td><a class="link2" href="{$link}">{$USER['login']}</a></td>
                                <td>{$USER['name']}</td>
                                <td>{$USER['surname']}</td>
                                <td>{$USER['active']}</td>
                                <td>{$USER['lastLogin']}</td>
                            </tr>
                        USER;
                    }

                    echo "</table>";
                }

            ?>

        </div>

    </main>

<?php include_once APP_PATH . DS . 'views' . DS . 'footer.php' ?>