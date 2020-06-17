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

            <form class="grid grid-col-12 grid-align-middle"  method="POST" action="<?php echo ROOT_URL ?>/register/add">

            <p class="grid-col-2">Login</p>
            <div class="grid-col-10 ">
            <input class="width-100" type="text" name="login" <?php if (!empty($DATA['register']['login'])) echo 'value="'.$DATA['register']['login'].'"' ?>>
            <?php if (!empty($DATA['errors']['register']['login'])) echo '<p class="text-small" style="color: var(--error-color);">' . $DATA['errors']['register']['login'] . '</p>' ?>
            </div>

            <p class="grid-col-2">Hasło</p>
            <div class="grid-col-10">
            <input class="width-100" type="password" name="password">
            <?php if (!empty($DATA['errors']['register']['password'])) echo '<p class="text-small" style="color: var(--error-color);">' . $DATA['errors']['register']['password'] . '</p>' ?>
            </div>

            <p class="grid-col-2">Powtórz hasło</p>
            <div class="grid-col-10 ">
            <input class="width-100" type="password" name="password2">
            <?php if (!empty($DATA['errors']['register']['password2'])) echo '<p class="text-small" style="color: var(--error-color);">' . $DATA['errors']['register']['password2'] . '</p>' ?>
            </div>

            <p class="grid-col-2">Imię</p>
            <div class="grid-col-10 ">
            <input class="width-100" type="text" name="name" <?php if (!empty($DATA['register']['name'])) echo 'value="'.$DATA['register']['name'].'"' ?>>
            <?php if (!empty($DATA['errors']['user']['name'])) echo '<p class="text-small" style="color: var(--error-color);">' . $DATA['errors']['user']['name'] . '</p>' ?>
            </div>

            <p class="grid-col-2">Nazwisko</p>
            <div class="grid-col-10 ">
            <input class="width-100" type="text" name="surname" <?php if (!empty($DATA['register']['surname'])) echo 'value="'.$DATA['register']['surname'].'"' ?>>
            <?php if (!empty($DATA['errors']['register']['surname'])) echo '<p class="text-small" style="color: var(--error-color);">' . $DATA['errors']['register']['surname'] . '</p>' ?>
            </div>

            <p class="grid-col-2">Rola</p>
            <div class="grid-col-10 ">
            <select class="width-100" name="role">
                <option <?php if (!empty($DATA['register']['role']) && $DATA['register']['role'] == 'user') echo 'selected' ?> value="user">User</option>
                <option <?php if (!empty($DATA['register']['role']) && $DATA['register']['role'] == 'worker') echo 'selected' ?> value="worker">Worker</option>
                <option <?php if (!empty($DATA['register']['role']) && $DATA['register']['role'] == 'admin') echo 'selected' ?> value="admin">Admin</option>
            </select>
            </div>

            <p class="grid-col-2">Aktywny</p>
            <div class="grid-col-10 ">
            <input type="checkbox" name="active" <?php if (!empty($DATA['register']['active'])) echo 'checked="checked"' ?>>
            <?php if (!empty($DATA['errors']['register']['active'])) echo '<p class="text-small" style="color: var(--error-color);">' . $DATA['errors']['register']['active'] . '</p>' ?>
            </div>


            <a class="grid-col-6" href="<?php echo ROOT_URL ?>/user"><button class="width-100" type="button"><strong style="color: var(--text-color-white);">Wroć</strong></button></a>
            <button class="grid-col-6 width-100" type="submit" name="accept"><strong style="color: var(--text-color-white);">ZATWIERDŹ</strong></button>

            </form>

        </div>

    </main>

<?php include_once APP_PATH . DS . 'views' . DS . 'footer.php' ?>