
<?php 
/**
 * @OA\Info(
 *     title="API de Pedidos",
 *     version="1.0.0",
 *     description="API para gestionar pedidos y ventas, incluyendo la creación de pedidos, consulta de productos y entrega.",
 *     @OA\Contact(
 *         email="soporte@tusistema.com"
 *     ),
 *     @OA\License(
 *         name="MIT",
 *         url="https://opensource.org/licenses/MIT"
 *     )
 * )
 * 
 * @OA\Tag(
 *     name="Pedidos",
 *     description="Operaciones relacionadas con los pedidos."
 * )
 * 
 * @OA\Schema(
 *     schema="Pedido",
 *     type="object",
 *     required={"id", "estado", "clientes_id", "administradores_id", "cobrado", "direccion", "ventas_id"},
 *     @OA\Property(property="id", type="integer", description="ID del pedido"),
 *     @OA\Property(property="estado", type="string", enum={"PENDIENTE", "ENTREGADO"}, description="Estado del pedido"),
 *     @OA\Property(property="fecha_creacion", type="string", format="date-time", description="Fecha de creación del pedido"),
 *     @OA\Property(property="fecha_actualizacion", type="string", format="date-time", description="Fecha de actualización del pedido"),
 *     @OA\Property(property="clientes_id", type="integer", description="ID del cliente"),
 *     @OA\Property(property="administradores_id", type="integer", description="ID del administrador"),
 *     @OA\Property(property="cobrado", type="integer", description="Monto cobrado"),
 *     @OA\Property(property="direccion", type="string", description="Dirección de entrega"),
 *     @OA\Property(property="fecha_entrega", type="string", format="date-time", description="Fecha de entrega"),
 *     @OA\Property(property="ventas_id", type="integer", description="ID de la venta asociada al pedido"),
 *     @OA\Property(
 *         property="productos",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/Producto")
 *     )
 * )
 * 
 * @OA\Schema(
 *     schema="Producto",
 *     type="object",
 *     @OA\Property(property="id", type="integer", description="ID del producto"),
 *     @OA\Property(property="cantidad", type="integer", description="Cantidad del producto")
 * )
 */