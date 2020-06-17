<?php include_once APP_PATH . DS . 'views' . DS . 'header.php' ?>

<?php include_once APP_PATH . DS . 'views' . DS . 'menu.php' ?>

    <main class="container grid grid-justify-center">

		<div class="grid-col-12">

            <?php 
                if(!empty($DATA['contact'])) {
            ?>

            <table>

                <tr><th>Email</th><th>Treść</th></tr>

                <?php foreach ($DATA['contact'] as $CONTACT) {
                    echo <<<CONTACT
                        <tr>
                            <td>{$CONTACT['email']}</td>
                            <td>{$CONTACT['text']}</td>
                        </tr>
                    CONTACT;
                } ?>

            </table>

            <?php } ?>

		</div>

    </main>

<?php include_once APP_PATH . DS . 'views' . DS . 'footer.php' ?>