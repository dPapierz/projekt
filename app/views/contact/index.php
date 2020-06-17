<?php 

use Models\User;

include_once APP_PATH . DS . 'views' . DS . 'header.php';

if (User::isLoged()) {
    include_once APP_PATH . DS . 'views' . DS . 'menu.php';
}

?>

    <main class="container grid grid-align-middle" <?php if(!User::isLoged()) echo 'style="padding-top: 100px;"' ?>>

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

        <form class="grid grid-col-6" method="POST" action="<?php echo ROOT_URL ?>/contact/send">

            <input class="grid-col-12" type="text" name="email" placeholder="Wpisz email">
        
            <textarea class="grid-col-12" style="resize:none; height: 525px;" name="text" placeholder="Treść..."></textarea>

            <button class="grid-col-12 text-white" type="submit"><strong>Wyślij</strong></button>
            
        </form>

        <div class="grid grid-col-6 grid-justify-right">

            <div class="grid grid-col-12">
                
                <img style="grid-column: 1 / span 12; grid-row: 1 / span 12" src="<?php echo ROOT_URL ?>/public/img/ziemia.png"></img>

                <a style="grid-column: 7 / span 6; grid-row: 1 / span 3" href="<?php echo ROOT_URL ?>/home"><img src="<?php echo ROOT_URL ?>/public/img/bank_logo_background.png"></a>

            </div>

            <div class="grid-col-12">

                <h3 class="text-big text-left">Kontakt 24h</h3>

                <p class="text-justify">Jakiekolwiek pytania i problemy dotyczące bankowości internetowej,
                    możesz zgłaszać pod numerem telefonu <strong>800 800 800</strong> czynnym całą dobę
                    lub przez poniższy <strong>formularz kontaktowy</strong></p>

            </div>

        </div>

    </main>

<?php include_once APP_PATH . DS . 'views' . DS . 'footer.php' ?>