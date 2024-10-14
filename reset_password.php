<?php

require 'config/config.php';
require 'config/database.php';
require 'clases/clientesFunciones.php';

$user_id = $_GET['id'] ?? $_POST['user_id'] ?? '';
$token = $_GET['token'] ?? $_POST['token']?? '';

if($user_id == '' || $token == ''){
    header("Location: index.php");
    exit;
}

$db = new Database();
$con = $db->conectar();

$errors = [];

if(!verificaToken($user_id, $token, $con)){
    echo "No se pudo verificar la información"; 
    exit;
}

if(!empty($_POST)){
    
    $password = trim($_POST['password']);
    $repassword = trim($_POST['repassword']);

    if(esNulo([$user_id, $token, $password, $repassword])){
        $errors[] = "Debe de llenar todos los campos";
    }

    if(!validaPassword($password, $repassword)){
        $errors[] = "Las contraseñas no coinciden";
    }
    
    if(count($errors) ==0){
        $pass_hash = password_hash($password, PASSWORD_DEFAULT);
        if(actualizaPassword($user_id, $pass_hash, $con)){
            echo "Contraseña modificada <br><a href='login.php'>Iniciar sesión</a>";
            exit;
        } else{
            $errors[] = "Error al modificar contraseña. Intentalo nuevamente";
        }
    }

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tienda En Linea</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <link href="css/estilo.css" rel="stylesheet">
</head>
<body>
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
            </div>
        </div>
    </div>
</header>
<main class="form-login m-auto pt-4">
    <h3>Cambiar contraseña</h3>
    <?php mostrarMensajes($errors); ?>

    <form action="reset_password.php" method="POST" class="row g-3" autocomplete="off">
        <input type="hidden" name="user_id" id="user_id" value="<?= $user_id; ?>" >
        <input type="hidden" name="token" id="token" value="<?= $token; ?>">

        <div class="form-floating">
            <input type="password" class="form-control" name="password" id="password" placeholder="Nueva contraseña" required>
            <label for="password">Nueva contraseña</label>
        </div>

        <div class="form-floating">
            <input type="password" class="form-control" name="repassword" id="repassword" placeholder="Confirmar contraseña" required>
            <label for="repassword">Confirmar contraseña</label>
        </div>

        <div class="d-grid gap-3 col-12">
            <button type="submit" class="btn btn-primary">Continuar</button>
        </div>

        <hr>
        <div class="col-12">
            <a href="login.php">Iniciar sesión</a>
        </div>
    </form>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
</body>
</html> 