<?php include_once APP_PATH . DS . 'views' . DS . 'header.php' ?>

<?php include_once APP_PATH . DS . 'views' . DS . 'menu.php' ?>

    <main class="container grid grid-justify-center">

        <?php
            if(!empty($DATA['account'])) {
            echo <<<ACCOUNT
            <div class="saldo_nazwa_konta grid-col-6">
                <p>JPD KONTO: {$DATA['account']['number']}</p>
            </div>
            <div class="saldo_nazwa_srodki grid-col-6">
                <p>Dostępne środki: {$DATA['account']['balance']} {$DATA['account']['currency']}</p>
            </div>
            ACCOUNT;
            }
        ?>

        <hr class="grid-col-12 width-100">

        <div class="grid grid-col-12 grid-justify-center width-100"> 

			<div class="grid-col-12">

                <h1>Wszystkie operacje zrealizowane</h1>

            </div>

        </div>

        <div class="grid-col-12">

            <?php 
                if(!empty($DATA['transfer']['outcome'])) {
            ?>

            <table>

                <tr><th>Data transakcji</th><th>Tytuł</th><th>Konto odbiorcy</th><th>Kwota</th><th>Nazwa Odbiorcy</th><th>Opis</th><th>Adres</th></tr>

                <?php foreach ($DATA['transfer']['outcome'] as $TRANSFER) {
                    echo <<<TRANSFER
                        <tr>
                            <td>{$TRANSFER['date']}</td>
                            <td>{$TRANSFER['title']}</td>
                            <td>{$TRANSFER['reciver']}</td>
                            <td>-{$TRANSFER['amount']}</td>
                            <td>{$TRANSFER['name']}</td>
                            <td width="250px">{$TRANSFER['description']}</td>
                            <td>{$TRANSFER['address']}</td>
                        </tr>
                    TRANSFER;
                } ?>

            </table>

            <?php } else {
                echo '<p>Brak ostatnich wydatków</p>';
            } ?>

		</div>

		<div class="grid-col-12">

            <?php 
                if(!empty($DATA['transfer']['income'])) {
            ?>

            <table>

                <tr><th>Data transakcji</th><th>Tytuł</th><th>Konto nadawcy</th><th>Kwota</th><th>Opis</th></tr>

                <?php foreach ($DATA['transfer']['income'] as $TRANSFER) {
                    echo <<<TRANSFER
                        <tr>
                            <td>{$TRANSFER['date']}</td>
                            <td>{$TRANSFER['title']}</td>
                            <td>{$TRANSFER['sender']}</td>
                            <td>+{$TRANSFER['amount']}</td>
                            <td width="300px">{$TRANSFER['description']}</td>
                        </tr>
                    TRANSFER;
                } ?>

            </table>

            <?php } else {
                echo '<p>Brak ostatnich przychodów</p>';
            } ?>

		</div>

    </main>

<?php include_once APP_PATH . DS . 'views' . DS . 'footer.php' ?>