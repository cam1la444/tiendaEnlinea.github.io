<?php
require 'config/config.php';
require 'config/database.php';
require 'clases/clientesFunciones.php';
$db = new Database();
$con = $db->conectar();

$errors = [];

if(!empty($_POST)){
    
    $email = trim($_POST['email']);

    if(esNulo([ $email])){
        $errors[] = "Debe de llenar el campo";
    }

    if(!esEmail($email)){
        $errors[] = "La dirección de correo no es válida";
    }

    if(count($errors) ==0){
        if(emailExiste($email, $con)){
            $sql= $con->prepare("SELECT usuarios.id, clientes.nombres FROM usuarios INNER JOIN clientes ON usuarios.id_cliente=clientes.id WHERE clientes.email LIKE ? LIMIT 1");
            $sql->execute([$email]);
            $row = $sql->fetch(PDO::FETCH_ASSOC);
            $user_id = $row['id'];
            $nombres = $row['nombres'];

            $token = solicitaPassword($user_id, $con);

            if($token !== null){
                require 'clases/Mailer.php';
                $mailer = new Mailer();

                $url = SITE_URL . 'reset_password.php?id='. $user_id .'&token='. $token;

                $asunto = "Recuperar password - Tienda en Linea";
                $cuerpo = "Estimado $nombres: <br>Si has solicitado el cambio de password debe de dar click en el siguiente link <a href='$url'>$url</a>";
                $cuerpo .= "<br>Si no hizo esta solicitud, puede ignorar este correo";

                if($mailer->enviarEmail($email, $asunto, $cuerpo)){
                    echo "<p><b>Correo enviado</b></p>";
                    echo "<p>Hemos enviado un correo electrónico a $email para restablecer la password</p>";

                    exit;
                }
            }
        } else{
            $errors[] = "No existe una cuenta asociada a esta dirección de correo electrónico";
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
<?php include 'menu.php'; ?>

<main class="form-login m-auto pt-4">
    <h3>Recuperar contraseña</h3>
    <?php mostrarMensajes($errors); ?>

    <form action="recupera.php" method="POST" class="row g-3" autocomplete="off">
        <div class="form-floating">
            <input type="email" class="form-control" name="email" id="email" placeholder="Correo electrónico" required>
            <label for="email">Correo electrónico</label>
        </div>

        <div class="d-grid gap-3 col-12">
            <button type="submit" class="btn btn-primary">Continuar</button>
        </div>

        <hr>
        <div class="col-12">
            ¿No tienes cuenta? <a href="registro.php">Registrate aqui</a>
        </div>
    </form>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
</body>
</html> 