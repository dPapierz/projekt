<?php include_once APP_PATH . DS . 'views' . DS . 'header.php' ?>

<?php include_once APP_PATH . DS . 'views' . DS . 'menu.php' ?>

    <main class="container grid grid-justify-center">

        <div class="grid-col-12">

            <?php 
            
                if(!empty($DATA['credit'])) {
                    echo <<<CREDIT
                    <table>
                        <tr><th>Akceptuj</th><th>Wartość</th><th>Waluta</th><th>Ilość rat</th><th>Osoba</th>
                    CREDIT;

                    foreach ($DATA['credit'] as $CREDIT) {
                        $link = ROOT_URL . '/credit/accept/' . $CREDIT['id'];
                        echo <<<CREDIT
                            <tr>
                                <td><a class="link2" href="{$link}">Akceptuj</a></td>
                                <td>{$CREDIT['amount']}
                                <td>{$CREDIT['currency']} </td>
                                <td>{$CREDIT['installment']} </td>
                                <td>{$CREDIT['name']} {$CREDIT['surname']}</td>
                            </tr>
                        CREDIT;
                    }

                    echo "</table>";
                }

            ?>

        </div>

    </main>

<?php include_once APP_PATH . DS . 'views' . DS . 'footer.php' ?>