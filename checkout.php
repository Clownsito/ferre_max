<?php
// -------------------------------------------------
// checkout.php
// Página que muestra el resumen de compra y el
// formulario de envío, además permite eliminar
// ítems del carrito.
// -------------------------------------------------

require_once 'conexion.php';    // Incluimos la configuración de conexión a MySQL
session_start();                // Iniciamos la sesión (aunque no uses $_SESSION, es buena práctica)

// -------------------------------------------------
// 1) Eliminación de un ítem del carrito
// Si llega por GET un parámetro "remove", borramos
// ese producto de la tabla carrito.
// -------------------------------------------------
if (isset($_GET['remove'])) {
    // Aseguramos que el valor sea un número entero
    $cod = intval($_GET['remove']);
    // Ejecutamos la consulta DELETE
    mysqli_query($conectar, "DELETE FROM carrito WHERE codprod = $cod");
    // Redirigimos a la misma página (sin parámetros) para limpiar la URL
    header("Location: checkout.php");
    exit();
}

// -------------------------------------------------
// 2) Consulta de los ítems para mostrar en el resumen
// Obtenemos código, cantidad, subtotal, nombre y precio unitario
// de cada producto que está en el carrito.
// -------------------------------------------------
$mcarro = "
  SELECT 
    c.codprod,                -- Código de producto en el carrito
    c.cantidad,               -- Cantidad del ítem
    c.precio_total_item,      -- Subtotal de ese ítem (cantidad * precio unitario)
    p.nombre,                 -- Nombre del producto
    p.precio AS precio_unitario -- Precio unitario del producto
  FROM carrito c
  INNER JOIN inv_productos p 
    ON c.codprod = p.codigo   -- Relacionamos carrito con la tabla de productos
";
$resCarrito = mysqli_query($conectar, $mcarro);

// -------------------------------------------------
// 3) Recolección de resultados y cálculo de total
// Recorremos el resultado y guardamos cada fila en un array
// Sumamos los subtotales para obtener el total general.
// -------------------------------------------------
$total = 0;
$items = [];
while ($row = mysqli_fetch_assoc($resCarrito)) {
    $items[] = $row;
    $total += $row['precio_total_item'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Checkout – Ferretería Max</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
  <link rel="stylesheet" href="css/style.css" />
</head>
<body>
  <!-- HEADER … -->
<header class="site-header">
    <div class="container" id="container-header">
        <a href="index.php" class="logo">
            <img src="img/logo.png" alt="Ferretería Max" />
            </a>
        <nav class="main-nav">
            <ul>
                <li><a href="index.php">Inicio</a></li>
                <li><a href="contacto.html">Contacto</a></li>
            </ul>
        </nav>
    </div>
</header>
  <main class="container">
    <h2>Resumen de tu pedido</h2>

    <?php if (empty($items)): ?>
      <p class="empty-cart">
        Tu carrito está vacío. <a href="index.php">Volver a productos</a>
      </p>
    <?php else: ?>
      <table class="checkout-table">
        <thead>
          <tr>
            <th>Producto</th>
            <th class="right">Precio unitario</th>
            <th class="center">Cantidad</th>
            <th class="right">Subtotal</th>
            <th>Eliminar</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($items as $item): ?>
          <tr>
            <td><?= htmlspecialchars($item['nombre']) ?></td>
            <td class="right">$ <?= number_format($item['precio_unitario'],0,',','.') ?></td>
            <td class="center"><?= $item['cantidad'] ?></td>
            <td class="right">$ <?= number_format($item['precio_total_item'],0,',','.') ?></td>
            <td class="center">
              <a href="checkout.php?remove=<?= $item['codprod'] ?>"
                 class="btn btn-sm btn-danger">
                &times;
              </a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
        <tfoot>
          <tr>
            <td colspan="3" class="right">Total:</td>
            <td class="right total-cell">$ <?= number_format($total,0,',','.') ?></td>
            <td></td>
          </tr>
        </tfoot>
      </table>

      <section class="shipping-form">
        <h3>Datos de envío</h3>
        <form action="confirmacion.php" method="post">
          <label for="nombre">Nombre completo</label>
          <input type="text" id="nombre" name="nombre" required>

          <label for="direccion">Dirección de envío</label>
          <input type="text" id="direccion" name="direccion" required>

          <label for="ciudad">Ciudad</label>
          <input type="text" id="ciudad" name="ciudad" required>

          <label for="telefono">Teléfono</label>
          <input type="tel" id="telefono" name="telefono" required>

          <button type="submit" class="btn btn-success">Finalizar compra</button>
        </form>
      </section>
    <?php endif; ?>
  </main>

  <!-- ================================
       FOOTER: mismo diseño que en otras páginas
       ================================ -->
  <footer class="site-footer">
    <div class="container">
      <p>&copy; 2025 Ferretería Max. Todos los derechos reservados.</p>
    </div>
  </footer>
</body>
</html>
