<?php
require 'vendor/autoload.php';
use setasign\Fpdi\Fpdi;

ini_set("log_errors", 1);
ini_set("error_log", "error_pdf");


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['archivos']) && isset($_POST['orden'])) {
    $archivosSubidos = $_FILES['archivos'];
    $orden = explode(',', $_POST['orden']);
    $rutaTemporal = 'tmp/';
    $archivos = [];

    // Crear directorio temporal si no existe
    if (!file_exists($rutaTemporal)) {
        mkdir($rutaTemporal, 0777, true);
    }

    // Mover archivos subidos al directorio temporal y ordenarlos
    foreach ($orden as $i) {
        $nombreArchivo = $archivosSubidos['name'][$i];
        $tipoArchivo = $archivosSubidos['type'][$i];
        $rutaArchivo = $rutaTemporal . basename($nombreArchivo);
        //Valida si es PDF o tiene otra extension:
        if ($tipoArchivo !== 'application/pdf') {
            echo "<script>";
            echo "alert('El archivo $nombreArchivo no es un PDF válido.');";
            error_log("El archivo $nombreArchivo no es un PDF válido.");
            echo "window.location.href = 'index.html';";
            echo "</script>";
            exit;
        }

        if (move_uploaded_file($archivosSubidos['tmp_name'][$i], $rutaArchivo)) {
            $archivos[] = $rutaArchivo;
        } else {
            echo "<script>";
            echo "alert('Error al subir el archivo: $nombreArchivo');";
            echo "window.location.href = 'index.html';";
            echo "</script>";
            error_log("Error al subir archivos $nombreArchivo por: permisos insuficienes en carpetas/archivos-----archivo demasiado pesado-----archivo con caracteres no validos - espacio en disco insuficiente --");
            exit;
        }
    }

    // Verificar archivos subidos
    foreach ($archivos as $archivo) {
        if (!file_exists($archivo) || filesize($archivo) === 0) {
            echo "<script>";
            echo "alert('El archivo $nombreArchivo está corrupto o vacío.');";
            echo "window.location.href = 'index.html';";
            echo "</script>";
            error_log("Archivo vacio o corrupto: $nombreArchivo");
            exit;
        }
    }

    // Nombre del archivo PDF resultante
    $archivoSalida = 'unificado.pdf';
    try {
         
    // Unir los PDFs
    unirPDFs($archivos, $archivoSalida);

    // Descargar el archivo unificado
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . basename($archivoSalida) . '"');
    readfile($archivoSalida);
} catch (Exception $e) {
    //echo "Excepción capturada: " . $e->getMessage();
    echo "<script>";
            echo "alert('Uno o mas archivos tienen conflicto, Comunicate con Soporte');";
            echo "window.location.href = 'index.html';";
            echo "</script>";
            error_log("La union de los archivos que contiene $nombreArchivo   tiene problemas");
            array_map('unlink', glob("tmp/*"));
            exit;
            
            
}   


    // Eliminar archivos temporales
    foreach ($archivos as $archivo) {
        unlink($archivo);
    }
    unlink($archivoSalida);
} else {
    echo "No se han subido archivos.";
}
// try {
//     unirPDFs($archivos, $archivoSalida);
// } catch (Exception $e) {
//     $errorMsg = "Error al unir los PDFs: " . $e->getMessage();
//     echo "<script>alert('$errorMsg');</script>";
//     error_log($errorMsg);
//     exit;
// }

function unirPDFs($archivos, $archivoSalida) {
    $pdf = new FPDI();

    foreach ($archivos as $archivo) {
        $paginas = $pdf->setSourceFile($archivo);
        for ($i = 1; $i <= $paginas; $i++) {
            $tplIdx = $pdf->importPage($i);
            $specs = $pdf->getTemplateSize($tplIdx);

            // Determinar orientación y tamaño
            $orientation = ($specs['width'] > $specs['height']) ? 'L' : 'P';
            $pdf->AddPage($orientation, [$specs['width'], $specs['height']]);
            $pdf->useTemplate($tplIdx, 0, 0, $specs['width'], $specs['height']);
        }
    }

    $pdf->Output('F', $archivoSalida);
    error_log("Se unieron pdf correctamente $archivoSalida");
    ini_set("log_errors", 1);
    ini_set("error_log", "error_log.txt");
}
?>
