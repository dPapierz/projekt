<?php include_once APP_PATH . DS . 'views' . DS . 'header.php' ?>

<?php include_once APP_PATH . DS . 'views' . DS . 'menu.php' ?>

    <main class="grid container">

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

        <form class="grid grid-col-12 grid-align-middle" action="<?php echo ROOT_URL ?>/transfer/accept" method="POST">

            <h1 class="grid-col-2">Przelew:</h1>
            <div class="grid-col-10">
            <p>Z rachunku: <strong><?php if (!empty($DATA['account'])) echo $DATA['account']['number'] ?></strong></p>
            <?php if (!empty($DATA['errors']['transfer']['sender'])) echo '<p class="text-small" style="color: var(--error-color);">' . $DATA['errors']['transfer']['sender'] . '</p>' ?>
            </div>

            <p class="grid-col-2">Tytuł przelewu:</p>
            <div class="grid-col-10 ">
            <input class="width-100" type="text" name="title" <?php if (!empty($DATA['form']['title'])) echo'value="'.$DATA['form']['title'].'"' ?>>
            <?php if (!empty($DATA['errors']['transfer']['title'])) echo '<p class="text-small" style="color: var(--error-color);">' . $DATA['errors']['transfer']['title'] . '</p>' ?>
            </div>

            <p class="grid-col-2">Numer rachunku:</p>
            <div class="grid-col-10 ">
            <input class="grid-col-10 width-100" type="text" name="reciver" <?php if (!empty($DATA['form']['reciver'])) echo'value="'.$DATA['form']['reciver'].'"' ?>>
            <?php if (!empty($DATA['errors']['transfer']['reciver'])) echo '<p class="text-small" style="color: var(--error-color);">' . $DATA['errors']['transfer']['reciver'] . '</p>' ?>
            </div>

            <p class="grid-col-2">Nazwa odbiorcy (opcjonalny):</p>
            <div class="grid-col-10 ">
            <input class="grid-col-10 width-100" type="text" name="name" <?php if (!empty($DATA['form']['name'])) echo'value="'.$DATA['form']['name'].'"' ?>>
            <?php if (!empty($DATA['errors']['transfer']['name'])) echo '<p class="text-small" style="color: var(--error-color);">' . $DATA['errors']['transfer']['name'] . '</p>' ?>
            </div>

            <p class="grid-col-2">Adres odbiorcy (opcjonalny):</p>
            <div class="grid-col-10 ">
            <input class="grid-col-10 width-100" type="text" name="address" <?php if (!empty($DATA['form']['address'])) echo'value="'.$DATA['form']['address'].'"' ?>>
            <?php if (!empty($DATA['errors']['transfer']['address'])) echo '<p class="text-small" style="color: var(--error-color);">' . $DATA['errors']['transfer']['address'] . '</p>' ?>
            </div>

            <p class="grid-col-2">Opis (opcjonalny):</p>
            <div class="grid-col-10 ">
            <input class="grid-col-10 width-100" type="text" name="description" <?php if (!empty($DATA['form']['description'])) echo'value="'.$DATA['form']['description'].'"' ?>>
            <?php if (!empty($DATA['errors']['transfer']['description'])) echo '<p class="text-small" style="color: var(--error-color);">' . $DATA['errors']['transfer']['description'] . '</p>' ?>
            </div>

            <p class="grid-col-2">Kwota:</p>
            <div class="grid-col-10 ">
            <input class="grid-col-10 width-100" type="number" min="0" step="0.01" name="amount" <?php if (!empty($DATA['form']['amount'])) echo'value="'.$DATA['form']['amount'].'"' ?>>
            <?php if (!empty($DATA['errors']['transfer']['amount'])) echo '<p class="text-small" style="color: var(--error-color);">' . $DATA['errors']['transfer']['amount'] . '</p>' ?>
            </div>

            <button class="grid-col-12 width-100" type="submit" name="accept"><strong style="color: var(--text-color-white);">ZATWIERDŹ</strong></button>

        </form>

    </main>

<?php include_once APP_PATH . DS . 'views' . DS . 'footer.php' ?>