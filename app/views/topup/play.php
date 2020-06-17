<?php include_once APP_PATH . DS . 'views' . DS . 'header.php' ?>

<?php include_once APP_PATH . DS . 'views' . DS . 'menu.php' ?>

    <main class="grid grid-align-middle container">

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

            <h1>Nowe doładowanie</h1>

        </div>

        <div class="grid-col-4">

            <img src="<?php echo ROOT_URL ?>/public/img/play.png">

        </div>

        <div class="grid grid-col-8">

            <form class="grid grid-col-12" method="POST" action="<?php echo ROOT_URL ?>/play/topup">

                <p class="grid-col-3">Numer telefonu +48</p>
                <div class="grid-col-9">
                <input class="grid-col-12 width-100" type="text" name="phone" <?php if (!empty($DATA['form']['phone'])) echo'value="'.$DATA['form']['phone'].'"' ?>>
                <?php if (!empty($DATA['error']['topup']['phone'])) echo '<p class="text-small" style="color: var(--error-color);">' . $DATA['error']['topup']['phone'] . '</p>' ?>
                </div>

                <p class="grid-col-3">Kwota</p>
                <div class="grid-col-9">
                <input class="grid-col-12 width-100" type="number" min="0" step="0.01" name="amount" <?php if (!empty($DATA['form']['amount'])) echo'value="'.$DATA['form']['amount'].'"' ?>>
                <?php if (!empty($DATA['error']['topup']['amount'])) echo '<p class="text-small" style="color: var(--error-color);">' . $DATA['error']['topup']['amount'] . '</p>' ?>
                </div>

                <button class="grid-col-12 width-100" type="submit" name="accept"><strong style="color: var(--text-color-white);">Doładuj</strong></button>
        
            </form>          

        </div>

    </main>

<?php include_once APP_PATH . DS . 'views' . DS . 'footer.php' ?>