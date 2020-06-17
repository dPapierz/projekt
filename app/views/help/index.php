<?php

use Models\User;

include_once APP_PATH . DS . 'views' . DS . 'header.php' ;

if (User::isLoged()) {
    include_once APP_PATH . DS . 'views' . DS . 'menu.php';
}

?>

    <main class="container grid" <?php if(!User::isLoged()) echo 'style="padding-top: 100px;"' ?>>

        <div class="grid-col-12 grid">

                <h1 class="grid-col-10 text-enormous">Najczęściej zadawane pytania</h1>

                <a class="grid-col-2" href="home"><img src="<?php $_SERVER['SERVER_NAME'] ?>/projekt/public/img/bank_logo_goto.png"></a>

                <hr class="thick grid-col-12 width-100">

        </div>

        <div class="grid-col-12">

            <p class="question text-big">Czy dostęp online do konta jest płatny?</p>

            <p class="answer">Aktywacja jak i dostęp do serwisu internetowego 
            JPD Bank są <strong>bezpłatne</strong></p>

            <hr class="light">

            <p class="question text-big">Jak zacząć korzystać z konta przez internet?</p>

            <p class="answer">Należy wypełnić arkusz aktywacyjny znajdujący się pod 
            linkiem <a class="link2" href="<?php $_SERVER['SERVER_NAME'] ?>/projekt/public/Register">Zostań klientem JPD Bank</a></p>

            <hr class="light">

            <p class="question text-big">Jak odblokować dostęp do konta?</p>

            <p class="answer">Jedyną metodą odblokowania dostępu do konta jest 
            rozmowa z naszym konsultantem pod numerem 
            telefonu <strong>800 800 800</strong></p>

            <hr class="light">

            <p class="question text-big">Nie pamiętam swojego numeru klienta, co robić, jak żyć?</p>

            <p class="answer">Numer klienta znajdziesz w <strong>mailu</strong> potwierdzającym 
            otwarcie konta lub <strong>umowie</strong>. Przejrzyj dokładnie wszystkie dokumenty, 
            które otrzymałeś od banku.</a></p>

        </div>

    </main>

<?php include_once APP_PATH . DS . 'views' . DS . 'footer.php' ?>