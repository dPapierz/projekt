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

            <form class="grid grid-col-12 grid-align-middle" action="<?php echo ROOT_URL ?>/credit/submit" method="POST">

                <p class="grid-col-2">Kwota:</p>
                <div class="grid-col-10 ">
                <input class="grid-col-10 width-100" type="number" min="0" step="0.01" name="amount" <?php if (!empty($DATA['credit']['amount'])) echo'value="'.$DATA['credit']['amount'].'"' ?>>
                <?php if (!empty($DATA['errors']['credit']['amount'])) echo '<p class="text-small" style="color: var(--error-color);">' . $DATA['errors']['credit']['amount'] . '</p>' ?>
                </div>

                <p class="grid-col-2">Waluta</p>
                <div class="grid-col-10">
                <select class="width-100" name="currency" <?php if (!$DATA['meta']['worker']) echo'readonly="readonly"' ?>>
                    <option <?php if (!empty($DATA['credit']['currency']) && $DATA['credit']['currency'] == 'PLN') echo 'selected' ?> value="PLN">PLN</option>
                </select>
                </div>

                <p class="grid-col-2">Ilość rat:</p>
                <div class="grid-col-10 ">
                <input class="grid-col-10 width-100" type="number" min="0" max="24" step="1" name="installment" <?php if (!empty($DATA['credit']['installment'])) echo'value="'.$DATA['credit']['installment'].'"' ?>>
                <?php if (!empty($DATA['errors']['credit']['installment'])) echo '<p class="text-small" style="color: var(--error-color);">' . $DATA['errors']['credit']['installment'] . '</p>' ?>
                </div>

                <button class="grid-col-12 width-100" type="submit" name="accept" value="1"><strong style="color: var(--text-color-white);">Złóż wniosek</strong></button>

            </form>

        </div>

    </main>

<?php include_once APP_PATH . DS . 'views' . DS . 'footer.php' ?>