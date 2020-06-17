<nav class="grid" style="top: 0; position:sticky;">
    <div class="grid-col-2">
        <a href="<?php echo ROOT_URL ?>/main">
            <img src="<?php echo ROOT_URL ?>/public/img/bank_logo_background.png">
        </a>
    </div>

    <?php if($this->isUser) { ?>

    <div class="menu grid-col-10">
        <ul>
            <li><a href="<?php echo ROOT_URL ?>/main">Strona główna</a></li>
            <li class="dropdown">
                <a href="#" class="dropbtn">Transakcje</a>
                <div class="dropdown-content">
                <a href="<?php echo ROOT_URL ?>/transfer">Przelewy</a>
                <a href="<?php echo ROOT_URL ?>/history">Historia operacji</a>
                </div>
            </li>
            <li class="dropdown">
                <a href="#" class="dropbtn">Kredyty</a>
                <div class="dropdown-content">
                <a href="<?php echo ROOT_URL ?>/credit">Twoje kredyty</a>
                <a href="<?php echo ROOT_URL ?>/credit/submit">Nowy kredyt</a>
                </div>
            </li>
            <li class="dropdown">
                <a href="#" class="dropbtn">Doładowania</a>
                <div class="dropdown-content">
                <a href="<?php echo ROOT_URL ?>/plus">Plus</a>
                <a href="<?php echo ROOT_URL ?>/tmobile">T-Mobile</a>
                <a href="<?php echo ROOT_URL ?>/orange">Orange</a>
                <a href="<?php echo ROOT_URL ?>/play">Play</a>
                <a href="<?php echo ROOT_URL ?>/topup">Inne</a>
                </div>
            </li>
            <li><a href="<?php echo ROOT_URL ?>/main/logout">Wyloguj się</a></li>
        </ul>
    </div>


    <?php } else { ?>

    <div class="menu grid-col-10">
        <ul>
            <li><a href="<?php echo ROOT_URL ?>/user">Użytkownicy</a></li>
            <?php if($this->isAdmin) echo '<li><a href="' . ROOT_URL . '/register">Nowy użytkownik</a></li>' ?>
            <li><a href="<?php echo ROOT_URL ?>/contact">Zgłoszenia</a></li>
            <?php if($this->isWorker) echo '<li><a href="' . ROOT_URL . '/credit">Kredyty</a></li>' ?>
            <li><a href="<?php echo ROOT_URL ?>/main/logout">Wyloguj się</a></li>
        </ul>
    </div>

    <?php }?>

</nav>