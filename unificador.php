<?php
require 'vendor/autoload.php';

use setasign\Fpdi\Fpdi;

function unirPDFs($archivos, $archivoSalida) {
    $pdf = new FPDI();

    foreach ($archivos as $archivo) {
        $paginas = $pdf->setSourceFile($archivo);
        for ($i = 1; $i <= $paginas; $i++) {
            $pdf->AddPage();
            $paginaImportada = $pdf->importPage($i);
            $pdf->useTemplate($paginaImportada);
        }
    }

    $pdf->Output('F', $archivoSalida);
}
?>
