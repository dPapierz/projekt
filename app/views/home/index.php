<?php include_once APP_PATH . DS . 'views' . DS . 'header.php' ?>

    <main class="container grid" style="padding-top: 200px;">

        <div class="grid-col-6">

            <a href="<?php echo ROOT_URL ?>/home"><img src="<?php echo ROOT_URL ?>/public/img/bank_logo_goto.png"></a>

        </div>

        <div class="grid-col-6 grid-item-align-middle grid-item-justify-center width-75">

            <form class="grid" method="POST" action="<?php echo ROOT_URL ?>/home/login">

                <?php if(isset($DATA['errors']['user'])) foreach($DATA['errors']['user'] as $error) {echo "<p class='grid-col-7 grid-item-justify-left text-small' style='color: var(--error-color)'>{$error}</p>";} ?>
                
                <input class="grid-col-12 width-100" type="text" name="login" placeholder="Wpisz numer klienta">

                <input class="grid-col-12 width-100" type="password" name="password" placeholder="Wpisz hasło">

                <p class="grid-col-7"></p>

                <button class="grid-col-5 grid-item-justify-right width-100 text-white" type="submit"><strong>ZALOGUJ</strong></button>

            </form>

        </div>

        <div class="grid-col-12 text-justify">

            <h3 class="text-big text-center">UWAGA</h3>

            <p>Mając na uwadze należytą ochronę środków zgromadzonych na rachunkach przypominamy klientom o konieczności zachowania szczególnej ostrożności podczas korzystania z bankowości internetowej:</p>

            <ul class="text-normal" style="list-style-type: none; padding: 0;">
                <li>Nie należy wchodzić na stronę logowania do systemu korzystając z odnośników otrzymanych poczta e-mail lub znajdujących się na stronach nie należących do banku</li>
                <hr class="light">
                <li>Nie należy odpowiadać na żadne e-maile dotyczące weryfikacji Twoich danych (np. identyfikatora, hasła) lub innych ważnych informacji - bank nigdy nie zwraca się o podanie danych poufnych za pomocą poczty elektronicznej</li>
                <hr class="light">
                <li>Przed potwierdzeniem operacji należy uważnie przeczytać SMS z kodem, aby upewnić się, że dotyczy on właściwego przelewu oraz czy numer rachunku na który wysyłane są środki jest zgodny ze zleceniem klienta</li>
                <hr class="light">
                <li>Nie należy przechowywać nazwy użytkownika i haseł w tym samych miejscu oraz nie należy udostępniać ich innym osobom</li>
                <hr class="light">
                <li>Należy zawsze kończyć pracę korzystając z polecenia - Wyloguj</li>
                <hr class="light">
                <li><strong>W przypadku wątpliwości co do prawidłowego działania bankowości internetowej, należy niezwłocznie skontaktować się z Bankiem</strong></li>
                <hr class="light">
                <br>
            </ul>

        </div>

    </main>

<?php include_once APP_PATH . DS . 'views' . DS . 'footer.php' ?>