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
  <!-- Enlazamos nuestro CSS principal -->
  <link rel="stylesheet" href="css/style.css" />
</head>
<body>

  <!-- ================================
       HEADER: mismo que en index.php
       ================================ -->
  <header class="site-header">
    <div class="container">
      <a href="index.php" class="logo">
        <img src="img/logo.png" alt="Ferretería Max">
      </a>
      <nav class="main-nav">
        <ul>
          <li><a href="index.php">Inicio</a></li>
          <li><a href="productos.html">Productos</a></li>
          <li><a href="checkout.php" class="active">Checkout</a></li>
          <li><a href="contacto.html">Contacto</a></li>
        </ul>
      </nav>
    </div>
  </header>

  <!-- ================================
       MAIN: contenido principal
       ================================ -->
  <main class="container">
    <h2>Resumen de tu pedido</h2>

    <!-- Si el carrito está vacío -->
    <?php if (empty($items)): ?>
      <p>Tu carrito está vacío. <a href="index.php">Volver a productos</a></p>

    <!-- Si hay ítems, los listamos en una tabla -->
    <?php else: ?>
      <table style="width:100%; border-collapse: collapse; margin-bottom: 2rem;">
        <thead>
          <tr style="background: #ae9701; color: #fff;">
            <!-- Cabeceras de la tabla -->
            <th style="padding: 0.5rem; text-align:left;">Producto</th>
            <th style="padding: 0.5rem; text-align:right;">Precio unitario</th>
            <th style="padding: 0.5rem; text-align:center;">Cantidad</th>
            <th style="padding: 0.5rem; text-align:right;">Subtotal</th>
            <th style="padding: 0.5rem;">Eliminar</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($items as $item): ?>
            <tr style="background: #fff; border-bottom: 1px solid #eee;">
              <!-- Nombre del producto -->
              <td style="padding: 0.5rem;"><?= htmlspecialchars($item['nombre']) ?></td>
              <!-- Precio unitario formateado -->
              <td style="padding: 0.5rem; text-align:right;">
                $ <?= number_format($item['precio_unitario'],0,',','.') ?>
              </td>
              <!-- Cantidad -->
              <td style="padding: 0.5rem; text-align:center;"><?= $item['cantidad'] ?></td>
              <!-- Subtotal formateado -->
              <td style="padding: 0.5rem; text-align:right;">
                $ <?= number_format($item['precio_total_item'],0,',','.') ?>
              </td>
              <!-- Enlace para eliminar ítem -->
              <td style="padding: 0.5rem; text-align:center;">
                <a href="checkout.php?remove=<?= $item['codprod'] ?>"
                   class="btn btn-sm btn-danger">
                  &times;
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
        <tfoot>
          <!-- Fila con el total general -->
          <tr>
            <td colspan="3" style="padding: 0.5rem; text-align:right; font-weight:bold;">
              Total:
            </td>
            <td style="padding: 0.5rem; text-align:right; font-size:1.2rem; font-weight:bold;">
              $ <?= number_format($total,0,',','.') ?>
            </td>
            <td></td>
          </tr>
        </tfoot>
      </table>

      <!-- Formulario para datos de envío -->
      <section class="contact-form">
        <h3>Datos de envío</h3>
        <form action="confirmacion.php" method="post">
          <!-- Campo Nombre -->
          <label for="nombre">Nombre completo</label>
          <input type="text" id="nombre" name="nombre"
                 placeholder="Tu nombre" required>

          <!-- Campo Dirección -->
          <label for="direccion">Dirección de envío</label>
          <input type="text" id="direccion" name="direccion"
                 placeholder="Calle, número" required>

          <!-- Campo Ciudad -->
          <label for="ciudad">Ciudad</label>
          <input type="text" id="ciudad" name="ciudad"
                 placeholder="Ej: Santiago" required>

          <!-- Campo Teléfono -->
          <label for="telefono">Teléfono</label>
          <input type="tel" id="telefono" name="telefono"
                 placeholder="+56 9 1234 5678" required>

          <!-- Botón de envío del pedido -->
          <button type="submit" class="btn">Finalizar compra</button>
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
