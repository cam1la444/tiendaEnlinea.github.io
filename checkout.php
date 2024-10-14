<?php
require 'config/config.php';
require 'config/database.php';
$db = new Database();
$con = $db->conectar();

$productos = isset($_SESSION['carrito']['productos']) ? $_SESSION['carrito']['productos'] : null;

$lista_carrito = array();

if($productos != null){
    foreach ($productos as $clave => $cantidad){
        $sql = $con->prepare("SELECT id, nombre, precio, descuento, $cantidad as cantidad FROM productos WHERE id=? AND activo =1");
        $sql->execute([$clave]);
        $lista_carrito[] = $sql->fetch(PDO::FETCH_ASSOC);
    }
}

//session_destroy();

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
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Precio</th>
                        <th>Cantidad</th>
                        <th>Subtotal</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($lista_carrito == null){
                        echo '<tr><td colspan="5" class="text-center"><b>Lista vacia</b></td></tr>';
                    } else{
                        $total =0;
                        foreach($lista_carrito as $producto){
                            $_id = $producto['id'];
                            $nombre = $producto['nombre'];
                            $precio = $producto['precio'];
                            $cantidad = $producto['cantidad'];
                            $descuento = $producto['descuento'];
                            $precio_desc = $precio - (($precio * $descuento) / 100);
                            $subtotal = $cantidad * $precio_desc;
                            $total += $subtotal;
                    ?>

                    <tr>
                        <td><?php echo $nombre; ?></td>
                        <td><?php echo MONEDA . number_format($precio_desc, 2, '.', ','); ?></td>
                        <td>
                            <input type="number" min="1" max="50" step="1" value="<?php echo $cantidad ?>"
                            size="5" id="cantidad_<?php echo $_id; ?>" onchange="actualizaCantidad(this.value, <?php echo $_id; ?>)">
                        </td>
                        <td>
                            <div id="subtotal_<?php echo $_id; ?>" name="subtotal[]"><?php echo MONEDA . number_format($subtotal, 2, '.', ','); ?></div>
                        </td>
                        <td><a id="eliminar" class="btn btn-warning btn-sm" data-bs-id="<?php echo $_id; ?>" data-bs-toggle="modal" data-bs-target="#eliminaModal">Eliminar</a></td>
                    </tr>
                    <?php } ?>

                    <tr>
                        <td colspan="3"></td>
                        <td colspan="2">
                            <p class="h3" id="total"><?php echo MONEDA . number_format($total, 2, '.', ','); ?></p>
                        </td>
                    </tr>
                </tbody>
                <?php } ?>
            </table>
        </div>

        <?php if($lista_carrito != null) { ?>
            <div class="row">
            <div class="col-md-5 offset-md-7 d-grid gap-2">
                <?php if(isset($_SESSION['user_cliente'])) { ?>
                    <a href="pago.php" class="btn btn-primary btn-lg">Realizar el pago</a>
                <?php } else { ?>
                    <a href="login.php?pago" class="btn btn-primary btn-lg">Realizar pago</a>
                <?php } ?>
            </div>
        </div>
        <?php } ?>
    </div>
</main>

<!-- Modal -->
<div class="modal fade" id="eliminaModal" tabindex="-1" aria-labelledby="eliminaModalLabel" aria-hidden="true">
<div class="modal-dialog modal-sm">
    <div class="modal-content">
    <div class="modal-header">
        <h5 class="modal-title" id="eliminaModalLabel">Modal title</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body">
        ¿Desea eliminar el producto de la lista?
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        <button id="btn-elimina" type="button" class="btn btn-danger" onclick="eliminar()">Eliminar</button>
    </div>
    </div>
</div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>

<!--<div id="mensajeExito" style="display: none; background-color: #008000; color: white; padding: 10px; position: fixed; top: 10px; right: 10px; border-radius: 5px;">Se agregó el producto al carrito</div> -->
<!--<div id="mensajeExito" style="display: none; background-color: #229954; color: white; padding: 10px; position: fixed; bottom: 10px; left: 10px; border-radius: 5px;">Ítem agregado al carrito <span onclick="ocultarMensaje()" style="cursor: pointer; margin-left: 10px;">&times;</span></div> -->
<div id="mensajeExito" style="display: none; color: white; padding: 10px; position: fixed; bottom: 10px; left: 10px; border-radius: 5px;">
    Ítem agregado al carrito <span onclick="ocultarMensaje()" style="cursor: pointer; margin-left: 10px;">&times;</span>
</div>

<script>
    let cantidadAnterior = {}
    let eliminaModal = document.getElementById('eliminaModal')
    eliminaModal.addEventListener('show.bs.modal', function(event){
        let button = event.relatedTarget
        let id = button.getAttribute('data-bs-id')
        let buttonElimina = eliminaModal.querySelector('.modal-footer #btn-elimina')
        buttonElimina.value =id
    })
    
    function actualizaCantidad(cantidad, id){
        let url = 'clases/actualizar_carrito.php'
        let formData = new FormData()
        formData.append('action', 'agregar')
        formData.append('id', id)
        formData.append('cantidad', cantidad)
        let cantidadAnteriorItem = cantidadAnterior[id] !== undefined ? cantidadAnterior[id] : cantidad;


//se esta utilizando ajax
        fetch(url, {
            method: 'POST',
            body: formData,
            mode: 'cors'
        }).then(response => response.json()).then(data=> {
            if(data.ok){
                let divsubtotal =document.getElementById('subtotal_' + id)
                divsubtotal.innerHTML = data.sub

                // Mostrar el mensaje de confirmación
                let mensajeTexto = cantidad > cantidadAnteriorItem ? "Producto agregado al carrito" : "Producto quitado del carrito";
                let colorMensaje = cantidad > cantidadAnteriorItem ? "#229954" : "#e74c3c";

                // Mostrar el mensaje adecuado
                let mensajeExito = document.getElementById("mensajeExito");
                mensajeExito.innerHTML = mensajeTexto + ' <span onclick="ocultarMensaje()" style="cursor: pointer; margin-left: 10px;">&times;</span>' // Cambiar el texto del mensaje
                mensajeExito.style.backgroundColor = colorMensaje;
                mensajeExito.style.display = "block";

                    // Ocultar el mensaje después de 3 segundos
                    setTimeout(function(){
                        mensajeExito.style.display = "none";
                    }, 4500);
                }

                let total = 0.00
                let list =document.getElementsByName('subtotal[]')

                for(let i =0; i<list.length; i++){
                    total += parseFloat(list[i].innerHTML.replace(/[<?php echo MONEDA ?>,]/g, ''))
                }

                total = new Intl.NumberFormat('en-US',{
                    minimumFractionDigits:2
                }).format(total)
                document.getElementById('total').innerHTML = '<?php echo MONEDA ?>' + total

                cantidadAnterior[id] = cantidad;
            })
        }

        function ocultarMensaje() {
            document.getElementById("mensajeExito").style.display = "none";
        }

        function eliminar(){
            let botonElimina = document.getElementById('btn-elimina')
            let id = botonElimina.value

            let url = 'clases/actualizar_carrito.php'
            let formData = new FormData()
            formData.append('action', 'eliminar')
            formData.append('id', id)


//se esta utilizando ajax
        fetch(url, {
            method: 'POST',
            body: formData,
            mode: 'cors'
        }).then(response => response.json())
        .then(data=> {
            if(data.ok){
                location.reload()
            }
        })
    }
                
</script>
</body>
</html>