<?php include_once APP_PATH . DS . 'views' . DS . 'header.php' ?>

<?php include_once APP_PATH . DS . 'views' . DS . 'menu.php' ?>

    <main class="container grid grid-justify-center">

        <?php 

            if (isset($DATA['success'])) {
                foreach ($DATA['success'] as $success) {
                    if (is_array($success)) {
                        continue;
                    }

                    echo <<<SUCCESS
                    <div class="grid-col-12">
                        <p class="text-small" style="color: var(--success-color);">{$success}</p>
                    </div>
                    SUCCESS;
                }
            }

            if (isset($DATA['errors'])) {
                foreach ($DATA['errors'] as $error) {
                    if (is_array($error)) {
                        continue;
                    }

                    echo <<<ERROR
                    <div class="grid-col-12">
                        <p class="text-small" style="color: var(--error-color);">{$error}</p>
                    </div>
                    ERROR;
                }
            }

        ?>

        <div class="grid-col-12">

            <?php 
            
                if(!empty($DATA['credit'])) {
                    echo <<<USERS
                    <table>
                        <tr><th>Szczegóły</th><th>Wartość</th><th>Status</th>
                    USERS;

                    foreach ($DATA['credit'] as $CREDIT) {
                        $link = ($CREDIT['state'] === 'granted' || $CREDIT['state'] === 'closed') ? ROOT_URL . '/credit/show/' . $CREDIT['id'] : '#';
                        echo <<<CREDIT
                            <tr>
                                <td><a class="link2" href="{$link}">Więcej</a></td>
                                <td>{$CREDIT['amount']} {$CREDIT['currency']}</td>
                                <td>{$CREDIT['state']}</td>
                            </tr>
                        CREDIT;
                    }

                    echo "</table>";
                }

            ?>

        </div>

    </main>

<?php include_once APP_PATH . DS . 'views' . DS . 'footer.php' ?>