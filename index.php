<?php

require_once 'conexion.php'; //Pagina de conexion a la base de datos
session_start(); //Iniciamos sesion 

$sql = "SELECT * FROM inv_productos"; //Consulta sql de la tabla inv_productos
$sqlquery = mysqli_query($conectar, $sql); //Executamos la consulta 

if (isset($_GET['id'])) { //Si, id se obtiene por Get entonces...
    $codprod = $_GET['id']; 

    // Preguntar si ya existe el producto en el carrito para ESE USUARIO
    $revisar = "SELECT COUNT(*) as contar FROM carrito WHERE codprod = '$codprod'"; //Contamos los productos
    $revisar_query = mysqli_query($conectar, $revisar);
    $arrayrevisar = mysqli_fetch_array($revisar_query);

    if ($arrayrevisar['contar'] > 0) {
        // Ya está el producto en el carro, incrementar la cantidad y actualizar el precio total del item

        // Primero, obtenemos el precio unitario del producto
        $precio_unitario_sql = "SELECT precio FROM inv_productos WHERE codigo = '$codprod'";
        $precio_unitario_query = mysqli_query($conectar, $precio_unitario_sql);
        $fila_precio = mysqli_fetch_assoc($precio_unitario_query);

        if ($fila_precio) { //Si encuentra precio de la tabla donde codigo es codprod entonces...
            $precio_unitario = $fila_precio['precio'];

            // Actualizamos la cantidad y el precio total del item
            $updc = "UPDATE carrito SET cantidad = cantidad + 1, precio_total_item = precio_total_item + $precio_unitario WHERE codprod = '$codprod'";
            $updatecarro = mysqli_query($conectar, $updc);

            if ($updatecarro) {
                // Éxito al actualizar
                // mensaje o redirección
            } else {
                // Error al actualizar
                echo "Error al actualizar la cantidad y el precio en el carrito: " . mysqli_error($conectar);
            }
        } else {
            // Error al obtener el precio unitario
            echo "Error al obtener el precio del producto con código: " . $codprod;
        }
    } else {
        // El producto no está en el carrito, insertar un nuevo registro
        // También obtenemos el precio unitario para insertarlo en precio_total_item
        $precio_unitario_sql_insert = "SELECT precio FROM inv_productos WHERE codigo = '$codprod'";
        $precio_unitario_query_insert = mysqli_query($conectar, $precio_unitario_sql_insert);
        $fila_precio_insert = mysqli_fetch_assoc($precio_unitario_query_insert);

        if ($fila_precio_insert) {
            $precio_unitario_insert = $fila_precio_insert['precio']; //Obtenemos el precio ejecutando la consulta con una variable.
            $carrito = "INSERT INTO carrito (codprod, cantidad, precio_total_item) VALUES ('$codprod', 1, $precio_unitario_insert)";//Insertamos en la tabla los respectivos valores.
            $querycarrito = mysqli_query($conectar, $carrito);
            if ($querycarrito) { //Si se inserta entonces...
                // Éxito al insertar
                // mensaje o redirección
                header("Location: index.php");
                echo "<script>window.location.reload();</script>";
                exit();
            } else {
                // Error al insertar
                echo "Error al agregar el producto al carrito: " . mysqli_error($conectar);
            }
        } else {
            // Error al obtener el precio unitario para la inserción
            echo "Error al obtener el precio del producto con código: " . $codprod . " para la inserción.";
        }
    }

    // Redirigir para evitar que se vuelva a agregar el producto al recargar la página
    header("Location: index.php");
    exit();
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Ferretería Max</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="css/style.css" />
</head>
<body>
    <header class="site-header">
        <div class="container" id="container-header">
            <a href="index.html" class="logo">
                <img src="img/logo.png" alt="Ferretería Max" />
                </a>
            <nav class="main-nav">
                <ul>
                    <li><a href="index.html">Inicio</a></li>
                    <li><a href="productos.html">Productos</a></li>
                    <li><a href="contacto.html">Contacto</a></li>
                    <li class="carrito-toggle">
                        <button class="btn btn-warning" id="abrirCarrito">
                            Carrito <span class="badge bg-danger carrito-cantidad">
                                <?php
                                $total_items_sql = "SELECT SUM(cantidad) AS total_items FROM carrito"; //Sumamos la cantidad del carrito
                                $total_items_query = mysqli_query($conectar, $total_items_sql);
                                $fila_total_items = mysqli_fetch_assoc($total_items_query);
                                echo $fila_total_items['total_items'] ? $fila_total_items['total_items'] : 0; // mostramos total_items si es verdadero y sino mostramos 0
                                ?>
                            </span>
                        </button>
                    </li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="container">
        <section class="hero">
            <h2>¡Bienvenido a <em>Ferretería Max</em>!</h2>
            <p>Todo lo que necesitas para tus proyectos de construcción.</p>
            <a href="productos.html" class="btn btn-warning">Ver Productos</a>
        </section>

        <section class="featured-products">
            <h3>Productos Destacados</h3>
            <div class="product-grid">
                <?php foreach ($sqlquery as $row){ //Recorremos la consulta para obtener los datos e imprimirlos.?> 
                    <article class="product-card">
                        <img src="<?php echo $row['img']; ?>" alt="<?php echo $row['nombre']; ?>">
                        <h4><?php echo $row['nombre']; ?></h4>
                        <p><?php echo $row['descripcion']; ?></p>
                        <h4>$<?php echo number_format($row['precio'], 0, ',', '.'); //Mostramos el precio en formato chileno.?></h4> 
                        <a class="btn btn-warning agregar-carrito" href="index.php?id=<?php echo $row['codigo']?>">Agregar al Carrito x1</a>
                    </article>
                <?php }?>
            </div>
        </section>

        <div class="carrito-sidebar" id="carritoSidebar">
            <div class="carrito-header">
                <h3>Tu Carrito</h3>
                <button class="btn-cerrar" id="cerrarCarrito">&times;</button>
            </div>
            <div class="carrito-items">
                <?php
                $mcarro = "SELECT c.cantidad, c.precio_total_item, p.nombre, p.img FROM carrito c INNER JOIN inv_productos p ON c.codprod = p.codigo";//Selecciona cantidad, precio total del ítem del carrito, nombre e imagen del producto, uniendo las tablas 'carrito' (alias 'c') e 'inv_productos' (alias 'p') por el código del producto.
                $mostrarcarro_sidebar = mysqli_query($conectar, $mcarro);
                while ($row_carrito_sidebar = mysqli_fetch_assoc($mostrarcarro_sidebar)) { //Recorremos la consulta para setear e imprimir los datos.
                    $cantidad_item_sidebar = $row_carrito_sidebar['cantidad'];
                    $precio_total_item_sidebar = $row_carrito_sidebar['precio_total_item'];
                    $nombre_producto_sidebar = $row_carrito_sidebar['nombre'];
                    $img_producto_sidebar = $row_carrito_sidebar['img'];
                    ?>
                    <div class="carrito-item">
                        <img src="<?php echo $img_producto_sidebar; ?>" alt="<?php echo $nombre_producto_sidebar; ?>" class="carrito-item-imagen">
                        <div class="carrito-item-detalles">
                            <h4><?php echo $nombre_producto_sidebar; ?></h4>
                            <p>Cantidad: <?php echo $cantidad_item_sidebar; ?></p>
                            <p>Total: $ <?php echo number_format($precio_total_item_sidebar, 0, ',', '.'); //Setear precio en pesos chilenos?></p>
                        </div>
                    </div>
                    <?php
                } //Terminamos el while
                ?>
            </div>
            <div class="carrito-footer">
                <?php
                $total_carrito_sql = "SELECT SUM(precio_total_item) AS total FROM carrito";//Sumamos el precio total item del carrito
                $total_carrito_query = mysqli_query($conectar, $total_carrito_sql);
                $fila_total_carrito = mysqli_fetch_assoc($total_carrito_query);
                $total_carrito = $fila_total_carrito['total'] ? $fila_total_carrito['total'] : 0; //mostramos total si es verdadero y sino 0
                ?>
                <h3>Total del Carrito: $ <?php echo number_format($total_carrito, 0, ',', '.'); //Peso chileno?></h3> 
                <a href="checkout.php" class="btn btn-success">Ir al Checkout</a>
            </div>
        </div>

        <div class="carrito-overlay" id="carritoOverlay"></div>

    </main>

    <footer class="site-footer">
        <div class="container">
            <p>&copy; 2025 Ferretería Max. Todos los derechos reservados.</p>
        </div>
    </footer>

    <script>
        /* Selecciona elementos del carrito (botones, sidebar, overlay, contador) y agrega listeners para mostrar/ocultar el sidebar al hacer clic en el botón o el overlay. */
        const abrirCarritoBtn = document.getElementById('abrirCarrito');
        const cerrarCarritoBtn = document.getElementById('cerrarCarrito');
        const carritoSidebar = document.getElementById('carritoSidebar');
        const carritoOverlay = document.getElementById('carritoOverlay');
        const carritoCantidadBadge = document.querySelector('.carrito-cantidad');
        const agregarCarritoBtns = document.querySelectorAll('.agregar-carrito');

        abrirCarritoBtn.addEventListener('click', () => {
            carritoSidebar.classList.add('activo');
            carritoOverlay.classList.add('activo');
        });

        cerrarCarritoBtn.addEventListener('click', () => {
            carritoSidebar.classList.remove('activo');
            carritoOverlay.classList.remove('activo');
        });

        carritoOverlay.addEventListener('click', () => {
            carritoSidebar.classList.remove('activo');
            carritoOverlay.classList.remove('activo');
        });

        // Por ahora, la cantidad se actualiza al recargar la página.
    </script>
    <script src="js/main.js"></script> 
</body>
</html>