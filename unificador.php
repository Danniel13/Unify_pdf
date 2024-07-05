<?php
require 'vendor/autoload.php';

use setasign\Fpdi\Fpdi;

function unirPDFs($archivos, $archivoSalida) {
    $pdf = new FPDI();
    try {
        //code...
    } catch (\Throwable $th) {
        //throw $th;
    }
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
}
?>
