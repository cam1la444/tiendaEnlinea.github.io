<?php
require 'config/config.php';
require 'config/database.php';
$db = new Database();
$con = $db->conectar();

$sql = $con->prepare("SELECT id, nombre, precio FROM productos WHERE activo =1");
$sql->execute();
$resultado = $sql->fetchAll(PDO::FETCH_ASSOC);
//session_destroy();
//print_r($_SESSION)
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
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-3">
        <?php foreach($resultado as $row){?>
            <div class="col">
                <div class="card shadow-sm">
                    <?php
                    $id = $row['id'];
                    $images = "images/productos/".$id."/prod1.jpg";
                    if(!file_exists($images)){
                        $images = "images/noImages.jpg";
                    }
                    ?>
                    <img src="<?php echo $images; ?>">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $row['nombre'];?></h5>
                        <p class="card-text">$ <?php echo number_format($row['precio'],2,'.',',');?></p>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="btn-group">
                                <a href="details.php?id=<?php echo $row['id']; ?>&token=<?php echo hash_hmac('sha1', $row['id'], KEY_TOKEN); ?>" class="btn btn-primary">Detalles</a>
                            </div>
                            <button class="btn btn-outline-success" type="button" onclick="addProducto(<?php echo $row['id']; ?>, '<?php echo hash_hmac('sha1', $row['id'], KEY_TOKEN); ?>')">Agregar al carrito</button>

                        </div>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>
    </div>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>

<!--<div id="mensajeExito" style="display: none; background-color: #008000; color: white; padding: 10px; position: fixed; top: 10px; right: 10px; border-radius: 5px;">Se agregó el producto al carrito</div> -->
<div id="mensajeExito" style="display: none; background-color: #229954; color: white; padding: 10px; position: fixed; bottom: 10px; left: 10px; border-radius: 5px;">Ítem agregado al carrito <span onclick="ocultarMensaje()" style="cursor: pointer; margin-left: 10px;">&times;</span></div>

<script>
    function addProducto(id, token){
        let url = 'clases/carrito.php'
        let formData = new FormData()
        formData.append('id', id)
        formData.append('token', token)
//se esta utilizando ajax
        fetch(url, {
            method: 'POST',
            body: formData,
            mode: 'cors'
        }).then(response => response.json()).then(data=> {
            if(data.ok){
                let elemento = document.getElementById("num_cart")
                elemento.innerHTML = data.numero

                // Mostrar el mensaje de confirmación
                let mensajeExito = document.getElementById("mensajeExito");
                    mensajeExito.style.display = "block";

                    // Ocultar el mensaje después de 3 segundos
                    setTimeout(function(){
                        mensajeExito.style.display = "none";
                    }, 3000);
                }
            })
        }

        function ocultarMensaje() {
            document.getElementById("mensajeExito").style.display = "none";
        }
</script>
</body>
</html>