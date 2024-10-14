<?php
require 'config/config.php';
require 'config/database.php';
require 'clases/clientesFunciones.php';
$db = new Database();
$con = $db->conectar();

$errors = [];

if(!empty($_POST)){
    $nombres = trim($_POST['nombres']);
    $apellidos = trim($_POST['apellidos']);
    $email = trim($_POST['email']);
    $telefono = trim($_POST['telefono']);
    $dui = trim($_POST['dui']);
    $usuario = trim($_POST['usuario']);
    $password = trim($_POST['password']);
    $repassword = trim($_POST['repassword']);

    if(esNulo([$nombres, $apellidos, $email, $telefono, $dui, $usuario, $password, $repassword])){
        $errors[] = "Debe de llenar todos los campos";
    }

    if(!esEmail($email)){
        $errors[] = "La dirección de correo no es válida";
    }

    if(!validaPassword($password, $repassword)){
        $errors[] = "Las contraseñas no coinciden";
    }

    if(usuarioExiste($usuario, $con)){
        $errors[] = "El nombre de usuario $usuario ya existe";
    }

    if(emailExiste($email, $con)){
        $errors[] = "El correo electronico $email ya existe";
    }

    if(count($errors) == 0){
        $id= registraCliente([$nombres, $apellidos, $email, $telefono, $dui], $con);
        
        if($id >0){

            require 'clases/Mailer.php';
            $mailer = new Mailer();
            $token = generarToken();
            $pass_hash = password_hash($password, PASSWORD_DEFAULT);

            $idUsuario = registraUsuario([$usuario, $pass_hash, $token, $id], $con);
            if($idUsuario >0){
                $url = SITE_URL . 'activa_cliente.php?id='. $id .'&token='. $token;
                $asunto = "Activar cuenta - Tienda en Linea";
                $cuerpo = "Estimado $nombres: <br>Para continuar con el proceso de registro es indespensable dar click en el siguiente link <a href='$url'>Activar cuenta</a>"; 

                if($mailer->enviarEmail($email, $asunto, $cuerpo)){
                    echo "Para terminar el proceso de registro siga las indicaciones que se le ha enviado al correo electronico $email";
                    exit;
                }
            } else{
                $errors[] = "Error al registrar usuario";
            }
    } else{
        $errors[] = "Error al registrar cliente";
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

<main>
    <!--codigo para autogenerar la imagen, informacion y detalles de los productos por el FOREACH sin repertir codigo-->
    <div class="container">
        <h2>Datos del cliente</h2>
        <?php mostrarMensajes($errors); ?>

        <form class="row g-3" actions="registro.php" method="post" autocomplete="off">
            <div class="col-md-6">
                <label for="nombres"><span class="text-danger">*</span> Nombres</label>
                <input type="text" name="nombres" id="nombres" class="form-control" requireda>
            </div>

            <div class="col-md-6">
                <label for="apellidos"><span class="text-danger">*</span> Apellidos</label>
                <input type="text" name="apellidos" id="apellidos" class="form-control" requireda>
            </div>

            <div class="col-md-6">
                <label for="email"><span class="text-danger">*</span> Correo Electronico</label>
                <input type="email" name="email" id="email" class="form-control" requireda>
                <span id="validaEmail" class="text-danger"></span>
            </div>

            <div class="col-md-6">
                <label for="telefono"><span class="text-danger">*</span> Telefono</label>
                <input type="tel" name="telefono" id="telefono" class="form-control" requireda>
            </div>

            <div class="col-md-6">
                <label for="dui"><span class="text-danger">*</span> DUI</label>
                <input type="text" name="dui" id="dui" class="form-control" requireda>
            </div>

            <div class="col-md-6">
                <label for="usuario"><span class="text-danger">*</span> Usuario</label>
                <input type="text" name="usuario" id="usuario" class="form-control" requireda>
                <span id="validaUsuario" class="text-danger"></span>
            </div>

            <div class="col-md-6">
                <label for="password"><span class="text-danger">*</span> Contraseña</label>
                <input type="password" name="password" id="password" class="form-control" requireda>
            </div>

            <div class="col-md-6">
                <label for="repassword"><span class="text-danger">*</span> Verificar contraseña</label>
                <input type="password" name="repassword" id="repassword" class="form-control" requireda>
            </div>

            <i><b>Nota: </b> Todos los campos son obligatorios</i>

            <div class="col-12">
                <button type="submit" class="btn btn-primary">Registrar</button>
            </div>

        </form>
    
    </div>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>

<script>
    let txtUsuario = document.getElementById('usuario')
    txtUsuario.addEventListener("blur", function(){
        existeUsuario(txtUsuario.value)
    },false)

    let txtEmail = document.getElementById('email')
    txtEmail.addEventListener("blur", function(){
        existeEmail(txtEmail.value)
    },false)

    function existeEmail(email){
        let url = "clases/clienteAjax.php"
        let formData = new FormData();
        formData.append("action", "exiteEmail");
        formData.append("email", email)

        fetch(url,{
            method: 'POST',
            body: formData
        }).then(response=> response.json())
        .then(data => {
            if(data.ok){
                document.getElementById('email').value = ''
                document.getElementById('validaEmail').innerHTML = 'Email no disponinle'
            } else{
                document.getElementById('validaEmail').innerHTML = ''
            }
        })
    }

    function existeUsuario(usuario){
        let url = "clases/clienteAjax.php"
        let formData = new FormData();
        formData.append("action", "existeUsuario");
        formData.append("usuario", usuario)

        fetch(url,{
            method: 'POST',
            body: formData
        }).then(response=> response.json())
        .then(data => {
            if(data.ok){
                document.getElementById('usuario').value = ''
                document.getElementById('validaUsuario').innerHTML = 'Usuario no disponinle'
            } else{
                document.getElementById('validaUsuario').innerHTML = ''
            }
        })
    }
</script>
</body>
</html> 