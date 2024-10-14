<header>
    <div class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a href="#" class="navbar-brand">
                <strong>Tienda en Linea</strong>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
            data-bs-target="#navbarHeader" aria-controls="navbarHeader" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarHeader">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a href="#" class="nav-link active">Catalogo</a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">Contacto</a>
                </li>
            </ul>
            <a href="checkout.php" class="btn btn-primary me-2 btn-sm">Carrito <span id="num_cart" class="badge bg-secondary">
                <?php echo $num_cart; ?>
            </span></a>

            <?php if(isset($_SESSION['user_id'])) { ?>
                <div class="dropdown">
                    <button class="btn btn-success btn-sm dropdown-toggle" type="button" id="btn_session" data-bs-toggle="dropdown" aria-expanded="false">
                    <?php echo $_SESSION['user_name']; ?></a>
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="btn_session">
                        <li><a class="dropdown-item" href="logout.php">Cerrar sesión</a></li>
                        <li><a class="dropdown-item" href="compras.php">Mis compras</a></li>
                    </ul>
                </div>
            <?php } else{ ?>
                <a href="login.php" class="btn btn-success btn-sm">Ingresar</a>
            <?php } ?>   
            </div>
        </div>
    </div>
</header>