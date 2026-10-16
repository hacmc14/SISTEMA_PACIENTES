<?php
$conexion = new mysqli("localhost","root", "","sistema_pacientes");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}
$conexion->set_charset("utf8mb4");

$conexion->query(
    "CREATE TABLE IF NOT EXISTS dietas_pacientes (
        paciente_id INT NOT NULL PRIMARY KEY,
        dieta VARCHAR(255) NOT NULL,
        suministrada TINYINT(1) NOT NULL DEFAULT 0,
        fecha_actualizacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
            ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
);

$columnas_dieta = $conexion->query("SHOW COLUMNS FROM dietas_pacientes LIKE 'suministrada'");
if ($columnas_dieta->num_rows === 0) {
    $conexion->query(
        "ALTER TABLE dietas_pacientes
         ADD COLUMN suministrada TINYINT(1) NOT NULL DEFAULT 0"
    );
}

$conexion->query("CREATE TABLE IF NOT EXISTS usuarios_sistema (
 id INT AUTO_INCREMENT PRIMARY KEY,
 nombre VARCHAR(100) NOT NULL,
 usuario VARCHAR(40) NOT NULL UNIQUE,
 clave_hash VARCHAR(255) NOT NULL,
 rol ENUM('master','usuario') NOT NULL DEFAULT 'usuario',
 creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

?>