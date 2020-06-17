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

            <?php if(!empty($DATA['user'])) { ?>

            <form class="grid grid-col-12 grid-align-middle"  method="POST" action="<?php echo ROOT_URL ?>/user/change/<?php echo $DATA['meta']['id'] ?>">

            <p class="grid-col-2">Login</p>
            <div class="grid-col-10 ">
            <input class="width-100" type="text" name="login" <?php if (!empty($DATA['user']['login'])) echo'value="'.$DATA['user']['login'].'"' ?> readonly="readonly">
            <?php if (!empty($DATA['errors']['user']['login'])) echo '<p class="text-small" style="color: var(--error-color);">' . $DATA['errors']['user']['login'] . '</p>' ?>
            </div>

            <p class="grid-col-2">Hasło</p>
            <div class="grid-col-10 ">
            <input class="width-100" type="password" name="password" <?php if (!$DATA['meta']['admin']) echo'readonly="readonly"' ?>>
            <?php if (!empty($DATA['errors']['user']['password'])) echo '<p class="text-small" style="color: var(--error-color);">' . $DATA['errors']['user']['password'] . '</p>' ?>
            </div>

            <p class="grid-col-2">Powtórz hasło</p>
            <div class="grid-col-10 ">
            <input class="width-100" type="password" name="password2" <?php if (!$DATA['meta']['admin']) echo'readonly="readonly"' ?>>
            <?php if (!empty($DATA['errors']['user']['password2'])) echo '<p class="text-small" style="color: var(--error-color);">' . $DATA['errors']['user']['password2'] . '</p>' ?>
            </div>

            <p class="grid-col-2">Imię</p>
            <div class="grid-col-10 ">
            <input class="width-100" type="text" name="name" <?php if (!empty($DATA['user']['name'])) echo'value="'.$DATA['user']['name'].'"' ?> <?php if (!$DATA['meta']['admin']) echo'readonly="readonly"' ?>>
            <?php if (!empty($DATA['errors']['user']['name'])) echo '<p class="text-small" style="color: var(--error-color);">' . $DATA['errors']['user']['name'] . '</p>' ?>
            </div>

            <p class="grid-col-2">Nazwisko</p>
            <div class="grid-col-10 ">
            <input class="width-100" type="text" name="surname" <?php if (!empty($DATA['user']['surname'])) echo'value="'.$DATA['user']['surname'].'"' ?> <?php if (!$DATA['meta']['admin']) echo'readonly="readonly"' ?>>
            <?php if (!empty($DATA['errors']['user']['surname'])) echo '<p class="text-small" style="color: var(--error-color);">' . $DATA['errors']['user']['surname'] . '</p>' ?>
            </div>

            <p class="grid-col-2">Rola</p>
            <div class="grid-col-10 ">
            <select class="width-100" name="role">
                <?php if (!$DATA['meta']['admin']) { ?>
                    <?php if (!empty($DATA['user']['role'])) echo '<option value="' . $DATA['user']['role'] . '">' . ucfirst($DATA['user']['role']) . '</option>'?>
                <?php } else { ?>
                <option <?php if (!empty($DATA['user']['role']) && $DATA['user']['role'] == 'user') echo 'selected' ?> value="user">User</option>
                <option <?php if (!empty($DATA['user']['role']) && $DATA['user']['role'] == 'worker') echo 'selected' ?> value="worker">Worker</option>
                <option <?php if (!empty($DATA['user']['role']) && $DATA['user']['role'] == 'admin') echo 'selected' ?> value="admin">Admin</option>
                <?php } ?>
            </select>
            </div>

            <p class="grid-col-2">Aktywny</p>
            <div class="grid-col-10 ">
            <input type="checkbox" name="active" <?php if (!empty($DATA['user']['active'])) echo 'checked="checked"' ?>>
            <?php if (!empty($DATA['errors']['user']['active'])) echo '<p class="text-small" style="color: var(--error-color);">' . $DATA['errors']['user']['active'] . '</p>' ?>
            </div>


            <a class="grid-col-6" href="<?php echo ROOT_URL ?>/user"><button class="width-100" type="button"><strong style="color: var(--text-color-white);">Wroć</strong></button></a>
            <button class="grid-col-6 width-100" type="submit" name="user" value="1"><strong style="color: var(--text-color-white);">ZATWIERDŹ</strong></button>

            </form>

            <?php } ?>

            <?php if(!empty($DATA['account'])) { ?>

            <hr class="grid-col-12 thick width-100" style="margin: 5rem 0;">

            <form class="grid grid-col-12 grid-align-middle"  method="POST" action="<?php echo ROOT_URL ?>/user/change/<?php echo $DATA['meta']['id'] ?>">

            <p class="grid-col-2">Nr konta</p>
            <div class="grid-col-10 ">
            <input class="width-100" type="text" name="number" <?php if (!empty($DATA['account']['number'])) echo'value="'.$DATA['account']['number'].'"' ?> readonly="readonly">
            </div>

            <p class="grid-col-2">Stan konta</p>
            <div class="grid-col-10 ">
            <input class="width-100" type="number" name="balance" <?php if (!empty($DATA['account']['balance'])) echo'value="'.$DATA['account']['balance'].'"' ?> <?php if (!$DATA['meta']['worker']) echo'readonly="readonly"' ?>>
            <?php if (!empty($DATA['errors']['account']['balance'])) echo '<p class="text-small" style="color: var(--error-color);">' . $DATA['errors']['account']['balance'] . '</p>' ?>
            </div>

            <p class="grid-col-2">Waluta</p>
            <div class="grid-col-10">
            <select class="width-100" name="currency" <?php if (!$DATA['meta']['worker']) echo'readonly="readonly"' ?>>
                <option <?php if (!empty($DATA['account']['currency']) && $DATA['account']['currency'] == 'PLN') echo 'selected' ?> value="PLN">PLN</option>
            </select>
            </div>

            <p class="grid-col-2">Debet</p>
            <div class="grid-col-10">
            <input class="width-100" type="number" name="debit" <?php if (!empty($DATA['account']['debit'])) echo'value="'.$DATA['account']['debit'].'"' ?> <?php if (!$DATA['meta']['worker']) echo'readonly="readonly"' ?>>
            <?php if (!empty($DATA['errors']['account']['debit'])) echo '<p class="text-small" style="color: var(--error-color);">' . $DATA['errors']['account']['debit '] . '</p>' ?>
            </div>

            <a class="grid-col-6" href="<?php echo ROOT_URL ?>/user"><button class="width-100" type="button"><strong style="color: var(--text-color-white);">Wroć</strong></button></a>
            <button class="grid-col-6 width-100" type="submit" name="account" value="1"><strong style="color: var(--text-color-white);">ZATWIERDŹ</strong></button>

            </form>

            <?php } ?>

        </div>

    </main>

<?php include_once APP_PATH . DS . 'views' . DS . 'footer.php' ?>