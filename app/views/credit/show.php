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
                    echo <<<CREDIT
                    <div class="grid-col-12"><h1>Kredyt na {$DATA['credit'][0]['credit_amount']} {$DATA['credit'][0]['currency']}</div>
                    <table>
                        <tr><th>Rata</th><th>Wartość</th><th>Waluta</th><th>Zapłacona</th><th>Zapłać</th></tr>
                    CREDIT;

                    foreach ($DATA['credit'] as $key => $CREDIT) {
                        $lp = $key + 1;
                        $link = (!$CREDIT['paid']) ? ROOT_URL . '/credit/show/' . $CREDIT['credit_id'] . '/' .$CREDIT['id'] : '#';
                        $CREDIT['paid'] = ($CREDIT['paid']) ? 'Tak' : 'Nie';
                        echo <<<CREDIT
                            <tr>
                                <td>{$lp}</td>
                                <td>{$CREDIT['amount']}</td>
                                <td>{$CREDIT['currency']}</td>
                                <td>{$CREDIT['paid']}</td>
                                <td><a class="link2" href="{$link}">Zapłać</a></td>
                            </tr>
                        CREDIT;
                    }

                    echo "</table>";
                }

            ?>

        </div>

    </main>

<?php include_once APP_PATH . DS . 'views' . DS . 'footer.php' ?>